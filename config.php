<?php
$host = "localhost";
$db = "ca25";
$user = "root";
$pass = "Ca25@123";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erreur connexion BDD : " . $conn->connect_error);
}
?>

<link rel="stylesheet" href="style.css">
