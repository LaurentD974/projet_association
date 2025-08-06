<?php

namespace App\Entity;

use App\Repository\ReferentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferentRepository::class)]
class Referent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $referent = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $responsableDe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferent(): ?User
    {
        return $this->referent;
    }

    public function setReferent(?User $referent): self
    {
        $this->referent = $referent;
        return $this;
    }

    public function getResponsableDe(): ?User
    {
        return $this->responsableDe;
    }

    public function setResponsableDe(?User $responsableDe): self
    {
        $this->responsableDe = $responsableDe;
        return $this;
    }
}