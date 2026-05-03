<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uid = $_POST['uid'];

    $stmt = $conn->prepare("SELECT * FROM Carte WHERE RFID=? AND active=1");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $idUser = $row['idUser'];

        echo "ACCES AUTORISE";

        $conn->query("INSERT INTO Acces_log (Resultat_tentative, idUser, UID) VALUES ('ACCES_OK', $idUser, '$uid')");
    } else {
        echo "ACCES REFUSE";

        $conn->query("INSERT INTO Acces_log (Resultat_tentative, UID) VALUES ('REFUSE', '$uid')");
    }
}
?>

<form method="post">
    <h2>Simulation badge</h2>
    <input type="text" name="uid" placeholder="UID RFID">
    <button type="submit">Tester</button>
</form>>
