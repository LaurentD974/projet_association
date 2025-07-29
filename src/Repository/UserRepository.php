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

    /**
     * Mise à jour automatique du mot de passe haché
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne tous les utilisateurs ayant le rôle ROLE_USER
     */
    public function findMembers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_USER%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Métiers distincts
     */
    public function findDistinctMetiers(): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.metier')
            ->where('u.metier IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($row) => $row['metier'], $result);
    }

    /**
     * Villes distinctes
     */
    public function findDistinctVilles(): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.ville')
            ->where('u.ville IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($row) => $row['ville'], $result);
    }

    /**
     * Fonctions distinctes (fonction1)
     */
    public function findDistinctFonctions(): array
    {
        $result = $this->createQueryBuilder('u')
            ->select('DISTINCT u.fonction1')
            ->where('u.fonction1 IS NOT NULL')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($row) => $row['fonction1'], $result);
    }

    /**
     * Filtrage par métier, ville, fonction
     */
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
            $qb->andWhere('u.fonction1 = :fonction1')->setParameter('fonction1', $filters['fonction1']);
        }

        $qb->select('u.id,u.email,u.nom,u.prenom,u.metier,u.statut,u.position,u.telephone,u.fonction1,u.ville');

        return $qb->getQuery()->getArrayResult();
    }
}