<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Parameters;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ParametersController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    /**
     * @Route("/parameters", name="admin_parameters")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createFormBuilder()
            ->add('tricksPerPage', ChoiceType::class, [
                'label' => 'Nombre de figures par page',
                'data' => $this->em->getRepository(Parameters::class)->findOneBy(['name' => 'tricksPerPage'])->getValue(),
                'choices' => [
                    '5' => 5,
                    '10' => 10,
                    '15' => 15,
                    '20' => 20,
                ],
            ])
            ->add('commentsPerPage', ChoiceType::class, [
                'label' => 'Nombre de commentaires par page',
                'data' => $this->em->getRepository(Parameters::class)->findOneBy(['name' => 'commentsPerPage'])->getValue(),
                'choices' => [
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                    '10' => 10,
                    '20' => 20,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tricksPerPage = $form->get('tricksPerPage')->getData();
            $commentsPerPage = $form->get('commentsPerPage')->getData();
            $tricksPerPageParameter = $this->em->getRepository(Parameters::class)->findOneBy(['name' => 'tricksPerPage']);
            $commentsPerPageParameter = $this->em->getRepository(Parameters::class)->findOneBy(['name' => 'commentsPerPage']);
            $tricksPerPageParameter->setValue($tricksPerPage);
            $commentsPerPageParameter->setValue($commentsPerPage);
            $this->em->persist($tricksPerPageParameter);
            $this->em->persist($commentsPerPageParameter);
            $this->em->flush();
            $this->addFlash('success', 'Les paramètres ont bien été enregistrés');

            return $this->redirect($request->getUri());
        }

        return $this->render('admin/parameters.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
