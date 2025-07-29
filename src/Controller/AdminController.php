<?php
namespace App\Controller;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'admin_user_list')]
    public function listUsers(UserRepository $repo): Response {
        $users = $repo->findAll();
        return $this->render('admin/users.html.twig', ['users' => $users]);
    }

    #[Route('/events', name: 'admin_events_validate')]
    public function validateEvents(EventRepository $repo): Response {
        $pending = $repo->findBy(['isValidated' => false]);
        return $this->render('admin/events.html.twig', ['events' => $pending]);
    }
}
