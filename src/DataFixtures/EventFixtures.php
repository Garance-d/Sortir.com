<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Event;
use App\Entity\EventStatus;
use App\Entity\City;
use App\Entity\Location;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EventFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $statuses = [];
        $statusLabels = ['OPEN', 'CLOSED', 'CANCELLED', 'ON GOING', 'DONE'];

        foreach ($statusLabels as $label) {
            $status = new EventStatus();
            $status->setLabel($label);
            $manager->persist($status);
            $statuses[] = $status;
        }

        $campuses = [];
        for ($i = 1; $i <= 5; $i++) {
            $campus = new Campus();
            $campus->setName($faker->city());
            $manager->persist($campus);
            $campuses[] = $campus;
        }

        $cities = [];
        for ($i = 0; $i < 10; $i++) {
            $city = new City();
            $city->setName($faker->city());
            $city->setPostalCode($faker->postcode());
            $manager->persist($city);
            $cities[] = $city;
        }

        $locations = [];
        for ($i = 0; $i < 5; $i++) {
            $location = new Location();
            $location->setName($faker->city);
            $location->setStreet($faker->streetAddress);
            $location->setLatitude($faker->latitude);
            $location->setLongitude($faker->longitude);
            $manager->persist($location);
            $locations[] = $location;
        }

        $manager->flush();

        $events = [];
        for ($i = 0; $i < 20; $i++) {
            $event = new Event();
            $event->setName($faker->words(3, true));
            $event->setDescription($faker->paragraph());
            $event->setStartAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
            $event->setDuration($faker->numberBetween(30, 60));
            $event->setRegistrationEndsAt(\DateTimeImmutable::createFromMutable($faker->dateTime()));
            $event->setMaxUsers($faker->numberBetween(1, 10));

            $event->setStatus($faker->randomElement($statuses));
            $event->setLocation($faker->randomElement($locations));  // Associer un lieu existant

            $manager->persist($event);

            $this->addReference('event_'.$i, $event);
        }

        $manager->flush();

        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setPhone($faker->phoneNumber());
            $user->setEmail($faker->email());
            $user->setUsername($faker->userName());

            $plainPassword = 'password123'; // Utilise une valeur fixe ou random

            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setAdministrator(false);
            $user->setActive(true);
            $campus = $faker->randomElement($campuses); // Prend un campus déjà créé
            $user->setCampus($campus);

            // Associer l'utilisateur à un événement aléatoire
            $eventReference = $this->getReference('event_' . $faker->numberBetween(0, 19), Event::class); // Prendre un événement existant
            // Ajouter l'utilisateur à l'événement
            $eventReference->addUser($user); // Exemple d'ajout d'un utilisateur à un événement

            $manager->persist($user);
            $users[] = $user;
        }
        $manager->flush();
    }
}


