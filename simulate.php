<?php
session_name("CA25SESSID");

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite'=> 'Strict'
]);

session_start();

// Vérification session admin
if (!isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

include("config.php");
include("csrf.php");
$message = "";

// Simulation d'un passage de badge RFID
if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Erreur CSRF : requête non autorisée.");
}
    $uid = htmlspecialchars(trim($_POST['uid']));
if (empty($uid)) {
    $message = "<p class='error'>Veuillez entrer un UID</p>";
} else {

// Recherche d'un badge dans la base
    $stmt = $conn->prepare("SELECT * FROM Carte WHERE RFID=?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

// Vérification si le badge est actif
        if ($row['active'] == 1) {
            $idUser = $row['idUser'];
            $message = "<p class='success'>ACCES AUTORISE</p>";


// Enregistrement du résultat dans les logs
            $stmtLog = $conn->prepare("INSERT INTO Acces_log (Resultat_tentative, idUser, UID) Values (?, ?, ?)");
            $resultat = "ACCES_OK";
            $stmtLog->bind_param("sis", $resultat, $idUser, $uid);
            $stmtLog->execute();
    } else {
        $message =  "<p class='warning'>BADGE INACTIF</p>";
        $idUser = $row['idUser'];
        $stmtLog = $conn->prepare("INSERT INTO Acces_log (Resultat_tentative, idUser, UID) VALUES (?, ?, ?)");
        $resultat = "BADGE_INACTIF";
        $stmtLog->bind_param("sis", $resultat, $idUser, $uid);
        $stmtLog->execute();
    }
    } else {
        $message = "<p class='error'>ACCES REFUSE</p>";
        $stmtLog = $conn->prepare("INSERT INTO Acces_log (Resultat_tentative, UID) VALUES (?, ?)");
        $resultat = "BADGE_INCONNU";
        $stmtLog->bind_param("ss", $resultat, $uid);
        $stmtLog->execute();
    }
    }
}

// Interface simulation
?>
<link rel="stylesheet" href="style.css">
<div class="container">

<img src="img/logo_ca25.png" class="background-logo">
<form method="post">
<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <h1>Simulation badge</h1>
    <?php echo $message; ?>
    <div class="simulate-form">
        <input type="text" name="uid" placeholder="UID RFID">
        <button type="submit">Tester</button>
    </div>
</form>

<a href="dashboard.php" class="btn retour">Retour</a>


</div>

<footer>
CA25 - Application de gestion des badges RFID<br>
BTS CIEL - Virginie R.
</footer>
