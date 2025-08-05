<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/members', name: 'api_members')]
    public function getMembers(Request $request, UserRepository $userRepository): JsonResponse
    {
        $filters = $request->query->all();

        // On utilise le champ unique 'fonction'
        if (isset($filters['fonction'])) {
            $filters = [
                'metier' => $filters['metier'] ?? null,
                'ville' => $filters['ville'] ?? null,
                'fonction' => $filters['fonction'],
            ];
        }

        $users = $userRepository->findByFilters(array_filter($filters));

        return new JsonResponse($users);
    }

    #[Route('/api/filters', name: 'api_filters')]
    public function getFilters(UserRepository $userRepository): JsonResponse
    {
        $metiers = $userRepository->findDistinctMetiers();
        $villes = $userRepository->findDistinctVilles();
        $fonctions = $userRepository->findDistinctFonctions(); // fonction1 + fonction2 fusionnés

        return new JsonResponse([
            'metiers' => $metiers,
            'villes' => $villes,
            'fonctions' => $fonctions,
        ]);
    }

    #[Route('/api/filter-users', name: 'api_filter_users')]
    public function filterUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        $filters = [
            'metier' => $request->query->get('metier'),
            'ville' => $request->query->get('ville'),
            'fonction' => $request->query->get('fonction'), // ✅ un seul champ
        ];

        $users = $userRepository->findByFilters(array_filter($filters));

        return new JsonResponse($users);
    }
}