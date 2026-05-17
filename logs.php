<?php

// ======================================================
// CONFIGURATION GÉNÉRALE
// ======================================================

date_default_timezone_set('Europe/Paris');

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


// ======================================================
// VÉRIFICATION SESSION ADMINISTRATEUR
// ======================================================

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}


// ======================================================
// EXPIRATION AUTOMATIQUE DE SESSION
// ======================================================

if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity'] > 600)
) {

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit();
}

$_SESSION['last_activity'] = time();


// ======================================================
// PRÉPARATION DES FILTRES
// ======================================================

$where = [];
$params = [];
$types = "";


// ------------------------------------------------------
// FILTRE PAR TYPE D’ACCÈS
// ------------------------------------------------------

if (isset($_GET['filtre']) && $_GET['filtre'] != "") {

    if ($_GET['filtre'] == "ok") {

        $where[] = "Acces_log.Resultat_tentative = ?";
        $params[] = "ACCES_OK";
        $types .= "s";

    } elseif ($_GET['filtre'] == "refus") {

        $where[] = "Acces_log.Resultat_tentative != ?";
        $params[] = "ACCES_OK";
        $types .= "s";
    }
}


// ------------------------------------------------------
// FILTRE PAR UTILISATEUR
// ------------------------------------------------------

if (isset($_GET['user']) && $_GET['user'] != "") {

    $where[] = "Acces_log.idUser = ?";
    $params[] = intval($_GET['user']);
    $types .= "i";
}


// ------------------------------------------------------
// RECHERCHE UID / NOM / PRÉNOM
// ------------------------------------------------------

if (isset($_GET['q']) && trim($_GET['q']) != "") {

    $search = "%" . trim($_GET['q']) . "%";

    $where[] = "(
        Acces_log.UID LIKE ?
        OR User.Nom LIKE ?
        OR User.Prenom LIKE ?
    )";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "sss";
}


// ======================================================
// CONSTRUCTION DE LA REQUÊTE SQL
// ======================================================

$whereSQL = "";

if (count($where) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}


// ======================================================
// PAGINATION
// ======================================================

$limit = 15;

$page = isset($_GET['page'])
    ? max(1, intval($_GET['page']))
    : 1;

$offset = ($page - 1) * $limit;


// ======================================================
// RÉCUPÉRATION DES LOGS D’ACCÈS
// ======================================================

$sql = "
    SELECT
        Acces_log.*,
        User.Nom,
        User.Prenom

    FROM Acces_log

    LEFT JOIN User
        ON Acces_log.idUser = User.idUser

    $whereSQL

    ORDER BY Acces_log.Date_heure_entree DESC

    LIMIT ? OFFSET ?
";

$params[] = $limit;
$params[] = $offset;

$types .= "ii";

$stmt = $conn->prepare($sql);


// ------------------------------------------------------
// ASSOCIATION DES PARAMÈTRES
// ------------------------------------------------------

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();


// ======================================================
// PAGINATION SUIVANTE / PRÉCÉDENTE
// ======================================================

$prevPage = $page - 1;
$nextPage = $page + 1;

$hasNextPage = $result->num_rows == $limit;


// ======================================================
// LISTE DES UTILISATEURS
// ======================================================

$users = $conn->query("
    SELECT idUser, Nom, Prenom
    FROM User
    ORDER BY Nom
");

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Logs CA25</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<img src="img/logo_ca25.png" class="background-logo">

<div class="container">

    <h2>Logs d'accès</h2>


    <!-- ==================================================
         FILTRES
    =================================================== -->

    <form method="get">

        <select name="filtre">

            <option value="">Tous</option>

            <option value="ok">
                Accès autorisé
            </option>

            <option value="refus">
                Accès refusé
            </option>

        </select>


        <select name="user">

            <option value="">
                Tous les utilisateurs
            </option>

            <?php while ($user = $users->fetch_assoc()) { ?>

                <option value="<?php echo htmlspecialchars($user['idUser']); ?>">

                    <?php
                    echo htmlspecialchars(
                        $user['Nom']
                        . " "
                        . $user['Prenom']
                    );
                    ?>

                </option>

            <?php } ?>

        </select>


        <input
            type="text"
            name="q"
            placeholder="Recherche UID ou nom"
            value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
        >

        <button type="submit">
            Filtrer
        </button>

    </form>


    <!-- ==================================================
         ACTIONS
    =================================================== -->

    <div style="margin-top:15px; margin-bottom:25px; text-align:center;">

        <button
            type="button"
            onclick="window.print()"
        >
            Imprimer / Export PDF
        </button>

        <a href="export_logs_csv.php" class="btn">
            Export CSV
        </a>

    </div>


    <!-- ==================================================
         TABLEAU DES LOGS
    =================================================== -->

    <table class="table logs-table">

        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Résultat</th>
            <th>Utilisateur</th>
            <th>UID</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>

            <tr>

                <td>
                    <?php echo htmlspecialchars($row['idAcces']); ?>
                </td>

                <td>
                    <?php
                    echo date(
                        "d/m/Y H:i",
                        strtotime($row['Date_heure_entree'])
                    );
                    ?>
                </td>


                <!-- =========================================
                     AFFICHAGE DU TYPE D’ACCÈS
                ========================================== -->

                <?php if ($row['Resultat_tentative'] == "ACCES_OK") { ?>

                    <td class="success">
                        Accès autorisé
                    </td>

                <?php } elseif ($row['Resultat_tentative'] == "BADGE_INACTIF") { ?>

                    <td class="warning">
                        Badge inactif
                    </td>

                <?php } elseif ($row['Resultat_tentative'] == "BADGE_INCONNU") { ?>

                    <td class="error">
                        Badge inconnu
                    </td>

                <?php } else { ?>

                    <td class="error">
                        Accès refusé
                    </td>

                <?php } ?>


                <!-- =========================================
                     AFFICHAGE UTILISATEUR
                ========================================== -->

                <?php if ($row['Nom'] && $row['Prenom']) { ?>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $row['Nom']
                            . " "
                            . $row['Prenom']
                        );
                        ?>
                    </td>

                <?php } else { ?>

                    <td>
                        Badge inconnu
                    </td>

                <?php } ?>


                <td>
                    <?php echo htmlspecialchars($row['UID']); ?>
                </td>

            </tr>

        <?php } ?>

    </table>


    <!-- ==================================================
         PAGINATION
    =================================================== -->

    <br>

    <div style="margin-top:20px; text-align:center;">

        <?php if ($page > 1) { ?>

            <a
                style="margin-right:20px;"
                href="?page=<?php echo $prevPage; ?>"
            >
                ← Précédent
            </a>

        <?php } ?>


        <?php if ($hasNextPage) { ?>

            <a
                style="margin-left:20px;"
                href="?page=<?php echo $nextPage; ?>"
            >
                Suivant →
            </a>

        <?php } ?>

    </div>


    <!-- ==================================================
         BOUTON RETOUR
    =================================================== -->

    <div class="logs-action">

        <a href="dashboard.php" class="btn retour">
            Retour
        </a>

    </div>

</div>


<!-- ======================================================
     FOOTER
======================================================= -->

<footer>

    CA25 - Application de gestion des badges RFID<br>
    BTS CIEL - Virginie R.

</footer>
</body>
</html>