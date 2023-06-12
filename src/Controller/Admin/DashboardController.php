<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/dashboard.html.twig', [
            'users' => $this->em->getRepository(User::class)->count([]),
            'tricks' => $this->em->getRepository(Trick::class)->count([]),
            'groups' => $this->em->getRepository(Group::class)->count([]),
            'comments' => $this->em->getRepository(Comment::class)->count([]),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Back office - Snow Tricks');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-rotate-left', 'app_home');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Commentaires', 'fas fa-comments', Comment::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
        ->setName($user->getFullName())
        ->displayUserAvatar(false)
        ->addMenuItems([
            MenuItem::linkToRoute('Mon profil', 'fas fa-user', 'app_profile'),
        ]);
    }
}
