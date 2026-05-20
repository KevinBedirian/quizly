<?php
session_start();
require 'bdd.php';

if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: historique.php');
    exit;
}

$tentative_id = intval($_GET['id']);
$user_id = $_SESSION['id'];

// Vérifier que la tentative appartient à l'utilisateur
$query = "SELECT * FROM tentatives WHERE id = ? AND utilisateur_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $tentative_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tentative = mysqli_fetch_assoc($result);

if (!$tentative) {
    header('Location: historique.php');
    exit;
}

// Récupérer les réponses de l'utilisateur avec les détails des questions
$query = "
    SELECT r.id, r.question_id, r.reponse_utilisateur, r.correcte, 
           q.intitule, q.proposition_a, q.proposition_b, q.proposition_c, q.proposition_d, q.reponse as reponse_correcte
    FROM reponses r
    JOIN questions q ON r.question_id = q.id
    WHERE r.tentative_id = ?
    ORDER BY r.id ASC
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $tentative_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reponses = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Récupérer les infos de l'utilisateur
$query_user = "SELECT nom, prenom FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $query_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

// Calculer le score correct
$correct_count = 0;
foreach ($reponses as $rep) {
    if ($rep['correcte']) $correct_count++;
}

// Nombre total de questions de cette tentative
$total_reponses = count($reponses);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de tentative - Quizly</title>
    <link rel="stylesheet" href="quizly.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .detail-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            text-align: center;
        }

        .detail-header h1 {
            margin-bottom: 20px;
            font-size: 28px;
        }

        .score-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .score-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .score-item-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .score-item-label {
            font-size: 12px;
            opacity: 0.9;
        }

        .question-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #ddd;
        }

        .question-card.correct {
            border-left-color: #27ae60;
        }

        .question-card.incorrect {
            border-left-color: #e74c3c;
        }

        .question-header {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: flex-start;
        }

        .question-number {
            background: #667eea;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .question-title {
            flex: 1;
        }

        .question-title h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .question-title p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .question-status {
            background: #27ae60;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .question-status.incorrect {
            background: #e74c3c;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }

        .option {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
            font-size: 14px;
        }

        .option.selected {
            border-color: #667eea;
            background: #f0f4ff;
            font-weight: 600;
        }

        .option.correct {
            border-color: #27ae60;
            background: #eafaf1;
            color: #27ae60;
        }

        .option.incorrect {
            border-color: #e74c3c;
            background: #fadbd8;
            color: #e74c3c;
        }

        .option-letter {
            font-weight: bold;
            margin-right: 10px;
        }

        .answer-analysis {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .answer-block {
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
        }

        .answer-block h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .answer-block p {
            color: #555;
        }

        .answer-block.user-answer {
            background: #f0f4ff;
            border-left: 3px solid #667eea;
        }

        .answer-block.correct-answer {
            background: #eafaf1;
            border-left: 3px solid #27ae60;
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
            margin-top: 30px;
        }

        .btn-retour:hover {
            background: #1a252f;
        }

        .btn-back-link {
            text-align: center;
        }

        @media (max-width: 768px) {
            .answer-analysis {
                grid-template-columns: 1fr;
            }

            .question-header {
                flex-direction: column;
            }

            .score-info {
                grid-template-columns: 1fr;
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
    <div class="detail-container">
        <div class="detail-header">
            <h1>📋 Détail de votre tentative</h1>
            <p><?php 
                $datetime = new DateTime($tentative['date_passage']);
                echo 'Quiz du ' . $datetime->format('d/m/Y à H:i:s'); 
            ?></p>
            
            <div class="score-info">
                <div class="score-item">
                    <div class="score-item-value"><?php echo $correct_count . '/' . count($reponses); ?></div>
                    <div class="score-item-label">Bonnes réponses</div>
                </div>
                <div class="score-item">
                    <div class="score-item-value"><?php echo number_format($tentative['score'], 2); ?></div>
                    <div class="score-item-label">Score final</div>
                </div>
                <div class="score-item">
                    <div class="score-item-value"><?php echo round(($correct_count / count($reponses)) * 100); ?>%</div>
                    <div class="score-item-label">Pourcentage</div>
                </div>
            </div>
        </div>

        <!-- Questions et réponses -->
        <div>
            <?php foreach ($reponses as $index => $rep): 
                $is_correct = $rep['correcte'];
                $user_answer = $rep['reponse_utilisateur'];
                $correct_answer = $rep['reponse_correcte'];
                
                $propositions = [
                    'A' => $rep['proposition_a'],
                    'B' => $rep['proposition_b'],
                    'C' => $rep['proposition_c'],
                    'D' => $rep['proposition_d']
                ];
            ?>
                <div class="question-card <?php echo $is_correct ? 'correct' : 'incorrect'; ?>">
                    <div class="question-header">
                        <div class="question-number"><?php echo $index + 1; ?></div>
                        <div class="question-title">
                            <h3><?php echo htmlspecialchars($rep['intitule']); ?></h3>
                            <p><?php echo $is_correct ? '✅ Bonne réponse' : '❌ Mauvaise réponse'; ?></p>
                        </div>
                        <span class="question-status <?php echo !$is_correct ? 'incorrect' : ''; ?>">
                            <?php echo $is_correct ? '✓ CORRECT' : '✗ INCORRECT'; ?>
                        </span>
                    </div>

                    <div class="options-list">
                        <?php foreach ($propositions as $letter => $texte): 
                            $is_selected = ($letter === $user_answer);
                            $is_correct_option = ($letter === $correct_answer);
                            $class = '';
                            
                            if ($is_selected && $is_correct_option) {
                                $class = 'selected correct';
                            } elseif ($is_selected && !$is_correct_option) {
                                $class = 'selected incorrect';
                            } elseif (!$is_selected && $is_correct_option) {
                                $class = 'correct';
                            }
                        ?>
                            <div class="option <?php echo $class; ?>">
                                <span class="option-letter"><?php echo $letter; ?>)</span>
                                <span><?php echo htmlspecialchars($texte); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="answer-analysis">
                        <div class="answer-block user-answer">
                            <h4>📝 Votre réponse</h4>
                            <p>
                                <strong><?php echo $user_answer; ?>)</strong> 
                                <?php echo htmlspecialchars($propositions[$user_answer]); ?>
                            </p>
                        </div>
                        <div class="answer-block correct-answer">
                            <h4>✅ Bonne réponse</h4>
                            <p>
                                <strong><?php echo $correct_answer; ?>)</strong> 
                                <?php echo htmlspecialchars($propositions[$correct_answer]); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="btn-back-link">
            <a href="historique.php" class="btn-retour">← Retour à l'historique</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Quizly - Quiz Informatique. Tous droits réservés.</p>
    </footer>
</body>
</html>
