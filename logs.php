<?php
session_start();

if (!isset($_SESSION["admin"])) {
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

include("config.php");

$stmt = $conn->prepare("SELECT * FROM Acces_log ORDER BY idAcces DESC");
$stmt->execute();
$result = $stmt->get_result();
?>


<link rel="stylesheet" href="style.css">
<img src="img/logo_ca25.png" class="background-logo">

<div class="container">

<h2>Logs d'accès</h2>

<table class="table">

<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Résultat</th>
    <th>User</th>
    <th>UID</th>
</tr>

<?php
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row["idAcces"]."</td>";
    echo "<td>".htmlspecialchars($row["Date_heure_entree"])."</td>";
if ($row['Resultat_tentative'] == "ACCES_OK") {
    echo "<td class='success'>Accès autorisé</td>";
} else {
    echo "<td class='error'>Accès refusé</td>";
}
    echo "<td>".htmlspecialchars($row["idUser"])."</td>";
    echo "<td>".htmlspecialchars($row["UID"])."</td>";
    echo "</tr>";
}
?>


</table>
</div>
<br>
<a href="dashboard.php">Retour</a>
