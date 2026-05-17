<?php
date_default_timezone_set('Europe/Paris');

session_name("CA25SESSID");

session_set_cookie_params([
    'lifetime' =>0,
    'path' => '/',
    'secure' => true,
    'samesite' => 'Strict'
]);

session_start();

include("config.php");

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] != 1) {
    header("Location: dashboard.php");
    exit();
}

$search = $_GET['search'] ?? '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT *
        FROM Admin_log
        WHERE admin LIKE ? OR action LIKE ? OR action LIKE CONCAT('%', ?, '%')
        ORDER BY date_action DESC
        LIMIT ? OFFSET ?
    ");

    $like = "%".$search."%";
    $stmt->bind_param("sssii", $like, $like, $search, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {

    $sql = "SELECT * FROM Admin_log ORDER BY date_action DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
}
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

<div class="container" style="position: relative; z-index: 1;">

    <h1>Journal administrateur</h1>

<form method="get">
    <input type="text" name="search" placeholder="Rechercher une action ou un admin">
    <button type="submit">Rechercher</button>
</form>

<br>

    <table class="table">
        <tr>
            <th>Date</th>
            <th>Administrateur</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo date("d/m/Y H:i", strtotime($row['date_action'])); ?></td>
                <td><?php echo htmlspecialchars($row['admin']); ?></td>

                 <?php
                 $classe = "";
                 if (stripos($row['action'], 'Ajout') !== false) {
                     $classe = "log-ajout";
                 }
                 elseif (stripos($row['action'], 'Modification') !== false) {
                     $classe = "log-modification";
                 }
                 elseif (stripos($row['action'], 'Suppression') !== false) {
                     $classe = "log-suppression";
                 }
                 elseif (stripos($row['action'], 'Activation') !== false || stripos($row['action'], 'Désactivation') !== false) {
                     $classe = "log-etat";
                 }
                 ?>
                 <td class="<?= $classe ?>">
                     <?php echo htmlspecialchars($row['action']); ?>
                 </td>
            </tr>
        <?php } ?>
    </table>

    <br>
     <?php
     $hasNextPage = $result->num_rows == $limit;
     ?>
     <div style="text-align:center; margin-top:20px;">
     <?php if ($page > 1) { ?>
         <a style="margin-right:20px" href="admin_logs.php?page=<?php echo $page - 1 ?>"><-Précédent</a>
     <?php } ?>
     <?php if ($hasNextPage) { ?>
     <a style="margin-left:20px;" href="admin_logs.php?page=<?php echo $page + 1 ?>">Suivant-></a>
     <?php } ?>
     </div>

     <div style"text-align:center; margin-top:40px;">
         <a href="dashboard.php" class="btn retour">Retour</a>
     </div>
</div>

<footer>
Projet CA25 - Application de gestion des badges RFID<br>
BTS CIEL - Virginie R.
</footer>

</body>
</html>
