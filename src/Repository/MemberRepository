<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;   
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourne tous les membres ayant le rôle ROLE_USER
     */
    public function findMembers(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.roles LIKE :role')
            ->setParameter('role', '%ROLE_USER%')
            ->getQuery()
            ->getResult();
    }
public function getDistinctMetiers(): array
{
    return array_column($this->createQueryBuilder('m')
        ->select('DISTINCT m.metier')
        ->orderBy('m.metier', 'ASC')
        ->getQuery()
        ->getResult(),
        'metier');
}

public function getDistinctFonctions(): array
{
    return array_column($this->createQueryBuilder('m')
        ->select('DISTINCT m.fonction1')
        ->orderBy('m.fonction1', 'ASC')
        ->getQuery()
        ->getResult(),
        'fonction1');
}

public function getDistinctVilles(): array
{
    return array_column($this->createQueryBuilder('m')
        ->select('DISTINCT m.ville')
        ->orderBy('m.ville', 'ASC')
        ->getQuery()
        ->getResult(),
        'ville');
}
}