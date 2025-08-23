<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'app_planning')]
    public function index(NewsRepository $newsRepository): Response
    {
        $newsList = $newsRepository->findBy([], ['createdAt' => 'DESC']); // tri par date décroissante

        return $this->render('user/planning.html.twig', [
            'news_list' => $newsList,
        ]);
    }

    // #[Route('/api/events', name: 'api_events')]
    // public function getEvents(EventRepository $eventRepository, UserInterface $user): JsonResponse
    // {
    //     $metier = $user->getMetier() ?? ''; // Assure-toi que getMetier() existe dans ton entité User

    //     $events = $eventRepository->findFilteredForCalendar($metier);

    //     $formattedEvents = array_map(function ($e) {
    //         return [
    //             'id' => $e['id'],
    //             'title' => $e['title'],
    //             'start' => $e['startDate']->format('Y-m-d H:i:s'),
    //             'url' => '/event/' . $e['id'],
    //             'backgroundColor' => match ($e['type']) {
    //                 'Charpentier' => '#3498db',
    //                 'Menuisier' => '#2ecc71',
    //                 'Couvreur' => '#f39c12',
    //                 'Communautaire' => '#e74c3c',
    //                 default => '#95a5a6',
    //             },
    //             'textColor' => '#ffffff',
    //             'extendedProps' => [
    //                 'location'    => $e['location'] ?? 'Non précisé',
    //                 'type'        => $e['type'] ?? 'Non spécifié',
    //                 'description' => $e['description'] ?? 'Pas de description'
    //             ]
    //         ];
    //     }, $events);

    //     return new JsonResponse($formattedEvents);
    // }
}