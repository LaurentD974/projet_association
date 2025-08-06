<?php

// src/Controller/TestSecurityController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class TestSecurityController
{
    #[Route('/test-security')]
    public function test(Security $security): Response
    {
        return new Response('Security service works');
    }
}