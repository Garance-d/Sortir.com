<?php


namespace App\Controller;

use App\Entity\Event;
use App\Entity\Filter;
use App\Entity\User;
use App\Form\CreateEventFormType;
use App\Form\FilterType;
use App\Repository\EventRepository;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {

        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        // Récupération des valeurs du formulaire
        $filters = $form->getData();

        // Construction de la requête en fonction des filtres
        $queryBuilder = $eventRepository->createQueryBuilder('e')
            ->leftJoin('e.host', 'o')
            ->leftJoin('o.campus', 'c');

        if ($form->isSubmitted() && $form->isValid()) {

            if ($filters['campus']) {
                $queryBuilder->andWhere('c = :campus')
                    ->setParameter('campus', $filters['campus']);
            }

            if (!empty($filters['eventName'])) {
                $queryBuilder->andWhere('e.name LIKE :eventName')
                    ->setParameter('eventName', '%'.$filters['eventName'].'%');
            }

            if ($filters['date']) {
                $queryBuilder->andWhere('e.startAt = :date')
                    ->setParameter('date', $filters['date']);
            }

            if ($filters['eventCheckb']) {
                $queryBuilder->andWhere(':user MEMBER OF e.users')
                    ->setParameter('user', $this->getUser());
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
    public function show(Event $event, EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'location' => $event->getLocation(),
            'users' => $users,
        ]);
    }

    #[Route('/event/{id}/join/{idUser}', name: 'app_event_join', requirements: ['idUser' => '\d+', 'id' => '\d+'])]
    public function joinEvent(int $id, int $idUser, EntityManagerInterface $entityManager): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);
        $currentUser = $entityManager->getRepository(User::class)->find($idUser);

        if (!$event || !$currentUser) {
            throw $this->createNotFoundException("Événement ou utilisateur non trouvé.");
        }

        $event->addUser($currentUser);
        $entityManager->persist($event);
        $entityManager->flush();

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

        $event->removeUser($currentUser);
        $entityManager->persist($event);
        $entityManager->flush();

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

