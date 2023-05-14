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
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/trick")
 */
class TrickController extends AbstractController
{
    private $em;
    private $slugger;

    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->em = $em;
        $this->slugger = $slugger;
    }

    /**
     * @Route("/show/{slug}", name="app_show_trick")
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
            // set slug
            $trick->setSlug($this->slugger->slug($trick->getName())->lower());
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
                'slug' => $trick->getSlug()
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
     * @Route("/edit/{slug}", name="app_edit_trick")
     */
    public function edit(Trick $trick, Request $request, PictureService $pictureService): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->getData();
            // set slug
            $trick->setSlug($this->slugger->slug($trick->getName())->lower());
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
                'slug' => $trick->getSlug()
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
    * @Route("/delete", name="app_delete_trick", methods={"POST"})
    */
    public function delete(Request $request): Response
    {
        if ($this->isCsrfTokenValid('deleteTrick', $request->request->get('_token'))) {
            $trick = $this->em->getRepository(Trick::class)->find($request->request->get('id'));
            if ($trick) {
                $trick->setDeletedAt(new \DateTime());
                $this->em->persist($trick);
                $this->em->flush();

                $this->addFlash('success', 'La figure a bien été supprimée');
            } else {
                $this->addFlash('danger', 'La figure n\'a pas été trouvée');
            }
        } else {
            $this->addFlash('danger', 'Token invalide');

            return $this->redirect($request->headers->get('referer'));
        }
        return $this->redirectToRoute('app_home');
    }

    /**
    * @Route("/delete/miniature/{id}", name="app_delete_miniature", methods={"POST"})
    */
    public function deleteMiniature(Trick $trick, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $trick->setMiniature(null);
            $this->em->persist($trick);
            $this->em->flush();
        }
        return $this->redirectToRoute('app_edit_trick', [
            'slug' => $trick->getSlug()
        ]);
    }

    /**
     * @Route("/change/miniature/{slug}", name="app_change_miniature")
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
                'slug' => $trick->getSlug()
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
