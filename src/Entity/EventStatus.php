<?php

namespace App\Entity;

use App\Repository\EventStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[ORM\Entity(repositoryClass: EventStatusRepository::class)]
class EventStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'status')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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
            $event->setStatus($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {

            if ($event->getStatus() === $this) {
                $event->setStatus(null);
            }
        }

        return $this;
    }
//    public function prePersist(Event $event, LifecycleEventArgs $args): void // git ia
//    {
//        // Lors de la création de l'évènement, l'évènement passe en "OPEN"
//        $event->setStatus('OPEN');
//    }
//
//    public function preUpdate(Event $event, LifecycleEventArgs $args): void //git ia
//    {
//        // Met à jour le statut de l'événement en fonction des conditions spécifiées
//        $event->updateStatus();
//    }
}
