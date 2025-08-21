# URLs d'Administration - MyBlogSymfony

## 🔗 Liens directs

### Tableau de bord principal

- **Dashboard** : http://localhost:8080/admin

### Gestion du contenu

- **Catégories** : http://localhost:8080/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CCategoryCrudController
- **Blagues** : http://localhost:8080/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CJokeCrudController

### Commerce électronique

- **Commandes** : http://localhost:8080/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5COrderCrudController

### Gestion des utilisateurs

- **Utilisateurs** : http://localhost:8080/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CUserCrudController

### Site public

- **Accueil boutique** : http://localhost:8080/

## 🛠️ Commandes utiles

### Analytics et tests

```bash
# Tester les analytics
docker exec symfony_php_adeo_shop php bin/console app:test-analytics

# Générer des commandes de test
docker exec symfony_php_adeo_shop php bin/console app:generate-test-orders 10

# Vider le cache
docker exec symfony_php_adeo_shop php bin/console cache:clear
```

### Base de données

```bash
# Voir les commandes en base
docker exec symfony_php_adeo_shop php bin/console doctrine:query:sql "SELECT * FROM \`order\` LIMIT 10"

# Compter les commandes par statut
docker exec symfony_php_adeo_shop php bin/console doctrine:query:sql "SELECT status, COUNT(*) as count FROM \`order\` GROUP BY status"
```

## 📊 Métriques disponibles dans le dashboard

- **Catégories** : Nombre total de catégories
- **Blagues totales** : Nombre total de blagues
- **Blagues actives** : Nombre de blagues publiées
- **Utilisateurs** : Nombre total d'utilisateurs
- **Commandes totales** : Nombre total de commandes
- **Commandes payées** : Commandes avec statut payé/réussi
- **Revenus totaux** : Montant total des ventes
- **Revenus (7 jours)** : Revenus de la semaine

## 🔐 Accès admin

- **Email** : admin@admin.com (à vérifier selon votre configuration)
- **Accès requis** : ROLE_ADMIN

## 📱 Responsive

L'interface d'administration est optimisée pour :

- Desktop
- Tablettes
- Smartphones

Toutes les fonctionnalités sont accessibles sur tous les appareils.
