<?php


namespace App\Controller;

use App\Entity\Event;
use App\Entity\Filter;
use App\Form\CreateEventFormType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
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

    #[Route('/event/index', name: 'app_event_index')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(RegistrationFormType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
        }

        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

//    #[Route('/event/index', name: 'filtre', methods: ['GET'])]
//    public function new(Request $request, EntityManagerInterface $entityManager): Response
//    {
//
//        $filter = new Filter();
//
//        $filter->setCampus('Nantes');
//        $filter->setEvent('dezdez');
//        $filter->setDate(date('Y-m-d'));
//        $filter->setEventCheckb(is_bool(true));
//
//        dump($filter);
//
//
//        $filterForm = $this->createForm(RegistrationFormType::class, $filter);
//        $filterForm->handleRequest($request);
//
//        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
//
//            //$data = $form->getData();
//            $entityManager->persist($filter);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_event_index');
//
//        }
//
//        $filter = $entityManager->getRepository(Event::class)->findAll();
//
//        return $this->render('event/index.html.twig', [
//            'form' => $filterForm->createView(),
//            'events' => $filter,
//        ]);
//    }




}

