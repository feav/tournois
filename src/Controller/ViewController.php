<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ViewController extends AbstractController
{
    /**
     * @Route("/", name="client_homepage")
     */
    public function index()
    {
        return $this->render('website/index.html.twig', []);
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
