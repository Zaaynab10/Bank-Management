# Bank Management

## Description

Projet de gestion bancaire permettant aux utilisateurs de créer des comptes bancaires, effectuer des transactions (dépôts, retraits, transferts) et consulter leurs historiques de transactions. Ce projet a été réalisé dans le cadre de notre formation à l'EFREI en 2ème année du Bachelor Développement Web & Application, pour la matière "Challenge Web".

### Équipe de développement
- **Eliott CRESSIAUX**
- **Kwameh DHEGBO**
- **Ndeye Seynabou DIAW**
- **Ismail IBRAHIMI**

## Fonctionnalités

### Côté Utilisateur
- **Création de comptes bancaires**
- **Dépôt de fonds**
- **Retrait de fonds**
- **Transferts entre comptes**
- **Consultation des transactions par compte**
- **Interface utilisateur responsive et professionnelle**

### Côté Administrateur
- **Gestion des utilisateurs** : Ajout, modification et suppression d'utilisateurs
- **Tableau de bord admin** : Supervision globale des comptes et des transactions
- **Gestion des transactions** : Ajout, édition et suppression des transactions
- **Vue analytique** : Suivi des activités de l'application avec des graphiques et des données détaillées

## Technologies utilisées
- **Framework** : Symfony
- **Base de données** : PostgreSQL
- **Frontend** : HTML, CSS, JS
- **Backend** : PHP

## Installation

### Prérequis
1. PHP 8.1 ou version ultérieure
2. Composer
3. PostgreSQL
4. Node.js (pour la gestion des assets si nécessaire)

### Étapes
1. Clonez le projet :
   ```bash
   git clone https://github.com/Zaaynab10/Bank-Management.git
   ```
2. Naviguez dans le dossier du projet :
   ```bash
   cd Bank-Management
   ```
3. Installez les dépendances PHP avec Composer :
   ```bash
   composer install
   ```
4. Configurez le fichier `.env` :
   - Remplacez `DATABASE_URL` par les informations de connexion à votre base de données PostgreSQL OU MySQL (sans inclure le mot de passe dans les commits) :
     ```env
     DATABASE_URL="postgresql://<username>:<password>@127.0.0.1:5432/<nom_de_la_base>?serverVersion=16&charset=utf8"
     ```
5. Créez la base de données :
   ```bash
   php bin/console doctrine:database:create
   ```
6. Appliquez les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
7. Lancez le serveur de développement Symfony :
   ```bash
   symfony server:start
   ```
8. Accédez à l'application :
   [http://localhost:8000](http://localhost:8000)

## Structure du projet
- **src/Controller** : Contient les contrôleurs Symfony.
- **src/Entity** : Contient les entités Doctrine.
- **templates/** : Contient les fichiers Twig pour le frontend.
- **public/CSS** : Contient les fichiers CSS pour le design.
- **public/JS** : Contient les fichiers JavaScript.
