<?php
// Configuration de la session sécurisée
date_default_timezone_set('Europe/Paris');

session_name("CA25SESSID");

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'samesite' => 'Strict'
]);

session_start();

// Inclusion de la configuration de la base de données
include("config.php");

// Vérification de la connexion administrateur
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Vérification des droits administrateur
if ($_SESSION['role'] != 1) {
    header("Location: dashboard.php");
    exit();
}

// Récupération de la recherche utilisateur
$search = $_GET['search'] ?? '';

// Gestion de la pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Recherche filtrée dans le journal administrateur
if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT *
        FROM Admin_log
        WHERE admin LIKE ?
           OR action LIKE ?
           OR action LIKE CONCAT('%', ?, '%')
        ORDER BY date_action DESC
        LIMIT ? OFFSET ?
    ");

    $like = "%" . $search . "%";
    $stmt->bind_param("sssii", $like, $like, $search, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "
        SELECT *
        FROM Admin_log
        ORDER BY date_action DESC
        LIMIT $limit OFFSET $offset
    ";

    $result = $conn->query($sql);
}

// Vérification de l'existence d'une page suivante
$hasNextPage = $result->num_rows == $limit;
$prevPage = $page - 1;
$nextPage = $page + 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal administrateur</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<img src="img/logo_ca25.png" class="background-logo">

<div class="container">

    <h1>Journal administrateur</h1>

    <!-- Formulaire de recherche dans le journal administrateur -->
    <form method="get">
        <input
            type="text"
            name="search"
            placeholder="Rechercher une action ou un admin"
            value="<?= htmlspecialchars($search) ?>"
        >
        <button type="submit">Rechercher</button>
    </form>

    <br>

    <!-- Tableau d'affichage du journal administrateur -->
    <table class="table logs-table">
        <tr>
            <th>Date</th>
            <th>Administrateur</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <?php
            // Définition de la couleur des actions selon leur type
            $classe = "";

            if (stripos($row['action'], 'Ajout') !== false) {
                $classe = "log-ajout";
            } elseif (stripos($row['action'], 'Modification') !== false) {
                $classe = "log-modification";
            } elseif (stripos($row['action'], 'Suppression') !== false) {
                $classe = "log-suppression";
            } elseif (
                stripos($row['action'], 'Activation') !== false ||
                stripos($row['action'], 'Désactivation') !== false
            ) {
                $classe = "log-etat";
            }
            ?>

            <tr>
                <td><?= date("d/m/Y H:i", strtotime($row['date_action'])) ?></td>
                <td><?= htmlspecialchars($row['admin']) ?></td>
                <td class="<?= $classe ?>">
                    <?= htmlspecialchars($row['action']) ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <br>

    <!-- Navigation entre les pages du journal -->
    <div style="text-align:center; margin-top:20px;">
        <?php if ($page > 1) { ?>
            <a style="margin-right:20px;" href="admin_logs.php?page=<?= $prevPage ?>">
                &lt;- Précédent
            </a>
        <?php } ?>

        <?php if ($hasNextPage) { ?>
            <a style="margin-left:20px;" href="admin_logs.php?page=<?= $nextPage ?>">
                Suivant -&gt;
            </a>
        <?php } ?>
    </div>

    <!-- Bouton de retour au tableau de bord -->
    <div style="text-align:center; margin-top:40px;">
        <a href="dashboard.php" class="btn retour">Retour</a>
    </div>

</div>

<footer>
    CA25 - Application de gestion des badges RFID<br>
    BTS CIEL - Virginie R.
</footer>

</body>
</html>