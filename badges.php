<?php
session_name("CA25SESSID");

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
include("config.php");
include("csrf.php");

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] != 1) {
    header("Location: dashboard.php");
    exit();
}

// Ajout utilisateur
if (isset($_POST["add_user"])) {

    if (!verify_csrf_token($_POST['csrf_token'] ??'')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);

    if ($nom != "" && $prenom != "") {

        $check = $conn->prepare("SELECT idUser FROM User WHERE Nom = ? AND Prenom = ?");
        $check->bind_param("ss", $nom, $prenom);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "<p class='error'>Cet utilisateur existe déjà</p>";
        } else {

            $stmt = $conn->prepare("INSERT INTO User (Nom, Prenom, Email, Motf, SuperUser) VALUES (?, ?, ?, '', 0)");
            $stmt->bind_param("sss", $nom, $prenom, $email);
            $stmt->execute();

            $admin = $_SESSION['admin'];
            $action = "Ajout utilisateur : " . $nom . " " . $prenom;
            $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
            $log->bind_param("ss", $admin, $action);
            $log->execute();

            header("Location: badges.php");
            exit();
        }
    }
}

//Création compte applicatif

if (isset($_POST['add_account'])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $idUser = intval($_POST['idUser']);
    $identifiant = trim($_POST['identifiant']);
    $password = trim($_POST['password']);
    $role = intval($_POST['role']);

    if ($idUser > 0 && $identifiant != "" && $password != "") {
        $check = $conn->prepare("SELECT idUser FROM User WHERE Identifiant = ? OR (idUser = ? AND Identifiant IS NOT NULL)");
        $check->bind_param("si", $identifiant, $idUser);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "<p class='error'>Cet identifiant existe déjà</p>";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE User SET Identifiant = ?, Motf = ?, SuperUser = ? WHERE idUser = ?");
            $stmt->bind_param("ssii", $identifiant, $hash, $role, $idUser);
            $stmt->execute();

            $admin = $_SESSION['admin'];
            $action = "Création compte applicatif : " . $identifiant . " (ID utilisateur " . $idUser . ")";
            $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
            $log->bind_param("ss", $admin, $action);
            $log->execute();

            header("Location: badges.php");
            exit();
        }
    }
}

// Modification rôle compte applicatif
if (isset($_POST['update_role'])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $idUser = intval($_POST['idUser']);
    $role = intval($_POST['role']);

    $stmt = $conn->prepare("UPDATE User SET SuperUser = ? WHERE idUser = ?");
    $stmt->bind_param("ii", $role, $idUser);
    $stmt->execute();

    $admin = $_SESSION['admin'];

    $userInfo = $conn->prepare("SELECT Nom, Prenom FROM User WHERE idUser = ?");
    $userInfo->bind_param("i", $idUser);
    $userInfo->execute();
    $userResult = $userInfo->get_result();
    $userRow = $userResult->fetch_assoc();
    $action = "Modification rôle : " . $userRow['Nom'] . " " . $userRow['Prenom'] . " (ID " . $idUser . ")";

    $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
    $log->bind_param("ss", $admin, $action);
    $log->execute();

    $_SESSION['message'] = "<p class='success'>Rôle modifié avec succès</p>";

    header("Location: badges.php");
    exit();
}

// Ajout badges
if (isset($_POST["add_badge"])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $rfid = trim(htmlspecialchars($_POST["rfid"]));
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

    $admin = $_SESSION['admin'];
    $action = "Ajout badge RFID : " . $rfid;
    $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
    $log->bind_param("ss", $admin, $action);
    $log->execute();

    header("Location: badges.php");
    exit();
    }
}


// Toggle actif / inactif
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $badgeInfo = $conn->prepare("SELECT RFID FROM Carte WHERE idCarte = ?");
    $badgeInfo->bind_param("i", $id);
    $badgeInfo->execute();
    $badgeResult = $badgeInfo->get_result();
    $badgeRow = $badgeResult->fetch_assoc();
    $stmt = $conn->prepare("UPDATE Carte SET active = 1 - active WHERE idCarte = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $admin =$_SESSION['admin'];

    $check = $conn->prepare("SELECT active FROM Carte WHERE idCarte = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $resultCheck = $check->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    $etat = ($rowCheck['active'] == 1) ? "Activation" : "Désactivation";

    $admin = $_SESSION['admin'];
    $action = $etat . " badge : " . $badgeRow['RFID'] . " (ID " . $id . ")";

    $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
    $log->bind_param("ss", $admin, $action);
    $log->execute();

    header("Location: badges.php");
    exit();
}

$users = $conn->query("SELECT idUser, Nom, Prenom FROM User ORDER BY Nom");
$usersTable = $conn->query("SELECT idUser, Nom, Prenom, Identifiant, SuperUser FROM User ORDER BY Nom");

// Suppression badge
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    if ($id <= 0) {
        exit("ID invalide");
    }

    $badgeInfo = $conn->prepare("SELECT RFID FROM Carte WHERE idCarte = ?");
    $badgeInfo->bind_param("i", $id);
    $badgeInfo->execute();
    $badgeResult = $badgeInfo->get_result();
    $badgeRow = $badgeResult->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM Carte WHERE idCarte = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $admin = $_SESSION['admin'];
    $action = "Suppression badge : " . $badgeRow['RFID'] . " (ID " . $id . ")";

    $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
    $log->bind_param("ss", $admin, $action);
    $log-> execute();

    header("Location: badges.php");
    exit();
}

// Suppression utilisateur
if (isset($_GET['deleteuser'])) {

    $id = intval($_GET['deleteuser']);

    $adminCount = $conn->query("SELECT COUNT(*) AS total FROM User WHERE SuperUser = 1")->fetch_assoc();

    $userRoleCheck = $conn->prepare("SELECT SuperUser FROM User WHERE idUser = ?");
    $userRoleCheck->bind_param("i", $id);
    $userRoleCheck->execute();
    $userRoleResult = $userRoleCheck->get_result();
    $userRole = $userRoleResult->fetch_assoc();

    if ($userRole['SuperUser'] == 1 && $adminCount['total'] <= 1) {
        $_SESSION['message'] = "<p class='error'>Impossible de supprimer le dernier administrateur</p>";
        header("Location: badges.php");
        exit();
    } else {

    $userInfo = $conn->prepare("SELECT Nom, Prenom FROM User WHERE idUser = ?");
    $userInfo->bind_param("i", $id);
    $userInfo->execute();
    $userResult = $userInfo->get_result();
    $userRow = $userResult->fetch_assoc();

    // Supprime les badges liés
    $stmt = $conn->prepare("DELETE FROM Carte WHERE idUser = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Supprime utilisateur
    $stmt = $conn->prepare("DELETE FROM User WHERE idUser = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $admin = $_SESSION['admin'];
    $action = "Suppression utilisateur : " . $userRow['Nom'] . " " . $userRow['Prenom'] . " (ID " . $id . ")";

    $log = $conn->prepare("INSERT INTO Admin_log (admin, action) VALUES (?, ?)");
    $log->bind_param("ss", $admin, $action);
    $log->execute();

    }
    header("Location: badges.php");
    exit();
}


// Récupération badges
$result = $conn->query("
SELECT
    Carte.*,
    User.Nom,
    User.Prenom,
    User.Identifiant
FROM Carte
LEFT JOIN User ON Carte.idUser = User.idUser
");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des badges</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<img src="img/logo_ca25.png" class="background-logo">
<div class="container">

<h1>Gestion des badges</h1>
<?php
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<h3>Ajouter un badge</h3>

<form method="post">

    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

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

    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

    <input type="text" name="nom" placeholder="Nom" required>
    <input type="text" name="prenom" placeholder="Prénom" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit" name="add_user">Ajouter</button>
</form>
<br>

<?php if ($_SESSION['role'] == 1) { ?>

<h3>Créer un compte applicatif</h3>

<form method="post">

    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <select name="idUser" required>
        <option value="">Utilisateur associé</option>
        <?php
        $usersAccount = $conn->query("SELECT idUser, Nom, Prenom FROM User ORDER BY Nom");
        while ($u = $usersAccount->fetch_assoc()) { ?>
            <option value="<?php echo $u['idUser']; ?>">
                <?php echo $u['Nom'] . " " . $u['Prenom']; ?>
            </option>
        <?php } ?>
     <select>

     <input type="text" name="identifiant" placeholder="Identifiant" required>
     <input type="password" name="password" placeholder="Mot de passe temporaire" required>
     <select name="role">
         <option value="0">Utilisateur</option>
         <option value="1">Administrateur</option>
      </select>
      <br><br>

      <button type="submit" name="add_account">Créer compte</button>

</form>

<br>

<?php } ?>

<h3>Liste des badges</h3>

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
        echo ($row["active"] == 1) ? "Actif" : "Inactif";
        ?>
    </td>

    <td>
        <?php
        $lien = "badges.php?toggle=" . intval($row['idCarte']);
        echo ($row["active"] == 1) ?  "<a href='$lien'>Désactiver</a>" : "<a href='$lien'>Activer</a>";
        ?>
    </td>

    <td>
        <a href="badges.php?delete=<?= intval($row["idCarte"]) ?>"onclick="return confirm('Supprimer ce badge ?');">Supprimer
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
        <th>Compte</th>
        <th>Rôle</th>
        <th>Modifier</th>
        <th>Action</th>
    </tr>

<?php while($u = $usersTable->fetch_assoc()) { ?>

<tr>
    <td><?= htmlspecialchars($u['idUser']) ?></td>
    <td><?= htmlspecialchars($u['Nom']) ?></td>
    <td><?= htmlspecialchars($u['Prenom']) ?></td>
    <td><?php echo !empty($u['Identifiant']) ? "Oui" : "Non"; ?></td>
    <td><?php echo ($u['SuperUser'] == 1) ? "Administrateur" : "Utilisateur"; ?></td>
    <td>
    <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="idUser" value="<?= $u['idUser'] ?>">
    <select name="role">
        <option value="0">Utilisateur</option>
        <option value="1">administrateur</option>
    </select>

    <button type="submit" name="update_role">Modifier</button>
    </form>
    </td>
    <td><a href="badges.php?deleteuser=<?= $u['idUser'] ?>"onclick="return confirm('Supprimer cet utilisateur et ses badges associés ?')">Supprimer
        </a>
    </td>
</tr>

<?php } ?>

</table>
<br>
<div style="text-align:center; margin-top:20px;">
    <a href="dashboard.php" class="btn retour">Retour</a>

</div>

</div>

<footer>
CA25 - Application de gestion des badges RFID<br>
BTS CIEL - Virginie R.
</footer>

</body>
</html>
