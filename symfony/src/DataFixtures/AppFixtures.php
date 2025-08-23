<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Joke;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Créer les catégories
        $categories = $this->createCategories($manager);

        // Créer les blagues
        $this->createJokes($manager, $categories);

        // Créer l'utilisateur admin
        $this->createUsers($manager);

        $manager->flush();
    }

    private function createCategories(ObjectManager $manager): array
    {
        $categoriesData = [
            [
                'name' => 'Anniversaire',
                'slug' => 'anniversaire',
                'description' => 'Des vannes à sortir juste avant le gâteau. Parfaites pour faire rire toute la table sans plomber les bougies.',
                'color' => 'rgba(254, 252, 1, 0.12)',
                'icon' => 'fa-solid fa-cake-candles'
            ],
            [
                'name' => 'Saint-Valentin',
                'slug' => 'saint-valentin',
                'description' => 'Traits d\'esprit tendres pour briser la glace ou pimenter le tête-à-tête. Romantique, taquin, efficace.',
                'color' => 'rgba(224, 49, 49, 0.12)',
                'icon' => 'fa-solid fa-heart'
            ],
            [
                'name' => 'Mariage',
                'slug' => 'mariage',
                'description' => 'Blagues bon esprit pour discours, toast ou livre d\'or. De quoi faire sourire sans vexer la belle-famille.',
                'color' => 'rgba(132, 94, 247, 0.12)',
                'icon' => 'fa-solid fa-ring'
            ],
            [
                'name' => 'Bureau',
                'slug' => 'bureau',
                'description' => 'Suffisamment sages pour la machine à café, assez piquants pour décrocher un vrai sourire.',
                'color' => 'rgba(77, 171, 247, 0.12)',
                'icon' => 'fa-solid fa-mug-saucer'
            ],
            [
                'name' => 'Fête des Mères',
                'slug' => 'fete-des-meres',
                'description' => 'Douces et malines, idéales pour un message attentionné ou un petit toast à maman.',
                'color' => 'rgba(255, 77, 109, 0.12)',
                'icon' => 'fa-solid fa-hand-holding-heart'
            ],
            [
                'name' => 'Fête des Pères',
                'slug' => 'fete-des-peres',
                'description' => 'Jeux de mots et autodérision "papa-style", calibrés pour faire rire toute la famille.',
                'color' => 'rgba(56, 217, 169, 0.12)',
                'icon' => 'fa-solid fa-user-tie'
            ],
            [
                'name' => 'Apéro / Soirée',
                'slug' => 'apero-soiree',
                'description' => 'À dégainer entre deux toasts pour lancer la conversation et chauffer l\'ambiance.',
                'color' => 'rgba(255, 168, 36, 0.12)',
                'icon' => 'fa-solid fa-champagne-glasses'
            ]
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $category->setDescription($data['description']);
            $category->setColor($data['color']);
            $category->setIcon($data['icon']);
            $category->setIsActive(true);
            $category->setCreatedAt(new \DateTimeImmutable('2025-08-17 14:37:06'));
            $category->setUpdatedAt(new \DateTimeImmutable('2025-08-19 15:25:51'));

            $manager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }

    private function createJokes(ObjectManager $manager, array $categories): void
    {
        // Récupérer les catégories par nom pour faciliter l'assignation
        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getName()] = $category;
        }

        $jokesData = [
            // Anniversaire
            ['Bougies soufflées', 'Pourquoi les bougies ne vont jamais à l\'école ? Parce qu\'elles se font toujours souffler !', 'Une devinette d\'anniversaire qui ne manque pas d\'air.', 'Anniversaire'],
            ['Le comble du gâteau', 'Quel est le comble pour un gâteau d\'anniversaire ? Se faire couper la parole !', 'Un comble sucré à raconter le jour J.', 'Anniversaire'],
            ['Fête mortelle', 'Pourquoi les squelettes ne fêtent jamais leur anniversaire ? Parce qu\'ils n\'ont personne à inviter !', 'Même les squelettes ont leurs petits soucis d\'organisation.', 'Anniversaire'],
            ['Bougie retraitée', 'Que fait une bougie à la retraite ? Elle part en fumée !', 'Le repos bien mérité d\'une bougie de gâteau…', 'Anniversaire'],

            // Fête des Pères  
            ['Marchandage', 'Un fils dit à son père : Si tu me donnes 30€, je serai sage. À ton âge, j\'étais sage gratuitement.', 'Une tentative d\'extorsion vite douchée par la mémoire paternelle.', 'Fête des Pères'],
            ['Papa esquimau', 'Comment appelle-t-on un père qui tombe sur la glace ? Un esquimau.', 'Un jeu de mots givré pour les papas maladroits.', 'Fête des Pères'],
            ['Sport de plage', 'Comment les pères font-ils du sport sur la plage ? En rentrant leur ventre quand ils voient un bikini !', 'L\'entraînement intensif de papa sur la plage en été.', 'Fête des Pères'],
            ['Coût du mariage', 'Papa, combien faut-il payer pour se marier ? Difficile à dire, mon fils. Personnellement, je paie encore.', 'Quand papa donne une réponse très personnelle sur le mariage.', 'Fête des Pères'],
            ['Même âge', 'Quel âge a ton père ? Le même que moi. C\'est impossible ! Il n\'est devenu père que quand je suis né.', 'Une logique imparable qui laisse l\'auditoire sans voix.', 'Fête des Pères'],
            ['Papa parking', 'Pourquoi les pères sont-ils comme les places de parking ? C\'est facile, tous les bons sont déjà pris.', 'Une comparaison espiègle sur la rareté des perles rares.', 'Fête des Pères'],
            ['Salut Soif', 'Papa, j\'ai soif ! Enchanté Soif, moi c\'est Papa.', 'Le grand classique des jeux de mots paternels.', 'Fête des Pères'],
            ['Papa de l\'année', 'Le papa de l\'année, c\'est celui qui dit toujours oui et qui ne contrarie jamais maman.', 'Le secret (presque) sûr pour obtenir le trophée du meilleur papa.', 'Fête des Pères'],

            // Apéro/Soirée
            ['Plouf au café', 'Un mec rentre dans un café. Et plouf.', 'Un grand classique des histoires de comptoir.', 'Apéro / Soirée'],
            ['Bouchons', 'Désolé pour le retard, il y avait des bouchons.', 'L\'excuse préférée des retardataires de l\'apéro.', 'Apéro / Soirée'],
            ['Sous pression', 'Pourquoi les bières sont-elles toujours stressées ? Parce qu\'elles ont la pression.', 'La vie anxiogène d\'une blonde (bien tirée).', 'Apéro / Soirée'],
            ['Tabouret du saoulard', 'Quel est l\'endroit préféré d\'un mec saoul pour s\'asseoir ? Un ta-bourré.', 'Un bon mot de comptoir que même ivre on peut comprendre.', 'Apéro / Soirée'],
            ['Bain de bière', 'Que dit une bière qui tombe dans l\'eau ? Je sais panaché.', 'Une réponse mousseuse à une situation un peu diluée.', 'Apéro / Soirée'],
            ['Alcootest astuce', 'Pourquoi faut-il enlever ses lunettes avant un alcootest ? Ça fait deux verres en moins.', 'Le conseil (peu fiable) d\'un habitué pour descendre sous la limite.', 'Apéro / Soirée'],
            ['Soûl-marin', 'Comment appelle-t-on un matelot bourré ? Un soûl-marin.', 'Une devinette qui a le pied marin (et un peu la tête qui tourne).', 'Apéro / Soirée'],
            ['Solution liquide', 'L\'alcool ne résout pas les problèmes, mais l\'eau non plus.', 'Un rappel que la sobriété n\'est pas gage de miracle non plus.', 'Apéro / Soirée'],
            ['Où est Modération ?', 'On dit de boire avec modération, mais il ne vient jamais boire avec nous.', 'Le plus grand mystère des consignes de l\'apéro.', 'Apéro / Soirée'],
            ['Boule de mousse', 'Une bière qui roule n\'amasse pas mousse.', 'Un proverbe rebrassé à la sauce houblon.', 'Apéro / Soirée'],
            ['Théorie du pastis', 'Le pastis, c\'est comme les seins : un, c\'est pas assez ; trois, c\'est trop.', 'Une analogie culottée qui fait sourire (jaune).', 'Apéro / Soirée'],
            ['Fête des fumeurs', 'Quelle est la date de la fête des fumeurs ? Le 1er joint.', 'Un calembour à allumer lors d\'une soirée enfumée.', 'Apéro / Soirée']
        ];

        foreach ($jokesData as $data) {
            $joke = new Joke();
            $joke->setTitle($data[0]);
            $joke->setBodyText($data[1]);
            $joke->setDescription($data[2]);
            $joke->setCategory($categoryMap[$data[3]]);
            $joke->setLanguage('fr');
            $joke->setIsActive(true);
            $joke->setNsfw(false);
            $joke->setPrice(99); // 0.99€ en centimes
            $joke->setCreatedAt(new \DateTimeImmutable('2025-08-17 15:11:36'));
            $joke->setUpdatedAt(new \DateTimeImmutable('2025-08-17 15:11:36'));

            $manager->persist($joke);
        }
    }

    private function createUsers(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('ozarmctz@proton.me');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);

        // Mot de passe: admin123 (à changer en production)
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        // Les dates seront automatiquement définies par PrePersist

        $manager->persist($admin);
    }
}
