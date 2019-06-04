<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'lolo le grand!!!',
            'title' => 'Bienvenue dans ce blog',
			'age' => '31',
			'tableau' =>['lolo','riri','fifi'],
        ]);
    }
}
