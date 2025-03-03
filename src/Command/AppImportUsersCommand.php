<?php

namespace App\Command;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[AsCommand(
    name: 'app:import-users',
    description: 'Import users from a CSV file using the Symfony Serializer',
)]
class AppImportUsersCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private Serializer $serializer;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

        // Configuration du Serializer
        $this->serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csvFile', InputArgument::REQUIRED, 'Path to the CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvFile = $input->getArgument('csvFile');

        if (!file_exists($csvFile)) {
            $io->error("The file '$csvFile' does not exist.");
            return Command::FAILURE;
        }

        // Lire et désérialiser le fichier CSV en tableau d'objets
        $csvContent = file_get_contents($csvFile);
        $usersData = $this->serializer->decode($csvContent, 'csv');

        foreach ($usersData as $data) {
            $user = new User();

            // Hydrate les informations de l'utilisateur
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            $user->setRoles(explode(',', $data['roles']));
            $user->setAdministrator((bool) $data['administrator']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setActive((bool) $data['active']);

            $campus = $this->entityManager->getRepository(Campus::class)->find($data['campus']);
            if ($campus) {
                $user->setCampus($campus);
            } else {
                $io->warning("Campus ID '{$data['campus']}' not found for user '{$data['email']}'.");
            }

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
        $io->success('Users imported successfully!');
        return Command::SUCCESS;
    }
}
