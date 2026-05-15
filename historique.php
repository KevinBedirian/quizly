<?php
session_start();
require 'bdd.php';

if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['id'];

// Récupérer toutes les tentatives de l'utilisateur
$query = "SELECT * FROM tentatives WHERE utilisateur_id = ? ORDER BY date_passage DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tentatives = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Récupérer les infos de l'utilisateur
$query_user = "SELECT nom, prenom FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $query_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique - Quizly</title>
    <link rel="stylesheet" href="quizly.css">
    <style>
        .historique-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .historique-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .historique-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .historique-header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .tentatives-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .tentative-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: center;
            transition: all 0.3s ease;
        }

        .tentative-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .tentative-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .tentative-label {
            color: #7f8c8d;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .tentative-value {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
        }

        .score-badge {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            min-width: 80px;
        }

        .score-badge.excellent {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .score-badge.bon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .score-badge.moyen {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .btn-details {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            min-width: 120px;
        }

        .btn-details:hover {
            background: #5568d3;
            transform: scale(1.05);
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .empty-message h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-message p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-retour {
            background: #2c3e50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-retour:hover {
            background: #1a252f;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .tentative-card {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .header-buttons {
                flex-direction: column;
            }

            .btn-details, .btn-primary, .btn-retour {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="topbar">
        🖥️ QUIZLY - Quiz Informatique
    </div>

    <!-- Menu de navigation -->
    <div class="menu">
        <a href="accueil.php">Accueil</a>
        <a href="quizly.php">Commencer un quiz</a>
        <a href="historique.php">📊 Mes historiques</a>
        <a href="deconnexion.php">Déconnexion</a>
    </div>

    <!-- Contenu principal -->
    <div class="historique-container">
        <div class="historique-header">
            <h1>📊 Historique de vos tentatives</h1>
            <p>Bienvenue <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>
        </div>

        <?php if (empty($tentatives)): ?>
            <div class="empty-message">
                <h2>Aucune tentative pour le moment</h2>
                <p>Vous n'avez pas encore réalisé de quiz. Commencez maintenant !</p>
                <a href="quizly.php" class="btn-primary">🚀 Commencer un quiz</a>
            </div>
        <?php else: ?>
            <div class="tentatives-list">
                <?php foreach ($tentatives as $tentative): 
                    $score = $tentative['score'];
                    $score_class = $score >= 16 ? 'excellent' : ($score >= 10 ? 'bon' : 'moyen');
                    
                    // Formater la date et l'heure
                    $datetime = new DateTime($tentative['date_passage']);
                    $date_fr = $datetime->format('d/m/Y');
                    $time_fr = $datetime->format('H:i:s');
                ?>
                    <div class="tentative-card">
                        <div class="tentative-info">
                            <span class="tentative-label">📅 Date</span>
                            <span class="tentative-value"><?php echo $date_fr; ?></span>
                        </div>
                        
                        <div class="tentative-info">
                            <span class="tentative-label">🕐 Heure</span>
                            <span class="tentative-value"><?php echo $time_fr; ?></span>
                        </div>
                        
                        <div class="score-badge <?php echo $score_class; ?>">
                            <?php echo number_format($score, 2); ?>/20
                        </div>
                        
                        <a href="detail_tentative.php?id=<?php echo $tentative['id']; ?>" class="btn-details">
                            Voir détails →
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="header-buttons">
            <a href="accueil.php" class="btn-retour">← Retour au menu</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Quizly - Quiz Informatique. Tous droits réservés.</p>
    </footer>
</body>
</html>
