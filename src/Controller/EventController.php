<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Filter;
use App\Form\CreateEventFormType;
use App\Form\FilterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filter = new Filter();
        $form = $this->createForm(FilterType::class, $filter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $queryBuilder = $entityManager->getRepository(Event::class)->createQueryBuilder('e');

            // Filtrage basé sur le campus
            if ($data->getCampus()) {
                $queryBuilder->andWhere('e.campus = :campus')
                    ->setParameter('campus', $data->getCampus());
            }

            // Filtrage basé sur l'événement (nom)
            if ($data->getEvent()) {
                $queryBuilder->andWhere('e.name LIKE :event')
                    ->setParameter('event', '%' . $data->getEvent() . '%');
            }

            // Filtrage basé sur la date
            if ($data->getDate()) {
                $queryBuilder->andWhere('e.date = :date')
                    ->setParameter('date', $data->getDate());
            }

            // Filtrage basé sur le checkbox eventCheckb
            if ($data->isEventCheckb()) {
                $queryBuilder->andWhere('e.isEvent = :isEvent')
                    ->setParameter('isEvent', true);
            }

            $events = $queryBuilder->getQuery()->getResult();
        } else {
            $events = $entityManager->getRepository(Event::class)->findAll();
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'filterForm' => $form->createView(),
        ]);
    }
    #[Route('/create', name: 'app_event_create')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(CreateEventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();
            return $this->redirectToRoute('app_event');
        }
        return $this->render('event/create.html.twig', [
            'createEventForm' => $form,
        ]);
    }
}

