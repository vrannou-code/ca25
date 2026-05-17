<?php

// ======================================================
// GESTION DE LA PROTECTION CSRF
// Projet CA25 - Gestion des badges RFID
// BTS CIEL - Virginie R.
// ======================================================


// ------------------------------------------------------
// Génération du token CSRF
// ------------------------------------------------------
// Cette fonction crée un token unique stocké
// dans la session utilisateur.
// Le token est ensuite envoyé dans les formulaires
// afin d'empêcher les requêtes frauduleuses.
// ------------------------------------------------------
function generate_csrf_token()
{
    // Vérifie si un token existe déjà
    if (empty($_SESSION['csrf_token'])) {

        // Génère un token sécurisé aléatoire
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Retourne le token
    return $_SESSION['csrf_token'];
}


// ------------------------------------------------------
// Vérification du token CSRF
// ------------------------------------------------------
// Cette fonction compare le token reçu depuis
// le formulaire avec celui enregistré en session.
// ------------------------------------------------------
function verify_csrf_token($token)
{
    return (
        isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token)
    );
}

?>