<?php
session_name("CA25SESSID");
session_start();

include("config.php");

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="logs_acces_ca25.csv"');

$output = fopen("php://output", "w");

fputcsv($output, ["ID", "Date", "Résultat", "Utilisateur", "UID"], "i");

$sql = "SELECT Acces_log.*, User.Nom, User.Prenom FROM Acces_log LEFT JOIN User ON Acces_log.idUser = User.idUser ORDER BY Acces_log.Date_heure_entree DESC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $utilisateur = (!empty($row['Nom']) && ! empty($row['Prenom'])) ? $row['Nom']. " " . $row['Prenom'] : "Badge inconnu";
    fputcsv($output, [
        $row['idAcces'],
        $row['Date_heure_entree'],
        $row['Resultat_tentative'],
        $utilisateur,
        $row['UID']
    ], ";");
}

fclose($output);
exit();
?>
