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
        return 1;
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
                /*if($request->get('date_debut')){
                    $tournoi->setDateDebut(new \Datetime());
                    $tournoi->setDateFin(new \Datetime());// dateDebut + duree
                }*/
                $em->persist($tournoi);
                $em->flush();
                $this->createEquipe($tournoi);
                $this->createTerrain($tournoi);
                $this->generateMatch($tournoi);

    			return new Response("Enregistrement effectué avec succès", 200);
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
        
        return $this->render('admin/home.html.twig', [
            'typeTournois' => $typeTournois,
            'tournoi'=> $tournoi,
            'dureeTour'=> is_null($tournoi) ? "" : ($tournoi->getDuree() / $tournoi->getNbrTour()),
            'nbrMatch'=> is_null($tournoi) ? "" : $this->getNrbMatch($tournoi)
        ]);
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

    public function generateMatch($tournoi){
        $em = $this->getDoctrine()->getManager();
        $currentTour = $tournoi->getCurrentTour();
        if( $currentTour <= $tournoi->getNbrTour() ){

            $tabTerrain = [];
            for ($i=1; $i <= $tournoi->getNbrTerrain(); $i++) { 
                $tabTerrain[] = $i;
            }
            /* on fait jouer toutes les equipes */
            if($currentTour == 1){
                $equipes = $this->equipeRepository->findBy(['tournoi'=>$tournoi->getId()]);
                $j=0;
                for ($i=0; $i< count($equipes); ($i=$i+2) ) { 
                    $match = new Match2();
                    $match->setDuree($tournoi->getDuree() / $this->getNbrTour($tournoi->getNbrEquipe()));
                    $match->setNumTour($currentTour);
                    $match->setTournoi($tournoi);
                    $match->addEquipe($equipes[$i]);
                    $match->addEquipe($equipes[$i+1]);

                    if(isset($tabTerrain[$j])){
                        $terrain = $this->terrain2Repository->find($tabTerrain[$j]);
                        $match->setTerrain2($terrain);
                        $j++;
                    }
                    $em->persist($match);
                }
            }
            else{
                $ArrayNewEquipes = $this->regroupeEquipe($tournoi, $currentTour);
                for ($i=0; $i< count($ArrayNewEquipes); ($i=$i+2) ) { 
                    $match = new Match();
                    $match->setDuree($tournoi->getDuree() / $this->getNbrTour($tournoi->getNbrEquipe()));
                    $match->setNumTour($currentTour);
                    $match->setTournoi($tournoi);
                    $equipe1 = $this->equipeRepository->find(array_keys($ArrayNewEquipes)[$i]);
                    $equipe2 = $this->equipeRepository->find(array_keys($ArrayNewEquipes)[$i+1]);
                    $match->addEquipe($equipe1);
                    $match->addEquipe($equipe2);
                    $em->persist($match);
                }
            }
            $em->flush();
        }
        else
            return new Response('fin du tournoi');

        return 1;
    }
    public function regroupeEquipe($tournoi, $currentTour){
        $em = $this->getDoctrine()->getManager();
        $matchLastTour = $this->match2Repository->findBy(['num_tour'=>($currentTour -1), 'tournoi'=>$tournoi->getId()]); 
        $ArrPerdant = $arrGagnant = [];

        foreach ($matchLastTour as $key => $value) {
            $tabScore = explode("-", $valeur->getScore());
            rsort($tabScore);

            /* match different de match null */
            if($tabScore[0] != $tabScore[1]){
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

        if(count($arrGagnant)%2 !=0 && count($ArrPerdant)%2 != 0){
            $firstKeyPerdant = array_keys($ArrPerdant)[0];
            $arrGagnant[$firstKeyPerdant] = $array[$firstKeyPerdant];
            array_shift($ArrPerdant);
        }

        /* 2 poules gagnant et vainqueur */
        if($currentTour >=3)
            $ArrPerdant = [];
        $ArrayNewEquipes = array_merge($arrGagnant, $ArrPerdant);

        return $ArrayNewEquipes;
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

}
