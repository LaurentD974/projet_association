<?php

namespace App\Repository;

use App\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Referent>
 */
class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referent::class);
    }

    // Tu peux ajouter des méthodes personnalisées ici si besoin
    public function findEncadresByReferent(User $referent): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.referent = :referent')
        ->setParameter('referent', $referent)
        ->getQuery()
        ->getResult();
}
}