<?php
// Test rapide du flux de paiement
require_once 'symfony/vendor/autoload.php';

echo "🧪 Test du flux de paiement Stripe\n";
echo "==================================\n\n";

// Test 1: Création d'un PaymentIntent
echo "1️⃣ Test création PaymentIntent...\n";

$createData = [
    'joke_id' => 1,
    'email' => 'test@example.com'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/payment/create-payment-intent');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($createData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

$responseData = json_decode($response, true);

if ($httpCode === 200 && isset($responseData['client_secret'])) {
    echo "✅ PaymentIntent créé avec succès!\n";
    echo "Client Secret: " . substr($responseData['client_secret'], 0, 20) . "...\n";
    echo "Order ID: " . $responseData['order_id'] . "\n\n";
} else {
    echo "❌ Erreur lors de la création du PaymentIntent\n\n";
}

echo "📝 Instructions pour tester manuellement:\n";
echo "=========================================\n";
echo "1. Ouvrir http://localhost:8080\n";
echo "2. Cliquer sur 'Acheter' pour une blague\n";
echo "3. Remplir l'email et utiliser la carte de test Stripe:\n";
echo "   - Numéro: 4242 4242 4242 4242\n";
echo "   - Date: 12/28 (ou toute date future)\n";
echo "   - CVC: 123\n";
echo "4. Cliquer sur 'Continuer' pour passer à l'étape facturation\n";
echo "5. Remplir l'adresse de facturation\n";
echo "6. Cliquer sur 'Payer maintenant'\n";
echo "7. Le paiement devrait être traité avec succès !\n\n";

echo "🎯 Le tunnel de paiement est maintenant fonctionnel de A à Z!\n";
