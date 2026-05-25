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
$query_user = "SELECT nom, prenom, moyenne_generale FROM users WHERE id = ?";
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

        .stats-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            text-align: center;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: bold;
        }

        .cheat-badge {
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
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

            .stats-summary {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .stat-value {
                font-size: 28px;
            }
        }

        /* Conteneur compact pour le graphique */
        .chart-wrapper {
            background: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            height: 220px; /* hauteur compacte */
            max-width: 100%;
            overflow: hidden;
        }

        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
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

        <div class="stats-summary">
            <div class="stat-item">
                <div class="stat-label">📈 Moyenne Générale</div>
                <div class="stat-value"><?php echo number_format($user['moyenne_generale'], 2); ?>/20</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">🎯 Tentatives Totales</div>
                <div class="stat-value"><?php echo count($tentatives); ?></div>
            </div>
        </div>

        <?php
        // Préparer les données pour le graphique des scores et des triches
        $chart_labels = [];
        $chart_scores = [];
        $cheat_counts = [];
        foreach ($tentatives as $t) {
            $dt = new DateTime($t['date_passage']);
            $chart_labels[] = $dt->format('d/m/Y H:i');
            $chart_scores[] = (float) $t['score'];

            // Compter les triches par jour (si 'motif' est présent)
            $day = $dt->format('d/m/Y');
            if (!isset($cheat_counts[$day])) {
                $cheat_counts[$day] = 0;
            }
            if (!empty($t['motif'])) {
                $cheat_counts[$day]++;
            }
        }
        // Préparer tableaux pour JS
        $cheat_labels = array_keys($cheat_counts);
        $cheat_values = array_values($cheat_counts);
        ?>

        <?php if (empty($tentatives)): ?>
            <div class="empty-message">
                <h2>Aucune tentative pour le moment</h2>
                <p>Vous n'avez pas encore réalisé de quiz. Commencez maintenant !</p>
                <a href="quizly.php" class="btn-primary">🚀 Commencer un quiz</a>
            </div>
        <?php else: ?>
            <!-- Graphique des scores -->
            <div class="chart-wrapper">
                <h3 style="margin-top:0;">Historique des scores</h3>
                <canvas id="scoresChart"></canvas>
            </div>

            <div class="chart-wrapper">
                <h3 style="margin-top:0;">Nombre de triches par jour</h3>
                <canvas id="cheatsChart"></canvas>
            </div>
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
                        
                        <div>
                            <div class="score-badge <?php echo $score_class; ?>">
                                <?php echo number_format($score, 2); ?>/20
                            </div>
                            <?php if ($tentative['motif']): ?>
                                <div class="cheat-badge">
                                    ⚠️ <?php echo htmlspecialchars($tentative['motif']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="detail_tentative.php?id=<?php echo $tentative['id']; ?>" class="btn-details">
                            Voir détails →
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="header-buttons">

        <a href="export_notes.php" class="btn-historique">
            📥 Exporter mes notes CSV
        </a>

            <a href="accueil.php" class="btn-retour">← Retour au menu</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Quizly - Quiz Informatique. Tous droits réservés.</p>
    </footer>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function(){
            const labels = <?php echo json_encode($chart_labels, JSON_UNESCAPED_UNICODE); ?>;
            const scores = <?php echo json_encode($chart_scores); ?>;

            // Si on veut afficher dans l'ordre chronologique (anciennes -> récentes)
            const reversedLabels = labels.slice().reverse();
            const reversedScores = scores.slice().reverse();

            const ctx = document.getElementById('scoresChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: reversedLabels,
                    datasets: [{
                        label: 'Score (/20)',
                        data: reversedScores,
                        borderColor: 'rgba(102,126,234,0.9)',
                        backgroundColor: 'rgba(102,126,234,0.15)',
                        tension: 0.25,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(118,75,162,0.9)'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            suggestedMin: 0,
                            suggestedMax: 20
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            // Graphique des triches (barres)
            const cheatLabels = <?php echo json_encode($cheat_labels, JSON_UNESCAPED_UNICODE); ?>;
            const cheatCounts = <?php echo json_encode($cheat_values); ?>;
            const reversedCheatLabels = cheatLabels.slice().reverse();
            const reversedCheatCounts = cheatCounts.slice().reverse();
            const ctx2 = document.getElementById('cheatsChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: reversedCheatLabels,
                    datasets: [{
                        label: 'Triches',
                        data: reversedCheatCounts,
                        backgroundColor: 'rgba(231,76,60,0.9)'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    },
                    plugins: { legend: { display: false } },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        })();
    </script>
</body>
</html>
