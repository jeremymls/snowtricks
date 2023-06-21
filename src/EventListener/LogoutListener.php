<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogoutListener
{
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;

    public function __construct(URLGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }
    public function __invoke(LogoutEvent $event): void
    {
        $referer = $event->getRequest()->headers->get('referer');
        if (
            $referer == $this->urlGenerator->generate('app_profile', [], UrlGeneratorInterface::ABSOLUTE_URL) ||
            $referer == $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'danger',
                $this->translator->trans(
                    'Your account has been deleted. If you want to reactivate it, please contact the administrator.'
                )
            );
            if ($referer == $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL)) {
                $event->setResponse(new RedirectResponse($referer));
            }
        } else {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'danger',
                'You are logged out.'
            );
        }
    }

}
