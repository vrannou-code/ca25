<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM User WHERE Identifiant=? AND SuperUser=1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($pass, $row['Mdp'])) {
            $_SESSION['admin'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Identifiants incorrects";
        }
} else {
    echo "Identifiant incorrects";
}
}
?>

<form method="post">
    <h2>Connexion Admin</h2>
    <input type="text" name="username" placeholder="Identifiant"><br>
    <input type="password" name="password" placeholder="Mot de passe"><br>
    <button type="submit">Se connecter</button>
</form>
