#!/bin/bash

# Script d'aide pour les opérations de développement
set -e

case "$1" in
    "start")
        echo "🚀 Démarrage de l'environnement..."
        docker compose up -d
        ;;
    "stop")
        echo "🛑 Arrêt de l'environnement..."
        docker compose down
        ;;
    "restart")
        echo "🔄 Redémarrage de l'environnement..."
        docker compose restart
        ;;
    "logs")
        if [ -z "$2" ]; then
            docker compose logs -f
        else
            docker compose logs -f "$2"
        fi
        ;;
    "console")
        shift
        docker compose exec php php bin/console "$@"
        ;;
    "composer")
        shift
        docker compose exec php composer "$@"
        ;;
    "php")
        shift
        docker compose exec php php "$@"
        ;;
    "migrate")
        echo "🔄 Exécution des migrations..."
        docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
        ;;
    "cache-clear")
        echo "🧹 Nettoyage du cache..."
        docker compose exec php php bin/console cache:clear
        ;;
    "status")
        echo "📊 Statut des containers:"
        docker compose ps
        ;;
    "build")
        echo "🔨 Construction des images..."
        docker compose build --no-cache
        ;;
    "reset")
        echo "🔄 Reset complet de l'environnement..."
        docker compose down --volumes
        docker compose build --no-cache
        docker compose up -d
        sleep 30
        docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
        docker compose restart worker
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|logs [service]|console [command]|composer [command]|php [command]|migrate|cache-clear|status|build|reset}"
        echo ""
        echo "Exemples:"
        echo "  $0 start              # Démarrer l'environnement"
        echo "  $0 logs php           # Voir les logs PHP"
        echo "  $0 console debug:router # Lister les routes"
        echo "  $0 composer install   # Installer les dépendances"
        echo "  $0 migrate            # Exécuter les migrations"
        echo "  $0 reset              # Reset complet"
        exit 1
        ;;
esac
