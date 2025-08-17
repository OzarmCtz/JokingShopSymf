# Images des Blagues - Documentation

## Changements récents

L'entité `Joke` a été mise à jour pour supporter deux types d'images distinctes :

### Nouveaux champs

1. **`preview_image`** - Image d'aperçu pour les cards

   - Affichée sur les cartes de la boutique (page d'accueil)
   - Format recommandé : 240x280px (aspect ratio des cards)
   - Champ obligatoire pour une bonne présentation

2. **`view_image`** - Image de vue pour la modal de détails
   - Affichée dans la modal qui s'ouvre quand on clique sur une card
   - Format recommandé : 400x400px ou plus (carré ou paysage)
   - Champ optionnel (fallback sur `preview_image` si absent)

### Ancien champ

- **`photo`** - ❌ Supprimé et remplacé par `preview_image`

## EasyAdmin

Dans l'interface d'administration, vous pouvez maintenant :

1. **Télécharger une image d'aperçu** (preview_image)

   - Utilisée pour les cartes de la boutique
   - Nommage automatique : `preview-[slug]-[uuid].[extension]`

2. **Télécharger une image de vue** (view_image)
   - Utilisée dans la modal de détails
   - Nommage automatique : `view-[slug]-[uuid].[extension]`

## Logique de fallback

Pour la modal de détails, l'ordre de priorité des images est :

1. `view_image` (si disponible)
2. `preview_image` (fallback)
3. `placeholder.jpg` (fallback final)

## Migration des données

- Les anciennes images `photo` ont été automatiquement renommées en `preview_image`
- Aucune perte de données n'a eu lieu
- Les images existantes continuent de fonctionner pour les cards
- Vous pouvez maintenant ajouter des `view_image` spécifiques pour les modals

## Recommandations

1. **Pour les nouvelles blagues** : téléchargez les deux images

   - Une image optimisée pour les cards (240x280px)
   - Une image haute qualité pour la modal (400x400px minimum)

2. **Pour les blagues existantes** : ajoutez progressivement des `view_image`

   - Les `preview_image` existantes serviront de fallback
   - Priorisez les blagues les plus populaires

3. **Formats supportés** : JPG, PNG, WebP
4. **Taille max recommandée** : 2MB par image
