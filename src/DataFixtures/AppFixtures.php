<?php

// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $metiers = ['Charpentier', 'Maçon', 'Forgeron', 'Tailleur de pierre'];
        $fonctions = ['Responsable', 'Coordinateur', 'Formateur', 'Accompagnant'];
        $provinces = ['Île-de-France', 'Rhône-Alpes', 'Occitanie', 'Grand Est'];
        $statuts = ['Actif', 'Inactif'];
        $villes = ['Paris', 'Lyon', 'Toulouse', 'Strasbourg'];

        // === Membres ROLE_USER ===
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user$i@asso.fr");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'userpass'));

            $user->setPrenom("Prénom$i");
            $user->setNom("Nom$i");
            $user->setTelephone("060000000$i");
            $user->setMetier($metiers[array_rand($metiers)]);
            $user->setStatut($statuts[array_rand($statuts)]);
            $user->setPosition("Position $i");
            $user->setNomProvince($provinces[array_rand($provinces)]);
            $user->setNomCompagnon("Compagnon $i");
            $user->setFonction1($fonctions[array_rand($fonctions)]);
            $user->setFonction2($fonctions[array_rand($fonctions)]);
            $user->setAdresse1("10 rue de la Gâche");
            $user->setAdresse2("Bât B, étage $i");
            $user->setCodePostale("7500$i");
            $user->setVille($villes[array_rand($villes)]);
            $user->setDroit("Standard");
            $user->setPhoto("profil$i.jpg");

            $manager->persist($user);
        }

        // === ROLE_GACHEUR ===
        for ($i = 1; $i <= 3; $i++) {
            $gacheur = new User();
            $gacheur->setEmail("gacheur$i@asso.fr");
            $gacheur->setRoles(['ROLE_GACHEUR']);
            $gacheur->setPassword($this->hasher->hashPassword($gacheur, 'gacheurpass'));

            $gacheur->setPrenom("Gâcheur$i");
            $gacheur->setNom("Dupont$i");
            $gacheur->setFonction1("Responsable Gâche");
            $gacheur->setMetier("Maître Artisan");
            $gacheur->setNomProvince("Gâche Provence");
            $gacheur->setVille("Marseille");
            $gacheur->setStatut("Actif");
            $gacheur->setPhoto("gacheur$i.jpg");

            $manager->persist($gacheur);
        }

        // === ROLE_ADMIN ===
        $admin = new User();
        $admin->setEmail("admin@asso.fr");
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'adminpass'));

        $admin->setPrenom("Admin");
        $admin->setNom("Superviseur");
        $admin->setFonction1("Administrateur principal");
        $admin->setMetier("Gestion");
        $admin->setVille("Paris");
        $admin->setStatut("Actif");
        $admin->setPhoto("admin.jpg");

        $manager->persist($admin);

        $manager->flush();
    }
}