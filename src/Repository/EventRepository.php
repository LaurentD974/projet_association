<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

  public function findAllForCalendar(): array
{
    return $this->createQueryBuilder('e')
        ->select('e.id, e.title, e.type, e.location, e.description, e.startDate')
        ->where('e.isValidated = 1') // facultatif si tu veux filtrer
        ->getQuery()
        ->getArrayResult();
}
public function findFilteredForCalendar(string $metier): array
{
    return $this->createQueryBuilder('e')
        ->select('e.id, e.title, e.startDate, e.location, e.type, e.description')
        ->where('e.type = :communautaire OR e.type = :metier')
        ->setParameter('communautaire', 'Communautaire')
        ->setParameter('metier', $metier)
        ->getQuery()
        ->getArrayResult();
}
// src/Repository/EventRepository.php

public function findByProfession(string $profession): array
{
    return $this->createQueryBuilder('e')
        ->join('e.organisateur', 'u') // Assure-toi que l'événement a un organisateur de type User
        ->where('u.metier = :metier')
        ->setParameter('metier', $profession)
        ->orderBy('e.date', 'ASC')
        ->getQuery()
        ->getResult();
}
}