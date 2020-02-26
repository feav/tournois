<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EquipeController extends AbstractController
{
    /**
     * @Route("/admin/equipe/add/{id}", name="admin_add_equipe")
     */
    public function new(Request $request, $id = null)
    {
    	if ($request->isXmlHttpRequest()) {
    		if ($request->isMethod('POST')) {
    			return new Response("Enregistrement effectué avec succès", 200);
    		}
    		else{
    			$html = $this->renderView('admin/formulaires/add_equipe.html.twig', [
                'datas' => [],
            	]);
            	$response = new Response(json_encode($html));
	            $response->headers->set('Content-Type', 'application/json');

	            return $response;
    		}
        }
        return  new Response("passer par une requette ajax");
    }
}
