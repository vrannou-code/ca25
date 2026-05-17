<?php
session_name("CA25SESSID");

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
include("config.php");

$totalBadges = $conn->query("SELECT COUNT(*) AS total FROM Carte")->fetch_assoc()['total'];
$badgesActifs = $conn->query("SELECT COUNT(*) AS total FROM Carte WHERE active = 1")->fetch_assoc()['total'];
$badgesInactifs = $conn->query("SELECT COUNT(*) AS total FROM Carte WHERE active = 0")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM User WHERE SuperUser = 0")->fetch_assoc()['total'];
$accesOK = $conn->query("SELECT COUNT(*) AS total FROM Acces_log WHERE Resultat_tentative = 'ACCES_OK'")->fetch_assoc()['total'];
$accesRefuses = $conn->query("SELECT COUNT(*) AS total FROM Acces_log WHERE Resultat_tentative = 'BADGE_INCONNU'")->fetch_assoc()['total'];

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

<div class="stats">
    <div class="card-stat">
        <h3><?php echo $totalBadges; ?></h3>
        <p>Badges</p>
    </div>

    <div class="card-stat">
        <h3><?php echo $badgesActifs; ?></h3>
        <p>Actifs</p>
    </div>

    <div class="card-stat">
        <h3><?php echo $badgesInactifs; ?></h3>
        <p>Inactifs</p>
    </div>

    <div class="card-stat">
        <h3><?php echo $accesOK; ?></h3>
        <p>Accès autorisés</p>
    </div>

    <div class="card-stat">
        <h3><?php echo $accesRefuses; ?></h3>
        <p>Accès refusés</p>
    </div>

    <div class="card-stat">
        <h3><?php echo $totalUsers; ?></h3>
        <p>Utilisateurs</p>
    </div>
</div>

    <div class="buttons">
        <a href="simulate.php" class="btn">Simuler badge</a>
        <a href="logs.php" class="btn">Voir logs</a>
        <?php if ($_SESSION['role'] == 1) { ?>
            <a href="admin_logs.php" class="btn">Journal admin</a>
        <?php } ?>
        <?php if ($_SESSION['role'] == 1) { ?>
        <a href="badges.php" class="btn">Gérer badges</a>
        <?php } ?>
        <a href="logout.php" class="btn">Déconnexion</a>
    </div>
</div>

<footer>
CA25 - Application de gestion des badges RFID<br>
BTS CIEL - Virginie R.
</footer>
