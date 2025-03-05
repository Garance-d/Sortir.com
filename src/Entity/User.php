<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\PasswordStrength;

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
    #[Assert\NotNull(message: "Le prénom est requis.")]
    #[Assert\NotBlank(message: "Le prénom est requis.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÿ\s\-]+$/u',
        message: "Le prénom ne peut contenir que des lettres et des tirets."
    )]
    #[Assert\Length(
        min: 1,
        max: 30,
        minMessage: "The firstname cannot be longer than {{ limit }} characters.",
        maxMessage: "The firstname cannot be longer than {{ limit }} characters."
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotNull(message: "Le nom est requis.")]
    #[Assert\NotBlank(message: "Le nom est requis.")]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÿ\s\-]+$/u',
        message: "Le nom ne peut contenir que des lettres et des tirets."
    )]
    #[Assert\Length(
        min: 1,
        max: 30,
        minMessage: "The name must have at least {{ limit }} characters.",
        maxMessage: "The name can't have more than {{ limit }} characters."
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est requis.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z][a-zA-Z0-9._]{2,19}$/',
        message: "Le nom d'utilisateur doit contenir entre 3 et 20 caractères, avec uniquement des lettres, chiffres, points et underscores."
    )]    private ?string $username = null;

    #[ORM\Column(type:"string", length:255, nullable:true)]
    private $profilePicture;

    #[ORM\Column(length: 50)]
    #[Assert\NotNull(message: "L'email ne peut pas être nul.")]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide.')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._%+-]+@campus-eni\.fr$/',
        message: "Seuls les e-mails du domaine @campus-eni.fr sont autorisés."
    )]
    private ?string $email = null;

    #[ORM\Column(length: 15)]
    #[Assert\Regex(
        pattern: '/^(\+?\d{1,3}[-.\s]?)?(\d{2,3}[-.\s]?)?(\d{2,3}[-.\s]?\d{2,3}[-.\s]?\d{2,4})$/',
        message: "Le numéro de téléphone est invalide."
    )]
    private ?string $phone = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?bool $administrator = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\PasswordStrength([
        'minScore' => 1,  // Accepte tous les mots de passe
        'message' => 'Your password is too easy to guess.'
    ])]
//    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'users')]
    private Collection $events;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'host')]
    private Collection $eventsHost;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $confirmationTokenExpiresAt = null; // Date d'expiration du token

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
        $this->eventsHost = new ArrayCollection();
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

    public function setPhone(string $phone): static
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email ?? ''; // Sécurisé pour éviter les erreurs si email = null
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    /**
     * @param list<string> $roles
     */
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
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

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        $this->events->removeElement($event);

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    /**
     * @return mixed
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @param mixed $profilePicture
     */
    public function setProfilePicture($profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEventsHost(): Collection
    {
        return $this->eventsHost;
    }

    public function addEventsHost(Event $eventsHost): static
    {
        if (!$this->eventsHost->contains($eventsHost)) {
            $this->eventsHost->add($eventsHost);
            $eventsHost->setHost($this);
        }

        return $this;
    }

    public function removeEventsHost(Event $eventsHost): static
    {
        if ($this->eventsHost->removeElement($eventsHost)) {
            // set the owning side to null (unless already changed)
            if ($eventsHost->getHost() === $this) {
                $eventsHost->setHost(null);
            }
        }

        return $this;
    }
}
