<?php

// ======================================================
// SIMULATION D'UN PASSAGE DE BADGE RFID
// Projet CA25 - Gestion des badges RFID
// BTS CIEL - Virginie R.
// ======================================================


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
include("csrf.php");


// ======================================================
// CONTRÔLE D'ACCÈS
// ======================================================

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}


// ======================================================
// INITIALISATION
// ======================================================

$message = "";


// ======================================================
// TRAITEMENT DE LA SIMULATION
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $uid = trim(htmlspecialchars($_POST['uid'] ?? ''));

    if (empty($uid)) {

        $message = "<p class='error'>Veuillez entrer un UID.</p>";

    } else {

        // Recherche du badge RFID dans la base
        $stmtBadge = $conn->prepare("
            SELECT *
            FROM Carte
            WHERE RFID = ?
        ");

        $stmtBadge->bind_param("s", $uid);
        $stmtBadge->execute();

        $result = $stmtBadge->get_result();

        if ($result->num_rows === 1) {

            $badge = $result->fetch_assoc();

            // Cas 1 : badge connu et actif
            if ($badge['active'] == 1) {

                $idUser = $badge['idUser'];
                $resultat = "ACCES_OK";

                $message = "<p class='success'>ACCÈS AUTORISÉ</p>";

                $stmtLog = $conn->prepare("
                    INSERT INTO Acces_log (Resultat_tentative, idUser, UID)
                    VALUES (?, ?, ?)
                ");

                $stmtLog->bind_param("sis", $resultat, $idUser, $uid);
                $stmtLog->execute();

            } else {

                // Cas 2 : badge connu mais inactif
                $idUser = $badge['idUser'];
                $resultat = "BADGE_INACTIF";

                $message = "<p class='warning'>BADGE INACTIF</p>";

                $stmtLog = $conn->prepare("
                    INSERT INTO Acces_log (Resultat_tentative, idUser, UID)
                    VALUES (?, ?, ?)
                ");

                $stmtLog->bind_param("sis", $resultat, $idUser, $uid);
                $stmtLog->execute();
            }

        } else {

            // Cas 3 : badge inconnu
            $resultat = "BADGE_INCONNU";

            $message = "<p class='error'>ACCÈS REFUSÉ</p>";

            $stmtLog = $conn->prepare("
                INSERT INTO Acces_log (Resultat_tentative, UID)
                VALUES (?, ?)
            ");

            $stmtLog->bind_param("ss", $resultat, $uid);
            $stmtLog->execute();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Logs CA25</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="login-container">

    <img src="img/logo_ca25.png" class="background-logo">

    <h1>Simulation badge</h1>

    <?php echo $message; ?>

    <!-- Formulaire de simulation RFID -->
<form method="post">

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo generate_csrf_token(); ?>"
    >

    <div class="simulate-form">

        <input
            type="text"
            name="uid"
            placeholder="UID RFID"
        >

        <button type="submit">
            Tester
        </button>

    </div>

</form>

    <!-- Bouton de retour au tableau de bord -->
    <a href="dashboard.php" class="btn retour">
        Retour
    </a>

</div>

<footer>
    CA25 - Application de gestion des badges RFID<br>
    BTS CIEL - Virginie R.
</footer>

</body>
</html>