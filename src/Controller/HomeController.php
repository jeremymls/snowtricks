<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $tricks = $em->getRepository(Trick::class)->findBy(['deletedAt' => null], ['createdAt' => 'DESC'], 10);
        return $this->render('home.html.twig', [
            'tricks' => $tricks
        ]);
    }
}
