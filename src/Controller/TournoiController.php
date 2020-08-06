<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TypeTournoiRepository;
use App\Repository\TournoiRepository;
use App\Repository\EquipeRepository;
use App\Repository\JoueurRepository;
use App\Repository\TerrainRepository;
use App\Repository\Match2Repository;
use App\Repository\Terrain2Repository;

use App\Entity\TypeTournoi;
use App\Entity\Tournoi;
use App\Entity\Equipe;
use App\Entity\Terrain;
use App\Entity\Joueur;
use App\Entity\Terrain2;
use App\Entity\Match2;

class TournoiController extends AbstractController
{
    private $global_s;
    private $typeTournoiRepository;
    private $tournoiRepository;
    private $equipeRepository;
    private $terrainRepository;
    private $terrain2Repository;
    private $match2Repository;
    private $params;
    
    public function __construct(ParameterBagInterface $params, TypeTournoiRepository $typeTournoiRepository, TournoiRepository $tournoiRepository, EquipeRepository $equipeRepository, TerrainRepository $terrainRepository, Match2Repository $match2Repository, Terrain2Repository $terrain2Repository){
        $this->params = $params;
      $this->typeTournoiRepository = $typeTournoiRepository;
      $this->tournoiRepository = $tournoiRepository;
      $this->equipeRepository = $equipeRepository;
      $this->terrainRepository = $terrainRepository;
      $this->terrain2Repository = $terrain2Repository;
      $this->match2Repository = $match2Repository;
    }

    /**
     * @Route("/admin/match/update-score/{id}", name="update_score")
     */
    public function updateScore(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $match = $this->match2Repository->find($id);
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                $newScore = $request->get('score1')."-".$request->get('score2');
                $newScore = str_replace(" ", "", $newScore);
                $match->setScore($newScore);
                if( (int)$request->get('score1') > (int)$request->get('score2') )
                    $match->setVainqueur($match->getEquipes()[0]->getId());
                elseif((int)$request->get('score1') < (int)$request->get('score2'))
                    $match->setVainqueur($match->getEquipes()[1]->getId());
                else
                    $match->setVainqueur(null);

                $em->persist($match);
                $em->flush();

                return new Response("Enregistrement effectué avec succès", 200);
            }
            else{
                if(is_null($match->getScore()))
                    $tabScore = [0,0];
                else
                    $tabScore = explode("-", $match->getScore());

                $tabEquipe = [ $match->getEquipes()[0]->getNom(), $match->getEquipes()[1]->getNom() ];
                $html = $this->renderView('admin/formulaires/score_update.html.twig', [
                    'tabScore'=>$tabScore,
                    'tabEquipe'=>$tabEquipe,
                    'url'=> $this->generateUrl('update_score', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
                ]);
                $response = new Response(json_encode($html));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        return  new Response("passer par une requette ajax");
    }

    /**
     * @Route("/admin/tournoi/add/{id}", name="admin_add_tournoi")
     */
    public function new(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = new Tournoi();
        if(!is_null($id))
            $tournoi = $this->tournoiRepository->find($id);

    	if ($request->isXmlHttpRequest()) {
    		if ($request->isMethod('POST')) {
                $typeTournoi = $this->typeTournoiRepository->findOneBy(['referent'=>$request->get('typeTournoi')]);
                $duree = $request->get('duree') - ceil($request->get('duree')*0.15);

                $tournoi->setType($typeTournoi);
                $tournoi->setNom($request->get('nom'));
                $tournoi->setNbrJoueur($request->get('nbrJoueur'));
                $tournoi->setNbrJoueurEquipe($request->get('nbrJoueurEquipe'));
                $nbrEquipe = ceil($request->get('nbrJoueur')/$request->get('nbrJoueurEquipe'));
                $nbrEquipe = ($nbrEquipe%2 != 0) ? ($nbrEquipe+1) : $nbrEquipe ;
                $tournoi->setNbrEquipe($nbrEquipe);
                $tournoi->setNbrTerrain($request->get('nbrTerrain'));
                $tournoi->setDuree($duree);
                $tournoi->setNbrTour( $this->getNbrTour($tournoi->getNbrEquipe(), $request->get('typeTournoi')));

                if( ($request->files->get('logo') instanceof UploadedFile) && 
                    (($request->files->get('logo'))->getError()=="0")){
                    $assetFile = "/public/images/logo/";
                    if (!file_exists($this->params->get('kernel.project_dir'). $assetFile)) {
                        mkdir($this->params->get('kernel.project_dir') . $assetFile, 0755);
                    }

                    $fullAssetFile = $this->params->get('kernel.project_dir') . $assetFile;
                    $tournoi->setLogo( $this->buildFiles([$request->files->get('logo')], ['jpg', 'png', 'jpeg'], 100000000, $fullAssetFile, false)[0] );
                }
                $em->persist($tournoi);
                $em->flush();
                $this->createEquipe($tournoi);
                $this->createTerrain($tournoi);
                $this->generateMatch($tournoi);
                if( ($request->files->get('fichier_joueurs') instanceof UploadedFile) && 
                    (($request->files->get('fichier_joueurs'))->getError()=="0")){
                    $tmpPathJoueur = $request->files->get('fichier_joueurs')->getRealPath();
                    $this->addJoueursEquipe($tournoi, $tmpPathJoueur);
                }

    			return new Response(json_encode(array('url'=> $this->generateUrl('tableau_de_bord', ['id'=>$tournoi->getId()], UrlGenerator::ABSOLUTE_URL))));
    		}
    		else{
                $action = is_null($id) ? "Ajout" : "Edition";
                $typeTournois = $this->typeTournoiRepository->findAll();
    			$html = $this->renderView('admin/formulaires/add_tournoi.html.twig', [
                    'action'=>$action,
                    'typeTournois' => $typeTournois,
                    'url'=> $this->generateUrl('admin_add_tournoi', ['id'=>$id], UrlGenerator::ABSOLUTE_URL),
                    'url_estimation'=> $this->generateUrl('admin_estimation_tournoi', [], UrlGenerator::ABSOLUTE_URL),
            	]);
            	$response = new Response(json_encode($html));
	            $response->headers->set('Content-Type', 'application/json');

	            return $response;
    		}
        }
        return  new Response("passer par une requette ajax");
    }

    /**
     * @Route("/admin/tournoi-libre/add/{id}", name="admin_add_tournoi_libre")
     */
    public function newLibre(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = new Tournoi();
        if(!is_null($id))
            $tournoi = $this->tournoiRepository->find($id);

        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                $typeTournoi = $this->typeTournoiRepository->findOneBy(['referent'=>'libre']);
                $dureeMatch = $request->get('dureeMatch') - ceil($request->get('dureeMatch')*0.15);

                $nbrRows = 2;
                if( ($request->files->get('fichier_joueurs') instanceof UploadedFile) && 
                    (($request->files->get('fichier_joueurs'))->getError()=="0")){
                    $tmpPathJoueur = $request->files->get('fichier_joueurs')->getRealPath();
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($tmpPathJoueur);
                    $spreadsheet = $spreadsheet->getActiveSheet();
                    $nbrRows = (int)$spreadsheet->getHighestRow();
                }

                $tournoi->setType($typeTournoi);
                $tournoi->setNom($request->get('nom'));
                $tournoi->setNbrJoueur($nbrRows);
                $tournoi->setNbrJoueurEquipe($request->get('nbrJoueurEquipe'));
                $nbrEquipe = ceil($tournoi->getNbrJoueur()/$request->get('nbrJoueurEquipe'));
                $nbrEquipe = ($nbrEquipe%2 != 0) ? ($nbrEquipe+1) : $nbrEquipe ;
                $tournoi->setNbrEquipe($nbrEquipe);

                if( ($request->files->get('logo') instanceof UploadedFile) && 
                    (($request->files->get('logo'))->getError()=="0")){
                    $assetFile = "/public/images/logo/";
                    if (!file_exists($this->params->get('kernel.project_dir'). $assetFile)) {
                        mkdir($this->params->get('kernel.project_dir') . $assetFile, 0755);
                    }

                    $fullAssetFile = $this->params->get('kernel.project_dir') . $assetFile;
                    $tournoi->setLogo( $this->buildFiles([$request->files->get('logo')], ['jpg', 'png', 'jpeg'], 100000000, $fullAssetFile, false)[0] );
                }
                $em->persist($tournoi);
                $em->flush();
                $this->createEquipe($tournoi);
                //$this->createTerrain($tournoi);
                $this->generateMatchLibre($tournoi, $dureeMatch);
                if( ($request->files->get('fichier_joueurs') instanceof UploadedFile) && 
                    (($request->files->get('fichier_joueurs'))->getError()=="0")){
                    $tmpPathJoueur = $request->files->get('fichier_joueurs')->getRealPath();
                    $nbrJoueur =  $this->addJoueursEquipe($tournoi, $tmpPathJoueur);
                }

                return new Response(json_encode(array('url'=> $this->generateUrl('tableau_de_bord_libre', ['id'=>$tournoi->getId()], UrlGenerator::ABSOLUTE_URL))));
            }
            else{
                $action = is_null($id) ? "Ajout" : "Edition";
                $typeTournois = $this->typeTournoiRepository->findAll();
                $html = $this->renderView('admin/formulaires/add_tournoi_libre.html.twig', [
                    'action'=>$action,
                    'typeTournois' => $typeTournois,
                    'url'=> $this->generateUrl('admin_add_tournoi_libre', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
                ]);
                $response = new Response(json_encode($html));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        return  new Response("passer par une requette ajax");
    }

    public function sendMailStartTournoi($tournoi, \Swift_Mailer $mailer){

        $tabEmail = $this->tournoiRepository->getEmailJoueurTournois($tournoi->getId());
        if(!count($tabEmail))
            return 1;

        $nbrPassage = $this->getNbrPassage($tournoi->getNbrEquipe(), $tournoi->getType()->getReferent(), $tournoi->getNbrTerrain());
        $dureePassage = is_null($tournoi) ? "" : $this->calculDureePassage($tournoi);
        $nbrMatch = is_null($tournoi) ? "" : $this->getNrbMatch($tournoi);
        $message = "<p>Bonjour, <br>Nous vous informons que la competition du tournoi <b>".$tournoi->getNom()."</b> vient de demarrer.<h3>Estimation du tournoi</h3><ul><li>".$tournoi->getNbrTour()." Tours, en ".$nbrPassage." Passages</li><li>".$nbrMatch." Matchs</li><li>".$dureePassage." minutes / match</li></ul><a href='".$this->generateUrl('client_homepage', ['id'=>$tournoi->getId()], UrlGenerator::ABSOLUTE_URL)."'>Cliquer ici</a> pour acceder aux ecrans de jeux</p>";
        try {
            $mail = (new \Swift_Message('Tournoi '.$tournoi->getNom()))
                ->setFrom(array('alexngoumo.an@gmail.com' => 'Tournoi'))
                ->setCc(array('alexngoumo.an@gmail.com'))
                ->setTo($tabEmail)
                ->setBody($message,
                    'text/html'
                );
            $mailer->send($mail);
        } catch (Exception $e) {
            print_r($e->getMessage());
        }      
        return 1;
    }

    public function sendMailStartTournoiLibre($tournoi, \Swift_Mailer $mailer){

        $tabEmail = $this->tournoiRepository->getEmailJoueurTournois($tournoi->getId());
        if(!count($tabEmail))
            return 1;

        $message = "<p>Bonjour, <br>Nous vous informons que la competition en partie libre <b>".$tournoi->getNom()."</b> vient de demarrer <a href='".$this->generateUrl('client_homepage', ['id'=>$tournoi->getId()], UrlGenerator::ABSOLUTE_URL)."'>Cliquer ici</a> pour acceder aux ecrans de jeux</p>";
        try {
            $mail = (new \Swift_Message('Competition '.$tournoi->getNom()))
                ->setFrom(array('alexngoumo.an@gmail.com' => 'Tournoi'))
                ->setCc(array('alexngoumo.an@gmail.com'))
                ->setTo($tabEmail)
                ->setBody($message,
                    'text/html'
                );
            $mailer->send($mail);
        } catch (Exception $e) {
            print_r($e->getMessage());
        }      
        return 1;
    }

    /**
     * @Route("/admin/tournoi/estimation", name="admin_estimation_tournoi")
     */
    public function calculEstimation(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                $typeTournois = $this->typeTournoiRepository->findAll();
                $ESTIMATION = [];
                $duree = $request->get('duree') - ceil($request->get('duree')*0.15);
                foreach ($typeTournois as $key => $value) {
                    $referent = $value->getReferent();
                    if($referent == "libre")
                        continue;

                    $ESTIMATION[$referent] = [];
                    for($i = 2; $i<=4; $i++){//estimation avec 2,3 et 4 joueurs/eqp
                        $datas = [];
                        $nbrEquipe = ceil($request->get('nbrJoueur')/$i);
                        $nbrEquipe = ($nbrEquipe%2 != 0) ? ($nbrEquipe+1) : $nbrEquipe ;
                        $datas['nbr_joueur_equipe'] = $i;
                        $datas['nbr_equipe'] = $nbrEquipe;
                        $datas['nbr_tour'] = $this->getNbrTour($nbrEquipe, $referent);
                        $datas['nbr_match'] = $this->getNrbMatchEstimation($nbrEquipe, $referent);
                        $datas['dureePassage'] = $this->calculDureePassageEstimation($duree, $request->get('nbrTerrain'), $nbrEquipe, $referent);
                        $datas['nbr_passage'] = $this->getNbrPassage($nbrEquipe, $referent, $request->get('nbrTerrain'));
                        $ESTIMATION[$referent][] = $datas;
                    }
                }
                
                return new Response(json_encode($ESTIMATION));
            }
        }
        return  new Response("passer par une requette ajax");
    }

    public function buildFiles($files, $tabExtension, $maxSize, $directorySave, $save_originalName){
        $filesArray = array();
        foreach ($files as $key => $value) {
            if( ($value instanceof UploadedFile) && ($value->getError()=="0")){
                if($value->getSize() < $maxSize){
                    $originalName=$value->getClientOriginalName();
                    $name_array = explode('.',$originalName);
                    $file_type=$name_array[sizeof($name_array)-1];
                    $nameWithoutExt = str_replace(".".$file_type, "", $originalName);
                    $valid_filetypes=  $tabExtension;
                    
                    if(in_array(strtolower($file_type),$valid_filetypes)){
                        if($save_originalName)
                            $name = $originalName;
                        else
                            $name=$nameWithoutExt.'-'.Date("Yds").'.'.$file_type;
                        $value->move($directorySave, $name);
                        $filesArray[] = $name;
                    }else{
                        print_r("Entrez votre image avec une extension valide");
                    }
                }else{
                    print_r("Fichier trop lourd".$value->getSize());
                }
            }else{
                print_r("Erreur de chargement du fichier");
            }            
        }
        return $filesArray;
    }

    /**
     * @Route("/admin/tournois", name="tableau_tournois_list")
     */
    public function tounoisList(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tournois = $this->tournoiRepository->findAll();
        
        return $this->render('admin/tournois_list.html.twig', [
            'tournois' => $tournois
        ]);
    }

    /**
     * @Route("/admin/tournoi/{id}", name="tableau_de_bord")
     */
    public function index(Request $request, $id=null)
    {   
        $em = $this->getDoctrine()->getManager();
        $typeTournois = $this->typeTournoiRepository->findAll();
        
        if(!is_null($id)){
            $tournoi = $this->tournoiRepository->find($id);
            if($tournoi->getType()->getReferent() == 'libre')
                return $this->redirectToRoute('tableau_de_bord_libre', ['id'=>$tournoi->getId()]);
        }
        else{
            $tournoi = $this->tournoiRepository->findOneBy([], ['id'=>'DESC'], 0);
            if($tournoi->getType()->getReferent() == 'libre'){
                return $this->redirectToRoute('tableau_de_bord_libre', ['id'=>$tournoi->getId()]);
            }
            else
                return $this->redirectToRoute('tableau_de_bord', ['id'=>$tournoi->getId()]);
        }

        $matchs = $matchsHistorique = [];
        $first_match_playing_id = "";
        if(!is_null($tournoi)){
            if($tournoi->getEtat() == "termine")
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
            else{
                //$matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , $tournoi->getNbrTerrain());
                //$matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['date_debut'=>'DESC']);
                $matchs = $this->match2Repository->getMatchByEtat($tournoi->getId(), $tournoi->getCurrentTour());
            }

            $nbrEquipeQualifie =  $this->equipeRepository->getNbrEquipeQualifie($tournoi->getId());
            if($nbrEquipeQualifie == 4)
                $demieFinale_finale = "demie_finale";
            if($nbrEquipeQualifie == 2)
                $demieFinale_finale = "finale";

            $lastMatch = $this->match2Repository->findOneBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours'], null, 1);
            if(!is_null($lastMatch)){
                $first_match_playing_id = $lastMatch->getId();
            }

            $matchsHistorique = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'termine']);
        }

        return $this->render('admin/home.html.twig', [
            'typeTournois' => $typeTournois,
            'matchs' => $matchs,
            'matchsHistorique'=>$matchsHistorique,
            'tournoi'=> $tournoi,
            'winner'=> (!is_null($tournoi) && $tournoi->getEtat() == "termine") ? $this->equipeRepository->findOneBy(['en_competition'=> true, 'tournoi'=>$tournoi->getId()]) : "" ,
            'demieFinale_finale'=> isset($demieFinale_finale) ? $demieFinale_finale : "",
            'dureePassage'=> is_null($tournoi) ? "" : $this->calculDureePassage($tournoi),
            'nbr_passage' => $this->getNbrPassage($tournoi->getNbrEquipe(), $tournoi->getType()->getReferent(), $tournoi->getNbrTerrain()),
            'nbrMatch'=> is_null($tournoi) ? "" : $this->getNrbMatch($tournoi),
            'dateFinTournoi'=> (is_null($tournoi) || is_null($tournoi->getDateFin())) ? "" : $tournoi->getDateFin(),
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : "",
            'first_match_playing_id'=> $first_match_playing_id,
            'page'=>'dashboard'
        ]);
    }

    /**
     * @Route("/admin/tournoi-libre/{id}", name="tableau_de_bord_libre")
     */
    public function indexLibre(Request $request, $id=null)
    {   
        $em = $this->getDoctrine()->getManager();
        
        if(!is_null($id))
            $tournoi = $this->tournoiRepository->find($id);
            if($tournoi->getType()->getReferent() != 'libre')
                return $this->redirectToRoute('tableau_de_bord', ['id'=>$tournoi->getId()]);
        else{
            $tournoi = $this->tournoiRepository->findOneBy([], ['id'=>'DESC'], 0);
            if($tournoi->getType()->getReferent() != 'libre')
                return $this->redirectToRoute('tableau_de_bord', ['id'=>$tournoi->getId()]);
        }

        $matchs = [];
        $first_match_playing_id = "";
        if(!is_null($tournoi)){
            if($tournoi->getEtat() == "termine"){
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId()], ['id'=> 'DESC'], 1);
            }
            else{
                $matchs = $this->match2Repository->getMatchLibreByEtat($tournoi->getId());
            }

            $lastMatch = $this->match2Repository->findOneBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours'], null, 1);
            if(!is_null($lastMatch)){
                $first_match_playing_id = $lastMatch->getId();
            }

            $matchsHistorique = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'termine']);
        }
        
        return $this->render('admin/home_libre.html.twig', [
            'matchs' => $matchs,
            'matchsHistorique'=>$matchsHistorique,
            'tournoi'=> $tournoi,
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : "",
            'dateFinTournoi'=> (is_null($tournoi) || is_null($tournoi->getDateFin())) ? "" : $tournoi->getDateFin(),
            'first_match_playing_id'=> $first_match_playing_id,
            'page'=>'dashboard'
        ]);
    }

    /**
     * @Route("/admin/tournoi-launch/{id}", name="tournoi_launch")
     */
    public function launchTournoi(Request $request, $id, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $tournoi->setDateDebut(new \Datetime());
        $tournoi->setEtat('en_cours');
        $dateFinTournoi = "";
        $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
        $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);
        $tournoi->setDateFin(new \Datetime($dateFinTournoi));

        $matchCurrentTour = $this->match2Repository->findBy(['num_tour'=>1, 'tournoi'=>$tournoi->getId()],null, $tournoi->getNbrTerrain());
        foreach ($matchCurrentTour as $key => $value) {
            $value->setEtat('en_cours');
            $value->setDateDebut(new \Datetime());
            $currentDate = new \Datetime();
            $currentDate->add(new \DateInterval('PT'.$value->getDuree().'M'));
            $dateFin = $currentDate->format('Y-m-d H:i:s');
            $value->setDateFin((new \DateTime($dateFin)));
        }
        $this->definedDateNextMatch($tournoi);
        $em->flush();
        $this->sendMailStartTournoi($tournoi, $mailer);
        return $this->redirectToRoute('tableau_de_bord',['id'=>$id]);
    }


    /**
     * @Route("/admin/launch-match-libre/{id}", name="demarrer_match_libre")
     */
    public function launchPartieLibre(Request $request, $id, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $tournoi->setDateDebut(new \Datetime());
        $tournoi->setEtat('en_cours');

        $matchEnCour = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours']);
        foreach ($matchEnCour as $key => $value) {
            $value->setEtat('termine');
        }

        $matchCurrentTour = $this->match2Repository->findBy(['etat'=>'en_attente', 'tournoi'=>$tournoi->getId()], null, 1);
        foreach ($matchCurrentTour as $key => $value) {
            $value->setEtat('en_cours');
            $value->setDateDebut(new \Datetime());
            $currentDate = new \Datetime();
            $currentDate->add(new \DateInterval('PT'.$value->getDuree().'M'));
            $dateFin = $currentDate->format('Y-m-d H:i:s');
            $value->setDateFin((new \DateTime($dateFin)));
        }
        $this->definedDateNextMatchLibre($tournoi);
        $em->flush();
        $this->sendMailStartTournoiLibre($tournoi, $mailer);
        return $this->redirectToRoute('tableau_de_bord_libre',['id'=>$id]);
    }

    /**
     * @Route("/admin/launch-match-libre-next/{id}", name="demarrer_match_next_libre")
     */
    public function launchPartieNextLibre(Request $request, $id, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);

        $matchEnCour = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours']);
        foreach ($matchEnCour as $key => $value) {
            $value->setEtat('termine');
        }

        $matchCurrentTour = $this->match2Repository->findBy(['etat'=>'en_attente', 'tournoi'=>$tournoi->getId()], null, 1);
        foreach ($matchCurrentTour as $key => $value) {
            $value->setEtat('en_cours');
            $value->setDateDebut(new \Datetime());
            $currentDate = new \Datetime();
            $currentDate->add(new \DateInterval('PT'.$value->getDuree().'M'));
            $dateFin = $currentDate->format('Y-m-d H:i:s');
            $value->setDateFin((new \DateTime($dateFin)));
        }
        $this->definedDateNextMatchLibre($tournoi);
        $em->flush();
        return $this->redirectToRoute('tableau_de_bord_libre',['id'=>$id]);
    }

    /**
     * @Route("/admin/annuler-tournoi/{id}", name="annuler_tournoi")
     */
    public function annulerTournoi(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $tournoi->setActif(0);
        $tournoi->setEtat('termine');
        $em->flush();
        if($tournoi->getType()->getReferent() == 'libre')
            return $this->redirectToRoute('tableau_de_bord_libre',['id'=>$id]);
        else
            return $this->redirectToRoute('tableau_de_bord',['id'=>$id]);
    }

    /**
     * @Route("/admin/cloturer-tournoi/{id}", name="cloturer_tournois")
     */
    public function cloturerTournoi(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $tournoi->setEtat('termine');
        $em->flush();
        return $this->redirectToRoute('tableau_de_bord_libre',['id'=>$id]);
    }

    /**
     * @Route("/admin/tour-launch/{id}", name="tour_launch")
     */
    public function launchTour(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $currentTour = $tournoi->getCurrentTour();
   
        $matchAttente = $this->match2Repository->findBy(['num_tour'=>$currentTour, 'tournoi'=>$tournoi->getId(), 'etat'=>'en_attente'], null, $tournoi->getNbrTerrain());
        if(count($matchAttente)){
            $j = 0;
            $terrains = $this->terrain2Repository->findBy(['tournoi'=>$tournoi->getId()]);
            foreach ($matchAttente as $key => $value) {
                $value->setEtat('en_cours');
                $value->setDateDebut(new \Datetime());
                $currentDate = new \Datetime();
                $currentDate->add(new \DateInterval('PT'.$this->calculDureePassage($tournoi).'M'));
                $dateFin = $currentDate->format('Y-m-d H:i:s');
                $value->setDateFin((new \DateTime($dateFin)));
                if(isset( $terrains[$j] )){
                    $value->setTerrain2($terrains[$j]);
                    $j++;
                }
            }
            $em->flush();
        }
        $this->definedDateNextMatch($tournoi);
        return $this->redirectToRoute('tableau_de_bord', ['id'=>$id]);
    }

    public function definedDateNextMatch($tournoi_id){
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($tournoi_id);
        $currentTour = $tournoi->getCurrentTour();
        $nextMatchAttente = $this->match2Repository->findBy(['num_tour'=>$currentTour, 'tournoi'=>$tournoi->getId(), 'etat'=>'en_attente']);

        $nbrMatchAttente = count($nextMatchAttente);
        for ($i=0; $i < ceil($nbrMatchAttente/$tournoi->getNbrTerrain()); $i++) { 
            $inscrementMinute = 2*($i+1);
            for ($j=($tournoi->getNbrTerrain()*$i); ( $j < $nbrMatchAttente  && ( ($i+1)*$tournoi->getNbrTerrain()) ); $j++) { 
                $value = $nextMatchAttente[$j];
                $dateStart = strtotime('now + '.($value->getDuree()*$inscrementMinute).' minutes');
                $dateEnd = strtotime('now + '.($value->getDuree()*($inscrementMinute+1)).' minutes');
                $value->setDateDebut( new \Datetime(date('Y-m-d H:i:s', $dateStart)) );
                $value->setDateFin( new \Datetime(date('Y-m-d H:i:s', $dateEnd)) );   
            }
        }
        $em->flush();
        return 1;
    }

    public function definedDateNextMatchLibre($tournoi_id){
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($tournoi_id);
        $currentTour = $tournoi->getCurrentTour();
        $nextMatchAttente = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_attente']);

        $nbrMatchAttente = count($nextMatchAttente);
        for ($i=0; $i < $nbrMatchAttente; $i++) { 
            $inscrementMinute = $i+1;
            $value = $nextMatchAttente[$i];
            $dateStart = strtotime('now + '.($value->getDuree()*$inscrementMinute).' minutes');
            $dateEnd = strtotime('now + '.($value->getDuree()*($inscrementMinute+1)).' minutes');
            $value->setDateDebut( new \Datetime(date('Y-m-d H:i:s', $dateStart)) );
            $value->setDateFin( new \Datetime(date('Y-m-d H:i:s', $dateEnd)) );
        }
        $em->flush();
        return 1;
    }

    /**
     * @Route("/admin/goto-next-tour/{id}", name="goto_next_tour")
     */
    public function nextTour(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $currentTour = $tournoi->getCurrentTour();

        /* on met fin aux matchs qui viennent de ce jouer pour ce tour */
        $matchEnCour = $this->match2Repository->findBy(['num_tour'=>$currentTour, 'tournoi'=>$tournoi->getId(), 'etat'=>'en_cours']);
        foreach ($matchEnCour as $key => $value) {
            $value->setEtat('termine');
        }
        $em->flush();

        $matchAttente = $this->match2Repository->findBy(['num_tour'=>$currentTour, 'tournoi'=>$tournoi->getId(), 'etat'=>'en_attente'], null, $tournoi->getNbrTerrain());
        if(count($matchAttente)){
            /*$j = 0;
            $terrains = $this->terrain2Repository->findBy(['tournoi'=>$tournoi->getId()]);
            foreach ($matchAttente as $key => $value) {
                $value->setEtat('en_cours');
                $value->setDateDebut(new \Datetime());
                $currentDate = new \Datetime();
                $currentDate->add(new \DateInterval('PT'.$this->calculDureePassage($tournoi).'M'));
                $dateFin = $currentDate->format('Y-m-d H:i:s');
                $value->setDateFin((new \DateTime($dateFin)));
                if(isset( $terrains[$j] )){
                    $value->setTerrain2($terrains[$j]);
                    $j++;
                }
            }
            $em->flush();*/
            return $this->redirectToRoute('tableau_de_bord', ['id'=>$id]);
        }

        /* Demande d'aller au tour suivant */
        $tournoi->setCurrentTour($currentTour+1);
        $arrJoeurQualifie = $this->regroupeEquipe($tournoi);
        /* mise à jour des infos des match et equipe du tour precedent */
        $matchLastTour = $this->match2Repository->findBy(['num_tour'=>$currentTour, 'tournoi'=>$tournoi->getId()]);
        foreach ($matchLastTour as $key => $value) {
            $value->setEtat('termine');
            $equipesMatch = $value->getEquipes();
            if(!in_array($equipesMatch[0]->getId() , $arrJoeurQualifie)){
                $equipe = $this->equipeRepository->find($equipesMatch[0]->getId());
                $equipe->setEnCompetition(false);
            }
            if(!in_array($equipesMatch[1]->getId() , $arrJoeurQualifie)){
                $equipe = $this->equipeRepository->find($equipesMatch[1]->getId());
                $equipe->setEnCompetition(false);
            }
        }
        $em->flush();

        if(count($arrJoeurQualifie) >=2)
            $this->generateMatch($tournoi, $arrJoeurQualifie);
        else{
            $tournoi->setEtat('termine');
            $tournoi->setCurrentTour($currentTour);
            $em->flush();
            return $this->redirectToRoute('tableau_de_bord', ['id'=>$id]);
        }
        
        return $this->redirectToRoute('tableau_de_bord', ['id'=>$id]);
    }

    public function getNrbMatch($tournoi){
        if($tournoi->getType()->getReferent() == "ellimination-direct"){
            return $this->directIllimination($tournoi->getNbrEquipe());
        }
        elseif($tournoi->getType()->getReferent() == "deux-poules-perdant-vainqueur"){
            return $this->deuxPoules($tournoi->getNbrEquipe());
        }
    }

    public function getNbrPassage($nbrEquipe, $typeTournoi, $nbrTerrain){
        $nbrEquipe = ceil($nbrEquipe);
        if($typeTournoi == "ellimination-direct"){

            if ($nbrEquipe <= 2){
                return 1;
            }
            else{
                if($nbrEquipe%2 != 0)
                    $nbrEquipe = $nbrEquipe+1;

                return ceil( ($nbrEquipe/2) /$nbrTerrain) + $this->getNbrPassage( ($nbrEquipe/2), $typeTournoi, $nbrTerrain );
            }
        }
        elseif($typeTournoi == "deux-poules-perdant-vainqueur"){
            return (ceil( ($nbrEquipe/2) / $nbrTerrain)*2) + $this->getNbrPassage(ceil($nbrEquipe/4), 'ellimination-direct', $nbrTerrain);
        }
    }
    public function getNrbMatchEstimation($nbrEquipe, $typeTournoi){
        if($typeTournoi == "ellimination-direct"){
            return $this->directIllimination($nbrEquipe);
        }
        elseif($typeTournoi == "deux-poules-perdant-vainqueur"){
            return $this->deuxPoules($nbrEquipe);
        }
    }
    public function getNbrTour($nbrEquipe, $typeTournoi){
        $nbrEquipe = ceil($nbrEquipe);
        if($typeTournoi == "ellimination-direct"){
            if ($nbrEquipe <= 2)
                return 1;
            else{
                if($nbrEquipe%2 != 0)
                    $nbrEquipe = $nbrEquipe+1;
                return 1 + $this->getNbrTour( ($nbrEquipe/2), $typeTournoi );
            }
        }
        elseif($typeTournoi == "deux-poules-perdant-vainqueur"){
            return 2 + $this->getNbrTour( ($nbrEquipe/4), 'ellimination-direct' );
        }
    }

    public function addJoueursEquipe($tournoi, $tmpPathJoueur){
        $em = $this->getDoctrine()->getManager();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmpPathJoueur);
        $spreadsheet = $spreadsheet->getActiveSheet();
        $nbrRows = (int)$spreadsheet->getHighestRow();
        /*$joueurs = [];
        for($i=1; $i <= $nbrRows ; $i++) { 
            $joueurs[] = $spreadsheet->getCellByColumnAndRow(1, $i)->getValue();
        }*/
        $joueurs = $spreadsheet->toArray(); // all datas rows and cells
        $equipes = $this->equipeRepository->findBy(['tournoi'=>$tournoi->getId()]);
        //dd($joueurs);
        //$nbrJoueurPerEquipe = (($nbrRows/$tournoi->getNbrEquipe()) > $tournoi->getNbrJoueurEquipe()) ? $tournoi->getNbrJoueurEquipe() : ($nbrRows/$tournoi->getNbrEquipe());
        //$nbrJoueurPerEquipe = ($nbrRows/$tournoi->getNbrEquipe()) on fait jouer tout le monde
        $nbrJoueurPerEquipe = $tournoi->getNbrJoueurEquipe();

        $count = 0;
        foreach ($equipes as $key => $value) {
            for ($i=0; $i < ceil($nbrJoueurPerEquipe); $i++) { 
                if($count < $nbrRows){
                    $joueur = new Joueur();
                    $joueur->setNom($joueurs[$count][0]);
                    $joueur->setEmail($joueurs[$count][1]);
                    $joueur->setTelephone($joueurs[$count][2]);
                    $joueur->setEquipe($value);
                    $em->persist($joueur);
                }
                $count++;
            }
            if($i >= $nbrRows)
                break;
        }
        $em->flush();
        return $nbrRows;
    }

    public function createEquipe($tournoi){
        $em = $this->getDoctrine()->getManager();
        $letter = "a b c d e f g h i j k l m n o p q r s t u v w x y z";
        $nbrEquipe = $tournoi->getNbrEquipe();
        if($tournoi->getNbrEquipe() > 26){
            for ($i=1; $i <= ($nbrEquipe-26); $i++) { 
                $letter .= " a".$i;
            }
        }
        $letter = explode(" ", $letter);
        for ($i=0; $i < $nbrEquipe ; $i++) { 
            $equipe = new Equipe();
            $equipe->setNom("equipe ".$letter[$i]);
            $equipe->setTournoi($tournoi);
            $em->persist($equipe);
        }
        $em->flush();
        return 1;
    }

    public function createTerrain($tournoi){
        $em = $this->getDoctrine()->getManager();
        $nbrTerrain = $tournoi->getNbrTerrain();
        for ($i=1; $i <= $nbrTerrain ; $i++) { 
            $terrain = new Terrain2();
            $terrain->setNom("terrain ".$i);
            $terrain->setTournoi($tournoi);
            $em->persist($terrain);
        }
        $em->flush();
        return 1;
    }

    public function generateMatchLibre($tournoi, $dureeMatch){
        $em = $this->getDoctrine()->getManager();
        $equipes = $this->equipeRepository->findBy(['tournoi'=>$tournoi->getId()]);
        for ($i=0; $i< count($equipes); ($i=$i+2) ) { 
            $match = new Match2();
            $match->setDuree($dureeMatch);
            $match->setTournoi($tournoi);
            $match->addEquipe($equipes[$i]);
            $match->addEquipe($equipes[$i+1]);
            $em->persist($match);
        }
        $tournoi->setDuree($dureeMatch*count($tournoi->getMatchs2()));
        $em->flush();
    }

    public function generateMatch($tournoi, $arrJoeurQualifie = null){
        $em = $this->getDoctrine()->getManager();
        $currentTour = $tournoi->getCurrentTour();
        $terrains = $this->terrain2Repository->findBy(['tournoi'=>$tournoi->getId()]);

        /* on fait jouer toutes les equipes */
        if($currentTour == 1){
            $equipes = $this->equipeRepository->findBy(['tournoi'=>$tournoi->getId()]);
            $j=0;
            for ($i=0; $i< count($equipes); ($i=$i+2) ) { 
                $match = new Match2();
                $match->setDuree($this->calculDureePassage($tournoi));
                $match->setNumTour($currentTour);
                $match->setTournoi($tournoi);
                $match->addEquipe($equipes[$i]);
                $match->addEquipe($equipes[$i+1]);

                if(!isset($terrains[$j]))
                    $j = 0;
                $match->setTerrain2($terrains[$j]);
                    $j++;
                $em->persist($match);
            }
        }
        else{
            $terrains = $this->terrain2Repository->findBy(['tournoi'=>$tournoi->getId()]);
            $j=0;
            for ($i=0; $i< count($arrJoeurQualifie); ($i=$i+2) ) { 
                $match = new Match2();
                $match->setDuree($this->calculDureePassage($tournoi));
                $match->setNumTour($currentTour);
                $match->setTournoi($tournoi);
                $equipe1 = $this->equipeRepository->find($arrJoeurQualifie[$i]);
                $equipe2 = $this->equipeRepository->find($arrJoeurQualifie[$i+1]);
                $match->addEquipe($equipe1);
                $match->addEquipe($equipe2);

                if(!isset($terrains[$j]))
                    $j = 0;
                $match->setTerrain2($terrains[$j]);
                    $j++;
                $em->persist($match);
            }
        }
        $em->flush();

        return 1;
    }
    public function regroupeEquipe($tournoi){
        $em = $this->getDoctrine()->getManager();
        $currentTour = $tournoi->getCurrentTour();
        $matchLastTour = $this->match2Repository->findBy(['num_tour'=>($currentTour -1), 'tournoi'=>$tournoi->getId()]); 
        $ArrPerdant = $arrGagnant = [];
        foreach ($matchLastTour as $key => $valeur) {
            $tabScore = explode("-", $valeur->getScore());
            rsort($tabScore);

            /* match different de match null */
            if(!is_null($valeur->getScore()) && ($tabScore[0] != $tabScore[1]) ){
                $gagnant = $valeur->getVainqueur();// id vainqueur
                $arrGagnant[$gagnant] = abs($tabScore[1] - $tabScore[0]); // gagné avec un ecart de x but
                if (($valeur->getEquipes()[0])->getId() == $gagnant){
                    $perdant = ($valeur->getEquipes()[1])->getId();
                    $ArrPerdant[$perdant] = abs($tabScore[1] - $tabScore[0]); // perdue avec un ecart de x but
                }
                else{
                    $perdant = ($valeur->getEquipes()[0])->getId();
                    $ArrPerdant[$perdant] = abs($tabScore[1] - $tabScore[0]);
                }
            }//en cas de match null
            else{
                $arrGagnant[ ($valeur->getEquipes()[0])->getId() ] = 0;
                $arrGagnant[ ($valeur->getEquipes()[1])->getId() ] = 0;
            }
        }

        /* trie du meilleur gagnant au mauvais gagnant */
        arsort($arrGagnant);
        /* trie du meilleur perdant au pire perdant */
        asort($ArrPerdant);
        
        /* en cas d'equipe impair, on complete avec le meilleur perdant */
        if( (count($arrGagnant)%2 !=0 && count($ArrPerdant)%2 != 0) && (count($arrGagnant) >=2 ) ){
            $firstKeyPerdant = array_keys($ArrPerdant)[0];
            $arrGagnant[$firstKeyPerdant] = $ArrPerdant[$firstKeyPerdant];
            unset($ArrPerdant[$firstKeyPerdant]); 
        }
        
        /* elliminer les perdant apres le 2ieme tour ou en cas l'ellimination direct */
        //if( (($currentTour == 2) && ($tournoi->getType()->getReferent()=="ellimination-direct")) || ($currentTour >=3) ){
        if( ($tournoi->getType()->getReferent() == "ellimination-direct") || ($currentTour >= 3) ){
            $ArrPerdant = [];
        }
        $ArrayNewEquipes = array_merge(array_keys($arrGagnant), array_keys($ArrPerdant));

        return $ArrayNewEquipes;    
    }
    public function calculDureePassage($tournoi){
        $nbrPassage = $this->getNbrPassage($tournoi->getNbrEquipe(), $tournoi->getType()->getReferent(), $tournoi->getNbrTerrain());
        $dureePassage = ceil( ($tournoi->getDuree() ) / $nbrPassage );

        return $dureePassage;
    }
    public function calculDureePassageEstimation($dureeTournoi, $nbrTerrain, $nbrEquipe, $typeTournoi){
        $nbrPassage = $this->getNbrPassage($nbrEquipe, $typeTournoi, $nbrTerrain);
        $dureePassage = ceil( ($dureeTournoi ) / $nbrPassage );

        return $dureePassage;
    }
    public function directIllimination($valeur){
        if ($valeur <= 2)
            return 1;
        else{
            if($valeur%2 != 0)
                $valeur = $valeur+1;
            return ($valeur/2) + $this->directIllimination( ($valeur/2) );
        }
    }
    public function deuxPoules($valeur){
       return $valeur + $this->directIllimination(ceil($valeur/4));
    }
    public function isEndTournoi($tournoi){
        $nbrEquipeQualifie =  $this->equipeRepository->getNbrEquipeQualifie($tournoi->getId());
        if ($nbrEquipeQualifie > 1)
           return false;
        else{
            /*$em = $this->getDoctrine()->getManager();
            $tournoi->setEtat("termine");
            $em->flush();*/
            return true;
        }
    }

}
