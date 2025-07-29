<?php

// src/Controller/UserController.php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/members', name: 'member_page')]
    public function showMembers(UserRepository $userRepository): Response
    {
        $members = $userRepository->findMembers();

        return $this->render('member/search.html.twig', [
            'members' => $members,
        ]);
    }
}