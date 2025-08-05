<?php

namespace App\Controller;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
   use App\Repository\UserRepository; // en haut du fichier

#[Route('/member')]
class MemberController extends AbstractController
{
    #[Route('/', name: 'member_list')]
    public function index(MemberRepository $memberRepository): Response
    {
        $members = $memberRepository->findAll();
        return $this->render('member/index.html.twig', [
            'members' => $members,
        ]);
    }



#[Route('/show/{id}', name: 'member_show')]
public function show($id, UserRepository $userRepository): Response
{
    $member = $userRepository->find($id);

    return $this->render('member/show.html.twig', [
        'member' => $member,
    ]);
}

    #[Route('/search', name: 'user_search')]
    public function search(Request $request, MemberRepository $repo): Response
    {
        $metier = $request->query->get('metier');
        $ville = $request->query->get('ville');
        $fonction1 = $request->query->get('fonction1'); // Correction ici

        $queryBuilder = $repo->createQueryBuilder('u');

        if ($metier) {
            $queryBuilder->andWhere('u.metier = :metier')->setParameter('metier', $metier);
        }
        if ($ville) {
            $queryBuilder->andWhere('u.ville = :ville')->setParameter('ville', $ville);
        }
        if ($fonction1) {
            $queryBuilder->andWhere('u.fonction1 LIKE :fonction1')->setParameter('fonction1', "%$fonction1%");
        }

        $results = $queryBuilder->getQuery()->getResult();

        return $this->render('member/search.html.twig', [
            'members' => $results,
        ]);
    }

    #[Route('/api/members', name: 'api_member_filter')]
    public function filterAjax(Request $request, MemberRepository $repo): JsonResponse
    {
        $metier = $request->query->get('metier');
        $ville = $request->query->get('ville');
        $fonction = $request->query->get('fonction'); // Correction ici

        $qb = $repo->createQueryBuilder('u');

        if ($metier) {
            $qb->andWhere('u.metier = :metier')->setParameter('metier', $metier);
        }
        if ($ville) {
            $qb->andWhere('u.ville = :ville')->setParameter('ville', $ville);
        }
        if ($fonction) {
            $qb->andWhere('u.fonction LIKE :fonction1')->setParameter('fonction', "%$fonction%");
        }

        $results = $qb->getQuery()->getResult();

        $data = array_map(fn($u) => [
            'id' => $u->getId(), // 👈 Ajout nécessaire pour la redirection
            'nom' => $u->getNom(),
            'prenom' => $u->getPrenom(),
            'metier' => $u->getMetier(),
            'statut' => $u->getStatut(),
            'fonction1' => $u->getFonction1(),
            'fonction2' => $u->getFonction2(),
            'ville' => $u->getVille(),
        ], $results);

        return new JsonResponse($data);
    }

    #[Route('/api/filters', name: 'api_member_filters')]
    public function getFilters(MemberRepository $repo): JsonResponse
    {
        $metiers = $repo->getDistinctMetiers();
        $fonctions = $repo->getDistinctFonctions();
        $villes = $repo->getDistinctVilles();

        return $this->json([
            'metiers' => $metiers,
            'fonctions' => $fonctions,
            'villes' => $villes,
        ]);
    }
    #[Route('/members', name: 'member_page')]
public function showMembers(UserRepository $userRepository): Response
{
    $members = $userRepository->findMembers();

    return $this->render('member/search.html.twig', [
        'members' => $members,
    ]);
}
}