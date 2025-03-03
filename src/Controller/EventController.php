<?php


namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Filter;
use App\Entity\Location;

use App\Form\CreateEventFormType;
use App\Form\FilterType;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Void_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

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

    // Afficher le détail de l'événement
    #[Route('/event/{id}', name: 'app_event_show')]
    public function show(Event $event, LocationRepository $locationRepository): Response
    {
        $location = $locationRepository->findAll();
        $map = (new Map())
            ->fitBoundsToMarkers();


        // With an info window associated to the marker:

        foreach ($location as $location) {
            $map->addMarker(new Marker(
                position: new Point($location->getLatitude(), $location->getLongitude()),
                title: $location->getName(),

                infoWindow: new InfoWindow(
                    headerContent: '<b>' . $event->getName() . '</b>',
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
        ]);
    }

    // Modifier un événement
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

    // Supprimer un événement
    #[Route('/event/{id}/delete', name: 'app_event_delete')]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF pour éviter les suppressions non sécurisées
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {

            $entityManager->remove($event);
            $entityManager->flush();
        }

        {
            $transport = Transport::fromDsn('smtp://g.dev.informatique@gmail.com:djljvlbgzmdiazvr@smtp.gmail.com:587');
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from('g.dev.informatique@gmail.com')
                ->to('g.dev.informatique@gmail.com')
                ->cc('g.dev.informatique@gmail.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Votre évènement '.$event->getName().' de Sortir.com est supprimer')
                ->text('Votre évenement '.$event->getName().' à bien été supprimer')
                ->html('<p style="font-weight: normal">Votre évenement <span style="font-weight: bold">'.$event->getName().'</span> à bien été supprimer.</p> 
                    <p style="font-style: italic; font-weight: normal">Ceci est un message automatiquement envoyer.</p>
                    ');

            $mailer->send($email);

        }

        return $this->redirectToRoute('app_event');
    }

}