<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;
use App\Entity\Event;
use App\Form\EventType;

class EventController extends AbstractController
{
#[Route('/api/events', name: 'api_events')]
public function events(EventRepository $repo, EntityManagerInterface $em): JsonResponse
{
    $user = $this->getUser();

    if (!$user) {
        return new JsonResponse(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
    }

    $roles = $user->getRoles();
    $isPrivileged = in_array('ROLE_GACHEUR', $roles) || in_array('ROLE_ADMIN', $roles);

    if ($isPrivileged) {
        $qb = $em->createQueryBuilder();
        $qb->select('e')
            ->from(Event::class, 'e')
            ->leftJoin('e.proposedBy', 'u')
            ->where('e.isValidated = true');

        $events = $qb->getQuery()->getResult();
    } else {
        $qb = $em->createQueryBuilder();
$qb->select('e')
    ->from(Event::class, 'e')
    ->where('e.isValidated = true')
    ->andWhere(
        $qb->expr()->orX(
            $qb->expr()->eq('LOWER(e.type)', ':metier'),
            $qb->expr()->eq('e.type', ':typeCommunautaire')
        )
    )
    ->setParameter('metier', strtolower($user->getMetier()))
    ->setParameter('typeCommunautaire', 'Communautaire');

        $events = $qb->getQuery()->getResult();
    }

    $data = [];

    foreach ($events as $event) {
        $data[] = [
            'title' => $event->getTitle(),
            'start' => $event->getStartDate()->format('c'),
            'end'   => $event->getEndDate() ? $event->getEndDate()->format('c') : null,
            'url'   => $this->generateUrl('event_detail', ['id' => $event->getId()]),
            'backgroundColor' => match ($event->getType()) {
                'Charpentier' => '#3498db',
                'Menuisier' => '#2ecc71',
                'Couvreur' => '#f39c12',
                'Serrurier' => '#8e44ad',
                'Plombier' => '#e67e22',
                'Communautaire' => '#e74c3c',
                default => '#95a5a6',
            },
            'textColor' => '#ffffff',
            'extendedProps' => [
                'location'     => $event->getLocation(),
                'type'         => $event->getType(),
                'description'  => $event->getDescription(),
                'is_validated' => $event->isValidated(),
                'proposed_by'  => $event->getProposedBy() ? [
                    'nom'    => $event->getProposedBy()->getNom(),
                    'prenom' => $event->getProposedBy()->getPrenom(),
                    'metier' => $event->getProposedBy()->getMetier()
                ] : null
            ]
        ];
    }

    return new JsonResponse($data);
}

    #[Route('/subscribe/{id}', name: 'event_subscribe', methods: ['POST'])]
    public function subscribe(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        if ($event->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
        } else {
            $event->addParticipant($user);
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie !');
        }

        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}', name: 'event_detail')]
    public function detail(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException("L'événement avec l'ID $id n'existe pas.");
        }

        return $this->render('event/detail.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/unsubscribe/{id}', name: 'event_unsubscribe', methods: ['POST'])]
    public function unsubscribe(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour vous désinscrire.');
            return $this->redirectToRoute('app_login');
        }

        if ($event->getParticipants()->contains($user)) {
            $event->removeParticipant($user);
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Vous avez été désinscrit de l’événement.');
        } else {
            $this->addFlash('warning', 'Vous n’étiez pas inscrit à cet événement.');
        }

        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
    }
    
}