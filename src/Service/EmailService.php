<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from('ne-repondez-pas@freedomsoftwarepro.com.com') // L'email de l'expéditeur
            ->to($to) // Le destinataire
            ->subject($subject) // Le sujet
            ->html($body); // Le contenu HTML

        $this->mailer->send($email);
    }
}
