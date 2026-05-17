# CA25 - Gestion des badges RFID

## Description

CA25 est une application web développée en PHP permettant de gérer un système de contrôle d’accès par badges RFID.

Le projet a été réalisé dans le cadre du BTS CIEL, option IR.

L’objectif est de simuler et administrer un système d’accès sécurisé avec :
- gestion des utilisateurs ;
- gestion des badges RFID ;
- simulation de passage de badge ;
- journalisation des accès ;
- journalisation des actions administrateur.

---

## Fonctionnalités

- Connexion administrateur
- Gestion des sessions
- Ajout d’utilisateurs
- Création de comptes applicatifs
- Attribution de badges RFID
- Activation / désactivation de badges
- Suppression de badges
- Suppression d’utilisateurs
- Simulation de badge RFID
- Journal des accès
- Journal administrateur
- Filtrage des logs
- Export CSV des logs
- Impression / export PDF via navigateur

---

## Technologies utilisées

- PHP
- MariaDB / MySQL
- HTML
- CSS
- Apache
- Visual Studio Code
- Git

---

## Installation

1. Installer Apache
2. Installer PHP
3. Installer MariaDB ou MySQL
4. Importer la base de données du projet
5. Copier les fichiers du projet dans :

```bash
/var/www/html/ca25/
```

6. Configurer la connexion à la base de données dans :

```bash
config.php
```

7. Accéder à l’application depuis un navigateur :

```text
http://adresse_ip_du_serveur/ca25/
```

---

## Sécurité

Le projet intègre plusieurs mesures de sécurité :

- Requêtes SQL préparées contre les injections SQL
- Hachage des mots de passe avec `password_hash()`
- Vérification des mots de passe avec `password_verify()`
- Protection CSRF sur les formulaires sensibles
- Sessions PHP sécurisées
- Cookies de session en `httponly`
- Paramètre `SameSite=Strict`
- Expiration automatique de session
- Préparation de l'application pour une utilisation HTTPS
- Protection XSS avec `htmlspecialchars()`
- Journalisation des actions administrateur

---

## Structure des fichiers principaux

```text
config.php              Connexion à la base de données
csrf.php                Génération et vérification des tokens CSRF
index.php               Page de connexion
dashboard.php           Tableau de bord principal
simulate.php            Simulation d’un passage de badge RFID
logs.php                Journal des accès
admin_logs.php          Journal des actions administrateur
badges.php              Gestion des utilisateurs et badges
export_logs_csv.php     Export CSV des logs d’accès
logout.php              Déconnexion utilisateur
style.css               Mise en forme de l’application
```

---

## Utilisation

1. Se connecter avec un compte administrateur
2. Accéder au tableau de bord
3. Ajouter des utilisateurs
4. Ajouter ou attribuer des badges RFID
5. Simuler le passage d’un badge
6. Consulter les logs d’accès
7. Exporter les logs si nécessaire
8. Consulter le journal administrateur

---

## Améliorations possibles

- Ajout d’une authentification renforcée avec 2FA
- Connexion futur avec un lecteur RFID réel
- Développement futur d’une API pour communiquer avec du matériel externe
- Gestion plus fine des rôles utilisateurs
- Amélioration de l’interface responsive
- Ajout de statistiques avancées
- Sauvegarde automatique des journaux

---

## Auteur

Virginie R.  
BTS CIEL - Option IR