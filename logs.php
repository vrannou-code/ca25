<?php
include("config.php");

$result = $conn->query("SELECT * FROM Acces_log ORDER BY idAcces DESC");

echo ">h2>Logs accès</h2>";

while ($row = $result->fetch_assoc()) {
    echo $row['UID'] . " - " . $row['Resultat_tentative'] . " - " . $row['Date_heure_entree'] . "<br>";
}
?>
