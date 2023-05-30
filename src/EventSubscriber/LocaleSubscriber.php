<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;
    public function __construct(string $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        // On essaie de voir si la locale a été fixée dans le paramètre de routing _locale
        if ($locale = $request->attributes->get('_locale')) {
            $request->setLocale($locale);
        } else {
            // Si aucune locale n'a été fixée explicitement dans la requête, on utilise celle de la session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]]
        ];
    }
}
