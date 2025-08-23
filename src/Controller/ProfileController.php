<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Referent;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\ReferentRepository;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProfileController extends AbstractController
{
    #[Route('/mon-profil', name: 'app_profile')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher,
        ReferentRepository $referentRepo,
        #[CurrentUser] User $user
    ): Response {

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
        }

        $association = $referentRepo->findOneBy(['responsableDe' => $user]);
        $referent = $association ? $association->getReferent() : null;

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', '❌ Mot de passe actuel incorrect.');
                return $this->redirectToRoute('app_profile');
            }

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

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $this->addFlash('info', '🔐 Mot de passe mis à jour avec succès.');
            }

            $em->flush();
            $this->addFlash('success', '✅ Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'referent' => $referent,
            'association' => $association,
        ]);
    }

    #[Route('/mon-profil/succes', name: 'app_profile_success')]
    public function success(ReferentRepository $referentRepo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $association = $referentRepo->findOneBy(['responsableDe' => $user]);
        $referent = $association ? $association->getReferent() : null;

        $form = $this->createForm(UserProfileType::class, $user);

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'referent' => $referent,
            'association' => $association,
        ]);
    }

  #[Route('/referent/{id}', name: 'referent_show')]
public function show(int $id, EntityManagerInterface $em, ReferentRepository $referentRepo): Response
{
    $referentUser = $em->getRepository(User::class)->find($id);

    if (!$referentUser) {
        throw $this->createNotFoundException('Référent introuvable.');
    }

    $encadres = $referentRepo->findEncadresByReferent($referentUser);

    return $this->render('referent/show.html.twig', [
        'referent' => $referentUser, // ← C’est un User
        'encadres' => array_map(fn($r) => $r->getResponsableDe(), $encadres),
    ]);
}
}