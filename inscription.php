<?php
session_start();
require_once "bdd.php";

$message = "";
$nom = $prenom = $mail = "";

if(isset($_POST['inscription'])){
    $nom       = trim($_POST['nom']);
    $prenom    = trim($_POST['prenom']);
    $mail      = trim($_POST['mail']);
    $mdp_clair = $_POST['mdp'];

    if (strlen($mdp_clair) < 10) {
        $message = "Le mot de passe doit faire au moins 10 caractères.";
    } elseif (!preg_match('/[A-Z]/', $mdp_clair)) {
        $message = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match('/[a-z]/', $mdp_clair)) {
        $message = "Le mot de passe doit contenir au moins une minuscule.";
    } elseif (!preg_match('/[0-9]/', $mdp_clair)) {
        $message = "Le mot de passe doit contenir au moins un chiffre.";
    } elseif (!preg_match('/[^a-zA-Z0-9]/', $mdp_clair)) {
        $message = "Le mot de passe doit contenir au moins un caractère spécial.";
    } else {
        $verif_sql = "SELECT id FROM users WHERE email = ?";
        $verif_stmt = mysqli_prepare($conn, $verif_sql);
        mysqli_stmt_bind_param($verif_stmt, "s", $mail);
        mysqli_stmt_execute($verif_stmt);
        mysqli_stmt_store_result($verif_stmt);

        if (mysqli_stmt_num_rows($verif_stmt) > 0) {
            $message = "Erreur : Cette adresse email est déjà utilisée.";
            mysqli_stmt_close($verif_stmt);
        } else {
            mysqli_stmt_close($verif_stmt);
            $mdp_hash  = password_hash($mdp_clair, PASSWORD_DEFAULT);
            $sql  = "INSERT INTO users (nom,prenom,email,mdp) VALUES (?,?,?,?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $nom,$prenom,$mail,$mdp_hash);
            if(mysqli_stmt_execute($stmt)){
                $message = "Inscription réussie ! Redirection en cours...";
                header("Refresh:2; url=connexion.php");
            } else {
                $message = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizly - Inscription</title>
    <link rel="stylesheet" href="quizly.css">
</head>
<body>
    <div class="topbar">QUIZLY</div>
    <div class="main">
        <div class="container">
            <h1>QUIZLY</h1>
            <hr>
            <?php if($message): ?>
                <div class="alert <?= strpos($message, 'réussie') !== false ? 'success' : '' ?>"><?= $message ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div style="display:flex; gap:15px;">
                    <div class="form-group" style="flex:1;"><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" required></div>
                    <div class="form-group" style="flex:1;"><label>Prénom</label><input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" name="mail" value="<?= htmlspecialchars($mail) ?>" required></div>
                <div class="form-group"><label>Mot de passe <small>(Min 10 char, Maj, min, Chiffre, Spé)</small></label><input type="password" name="mdp" required></div>
                <input type="submit" name="inscription" value="S'inscrire">
            </form>
            <a href="connexion.php" class="link">Déjà membre ? Se connecter</a>
        </div>
    </div>
</body>
</html>
