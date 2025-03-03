<?php


namespace App\Controller;

use App\Entity\Event;
use App\Entity\Filter;
use App\Entity\Location;
use App\Entity\User;
use App\Form\CreateEventFormType;
use App\Form\FilterType;
use App\Repository\EventRepository;
use App\Repository\FilterRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {

        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        $filters = $form->getData();

        $queryBuilder = $eventRepository->createQueryBuilder('e')
            ->leftJoin('e.host', 'o')
            ->leftJoin('o.campus', 'c');

        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->modify('-1 month');

        if ($form->isSubmitted() && $form->isValid()) {

            if ($filters['eventCheckbArchive']) {
                // Montrer UNIQUEMENT les événements archivés (plus vieux qu'un mois)
                $queryBuilder->andWhere('e.startAt < :oneMonthAgo')
                    ->setParameter('oneMonthAgo', $oneMonthAgo);
            } else {
                // Comportement par défaut: exclure les événements archivés
                $queryBuilder->andWhere('e.startAt > :oneMonthAgo')
                    ->setParameter('oneMonthAgo', $oneMonthAgo);

                if ($filters['campus']) {
                    $queryBuilder->andWhere('c = :campus')
                        ->setParameter('campus', $filters['campus']);
                }

                if (!empty($filters['eventName'])) {
                    $queryBuilder->andWhere('e.name LIKE :eventName')
                        ->setParameter('eventName', '%'.$filters['eventName'].'%');
                }

                if ($filters['date']) {
                    $startOfDay = clone $filters['date'];
                    $startOfDay->setTime(0, 0, 0);

                    $endOfDay = clone $filters['date'];
                    $endOfDay->setTime(23, 59, 59);

                    $queryBuilder->andWhere('e.startAt BETWEEN :start_date AND :end_date')
                        ->setParameter('start_date', $startOfDay)
                        ->setParameter('end_date', $endOfDay);
                }

                if ($filters['eventCheckbUser']) {
                    $queryBuilder->andWhere(':user MEMBER OF e.users')
                        ->setParameter('user', $this->getUser());
                }

                if ($filters['eventCheckbHost']) {
                    $queryBuilder->andWhere('e.host = :host')
                        ->setParameter('host', $this->getUser());
                }
            }

            $events = $queryBuilder->getQuery()->getResult();

        } else {
            // Sans filtre, n'afficher que les événements non archivés
            $queryBuilder->andWhere('e.startAt > :oneMonthAgo')
                ->setParameter('oneMonthAgo', $oneMonthAgo);

            $events = $queryBuilder->getQuery()->getResult();
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'filterForm' => $form->createView(),
        ]);
    }
    #[Route('/create/{id}', name: 'app_event_create', requirements: ['id' => '\d+'])]
    public function createEvent(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $currentUser = $entityManager->getRepository(User::class)->find($id);

        $event->setHost($currentUser);

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

    #[Route('/event/{id}', name: 'app_event_show')]
    public function show(Event $event, EntityManagerInterface $entityManager, LocationRepository $locationRepository): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $location = $locationRepository->findAll();
        $map = (new Map())

            ->fitBoundsToMarkers();


        // With an info window associated to the marker:

        foreach ($location as $location) {
        $map->addMarker(new Marker(
            position: new Point($location->getLatitude(), $location->getLongitude()),
            title: $location->getName(),
            infoWindow: new InfoWindow(
                headerContent: $event->getName(),
                content: $location->getStreet(),
                extra: [
                    'num_items' => 3,
                    'includes_link' => true,
                ],
            ),
            extra: [
                'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
            ],
        ));
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'map' => $map,
            'location' => $event->getLocation(),
            'users' => $users,
        ]);
    }

    #[Route('/event/{id}/join/{idUser}', name: 'app_event_join', requirements: ['idUser' => '\d+', 'id' => '\d+'])]
    public function joinEvent(int $id, int $idUser, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);
        $currentUser = $entityManager->getRepository(User::class)->find($idUser);

        if (!$event || !$currentUser) {
            throw $this->createNotFoundException("Événement ou utilisateur non trouvé.");
        }

        $today = new \DateTime();

        if ($event->getRegistrationEndsAt() >= $today) {
            $event->addUser($currentUser);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez été inscrit à l\'événement avec succès !');
        } else {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée. Vous ne pouvez plus vous inscrire à cet événement.');
        }

        return $this->redirectToRoute('app_event_show', [
            'id' => $id,
        ]);
    }

    #[Route('/event/{id}/quit/{idUser}', name: 'app_event_quit', requirements: ['idUser' => '\d+', 'id' => '\d+'])]
    public function quitEvent(int $id, int $idUser, EntityManagerInterface $entityManager): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);
        $currentUser = $entityManager->getRepository(User::class)->find($idUser);

        if (!$event || !$currentUser) {
            throw $this->createNotFoundException("Événement ou utilisateur non trouvé.");
        }

        $today = new \DateTime();

        if ($event->getStartAt() > $today) {
            $event->removeUser($currentUser);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez été inscrit à l\'événement avec succès !');
        } else {
            $this->addFlash('error', 'L\'évènement a commencé. Vous ne pouvez plus vous désister de cet événement.');
        }


        return $this->redirectToRoute('app_event_show', [
            'id' => $id,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'app_event_edit')]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreateEventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_event');
        }

        return $this->render('event/edit.html.twig', [
            'editEventForm' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/delete', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour éviter les suppressions non sécurisées
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event');
    }
}

