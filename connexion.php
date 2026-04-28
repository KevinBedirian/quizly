<?php
session_start();
require_once "bdd.php";

$erreur = "";

if(isset($_POST['connexion'])){ 
    $mail = trim($_POST['mail']);
    $mdp_saisi = $_POST['mdp'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $mail);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);

    if($ligne = mysqli_fetch_assoc($resultat)){
        if(password_verify($mdp_saisi, $ligne['mdp'])) {
            $_SESSION['id']     = $ligne['id'];
            $_SESSION['role']   = $ligne['role'];

            $_SESSION['nom']    = $ligne['nom'];
            $_SESSION['prenom'] = $ligne['prenom'];
            $_SESSION['mail']   = $ligne['email'];
            header("location: quizly.php");
            exit();
        } else {
            $erreur = "Identifiants incorrects.";
        }
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchly - Connexion</title>
    <link rel="stylesheet" href="quizly.css">
</head>
<body>
    <div class="main">
        <div class="container">
            <h1>Connectez vous pour commencer à jouer !</h1>
            <hr>
            <?php if(!empty($erreur)): ?>
                <div class="alert"><?= $erreur ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="form-group">
                    <label>Adresse Email</label>
                    <input type="email" name="mail" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="mdp" required>
                </div>
                <input type="submit" value="Accéder aux quiz" name="connexion">
            </form>
            <a href="inscription.php" class="link">Créer un compte</a>
        </div>
    </div>
</body>
</html>
