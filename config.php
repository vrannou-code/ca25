<?php

// Connexion à la base de données
$host = "localhost";
$db = "ca25";
$user = "root";
$pass = "Ca25@123";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erreur connexion BDD : " . $conn->connect_error);
}

?>
