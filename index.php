<?php
session_start();
include("config.php");

// Authentification administrateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM User WHERE Identifiant=? AND SuperUser=1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

//Vérification du mot de passe
        if (password_verify($pass, $row['Mdp'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $user;
            $_SESSION['last_activity'] = time();
            header("Location: dashboard.php");
            exit();
        } else {
            $error =  "Identifiants incorrects";
        }
} else {
    echo "Identifiants incorrects";
}
}
?>

<img src="img/logo_ca25.png" class="background-logo">
<link rel="stylesheet" href="style.css">
<div class="login-container">
<?php if (!empty($error)) echo "<pclass='error'>$erroe</p>" ?>
    <form method="post">
        <h2>Connexion Admin</h2>
        <input type="text" name="username" placeholder="Identifiant" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
</div>
