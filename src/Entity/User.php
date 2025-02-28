<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d’utilisateur est déjà utilisé.')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le prénom est requis.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÿ\s\-]+$/u',
        message: "Le prénom ne peut contenir que des lettres et des tirets."
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le nom est requis.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÿ\s\-]+$/u',
        message: "Le nom ne peut contenir que des lettres et des tirets."
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Regex(
        pattern: '/^(\+?\d{1,3}[-.\s]?)?(\d{2,3}[-.\s]?)?(\d{2,3}[-.\s]?\d{2,3}[-.\s]?\d{2,4})$/',
        message: "Le numéro de téléphone est invalide."
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?bool $administrator = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $active = false; // Par défaut inactif jusqu'à confirmation

    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'users')]
    private Collection $events;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est requis.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z][a-zA-Z0-9._]{2,19}$/',
        message: "Le nom d'utilisateur doit contenir entre 3 et 20 caractères, avec uniquement des lettres, chiffres, points et underscores."
    )]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $confirmationTokenExpiresAt = null; // Date d'expiration du token

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->roles = ['ROLE_USER']; // Assigne par défaut ROLE_USER
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
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
        return (string) $this->email ?? ''; // Sécurisé pour éviter les erreurs si email = null
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function isAdministrator(): ?bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): static
    {
        $this->administrator = $administrator;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    public function getConfirmationTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->confirmationTokenExpiresAt;
    }

    public function setConfirmationTokenExpiresAt(?\DateTimeInterface $confirmationTokenExpiresAt): static
    {
        $this->confirmationTokenExpiresAt = $confirmationTokenExpiresAt;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Supprime les données sensibles si nécessaire
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }
}
