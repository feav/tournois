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
     * @Route("/tournoi/{id}", name="client_homepage")
     */
    public function index( Request $Request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $typeTournois = $this->typeTournoiRepository->findAll();
       
        $tournoi = $this->tournoiRepository->find($id);
        $matchs = $this->match2Repository->findBy(['tournoi'=>$tournoi->getId(), 'etat'=>'en_cours']);

       $dateFinTournoi = "";
        if(!is_null($tournoi)){
            $newtimestamp = strtotime($tournoi->getDateDebut()->format('Y-m-d H:i:s').' '.$tournoi->getDuree().' minute');           
            $dateFinTournoi = date('Y-m-d H:i:s', $newtimestamp);
        }
        
        return $this->render('website/index.html.twig', [
            'matchs' => $matchs,
            'tournoi'=> $tournoi,
            'dateFin'=> is_null($tournoi) ? "" : $dateFinTournoi
        ]);
    }

    /**
     * @Route("/resultat", name="client_resultat_tournoi")
     */
    public function resultat()
    {
        return $this->render('website/resultat.html.twig', []);
    }
    /**
     * @Route("/finale", name="client_finale_tournoi")
     */
    public function finale()
    {
        return $this->render('website/finale.html.twig', []);
    }
}
