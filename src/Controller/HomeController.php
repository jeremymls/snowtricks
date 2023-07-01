<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->getTrickPaginator(0);

        return $this->render('home.html.twig', [
            'tricks' => $tricks,
            'next' => min(count($tricks), $trickRepository->getTrickPerPage()),
        ]);
    }
}
