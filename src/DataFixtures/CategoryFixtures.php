<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $event = new Event();
        $event->setName('Nom de la sortie');
        $event->setDateCreated(new \DateTimeImmutable());
        $event->persist($event);

        $category2 = new Category();
        $category2->setName('Date de la sortie');
        $category2->setDateCreated(new \DateTimeImmutable());
        $manager->persist($category2);

        $category3 = new Category();
        $category3->setName('Clôture');
        $category3->setDateCreated(new \DateTimeImmutable());
        $manager->persist($category2);

        $category4 = new Category();
        $category4->setName('inscrits/places');
        $category4->setDateCreated(new \DateTimeImmutable());
        $manager->persist($category2);

        $category5 = new Category();
        $category5->setName('Etat');
        $category5->setDateCreated(new \DateTimeImmutable());
        $manager->persist($category2);

        $category6 = new Category();
        $category6->setName('Inscrit');
        $category6->setDateCreated(new \DateTimeImmutable());
        $manager->persist($category2);

        $user = new User();
        $user->setName('Organisateur');
        $user->setDateCreated(new \DateTimeImmutable());
        $manager->persist($user);

        $category8 = new Category();
        $category8->setName('Actions');
        $category8->setDateCreated(new \DateTimeImmutable());
        $manager->persist($category2);

        $manager->flush();
    }
}
