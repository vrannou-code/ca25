<?php

// ======================================================
// PAGE DE CONNEXION ADMINISTRATEUR
// Projet CA25 - Gestion des badges RFID
// BTS CIEL - Virginie R.
// ======================================================


// ======================================================
// CONFIGURATION DE LA SESSION
// ======================================================

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
// INITIALISATION DES VARIABLES
// ======================================================

$error = "";

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}


// ======================================================
// TRAITEMENT DU FORMULAIRE DE CONNEXION
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_SESSION['lock_time']) && time() < $_SESSION['lock_time']) {

        $remaining = $_SESSION['lock_time'] - time();

        $error = "Trop de tentatives. Réessayez dans "
            . ceil($remaining / 60)
            . " minute(s).";

    } else {

        $user = $_POST['username'] ?? "";
        $pass = $_POST['password'] ?? "";

        $stmt = $conn->prepare("
            SELECT *
            FROM User
            WHERE Identifiant = ?
        ");

        $stmt->bind_param("s", $user);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $row = $result->fetch_assoc();

            if (password_verify($pass, $row['Motf'])) {

                session_regenerate_id(true);

                $_SESSION['admin'] = $user;
                $_SESSION['role'] = $row['SuperUser'];
                $_SESSION['last_activity'] = time();
                $_SESSION['login_attempts'] = 0;

                unset($_SESSION['lock_time']);

                header("Location: dashboard.php");
                exit();

            } else {

                $_SESSION['login_attempts']++;

                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lock_time'] = time() + 300;
                    $error = "Trop de tentatives. Réessayez dans 5 minutes.";
                } else {
                    $error = "Identifiants incorrects.";
                }
            }

        } else {

            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lock_time'] = time() + 300;
                $error = "Trop de tentatives. Réessayez dans 5 minutes.";
            } else {
                $error = "Identifiants incorrects.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion CA25</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<img src="img/logo_ca25.png" class="background-logo">

<div class="login-container">

    <h2>Connexion administrateur</h2>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error); ?></p>
    <?php } ?>

    <!-- Formulaire de connexion -->
    <form method="post">

        <input
            type="text"
            name="username"
            placeholder="Identifiant"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Mot de passe"
            required
        >

        <button type="submit">Se connecter</button>

    </form>

</div>

</body>
</html>