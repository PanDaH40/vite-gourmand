CREATE DATABASE IF NOT EXISTS vite_gourmand
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE vite_gourmand;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : mysql:3306
-- Généré le : mer. 07 oct. 2026 à 23:34
-- Version du serveur : 8.4.11
-- Version de PHP : 8.3.35

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
-- Structure de la table `adapte_menu_regime`
--

CREATE TABLE `adapte_menu_regime` (
  `menu_id` int NOT NULL,
  `regime_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `allergene`
--

CREATE TABLE `allergene` (
  `allergene_id` int NOT NULL,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `avis_id` int NOT NULL,
  `note` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`avis_id`, `note`, `description`, `statut`) VALUES
(1, '5', 'Très bon repas et livraison parfaite.', 'Accepté'),
(2, '5', 'Oui', 'Accepté'),
(3, '5', 'Excellent repas', 'Refusé'),
(4, '5', 'Excellent', 'Accepté'),
(5, '1', 'Nul', 'Refusé');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_commande` date DEFAULT NULL,
  `date_prestation` date DEFAULT NULL,
  `heure_livraison` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix_menu` double DEFAULT NULL,
  `nombre_personne` int DEFAULT NULL,
  `prix_livraison` double DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pret_materiel` tinyint(1) DEFAULT NULL,
  `restitution_materiel` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`numero_commande`, `date_commande`, `date_prestation`, `heure_livraison`, `prix_menu`, `nombre_personne`, `prix_livraison`, `statut`, `pret_materiel`, `restitution_materiel`) VALUES
('CMD-20261001154847-397', '2026-10-01', '2026-10-23', '20:48', 245, 7, 5, 'terminée', 1, 1),
('CMD-20261007001753-920', '2026-10-07', '2026-10-21', '05:17', 1188, 44, 5, 'terminée', 1, 1),
('CMD-20261007011254-529', '2026-10-07', '2026-10-23', '04:12', 210, 7, 5, 'terminée', 1, 1),
('CMD-20261007011818-487', '2026-10-07', '2026-10-09', '06:17', 180, 6, 5, 'refusée', 1, 1),
('CMD-20261007012708-810', '2026-10-07', '2026-10-15', '06:27', 150, 5, 5, 'annulée', 0, 0),
('CMD-20261007013858-414', '2026-10-07', '2026-10-15', '05:38', 150, 5, 5, 'terminée', 0, 0),
('CMD-20261007014103-643', '2026-10-07', '2026-10-16', '03:42', 150, 5, 0, 'refusée', 0, 0),
('CMD-20261007034519-637', '2026-10-07', '2026-10-14', '08:45', 120, 4, 5, 'terminée', 0, 0),
('CMD-20261007040723-878', '2026-10-07', '2026-10-21', '07:07', 300, 10, 95.74, 'terminée', 0, 0),
('CMD-20261007044535-522', '2026-10-07', '2026-10-14', '06:48', 180, 6, 117.98, 'terminée', 0, 0),
('CMD-20261007045119-859', '2026-10-07', '2026-10-14', '09:50', 240, 8, 117.98, 'refusée', 0, 0),
('CMD-20261007062313-698', '2026-10-07', '2026-10-15', '08:26', 150, 5, 260.97, 'terminée', 1, 1),
('CMD-20261007062631-970', '2026-10-07', '2026-10-14', '11:26', 240, 8, 117.98, 'terminée', 1, 1),
('CMD-20261007063846-153', '2026-10-07', '2026-10-20', '10:38', 245, 7, 117.98, 'terminée', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `commande_menu`
--

CREATE TABLE `commande_menu` (
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_menu`
--

INSERT INTO `commande_menu` (`numero_commande`, `menu_id`) VALUES
('CMD-20261007040723-878', 1),
('CMD-20261007044535-522', 1),
('CMD-20261007001753-920', 2),
('CMD-20261007011254-529', 2),
('CMD-20261007011818-487', 2),
('CMD-20261007012708-810', 2),
('CMD-20261007013858-414', 2),
('CMD-20261007014103-643', 2),
('CMD-20261007034519-637', 2),
('CMD-20261007062313-698', 2),
('CMD-20261007045119-859', 3),
('CMD-20261007062631-970', 3),
('CMD-20261001154847-397', 4),
('CMD-20261007063846-153', 4);

-- --------------------------------------------------------

--
-- Structure de la table `commande_utilisateur`
--

CREATE TABLE `commande_utilisateur` (
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `utilisateur_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_utilisateur`
--

INSERT INTO `commande_utilisateur` (`numero_commande`, `utilisateur_id`) VALUES
('CMD-20261001154847-397', 1),
('CMD-20261007034519-637', 1),
('CMD-20261007040723-878', 1),
('CMD-20261007001753-920', 2),
('CMD-20261007011254-529', 2),
('CMD-20261007011818-487', 2),
('CMD-20261007012708-810', 2),
('CMD-20261007013858-414', 2),
('CMD-20261007014103-643', 2),
('CMD-20261007062313-698', 2),
('CMD-20261007044535-522', 7),
('CMD-20261007045119-859', 7),
('CMD-20261007062631-970', 7),
('CMD-20261007063846-153', 7);

-- --------------------------------------------------------

--
-- Structure de la table `contient_plat_allergene`
--

CREATE TABLE `contient_plat_allergene` (
  `plat_id` int NOT NULL,
  `allergene_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_commande`
--

CREATE TABLE `historique_commande` (
  `historique_id` int NOT NULL,
  `numero_commande` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_modification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mode_contact` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `historique_commande`
--

INSERT INTO `historique_commande` (`historique_id`, `numero_commande`, `statut`, `date_modification`, `mode_contact`, `motif`) VALUES
(1, 'CMD-20261007040723-878', 'acceptée', '2026-10-07 05:31:29', NULL, NULL),
(2, 'CMD-20261007040723-878', 'en préparation', '2026-10-07 05:32:54', NULL, NULL),
(3, 'CMD-20261007040723-878', 'en cours de livraison', '2026-10-07 05:33:44', NULL, NULL),
(4, 'CMD-20261007040723-878', 'livré', '2026-10-07 05:34:14', NULL, NULL),
(5, 'CMD-20261007040723-878', 'terminée', '2026-10-07 05:35:13', NULL, NULL),
(6, 'CMD-20261007044535-522', 'acceptée', '2026-10-07 06:08:20', NULL, NULL),
(7, 'CMD-20261007044535-522', 'en préparation', '2026-10-07 06:08:22', NULL, NULL),
(8, 'CMD-20261007044535-522', 'en cours de livraison', '2026-10-07 06:08:23', NULL, NULL),
(9, 'CMD-20261007044535-522', 'livré', '2026-10-07 06:08:26', NULL, NULL),
(10, 'CMD-20261007044535-522', 'terminée', '2026-10-07 06:08:30', NULL, NULL),
(11, 'CMD-20261007045119-859', 'refusée', '2026-10-07 06:20:38', 'mail', 'Test refus'),
(12, 'CMD-20261007062313-698', 'acceptée', '2026-10-07 06:23:51', NULL, NULL),
(13, 'CMD-20261007062313-698', 'en préparation', '2026-10-07 06:23:53', NULL, NULL),
(14, 'CMD-20261007062313-698', 'en cours de livraison', '2026-10-07 06:23:55', NULL, NULL),
(15, 'CMD-20261007062313-698', 'livré', '2026-10-07 06:24:00', NULL, NULL),
(16, 'CMD-20261007062313-698', 'en attente du retour de matériel', '2026-10-07 06:24:29', NULL, NULL),
(17, 'CMD-20261007062313-698', 'terminée', '2026-10-07 06:24:33', NULL, NULL),
(18, 'CMD-20261007062631-970', 'acceptée', '2026-10-07 06:27:01', NULL, NULL),
(19, 'CMD-20261007062631-970', 'en préparation', '2026-10-07 06:27:03', NULL, NULL),
(20, 'CMD-20261007062631-970', 'en cours de livraison', '2026-10-07 06:27:04', NULL, NULL),
(21, 'CMD-20261007062631-970', 'livré', '2026-10-07 06:27:12', NULL, NULL),
(22, 'CMD-20261007062631-970', 'en attente du retour de matériel', '2026-10-07 06:28:06', NULL, NULL),
(23, 'CMD-20261007062631-970', 'terminée', '2026-10-07 06:39:47', NULL, NULL),
(24, 'CMD-20261007063846-153', 'acceptée', '2026-10-07 06:39:50', NULL, NULL),
(25, 'CMD-20261007063846-153', 'en préparation', '2026-10-07 06:39:51', NULL, NULL),
(26, 'CMD-20261007063846-153', 'en cours de livraison', '2026-10-07 06:39:52', NULL, NULL),
(27, 'CMD-20261007063846-153', 'livré', '2026-10-07 06:39:54', NULL, NULL),
(28, 'CMD-20261007063846-153', 'en attente du retour de matériel', '2026-10-07 06:39:57', NULL, NULL),
(29, 'CMD-20261007063846-153', 'terminée', '2026-10-07 06:41:26', NULL, NULL),
(30, 'CMD-20261007034519-637', 'en préparation', '2026-10-07 07:07:34', NULL, NULL),
(31, 'CMD-20261007034519-637', 'en cours de livraison', '2026-10-07 07:07:36', NULL, NULL),
(32, 'CMD-20261007034519-637', 'livré', '2026-10-07 07:07:37', NULL, NULL),
(33, 'CMD-20261007034519-637', 'terminée', '2026-10-07 07:07:40', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `horaire`
--

CREATE TABLE `horaire` (
  `horaire_id` int NOT NULL,
  `jour` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heure_ouverture` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heure_fermeture` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `horaire`
--

INSERT INTO `horaire` (`horaire_id`, `jour`, `heure_ouverture`, `heure_fermeture`) VALUES
(1, 'Lundi', '09:00', '19:00'),
(2, 'Mardi', '08:00', '20:00'),
(3, 'Mercredi', '08:00', '20:00'),
(4, 'Jeudi', '08:00', '20:00'),
(5, 'Vendredi', '08:00', '20:00'),
(6, 'Samedi', '09:00', '20:00');

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int NOT NULL,
  `titre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_personne_minimum` int DEFAULT NULL,
  `prix_par_personne` double DEFAULT NULL,
  `regime` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantite_restante` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`menu_id`, `titre`, `nombre_personne_minimum`, `prix_par_personne`, `regime`, `description`, `quantite_restante`) VALUES
(1, 'Menu Pâques', 6, 30, 'Classique', 'Menu festif pour célébrer Pâques.', 15),
(2, 'Menu Noël', 4, 30, 'Classique', 'Menu spécial fêtes de fin d’année.', 15),
(3, 'Menu Classique', 8, 30, 'Classique', 'Menu traditionnel pour vos événements.', 10),
(4, 'Menu Anniversaire', 5, 35, 'Classique', 'Menu convivial pour fêter un anniversaire.', 3),
(9, 'Test', 5, 25, 'Végétarien', 'Test', 50);

-- --------------------------------------------------------

--
-- Structure de la table `plat`
--

CREATE TABLE `plat` (
  `plat_id` int NOT NULL,
  `titre_plat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `plat`
--

INSERT INTO `plat` (`plat_id`, `titre_plat`, `photo`) VALUES
(2, 'Saumon rôti', NULL),
(3, 'Fondant au chocolat', NULL),
(4, 'Tarte aux pommes', NULL),
(5, 'Pâtes au saumon', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `possede_utilisateur_role`
--

CREATE TABLE `possede_utilisateur_role` (
  `utilisateur_id` int NOT NULL,
  `role_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `possede_utilisateur_role`
--

INSERT INTO `possede_utilisateur_role` (`utilisateur_id`, `role_id`) VALUES
(2, 1),
(3, 2),
(1, 3),
(7, 3),
(8, 3);

-- --------------------------------------------------------

--
-- Structure de la table `propose_menu_plat`
--

CREATE TABLE `propose_menu_plat` (
  `menu_id` int NOT NULL,
  `plat_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `propose_menu_plat`
--

INSERT INTO `propose_menu_plat` (`menu_id`, `plat_id`) VALUES
(2, 3),
(1, 4),
(4, 5);

-- --------------------------------------------------------

--
-- Structure de la table `propose_menu_theme`
--

CREATE TABLE `propose_menu_theme` (
  `menu_id` int NOT NULL,
  `theme_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `publie_utilisateur_avis`
--

CREATE TABLE `publie_utilisateur_avis` (
  `avis_id` int NOT NULL,
  `utilisateur_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `publie_utilisateur_avis`
--

INSERT INTO `publie_utilisateur_avis` (`avis_id`, `utilisateur_id`) VALUES
(1, 1),
(2, 2),
(3, 2),
(4, 7),
(5, 7);

-- --------------------------------------------------------

--
-- Structure de la table `regime`
--

CREATE TABLE `regime` (
  `regime_id` int NOT NULL,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `role` (
  `role_id` int NOT NULL,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`role_id`, `libelle`) VALUES
(1, 'Administrateur'),
(2, 'Utilisateur'),
(3, 'Employé');

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

CREATE TABLE `theme` (
  `theme_id` int NOT NULL,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `utilisateur_id` int NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse_postale` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utilisateur_id`, `email`, `password`, `prenom`, `telephone`, `ville`, `pays`, `adresse_postale`, `actif`) VALUES
(1, 'Test@mail.com', 'P_yk1v8OCUFX6QlZuRh3ZnGrN-mgambU7NoQYwAniaNpXSV3T', 'Test', '0666666666', 'Pau', 'France', '2 rue Lilas', 1),
(2, 'admin@vitegourmand.fr', 'P7kU0IipLnUHZw6AiC4W7O_KBcnoS6SE3YvchaEnFScMiI-Pj', 'Admin', '0600000000', 'Bordeaux Centre', 'France', '1 rue de Bordeaux', 1),
(3, 'Test2@mail.com', 'PczXFshZXEJ6bzOl99n8PQQJP13ZHCdVCScudHzNTC8faZZFt', 'Test2', '0666666666', 'Pau', 'France', 'Pau', 1),
(4, 'Test3@mail.com', 'PSine0SUuOBLthWhUGMKWapF6Gx7pt6AQe-9FqUsGFTbrYhRR', 'Test3', '0666666666', 'Pay', 'France', 'Pau', 1),
(5, 'Test3@mail.com', 'e43edece53127b593f7948de6e63ceb5', 'Test4', '0666666666', 'Pay', 'France', 'Pau', 1),
(6, 'Test56@mail.com', 'PiiVdHCOJ8jckRMysdUoBhSQqME1U8VQhV_GHole9p_9YzFAL', 'Test56', '0666666666', 'Pau', 'France', 'Pau', 1),
(7, 'employe@example.com', 'Pwry97EkpswMHvJieuX50st7lPvDrKiP3i-ZyCFmyEB7HjA8e', 'Employe', '0666666666', 'Pau', 'France', '2 rue Lilas', 1),
(8, 'compte-test@example.com', 'Pi2mrcVsW3Sg4ieYaSlQlU93I5Z0beJmiPsgewG1etMH2nhUe', NULL, NULL, NULL, NULL, NULL, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adapte_menu_regime`
--
ALTER TABLE `adapte_menu_regime`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `fk_amr_regime` (`regime_id`);

--
-- Index pour la table `allergene`
--
ALTER TABLE `allergene`
  ADD PRIMARY KEY (`allergene_id`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`avis_id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD UNIQUE KEY `numero_commande` (`numero_commande`);

--
-- Index pour la table `commande_menu`
--
ALTER TABLE `commande_menu`
  ADD PRIMARY KEY (`numero_commande`),
  ADD KEY `fk_cm_menu` (`menu_id`);

--
-- Index pour la table `commande_utilisateur`
--
ALTER TABLE `commande_utilisateur`
  ADD PRIMARY KEY (`numero_commande`),
  ADD KEY `fk_cu_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `contient_plat_allergene`
--
ALTER TABLE `contient_plat_allergene`
  ADD PRIMARY KEY (`plat_id`,`allergene_id`),
  ADD KEY `fk_cpa_allergene` (`allergene_id`);

--
-- Index pour la table `historique_commande`
--
ALTER TABLE `historique_commande`
  ADD PRIMARY KEY (`historique_id`),
  ADD KEY `fk_historique_commande` (`numero_commande`);

--
-- Index pour la table `horaire`
--
ALTER TABLE `horaire`
  ADD PRIMARY KEY (`horaire_id`);

--
-- Index pour la table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `fk_menu_regime_libelle` (`regime`);

--
-- Index pour la table `plat`
--
ALTER TABLE `plat`
  ADD PRIMARY KEY (`plat_id`);

--
-- Index pour la table `possede_utilisateur_role`
--
ALTER TABLE `possede_utilisateur_role`
  ADD PRIMARY KEY (`utilisateur_id`),
  ADD KEY `fk_pur_role` (`role_id`);

--
-- Index pour la table `propose_menu_plat`
--
ALTER TABLE `propose_menu_plat`
  ADD PRIMARY KEY (`menu_id`,`plat_id`),
  ADD KEY `fk_pmp_plat` (`plat_id`);

--
-- Index pour la table `propose_menu_theme`
--
ALTER TABLE `propose_menu_theme`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `fk_pmt_theme` (`theme_id`);

--
-- Index pour la table `publie_utilisateur_avis`
--
ALTER TABLE `publie_utilisateur_avis`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `fk_pua_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `regime`
--
ALTER TABLE `regime`
  ADD PRIMARY KEY (`regime_id`),
  ADD UNIQUE KEY `uq_regime_libelle` (`libelle`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Index pour la table `theme`
--
ALTER TABLE `theme`
  ADD PRIMARY KEY (`theme_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`utilisateur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `allergene`
--
ALTER TABLE `allergene`
  MODIFY `allergene_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `historique_commande`
--
ALTER TABLE `historique_commande`
  MODIFY `historique_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `horaire`
--
ALTER TABLE `horaire`
  MODIFY `horaire_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `plat`
--
ALTER TABLE `plat`
  MODIFY `plat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `regime`
--
ALTER TABLE `regime`
  MODIFY `regime_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `theme`
--
ALTER TABLE `theme`
  MODIFY `theme_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `utilisateur_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `adapte_menu_regime`
--
ALTER TABLE `adapte_menu_regime`
  ADD CONSTRAINT `fk_amr_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`),
  ADD CONSTRAINT `fk_amr_regime` FOREIGN KEY (`regime_id`) REFERENCES `regime` (`regime_id`);

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
-- Contraintes pour la table `historique_commande`
--
ALTER TABLE `historique_commande`
  ADD CONSTRAINT `fk_historique_commande` FOREIGN KEY (`numero_commande`) REFERENCES `commande` (`numero_commande`) ON DELETE CASCADE;

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
