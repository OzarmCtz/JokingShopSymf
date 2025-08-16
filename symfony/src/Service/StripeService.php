<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    public function __construct(
        private string $secretKey
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    public function createPaymentIntent(int $amount, string $currency = 'eur', array $metadata = []): PaymentIntent
    {
        try {
            return PaymentIntent::create([
                'amount' => $amount, // Le montant est déjà en centimes
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception('Erreur lors de la création du payment intent: ' . $e->getMessage());
        }
    }

    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Erreur lors de la récupération du payment intent: ' . $e->getMessage());
        }
    }

    public function confirmPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Erreur lors de la confirmation du payment intent: ' . $e->getMessage());
        }
    }
}
