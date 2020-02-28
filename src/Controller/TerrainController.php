<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Terrain2Repository;
use App\Entity\Terrain2;

class TerrainController extends AbstractController
{
    private $terrain2Repository;
    
    public function __construct(Terrain2Repository $terrain2Repository){
      $this->terrain2Repository = $terrain2Repository;
    }

    /**
     * @Route("/admin/terrain/add/{id}", name="admin_add_terrain")
     */
    public function new(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $terrain = new Terrain2();
        if(!is_null($id))
            $terrain = $this->terrain2Repository->find($id);

    	if ($request->isXmlHttpRequest()) {
    		if ($request->isMethod('POST')) {
                $terrain->setNom($request->get('nom'));
                $em->persist($terrain);
                $em->flush();

    			return new Response("Enregistrement effectué avec succès", 200);
    		}
    		else{
                $action = is_null($id) ? "Ajout" : "Edition";
    			$html = $this->renderView('admin/formulaires/add_terrain.html.twig', [
                    'action'=>$action,
                    'terrain'=>$terrain,
                    'url'=> $this->generateUrl('admin_add_terrain', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
            	]);
            	$response = new Response(json_encode($html));
	            $response->headers->set('Content-Type', 'application/json');

	            return $response;
    		}
        }
        return  new Response("passer par une requette ajax");
    }
}
