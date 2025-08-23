<?php


namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class TestSecurityController
{
    #[Route('/test-security')]
    public function test(SecurityBundleSecurity $security): Response
    {
        return new Response('Security service works');
    }
}