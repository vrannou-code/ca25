<?php
session_start();

// Vérification session admin
if (!isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

// Expiration automatique de la session
if (!isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
     session_unset();
     session_destroy();
     header("Location: index.php");
     exit();
}

$_SESSION['last_activity'] = time();

include("config.php");

// Préparation des filtres
$where = [];
$params = [];
$types = "";

if (isset($_GET["filtre"]) && $_GET["filtre"] != "") {
    if ($_GET["filtre"] == "ok") {
        $where[] = "Acces_log.Resultat_tentative = ?";
        $params[] = "ACCES_OK";
        $types .= "s";
    } elseif ($_GET["filtre"] == "refus") {
        $where[] = "Acces_log.Resultat_tentative != ?";
        $params[] = "ACCES_OK";
        $types .= "s";
    }
}

if (isset($_GET["user"]) && $_GET["user"] != "") {
    $where[] = "Acces_log.idUser = ?";
    $params[] = intval($_GET["user"]);
    $types .= "i";
}

$whereSQL = "";
if (count($where) > 0) {
    $whereSQL = "WHERE " . implode("AND ", $where);
}

// Récupération des logs d'accès
$sql = "SELECT Acces_log.*, User.Nom, User.Prenom FROM Acces_log LEFT JOIN User ON Acces_log.idUser = User.idUser
        $whereSQL ORDER BY Acces_log.Date_heure_entree DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Récupération des utilisateurs pour le filtre
$users = $conn->query("SELECT idUser, Nom, Prenom FROM User ORDER BY Nom");
?>


<link rel="stylesheet" href="style.css">
<img src="img/logo_ca25.png" class="background-logo">

<div class="container">

<h2>Logs d'accès</h2>

<form method="get">
    <select name="filtre">
        <option value="">Tous</option>
        <option value="ok">Accès autorisé</option>
        <option value="refus">Accès refusé</option>
    </select>

    <select name="user">
        <option value="">Tous les utilisateurs</option>
        <?php while ($user = $users->fetch_assoc()) { ?>
            <option value="<?php echo htmlspecialchars($user["idUser"]); ?>">
                <?php echo htmlspecialchars($user["Nom"] . " " .$user["Prenom"]); ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Filtrer</button>
</form>

<br>

<table class="table">

<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Résultat</th>
    <th>Utilisateur</th>
    <th>UID</th>
</tr>

<?php
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".htmlspecialchars($row["idAcces"])."</td>";
    echo "<td>".htmlspecialchars($row["Date_heure_entree"])."</td>";

    if ($row['Resultat_tentative'] == "ACCES_OK") {
    echo "<td class='success'>Accès autorisé</td>";
    } elseif ($row['Resultat_tentative'] == "BADGE_INACTIF") {
    echo "<td class='warning'>Badge inactif</td>";
    } elseif ($row['Resultat_tentative'] == "BADGE INCONNU") {
    echo "<td class='error'>Badge inconnu</td>";
    } else {
    echo "<td class='error'>Accès refusé</td>";
    }

    if ($row['Nom'] && $row['Prenom']) {
    echo "<td>".htmlspecialchars($row['Nom']." ".$row['Prenom'])."</td>";
    } else {
    echo "<td>Badge inconnu</td>";
    }
    echo "<td>".htmlspecialchars($row["UID"])."</td>";
    echo "</tr>";
}
?>


</table>
<br>
<a href="dashboard.php" class="btn retour">Retour</a>
</div>
<footer>
    <p>CA25 - Application de gestion des badges RFID</p>
    <p>BTS CIEL - Virginie R.</p>
</footer>
