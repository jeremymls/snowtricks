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
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route({
     *   "fr": "/connexion",
     *   "en": "/login"
     * }, name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        if ($this->getUser()) {
            $this->addFlash(
                'warning',
                $translator->trans('You are already logged in. If you want too change your account, please log out.')
            );
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
     * @Route({
     *  "fr": "/mon-profil",
     *  "en": "/my-profile"
     * }, name="app_profile")
     */
    public function profile(Request $request, EntityManagerInterface $em, PictureService $pictureService, TranslatorInterface $translator): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', $translator->trans('You are not logged in. Please log in to access your profile.'));
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
                $this->addFlash('success', $translator->trans('Your profile has been updated.'));
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', $translator->trans('Your profile could not be updated.'));
        }

        return $this->render('security/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route({
     * "fr": "/changer/mot-de-passe",
     * "en": "/change/password"
     * }, name="app_change_password")
     */
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', $translator->trans('You must be logged in to change your password.'));
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(PasswordFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$userPasswordHasher->isPasswordValid($user, $form->get('actualPassword')->getData())) {
                    $this->addFlash('danger', $translator->trans('This password does not match your current password.'));
                    return $this->redirectToRoute('app_change_password');
                }
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                $em->flush();
                $this->addFlash('success', $translator->trans('Your password has been updated.'));
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', $translator->trans('Your password could not be updated.'));
        }

        return $this->render('security/edit_pass.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("delete/profile-image", name="app_delete_profile_image")
     */
    public function deleteProfileImage(
        EntityManagerInterface $em,
        PictureService $pictureService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $image = $user->getImage();
        if ($image) {
            $pictureService->delete($image->getName(), 'users');
            $user->setImage(null);
            $em->flush();
        }

        return $this->redirectToRoute('app_profile');
    }

}
