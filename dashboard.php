<?php

// ======================================================
// CONFIGURATION DE LA SESSION
// ======================================================

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


// ======================================================
// CONTRÔLE D'ACCÈS
// ======================================================

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}


// ======================================================
// EXPIRATION AUTOMATIQUE DE LA SESSION
// ======================================================

if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > 600)
) {
    session_unset();
    session_destroy();

    header("Location: index.php");
    exit();
}

$_SESSION['last_activity'] = time();


// ======================================================
// RÉCUPÉRATION DES STATISTIQUES DU DASHBOARD
// ======================================================

$totalBadges = $conn->query("
    SELECT COUNT(*) AS total
    FROM Carte
")->fetch_assoc()['total'];

$badgesActifs = $conn->query("
    SELECT COUNT(*) AS total
    FROM Carte
    WHERE active = 1
")->fetch_assoc()['total'];

$badgesInactifs = $conn->query("
    SELECT COUNT(*) AS total
    FROM Carte
    WHERE active = 0
")->fetch_assoc()['total'];

$totalUsers = $conn->query("
    SELECT COUNT(*) AS total
    FROM User
    WHERE SuperUser = 0
")->fetch_assoc()['total'];

$accesOK = $conn->query("
    SELECT COUNT(*) AS total
    FROM Acces_log
    WHERE Resultat_tentative = 'ACCES_OK'
")->fetch_assoc()['total'];

$accesRefuses = $conn->query("
    SELECT COUNT(*) AS total
    FROM Acces_log
    WHERE Resultat_tentative = 'BADGE_INCONNU'
")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard CA25</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<img src="img/logo_ca25.png" class="background-logo" alt="Logo CA25">

<div class="container">

    <h1>Dashboard CA25</h1>
    <h3>Menu principal</h3>

    <!-- Cartes statistiques du dashboard -->
    <div class="stats">

        <div class="card-stat">
            <h3><?= $totalBadges; ?></h3>
            <p>Badges</p>
        </div>

        <div class="card-stat">
            <h3><?= $badgesActifs; ?></h3>
            <p>Actifs</p>
        </div>

        <div class="card-stat">
            <h3><?= $badgesInactifs; ?></h3>
            <p>Inactifs</p>
        </div>

        <div class="card-stat">
            <h3><?= $accesOK; ?></h3>
            <p>Accès autorisés</p>
        </div>

        <div class="card-stat">
            <h3><?= $accesRefuses; ?></h3>
            <p>Accès refusés</p>
        </div>

        <div class="card-stat">
            <h3><?= $totalUsers; ?></h3>
            <p>Utilisateurs</p>
        </div>

    </div>

    <!-- Boutons de navigation -->
    <div class="buttons">

        <a href="simulate.php" class="btn">Simuler badge</a>
        <a href="logs.php" class="btn">Voir logs</a>

        <?php if ($_SESSION['role'] == 1) { ?>
            <a href="admin_logs.php" class="btn">Journal admin</a>
            <a href="badges.php" class="btn">Gérer badges</a>
        <?php } ?>

        <a href="logout.php" class="btn">Déconnexion</a>

    </div>

</div>

<footer>
    CA25 - Application de gestion des badges RFID<br>
    BTS CIEL - Virginie R.
</footer>

</body>
</html>