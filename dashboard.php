<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
?>

<link rel="stylesheet" href="style.css">
<img src="img/logo_ca25.png" class="background-logo">

<h1>Dashboard CA25</h1>

<a href="simulate.php">Simuler badge</a><br>
<a href="logs.php">Voir logs</a><br>
<a href="logout.php">Déconnexion</a>
