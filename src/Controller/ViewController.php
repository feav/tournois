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

class ViewController extends AbstractController
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
     * @Route("/get-match-en-cours", name="get_match_en_cours_xhr")
     */
    public function getMatchEncoursXhr(Request $request)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($request->get('id'));
        if($tournoi->getEtat() == "termine"){
          $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour()], ['id'=> 'DESC'], 1);
        }
        else{
          $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'num_tour'=>$tournoi->getCurrentTour(), 'etat'=>'en_cours'],null , $tournoi->getNbrTerrain());
        }

        $matchsArr = [];
        foreach ($matchs as $key => $value) {
            $equipeArr = []; 
            foreach ($value->getEquipes() as $key => $eqp) {
                $equipeArr[] = [
                    'id'=>$eqp->getId(),
                    'nom'=>$eqp->getNom(),
                    'joueurs'=>explode(',', $eqp->getJoueurs())
                ];
            }

            $matchsArr[]= [
                'vainqueur'=>$value->getVainqueur(),
                'terrain'=>$value->getTerrain2()->getNom(),
                'equipes'=> $equipeArr,
                'score'=> is_null($value->getScore()) ? [0,0] : explode('-', str_replace(" ", "", $value->getScore())),
                'date_debut'=>$value->getDateDebut()
            ];
        }

        $tournoiArr = [
          'id'=>$tournoi->getId(),
          'etat'=>$tournoi->getEtat(),
          'num_tour'=>$tournoi->getCurrentTour()
        ];

        return new Response(json_encode(['matchs'=>$matchsArr, "tournoi"=>$tournoiArr]));
    }

    /**
     * @Route("/get-match-en-termine", name="get_match_en_termine_xhr")
     */
    public function getMatchTermineXhr(Request $request)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($request->get('id'));
        $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'termine']);
        $matchsArr = [];

        $lastTour = $matchs[0]->getNumTour();
        $i = 0;
        foreach ($matchs as $key => $value) {
            $equipeArr = []; 
            foreach ($value->getEquipes() as $key => $eqp) {
                $equipeArr[] = [
                    'id'=>$eqp->getId(),
                    'nom'=>$eqp->getNom(),
                    'joueurs'=>explode(',', $eqp->getJoueurs())
                ];
            }
            $datas = [
                'vainqueur'=>$value->getVainqueur(),
                'terrain'=>$value->getTerrain2()->getNom(),
                'equipes'=> $equipeArr,
                'score'=> is_null($value->getScore()) ? [0,0] : explode('-', str_replace(" ", "", $value->getScore())),
                'date_debut'=>$value->getDateDebut(),
                'new_tour'=> ($i == 0) ? $lastTour : ""
            ];
            if($value->getNumTour() != $lastTour){
              $datas['new_tour']=$lastTour;
              $lastTour = $value->getNumTour();
            } 
            $matchsArr[]= $datas;
            $i++;
        }
        return new Response(json_encode($matchsArr));
    }
    /**
     * @Route("/scores-tournoi/{id}", name="client_resultat_tournoi")
     */
    public function resultat(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
       $dateFinTournoi = "";
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);
        }
        
        return $this->render('website/resultat.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi
        ]);
    }
    /**
     * @Route("/finale-tournoi/{id}", name="client_finale_tournoi")
     */
    public function finale(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);

       $dateFinTournoi = "";
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);
        }
        
        return $this->render('website/finale.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi
        ]);
    }


    /**
     * @Route("/{id}", name="client_homepage")
     */
    public function index( Request $Request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();       
        $tournoi = $this->tournoiRepository->find($id);
        $dateFinTournoi = "";
        if( !is_null($tournoi) && !is_null($tournoi->getDateDebut()) ){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);
        }
        
        return $this->render('website/index.html.twig', [
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi
        ]);
    }
}
