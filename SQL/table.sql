-- Active: 1751677265092@@127.0.0.1@3306
CREATE DATABASE vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vite_gourmand;


-- 1) TABLES
CREATE TABLE utilisateur (
  utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(50),
  password VARCHAR(50),
  prenom VARCHAR(50),
  telephone VARCHAR(50),
  ville VARCHAR(50),
  pays VARCHAR(50),
  adresse_postale VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE role (
  role_id INT PRIMARY KEY,
  libelle VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE avis (
  avis_id INT PRIMARY KEY,
  note VARCHAR(50),
  description VARCHAR(50),
  statut VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE commande (
  numero_commande VARCHAR(50),
  date_commande DATE,
  date_prestation DATE,
  heure_livraison VARCHAR(50),
  prix_menu DOUBLE,
  nombre_personne INT,
  prix_livraison DOUBLE,
  statut VARCHAR(50),
  pret_materiel BOOL,
  restitution_materiel BOOL,
  UNIQUE (numero_commande)
) ENGINE=InnoDB;

CREATE TABLE menu (
  menu_id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(50),
  nombre_personne_minimum INT,
  prix_par_personne DOUBLE,
  regime VARCHAR(50),
  description VARCHAR(50),
  quantite_restante INT
) ENGINE=InnoDB;

CREATE TABLE regime (
  regime_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE theme (
  theme_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE plat (
  plat_id INT AUTO_INCREMENT PRIMARY KEY,
  titre_plat VARCHAR(50),
  photo BLOB
) ENGINE=InnoDB;

CREATE TABLE allergene (
  allergene_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE horaire (
  horaire_id INT AUTO_INCREMENT PRIMARY KEY,
  jour VARCHAR(50),
  heure_ouverture VARCHAR(50),
  heure_fermeture VARCHAR(50)
) ENGINE=InnoDB;


-- 2) RELATIONS

-- utilisateur (1,1) --- possede --- (0,n) role
CREATE TABLE possede_utilisateur_role (
  utilisateur_id INT PRIMARY KEY,
  role_id INT NOT NULL,
  CONSTRAINT fk_pur_utilisateur
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
  CONSTRAINT fk_pur_role
    FOREIGN KEY (role_id) REFERENCES role(role_id)
) ENGINE=InnoDB;

-- utilisateur (1,1) --- publie --- (0,n) avis
CREATE TABLE publie_utilisateur_avis (
  avis_id INT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  CONSTRAINT fk_pua_avis
    FOREIGN KEY (avis_id) REFERENCES avis(avis_id),
  CONSTRAINT fk_pua_utilisateur
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id)
) ENGINE=InnoDB;

-- commande (0,n) --- (1,1) utilisateur
CREATE TABLE commande_utilisateur (
  numero_commande VARCHAR(50) PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  CONSTRAINT fk_cu_commande
    FOREIGN KEY (numero_commande) REFERENCES commande(numero_commande),
  CONSTRAINT fk_cu_utilisateur
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id)
) ENGINE=InnoDB;

-- commande (0,n) --- (1,1) menu
CREATE TABLE commande_menu (
  numero_commande VARCHAR(50) PRIMARY KEY,
  menu_id INT NOT NULL,
  CONSTRAINT fk_cm_commande
    FOREIGN KEY (numero_commande) REFERENCES commande(numero_commande),
  CONSTRAINT fk_cm_menu
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id)
) ENGINE=InnoDB;

-- menu (1,1) --- adapte --- (0,n) regime
CREATE TABLE adapte_menu_regime (
  menu_id INT PRIMARY KEY,
  regime_id INT NOT NULL,
  CONSTRAINT fk_amr_menu
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id),
  CONSTRAINT fk_amr_regime
    FOREIGN KEY (regime_id) REFERENCES regime(regime_id)
) ENGINE=InnoDB;

-- menu (1,1) --- propose --- (0,n) theme
CREATE TABLE propose_menu_theme (
  menu_id INT PRIMARY KEY,
  theme_id INT NOT NULL,
  CONSTRAINT fk_pmt_menu
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id),
  CONSTRAINT fk_pmt_theme
    FOREIGN KEY (theme_id) REFERENCES theme(theme_id)
) ENGINE=InnoDB;

-- menu (1,n) --- propose --- (0,n) plat  => N-N
CREATE TABLE propose_menu_plat (
  menu_id INT NOT NULL,
  plat_id INT NOT NULL,
  PRIMARY KEY (menu_id, plat_id),
  CONSTRAINT fk_pmp_menu
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id),
  CONSTRAINT fk_pmp_plat
    FOREIGN KEY (plat_id) REFERENCES plat(plat_id)
) ENGINE=InnoDB;

-- plat (0,n) --- contient --- (0,n) allergene => N-N
CREATE TABLE contient_plat_allergene (
  plat_id INT NOT NULL,
  allergene_id INT NOT NULL,
  PRIMARY KEY (plat_id, allergene_id),
  CONSTRAINT fk_cpa_plat
    FOREIGN KEY (plat_id) REFERENCES plat(plat_id),
  CONSTRAINT fk_cpa_allergene
    FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id)
) ENGINE=InnoDB;

ALTER TABLE regime
  ADD UNIQUE KEY uq_regime_libelle (libelle);

ALTER TABLE menu
  ADD CONSTRAINT fk_menu_regime_libelle
  FOREIGN KEY (regime)
  REFERENCES regime(libelle);

  INSERT INTO regime (libelle) VALUES
('Classique'),
('Végétarien'),
('Vegan');

INSERT INTO menu
(titre, nombre_personne_minimum, prix_par_personne, regime, description, quantite_restante)
VALUES
('Menu Pâques', 6, 30, 'Classique', 'Menu festif pour célébrer Pâques.', 5),
('Menu Noël', 4, 30, 'Classique', 'Menu spécial fêtes de fin d’année.', 6),
('Menu Classique', 8, 30, 'Classique', 'Menu traditionnel pour repas convivial.', 8);

/*

