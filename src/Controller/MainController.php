<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/change-locale/{locale}", name="change_locale")
     */
    public function changeLocale($locale, Request $request, TranslatorInterface $translator) : Response
    {
        // On stocke la langue demandée dans la session
        $request->getSession()->set('_locale', $locale);

        $this->addFlash('success', $translator->trans('Language changed successfully', [], null, $locale));

        // On revient sur la page précédente
        return $this->redirect($request->headers->get('referer'));
    }
}
