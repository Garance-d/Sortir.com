<?php

namespace App\Listener;

use App\Repository\EventStatusRepository;
use DateInterval;
use Doctrine\ORM\Event\PostLoadEventArgs;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class EventListener
{
    public function __construct(
        private readonly EventStatusRepository $eventStatusRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function postLoad(Event $event, PostLoadEventArgs $args): void
    {
        // Vérifier si le statut est 6, auquel cas on ne fait rien
        if ($event->getStatus() && $event->getStatus()->getId() === 6) {
            return;
        }

        $eventDuration = $event->getDuration(); // Durée en minutes
        $currentDate = new \DateTime(); // Aujourd'hui
        $currentDate->add(new DateInterval('PT1H')); // Ajoute 1 heure

        // Créer une copie de la date de début pour ne pas modifier l'original
        $startDate = clone $event->getStartAt();
        $endDate = (clone $startDate)->modify('+' . $eventDuration . ' minutes');

        // événement déjà commencé
        if ($startDate <= $currentDate) {
            // événement en cours
            if ($currentDate >= $startDate && $currentDate <= $endDate) {
                $status = $this->eventStatusRepository->find(4); // Statut "Activité en cours"
                $event->setStatus($status);
            }
            // Si l'événement est passé
            elseif ($currentDate > $endDate) {
                $status = $this->eventStatusRepository->find(5); // Statut "Passée"
                $event->setStatus($status);
            }
        }
        else {
            $status = $this->eventStatusRepository->find(1); // Statut "Créée"
            $event->setStatus($status);
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}