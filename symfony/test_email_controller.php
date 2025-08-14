<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$transport = Transport::fromDsn('smtp://mailpit:1025');
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('support@demo.fr')
    ->to('test@example.com')
    ->subject('Test direct depuis AccountController')
    ->text('Test pour vérifier que l\'envoi d\'email fonctionne depuis AccountController');

try {
    $mailer->send($email);
    echo "✅ Email de test envoyé avec succès!\n";
} catch (\Exception $e) {
    echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
}
