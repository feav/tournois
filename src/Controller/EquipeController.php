<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EquipeRepository;
use App\Entity\Equipe;

class EquipeController extends AbstractController
{
    private $equipeRepository;
    
    public function __construct(EquipeRepository $equipeRepository){
      $this->equipeRepository = $equipeRepository;
    }

    /**
     * @Route("/admin/equipe/add/{id}", name="admin_add_equipe")
     */
    public function new(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $equipe = new Equipe();
        if(!is_null($id))
            $equipe = $this->equipeRepository->find($id);

    	if ($request->isXmlHttpRequest()) {
    		if ($request->isMethod('POST')) {
                $equipe->setNom($request->get('nom'));
                $equipe->setJoueurs($request->get('joueurs'));
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
