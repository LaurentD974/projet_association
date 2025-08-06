<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use App\Form\EntrepriseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
    #[Route('/gacheur/entreprise/new', name: 'entreprise_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_GACHEUR');

        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entreprise);
            $em->flush();

            $this->addFlash('success', '✅ Entreprise créée avec succès.');
            return $this->redirectToRoute('entreprise_new');
        }

        return $this->render('entreprise/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    // Afficher la liste
#[Route('/gacheur/entreprises', name: 'entreprise_index')]
public function index(EntrepriseRepository $entrepriseRepository): Response
{
    return $this->render('entreprise/index.html.twig', [
        'entreprises' => $entrepriseRepository->findAll(),
    ]);
}

// Modifier une entreprise
#[Route('/gacheur/entreprise/{id}/edit', name: 'entreprise_edit')]
public function edit(Request $request, Entreprise $entreprise, EntityManagerInterface $em): Response
{
    $form = $this->createForm(EntrepriseType::class, $entreprise);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Entreprise modifiée avec succès.');
        return $this->redirectToRoute('entreprise_index');
    }

    return $this->render('entreprise/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

// Supprimer une entreprise
#[Route('/gacheur/entreprise/{id}/delete', name: 'entreprise_delete', methods: ['POST'])]
public function delete(Request $request, Entreprise $entreprise, EntityManagerInterface $em): Response
{
    if ($this->isCsrfTokenValid('delete'.$entreprise->getId(), $request->request->get('_token'))) {
        $em->remove($entreprise);
        $em->flush();
        $this->addFlash('success', 'Entreprise supprimée.');
    }

    return $this->redirectToRoute('entreprise_index');
}
#[Route('/entreprise/{id}', name: 'entreprise_show')]
public function show(Entreprise $entreprise): Response
{
    return $this->render('entreprise/show.html.twig', [
        'entreprise' => $entreprise,
    ]);
}
}