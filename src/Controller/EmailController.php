<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SendEmailType; // ⬅️ utilise le FormType proposé (EntityType, expanded=true)
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailController extends AbstractController
{
    #[Route('/envoyer-email', name: 'envoyer-email')]
    public function sendEmail(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $em
    ) {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        // Récupère les destinataires de la même corporation/métier (hors expéditeur, email non vide)
        $receivers = $em->getRepository(User::class)->findBy([
            'metier' => $currentUser->getMetier(),
        ]);

        $filteredReceivers = array_values(array_filter(
            $receivers,
            static function (?User $u) use ($currentUser): bool {
                if (!$u instanceof User) return false;
                if (!$u->getEmail()) return false;
                return \strtolower($u->getEmail()) !== \strtolower($currentUser->getEmail());
            }
        ));

        // Formulaire : cases à cocher (cochées par défaut via SendEmailType::POST_SET_DATA)
        $form = $this->createForm(SendEmailType::class, null, [
            'expediteur_email'      => (string) $currentUser->getEmail(),
            'destinataires_choices' => $filteredReceivers, // tableau d'objets User
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var iterable<User> $selected */
            $selected = $form->get('destinataires')->getData(); // sélection finale (après décoches)
            $message  = (string) $form->get('message')->getData();

            // Rien de sélectionné → on avertit
            if (!$selected || \count(is_array($selected) ? $selected : iterator_to_array($selected)) === 0) {
                $this->addFlash('success', 'Aucun destinataire sélectionné.');
                return $this->redirectToRoute('envoyer-email');
            }

            $expediteurEmail = (string) $currentUser->getEmail();
            $nomComplet      = trim(($currentUser->getPrenom() ?? '') . ' ' . ($currentUser->getNom() ?? ''));

            // Signature HTML
            $signatureHtml = <<<HTML
<br><br>
<hr>
<p style="font-size:0.9em;color:#555;">
  Cet email a été envoyé par <strong>{$nomComplet}</strong>.
</p>
<p style="font-size:0.9em;color:#555;">
  Pour lui répondre, cliquez ici :
  (<a href="mailto:{$expediteurEmail}">{$expediteurEmail}</a>).
</p>
HTML;

            $messageAvecSignature = nl2br($message) . $signatureHtml;

            $envoyes = 0;
            foreach ($selected as $receiver) {
                if (!$receiver instanceof User) {
                    continue;
                }
                $to = $receiver->getEmail();
                if (!$to) {
                    continue;
                }

                $email = (new Email())
                    ->from($expediteurEmail)
                    ->to($to)
                    ->subject('Message de votre maître de métier')
                    ->html($messageAvecSignature);

                $mailer->send($email);
                $envoyes++;
            }

            $this->addFlash('success', sprintf('Email envoyé à %d destinataire(s).', $envoyes));
            return $this->redirectToRoute('envoyer-email');
        }

        return $this->render('email/send.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
