<?php

namespace App\Listener;

use App\Repository\EventStatusRepository;
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
        $eventDuration = $event->getDuration(); // Durée en minutes
        $currentDate = new \DateTime(); // Aujourd'hui

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
//        $this->entityManager->flush();
    }
}