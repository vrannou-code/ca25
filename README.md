# CA25 - Gestion des badges

## Description
Application web en PHP permettant de gérer des badges RFID.

Projet réalisé dans le cadre du BTS CIEL (option IR).

Objectif : gérer les accès via badges RFID avec suivi des logs d’accès.

---

## Fonctionnalités

- Ajout d’utilisateurs
- Attribution de badges RFID
- Activation / désactivation des badges
- Suppression de badges
- Filtrage des logs d’accès (autorisé / refusé)
- Historique des accès

---

## Technologies utilisées

- PHP
- MySQL
- HTML / CSS

---

## Installation

1. Installer un serveur web (Apache ou Nginx)
2. Installer PHP
3. Installer MySQL
4. Importer la base de données
5. Placer les fichiers dans `/var/www/html/`
6. Accéder à l’application via navigateur

---

## Sécurité

- Gestion des sessions PHP
- Protection des pages admin
- Requêtes SQL préparées (anti-injection)
- Timeout de session

---

## Auteur

Virginie R.

---

## Améliorations possibles

- Ajout d’une authentification renforcée (2FA)
- Interface utilisateur améliorée
- Gestion des rôles utilisateurs
- API pour connexion avec système RFID réel
