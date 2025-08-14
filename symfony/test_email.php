<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require_once __DIR__ . '/vendor/autoload.php';

$transport = Transport::fromDsn('smtp://mailpit:1025');
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('test@example.com')
    ->to('recipient@example.com')
    ->subject('Test Email')
    ->text('This is a test email to verify Mailpit configuration.');

try {
    $mailer->send($email);
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
}
