<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

include("config.php");

$stmt = $conn->prepare("SELECT * FROM Acces_log ORDER BY idAcces DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Logs d'accès</h2>

<table border="1">
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
    echo "<td>".$row["Date_heure_entree"]."</td>";
    echo "<td>".$row["Resultat_tentative"]."</td>";
    echo "<td>".$row["idUser"]."</td>";
    echo "<td>".$row["UID"]."</td>";
}
?>

</table>

<br>
<a href="dashboard.php">Retour</a>
