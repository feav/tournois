-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  Dim 09 août 2020 à 23:56
-- Version du serveur :  10.4.10-MariaDB
-- Version de PHP :  7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `schule`
--

-- --------------------------------------------------------

--
-- Structure de la table `abonnement`
--

DROP TABLE IF EXISTS `abonnement`;
CREATE TABLE IF NOT EXISTS `abonnement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formule_id` int(11) DEFAULT NULL,
  `ecole_id` int(11) NOT NULL,
  `prix` double NOT NULL,
  `canceled_date` datetime NOT NULL,
  `replicate_remise` double DEFAULT NULL,
  `remise` double DEFAULT NULL,
  `stripe_suscription` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_resilie` tinyint(1) DEFAULT NULL,
  `stripe_customer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut` datetime DEFAULT NULL,
  `expired` tinyint(1) DEFAULT NULL,
  `code_visio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_351268BB2A68F4D1` (`formule_id`),
  KEY `IDX_351268BB77EF1B1E` (`ecole_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classe`
--

DROP TABLE IF EXISTS `classe`;
CREATE TABLE IF NOT EXISTS `classe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecole_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cycle_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8F87BF9677EF1B1E` (`ecole_id`),
  KEY `IDX_8F87BF965EC1162` (`cycle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `classe`
--

INSERT INTO `classe` (`id`, `ecole_id`, `nom`, `cycle_id`) VALUES
(1, 1, 'CE1', 1),
(2, 1, 'CM2', 1),
(3, 2, 'CE1', 3),
(4, 2, 'CM2', 3);

-- --------------------------------------------------------

--
-- Structure de la table `cour`
--

DROP TABLE IF EXISTS `cour`;
CREATE TABLE IF NOT EXISTS `cour` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cycle`
--

DROP TABLE IF EXISTS `cycle`;
CREATE TABLE IF NOT EXISTS `cycle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ecole_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B086D19377EF1B1E` (`ecole_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cycle`
--

INSERT INTO `cycle` (`id`, `nom`, `ecole_id`) VALUES
(1, 'Primaire', 1),
(2, 'Collège', 1),
(3, 'Primaire', 2);

-- --------------------------------------------------------

--
-- Structure de la table `devoir`
--

DROP TABLE IF EXISTS `devoir`;
CREATE TABLE IF NOT EXISTS `devoir` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matiere_id` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `sujet` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `corrige` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moyenne_generale` double DEFAULT NULL,
  `note` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_749EA771F46CD258` (`matiere_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `devoir_user`
--

DROP TABLE IF EXISTS `devoir_user`;
CREATE TABLE IF NOT EXISTS `devoir_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `devoir_id` int(11) NOT NULL,
  `copy` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` double DEFAULT NULL,
  `mention` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_78666799A76ED395` (`user_id`),
  KEY `IDX_78666799C583534E` (`devoir_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ecole`
--

DROP TABLE IF EXISTS `ecole`;
CREATE TABLE IF NOT EXISTS `ecole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_uai` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_postal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fixe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `directeur_id` int(11) DEFAULT NULL,
  `annee_academique` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9786AACE82E7EE8` (`directeur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ecole`
--

INSERT INTO `ecole` (`id`, `nom`, `type`, `email`, `code_uai`, `adresse`, `rue`, `ville`, `code_postal`, `pays`, `telephone`, `fixe`, `logo`, `status`, `directeur_id`, `annee_academique`) VALUES
(1, 'ecole 1', '0', 'ecole1@gmail.com', 'UI-4554SDF54SDF', '125e paris', 'Rue djodjo', 'YAOUNDE', '1925', 'Åland Islands', '656645659555', NULL, NULL, 1, NULL, '2020-08-08'),
(2, 'ecole-2', '1', 'ecole2@gmail.com', 'UI-56x4dg4d', '19 tam tam', '125', 'bel', '1925', 'Belgium', '64566', NULL, NULL, 1, NULL, '2020-08-08');

-- --------------------------------------------------------

--
-- Structure de la table `formule`
--

DROP TABLE IF EXISTS `formule`;
CREATE TABLE IF NOT EXISTS `formule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` double NOT NULL,
  `stripe_product` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formule`
--

INSERT INTO `formule` (`id`, `nom`, `prix`, `stripe_product`, `duree`) VALUES
(1, 'Formule Basic', 29, 'prod_HV0m8l9ZeBrufF', 30);

-- --------------------------------------------------------

--
-- Structure de la table `jumellage`
--

DROP TABLE IF EXISTS `jumellage`;
CREATE TABLE IF NOT EXISTS `jumellage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `confirme` tinyint(1) DEFAULT NULL,
  `date_invitation` datetime NOT NULL,
  `date_validation` datetime DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumelle_auteur_id` int(11) NOT NULL,
  `jumelle_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AEF28FBCD681F95` (`jumelle_auteur_id`),
  KEY `IDX_AEF28FBBB17E203` (`jumelle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `jumellage`
--

INSERT INTO `jumellage` (`id`, `confirme`, `date_invitation`, `date_validation`, `titre`, `jumelle_auteur_id`, `jumelle_id`) VALUES
(1, 1, '2020-08-09 10:09:17', '2020-08-09 10:19:58', NULL, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `litige`
--

DROP TABLE IF EXISTS `litige`;
CREATE TABLE IF NOT EXISTS `litige` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `documents` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `traite` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EEE9D46DA76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `matiere`
--

DROP TABLE IF EXISTS `matiere`;
CREATE TABLE IF NOT EXISTS `matiere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classe_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coefficient` int(11) NOT NULL,
  `note_moyenne` double NOT NULL,
  `note_totale` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9014574A8F5EA509` (`classe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `matiere`
--

INSERT INTO `matiere` (`id`, `classe_id`, `nom`, `coefficient`, `note_moyenne`, `note_totale`) VALUES
(1, 1, 'Français', 1, 10, 20),
(2, 1, 'Mathématique', 2, 10, 20),
(3, 3, 'Français', 1, 20, 20),
(4, 3, 'Mathématique', 2, 10, 20);

-- --------------------------------------------------------

--
-- Structure de la table `matiere_meta`
--

DROP TABLE IF EXISTS `matiere_meta`;
CREATE TABLE IF NOT EXISTS `matiere_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matiere_id` int(11) NOT NULL,
  `mkey` double NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A420926BF46CD258` (`matiere_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `matiere_meta`
--

INSERT INTO `matiere_meta` (`id`, `matiere_id`, `mkey`, `value`) VALUES
(1, 1, 10, 'Passable'),
(2, 1, 12, 'Assez bien'),
(3, 1, 11, 'Passable'),
(4, 1, 14, 'Bien'),
(5, 2, 10, 'Passable'),
(6, 2, 11, 'Passable'),
(7, 2, 12, 'Assez bien'),
(8, 2, 15, 'Bien'),
(9, 3, 10, 'Passable'),
(10, 3, 12, 'Assez bien'),
(11, 4, 10, 'Passable');

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification_user`
--

DROP TABLE IF EXISTS `notification_user`;
CREATE TABLE IF NOT EXISTS `notification_user` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`notification_id`,`user_id`),
  KEY `IDX_35AF9D73EF1A9D84` (`notification_id`),
  KEY `IDX_35AF9D73A76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecole_id` int(11) DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` datetime DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username_canonical` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_canonical` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `confirmation_token` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `avatar` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D64992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_8D93D649A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_8D93D649C05FB297` (`confirmation_token`),
  KEY `IDX_8D93D64977EF1B1E` (`ecole_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `ecole_id`, `nom`, `prenom`, `date_naissance`, `titre`, `role`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `avatar`, `telephone`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, 'alex', 'alex', 'alexngoumo.an@gmail.com', 'alexngoumo.an@gmail.com', 1, NULL, '$2y$13$daYAm9Jlw4abflGxNhie7uMz/rRiQN.5s8zcSljCx7/d1BPiUHpoO', '2020-08-08 20:50:18', NULL, NULL, 'a:1:{i:0;s:16:\"ROLE_SUPER_ADMIN\";}', NULL, NULL),
(2, 1, NULL, NULL, NULL, NULL, '1', 'd1', 'd1', 'd1@gmail.com', 'd1@gmail.com', 1, NULL, '$2y$13$yk/4/YCpys8ECFt9R2cRSeMDo6iOERDEPQxYyioDKewrRPxH6./9i', '2020-08-08 20:48:34', NULL, NULL, 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', NULL, NULL),
(3, 2, 'alex-2', 'alex-2', '1935-05-17 00:00:00', '0', '1', 'd2', 'd2', 'd2@gmail.com', 'd2@gmail.com', 1, NULL, '$2y$13$XFtJTdIHbc1QE1k3o4WK/es03mGy3Jts1mHCnREI7uIaLQbHu9h.y', '2020-08-08 22:15:20', NULL, NULL, 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', '5f2f3b20c3c73_user.png', '+1 658 895 656 21'),
(4, 1, 'prof-1', 'prof-1', '1930-11-10 00:00:00', '0', '2', '2q61e8ej-swap', '2q61e8ej-swap', 'prof1@gmail.com', 'prof1@gmail.com', 1, NULL, '$2y$13$1kjYTH4XmFIkOFRJvgvtm.s9G6XjeibD0DOrTbfOBO7JTEmibJENy', NULL, NULL, NULL, 'a:1:{i:0;s:15:\"ROLE_PROFESSEUR\";}', NULL, '+1 569 877 544 66'),
(5, 1, 'prof-2', 'prof-2', '1930-12-11 00:00:00', '1', '2', 'rae3345a-swap', 'rae3345a-swap', 'prof2@gmail.com', 'prof2@gmail.com', 1, NULL, '$2y$13$hkjQtAuahJsv4PyRplPlGuJ7E/VHtNoVqeN5qxBmwVVq9/.d0AjGi', NULL, NULL, NULL, 'a:1:{i:0;s:15:\"ROLE_PROFESSEUR\";}', NULL, '+1 897 654 654 23'),
(6, 1, 'eleve-1', 'eleve-1', '1930-10-12 00:00:00', '2', '3', 'i1140cbv-swap', 'i1140cbv-swap', 'eleve1@gmail.com', 'eleve1@gmail.com', 1, NULL, '$2y$13$fWMRqxpdwhHQpI/V34WTgOB.hfoCd9BkRhZD9LzRV3sjCghOrWCeW', NULL, NULL, NULL, 'a:0:{}', NULL, '+1 584 699 445 45'),
(7, 1, 'eleve-2', 'eleve-2', '1930-10-23 00:00:00', '1', '3', '2wn0w31l-swap', '2wn0w31l-swap', 'eleve2@gmail.com', 'eleve2@gmail.com', 1, NULL, '$2y$13$LYQMn6RNZi3Yxrgqsl4rLeDwIgXIIc79dVbY63PfjOoywXfyZ.Aji', NULL, NULL, NULL, 'a:0:{}', NULL, '+1 584 699 445 45'),
(8, 1, 'eleve-3', 'eleve-3', '1927-10-12 00:00:00', '1', '3', 'a67z0gw1-swap', 'a67z0gw1-swap', 'eleve3@gmail.com', 'eleve3@gmail.com', 1, NULL, '$2y$13$k.vX2VEM1VRffYq.uH26H.9A4///YBL.Hxqz0W12qJd13u2hvHLmC', NULL, NULL, NULL, 'a:0:{}', NULL, '+1 569 877 544 66'),
(9, 2, 'eleve-4', 'eleve-4', '1933-01-30 00:00:00', '2', '3', '1w592cut-swap', '1w592cut-swap', 'elev4@gmail.com', 'elev4@gmail.com', 1, NULL, '$2y$13$65KWuwc9JqcsPAg3ZoqycOjP8bUwELmYd8zeGQEGuSenoAqvd97DS', NULL, NULL, NULL, 'a:0:{}', NULL, '+1 658 895 656 21'),
(10, 2, 'prof-3', 'prof-3', '1931-02-12 00:00:00', '1', '2', 'te94g5v2-swap', 'te94g5v2-swap', 'prof3@gmail.com', 'prof3@gmail.com', 1, NULL, '$2y$13$/ZyHfjJUPaOMhyhdsox/jeFnWBwNtgSJRSkmXrm0gJ2dH55JDQjlO', NULL, NULL, NULL, 'a:1:{i:0;s:15:\"ROLE_PROFESSEUR\";}', NULL, '+1 658 895 656 21'),
(11, 2, 'eleve-5', 'eleve-5', '1932-05-17 00:00:00', '0', '3', '0ddhj601-swap', '0ddhj601-swap', 'eleve5@gmail.com', 'eleve5@gmail.com', 1, NULL, '$2y$13$STV3her94yq9pUCys8vXsO2Oxt14iw1srcAepHqgqkLQd29opcbBS', NULL, NULL, NULL, 'a:0:{}', NULL, '+1 658 895 656 21');

-- --------------------------------------------------------

--
-- Structure de la table `user_classe`
--

DROP TABLE IF EXISTS `user_classe`;
CREATE TABLE IF NOT EXISTS `user_classe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `date_inscription` date DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EAD5A4ABA76ED395` (`user_id`),
  KEY `IDX_EAD5A4AB8F5EA509` (`classe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_classe`
--

INSERT INTO `user_classe` (`id`, `user_id`, `classe_id`, `date_inscription`, `role`) VALUES
(1, 4, 1, '2020-08-08', '2'),
(2, 6, 1, '2020-08-08', '3'),
(3, 5, 2, '2020-08-08', '2'),
(4, 7, 1, '2020-08-08', '3'),
(5, 8, 1, '2020-08-08', '3'),
(6, 5, 1, '2020-08-08', '2'),
(7, 9, 3, '2020-08-08', '3'),
(8, 11, 3, '2020-08-08', '3'),
(9, 10, 4, '2020-08-08', '2'),
(10, 10, 3, '2020-08-08', '2');

-- --------------------------------------------------------

--
-- Structure de la table `user_matiere`
--

DROP TABLE IF EXISTS `user_matiere`;
CREATE TABLE IF NOT EXISTS `user_matiere` (
  `user_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`matiere_id`),
  KEY `IDX_C8194940A76ED395` (`user_id`),
  KEY `IDX_C8194940F46CD258` (`matiere_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_matiere`
--

INSERT INTO `user_matiere` (`user_id`, `matiere_id`) VALUES
(4, 1),
(4, 2),
(5, 1),
(10, 3);

-- --------------------------------------------------------

--
-- Structure de la table `user_visioconference`
--

DROP TABLE IF EXISTS `user_visioconference`;
CREATE TABLE IF NOT EXISTS `user_visioconference` (
  `user_id` int(11) NOT NULL,
  `visioconference_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`visioconference_id`),
  KEY `IDX_C0546ECCA76ED395` (`user_id`),
  KEY `IDX_C0546ECCC5C89FEB` (`visioconference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `visioconference`
--

DROP TABLE IF EXISTS `visioconference`;
CREATE TABLE IF NOT EXISTS `visioconference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classe_id` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `date_creation` datetime NOT NULL,
  `confirme` int(11) DEFAULT NULL,
  `date_programme` datetime DEFAULT NULL,
  `duree` int(11) DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `capacite` int(11) NOT NULL,
  `initiateur` int(11) NOT NULL,
  `ecole` int(11) NOT NULL,
  `jumelle` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D28802438F5EA509` (`classe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `abonnement`
--
ALTER TABLE `abonnement`
  ADD CONSTRAINT `FK_351268BB2A68F4D1` FOREIGN KEY (`formule_id`) REFERENCES `formule` (`id`),
  ADD CONSTRAINT `FK_351268BB77EF1B1E` FOREIGN KEY (`ecole_id`) REFERENCES `ecole` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `classe`
--
ALTER TABLE `classe`
  ADD CONSTRAINT `FK_8F87BF965EC1162` FOREIGN KEY (`cycle_id`) REFERENCES `cycle` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_8F87BF9677EF1B1E` FOREIGN KEY (`ecole_id`) REFERENCES `ecole` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cycle`
--
ALTER TABLE `cycle`
  ADD CONSTRAINT `FK_B086D19377EF1B1E` FOREIGN KEY (`ecole_id`) REFERENCES `ecole` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `devoir`
--
ALTER TABLE `devoir`
  ADD CONSTRAINT `FK_749EA771F46CD258` FOREIGN KEY (`matiere_id`) REFERENCES `matiere` (`id`);

--
-- Contraintes pour la table `devoir_user`
--
ALTER TABLE `devoir_user`
  ADD CONSTRAINT `FK_78666799A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_78666799C583534E` FOREIGN KEY (`devoir_id`) REFERENCES `devoir` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ecole`
--
ALTER TABLE `ecole`
  ADD CONSTRAINT `FK_9786AACE82E7EE8` FOREIGN KEY (`directeur_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `jumellage`
--
ALTER TABLE `jumellage`
  ADD CONSTRAINT `FK_AEF28FBBB17E203` FOREIGN KEY (`jumelle_id`) REFERENCES `ecole` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_AEF28FBCD681F95` FOREIGN KEY (`jumelle_auteur_id`) REFERENCES `ecole` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `litige`
--
ALTER TABLE `litige`
  ADD CONSTRAINT `FK_EEE9D46DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `matiere`
--
ALTER TABLE `matiere`
  ADD CONSTRAINT `FK_9014574A8F5EA509` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `matiere_meta`
--
ALTER TABLE `matiere_meta`
  ADD CONSTRAINT `FK_A420926BF46CD258` FOREIGN KEY (`matiere_id`) REFERENCES `matiere` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notification_user`
--
ALTER TABLE `notification_user`
  ADD CONSTRAINT `FK_35AF9D73A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_35AF9D73EF1A9D84` FOREIGN KEY (`notification_id`) REFERENCES `notification` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D64977EF1B1E` FOREIGN KEY (`ecole_id`) REFERENCES `ecole` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_classe`
--
ALTER TABLE `user_classe`
  ADD CONSTRAINT `FK_EAD5A4AB8F5EA509` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_EAD5A4ABA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_matiere`
--
ALTER TABLE `user_matiere`
  ADD CONSTRAINT `FK_C8194940A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C8194940F46CD258` FOREIGN KEY (`matiere_id`) REFERENCES `matiere` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_visioconference`
--
ALTER TABLE `user_visioconference`
  ADD CONSTRAINT `FK_C0546ECCA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C0546ECCC5C89FEB` FOREIGN KEY (`visioconference_id`) REFERENCES `visioconference` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `visioconference`
--
ALTER TABLE `visioconference`
  ADD CONSTRAINT `FK_D28802438F5EA509` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
