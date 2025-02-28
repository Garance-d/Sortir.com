<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $user->setAdministrator(false);
            $user->setActive(true);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    #[Route('/update/{id}', name: 'app_update', requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'utilisateur par ID
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            // Si l'utilisateur n'existe pas, rediriger ou afficher un message d'erreur
            return $this->redirectToRoute('app_register');  // Redirection vers la page d'inscription par exemple
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un nouveau mot de passe a été renseigné
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                // Si un mot de passe a été fourni, on le hache et on le met à jour
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            // Enregistrer les autres informations sans toucher au mot de passe si celui-ci n'a pas été modifié
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger vers la page de connexion ou autre page après mise à jour
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/update.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
