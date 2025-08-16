<?php

namespace App\Controller;

use App\Entity\Joke;
use App\Entity\Order;
use App\Entity\User;
use App\Service\JokeEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\StripeService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StripeService $stripeService,
        private MailerInterface $mailer,
        private JokeEmailService $jokeEmailService
    ) {}

    #[Route('/create-intent', name: 'payment_create_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $jokeId = $data['joke_id'] ?? null;
            $email = $data['email'] ?? null;

            if (!$jokeId || !$email) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }

            // Récupérer la blague
            $joke = $this->entityManager->getRepository(Joke::class)->find($jokeId);
            if (!$joke || !$joke->isActive()) {
                return new JsonResponse(['error' => 'Blague introuvable'], 404);
            }

            // Convertir le prix en centimes pour Stripe
            $priceInCents = (int) (floatval($joke->getPrice()) * 100);

            // Créer la commande AVANT le PaymentIntent
            $order = new Order();
            $order->setEmail($email);
            $order->setJoke($joke);
            $order->setAmount($joke->getPrice() * 100); // En centimes

            // Associer l'utilisateur connecté si disponible
            if ($this->getUser()) {
                $order->setUser($this->getUser());
            }

            // Créer le PaymentIntent avec Stripe AVANT de sauvegarder l'ordre
            $paymentIntent = $this->stripeService->createPaymentIntent(
                $joke->getPrice() * 100, // Prix en centimes
                'eur',
                [
                    'joke_title' => $joke->getTitle(),
                    'customer_email' => $email
                ]
            );

            // Sauvegarder l'ID du PaymentIntent dans l'ordre
            $order->setStripePaymentIntentId($paymentIntent->id);

            // Maintenant sauvegarder l'ordre avec le PaymentIntent ID
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            return new JsonResponse([
                'client_secret' => $paymentIntent->client_secret,
                'order_id' => $order->getId()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/confirm/{orderId}', name: 'payment_confirm', methods: ['POST'])]
    public function confirmPayment(int $orderId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $order = $this->entityManager->getRepository(Order::class)->find($orderId);
        if (!$order) {
            return new JsonResponse(['error' => 'Commande non trouvée'], 404);
        }

        try {
            // Vérifier que le paiement a bien été confirmé avec Stripe
            $paymentIntent = $this->stripeService->retrievePaymentIntent($order->getStripePaymentIntentId());

            if ($paymentIntent->status !== 'succeeded') {
                return new JsonResponse(['error' => 'Paiement non confirmé'], 400);
            }

            // Mettre à jour les informations de facturation
            $order->setCountry($data['country'] ?? null);
            $order->setAddress($data['address'] ?? null);
            $order->setCity($data['city'] ?? null);
            $order->setRegion($data['region'] ?? null);
            $order->setPostalCode($data['postal_code'] ?? null);
            $order->setCardHolderName($data['card_holder_name'] ?? null);

            // Extraire les 4 derniers chiffres de la carte depuis Stripe
            if (isset($paymentIntent->charges->data[0]->payment_method_details->card->last4)) {
                $order->setCardLast4($paymentIntent->charges->data[0]->payment_method_details->card->last4);
            }

            // Marquer comme payé
            $order->setStatus('paid');
            $order->setPaidAt(new \DateTime());

            $this->entityManager->flush();

            // Envoyer l'email avec la blague
            $this->jokeEmailService->sendJokeEmail($order);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la vérification du paiement: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/validate-card', name: 'payment_validate_card', methods: ['POST'])]
    public function validateCard(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cardNumber = $data['card_number'] ?? '';

        // Algorithme de Luhn pour valider le numéro de carte
        $isValid = $this->validateCardWithLuhn($cardNumber);

        return new JsonResponse(['valid' => $isValid]);
    }

    private function validateCardWithLuhn(string $cardNumber): bool
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = intval($cardNumber[$i]);

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10) === 0;
    }
}
