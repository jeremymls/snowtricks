<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route({
 *     "fr": "/commentaire",
 *     "en": "/comment"
 * })
 */
class CommentController extends AbstractController
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
     * @Route("/add", name="app_add_comment", methods={"POST"})
     */
    public function addComment(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $res = $request->request->get('comment');
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['slug' => $res['trick']]);
        $res['trick'] = $trick;
        $request->request->set('comment', $res);

        $commentForm = $this->createForm(CommentType::class);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
            $comment->setUser($this->getUser());
            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Comment added'));
        } else {
            $this->addFlash('error', $this->translator->trans('An error occurred'));
        }

        return $this->redirectToRoute('app_show_trick', [
            'slug' => $trick->getSlug(),
            '_fragment' => 'comments'
        ]);
    }


    /**
     * @Route("/get/{offset}/{slug}", name="app_get_comments", methods={"GET"})
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
                'createdAt' => $comment->getCreatedAt()->format('d/m/Y') . ' ' . $this->translator->trans('at') . ' ' . $comment->getCreatedAt()->format('H:i'),
                'user' => $comment->getUser()->getFullName(),
                'src' => $src
            ];
        }

        $url = $this->generateUrl('app_get_comments', [
            'offset' => $offset + $commentRepository->getCommentPerPage(),
            'slug' => $trick->getSlug()
        ]);
        $next = min(count($paginator), $offset + $commentRepository->getCommentPerPage());
        $action = $next < count($paginator) ? $url : null;

        return new JsonResponse([
            'comments' => $comments,
            'action' => $action

        ]);
    }

    /**
     * @Route("/delete", name="app_delete_comment", methods={"DELETE"})
     */
    public function deleteComment(Request $request): JsonResponse
    {
        $comment = $this->em->getRepository(Comment::class)->find($request->get('id'));

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('deleteComment' . $comment->getTrick()->getId(), $request->get('_token'))) {
            $comment->setDeletedAt(new \DateTime());
            $this->em->persist($comment);
            $this->em->flush();

            return new JsonResponse(['success' => true], 200);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 403);
        }
    }

    /**
     * @Route("/restore", name="app_restore_comment", methods={"POST"})
     */
    public function restoreComment(Request $request): JsonResponse
    {
        $comment = $this->em->getRepository(Comment::class)->find($request->request->get('id'));

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('restoreComment' . $comment->getTrick()->getId(), $request->request->get('_token'))) {
            $comment->setDeletedAt(null);
            $this->em->persist($comment);
            $this->em->flush();

            return new JsonResponse(['success' => true], 200);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 403);
        }
    }
}
