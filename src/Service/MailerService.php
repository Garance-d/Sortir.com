<?php

namespace App\Service;



use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;
    public function __construct(MailerService $mailer, Environment $twig)
    {
       $this->mailer = $mailer;
       $this->twig = $twig;
    }

    public function sendConfirmationEmail(string $to, string $token): void
    {
        $email = (new Email())
            ->from('noreply@sortir.com')
            ->to($to)
            ->subject('Confirmation de votre compte')
            ->html($this->twig->render('email/confirmation.html.twig', ['token' => $token]));
        $this->mailer->send($email);
    }
}