<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['admin1@asso.fr', 'adminpass', ['ROLE_ADMIN']],
            ['gacheur@asso.fr', 'gacheurpass', ['ROLE_GACHEUR']],
            ['user@asso.fr', 'userpass', ['ROLE_USER']],
        ];

        foreach ($usersData as [$email, $password, $roles]) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setPassword($this->hasher->hashPassword($user, $password));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
