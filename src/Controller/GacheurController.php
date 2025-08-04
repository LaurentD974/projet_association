<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
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
                $event->setIsValidated(false); // à valider par admin
            } elseif ($event->getType() === 'Corporatif') {
                $event->setIsValidated(true); // validé automatiquement
                $event->setType($user->getMetier()); // remplace "Corporatif" par métier
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
}