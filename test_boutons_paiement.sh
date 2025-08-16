#!/bin/bash

echo "🧪 Test des boutons de paiement après échec"
echo "==========================================="
echo ""

echo "📋 Instructions de test :"
echo "1. Ouvrir http://localhost:8080"
echo "2. Cliquer sur un bouton 'Acheter'"
echo "3. Remplir l'email"
echo "4. Utiliser une carte RÉELLE (pas de test) pour provoquer l'erreur :"
echo "   - Numéro: 4111111111111111 (vraie carte, sera refusée en mode test)"
echo "   - Date: 12/28"
echo "   - CVC: 123"
echo "5. Remplir l'adresse de facturation"
echo "6. Cliquer sur 'Payer maintenant'"
echo "7. ❌ L'erreur devrait apparaître: 'Votre carte a été refusée...'"
echo "8. ✅ Tester que le bouton 'Réessayer' fonctionne"
echo "9. ✅ Fermer le modal et tester que les boutons 'Acheter' fonctionnent"
echo ""

echo "🎯 Cartes de test Stripe (qui devraient FONCTIONNER) :"
echo "   - 4242424242424242 (Visa - succès)"
echo "   - 4000000000000002 (Visa - échec générique)"
echo "   - 4000000000009995 (Visa - fonds insuffisants)"
echo ""

echo "🚨 Symptômes du bug (corrigés) :"
echo "   - Bouton 'Réessayer' ne réagit pas après une erreur"
echo "   - Boutons 'Acheter' sur la page ne réagissent plus"
echo "   - Nécessité de rafraîchir la page"
echo ""

echo "✅ Corrections apportées :"
echo "   - Réactivation globale de tous les boutons après erreur"
echo "   - Gestionnaires d'événements robustes"
echo "   - Nettoyage complet des états de chargement"
echo "   - Protection contre la désactivation involontaire"
echo ""

# Vérifier que l'application est en cours d'exécution
if curl -s http://localhost:8080 > /dev/null; then
    echo "🟢 Application accessible sur http://localhost:8080"
else
    echo "🔴 Application non accessible. Lancer avec:"
    echo "   cd /home/pearce/dev/MyBlogSymfony && docker-compose up -d"
fi
