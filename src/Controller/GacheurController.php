<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Referent;
use App\Form\EventType;
use App\Form\ReferentType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ReferentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GacheurController extends AbstractController
{
    #[Route('/gacheur/dashboard', name: 'gacheur_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');
        return $this->render('gacheur/dashboard.html.twig');
    }

    #[Route('/gacheur/propose-event', name: 'event_propose')]
    public function proposeEvent(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $event = new Event();
        $user = $this->getUser();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setProposedBy($user);

            if ($event->getType() === 'Communautaire') {
                $event->setIsValidated(false);
            } elseif ($event->getType() === 'Corporatif') {
                $event->setIsValidated(true);
                $event->setType($user->getMetier());
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement proposé avec succès.');
            return $this->redirectToRoute('gacheur_dashboard');
        }

        return $this->render('gacheur/propose_event.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/gacheur/evenements-metier', name: 'event_by_profession')]
    public function eventsByProfession(EventRepository $eventRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $user = $this->getUser();
        $metier = $user->getMetier();

        $events = $eventRepo->findBy(['type' => $metier]);

        return $this->render('gacheur/events_by_profession.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/gacheur/utilisateurs/{position}', name: 'gacheur_utilisateurs')]
    public function utilisateursParMetierEtPosition(
        UserRepository $userRepository,
        string $position
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $gacheur = $this->getUser();
        $metier = $gacheur->getMetier();

        $utilisateurs = $userRepository->findBy([
            'metier' => $metier,
            'position' => $position
        ]);

        return $this->render('gacheur/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
            'position' => $position
        ]);
    }

    #[Route('/gacheur/attribuer/{id}', name: 'attribuer_entreprise')]
    public function attribuerEntreprise(
        User $user,
        EntrepriseRepository $entrepriseRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $entreprises = $entrepriseRepo->findAll();

        if ($request->isMethod('POST')) {
            $entrepriseId = $request->request->get('entreprise_id');
            $entreprise = $entrepriseRepo->find($entrepriseId);

            $user->setEntreprise($entreprise);
            $em->flush();

            $this->addFlash('success', 'Entreprise attribuée avec succès.');

            return $this->redirectToRoute('gacheur_utilisateurs', [
                'position' => $user->getPosition()
            ]);
        }

        return $this->render('gacheur/attribuer.html.twig', [
            'user' => $user,
            'entreprises' => $entreprises,
        ]);
    }

    #[Route('/gacheur/associer-referent', name: 'associer_referent')]
    public function associerReferent(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $referent = new Referent();
        $form = $this->createForm(ReferentType::class, $referent);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($referent);
            $em->flush();

            $this->addFlash('success', 'Association référent/utilisateur enregistrée.');
            return $this->redirectToRoute('gacheur_dashboard');
        }

        return $this->render('gacheur/associer_referent.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/gacheur/referents-utilisateurs', name: 'gacheur_referents_utilisateurs')]
    public function referentsUtilisateurs(ReferentRepository $referentRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $associations = $referentRepo->findAll();

        return $this->render('gacheur/referents_utilisateurs.html.twig', [
            'associations' => $associations,
        ]);
    }

    #[Route('/gacheur/association/{id}/modifier', name: 'modifier_association')]
    public function editAssociation(Referent $referent, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReferentType::class, $referent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Association modifiée avec succès.');
            return $this->redirectToRoute('gacheur_referents_utilisateurs');
        }

        return $this->render('gacheur/edit_association.html.twig', [
            'form' => $form->createView(),
            'referent' => $referent,
        ]);
    }

    #[Route('/gacheur/association/{id}/supprimer', name: 'supprimer_association', methods: ['POST'])]
    public function supprimerAssociation(Referent $referent, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_association_' . $referent->getId(), $request->request->get('_token'))) {
            $em->remove($referent);
            $em->flush();

            $this->addFlash('success', 'L\'association a été supprimée avec succès.');
        }

        return $this->redirectToRoute('gacheur_referents_utilisateurs');
    }
}