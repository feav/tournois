<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TypeTournoiRepository;
use App\Repository\TournoiRepository;
use App\Repository\EquipeRepository;
use App\Repository\TerrainRepository;
use App\Repository\Match2Repository;
use App\Repository\Terrain2Repository;

use App\Entity\TypeTournoi;
use App\Entity\Tournoi;
use App\Entity\Equipe;
use App\Entity\Terrain;
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
    
    public function __construct(TypeTournoiRepository $typeTournoiRepository, TournoiRepository $tournoiRepository, EquipeRepository $equipeRepository, TerrainRepository $terrainRepository, Match2Repository $match2Repository, Terrain2Repository $terrain2Repository){
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
                $typeTournoi = $this->typeTournoiRepository->find($request->get('typeTournoi'));
                $tournoi->setType($typeTournoi);
                $tournoi->setNom($request->get('nom'));
                $tournoi->setNbrEquipe($request->get('nbrEquipe'));
                $tournoi->setNbrTerrain($request->get('nbrTerrain'));
                $tournoi->setDuree($request->get('duree'));
                $tournoi->setNbrTour( $this->getNbrTour($tournoi->getNbrEquipe()) );

                $em->persist($tournoi);
                $em->flush();
                $this->createEquipe($tournoi);
                $this->createTerrain($tournoi);
                $this->generateMatch($tournoi);
    			return new Response(json_encode(array('url'=> $this->generateUrl('tableau_de_bord', ['id'=>$tournoi->getId()], UrlGenerator::ABSOLUTE_URL))));
    		}
    		else{
                $action = is_null($id) ? "Ajout" : "Edition";
                $typeTournois = $this->typeTournoiRepository->findAll();
    			$html = $this->renderView('admin/formulaires/add_tournoi.html.twig', [
                    'action'=>$action,
                    'typeTournois' => $typeTournois,
                    'url'=> $this->generateUrl('admin_add_tournoi', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
            	]);
            	$response = new Response(json_encode($html));
	            $response->headers->set('Content-Type', 'application/json');

	            return $response;
    		}
        }
        return  new Response("passer par une requette ajax");
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
        if(!is_null($id))
            $tournoi = $this->tournoiRepository->find($id);
        else
            $tournoi = $this->tournoiRepository->findOneBy([], ['id'=>'DESC'], 0);

        if($tournoi->getEtat() == "termine")
            $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()],null , $tournoi->getNbrTerrain());
        else
            $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , $tournoi->getNbrTerrain());

        $nbrEquipeQualifie =  $this->equipeRepository->getNbrEquipeQualifie($tournoi->getId());
        if($nbrEquipeQualifie == 4)
            $demieFinale_finale = "demie_finale";
        if($nbrEquipeQualifie == 2)
            $demieFinale_finale = "finale";

        return $this->render('admin/home.html.twig', [
            'typeTournois' => $typeTournois,
            'matchs' => $matchs,
            'tournoi'=> $tournoi,
            'winner'=> ($tournoi->getEtat() == "termine") ? $this->equipeRepository->findOneBy(['en_competition'=> true, 'tournoi'=>$tournoi->getId()]) : "" ,
            'demieFinale_finale'=> isset($demieFinale_finale) ? $demieFinale_finale : "",
            'dureeTour'=> is_null($tournoi) ? "" : $this->calculDureePassage($tournoi),
            'nbrMatch'=> is_null($tournoi) ? "" : $this->getNrbMatch($tournoi)
        ]);
    }

    /**
     * @Route("/admin/tournoi-launch/{id}", name="tournoi_launch")
     */
    public function launchTournoi(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tournoi = $this->tournoiRepository->find($id);
        $tournoi->setDateDebut(new \Datetime());
        $matchCurrentTour = $this->match2Repository->findBy(['num_tour'=>1, 'tournoi'=>$tournoi->getId()],null, $tournoi->getNbrTerrain());
        foreach ($matchCurrentTour as $key => $value) {
            $value->setEtat('en_cours');
            $value->setDateDebut(new \Datetime());
            $currentDate = new \Datetime();
            $currentDate->add(new \DateInterval('PT'.$value->getDuree().'M'));
            $dateFin = $currentDate->format('Y-m-d H:i:s');
            $value->setDateFin((new \DateTime($dateFin)));
        }

        $em->flush();
        return $this->redirectToRoute('tableau_de_bord',['id'=>$id]);
    }

    /**
     * @Route("/admin/goto-next-tour/{id}", name="goto_next_tour")
     */
    public function nextTour(Request $request, $id)
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
    public function getNbrTour($nbrEquipe){
        if ($nbrEquipe == 2)
            return 1;
        else{
            if($nbrEquipe%2 != 0)
                $nbrEquipe = $nbrEquipe+1;
            return 1 + $this->getNbrTour( ($nbrEquipe/2) );
        }
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

                if(isset( $terrains[$j] )){
                    $match->setTerrain2($terrains[$j]);
                    $j++;
                }
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

                if(isset( $terrains[$j] )){
                    $match->setEtat('en_cours');
                    $match->setDateDebut(new \Datetime());
                    $currentDate = new \Datetime();
                    $currentDate->add(new \DateInterval('PT'.$this->calculDureePassage($tournoi).'M'));
                    $dateFin = $currentDate->format('Y-m-d H:i:s');
                    $match->setDateFin((new \DateTime($dateFin)));
                    $match->setTerrain2($terrains[$j]);
                    $j++;
                }
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
        return ceil($tournoi->getDuree()/$this->getNrbMatch($tournoi)/$tournoi->getNbrTerrain());
    }
    public function directIllimination($valeur){
        if ($valeur == 2)
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
