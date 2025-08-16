<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class JokeEmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendJokeEmail(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@demo.fr', 'Boutique de Blagues'))
            ->to($order->getEmail())
            ->subject('Votre blague achetée : ' . $order->getJoke()->getTitle())
            ->htmlTemplate('emails/joke_purchase.html.twig')
            ->context([
                'order' => $order,
                'joke' => $order->getJoke(),
            ]);

        $this->mailer->send($email);
    }
}
