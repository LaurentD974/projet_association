<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class CookieManager
{
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function hasConsent(): bool
    {
        return $this->request->cookies->get('cookie_consent') === 'true';
    }

    public function addConsentCookie(Response $response): void
    {
        $cookie = new Cookie('cookie_consent', 'true', strtotime('+1 year'), '/', null, true, false);
        $response->headers->setCookie($cookie);
    }
}