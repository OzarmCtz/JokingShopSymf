# Correction de Conversion des Montants - EasyAdmin Orders

## 🐛 Problème identifié

Les montants des commandes étaient affichés incorrectement dans l'interface d'administration :

- Prix réel d'une blague : **0.99€**
- Affichage incorrect dans EasyAdmin : **99.00€**

## 🔍 Cause du problème

1. **Stockage en base** : Les montants sont stockés en centimes dans la table `order`

   - Une blague à 0.99€ → stockée comme 99 centimes
   - PaymentController : `$order->setAmount($joke->getPrice() * 100)`

2. **Configuration EasyAdmin incorrecte** :

   - `MoneyField` configuré avec `setStoredAsCents(false)`
   - Les montants étaient traités comme des euros au lieu de centimes

3. **Calculs analytics incorrects** :
   - Les méthodes de calcul de revenus ne divisaient pas par 100
   - Revenus affichés 100x plus élevés que la réalité

## ✅ Corrections appliquées

### 1. OrderCrudController.php

```php
// AVANT
yield MoneyField::new('amount')
    ->setStoredAsCents(false);

// APRÈS
yield MoneyField::new('amount')
    ->setStoredAsCents(true);
```

### 2. DashboardController.php

```php
// AVANT
return (float) $result->fetchOne() ?: 0.0;

// APRÈS
$totalCents = (float) $result->fetchOne() ?: 0.0;
return $totalCents / 100; // Convertir de centimes en euros
```

### 3. TestAnalyticsCommand.php

- Même correction appliquée pour les méthodes de test

## 📊 Résultats après correction

### Avant

- Revenus totaux affichés : **356.00€**
- Montant d'une commande : **99.00€**

### Après

- Revenus totaux affichés : **3.56€** ✅
- Montant d'une commande : **0.99€** ✅

## 🎯 Logique de conversion

```
Prix blague en BDD : 0.99€
↓ (PaymentController × 100)
Montant commande stocké : 99 centimes
↓ (EasyAdmin ÷ 100)
Affichage final : 0.99€
```

## 🔧 Tests de validation

```bash
# Vérifier les prix en base
docker exec symfony_php_adeo_shop php bin/console doctrine:query:sql "SELECT o.amount, j.price FROM \`order\` o JOIN joke j ON o.joke_id = j.id LIMIT 3"

# Tester les analytics
docker exec symfony_php_adeo_shop php bin/console app:test-analytics
```

## ⚠️ Points d'attention

- **Cohérence** : Tous les calculs liés aux montants doivent prendre en compte le stockage en centimes
- **Stripe** : Les montants envoyés à Stripe sont déjà en centimes (correct)
- **Affichage** : Toujours diviser par 100 pour l'affichage utilisateur

## 📅 Date de correction

**21 août 2025** - Problème identifié et corrigé
