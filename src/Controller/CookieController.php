<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CookieController extends AbstractController
{
    #[Route('/accept-cookies', name: 'accept_cookies', methods: ['POST'])]
    public function acceptCookies(): Response
    {
        $response = new Response();
        $cookie = Cookie::create('cookie_consent')
            ->withValue('true')
            ->withExpires(strtotime('+1 year'))
            ->withPath('/')
            ->withSecure(true)
            ->withHttpOnly(false);

        $response->headers->setCookie($cookie);
        return $response;
    }
}