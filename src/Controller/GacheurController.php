<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Event;
use App\Form\EventType;

#[Route('/gacheur')]
class GacheurController extends AbstractController
{
    #[Route('/proposer', name: 'gacheur_event_propose')]
    public function propose(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setProposedBy($this->getUser());
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('dashboard_gacheur');
        }

        return $this->render('gacheur/propose.html.twig', [
            'form' => $form->createView()
        ]);
    }
}