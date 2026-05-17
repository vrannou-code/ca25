<?php

// ======================================================
// EXPORT CSV DES LOGS D'ACCÈS
// Projet CA25 - Gestion des badges RFID
// BTS CIEL - Virginie R.
// ======================================================

session_name("CA25SESSID");
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
// EN-TÊTES HTTP POUR LE TÉLÉCHARGEMENT CSV
// ======================================================

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=logs_acces_ca25.csv");


// ======================================================
// CRÉATION DU FICHIER CSV
// ======================================================

$output = fopen("php://output", "w");

fputcsv($output, [
    "ID",
    "Date",
    "Résultat",
    "Utilisateur",
    "UID"
], ";");


// ======================================================
// RÉCUPÉRATION DES LOGS D'ACCÈS
// ======================================================

$sql = "
    SELECT
        Acces_log.idAcces,
        Acces_log.Date_heure_entree,
        Acces_log.Resultat_tentative,
        Acces_log.UID,
        User.Nom,
        User.Prenom
    FROM Acces_log
    LEFT JOIN User ON Acces_log.idUser = User.idUser
    ORDER BY Acces_log.Date_heure_entree DESC
";

$result = $conn->query($sql);


// ======================================================
// ÉCRITURE DES DONNÉES DANS LE FICHIER CSV
// ======================================================

while ($row = $result->fetch_assoc()) {

    if (!empty($row['Nom']) && !empty($row['Prenom'])) {
        $utilisateur = $row['Nom'] . " " . $row['Prenom'];
    } else {
        $utilisateur = "Badge inconnu";
    }

    fputcsv($output, [
        $row['idAcces'],
        $row['Date_heure_entree'],
        $row['Resultat_tentative'],
        $utilisateur,
        $row['UID']
    ], ";");
}


// ======================================================
// FERMETURE DU FICHIER CSV
// ======================================================

fclose($output);
exit();

?>