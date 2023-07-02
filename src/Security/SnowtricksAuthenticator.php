<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class SnowtricksAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private UserRepository $userRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, UserRepository $userRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $this->userRepository->findOneBy(['username' => $request->request->get('username')]);
        if ($user && $user->getDeletedAt() != null) {
            $request->getSession()->remove(Security::LAST_USERNAME);

            $request->getSession()->getFlashBag()->add(
                'danger',
                $this->translator->trans(
                    'Your account has been deleted. If you want to reactivate it, please contact the administrator.'
                )
            );
            return new RedirectResponse($this->urlGenerator->generate('app_logout'));
        }
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        $request->getSession()->getFlashBag()->add('success', $this->translator->trans('You are connected.'));
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $url = $this->getLoginUrl($request);
        $request->getSession()->getFlashBag()->add('warning', $this->translator->trans('You must be logged in to access this page.'));
        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
