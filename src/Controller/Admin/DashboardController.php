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
        $request = $this->get('request_stack')->getCurrentRequest();
        $requestedPeriod = $request->query->get('period', 'month');
        $requestedDateLimit = $request->query->get('dateLimit', '-1 year');
        $dateLimitation = new \DateTime($requestedDateLimit);
        $dateLimit = new \DateTime($requestedDateLimit);
        $dates = [];
        $period = [];
        $usersCount = [];
        $tricksCount = [];
        $commentsCount = [];


        switch ($requestedPeriod) {
            case 'year':
                $int = new \DateInterval('P1Y');
                $formatDate = 'Y';
                break;
            case 'month':
                $int = new \DateInterval('P1M');
                $formatDate = 'Y-m';
                break;
            case 'day':
            default:
                $int = new \DateInterval('P1D');
                $formatDate = 'Y-m-d';
                break;
        }
        for ($dateLimit; $dateLimitation <= new \DateTime(); $dateLimitation->add($int)) {
            $period[$dateLimit->format($formatDate)] = [];
        }

        // users
        $usersRepo = $this->em->getRepository(User::class);
        $users = $usersRepo->findAll();
        $usersByPeriod = $usersRepo->countByPeriod($requestedPeriod, $dateLimit);
        foreach ($usersByPeriod as $user) {
            $period[$user['period']]['user'] = $user['count'];
        }

        // tricks
        $tricksRepo = $this->em->getRepository(Trick::class);
        $tricks = $tricksRepo->findAll();
        $tricksByPeriod = $tricksRepo->countByPeriod($requestedPeriod, $dateLimit);
        foreach ($tricksByPeriod as $trick) {
            $period[$trick['period']]['trick'] = $trick['count'];
        }

        // comments
        $commentsRepo = $this->em->getRepository(Comment::class);
        $comments = $commentsRepo->findAll();
        $commentsByPeriod = $commentsRepo->countByPeriod($requestedPeriod, $dateLimit);
        foreach ($commentsByPeriod as $comment) {
            $period[$comment['period']]['comment'] = $comment['count'];
        }
        uksort($period, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });
        foreach ($period as $key => $value) {
            $dates[] = $key;
            $usersCount[] = $value['user'] ?? 0;
            $tricksCount[] = $value['trick'] ?? 0;
            $commentsCount[] = $value['comment'] ?? 0;
        }
        // Groups
        $groups = $this->em->getRepository(Group::class)->findAll();
        $groupsStats = [
            'labels' => [],
            'counts' => [],
            'colors' => [],
        ];
        foreach ($groups as $group) {
            $groupsStats['labels'][] = $group->getName();
            $groupsStats['counts'][] = count($group->getTricks());
            $groupsStats['colors'][] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }
        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'tricks' => $tricks,
            'comments' => $comments,
            'dates' => json_encode($dates),
            'usersCount' => json_encode($usersCount),
            'tricksCount' => json_encode($tricksCount),
            'commentsCount' => json_encode($commentsCount),
            'requestedPeriod' => $requestedPeriod,
            'groups' => $groups,
            'groupsStats' => $groupsStats,
            'requestedDateLimit' => $requestedDateLimit,
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
        yield MenuItem::linkToDashboard('Back-office', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Figures', 'fas fa-snowboarding', Trick::class);
        yield MenuItem::linkToCrud('Group', 'fas fa-list', Group::class);
        yield MenuItem::linkToCrud('Commentaires', 'fas fa-comments', Comment::class);
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
