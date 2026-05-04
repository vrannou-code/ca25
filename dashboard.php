<?php
session_start();
include("config.php");

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

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

    <a href="simulate.php" class="btn">Simuler badge</a><br><br>
    <a href="logs.php" class="btn">Voir logs</a><br><br>
    <a href="logout.php" class="btn">Déconnexion</a>
</div>
