<?php
// =====================================================
// CONFIGURATION DE LA SESSION
// =====================================================

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


// =====================================================
// CONTRÔLE D'ACCÈS
// =====================================================

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] != 1) {
    header("Location: dashboard.php");
    exit();
}


// =====================================================
// FONCTION DE JOURNALISATION ADMINISTRATEUR
// =====================================================

function logAdmin($conn, $action)
{
    $admin = $_SESSION['admin'];

    $stmtLog = $conn->prepare("
        INSERT INTO Admin_log (admin, action)
        VALUES (?, ?)
    ");

    $stmtLog->bind_param("ss", $admin, $action);
    $stmtLog->execute();
}


// =====================================================
// AJOUT D'UN UTILISATEUR
// =====================================================

if (isset($_POST['add_user'])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);

    if ($nom !== "" && $prenom !== "") {

        $stmtCheck = $conn->prepare("
            SELECT idUser
            FROM User
            WHERE Nom = ?
              AND Prenom = ?
        ");

        $stmtCheck->bind_param("ss", $nom, $prenom);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result();

        if ($checkResult->num_rows == 0) {

            $stmtInsert = $conn->prepare("
                INSERT INTO User (Nom, Prenom, Email, Motf, SuperUser)
                VALUES (?, ?, ?, '', 0)
            ");

            $stmtInsert->bind_param("sss", $nom, $prenom, $email);
            $stmtInsert->execute();

            logAdmin($conn, "Ajout utilisateur : " . $nom . " " . $prenom);
        }
    }

    header("Location: badges.php");
    exit();
}


// =====================================================
// CRÉATION D'UN COMPTE APPLICATIF
// =====================================================

if (isset($_POST['add_account'])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $idUser = intval($_POST['idUser']);
    $identifiant = trim($_POST['identifiant']);
    $password = trim($_POST['password']);
    $role = intval($_POST['role']);

    if ($idUser > 0 && $identifiant !== "" && $password !== "") {

        $stmtCheck = $conn->prepare("
            SELECT idUser
            FROM User
            WHERE Identifiant = ?
               OR (idUser = ? AND Identifiant IS NOT NULL)
        ");

        $stmtCheck->bind_param("si", $identifiant, $idUser);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result();

        if ($checkResult->num_rows == 0) {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmtUpdate = $conn->prepare("
                UPDATE User
                SET Identifiant = ?,
                    Motf = ?,
                    SuperUser = ?
                WHERE idUser = ?
            ");

            $stmtUpdate->bind_param("ssii", $identifiant, $hash, $role, $idUser);
            $stmtUpdate->execute();

            logAdmin(
                $conn,
                "Création compte applicatif : " . $identifiant . " (ID utilisateur " . $idUser . ")"
            );
        }
    }

    header("Location: badges.php");
    exit();
}


// =====================================================
// MODIFICATION DU RÔLE D'UN COMPTE
// =====================================================

if (isset($_POST['update_role'])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $idUser = intval($_POST['idUser']);
    $role = intval($_POST['role']);

    $stmtUpdate = $conn->prepare("
        UPDATE User
        SET SuperUser = ?
        WHERE idUser = ?
    ");

    $stmtUpdate->bind_param("ii", $role, $idUser);
    $stmtUpdate->execute();

    $stmtUser = $conn->prepare("
        SELECT Nom, Prenom
        FROM User
        WHERE idUser = ?
    ");

    $stmtUser->bind_param("i", $idUser);
    $stmtUser->execute();
    $userResult = $stmtUser->get_result();
    $user = $userResult->fetch_assoc();

    logAdmin(
        $conn,
        "Modification rôle : " . $user['Nom'] . " " . $user['Prenom'] . " (ID " . $idUser . ")"
    );

    $_SESSION['message'] = "<p class='success'>Rôle modifié avec succès</p>";

    header("Location: badges.php");
    exit();
}


// =====================================================
// AJOUT D'UN BADGE RFID
// =====================================================

if (isset($_POST['add_badge'])) {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Erreur CSRF : requête non autorisée.");
    }

    $rfid = trim(htmlspecialchars($_POST['rfid']));
    $idUser = intval($_POST['idUser']);

    if ($rfid !== "" && $idUser > 0) {

        $stmtCheck = $conn->prepare("
            SELECT idCarte
            FROM Carte
            WHERE RFID = ?
        ");

        $stmtCheck->bind_param("s", $rfid);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result();

        if ($checkResult->num_rows == 0) {

            $stmtInsert = $conn->prepare("
                INSERT INTO Carte (RFID, idUser, active)
                VALUES (?, ?, 1)
            ");

            $stmtInsert->bind_param("si", $rfid, $idUser);
            $stmtInsert->execute();

            logAdmin($conn, "Ajout badge RFID : " . $rfid);
        }
    }

    header("Location: badges.php");
    exit();
}


// =====================================================
// ACTIVATION / DÉSACTIVATION D'UN BADGE
// =====================================================

if (isset($_GET['toggle'])) {

    $idCarte = intval($_GET['toggle']);

    $stmtBadge = $conn->prepare("
        SELECT RFID, active
        FROM Carte
        WHERE idCarte = ?
    ");

    $stmtBadge->bind_param("i", $idCarte);
    $stmtBadge->execute();
    $badgeResult = $stmtBadge->get_result();
    $badge = $badgeResult->fetch_assoc();

    if ($badge) {

        $stmtUpdate = $conn->prepare("
            UPDATE Carte
            SET active = 1 - active
            WHERE idCarte = ?
        ");

        $stmtUpdate->bind_param("i", $idCarte);
        $stmtUpdate->execute();

        $newStatus = ($badge['active'] == 1) ? "Désactivation" : "Activation";

        logAdmin(
            $conn,
            $newStatus . " badge : " . $badge['RFID'] . " (ID " . $idCarte . ")"
        );
    }

    header("Location: badges.php");
    exit();
}


// =====================================================
// SUPPRESSION D'UN BADGE
// =====================================================

if (isset($_GET['delete'])) {

    $idCarte = intval($_GET['delete']);

    if ($idCarte <= 0) {
        exit("ID invalide");
    }

    $stmtBadge = $conn->prepare("
        SELECT RFID
        FROM Carte
        WHERE idCarte = ?
    ");

    $stmtBadge->bind_param("i", $idCarte);
    $stmtBadge->execute();
    $badgeResult = $stmtBadge->get_result();
    $badge = $badgeResult->fetch_assoc();

    $stmtDelete = $conn->prepare("
        DELETE FROM Carte
        WHERE idCarte = ?
    ");

    $stmtDelete->bind_param("i", $idCarte);
    $stmtDelete->execute();

    if ($badge) {
        logAdmin(
            $conn,
            "Suppression badge ID : " . $badge['RFID'] . " (ID " . $idCarte . ")"
        );
    }

    header("Location: badges.php");
    exit();
}


// =====================================================
// SUPPRESSION D'UN UTILISATEUR
// =====================================================

if (isset($_GET['deleteuser'])) {

    $idUser = intval($_GET['deleteuser']);

    $adminCount = $conn->query("
        SELECT COUNT(*) AS total
        FROM User
        WHERE SuperUser = 1
    ")->fetch_assoc();

    $stmtRole = $conn->prepare("
        SELECT SuperUser
        FROM User
        WHERE idUser = ?
    ");

    $stmtRole->bind_param("i", $idUser);
    $stmtRole->execute();
    $roleResult = $stmtRole->get_result();
    $userRole = $roleResult->fetch_assoc();

    if ($userRole['SuperUser'] == 1 && $adminCount['total'] <= 1) {

        $_SESSION['message'] = "<p class='error'>Impossible de supprimer le dernier administrateur</p>";

        header("Location: badges.php");
        exit();
    }

    $stmtUser = $conn->prepare("
        SELECT Nom, Prenom
        FROM User
        WHERE idUser = ?
    ");

    $stmtUser->bind_param("i", $idUser);
    $stmtUser->execute();
    $userResult = $stmtUser->get_result();
    $user = $userResult->fetch_assoc();

    $stmtDeleteBadges = $conn->prepare("
        DELETE FROM Carte
        WHERE idUser = ?
    ");

    $stmtDeleteBadges->bind_param("i", $idUser);
    $stmtDeleteBadges->execute();

    $stmtDeleteUser = $conn->prepare("
        DELETE FROM User
        WHERE idUser = ?
    ");

    $stmtDeleteUser->bind_param("i", $idUser);
    $stmtDeleteUser->execute();

    if ($user) {
        logAdmin(
            $conn,
            "Suppression utilisateur : " . $user['Nom'] . " " . $user['Prenom'] . " (ID " . $idUser . ")"
        );
    }

    header("Location: badges.php");
    exit();
}


// =====================================================
// RÉCUPÉRATION DES DONNÉES
// =====================================================

$users = $conn->query("
    SELECT idUser, Nom, Prenom
    FROM User
    ORDER BY Nom
");

$usersTable = $conn->query("
    SELECT idUser, Nom, Prenom, Identifiant, SuperUser
    FROM User
    ORDER BY Nom
");

$badges = $conn->query("
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
    <meta charset="UTF-8">
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

    <!-- Formulaire d'ajout d'un badge RFID -->
    <h3>Ajouter un badge</h3>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

        <input type="text" name="rfid" placeholder="UID RFID" required>

        <select name="idUser" required>
            <option value="">Utilisateur associé</option>

            <?php while ($user = $users->fetch_assoc()) { ?>
                <option value="<?= $user['idUser']; ?>">
                    <?= htmlspecialchars($user['Nom'] . " " . $user['Prenom']); ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit" name="add_badge">Ajouter</button>
    </form>

    <!-- Formulaire d'ajout d'un utilisateur -->
    <h3>Ajouter un utilisateur</h3>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email" required>

        <button type="submit" name="add_user">Ajouter</button>
    </form>

    <?php if ($_SESSION['role'] == 1) { ?>

        <!-- Formulaire de création d'un compte applicatif -->
        <h3>Créer un compte applicatif</h3>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">

            <select name="idUser" required>
                <option value="">Utilisateur associé</option>

                <?php
                $usersAccount = $conn->query("
                    SELECT idUser, Nom, Prenom
                    FROM User
                    ORDER BY Nom
                ");

                while ($user = $usersAccount->fetch_assoc()) {
                ?>
                    <option value="<?= $user['idUser']; ?>">
                        <?= htmlspecialchars($user['Nom'] . " " . $user['Prenom']); ?>
                    </option>
                <?php } ?>
            </select>

            <input type="text" name="identifiant" placeholder="Identifiant" required>
            <input type="password" name="password" placeholder="Mot de passe temporaire" required>

            <select name="role">
                <option value="0">Utilisateur</option>
                <option value="1">Administrateur</option>
            </select>

            <button type="submit" name="add_account">Créer compte</button>
        </form>

    <?php } ?>

    <!-- Tableau des badges RFID -->
    <h3>Liste des badges</h3>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>RFID</th>
            <th>Utilisateur</th>
            <th>État</th>
            <th>Action</th>
            <th>Supprimer</th>
        </tr>

        <?php while ($badge = $badges->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($badge['idCarte']); ?></td>
                <td><?= htmlspecialchars($badge['RFID']); ?></td>

                <td>
                    <?php
                    if (!empty($badge['Nom'])) {
                        echo htmlspecialchars($badge['Nom'] . " " . $badge['Prenom']);
                    } else {
                        echo "Non assigné";
                    }
                    ?>
                </td>

                <td>
                    <?= ($badge['active'] == 1) ? "Actif" : "Inactif"; ?>
                </td>

                <td>
                    <?php
                    $toggleLink = "badges.php?toggle=" . intval($badge['idCarte']);

                    if ($badge['active'] == 1) {
                        echo "<a href='$toggleLink'>Désactiver</a>";
                    } else {
                        echo "<a href='$toggleLink'>Activer</a>";
                    }
                    ?>
                </td>

                <td>
                    <a href="badges.php?delete=<?= intval($badge['idCarte']); ?>"
                       onclick="return confirm('Supprimer ce badge ?');">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Tableau des utilisateurs -->
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

        <?php while ($user = $usersTable->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($user['idUser']); ?></td>
                <td><?= htmlspecialchars($user['Nom']); ?></td>
                <td><?= htmlspecialchars($user['Prenom']); ?></td>

                <td>
                    <?= !empty($user['Identifiant']) ? "Oui" : "Non"; ?>
                </td>

                <td>
                    <?= ($user['SuperUser'] == 1) ? "Administrateur" : "Utilisateur"; ?>
                </td>

                <td>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                        <input type="hidden" name="idUser" value="<?= $user['idUser']; ?>">

                        <select name="role">
                            <option value="0" <?= ($user['SuperUser'] == 0) ? "selected" : ""; ?>>
                                Utilisateur
                            </option>
                            <option value="1" <?= ($user['SuperUser'] == 1) ? "selected" : ""; ?>>
                                Administrateur
                            </option>
                        </select>

                        <button type="submit" name="update_role">Modifier</button>
                    </form>
                </td>

                <td>
                    <a href="badges.php?deleteuser=<?= $user['idUser']; ?>"
                       onclick="return confirm('Supprimer cet utilisateur et ses badges associés ?');">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Bouton de retour au tableau de bord -->
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