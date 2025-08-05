<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findMembers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_USER%')
            ->getQuery()
            ->getResult();
    }

    public function findDistinctMetiers(): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.metier')
            ->where('u.metier IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($row) => $row['metier'], $result);
    }

    public function findDistinctVilles(): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.ville')
            ->where('u.ville IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($row) => $row['ville'], $result);
    }

    public function findDistinctFonctions(): array
    {
        $qb = $this->createQueryBuilder('u');

        $result1 = $qb
            ->select('DISTINCT u.fonction1 AS fonction')
            ->where('u.fonction1 IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        $result2 = $qb
            ->select('DISTINCT u.fonction2 AS fonction')
            ->where('u.fonction2 IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        $fonctions = array_merge($result1, $result2);
        $uniqueFonctions = array_unique(array_map(fn($row) => $row['fonction'], $fonctions));

        sort($uniqueFonctions);

        return $uniqueFonctions;
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_USER%');

        if (!empty($filters['metier'])) {
            $qb->andWhere('u.metier = :metier')->setParameter('metier', $filters['metier']);
        }

        if (!empty($filters['ville'])) {
            $qb->andWhere('u.ville = :ville')->setParameter('ville', $filters['ville']);
        }

        if (!empty($filters['fonction'])) {
            $qb->andWhere('u.fonction1 = :fonction OR u.fonction2 = :fonction')
               ->setParameter('fonction', $filters['fonction']);
        }

        $qb->select('u.id, u.email, u.nom, u.prenom, u.metier, u.statut, u.position, u.telephone, u.fonction1, u.fonction2, u.ville, u.photo');

        return $qb->getQuery()->getArrayResult();
    }
}