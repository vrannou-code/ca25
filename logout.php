<?php

// ======================================================
// DÉCONNEXION DE L'UTILISATEUR
// Projet CA25 - Gestion des badges RFID
// BTS CIEL - Virginie R.
// ======================================================


// ======================================================
// OUVERTURE DE LA SESSION
// ======================================================

session_name("CA25SESSID");
session_start();


// ======================================================
// SUPPRESSION DES DONNÉES DE SESSION
// ======================================================

$_SESSION = [];


// ======================================================
// SUPPRESSION DU COOKIE DE SESSION
// ======================================================

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


// ======================================================
// DESTRUCTION DE LA SESSION
// ======================================================

session_unset();
session_destroy();


// ======================================================
// REDIRECTION VERS LA PAGE DE CONNEXION
// ======================================================

header("Location: index.php");
exit();

?>