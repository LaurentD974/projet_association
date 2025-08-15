<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\AdminEventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EventRepository $eventRepo, UserRepository $userRepo): Response
    {
        $pendingCount = count($eventRepo->findBy(['isValidated' => false]));
        $totalCount = count($eventRepo->findAll());

        $users = $userRepo->findAll();
        $totalUsers = count($users);
        $totalCompagnons = count(array_filter($users, fn($u) => $u->getStatut() === 'Compagnon'));

        $metierCounts = [];
        foreach ($users as $user) {
            $metier = $user->getMetier(); // Assure-toi que cette méthode existe et retourne une string
            if ($metier) {
                $metierCounts[$metier] = ($metierCounts[$metier] ?? 0) + 1;
            }
        }

        return $this->render('admin/dashboard.html.twig', [
            'pendingCount' => $pendingCount,
            'totalCount' => $totalCount,
            'totalUsers' => $totalUsers,
            'totalCompagnons' => $totalCompagnons,
            'metierCounts' => $metierCounts,
        ]);
    }

    #[Route('/events', name: 'admin_events_validate')]
    public function validateEvents(EventRepository $repo): Response
    {
        $pending = $repo->findBy(['isValidated' => false]);
        return $this->render('admin/events/pending.html.twig', ['events' => $pending]);
    }

    #[Route('/events/list', name: 'admin_events_list')]
    public function listEvents(EventRepository $repo): Response
    {
        $events = $repo->findAll();
        return $this->render('admin/events/list.html.twig', ['events' => $events]);
    }

    #[Route('/events/new', name: 'admin_events_new')]
    public function newEvent(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(AdminEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('admin_events_list');
        }

        return $this->render('admin/events/form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/events/{id}/edit', name: 'admin_events_edit')]
    public function editEvent(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('admin_events_list');
        }

        return $this->render('admin/events/form.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}/delete', name: 'admin_events_delete', methods: ['POST'])]
    public function deleteEvent(Event $event, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('danger', 'Événement supprimé.');
        }

        return $this->redirectToRoute('admin_events_list');
    }

    #[Route('/events/{id}/validate', name: 'admin_events_validate_one')]
    public function validateOne(Event $event, EntityManagerInterface $em): Response
    {
        $event->setIsValidated(true);
        $em->flush();
        $this->addFlash('success', 'Événement validé.');
        return $this->redirectToRoute('admin_events_list');
    }
}