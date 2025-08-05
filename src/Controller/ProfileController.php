<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/mon-profil', name: 'app_profile')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
        }

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 🔐 Vérification du mot de passe actuel
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', '❌ Mot de passe actuel incorrect.');
                return $this->redirectToRoute('app_profile');
            }

            // 📸 Gestion de la photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('profile_photos_directory'),
                        $newFilename
                    );
                    $user->setPhoto($newFilename);
                    $this->addFlash('info', '📸 Photo mise à jour avec succès.');
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de la photo.');
                }
            }

            // 🔄 Mise à jour du mot de passe si nécessaire
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $this->addFlash('info', '🔐 Mot de passe mis à jour avec succès.');
            }

            // 💾 Enregistrement
            $em->flush();
            $this->addFlash('success', '✅ Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/mon-profil/succes', name: 'app_profile_success')]
    public function success(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(UserProfileType::class, $user);

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}