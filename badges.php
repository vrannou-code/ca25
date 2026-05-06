<?php
session_start();
include("config.php");

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Ajout utilisateur 
if (isset($_POST["add_user"])) {
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);

    if ($nom != "" && $prenom != "") {
        $stmt = $conn->prepare("INSERT INTO User (Nom, Prenom, SuperUser) VALUES (?, ?, 0)");
        $stmt->bind_param("ss", $nom, $prenom);
        $stmt->execute();

       header("Location: badges.php");
       exit();
    }
}


if (isset($_POST["add_badge"])) {
    $rfid = $_POST["rfid"];
    $idUser = intval($_POST["idUser"]);

    $check = $conn->prepare("SELECT idCarte FROM Carte WHERE RFID = ?");
    $check->bind_param("s", $rfid);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        $message = "<p class='error'>Ce badge existe déjà</p>";
    } else {
    $stmt = $conn->prepare("INSERT INTO Carte (RFID, idUser, active) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $rfid, $idUser);
    $stmt->execute();

    header("Location: badges.php");
    exit();
    }
}


// Toggle actif / inactif
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE Carte SET active = 1 - active WHERE idCarte = $id");
    header("Location: badges.php");
    exit();
}

$users = $conn->query("SELECT idUser, Nom, Prenom FROM User ORDER BY Nom");
$usersTable = $conn->query("SELECT idUser, Nom, Prenom FROM User ORDER BY Nom");
// Suppression badge
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    if ($id <= 0) {
        exit("ID invalide");
    }
    $stmt = $conn->prepare("DELETE FROM Carte WHERE idCarte = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: badges.php");
    exit();
}

// Suppression utilisateur
if (isset($_GET['deleteuser'])) {

    $id = intval($_GET['deleteuser']);

    // Supprime les badges liés
    $stmt = $conn->prepare("DELETE FROM Carte WHERE idUser = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Supprime utilisateur
    $stmt = $conn->prepare("DELETE FROM User WHERE idUser = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: badges.php");
    exit();
}


// Récupération badges
$result = $conn->query("SELECT Carte.*, User.Nom, User.Prenom FROM Carte LEFT JOIN User ON Carte.idUser = User.idUser");
?>

<link rel="stylesheet" href="style.css">
<img src="img/logo_ca25.png" class="background-logo">
<div class="container">

<h1>Gestion des badges</h1>
<?php if (!empty($message)) echo $message; ?>
<h3>Ajouter un badge</h3>

<form method="post">
    <input type="text" name="rfid" placeholder="UID RFID" required>
    <select name="idUser" required>
        <?php while ($user = $users->fetch_assoc()) { ?>
            <option value="<?php echo $user["idUser"]; ?>">
                <?php echo $user["Nom"]." ".$user["Prenom"]; ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit" name="add_badge">Ajouter</button>
</form>

<h3>Ajouter un utilisateur</h3>

<form method="post">
    <input type="text" name="nom" placeholder="Nom" required>
    <input type="text" name="prenom" placeholder="Prénom" required>
    <button type="submi" name="add_user">Ajouter</button>
</form>
<br>

<table class="table">
<tr>
    <th>ID</th>
    <th>RFID</th>
    <th>Utilisateur</th>
    <th>Etat</th>
    <th>Action</th>
    <th>Supprimer</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['idCarte']); ?></td>
    <td><?php echo htmlspecialchars($row['RFID']); ?></td>

    <td>
        <?php
        if (!empty($row['Nom'])) {
            echo htmlspecialchars($row['Nom']." ".$row['Prenom']);
        } else {
            echo "Non assigné";
        }
        ?>
    </td>

    <td>
        <?php
        if ($row["active"] == 1) {
            echo "Actif";
        } else {
            echo "Inactif";
        }
        ?>
    </td>

    <td>
        <?php
        $lien = "badges.php?toggle=" . $row['idCarte'];
        if ($row["active"] == 1) {
            echo "<a href='$lien'>Désactiver</a>";
        } else {
            echo "<a href='$lien'>Activer</a>";
        }
        ?>
    </td>

    <td>
        <a href="badges.php?delete=<?php echo $row["idCarte"]; ?>" onclick="return confirm('Supprimer ce badge ?');">Supprimer
        </a>
    </td>
</tr>
<?php } ?>

</table>

<h3>Liste des utilisateurs</h3>

<table class="table">
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Action</th>
    </tr>

<?php while($u = $usersTable->fetch_assoc()) { ?>

<tr>
    <td><?= $u['idUser'] ?></td>
    <td><?= $u['Nom'] ?></td>
    <td><?= $u['Prenom'] ?></td>
    <td><a href="badges.php?deleteuser=<?= $u['idUser'] ?>"onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer
        </a>
    </td>
</tr>

<?php } ?>

</table>
<br>
<a href="dashboard.php">Retour</a>
</div>
