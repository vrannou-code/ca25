<?php
session_start();

// Vérification session admin
if (!isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

include("config.php");
$message = "";

// Simulation d'un passage de badge RFID
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            $conn->query("INSERT INTO Acces_log (Resultat_tentative, idUser, UID) VALUES ('ACCES_OK', $idUser, '$uid')");
    } else {
        $message =  "<p class='error'>BADGE INACTIF</p>";

        $conn->query("INSERT INTO Acces_log (Resultat_tentative, UID) VALUES ('BADGE_INACTIF', '$uid')");
    }
    } else {
        $message = "<p class='error'>ACCES REFUSE</p>";
        $conn->query("INSERT INTO Acces_log (Resultat_tentative, UID) VALUES ('BADGE_INCONNU', '$uid')");
    }
    }
}

// Interface simulation
?>

<div class="container">
<link rel="stylesheet" href="style.css">
<img src="img/logo_ca25.png" class="background-logo">
<form method="post">
    <h1>Simulation badge</h1>
<?php echo $message; ?>
    <input type="text" name="uid" placeholder="UID RFID">
    <button type="submit">Tester</button>
</form>

<br><br>
<a href="dashboard.php" class="btn retour">Retour</a>
</div>

<footer>
    <p>CA25 - Application de gestion des badges RFID</p>
    <p>BTS CIEL - Virginie R.</p>
</footer>
