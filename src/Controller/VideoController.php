<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Video;
use App\Form\GroupType;
use App\Form\VideoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/add/video", name="app_add_video")
     */
    public function add(Request $request): Response
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $video = $form->getData();
            $this->em->persist($video);
            $this->em->flush();

            return $this->redirectToRoute('app_home');
        }
        return $this->render('group/add.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
