<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_form')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contact/send', name: 'contact_send', methods: ['POST'])]
    public function send(Request $request, MailerInterface $mailer): Response
    {
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');
        $message = $request->request->get('message');

        // Validation minimale
        if (empty($nom) || empty($email) || empty($message)) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('contact_form');
        }

        // Construction du message
        $emailMessage = (new Email())
            ->from('u8759513205@gmail.com') // doit correspondre à ton MAILER_DSN
            ->to('u8759513205@gmail.com') // destinataire réel
            ->replyTo($email) // permet de répondre à l’expéditeur utilisateur
            ->subject('Message via formulaire de contact')
            ->text("Nom: $nom\nEmail: $email\nMessage:\n$message");

        // Envoi avec gestion des erreurs
        try {
            $mailer->send($emailMessage);
        } catch (\Throwable $e) {
            // Affiche un message d'erreur (ou loggue-le en production)
            return new Response('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage(), 500);
        }

        return $this->redirectToRoute('contact_success');
    }

    #[Route('/contact/success', name: 'contact_success')]
    public function success(): Response
    {
        return $this->render('contact/success.html.twig');
    }
}
