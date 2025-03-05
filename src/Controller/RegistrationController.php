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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        MailerService               $mailerService,
        JWTService                  $jwt
    ): Response
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
            $user->setActive(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            $payload = [
                'user_id' => $user->getId(),
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwt_secret'));


            $user->setConfirmationToken($token);
            $user->setConfirmationTokenExpiresAt(new \DateTime('+24 hours'));
            $entityManager->flush();

            $mailerService->sendConfirmationEmail(
                'no-reply@sortir.com',
                $user->getEmail(),
                'Activation de votre compte sur Sortir.com',
                'confirmation',
                compact('user', 'token')
            );

            $this->addFlash('success', 'Un e-mail de confirmation a été envoyé. Veuillez vérifier votre boîte mail.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    #[Route('/update/{id}', name: 'app_update', requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_register');
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/update.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/confirm/{token}', name: 'app_confirm')]
    public function confirm(string $token, JWTService $jwt, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwt_secret'))) {
            $payload = $jwt->getPayload($token);
            ////
            if (!isset($payload['user_id'])) {
                $this->addFlash('danger', 'Le token est invalide.');
                return $this->redirectToRoute('app_register');
            }

            $user = $userRepository->find($payload['user_id']);

            if (!$user->isActive()) {
                $user->setActive(true);
                $user->setConfirmationToken(null);
                $em->flush();

                $this->addFlash('success', 'Votre compte est activé. Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            }
        }
        $this->addFlash('danger', 'Le lien de confirmation est invalide ou a expiré.');
        return $this->redirectToRoute('app_register');
    }
}