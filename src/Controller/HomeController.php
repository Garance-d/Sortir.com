<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(LocationRepository $locationRepository): Response
    {
                $locations = $locationRepository->findAll();
        $map = (new Map())
            ->fitBoundsToMarkers();
        foreach ($locations as $location) {
        $map->addMarker(new Marker(
            position: new Point($location->getLatitude(), $location->getLongitude()),
            title: $location->getName(),
            infoWindow: new InfoWindow(
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
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'map' => $map,
        ]);
    }

    #[Route('/site', name: 'app_sites')]
    public function site(): Response
    {
        return $this->render('home/sites.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/profil', name: 'app_profil')]
    public function profil(): Response
    {
        return $this->render('home/profil.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
