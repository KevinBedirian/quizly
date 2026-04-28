<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quizly - Au revoir</title>
    <link rel="stylesheet" href="quizly.css">
</head>
<body>
    <div class="main">
        <div class="container" style="text-align:center;">
            <h1>À bientôt.</h1>
            <hr>
            <p style="color:var(--text-muted);">Déconnexion sécurisée en cours...</p>
            <?php header("refresh:2;url=affichage.php"); ?>
        </div>
    </div>
</body>
</html>
