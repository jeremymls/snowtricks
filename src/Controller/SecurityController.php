<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\PasswordFormType;
use App\Form\UserType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash('warning', 'Vous êtes déjà connecté·e, en tant que ' . $this->getUser() . '.
            Si vous souhaitez changer de compte, veuillez vous déconnecter.');
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile(Request $request, EntityManagerInterface $em, PictureService $pictureService): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté·e pour accéder à votre profil.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $image = $form->get('image')->getData();
                if ($image) {
                    $folder = 'users';
                    $file = $pictureService->add($image, $folder);
                    $img = new Image();
                    $img->setName($file);
                    $user->setImage($img);
                }

                $em->flush();
                $this->addFlash('success', 'Votre profil a bien été mis à jour.');
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', 'Votre profil n\'a pas pu être mis à jour.');
        }

        return $this->render('security/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/change-password", name="app_change_password")
     */
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté·e pour modifier votre mot de passe.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(PasswordFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$userPasswordHasher->isPasswordValid($user, $form->get('actualPassword')->getData())) {
                    $this->addFlash('danger', 'Ce mot de passe ne correspond pas à votre mot de passe actuel.');
                    return $this->redirectToRoute('app_change_password');
                }
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                $em->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', 'Votre mot de passe n\'a pas pu être mis à jour.');
        }

        return $this->render('security/edit_pass.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
