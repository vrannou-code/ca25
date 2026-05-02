<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
?>

<h1>Dashboard CA25</h1>

<a href="simulate.php">Simuler badge</a><br>
<a href="logs.php">Voir logs</a><br>
<a href="logout.php">Déconnexion</a>
