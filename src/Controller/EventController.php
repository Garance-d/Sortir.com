<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\Events;
use Doctrine\ORM\Query\Filter\SQLFilter;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/events')]
    public function index(): Response
    {
        return $this->render('pages/index.html.twig');
    }


}






