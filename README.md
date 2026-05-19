Vite & Gourmand

Application web de gestion de commandes de menus traiteur développée dans le cadre de la formation Développeur Web et Web Mobile.

Présentation du projet

Vite & Gourmand est une application permettant aux utilisateurs de :

consulter des menus traiteur,
passer des commandes en ligne,
gérer leur profil,
consulter leurs commandes.

L’application possède également une interface administrateur permettant :

d’ajouter des menus,
de gérer les commandes,
de gérer les utilisateurs,
de gérer le matériel.

Le projet a été développé avec une architecture simple HTML / CSS / JavaScript / PHP / MySQL.

Technologies utilisées
Front-end
HTML5
CSS3
JavaScript Vanilla
Back-end
PHP 8
PDO
MySQL / MariaDB
Outils
XAMPP
phpMyAdmin
Visual Studio Code
Git / GitHub
Fonctionnalités principales
Utilisateur
Création de compte
Connexion / Déconnexion
Affichage dynamique des menus
Commande de menus
Consultation des commandes
Gestion du profil
Administrateur
Accès sécurisé administrateur
Ajout de menus
Affichage dynamique des menus
Gestion des commandes
Gestion des utilisateurs
Gestion du matériel
Sécurité mise en place
Utilisation des sessions PHP
Vérification des rôles administrateurs
Requêtes SQL préparées avec PDO
Validation des formulaires côté front-end et back-end
Contrôle des accès aux pages administrateur
Installation du projet
1. Cloner le projet
git clone https://github.com/VOTRE-UTILISATEUR/vite-gourmand.git
2. Placer le projet dans le dossier htdocs

Exemple :

C:/xampp/htdocs/vite_gourmand
3. Démarrer XAMPP ou WAMP

Démarrer :

Apache
MySQL
Base de données
1. Ouvrir phpMyAdmin
http://localhost/phpmyadmin
2. Importer la base SQL

Importer le fichier :

vite_gourmand.sql
Accès au site
Front-office
http://localhost/vite_gourmand/PagePrincipale.html
Administration

Compte administrateur : Fourni dans le dossier ECF

Structure du projet
vite_gourmand/
│
├── asset/
│   ├── CSS/
│   ├── JS/
│   └── Images/
│
├── php/
│   ├── db.php
│   ├── Connection.php
│   ├── TraitementCommande.php
│   ├── AjouterMenu.php
│   └── ...
│
├── PagePrincipale.html
├── Menu.html
├── Commande.html
├── Profil.html
└── ...
Auteur

Luc Derchez

Projet réalisé dans le cadre de la formation DWWM.
