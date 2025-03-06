<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerService $mailerService, JWTService $jwt, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setAdministrator(false);
            $user->setRoles(['ROLE_USER']);
            $user->setActive(false);
            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                // Génère un nouveau nom pour la photo de profil
                // D'abord on récupère le nom original de la photo
                $originalFilename = pathinfo($profilePicture->getClientOriginalName(), PATHINFO_FILENAME);
                // Ensuite on le passe dans un slugger qui remplace tous les caractères spéciaux et les espaces par '-' pour avoir un nom de fichier "propre"
                $safeFilename = $slugger->slug($originalFilename);
                // Enfin on le rend unique en créant un id associé à la photo avec la fonction uniqid()
                // Cela donne un nom de fichier sous la forme : ma-photo-de-profil603d8a6b6d007.jpg
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profilePicture->guessExtension();

                // Déplace le fichier dans le répertoire public/uploads
                try {
                    $profilePicture->move(
                        $this->getParameter('profile_pictures_directory'), // Le dossier de destination
                        $newFilename
                    );
                } catch (FileException $e) {
                    // En cas d'erreur (par exemple, problème de permission ou fichier trop grand)
                    $this->addFlash('error', 'Il y a eu un problème lors de l\'upload de votre photo de profil.');
                    return $this->redirectToRoute('register');
                }
                // Met à jour le chemin de l'image dans la base de données
                $user->setProfilePicture($newFilename);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            $payload = [
                'user_id' => $user->getId()
            ];
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
            $expiration = new \DateTime('+3 hour'); // Expiration dans 1 heure
            $user->setConfirmationToken($token);
            $user->setConfirmationTokenExpiresAt($expiration);
            $entityManager->flush(); // Met à jour en base
            $mailerService->sendConfirmationEmail(
                'no-reply@sortir.com',
                $user->getEmail(),
                'Activation de votre compte sur Sortir.com',
                'confirmation',
                ['user' => $user, 'token' => $token] // Contexte sous forme de tableau
            );
            $this->addFlash('success', 'Un e-mail de confirmation a été envoyé. Veuillez vérifier votre boîte mail.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile', requirements: ['id' => '\d+'])]
    public function profile(int $id, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'profile_id' => $id,
        ]);
    }

    #[Route('/update/{id}', name: 'app_update', requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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
                $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                // Génère un nouveau nom pour la photo de profil
                // D'abord on récupère le nom original de la photo
                $originalFilename = pathinfo($profilePicture->getClientOriginalName(), PATHINFO_FILENAME);
                // Ensuite on le passe dans un slugger qui remplace tous les caractères spéciaux et les espaces par '-' pour avoir un nom de fichier "propre"
                $safeFilename = $slugger->slug($originalFilename);
                // Enfin on le rend unique en créant un id associé à la photo avec la fonction uniqid()
                // Cela donne un nom de fichier sous la forme : ma-photo-de-profil603d8a6b6d007.jpg
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profilePicture->guessExtension();

                // Déplace le fichier dans le répertoire public/uploads
                try {
                    $profilePicture->move(
                        $this->getParameter('profile_pictures_directory'), // Le dossier de destination
                        $newFilename
                    );
                } catch (FileException $e) {
                    // En cas d'erreur (par exemple, problème de permission ou fichier trop grand)
                    $this->addFlash('error', 'Il y a eu un problème lors de l\'upload de votre photo de profil.');
                    return $this->redirectToRoute('register');
                }

                // Met à jour le chemin de l'image dans la base de données
                $user->setProfilePicture($newFilename);
            }
            // Enregistrer les autres informations sans toucher au mot de passe si celui-ci n'a pas été modifié
            $entityManager->persist($user);
            $entityManager->flush();
            // Rediriger vers la page de connexion ou autre page après mise à jour
            return $this->redirectToRoute('app_login');
        }
        return $this->render('profile/update.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/update/delete-photo/{id}', name: 'app_delete_photo')]
    public function deletePhoto(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur correspondant à l'ID dans l'URL
        $user = $entityManager->getRepository(User::class)->find($id);
        // Récupérer l'utilisateur actuellement connecté
        $currentUser = $this->getUser();
        dump($currentUser);
        if ($currentUser === $user) {
            // Récupérer le nom du fichier de la photo
            $profilePicture = $user->getProfilePicture();
            // Supprimer le fichier de la photo s'il existe
            if ($profilePicture) {
                // On s'assure que le dossier où les images sont stockées est correct
                $fileSystem = new Filesystem();
                $profilePicturePath = $this->getParameter('profile_pictures_directory') . '/' . $profilePicture;
                if ($fileSystem->exists($profilePicturePath)) {
                    $fileSystem->remove($profilePicturePath);
                    // Mettre à jour l'entité User (supprimer la photo de profil)
                    $user->setProfilePicture(null);
                    // Sauvegarder les changements dans la base de données
                    $entityManager->persist($user);
                    $entityManager->flush();
                } else {
                    $this->addFlash('error', 'La photo que vous essayez de supprimer n\'existe pas');
                }
            }
        }
        // Rediriger vers la page de profil
        return $this->redirectToRoute('app_profile', ['id' => $id]);
    }

    #[Route('/confirm/{token}', name: 'app_confirm')]
    public function confirm(string $token, JWTService $jwt, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);
            if ($user) {
                // Vérifie si le token a expiré
                if ($user->getConfirmationTokenExpiresAt() < new \DateTime()) {
                    $this->addFlash('danger', 'Votre lien de confirmation a expiré.');
                    return $this->redirectToRoute('app_register');
                }
                if (!$user->isActive()) {
                    $user->setActive(true);
                    $user->setConfirmationToken(null); // Supprime le token après activation
                    $em->flush();
                    $this->addFlash('success', 'Votre compte est activé. Vous pouvez maintenant vous connecter.');
                    return $this->redirectToRoute('app_login');
                }
            }
        }

        $this->addFlash('danger', 'Le lien de confirmation est invalide ou a expiré.');
        return $this->redirectToRoute('app_register');
    }
}
