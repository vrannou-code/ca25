<?php
session_start();
include("config.php");

//Vérification session admin
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Expiration automatique session
if (!isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
     session_unset();
     session_destroy();
     header("Location: index.php");
     exit();
}

$_SESSION['last_activity'] = time();

?>

<link rel="stylesheet" href="style.css">
<img src="img/logo_ca25.png" class="background-logo">

<div class="container">
    <h1>Dashboard CA25</h1>
    <h3>Menu principal</h3>
    <div class="buttons">
        <a href="simulate.php" class="btn">Simuler badge</a>
        <a href="logs.php" class="btn">Voir logs</a>
        <a href="badges.php" class="btn">Gérer badges</a>
        <a href="logout.php" class="btn">Déconnexion</a>
    </div>
</div>

<footer>
    <p>CA25 - Application de gestion des badges RFID</p>
    <p>BTS CIEL - Virginie R.</p>
</footer>
