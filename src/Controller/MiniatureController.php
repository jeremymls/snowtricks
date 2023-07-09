<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/miniature")
 */
class MiniatureController extends AbstractController
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
     * @Route({
     *     "fr": "/changer/{slug}",
     *     "en": "/change/{slug}"
     * }, name="app_change_miniature")
     */
    public function changeMiniature(Trick $trick, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

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

        if ($form->isSubmitted()) {
            $this->em->persist($trick);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Miniature changed'));

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
    * @Route("/delete/{slug}", name="app_delete_miniature", methods={"POST"})
    */
    public function deleteMiniature(Trick $trick, Request $request): Response
    {
        if ($this->isCsrfTokenValid('deleteTrick' . $trick->getId(), $request->request->get('_token'))) {
            $trick = $this->em->getRepository(Trick::class)->find($request->request->get('id'));
            if ($trick) {
                $trick->setMiniature(null);
                $this->em->persist($trick);
                $this->em->flush();
                $this->addFlash('success', $this->translator->trans('Miniature deleted'));
            } else {
                $this->addFlash('danger', $this->translator->trans('Trick not found'));
            }
        } else {
            $this->addFlash('danger', $this->translator->trans('Invalid token'));

            return $this->redirect($request->headers->get('referer'));
        }
        return $this->redirectToRoute('app_edit_trick', [
            'slug' => $trick->getSlug()
        ]);
    }
}
