# 🚀 MyBlogSymfony v1.0

Application Symfony de blog avec blagues premium, paiements Stripe et administration.

## ⚡ Déploiement Simple - Debian/Ubuntu (10 minutes)

```bash
# 1. Cloner le projet
git clone https://github.com/VotreUsername/MyBlogSymfony.git
cd MyBlogSymfony

# 2. Déployer sur serveur Debian
./scripts/deploy-debian.sh

# 3. Site accessible sur votre IP serveur !
```

## 🏗️ Développement Local

```bash
# Démarrer avec Docker
docker-compose up -d

# Installer dépendances
docker-compose exec php composer install

# Base de données MySQL + Données
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console doctrine:fixtures:load

# Accès: http://localhost:8080
```

## 📊 Fonctionnalités

- 🛒 Boutique de blagues avec filtres
- 💳 Paiements Stripe sécurisés
- 👤 Comptes utilisateurs
- 📱 Design responsive
- 🎭 Administration complète
- 📊 **Données préchargées** (catégories + blagues)## 🛠️ Stack

- **Symfony 7.3** + PHP 8.3
- **MySQL 8.0** (base de données)
- **Docker** + Nginx
- **Stripe** API
- **EasyAdmin** 4

## 📚 Configuration

```bash
# Créer symfony/.env.local
APP_ENV=prod
DATABASE_URL="mysql://symfony:symfony@127.0.0.1:3306/symfony"
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

# Accès
# Site: http://votre-ip:8080
# Admin: http://votre-ip:8080/admin
# Login: ozarmctz@proton.me / admin123
```

---

**Version :** 1.0.1 | **Serveur :** Debian/Ubuntu
