<?php

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Roles;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
#[UniqueEntity(fields: ['login'], message: 'There is already an account with this login')]
class Membre implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ==========================================
    // PROPRIÉTÉS (Les colonnes de ta base)
    // ==========================================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $login = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Roles::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Roles $role = null;


    // ==========================================
    // MÉTHODES (Les Getters et Setters)
    // ==========================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // On garantit que chaque utilisateur a au moins le rôle par défaut
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    // Le Setter pour les rôles natifs de Symfony (nécessaire au bon fonctionnement)
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    // Notre fameux setRole unique avec la synchronisation !
    public function setRole(?Roles $role): static
{
    $this->role = $role;

    if ($role) {
        $libelle = mb_strtoupper($role->getLib(), 'UTF-8'); // 👈 mb_strtoupper au lieu de strtoupper
        
        if ($libelle === 'ADMIN' || $libelle === 'ADMINISTRATEUR') {
            $this->roles = ['ROLE_ADMIN'];
        } elseif ($libelle === 'ENTRAÎNEUR' || $libelle === 'ENTRAINEUR') {
            $this->roles = ['ROLE_ENTRAINEUR']; // 👈 forcé sans accent
        } else {
            $this->roles = ['ROLE_' . $libelle];
        }
    } else {
        $this->roles = [];
    }

    return $this;
}

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Nettoyage si besoin
    }
}