## 🏗️ Développement Local

```bash
# Démarrer avec Docker
docker-compose up -d

# Installer dépendances
docker-compose exec php composer install

# Base de données
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
