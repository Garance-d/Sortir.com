<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $event = new Event();
            $event->setName('event '.$i);
            $event->setstartAt();
            $event->setregistrationEndsAt();
            $event->setmaxParticipants();
            $event->setstatus();
            $event->setid();
            $event->setdescription ();
            $event->setLocation();

            $manager->persist($event);
        }

        $admin = new Admin();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasherFactory->getPasswordHasher(Admin::class)->hash('admin'));
        $manager->persist($admin);

        $manager->flush();
    }
}
