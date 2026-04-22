<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nbPlace = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureDeb = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureFin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateDeb = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateFin = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    private ?Activite $activite = null;

    /**
     * @var Collection<int, Inscrire>
     */
    #[ORM\OneToMany(targetEntity: Inscrire::class, mappedBy: 'session', cascade: ['remove'])]
    private Collection $inscrires;

    // 👇 CHANGEMENT ICI : Relation ManyToOne (Un seul prof)
    #[ORM\ManyToOne(targetEntity: Membre::class)]
    #[ORM\JoinColumn(nullable: true)] // Peut être null si pas encore de prof assigné
    private ?Membre $entraineur = null;

    public function __construct()
    {
        $this->inscrires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbPlace(): ?int
    {
        return $this->nbPlace;
    }

    public function setNbPlace(int $nbPlace): static
    {
        $this->nbPlace = $nbPlace;
        return $this;
    }

    public function getHeureDeb(): ?\DateTime
    {
        return $this->heureDeb;
    }

    public function setHeureDeb(\DateTime $heureDeb): static
    {
        $this->heureDeb = $heureDeb;
        return $this;
    }

    public function getHeureFin(): ?\DateTime
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTime $heureFin): static
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getDateDeb(): ?\DateTime
    {
        return $this->dateDeb;
    }

    public function setDateDeb(\DateTime $dateDeb): static
    {
        $this->dateDeb = $dateDeb;
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): static
    {
        $this->activite = $activite;
        return $this;
    }

    public function getInscrires(): Collection
    {
        return $this->inscrires;
    }

    public function addInscrire(Inscrire $inscrire): static
    {
        if (!$this->inscrires->contains($inscrire)) {
            $this->inscrires->add($inscrire);
            $inscrire->setSession($this);
        }
        return $this;
    }

    public function removeInscrire(Inscrire $inscrire): static
    {
        if ($this->inscrires->removeElement($inscrire)) {
            if ($inscrire->getSession() === $this) {
                $inscrire->setSession(null);
            }
        }
        return $this;
    }

    // 👇 NOUVEAUX GETTER / SETTER pour l'entraineur unique
    public function getEntraineur(): ?Membre
    {
        return $this->entraineur;
    }

    public function setEntraineur(?Membre $entraineur): static
    {
        $this->entraineur = $entraineur;
        return $this;
    }
}