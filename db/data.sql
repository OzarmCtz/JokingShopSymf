-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.6 - MySQL Community Server - GPL
-- Server OS:                    Linux
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table symfony.app_settings: ~1 rows (approximately)
INSERT INTO `app_settings` (`id`, `company_name`, `company_legal_form`, `company_address`, `company_postal_code`, `company_city`, `company_country`, `company_siret`, `company_rcs`, `company_vat`, `company_capital`, `contact_email`, `contact_phone`, `support_email`, `support_hours`, `hosting_provider`, `hosting_address`, `hosting_phone`, `hosting_website`, `mediator_name`, `mediator_website`, `competent_court`, `social_facebook`, `social_twitter`, `social_instagram`, `social_linkedin`, `dpo_email`, `updated_at`, `publication_director`, `website_domain`, `company_description`, `legal_representative`) VALUES
	(2, 'Jo.King', 'Micro-entreprise', '123 Rue de la Comédie', '75001', 'Paris', 'France', NULL, NULL, NULL, NULL, 'contact@jo-king.fr', NULL, NULL, 'Lundi au vendredi, 9h-18h', NULL, NULL, NULL, NULL, NULL, NULL, 'Paris', NULL, NULL, NULL, NULL, NULL, '2025-09-11 12:31:41', 'Directeur Jo.King', 'jo-king.fr', NULL, NULL);

-- Dumping data for table symfony.category: ~7 rows (approximately)
INSERT INTO `category` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`, `description`, `color`, `icon`, `preview_image`, `view_image`) VALUES
	(1, 'Anniversaire', 'anniversaire', 1, '2025-08-17 14:37:06', '2025-09-11 14:35:50', 'Des vannes à sortir juste avant le gâteau. Parfaites pour faire rire toute la table sans plomber les bougies.', 'rgba(254, 252, 1, 0.12)', 'fa-solid fa-cake-candles', 'preview-anniv-preview-e4fc7942-017a-408d-91d2-b23a1b2d43c2.png', 'view-anniv-view-bb75dfef-d91f-48a3-9f2c-3c0cb4d8086b.png'),
	(2, 'Saint-Valentin', 'saint-valentin', 1, '2025-08-17 14:37:06', '2025-08-19 15:25:51', 'Traits d\'esprit tendres pour briser la glace ou pimenter le tête-à-tête. Romantique, taquin, efficace.', 'rgba(224, 49, 49, 0.12)', 'fa-solid fa-heart', NULL, NULL),
	(3, 'Mariage', 'mariage', 1, '2025-08-17 14:37:06', '2025-08-19 15:25:51', 'Blagues bon esprit pour discours, toast ou livre d\'or. De quoi faire sourire sans vexer la belle-famille.', 'rgba(132, 94, 247, 0.12)', 'fa-solid fa-ring', NULL, NULL),
	(4, 'Bureau', 'bureau', 1, '2025-08-17 14:37:06', '2025-08-19 15:25:51', 'Suffisamment sages pour la machine à café, assez piquants pour décrocher un vrai sourire.', 'rgba(77, 171, 247, 0.12)', 'fa-solid fa-mug-saucer', NULL, NULL),
	(5, 'Fête des Mères', 'fete-des-meres', 1, '2025-08-17 14:37:06', '2025-08-19 15:25:51', 'Douces et malines, idéales pour un message attentionné ou un petit toast à maman.', 'rgba(255, 77, 109, 0.12)', 'fa-solid fa-hand-holding-heart', NULL, NULL),
	(6, 'Fête des Pères', 'fete-des-peres', 1, '2025-08-17 14:37:06', '2025-09-11 14:25:12', 'Jeux de mots et autodérision "papa-style", calibrés pour faire rire toute la famille.', 'rgba(56, 217, 169, 0.12)', 'fa-solid fa-user-tie', 'preview-dad-preview-a0bfae51-40ac-4c5d-85b3-aac80ced9ad0.png', 'view-dad-view-4cd9bb54-f391-4b41-b8f4-6d2a898af84d.png'),
	(7, 'Apéro / Soirée', 'apero-soiree', 1, '2025-08-17 14:37:06', '2025-09-11 14:27:26', 'À dégainer entre deux toasts pour lancer la conversation et chauffer l\'ambiance.', 'rgba(255, 168, 36, 0.12)', 'fa-solid fa-champagne-glasses', 'preview-soiree-preview-e27ad93d-2555-421b-94dd-24875d8aa34e.png', 'view-soiree-view-565a21c5-361b-445b-a89b-79e20cedcdba.png');

-- Dumping data for table symfony.doctrine_migration_versions: ~17 rows (approximately)
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
	('DoctrineMigrations\\Version20250812145944', '2025-09-07 14:15:06', 53),
	('DoctrineMigrations\\Version20250812161615', '2025-09-07 14:15:06', 36),
	('DoctrineMigrations\\Version20250812170821', '2025-09-07 14:15:06', 40),
	('DoctrineMigrations\\Version20250812175031', '2025-09-07 14:15:06', 39),
	('DoctrineMigrations\\Version20250813124720', '2025-09-07 14:15:06', 19),
	('DoctrineMigrations\\Version20250813125948', '2025-09-07 14:15:06', 76),
	('DoctrineMigrations\\Version20250813140413', '2025-09-07 14:15:06', 37),
	('DoctrineMigrations\\Version20250816132143', '2025-09-07 14:15:06', 133),
	('DoctrineMigrations\\Version20250817150308', '2025-09-07 14:15:06', 36),
	('DoctrineMigrations\\Version20250818130856', '2025-09-07 14:15:06', 76),
	('DoctrineMigrations\\Version20250819144028', '2025-09-07 14:15:06', 37),
	('DoctrineMigrations\\Version20250819144709', '2025-09-07 14:15:06', 37),
	('DoctrineMigrations\\Version20250819151335', '2025-09-07 14:15:06', 10),
	('DoctrineMigrations\\Version20250822165413', '2025-09-07 14:15:06', 26),
	('DoctrineMigrations\\Version20250822170735', '2025-09-07 14:15:06', 41),
	('DoctrineMigrations\\Version20250911122914', '2025-09-11 12:29:23', 47),
	('DoctrineMigrations\\Version20250911123456', '2025-09-11 12:35:25', 16);

-- Dumping data for table symfony.joke: ~24 rows (approximately)
INSERT INTO `joke` (`id`, `category_id`, `title`, `body_text`, `language`, `is_active`, `nsfw`, `created_at`, `updated_at`, `price`, `description`) VALUES
	(1, 1, 'Bougies soufflées', 'Pourquoi les bougies ne vont jamais à l\'école ? Parce qu\'elles se font toujours souffler !', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une devinette d\'anniversaire qui ne manque pas d\'air.'),
	(2, 1, 'Le comble du gâteau', 'Quel est le comble pour un gâteau d\'anniversaire ? Se faire couper la parole !', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un comble sucré à raconter le jour J.'),
	(3, 1, 'Fête mortelle', 'Pourquoi les squelettes ne fêtent jamais leur anniversaire ? Parce qu\'ils n\'ont personne à inviter !', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Même les squelettes ont leurs petits soucis d\'organisation.'),
	(4, 1, 'Bougie retraitée', 'Que fait une bougie à la retraite ? Elle part en fumée !', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Le repos bien mérité d\'une bougie de gâteau…'),
	(5, 6, 'Marchandage', 'Un fils dit à son père : Si tu me donnes 30€, je serai sage. À ton âge, j\'étais sage gratuitement.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une tentative d\'extorsion vite douchée par la mémoire paternelle.'),
	(6, 6, 'Papa esquimau', 'Comment appelle-t-on un père qui tombe sur la glace ? Un esquimau.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un jeu de mots givré pour les papas maladroits.'),
	(7, 6, 'Sport de plage', 'Comment les pères font-ils du sport sur la plage ? En rentrant leur ventre quand ils voient un bikini !', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'L\'entraînement intensif de papa sur la plage en été.'),
	(8, 6, 'Coût du mariage', 'Papa, combien faut-il payer pour se marier ? Difficile à dire, mon fils. Personnellement, je paie encore.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Quand papa donne une réponse très personnelle sur le mariage.'),
	(9, 6, 'Même âge', 'Quel âge a ton père ? Le même que moi. C\'est impossible ! Il n\'est devenu père que quand je suis né.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une logique imparable qui laisse l\'auditoire sans voix.'),
	(10, 6, 'Papa parking', 'Pourquoi les pères sont-ils comme les places de parking ? C\'est facile, tous les bons sont déjà pris.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une comparaison espiègle sur la rareté des perles rares.'),
	(11, 6, 'Salut Soif', 'Papa, j\'ai soif ! Enchanté Soif, moi c\'est Papa.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Le grand classique des jeux de mots paternels.'),
	(12, 6, 'Papa de l\'année', 'Le papa de l\'année, c\'est celui qui dit toujours oui et qui ne contrarie jamais maman.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Le secret (presque) sûr pour obtenir le trophée du meilleur papa.'),
	(13, 7, 'Plouf au café', 'Un mec rentre dans un café. Et plouf.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un grand classique des histoires de comptoir.'),
	(14, 7, 'Bouchons', 'Désolé pour le retard, il y avait des bouchons.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'L\'excuse préférée des retardataires de l\'apéro.'),
	(15, 7, 'Sous pression', 'Pourquoi les bières sont-elles toujours stressées ? Parce qu\'elles ont la pression.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'La vie anxiogène d\'une blonde (bien tirée).'),
	(16, 7, 'Tabouret du saoulard', 'Quel est l\'endroit préféré d\'un mec saoul pour s\'asseoir ? Un ta-bourré.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un bon mot de comptoir que même ivre on peut comprendre.'),
	(17, 7, 'Bain de bière', 'Que dit une bière qui tombe dans l\'eau ? Je sais panaché.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une réponse mousseuse à une situation un peu diluée.'),
	(18, 7, 'Alcootest astuce', 'Pourquoi faut-il enlever ses lunettes avant un alcootest ? Ça fait deux verres en moins.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Le conseil (peu fiable) d\'un habitué pour descendre sous la limite.'),
	(19, 7, 'Soûl-marin', 'Comment appelle-t-on un matelot bourré ? Un soûl-marin.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une devinette qui a le pied marin (et un peu la tête qui tourne).'),
	(20, 7, 'Solution liquide', 'L\'alcool ne résout pas les problèmes, mais l\'eau non plus.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un rappel que la sobriété n\'est pas gage de miracle non plus.'),
	(21, 7, 'Où est Modération ?', 'On dit de boire avec modération, mais il ne vient jamais boire avec nous.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Le plus grand mystère des consignes de l\'apéro.'),
	(22, 7, 'Boule de mousse', 'Une bière qui roule n\'amasse pas mousse.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un proverbe rebrassé à la sauce houblon.'),
	(23, 7, 'Théorie du pastis', 'Le pastis, c\'est comme les seins : un, c\'est pas assez ; trois, c\'est trop.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Une analogie culottée qui fait sourire (jaune).'),
	(24, 7, 'Fête des fumeurs', 'Quelle est la date de la fête des fumeurs ? Le 1er joint.', 'fr', 1, 0, '2025-08-17 15:11:36', '2025-08-17 15:11:36', 0.99, 'Un calembour à allumer lors d\'une soirée enfumée.');

-- Dumping data for table symfony.messenger_messages: ~0 rows (approximately)

-- Dumping data for table symfony.order: ~0 rows (approximately)

-- Dumping data for table symfony.user: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
