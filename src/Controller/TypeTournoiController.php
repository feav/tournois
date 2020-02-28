<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TypeTournoiRepository;
use App\Entity\TypeTournoi;

/**
 * @Route("/admin")
 */
class TypeTournoiController extends AbstractController
{

    private $global_s;
    private $typeTournoiRepository;
    
    public function __construct(TypeTournoiRepository $typeTournoiRepository){
      $this->typeTournoiRepository = $typeTournoiRepository;
    }

    /**
     * @Route("/type-tournoi/add/{id}", name="admin_add_type_tournoi")
     */
    public function new(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $typeTournoi = new TypeTournoi();
        if(!is_null($id))
            $typeTournoi = $this->typeTournoiRepository->find($id);

    	if ($request->isXmlHttpRequest()) {
    		if ($request->isMethod('POST')) {
                $typeTournoi->setNom($request->get('nom'));
                $typeTournoi->setReferent($request->get('referent'));
                
                $em->persist($typeTournoi);
                $em->flush();

    			return new Response("Enregistrement effectué avec succès", 200);
    		}
    		else{
                $action = is_null($id) ? "Ajout" : "Edition";
    			$html = $this->renderView('admin/formulaires/add_type_tournoi.html.twig', [
                    'action'=>$action,
                    'typeTournoi'=>$typeTournoi,
                    'url'=> $this->generateUrl('admin_add_type_tournoi', ['id'=>$id], UrlGenerator::ABSOLUTE_URL)
                ]);
            	$response = new Response(json_encode($html));
	            $response->headers->set('Content-Type', 'application/json');

	            return $response;
    		}
        }
        return  new Response("passer par une requette ajax");
    }
}
