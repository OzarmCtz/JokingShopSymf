# 🚀 MyBlogSymfony - Déploiement Production v1.0

Application Symfony de blog avec système de blagues payantes et administration.

## 📋 Prérequis

- **AWS CLI** configuré avec vos clés d'accès
- **Docker** et **Docker Compose** (pour développement local)
- **Git** pour le déploiement

## ⚡ Déploiement Express (15 minutes)

### 1. Déploiement de démonstration

```bash
# Cloner le projet
git clone https://github.com/VotreUsername/MyBlogSymfony.git
cd MyBlogSymfony

# Lancer le déploiement automatique
./scripts/deploy-express-demo.sh
```

Cette commande va :
- ✅ Créer une instance EC2 t3.micro sur AWS
- ✅ Installer Docker et l'application automatiquement  
- ✅ Rendre le site accessible sur une IP publique
- ✅ Coût : ~2€/jour

### 2. Déploiement de l'application complète

```bash
# Déployer votre vraie application avec vos données
./scripts/deploy-real-app.sh
```

### 3. Nettoyage après démonstration

```bash
# Supprimer toutes les ressources AWS créées
./scripts/cleanup-express-demo.sh
```

## 🏗️ Architecture

### Démonstration
```
Internet → EC2 (t3.micro) → Docker → Nginx + PHP + SQLite
```

### Production recommandée
```
Internet → ALB → ECS Fargate → RDS MySQL
```

## 🔧 Configuration

### Variables d'environnement essentielles

Créer un fichier `.env.local` dans `/symfony/` :

```bash
# Environnement
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=your-secret-key-32-chars

# Base de données (SQLite pour démo, MySQL pour production)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
# ou pour MySQL : "mysql://user:password@host:3306/database"

# Email
MAILER_DSN=sendmail://default

# Stripe (paiements)
STRIPE_PUBLIC_KEY=pk_test_your_key
STRIPE_SECRET_KEY=sk_test_your_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

## 🐳 Développement Local

### Avec Docker

```bash
# Démarrer l'environnement
docker-compose up -d

# Installer les dépendances
docker-compose exec php composer install

# Créer la base de données
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate

# Accéder au site
http://localhost:8080

# Interface d'administration
http://localhost:8080/admin
```

### Commandes utiles

```bash
# Cache
docker-compose exec php php bin/console cache:clear

# Migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Console Symfony
docker-compose exec php php bin/console

# Logs
docker-compose logs -f
```

## 📊 Structure de l'Application

### Entités principales

- **Joke** : Blagues avec prix et catégorie
- **Category** : Catégories de blagues  
- **User** : Utilisateurs avec authentification
- **Order** : Commandes avec intégration Stripe
- **AppSettings** : Configuration de l'application

### Contrôleurs

- **ShopController** : Boutique et accueil
- **PaymentController** : Gestion des paiements Stripe
- **AccountController** : Compte utilisateur
- **Admin/** : Interface d'administration EasyAdmin

## 🔐 Sécurité

### Comptes administrateurs

```bash
# Créer un admin via console
docker-compose exec php php bin/console
```

### Permissions

- **ROLE_USER** : Utilisateur standard
- **ROLE_ADMIN** : Accès administration complète

## 💰 Coûts AWS Estimés

| Environnement | Type | Coût/jour | Coût/mois |
|---------------|------|-----------|-----------|
| **Démonstration** | t3.micro | ~2€ | ~60€ |
| **Production** | t3.small + RDS | ~4€ | ~120€ |

## 🎯 Fonctionnalités

### Front-end
- ✅ Boutique de blagues avec filtres
- ✅ Système de paiement Stripe
- ✅ Comptes utilisateurs
- ✅ Design responsive Bootstrap

### Administration
- ✅ Gestion des blagues et catégories
- ✅ Suivi des commandes
- ✅ Gestion des utilisateurs
- ✅ Configuration de l'application

### Technique
- ✅ Symfony 7.3
- ✅ PHP 8.3
- ✅ MySQL/SQLite
- ✅ Docker ready
- ✅ AWS compatible

## 🔧 Maintenance

### Surveillance

```bash
# Logs d'application
docker-compose logs -f php

# Logs Nginx
docker-compose logs -f nginx

# Monitoring système
htop
```

### Sauvegardes

```bash
# Backup base de données SQLite
cp symfony/var/data.db backup-$(date +%Y%m%d).db

# Backup MySQL
docker-compose exec db mysqldump -u symfony -psymfony symfony > backup.sql
```

## 📞 Support

### Logs importants

- Application : `symfony/var/log/`
- Nginx : `docker-compose logs nginx`
- PHP : `docker-compose logs php`

### Debugging

```bash
# Mode debug
APP_ENV=dev APP_DEBUG=1 in .env.local

# Profiler Symfony
http://localhost:8080/_profiler

# Logs en temps réel
tail -f symfony/var/log/prod.log
```

## 🚀 Mise en Production

1. **Configuration** : Mettre à jour `.env.local`
2. **Base de données** : Migrer vers MySQL/PostgreSQL
3. **HTTPS** : Configurer SSL/TLS
4. **Monitoring** : Mettre en place la surveillance
5. **Sauvegardes** : Automatiser les backups

---

**Version :** 1.0  
**Date :** Août 2025  
**Environnement :** Symfony 7.3 + Docker + AWS
