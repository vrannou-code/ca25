<?php

// ======================================================
// CONFIGURATION DE LA CONNEXION À LA BASE DE DONNÉES
// Projet CA25 - Gestion des badges RFID
// BTS CIEL - Virginie R.
// ======================================================


// -----------------------------
// Paramètres de connexion MySQL
// -----------------------------
$host = "localhost";
$db   = "ca25";
$user = "root";
$pass = "********";


// -----------------------------
// Connexion à la base de données
// -----------------------------
$conn = new mysqli($host, $user, $pass, $db);


// -----------------------------
// Vérification des erreurs
// -----------------------------
if ($conn->connect_error) {

    die(
        "Erreur de connexion à la base de données : "
        . $conn->connect_error
    );
}


// -----------------------------
// Encodage UTF-8
// Permet de gérer correctement
// les accents et caractères spéciaux
// -----------------------------
$conn->set_charset("utf8mb4");

?>