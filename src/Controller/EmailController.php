<?php

// src/Controller/EmailController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Part\TextPart;
use App\Form\EmailType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class EmailController extends AbstractController
{
    #[Route('/envoyer-email', name: 'envoyer-email')]
    public function sendEmail(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $em
    ) {
        $currentUser = $this->getUser();

        // Récupérer les destinataires ayant le même métier, sauf l'expéditeur
        $receivers = $em->getRepository(User::class)->findBy([
            'metier' => $currentUser->getMetier()
        ]);

        $filteredReceivers = array_filter($receivers, function ($user) use ($currentUser) {
            return $user->getEmail() !== $currentUser->getEmail();
        });

        $receiverEmails = array_map(fn($user) => $user->getEmail(), $filteredReceivers);

        // Créer le formulaire avec les données supplémentaires
        $form = $this->createForm(EmailType::class, null, [
            'expediteur_email' => $currentUser->getEmail(),
            'destinataires_emails' => implode(', ', $receiverEmails),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->get('message')->getData();
            $expediteurEmail = $currentUser->getEmail();
            $nomComplet = $currentUser->getPrenom() . ' ' . $currentUser->getNom();

            // Signature HTML multilingue
            $signatureHtml = <<<HTML
<br><br>
<hr>
<p style="font-size: 0.9em; color: #555;">
    Cet email a été envoyé par <strong>{$nomComplet}</strong> (<a href="mailto:{$expediteurEmail}">{$expediteurEmail}</a>).<br>
    Si vous souhaitez lui répondre, cliquez sur le lien ci-dessus.
</p>
HTML;

            $messageAvecSignature = nl2br($message) . $signatureHtml;

            foreach ($filteredReceivers as $receiver) {
                $email = (new Email())
                    ->from($expediteurEmail)
                    ->to($receiver->getEmail())
                    ->subject('Message de votre maître de métier')
                    ->html($messageAvecSignature);

                $mailer->send($email);
            }

            $this->addFlash('success', 'Email envoyé à votre corporation !');
            return $this->redirectToRoute('envoyer-email');
        }

        return $this->render('email/send.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}