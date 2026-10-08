# Vite & Gourmand

## Présentation

Vite & Gourmand est une application web de gestion de commandes pour un service de traiteur situé à Bordeaux.

Ce projet a été réalisé dans le cadre de l'Évaluation en Cours de Formation (ECF).

## Technologies utilisées

- HTML, CSS, JavaScript
- PHP 8.3 et PDO
- MySQL 8.4
- MongoDB 8
- Bootstrap
- Docker et Docker Compose
- PHPMailer

## Fonctionnalités

**Visiteur et client :**
- Consultation et recherche de menus
- Consultation des détails et des plats
- Création et connexion à un compte
- Commande de menus
- Consultation des commandes
- Dépôt d'avis

**Employé :**
- Gestion des menus et des plats
- Gestion des horaires
- Gestion des commandes et de leurs statuts
- Modération des avis

**Administrateur :**
- Création et gestion des comptes employés
- Désactivation et réactivation des comptes
- Consultation des statistiques MongoDB
- Filtrage des statistiques par menu et par dates

## Installation avec Docker

### Prérequis

- Docker Desktop
- Docker Compose

### Démarrage

Ouvrir un terminal à la racine du projet et exécuter :

```bash
docker compose up -d --build
```

Lors du premier démarrage avec un volume MySQL vide, le fichier `SQL/table.sql` initialise la base `vite_gourmand`.

### Accès

- Site web : http://localhost:8080
- phpMyAdmin : http://localhost:8081
- MySQL : `localhost:3307`
- MongoDB : `mongodb://localhost:27018`

## Base de données MySQL

Le script d'initialisation est disponible dans :

`SQL/table.sql`

Il contient la structure des tables, leurs relations et les données de démonstration.

## Base de données MongoDB

Le fichier de démonstration se trouve dans :

`MongoDB/orders_analytics.json`

Pour importer les données :

1. Ouvrir MongoDB Compass.
2. Se connecter à `mongodb://localhost:27018`.
3. Créer la base `vite_gourmand_analytics` si elle n'existe pas.
4. Créer la collection `orders_analytics`.
5. Utiliser **Add Data → Import JSON or CSV file**.
6. Sélectionner `MongoDB/orders_analytics.json` et importer les documents.

Les statistiques sont ensuite consultables depuis le tableau de bord Administrateur.

## Configuration des e-mails

L'application utilise PHPMailer pour l'envoi des e-mails.

Créer un fichier `.env` à la racine du projet contenant :

```env
GMAIL_APP_PASSWORD=mot_de_passe_application
```

Un mot de passe d'application Gmail valide est nécessaire pour activer l'envoi des e-mails.

Ne pas publier ce fichier sur GitHub.

## Arrêt du projet

```bash
docker compose down
```

Cette commande arrête les conteneurs sans supprimer les volumes de données.

## Auteur

Projet réalisé dans le cadre de l'ECF de développement web.