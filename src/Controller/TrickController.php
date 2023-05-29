<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\GroupType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function show(Trick $trick, Request $request, CommentRepository $commentRepository): Response
    {
        $paginator = $commentRepository->getCommentPaginator($trick, 0);

        $comment = new Comment();
        $comment->setTrick($trick);
        $comment->setUser($this->getUser());
        $commentForm = $this->createForm(CommentType::class, $comment);

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->em->persist($comment);
            $this->em->flush();

            return $this->redirectToRoute('app_show_trick', [
                'slug' => $trick->getSlug(),
                '_fragment' => 'comments'
            ]);
        }

        return $this->render('trick/details.html.twig', [
            'trick' => $trick,
            'title' => $trick->getName(),
            'comments' => $paginator,
            'next' => min(count($paginator), CommentRepository::PAGINATOR_PER_PAGE),
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * @Route("/add", name="app_add_trick")
     */
    public function add(Request $request, PictureService $pictureService, ValidatorInterface $validator): Response
    {
        $trick = new Trick();
        $trick->setName('Nouvelle figure');
        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);
        $videoError = false;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // validation des vidéos
            foreach ($form->get('videos') as $video) {
                if (count($validator->validate($video)) > 0) {
                    $this->addFlash('danger', 'Vérifiez les vidéos');
                    $videoError = true;
                }
            }
            foreach ($validator->validate($trick) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }

            if ($form->isValid()) {
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
        }

        return $this->render('trick/details.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'title' => 'Nouvelle figure',
            'action' => 'add',
            'groupForm' => $groupForm->createView(),
            'videoError' => $videoError
        ]);
    }

    /**
     * @Route("/edit/{slug}", name="app_edit_trick")
     */
    public function edit(Trick $trick, Request $request, PictureService $pictureService, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        // validation des vidéos
        $videoError = false;
        foreach ($form->get('videos') as $video) {
            if (count($validator->validate($video)) > 0) {
                $this->addFlash('danger', 'Vérifiez les vidéos');
                $videoError = true;
            }
        }

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
            'groupForm' => $groupForm->createView(),
            'videoError' => $videoError
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
     * @Route("/delete/video", name="app_delete_trick_video", methods={"DELETE"})
     */
    public function deleteVideo(Request $request): JsonResponse
    {
        $video = $this->em->getRepository(Video::class)->findOneBy([
            'video_id' => $request->get('id'),
            'trick' => $request->get('trick')
        ]);
        if (!$video) {
            return new JsonResponse(['error' => 'Video non trouvée'], 400);
        }
        if ($this->isCsrfTokenValid('deleteVideo' . $video->getTrick()->getId(), $request->get('_token'))) {
            $video->getTrick()->removeVideo($video);
            $this->em->flush();
            return new JsonResponse(['success' => true], 200);
        }
        return new JsonResponse(['error' => 'Token Invalide'], 403);
    }

    /**
     * @Route("/get/comments/{offset}/{slug}", name="app_get_comments", methods={"GET"})
     */
    public function getComments(int $offset, Trick $trick, CommentRepository $commentRepository): JsonResponse
    {
        $paginator = $commentRepository->getCommentPaginator($trick, $offset);
        $comments = [];
        foreach ($paginator as $comment) {
            $src = $comment->getUser()->getImage() ?
            '/uploads/images/users/' . $comment->getUser()->getImage() :
            '/core/img/default-user.png';
            $comments[] = [
                'id' => $comment->getId(),
                'text' => $comment->getText(),
                'createdAt' => $comment->getCreatedAt()->format('d/m/Y à H:i'),
                'user' => $comment->getUser()->getUsername(),
                'src' => $src
            ];
        }

        $url = $this->generateUrl('app_get_comments', [
            'offset' => $offset + CommentRepository::PAGINATOR_PER_PAGE,
            'slug' => $trick->getSlug()
        ]);
        $next = min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE);
        $action = $next < count($paginator) ? $url : null;

        return new JsonResponse([
            'comments' => $comments,
            'action' => $action

        ]);
    }

    /**
     * @Route("/get/tricks/{offset}", name="app_get_tricks", methods={"GET"})
     */
    public function getTricks(int $offset, TrickRepository $trickRepository): JsonResponse
    {
        $paginator = $trickRepository->getTrickPaginator($offset);
        $tricks = [];
        foreach ($paginator as $trick) {
            $tricks[] = [
                'id' => $trick->getId(),
                'name' => $trick->getName(),
                'slug' => $trick->getSlug(),
                'url' => $this->generateUrl('app_show_trick', [
                    'slug' => $trick->getSlug()
                ]),
                'urlEdit' => $this->generateUrl('app_edit_trick', [
                    'slug' => $trick->getSlug()
                ]),
            ];
        }

        $url = $this->generateUrl('app_get_tricks', [
            'offset' => $offset + TrickRepository::PAGINATOR_PER_PAGE
        ]);
        $next = min(count($paginator), $offset + TrickRepository::PAGINATOR_PER_PAGE);
        $action = $next < count($paginator) ? $url : null;

        return new JsonResponse([
            'tricks' => $tricks,
            'action' => $action

        ]);
    }

}
