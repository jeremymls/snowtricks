<?php

namespace App\Controller;

use App\Entity\Image;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImageController extends AbstractController
{
    private $em;
    private $slugger;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->translator = $translator;
    }

    /**
     * @Route("/image/delete/{id}", name="app_delete_trick_image", methods={"DELETE"})
     */
    public function deleteImage(Image $image, Request $request, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            $name = $image->getName();
            $pictureService->delete($name, 'tricks');
            $this->em->remove($image);
            $this->em->flush();

            return new JsonResponse(['success' => true], 200);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
