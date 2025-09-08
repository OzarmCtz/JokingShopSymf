#!/bin/bash

# Script de démarrage de l'environnement de développement
set -e

echo "🚀 Démarrage de l'environnement de développement JokingShop..."

# Arrêter les containers existants
echo "📦 Arrêt des containers existants..."
docker compose down

# Construire les images si nécessaire
echo "🔨 Construction des images Docker..."
docker compose build

# Démarrer les services
echo "🎯 Démarrage des services..."
docker compose up -d

# Attendre que la base de données soit prête
echo "⏳ Attente de la base de données..."
sleep 30

# Vérifier si les migrations sont nécessaires
echo "🔄 Vérification et exécution des migrations..."
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Redémarrer le worker si nécessaire
echo "🔄 Redémarrage du worker..."
docker compose restart worker

# Afficher le statut
echo "📊 Statut des containers:"
docker compose ps

echo ""
echo "✅ Environnement de développement prêt !"
echo "🌐 Application: http://localhost:8080"
echo "📧 Mailpit: http://localhost:8025"
echo "🗄️  Base de données: localhost:3306"
echo ""
echo "Commandes utiles:"
echo "  - Voir les logs: docker compose logs -f [service]"
echo "  - Console Symfony: docker compose exec php php bin/console [command]"
echo "  - Arrêter: docker compose down"
