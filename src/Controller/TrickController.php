<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\GroupType;
use App\Form\TrickType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/trick")
 */
class TrickController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/show/{id}", name="app_show_trick")
     */
    public function show(Trick $trick): Response
    {
        return $this->render('trick/details.html.twig', [
            'trick' => $trick,
            'title' => $trick->getName()
        ]);
    }

    /**
     * @Route("/add", name="app_add_trick")
     */
    public function add(Request $request, PictureService $pictureService): Response
    {
        $trick = new Trick();
        $trick->setName('Nouvelle figure');
        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get images
            $images = $form->get('images')->getData();

            foreach ($images as $key => $image) {
                $folder = 'tricks';

                $file = $pictureService->add($image, $folder);

                $img = new Image();
                $img->setName($file);
                if ($key === 0) {
                    $trick->setMiniature($file);
                }
                $trick->addImage($img);
            }

            // set user
            $trick->setUser($this->getUser());

            $this->em->persist($trick);
            $this->em->flush();

            return $this->redirectToRoute('app_show_trick', [
                'id' => $trick->getId()
            ]);
        }

        return $this->render('trick/details.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'title' => 'Nouvelle figure',
            'action' => 'add',
            'groupForm' => $groupForm->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_edit_trick")
     */
    public function edit(Trick $trick, Request $request, PictureService $pictureService): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();
            // get images
            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                $folder = 'tricks';

                $file = $pictureService->add($image, $folder);

                $img = new Image();
                $img->setName($file);
                $trick->addImage($img);
            }

            // set user
            $trick->setUser($this->getUser());

            $this->em->persist($trick);
            $this->em->flush();

            if ($request->request->get('_action') === 'change_mini') {
                return $this->redirectToRoute('app_change_miniature', [
                    'id' => $trick->getId()
                ]);
            }

            return $this->redirectToRoute('app_show_trick', [
                'id' => $trick->getId()
            ]);
        }
        return $this->render('trick/details.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'title' => $trick->getName(),
            'action' => 'edit',
            'groupForm' => $groupForm->createView()
        ]);
    }

    /**
     * @Route("/change/miniature/{id}", name="app_change_miniature")
     */
    public function changeMiniature(Trick $trick, Request $request): Response
    {
        $form = $this->createFormBuilder($trick)
            ->add('miniature', TextType::class, [
                'label' => 'Miniature',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($trick);
            $this->em->flush();

            return $this->redirectToRoute('app_edit_trick', [
                'id' => $trick->getId()
            ]);
        }

        return $this->render('trick/mini.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'title' => 'Choisir la miniature de ' . $trick->getName()
        ]);
    }

    /**
     * @Route("/delete/image/{id}", name="app_delete_trick_image", methods={"DELETE"})
     */
    public function deleteImage(Image $image, Request $request, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            $name = $image->getName();

            if ($pictureService->delete($name, 'tricks')) {
                $this->em->remove($image);
                $this->em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Image non supprimée'], 400);
        }
        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }

    /**
     * @Route("/delete/video/{id}", name="app_delete_trick_video", methods={"DELETE"})
     */
    public function deleteVideo(Video $video, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $data['_token'])) {
            $this->em->remove($video);
            // $this->em->flush();
            return new JsonResponse(['success' => true], 200);
        }
        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }
}
