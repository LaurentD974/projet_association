<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Referent;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\ReferentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ReferentAssociationType;

/**
 * @method \App\Entity\User|null getUser()
 */
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
        $user  = $this->currentUserOr403();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setProposedBy($user);

            if ($event->getType() === 'Communautaire') {
                $event->setIsValidated(false);
            } elseif ($event->getType() === 'Corporatif') {
                $event->setIsValidated(true);
                $event->setType((string) $user->getMetier());
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

        $user   = $this->currentUserOr403();
        $metier = (string) $user->getMetier();

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

        $gacheur = $this->currentUserOr403();
        $metier  = (string) $gacheur->getMetier();

        $utilisateurs = $userRepository->findBy([
            'metier'   => $metier,
            'position' => $position,
        ]);

        return $this->render('gacheur/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
            'position'     => $position,
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
            $entreprise   = $entrepriseRepo->find($entrepriseId);

            $user->setEntreprise($entreprise);
            $em->flush();

            $this->addFlash('success', 'Entreprise attribuée avec succès.');

            return $this->redirectToRoute('gacheur_utilisateurs', [
                'position' => $user->getPosition(),
            ]);
        }

        return $this->render('gacheur/attribuer.html.twig', [
            'user'        => $user,
            'entreprises' => $entreprises,
        ]);
    }

    #[Route('/gacheur/associer-referent', name: 'associer_referent')]
    public function associerReferent(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $me            = $this->currentUserOr403();
        $metierCourant = (string) $me->getMetier();

        $repo = $em->getRepository(User::class);

        // 1) Récupération des sédentaires et itinérants
        $sedentaires = $repo->createQueryBuilder('u')
            ->andWhere('u.position = :pos')
            ->setParameter('pos', 'Sédentaire') // adapte si autre libellé
            ->getQuery()->getResult();

        $itinerants = $repo->createQueryBuilder('u')
            ->andWhere('u.position = :pos')
            ->setParameter('pos', 'Itinérant') // adapte si autre libellé
            ->getQuery()->getResult();

        // 2) Ordonner : métier du user en tête, puis autres métiers (alpha), tri Nom/Prénom intra-groupe
        $choicesReferents  = $this->orderUsersByMetierPreferredFirst($sedentaires, $metierCourant);
        $choicesItinerants = $this->orderUsersByMetierPreferredFirst($itinerants,  $metierCourant);

        // 3) Formulaire 2 listes (groupées par métier)
        $form = $this->createForm(ReferentAssociationType::class, null, [
            'choices_referents'  => $choicesReferents,
            'choices_itinerants' => $choicesItinerants,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $referentUser */
            $referentUser  = $form->get('referent')->getData();
            /** @var User|null $itinerantUser */
            $itinerantUser = $form->get('responsableDe')->getData();

            if (!$referentUser || !$itinerantUser) {
                $this->addFlash('danger', 'Veuillez sélectionner un référent et un itinérant.');
                return $this->redirectToRoute('associer_referent');
            }

            $assoc = new Referent(); // entité d’association
            $assoc->setReferent($referentUser);
            $assoc->setResponsableDe($itinerantUser);

            $em->persist($assoc);
            $em->flush();

            $this->addFlash('success', 'Association référent/utilisateur enregistrée.');
            return $this->redirectToRoute('gacheur_referents_utilisateurs');
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
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $me            = $this->currentUserOr403();
        $metierCourant = (string) $me->getMetier();

        $repo = $em->getRepository(User::class);

        // Listes filtrées
        $sedentaires = $repo->createQueryBuilder('u')
            ->andWhere('u.position = :pos')
            ->setParameter('pos', 'Sédentaire')
            ->getQuery()->getResult();

        $itinerants = $repo->createQueryBuilder('u')
            ->andWhere('u.position = :pos')
            ->setParameter('pos', 'Itinérant')
            ->getQuery()->getResult();

        // Tri: métier du user connecté d'abord
        $choicesReferents  = $this->orderUsersByMetierPreferredFirst($sedentaires, $metierCourant);
        $choicesItinerants = $this->orderUsersByMetierPreferredFirst($itinerants,  $metierCourant);

        // Form non mappé : pré-remplissage des deux champs
        $form = $this->createForm(ReferentAssociationType::class, null, [
            'choices_referents'  => $choicesReferents,
            'choices_itinerants' => $choicesItinerants,
        ]);
        $form->get('referent')->setData($referent->getReferent());
        $form->get('responsableDe')->setData($referent->getResponsableDe());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $newRef */
            $newRef  = $form->get('referent')->getData();
            /** @var User|null $newItin */
            $newItin = $form->get('responsableDe')->getData();

            if (!$newRef || !$newItin) {
                $this->addFlash('danger', 'Veuillez sélectionner un référent et un itinérant.');
                return $this->redirectToRoute('modifier_association', ['id' => $referent->getId()]);
            }

            $referent->setReferent($newRef);
            $referent->setResponsableDe($newItin);
            $em->flush();

            $this->addFlash('success', 'Association modifiée avec succès.');
            return $this->redirectToRoute('gacheur_referents_utilisateurs');
        }

        return $this->render('gacheur/edit_association.html.twig', [
            'form'     => $form->createView(),
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

    /**
     * Ordonne : [même métier que $preferred] (triés Nom/Prénom), puis
     *           [autres métiers par ordre alpha], chacun trié Nom/Prénom.
     * Conserve l’ordre d’apparition pour piloter l’ordre des <optgroup>.
     *
     * @param User[] $users
     * @return User[]
     */
    private function orderUsersByMetierPreferredFirst(array $users, string $preferred): array
    {
        // Buckets par métier
        $buckets = [];
        foreach ($users as $u) {
            if (!$u instanceof User) {
                continue;
            }
            $metier = $u->getMetier() ?: '—';
            $buckets[$metier][] = $u;
        }

        // Tri Nom/Prénom à l’intérieur de chaque métier
        foreach ($buckets as &$list) {
            usort($list, fn(User $a, User $b) =>
                [$a->getNom(), $a->getPrenom()] <=> [$b->getNom(), $b->getPrenom()]
            );
        }
        unset($list);

        // Construction finale : d’abord métier préféré, puis autres métiers (alpha)
        $ordered = [];
        if ($preferred && isset($buckets[$preferred])) {
            $ordered = array_merge($ordered, $buckets[$preferred]);
            unset($buckets[$preferred]);
        }
        $keys = array_keys($buckets);
        natcasesort($keys);
        foreach ($keys as $k) {
            $ordered = array_merge($ordered, $buckets[$k]);
        }
        return $ordered;
    }

    /**
     * Garantit un User typé, sinon 403.
     */
    private function currentUserOr403(): User
    {
        $u = $this->getUser();
        if (!$u instanceof User) {
            throw $this->createAccessDeniedException();
        }
        return $u;
    }
}
