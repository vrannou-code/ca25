<?php
session_name("CA25SESSID");

// Déconnexion utilisateur
session_start();

$_SESSION = [];

session_unset();
session_destroy();

header("Location: index.php");
exit();
?>
