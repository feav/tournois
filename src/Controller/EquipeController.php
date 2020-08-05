<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EquipeRepository;
use App\Repository\JoueurRepository;
use App\Repository\TournoiRepository;
use App\Entity\Equipe;
use App\Entity\Joueur;

class EquipeController extends AbstractController
{
    private $equipeRepository;
    private $joueurRepository;
    
    public function __construct(EquipeRepository $equipeRepository, JoueurRepository $joueurRepository){
      $this->equipeRepository = $equipeRepository;
      $this->joueurRepository = $joueurRepository;
    }

    /**
     * @Route("/admin/joueur/add/{equipe_id}/{id}", name="admin_add_joueur_equipe")
     */
    public function newJoueur(Request $request, $equipe_id, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $equipe = $this->equipeRepository->find($equipe_id);
        $joueur = new Joueur();
        if(!is_null($id)){
            $joueur = $this->joueurRepository->find($id);
        }

    	if ($request->isXmlHttpRequest()) {
    		if ($request->isMethod('POST')) {
                $joueur->setNom($request->get('nom'));
                $joueur->setEmail($request->get('email'));
                $joueur->setTelephone($request->get('telephone'));
                $joueur->setEquipe($equipe);
                $em->persist($joueur);
                $em->flush();

    			return new Response("Enregistrement effectué avec succès", 200);
    		}
    		else{
                $action = is_null($id) ? "Ajout" : "Edition";
    			$html = $this->renderView('admin/formulaires/add_equipe.html.twig', [
                    'action'=>$action,
                    'joueur'=>$joueur,
                    'url'=> $this->generateUrl('admin_add_joueur_equipe', ['equipe_id'=>$equipe_id, 'id'=>$id], UrlGenerator::ABSOLUTE_URL)
            	]);
            	$response = new Response(json_encode($html));
	            $response->headers->set('Content-Type', 'application/json');

	            return $response;
    		}
        }
        return  new Response("passer par une requette ajax");
    }

    /**
     * @Route("/admin/equipe/add/{id}", name="admin_add_equipe")
    */
    public function newEquipe(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $equipe = new Equipe();
        if(!is_null($id))
            $equipe = $this->equipeRepository->find($id);

        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                $equipe->setNom($request->get('nom'));
                $em->persist($equipe);
                $em->flush();

                return new Response("Enregistrement effectué avec succès", 200);
            }
            else{
                $action = is_null($id) ? "Ajout" : "Edition";
                $html = $this->renderView('admin/formulaires/add_equipe.html.twig', [
                    'action'=>$action,
                    'equipe'=>$equipe,
                    'url'=> $this->generateUrl('admin_add_equipe', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
                ]);
                $response = new Response(json_encode($html));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        return  new Response("passer par une requette ajax");
    }
    
    /**
     * @Route("/admin/add-new-joueur/{tournoi_id}", name="admin_add_joueur_tournois")
    */
    public function newJoueurLibre(Request $request, $tournoi_id, TournoiRepository $tournoiRepository){
        $em = $this->getDoctrine()->getManager();
        $equipe = new Equipe();
        $tournoi = $tournoiRepository->find($tournoi_id);
        
        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('POST')) {
                $equipe->setNom($request->get('nom'));
                $em->persist($equipe);
                $em->flush();

                return new Response("Enregistrement effectué avec succès", 200);
            }
            else{
                $action = is_null($id) ? "Ajout" : "Edition";
                $html = $this->renderView('admin/formulaires/add_equipe.html.twig', [
                    'action'=>$action,
                    'equipe'=>$equipe,
                    'url'=> $this->generateUrl('admin_add_equipe', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
                ]);
                $response = new Response(json_encode($html));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
        return  new Response("passer par une requette ajax");
    }
}
