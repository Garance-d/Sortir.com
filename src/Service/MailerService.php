<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendConfirmationEmail(string $from, string $to, string $subject, string $template, array $context): void
    {
        // Création de l'email avec un template Twig
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("email/{$template}.html.twig") // Charge le bon fichier Twig
            ->context($context); // Passe les variables au template

        // Envoi de l'email
        $this->mailer->send($email);
    }
}
