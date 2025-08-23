<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CookieController extends AbstractController
{
    #[Route('/accept-cookies', name: 'accept_cookies', methods: ['POST'])]
    public function acceptCookies(): RedirectResponse
    {
        $response = $this->redirectToRoute('home'); // Remplace 'home' par ta route d’accueil si besoin

        $cookie = Cookie::create('cookie_consent')
            ->withValue('true')
            ->withExpires(strtotime('+1 year'))
            ->withPath('/')
            ->withSecure(false) // ⚠️ Mets true en production (HTTPS)
            ->withHttpOnly(false);

        $response->headers->setCookie($cookie);
        return $response;
    }

    #[Route('/refuse-cookies', name: 'refuse_cookies', methods: ['POST'])]
    public function refuseCookies(): RedirectResponse
    {
        $response = $this->redirectToRoute('home');

        $cookie = Cookie::create('cookie_consent')
            ->withValue('false')
            ->withExpires(strtotime('+1 year'))
            ->withPath('/')
            ->withSecure(false)
            ->withHttpOnly(false);

        $response->headers->setCookie($cookie);
        return $response;
    }

    #[Route('/reset-cookies', name: 'reset_cookies', methods: ['POST'])]
    public function resetCookies(Request $request): RedirectResponse
    {
        $response = $this->redirectToRoute('home');

        // Supprime le cookie en le réécrivant avec une date expirée
        $response->headers->clearCookie('cookie_consent');

        return $response;
    }
}