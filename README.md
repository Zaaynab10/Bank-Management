# Bank Management

## Description
**Bank Management** est une application de gestion bancaire permettant :
- **Aux utilisateurs** : De créer des comptes bancaires, effectuer des transactions (dépôts, retraits, transferts) et consulter leurs historiques de transactions.
- **Aux administrateurs** : De superviser les comptes, les transactions, et de suivre l'activité via des outils analytiques.

Ce projet a été réalisé dans le cadre de notre formation à l'EFREI (2ème année du Bachelor Développement Web & Application) pour la matière *Challenge Web*.

---

## Équipe de développement
- **Eliott CRESSIAUX**
- **Kwameh DHEGBO**
- **Ndeye Seynabou DIAW**
- **Ismail IBRAHIMI**

---

## Fonctionnalités

### Côté Utilisateur
- Création de comptes bancaires.
- Dépôt de fonds.
- Retrait de fonds.
- Transferts entre comptes.
- Consultation des transactions pour chaque compte.
- Interface utilisateur moderne, responsive et conviviale.

### Côté Administrateur
- **Gestion des utilisateurs** :
  - Ajout, modification et suppression d'utilisateurs.
- **Tableau de bord admin** :
  - Vue globale des comptes, transactions et indicateurs clés.
  - Outils d'analyse graphique.
- **Gestion des transactions** :
  - Consultation et annulation des transactions en cas d'erreur.
- **Supervision analytique** :
  - Suivi des activités via des graphiques interactifs.

---

## Navigation dans le site

### Côté utilisateur
- **Page d'accueil (Login)** : Connexion avec email et mot de passe.
- **Tableau de bord utilisateur** :
  - Aperçu des comptes bancaires et solde total.
  - Boutons rapides pour effectuer un dépôt, un retrait ou un transfert.
- **Page des transactions** :
  - Liste des transactions avec détails (montant, date, type).
- **Déconnexion** : Redirection vers la page de connexion.

### Côté administrateur
- **Page de connexion admin** : Accès sécurisé avec identifiants spécifiques.
- **Tableau de bord admin** :
  - Indicateurs principaux : nombre total d'utilisateurs, comptes bancaires, et transactions.
  - Navigation par icônes pour explorer les détails.
- **Gestion des utilisateurs** :
  - Ajouter un utilisateur via un formulaire.
  - Modifier ou supprimer des utilisateurs existants.
- **Gestion des transactions** :
  - Vue complète des transactions.
  - Option d'annulation pour chaque transaction.
- **Déconnexion** : Redirection vers la page d'accueil admin.

---

## Technologies utilisées
- **Framework** : Symfony
- **Base de données** : PostgreSQL
- **Frontend** : HTML, CSS, JavaScript
- **Backend** : PHP

---

## Installation

### Prérequis
- **PHP 8.1** ou version ultérieure.
- **Composer**.
- **PostgreSQL**.
- **Node.js** *(facultatif, pour la gestion avancée des assets)*.

### Étapes d'installation
1. **Clonez le projet** :
   ```bash
   git clone https://github.com/Zaaynab10/Bank-Management.git
   ```

2. **Naviguez dans le dossier du projet** :
   ```bash
   cd Bank-Management
   ```

3. **Installez les dépendances PHP avec Composer** :
   ```bash
   composer install
   ```

4. **Configurez le fichier `.env`** :
   Remplacez `DATABASE_URL` par vos informations PostgreSQL :
   ```dotenv
   DATABASE_URL="postgresql://<username>:<password>@127.0.0.1:5432/<nom_de_la_base>?serverVersion=16&charset=utf8"
   ```

5. **Créez la base de données** :
   ```bash
   php bin/console doctrine:database:create
   ```

6. **Appliquez les migrations** :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

7. **Lancez le serveur Symfony** :
   ```bash
   symfony server:start
   ```

8. **Accédez à l'application** : [http://localhost:8000](http://localhost:8000)

---

## Structure du projet
- `src/Controller` : Contient les contrôleurs Symfony.
- `src/Entity` : Contient les entités Doctrine.
- `templates/` : Contient les fichiers Twig pour le frontend.
- `public/CSS` : Contient les fichiers CSS pour le design.
- `public/JS` : Contient les fichiers JavaScript.
- `public/videos` : Contient les vidéos utilisées pour les arrière-plans.

---

## Améliorations futures
- Mise en place d'un système de notifications en temps réel.
- Optimisation de la gestion des rôles utilisateurs.
- Intégration d'une API pour les taux de change.

---

Si vous avez des questions ou souhaitez contribuer, n'hésitez pas à **ouvrir une issue** ou **soumettre une pull request** sur GitHub. 🚀

---
