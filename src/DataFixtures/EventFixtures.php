<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Exemple : récupérer un utilisateur existant avec l'ID 1
        $user = $manager->getRepository(User::class)->find(1);

        for ($i = 0; $i < 10; $i++) {
            $event = new Event();
            $event->setProposedBy($user);
            $event->setTitle($faker->sentence(4));
            $event->setType($faker->randomElement(['Atelier', 'Réunion', 'Sortie', 'Conférence']));
            $event->setLocation($faker->city());
            $event->setStartDate($faker->dateTimeBetween('now', '+2 months'));
            $event->setIsValidated($faker->boolean(80)); // 80% de chance d’être validé

            $manager->persist($event);
        }

        $manager->flush();
    }
}