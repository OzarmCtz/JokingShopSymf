# Correction: Division par zéro dans le Dashboard Admin

## Problème identifié

**Erreur:** `Division by zero` dans `admin/dashboard.html.twig` à la ligne 84

**Cause:** Le template tentait de calculer un pourcentage en divisant par `stats.jokes_total` sans vérifier si cette valeur était zéro.

```twig
<!-- AVANT: Code problématique -->
<small class="text-white">{{ ((stats.jokes_active / stats.jokes_total) * 100)|round }}% du total</small>
```

## Solution appliquée

### 1. Protection dans le template

Ajout d'une condition pour vérifier si `stats.jokes_total > 0` avant la division :

```twig
<!-- APRÈS: Code corrigé -->
<small class="text-white">
    {% if stats.jokes_total > 0 %}
        {{ ((stats.jokes_active / stats.jokes_total) * 100)|round }}% du total
    {% else %}
        0% du total
    {% endif %}
</small>
```

### 2. Protection dans le contrôleur

Ajout de `max(0, ...)` pour s'assurer que les valeurs sont toujours positives :

```php
'jokes_total' => max(0, $jokesTotalCount),
'jokes_active' => max(0, $jokesActiveCount),
```

## Scénarios couverts

- ✅ **Base de données vide** : Affiche "0% du total"
- ✅ **Aucune blague active** : Affiche "0% du total"
- ✅ **Base de données avec des blagues** : Calcule et affiche le pourcentage correct

## Tests recommandés

1. Accéder au dashboard avec une base de données vide
2. Accéder au dashboard avec des blagues inactives uniquement
3. Accéder au dashboard avec des blagues actives et inactives

## Fichiers modifiés

- `templates/admin/dashboard.html.twig` : Protection contre division par zéro
- `src/Controller/Admin/DashboardController.php` : Validation des valeurs statistiques
- `tests/Controller/Admin/DashboardControllerTest.php` : Tests unitaires (ajoutés)
