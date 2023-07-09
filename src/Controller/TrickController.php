<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\GroupType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "fr": "/figure",
 *     "en": "/trick"
 * })
 */
class TrickController extends AbstractController
{
    private $em;
    private $slugger;
    private $translator;

    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->translator = $translator;
    }

    /**
     * @Route("/page/{slug}", name="app_show_trick")
     */
    public function show(Trick $trick, CommentRepository $commentRepository): Response
    {
        $paginator = $commentRepository->getCommentPaginator($trick, 0);

        $comment = new Comment();
        $comment->setTrick($trick);
        $commentForm = $this->createForm(CommentType::class, $comment);

        return $this->render('trick/details.html.twig', [
            'trick' => $trick,
            'title' => $trick->getName(),
            'comments' => $paginator,
            'next' => min(count($paginator), $commentRepository->getCommentPerPage()),
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * @Route({
     *     "fr": "/ajouter",
     *     "en": "/add"
     * }, name="app_add_trick")
     */
    public function add(Request $request, PictureService $pictureService, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trick = new Trick();
        $trick->setName($this->translator->trans('New trick'));
        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);
        $videoError = false;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // validation des vidéos
            foreach ($form->get('videos') as $video) {
                if (count($validator->validate($video)) > 0) {
                    $this->addFlash('danger', $this->translator->trans('Check videos'));
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

                $this->addFlash('success', $this->translator->trans('Trick added'));

                return $this->redirectToRoute('app_home', [
                    '_fragment' => 'main'
                ]);
            }
        }

        return $this->render('trick/details.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'title' => $this->translator->trans('New trick'),
            'action' => 'add',
            'groupForm' => $groupForm->createView(),
            'videoError' => $videoError
        ]);
    }

    /**
     * @Route({
     *     "fr": "/modifier/{slug}",
     *     "en": "/edit/{slug}"
     * }, name="app_edit_trick")
     */
    public function edit(Trick $trick, Request $request, PictureService $pictureService, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(TrickType::class, $trick);
        $group = new Group();
        $groupForm = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        // validation des vidéos
        $videoError = false;
        foreach ($form->get('videos') as $video) {
            if (count($validator->validate($video)) > 0) {
                $this->addFlash('danger', $this->translator->trans('Check videos'));
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

            $this->addFlash('success', $this->translator->trans('Trick edited'));

            if ($request->request->get('_action') === 'change_mini') {
                return $this->redirectToRoute('app_change_miniature', [
                    'id' => $trick->getId()
                ]);
            }

            return $this->redirectToRoute('app_home', [
                '_fragment' => 'main'
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
        if ($this->isCsrfTokenValid('deleteTrick'. $request->request->get('id') , $request->request->get('_token'))) {
            $trick = $this->em->getRepository(Trick::class)->find($request->request->get('id'));
            if ($trick) {
                $trick->setDeletedAt(new \DateTime());
                $this->em->persist($trick);
                $this->em->flush();

                $this->addFlash('success', $this->translator->trans('Trick deleted'));
            } else {
                $this->addFlash('danger', $this->translator->trans('Trick not found'));
            }
        } else {
            $this->addFlash('danger', $this->translator->trans('Invalid token'));

            return $this->redirect($request->headers->get('referer'));
        }
        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/get/{offset}", name="app_get_tricks", methods={"GET"})
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
                'miniature' => $trick->getMiniature(),
                'url' => $this->generateUrl('app_show_trick', [
                    'slug' => $trick->getSlug()
                ]),
                'urlEdit' => $this->generateUrl('app_edit_trick', [
                    'slug' => $trick->getSlug()
                ]),
            ];
        }

        $url = $this->generateUrl('app_get_tricks', [
            'offset' => $offset + $trickRepository->getTrickPerPage()
        ]);
        $next = min(count($paginator), $offset + $trickRepository->getTrickPerPage());
        $action = $next < count($paginator) ? $url : null;

        return new JsonResponse([
            'tricks' => $tricks,
            'action' => $action

        ]);
    }
}
