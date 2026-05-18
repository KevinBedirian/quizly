<?php
session_start();
require 'bdd.php';

$is_logged_in = isset($_SESSION['id']);
$is_admin = false;

if ($is_logged_in) {
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    $is_admin = $user && $user['role'] == 1;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizly - Quiz Informatique</title>
    <link rel="stylesheet" href="quizly.css">
</head>
<body>
    <!-- En-tête -->
    <div class="topbar">
        🖥️ QUIZLY - Quiz Informatique
    </div>

    <!-- Menu de navigation -->
    <div class="menu">
        <a href="accueil.php">Accueil</a>
        <?php if ($is_logged_in): ?>
            <a href="quizly.php">🚀 Commencer</a>
            <a href="historique.php">📊 Mon historique</a>
            <?php if ($is_admin): ?>
                <a href="admin.php" style="background: #667eea; color: white; border-radius: 4px; padding: 8px 16px;">⚙️ Administration</a>
            <?php endif; ?>
            <a href="deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php">Connexion</a>
            <a href="inscription.php">Inscription</a>
        <?php endif; ?>
    </div>

    <!-- Section Héro -->
    <div class="hero">
        <div class="hero-content">
            <h1>Testez vos connaissances en informatique</h1>
            <p>Bienvenue sur Quizly ! Répondez à des questions variées et testez vos compétences informatiques</p>
            <?php if ($is_logged_in): ?>
                <button class="btn-primary"><a href="quizly.php" style="color: #667eea; text-decoration: none;">🚀 Commencer une partie</a></button>
            <?php else: ?>
                <button class="btn-primary"><a href="connexion.php" style="color: #667eea; text-decoration: none;">🔓 Se connecter</a></button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="main">
        <div class="container-large">
            
            <!-- Section À propos -->
            <div style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 40px;">
                <h2 style="margin-bottom: 20px; font-size: 24px; color: #2c3e50;">Comment ça marche ?</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
                    <div>
                        <div style="font-size: 36px; margin-bottom: 10px;">1️⃣</div>
                        <h4 style="margin-bottom: 10px;">Connectez-vous</h4>
                        <p style="color: #666; font-size: 14px;">Accédez à votre compte pour commencer à jouer</p>
                    </div>
                    <div>
                        <div style="font-size: 36px; margin-bottom: 10px;">2️⃣</div>
                        <h4 style="margin-bottom: 10px;">Lancez une partie</h4>
                        <p style="color: #666; font-size: 14px;">Démarrez un quiz et répondez aux questions</p>
                    </div>
                    <div>
                        <div style="font-size: 36px; margin-bottom: 10px;">3️⃣</div>
                        <h4 style="margin-bottom: 10px;">Gagnez des points</h4>
                        <p style="color: #666; font-size: 14px;">Accumlez des points et améliorez votre score</p>
                    </div>
                </div>
            </div>

            <!-- Section Caractéristiques -->
            <div>
                <h2 style="text-align: center; margin-bottom: 30px; font-size: 24px; color: #2c3e50;">Pourquoi Quizly ?</h2>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <span class="feature-icon">📚</span>
                        <h4>Centaines de questions</h4>
                        <p>Une immense base de données de questions variées en informatique</p>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">⚡</span>
                        <h4>Quizz dynamiques</h4>
                        <p>Des questions sélectionnées aléatoirement à chaque partie</p>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🏆</span>
                        <h4>Suivi de progression</h4>
                        <p>Suivez votre score et votre classement en temps réel</p>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🎯</span>
                        <h4>Améliorez vos skills</h4>
                        <p>Apprenez en jouant et maîtrisez l'informatique</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Quizly - Quiz Informatique. Tous droits réservés.</p>
        <p><small>Créé pour tester et améliorer vos compétences informatiques</small></p>
    </footer>

</body>
</html>