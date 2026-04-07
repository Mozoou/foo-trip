<?php

namespace App\Controller;

use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(DestinationRepository $repository): Response
    {
        $destinations = $repository->findAll();

        return $this->render('home/index.html.twig', [
            'destinations' => $destinations,
        ]);
    }
}
