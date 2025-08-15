<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_form')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

 #[Route('/contact/send', name: 'contact_send', methods: ['POST'])]
public function send(Request $request, MailerInterface $mailer, CsrfTokenManagerInterface $csrfTokenManager): Response
{
    $nom = $request->request->get('nom');
    $email = $request->request->get('email');
    $message = $request->request->get('message');
    $token = $request->request->get('_token');

    if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact_form', $token))) {
        throw new AccessDeniedHttpException('Jeton CSRF invalide.');
    }

    // Validation minimale
    if (empty($nom) || empty($email) || empty($message)) {
        $this->addFlash('error', 'Tous les champs sont obligatoires.');
        return $this->redirectToRoute('contact_form');
    }

    // Construction du message
    $emailMessage = (new Email())
        ->from('aocdtf.lareunion.974@gmail.com')
        ->to('aocdtf.lareunion.974@gmail.com')
        ->replyTo($email)
        ->subject('Message via formulaire de contact')
        ->text("Nom: $nom\nEmail: $email\nMessage:\n$message");

    try {
        $mailer->send($emailMessage);
    } catch (\Throwable $e) {
        return new Response('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage(), 500);
    }

    $this->addFlash('success', 'Votre message a bien été envoyé.');
    return $this->redirectToRoute('contact_success');
}

    #[Route('/contact/success', name: 'contact_success')]
    public function success(): Response
    {
        return $this->render('contact/success.html.twig');
    }
}
