<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
//    public const STATUS_LABELS = ['OPEN', 'CLOSED', 'CANCELLED', 'ONGOING', 'DONE']; // git ia

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull()] // git ia
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
//    #[Assert\NotNull()] // git ia
//    #[Assert\Positive()] // git ia
    private ?int $duration = null;

    #[ORM\Column]
//    #[Assert\NotNull()] // git ia
//    #[Assert\LessThan(propertyPath: 'startAt')] // git ia
    private ?\DateTimeImmutable $registrationEndsAt = null;

    #[ORM\Column]
    private ?int $maxUsers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'events', cascade: ["persist"])]
    private ?Location $location = null;


    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'events')]
    private Collection $users;

    public function getUserCount(): int
    {
        return count($this->users);
    }

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?EventStatus $status = null;

    #[ORM\ManyToOne(inversedBy: 'eventsHost')]
    private ?User $host = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
//        $this->status = 'OPEN';  // Lors de la création de l'évènement, l'évènement passe en "OPEN" // git ia
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationEndsAt(): ?\DateTimeImmutable
    {
        return $this->registrationEndsAt;
    }

    public function setRegistrationEndsAt(\DateTimeImmutable $registrationEndsAt): static
    {
        $this->registrationEndsAt = $registrationEndsAt;

        return $this;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(int $maxUsers): static
    {
        $this->maxUsers = $maxUsers;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addEvent($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeEvent($this);
        }

        return $this;
    }

    public function getStatus(): ?EventStatus
    {
        return $this->status;
    }

    public function setStatus(?EventStatus $status): static
    {
//        if (!in_array($status, self::STATUS_LABELS)) { //git ia
//            throw new \InvalidArgumentException("Invalid status value"); // git ia
//        }
        $this->status = $status;

        return $this;
    }

    public function getHost(): ?User
    {
        return $this->host;
    }

    public function setHost(?User $host): static
    {
        $this->host = $host;

        return $this;
    }

//    public function updateStatus(): void // git ia
//    {
//        $now = new \DateTimeImmutable();
//
//        if ($this->status !== 'CANCELLED') {
//            if ($this->registrationEndsAt <= $now || $this->getUsers()->count() >= $this->maxUsers) {
//                $this->status = 'CLOSED';
//            } elseif ($this->startAt <= $now && $now <= $this->startAt->modify('+' . $this->duration . ' minutes')) {
//                $this->status = 'ONGOING';
//            } elseif ($this->startAt->modify('+' . $this->duration . ' minutes') < $now) {
//                $this->status = 'DONE';
//            }
//        }
//    }
//
//    public function cancelEvent(): void // git ia
//    {
//        $this->status = 'CANCELLED';
//    }


}
