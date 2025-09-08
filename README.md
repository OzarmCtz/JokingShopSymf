## 🏗️ Développement Local

### Démarrage rapide

```bash
# Démarrage automatique de l'environnement complet
./start-dev.sh

# Ou manuellement :
docker-compose up -d
./dev.sh migrate
```

### Scripts utilitaires

```bash
# Script principal de développement
./dev.sh start              # Démarrer l'environnement
./dev.sh stop               # Arrêter l'environnement
./dev.sh restart            # Redémarrer l'environnement
./dev.sh logs [service]     # Voir les logs
./dev.sh console [command]  # Commandes Symfony
./dev.sh composer [command] # Commandes Composer
./dev.sh migrate            # Exécuter les migrations
./dev.sh cache-clear        # Nettoyer le cache
./dev.sh status             # Statut des containers
./dev.sh build              # Rebuilder les images
./dev.sh reset              # Reset complet

# Exemples d'utilisation
./dev.sh logs php           # Logs du container PHP
./dev.sh console debug:router # Lister les routes Symfony
./dev.sh composer require symfony/mailer # Ajouter une dépendance
```

### Services disponibles

- **Application Symfony** : http://localhost:8080
- **Mailpit (emails)** : http://localhost:8025
- **Base de données MySQL** : localhost:3306
  - Utilisateur : `symfony`
  - Mot de passe : `symfony`
  - Base : `symfony`

### Structure Docker

- **php** : Container PHP 8.3-FPM avec Symfony
- **nginx** : Serveur web Nginx
- **db** : Base de données MySQL 8
- **mailpit** : Interface de test pour emails
- **worker** : Worker Symfony Messenger

### Installation manuelle

```bash
# Cloner et démarrer
git clone [url]
cd JokingShopSymf
cp .env.example .env  # Ajuster UID/GID si nécessaire
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate

# Accès: http://localhost:8080
```

## 📊 Fonctionnalités

- 🛒 Boutique de blagues avec filtres
- 💳 Paiements Stripe sécurisés
- 👤 Comptes utilisateurs
- 📱 Design responsive
- 🎭 Administration complète

## 🛠️ Stack

- **Symfony 7.3** + PHP 8.3
- **SQLite** / MySQL
- **Docker** + Nginx
- **Stripe** API
- **EasyAdmin** 4

## 📚 Configuration

```bash
# Créer symfony/.env.local
APP_ENV=prod
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

# Accès
# Site: http://votre-ip
# Admin: http://votre-ip/admin
```

---

**Version :** 1.0.1 | **Serveur :** Debian/Ubuntu
