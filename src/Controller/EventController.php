<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class EventController extends AbstractController
{
    #[Route('/api/events', name: 'api_events')]
    public function events(EventRepository $repo): JsonResponse
    {
        $events = $repo->findBy(['isValidated' => true]);

        $data = array_map(fn($e) => [
            'title' => $e->getTitle(),
            'start' => $e->getStartDate()->format('Y-m-d'),
            'url'   => $this->generateUrl('event_show', ['id' => $e->getId()])
        ], $events);

        return new JsonResponse($data);
    }

    #[Route('/subscribe/{id}', name: 'event_subscribe')]
    public function subscribe(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $event->addParticipant($user);
        $em->flush();

        $this->addFlash('success', 'Inscription réussie.');
        return $this->redirectToRoute('user_planning');
    }
}

