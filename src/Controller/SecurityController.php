<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        return $this->render('security/forgot_password.html.twig');
    }
    #[Route('/redirect', name: 'role_redirect')]
public function redirectAfterLogin(): Response
{
    $user = $this->getUser();

    if ($this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('admin_dashboard');
    }

    if ($this->isGranted('ROLE_GACHEUR')) {
        return $this->redirectToRoute('gacheur_dashboard');
    }

    if ($this->isGranted('ROLE_USER')) {
        return $this->redirectToRoute('search_page');
    }

    // Fallback
    return $this->redirectToRoute('app_login');
}
}