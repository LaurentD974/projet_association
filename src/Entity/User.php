<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Event;
use App\Entity\Entreprise;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $metier = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomProvince = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomCompagnon = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fonction1 = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fonction2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse2 = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codePostale = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $droit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(name: 'date_arrivee', type: 'date', nullable: true)]
    private ?DateTimeInterface $dateArrivee = null;

    #[ORM\Column(name: 'date_depart', type: 'date', nullable: true)]
    private ?DateTimeInterface $dateDepart = null;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $eventsParticipated;

    #[ORM\ManyToOne(targetEntity: Entreprise::class, inversedBy: 'users')]
    private ?Entreprise $entreprise = null;

    public function __construct()
    {
        $this->eventsParticipated = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void {}

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }

    public function getMetier(): ?string { return $this->metier; }
    public function setMetier(?string $metier): static { $this->metier = $metier; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }

    public function getPosition(): ?string { return $this->position; }
    public function setPosition(?string $position): static { $this->position = $position; return $this; }

    public function getNomProvince(): ?string { return $this->nomProvince; }
    public function setNomProvince(?string $nomProvince): static { $this->nomProvince = $nomProvince; return $this; }

    public function getNomCompagnon(): ?string { return $this->nomCompagnon; }
    public function setNomCompagnon(?string $nomCompagnon): static { $this->nomCompagnon = $nomCompagnon; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getFonction1(): ?string { return $this->fonction1; }
    public function setFonction1(?string $fonction1): static { $this->fonction1 = $fonction1; return $this; }

    public function getFonction2(): ?string { return $this->fonction2; }
    public function setFonction2(?string $fonction2): static { $this->fonction2 = $fonction2; return $this; }

    public function getAdresse1(): ?string { return $this->adresse1; }
    public function setAdresse1(?string $adresse1): static { $this->adresse1 = $adresse1; return $this; }

    public function getAdresse2(): ?string { return $this->adresse2; }
    public function setAdresse2(?string $adresse2): static { $this->adresse2 = $adresse2; return $this; }

    public function getCodePostale(): ?string { return $this->codePostale; }
    public function setCodePostale(?string $codePostale): static { $this->codePostale = $codePostale; return $this; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): static { $this->ville = $ville; return $this; }

    public function getDroit(): ?string { return $this->droit; }
    public function setDroit(?string $droit): static { $this->droit = $droit; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }

    public function getDateArrivee(): ?DateTimeInterface { return $this->dateArrivee; }
    public function setDateArrivee(?DateTimeInterface $date_arrivee): static { $this->dateArrivee = $date_arrivee; return $this; }

    public function getDateDepart(): ?DateTimeInterface { return $this->dateDepart; }
    public function setDateDepart(?DateTimeInterface $date_depart): static { $this->dateDepart = $date_depart; return $this; }

    public function getEntreprise(): ?Entreprise { return $this->entreprise; }
    public function setEntreprise(?Entreprise $entreprise): self { $this->entreprise = $entreprise; return $this; }

    public function getEventsParticipated(): Collection { return $this->eventsParticipated; }

    public function addEventParticipated(Event $event): self
    {
        if (!$this->eventsParticipated->contains($event)) {
            $this->eventsParticipated->add($event);
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEventParticipated(Event $event): self
    {
        if ($this->eventsParticipated->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }
}