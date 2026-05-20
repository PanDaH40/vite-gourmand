-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 20 mai 2026 à 14:55
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `vite_gourmand`
--

-- --------------------------------------------------------

--
-- Structure de la table `allergene`
--

DROP TABLE IF EXISTS `allergene`;
CREATE TABLE IF NOT EXISTS `allergene` (
  `allergene_id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`allergene_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `avis_id` int NOT NULL,
  `note` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`avis_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_commande` date DEFAULT NULL,
  `date_prestation` date DEFAULT NULL,
  `heure_livraison` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix_menu` double DEFAULT NULL,
  `nombre_personne` int DEFAULT NULL,
  `prix_livraison` double DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pret_materiel` tinyint(1) DEFAULT NULL,
  `restitution_materiel` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `numero_commande` (`numero_commande`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`numero_commande`, `date_commande`, `date_prestation`, `heure_livraison`, `prix_menu`, `nombre_personne`, `prix_livraison`, `statut`, `pret_materiel`, `restitution_materiel`) VALUES
('CMD-20260519210603', '2026-05-19', '2026-05-27', '02:05', 1800, 60, 0, 'en attente', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `commande_menu`
--

DROP TABLE IF EXISTS `commande_menu`;
CREATE TABLE IF NOT EXISTS `commande_menu` (
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_id` int NOT NULL,
  PRIMARY KEY (`numero_commande`),
  KEY `fk_cm_menu` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_menu`
--

INSERT INTO `commande_menu` (`numero_commande`, `menu_id`) VALUES
('CMD-20260519210603', 1);

-- --------------------------------------------------------

--
-- Structure de la table `commande_utilisateur`
--

DROP TABLE IF EXISTS `commande_utilisateur`;
CREATE TABLE IF NOT EXISTS `commande_utilisateur` (
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`numero_commande`),
  KEY `fk_cu_utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_utilisateur`
--

INSERT INTO `commande_utilisateur` (`numero_commande`, `utilisateur_id`) VALUES
('CMD-20260519210603', 5);

-- --------------------------------------------------------

--
-- Structure de la table `contient_plat_allergene`
--

DROP TABLE IF EXISTS `contient_plat_allergene`;
CREATE TABLE IF NOT EXISTS `contient_plat_allergene` (
  `plat_id` int NOT NULL,
  `allergene_id` int NOT NULL,
  PRIMARY KEY (`plat_id`,`allergene_id`),
  KEY `fk_cpa_allergene` (`allergene_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horaire`
--

DROP TABLE IF EXISTS `horaire`;
CREATE TABLE IF NOT EXISTS `horaire` (
  `horaire_id` int NOT NULL AUTO_INCREMENT,
  `jour` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heure_ouverture` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heure_fermeture` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`horaire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE IF NOT EXISTS `menu` (
  `menu_id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_personne_minimum` int DEFAULT NULL,
  `prix_par_personne` double DEFAULT NULL,
  `regime` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantite_restante` int DEFAULT NULL,
  PRIMARY KEY (`menu_id`),
  KEY `fk_menu_regime_libelle` (`regime`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`menu_id`, `titre`, `nombre_personne_minimum`, `prix_par_personne`, `regime`, `description`, `quantite_restante`) VALUES
(1, 'Menu Pâques', 6, 30, 'Classique', 'Menu festif pour célébrer Pâques.', 5),
(2, 'Menu Noël', 4, 30, 'Classique', 'Menu spécial fêtes de fin d’année.', 6),
(3, 'Menu Classique', 8, 30, 'Classique', 'Menu traditionnel pour repas convivial.', 8),
(4, 'Test', 10, 25, 'Végétarien', 'Exotique', 10),
(5, 'test', 20, 20, 'Classique', 'Test', 20),
(6, 'zqdqzdqz', 20, 20, 'Classique', 'dzq', 20);

-- --------------------------------------------------------

--
-- Structure de la table `plat`
--

DROP TABLE IF EXISTS `plat`;
CREATE TABLE IF NOT EXISTS `plat` (
  `plat_id` int NOT NULL AUTO_INCREMENT,
  `titre_plat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` blob,
  PRIMARY KEY (`plat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `possede_utilisateur_role`
--

DROP TABLE IF EXISTS `possede_utilisateur_role`;
CREATE TABLE IF NOT EXISTS `possede_utilisateur_role` (
  `utilisateur_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`utilisateur_id`),
  KEY `fk_pur_role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `possede_utilisateur_role`
--

INSERT INTO `possede_utilisateur_role` (`utilisateur_id`, `role_id`) VALUES
(5, 1);

-- --------------------------------------------------------

--
-- Structure de la table `propose_menu_plat`
--

DROP TABLE IF EXISTS `propose_menu_plat`;
CREATE TABLE IF NOT EXISTS `propose_menu_plat` (
  `menu_id` int NOT NULL,
  `plat_id` int NOT NULL,
  PRIMARY KEY (`menu_id`,`plat_id`),
  KEY `fk_pmp_plat` (`plat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `propose_menu_theme`
--

DROP TABLE IF EXISTS `propose_menu_theme`;
CREATE TABLE IF NOT EXISTS `propose_menu_theme` (
  `menu_id` int NOT NULL,
  `theme_id` int NOT NULL,
  PRIMARY KEY (`menu_id`),
  KEY `fk_pmt_theme` (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `publie_utilisateur_avis`
--

DROP TABLE IF EXISTS `publie_utilisateur_avis`;
CREATE TABLE IF NOT EXISTS `publie_utilisateur_avis` (
  `avis_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`avis_id`),
  KEY `fk_pua_utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `regime`
--

DROP TABLE IF EXISTS `regime`;
CREATE TABLE IF NOT EXISTS `regime` (
  `regime_id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`regime_id`),
  UNIQUE KEY `uq_regime_libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `regime`
--

INSERT INTO `regime` (`regime_id`, `libelle`) VALUES
(1, 'Classique'),
(3, 'Vegan'),
(2, 'Végétarien');

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int NOT NULL,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`role_id`, `libelle`) VALUES
(1, 'Administrateur');

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

DROP TABLE IF EXISTS `theme`;
CREATE TABLE IF NOT EXISTS `theme` (
  `theme_id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `utilisateur_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse_postale` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utilisateur_id`, `email`, `password`, `prenom`, `telephone`, `ville`, `pays`, `adresse_postale`) VALUES
(2, 'test@test.com', '$2y$10$xmEKcHBb3k8aRpOQA6clqOHWgPBPj/3vYnMXOjD73nrYGOULgneaS', 'Dupont', '0600000000', 'Bordeaux', 'France', '12 rue test'),
(5, 'testadmin@mail.com', 'af11f5af94ac25e27ded5bc50e8940d9', 'Admin', '0666666666', 'Bordeaux', 'France', '2 rue lilas ');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande_menu`
--
ALTER TABLE `commande_menu`
  ADD CONSTRAINT `fk_cm_commande` FOREIGN KEY (`numero_commande`) REFERENCES `commande` (`numero_commande`),
  ADD CONSTRAINT `fk_cm_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`);

--
-- Contraintes pour la table `commande_utilisateur`
--
ALTER TABLE `commande_utilisateur`
  ADD CONSTRAINT `fk_cu_commande` FOREIGN KEY (`numero_commande`) REFERENCES `commande` (`numero_commande`),
  ADD CONSTRAINT `fk_cu_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);

--
-- Contraintes pour la table `contient_plat_allergene`
--
ALTER TABLE `contient_plat_allergene`
  ADD CONSTRAINT `fk_cpa_allergene` FOREIGN KEY (`allergene_id`) REFERENCES `allergene` (`allergene_id`),
  ADD CONSTRAINT `fk_cpa_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`);

--
-- Contraintes pour la table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_regime_libelle` FOREIGN KEY (`regime`) REFERENCES `regime` (`libelle`);

--
-- Contraintes pour la table `possede_utilisateur_role`
--
ALTER TABLE `possede_utilisateur_role`
  ADD CONSTRAINT `fk_pur_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`),
  ADD CONSTRAINT `fk_pur_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);

--
-- Contraintes pour la table `propose_menu_plat`
--
ALTER TABLE `propose_menu_plat`
  ADD CONSTRAINT `fk_pmp_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`),
  ADD CONSTRAINT `fk_pmp_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`);

--
-- Contraintes pour la table `propose_menu_theme`
--
ALTER TABLE `propose_menu_theme`
  ADD CONSTRAINT `fk_pmt_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`),
  ADD CONSTRAINT `fk_pmt_theme` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`theme_id`);

--
-- Contraintes pour la table `publie_utilisateur_avis`
--
ALTER TABLE `publie_utilisateur_avis`
  ADD CONSTRAINT `fk_pua_avis` FOREIGN KEY (`avis_id`) REFERENCES `avis` (`avis_id`),
  ADD CONSTRAINT `fk_pua_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
