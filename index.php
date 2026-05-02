<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM User WHERE Identifiant='$user' AND Mdp='$pass' AND SuperUser=1";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Identifiants incorrects";
    }
}
?>

<form method="post">
    <h2>Connexion Admin</h2>
    <input type="text" name="username" placeholder="Identifiant"><br>
    <input type="password" name="password" placeholder="Mot de passe"><br>
    <button type="submit">Se connecter</button>
</form>
