<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        NewsRepository $newsRepository
    ): Response {
        // Si tu veux vraiment déconnecter l'utilisateur à l'arrivée sur la page d'accueil :
        $tokenStorage->setToken(null);
        $session->invalidate();

        // Récupérer les actualités publiées
        $newsList = $newsRepository->findBy(['isPublished' => true], ['createdAt' => 'DESC']);

        // Afficher la page d'accueil avec les actualités
        return $this->render('home/index.html.twig', [
            'newsList' => $newsList,
        ]);
    }
}