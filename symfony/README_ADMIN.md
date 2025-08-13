# Interface d'Administration EasyAdmin

## Problème résolu : Gestion des mots de passe

### ❌ Problème initial

Lors de la modification d'un utilisateur dans l'interface admin, le mot de passe était systématiquement re-hashé, même si aucun nouveau mot de passe n'était fourni. Cela rendait l'utilisateur incapable de se connecter.

### ✅ Solution implémentée

#### 1. **EventSubscriber pour la gestion des mots de passe**

-   `UserPasswordSubscriber` : Intercepte les mises à jour d'utilisateurs
-   Vérifie si un nouveau mot de passe a été fourni
-   Préserve le mot de passe existant si le champ est vide
-   Hash uniquement les nouveaux mots de passe

#### 2. **Interface utilisateur améliorée**

-   Template personnalisé `admin/field/password.html.twig`
-   Messages informatifs clairs
-   Indication visuelle de la protection du mot de passe

#### 3. **Commande utilitaire**

-   `app:create-admin` : Pour créer facilement un administrateur

### 🔧 Comment ça fonctionne

1. **Création d'utilisateur** : Le mot de passe est hashé normalement
2. **Modification d'utilisateur** :
    - Si le champ mot de passe est **vide** → Le mot de passe existant est conservé
    - Si le champ mot de passe est **rempli** → Le nouveau mot de passe est hashé

### 🛡️ Sécurité

-   Les mots de passe sont toujours hashés avec l'algorithme Symfony (bcrypt/argon2i)
-   Aucun mot de passe en clair n'est stocké
-   L'interface indique clairement le comportement attendu

### 📝 Utilisation

1. **Créer un admin** :

    ```bash
    docker exec -it symfony_php_adeo_shop php bin/console app:create-admin
    ```

2. **Modifier un utilisateur** :

    - Accéder à `/admin/user`
    - Modifier les champs souhaités
    - **Laisser le champ mot de passe vide** pour conserver le mot de passe actuel
    - Ou **saisir un nouveau mot de passe** pour le changer

3. **Toggle des champs** :
    - Vous pouvez maintenant modifier `isVerified`, `roles`, etc. sans problème
    - Le mot de passe reste intact

### 🎯 Résultat

✅ **Avant** : Modifier n'importe quel champ → Mot de passe corrompu  
✅ **Après** : Modifier n'importe quel champ → Mot de passe préservé  
✅ **Nouveau mot de passe** : Saisir un nouveau mot de passe → Hash correct

---

_Cette solution garantit une expérience utilisateur fluide et sécurisée pour l'administration des utilisateurs._
