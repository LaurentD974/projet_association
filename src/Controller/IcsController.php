<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IcsController extends AbstractController
{
    #[Route('/event/{id}/export.ics', name: 'event_ics')]
    public function exportIcs(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException("Événement introuvable.");
        }

        // Format des dates ICS : YYYYMMDDTHHmmss
        $start = $event->getStartDate()->format('Ymd\THis');
        $end = $event->getEndDate()
            ? $event->getEndDate()->format('Ymd\THis')
            : $event->getStartDate()->modify('+1 hour')->format('Ymd\THis');

        $uid = $event->getId() . '@tonapp.local';
        $summary = $event->getTitle();
        $description = $event->getDescription();
        $location = $event->getLocation();

        $ics = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//TonApp/EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
UID:$uid
DTSTAMP:$start
DTSTART:$start
DTEND:$end
SUMMARY:$summary
DESCRIPTION:$description
LOCATION:$location
STATUS:CONFIRMED
SEQUENCE:0
END:VEVENT
END:VCALENDAR
ICS;

        return new Response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="event_$id.ics"',
        ]);
    }
}