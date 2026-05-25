<?php
session_start();
require 'bdd.php';

if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

function getUserDifficultyUnlocks($user_id, $conn) {
    $unlocked = [
        1 => ['medium' => false, 'hard' => false],
        2 => ['medium' => false, 'hard' => false],
        3 => ['medium' => false, 'hard' => false],
    ];

    $query = "SELECT MIN(q.id_categorie) AS categorie_id, MAX(q.difficulte) AS max_difficulte
              FROM tentatives t
              JOIN reponses r ON r.tentative_id = t.id
              JOIN questions q ON q.id = r.question_id
              WHERE t.utilisateur_id = ? AND t.score >= 10
              GROUP BY t.id
              HAVING COUNT(DISTINCT q.id_categorie) = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $cat_id = (int)$row['categorie_id'];
        $max_difficulte = (int)$row['max_difficulte'];

        if (!isset($unlocked[$cat_id])) {
            continue;
        }

        if ($max_difficulte >= 1) {
            $unlocked[$cat_id]['medium'] = true;
        }
        if ($max_difficulte >= 2) {
            $unlocked[$cat_id]['hard'] = true;
        }
        if ($max_difficulte >= 3) {
            $unlocked[$cat_id]['medium'] = true;
            $unlocked[$cat_id]['hard'] = true;
        }
    }

    return $unlocked;
}

// Récupération des catégories et difficultés sélectionnées
$categories = [];
if (isset($_POST['categories'])) {
    // Si c'est un array, le garder; sinon, le convertir en array
    $categories = is_array($_POST['categories']) ? $_POST['categories'] : [$_POST['categories']];
    // Convertir les valeurs en entiers
    $categories = array_map('intval', $categories);
}

$difficultes_par_categorie = [];

if (empty($categories)) {
    // Si aucune catégorie n'est fournie, rediriger vers l'accueil
    header('Location: accueil.php');
    exit;
}

// Construire le tableau des difficultés pour chaque catégorie
// Avec les selects, une seule difficulté par catégorie
$unlocked = getUserDifficultyUnlocks($_SESSION['id'], $conn);

foreach ($categories as $cat_id) {
    $difficulte_key = 'difficulte_' . $cat_id;
    
    if (isset($_POST[$difficulte_key]) && $_POST[$difficulte_key] !== '') {
        $difficulte = (int)$_POST[$difficulte_key];

        if ($difficulte === 2 && !$unlocked[$cat_id]['medium']) {
            header('Location: accueil.php?error=niveau_non_autorise');
            exit;
        }

        if ($difficulte === 3 && !$unlocked[$cat_id]['hard']) {
            header('Location: accueil.php?error=niveau_non_autorise');
            exit;
        }

        // Convertir le niveau en incluant les niveaux inférieurs
        // Par exemple: niveau 2 = [1, 2], niveau 3 = [1, 2, 3]
        $diff_list = [];
        for ($i = 1; $i <= $difficulte; $i++) {
            $diff_list[] = $i;
        }
        $difficultes_par_categorie[$cat_id] = $diff_list;
    }
}

// Vérifier qu'il y a au moins une difficulté pour chaque catégorie
foreach ($categories as $cat_id) {
    if (!isset($difficultes_par_categorie[$cat_id]) || empty($difficultes_par_categorie[$cat_id])) {
        header('Location: accueil.php');
        exit;
    }
}

// Construire la requête SQL dynamique
$conditions = [];

foreach ($categories as $cat_id) {
    $diff_list = $difficultes_par_categorie[$cat_id];
    $diff_str = implode(',', $diff_list);
    
    $conditions[] = "(id_categorie = $cat_id AND difficulte IN ($diff_str))";
}

$where_clause = implode(' OR ', $conditions);

// D'abord, compter le nombre de questions réelles disponibles
$count_query = "SELECT COUNT(*) as total FROM questions WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_questions = $count_row['total'];

// Limiter à 20 maximum, mais utiliser le nombre réel disponible
$limit = min(20, $total_questions);
$query = "SELECT * FROM questions WHERE $where_clause ORDER BY RAND() LIMIT $limit";

$result = mysqli_query($conn, $query);
$questions = [];

while ($row = mysqli_fetch_assoc($result)) {
    // PROTECTION : On convertit les caractères spéciaux HTML
    $row['intitule'] = htmlspecialchars($row['intitule'], ENT_QUOTES, 'UTF-8');
    $row['proposition_a'] = htmlspecialchars($row['proposition_a'], ENT_QUOTES, 'UTF-8');
    $row['proposition_b'] = htmlspecialchars($row['proposition_b'], ENT_QUOTES, 'UTF-8');
    $row['proposition_c'] = htmlspecialchars($row['proposition_c'], ENT_QUOTES, 'UTF-8');
    $row['proposition_d'] = htmlspecialchars($row['proposition_d'], ENT_QUOTES, 'UTF-8');
    
    $questions[] = $row;
}
$questions_json = json_encode($questions);
$total_questions_real = count($questions);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quizly - Mode Marathon (20 Questions)</title>
    <link rel="stylesheet" href="quizly.css">
    <style>
        #quiz-container { background: white; padding: 30px; border-radius: 12px; max-width: 800px; width: 90%; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .option-btn { display: block; width: 100%; margin: 10px 0; padding: 15px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; text-align: left; background: white; font-size: 16px; transition: 0.2s; }
        .option-btn:hover { border-color: #667eea; background: #f0f4ff; }
      * {
    user-select: none;
}  
        /* Style du minuteur */
        .timer-wrapper { position: absolute; top: -15px; right: 20px; background: #e74c3c; color: white; padding: 10px 20px; border-radius: 20px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-size: 1.2rem; z-index: 10; }
        .timer-low { animation: blink 0.5s infinite; background: #c0392b; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        
        .warning-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(255,255,255,0.98); z-index: 9999; 
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center;
        }

        .progress-bar { width: 100%; height: 8px; background: #eee; border-radius: 4px; margin-bottom: 20px; overflow: hidden; }
        #progress-fill { height: 100%; background: #667eea; width: 0%; transition: width 0.3s; }

        /* Styles pour l'écran de résultats détaillés */
        #result-screen {
            flex-direction: column;
            align-items: center !important;
            justify-content: flex-start !important;
            padding: 40px 20px !important;
            overflow-y: auto;
            background: #f2f6fb !important;
        }

        .result-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            width: 100%;
            max-width: 900px;
            margin-bottom: 30px;
        }

        .result-summary h1 {
            margin-bottom: 20px;
            font-size: 28px;
        }

        .score-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .score-box {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 8px;
        }

        .score-value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .score-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .results-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .question-result {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #ddd;
        }

        .question-result.correct {
            border-left-color: #27ae60;
            background: #f0fff4;
        }

        .question-result.incorrect {
            border-left-color: #e74c3c;
            background: #fff5f5;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .question-title {
            flex: 1;
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
            font-size: 15px;
            line-height: 1.5;
        }

        .result-badge {
            background: #27ae60;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            margin-left: 15px;
        }

        .result-badge.incorrect {
            background: #e74c3c;
        }

        .answer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
            font-size: 14px;
        }

        .answer-block {
            padding: 12px;
            border-radius: 8px;
        }

        .answer-block h4 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 13px;
            font-weight: 600;
        }

        .answer-block p {
            margin: 0;
            color: #555;
        }

        .user-answer {
            background: #f0f4ff;
            border-left: 3px solid #667eea;
        }

        .correct-answer {
            background: #eafaf1;
            border-left: 3px solid #27ae60;
        }

        .action-buttons {
            width: 100%;
            max-width: 900px;
            margin: 30px auto 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
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
            transition: all 0.3s ease;
        }

        .btn-retour:hover {
            background: #1a252f;
        }

        .btn-historique {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-historique:hover {
            background: #556ad5;
        }

        @media (max-width: 768px) {
            .answer-info {
                grid-template-columns: 1fr;
            }
            
            #result-screen {
                padding: 20px !important;
            }
            
            .result-summary {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body oncontextmenu="return false;">

    <div id="start-screen" class="warning-overlay">
        <h1 style="color: #2c3e50;">Mode Marathon Informatique</h1>
        <div style="max-width: 500px; text-align: left; background: #fff3f3; padding: 25px; border-radius: 8px; border-left: 5px solid #e74c3c; margin: 20px 0;">
            <p><strong>⚡ Paramètres de la session :</strong></p>
            <ul>
                <li>Nombre de questions : <strong><?php echo $total_questions_real; ?></strong></li>
                <li>Temps par question : <strong>10 secondes</strong></li>
                <li>Plein écran : <strong>Obligatoire</strong></li>
                <li><strong>Anti-triche :</strong> 1 avertissement max, sinon 0/<?php echo $total_questions_real; ?>.</li>
            </ul>
        </div>
        <button onclick="launchSecureQuiz()" class="btn-primary" style="padding: 15px 45px; font-size: 1.1rem; cursor: pointer; background:#667eea; color:white; border:none; border-radius:8px; font-weight: bold;">
            ACCEPTER ET LANCER LE QUIZ
        </button>
    </div>

    <div id="main-content" style="display:none; height: 100vh; align-items: center; justify-content: center; background: #f2f6fb;">
        <div id="quiz-container">
            <div id="timer-box" class="timer-wrapper">
                ⏱️ <span id="timer-sec">10</span>s
            </div>
            
            <div id="warning-msg" style="display:none; color: #e74c3c; font-weight: bold; margin-bottom: 15px; text-align:center;">
                ⚠️ DERNIER RAPPEL : Restez en plein écran !
            </div>
            <p id="warning-counter" style="display:none; color:#e74c3c; font-weight:bold; text-align:center;">
    Avertissements : 0 / 2
</p>

            <div style="display:flex; justify-content: space-between; margin-bottom: 10px; color: #7f8c8d; font-weight: bold;">
                <span id="progress-text">Question 1 / 20</span>
                <span id="score-live">Score: 0</span>
            </div>
            
            <div class="progress-bar">
                <div id="progress-fill"></div>
            </div>

            <h2 id="question-text" style="margin-bottom:25px; color:#2c3e50; min-height: 60px;">Chargement...</h2>
            
            <div id="options-container"></div>
        </div>
    </div>

    <div id="result-screen" class="warning-overlay" style="display:none;">
        <div class="result-summary">
            <h1 id="res-status" style="color: white;">Quiz Terminé</h1>
            <div class="score-grid">
                <div class="score-box">
                    <div class="score-value" id="res-score">0/20</div>
                    <div class="score-label">Bonnes réponses</div>
                </div>
                <div class="score-box">
                    <div class="score-value" id="res-percentage">0%</div>
                    <div class="score-label">Réussite</div>
                </div>
                <div class="score-box">
                    <div class="score-value" id="res-rating">0</div>
                    <div class="score-label">Score sur 20</div>
                </div>
            </div>
            <p id="res-msg" style="font-size: 18px; margin-top: 20px; margin-bottom: 0;"></p>
        </div>

        <div class="results-container" id="results-details"></div>

        <div class="action-buttons">
            <a href="accueil.php" class="btn-retour">🏠 Retour au Menu</a>
            <a href="historique.php" class="btn-historique">📊 Mon historique</a>
        </div>
    </div>

    <script>
        const questions = <?php echo $questions_json; ?>;
        const totalQuestionsReal = <?php echo $total_questions_real; ?>;
        let currentIdx = 0;
        let correctAnswers = 0;
        let warnings = 0;
        let quizActive = false;
        let userAnswers = []; // Enregistrer toutes les réponses
        
        let timerInterval;
        let timeLeft = 10;

        function launchSecureQuiz() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) { elem.requestFullscreen(); }
            else if (elem.webkitRequestFullscreen) { elem.webkitRequestFullscreen(); }

            document.getElementById('start-screen').style.display = 'none';
            document.getElementById('main-content').style.display = 'flex';
            quizActive = true;
             renderQuestion();
        }

        function startTimer() {
            clearInterval(timerInterval);
            timeLeft = 10;
            const timerDisplay = document.getElementById('timer-sec');
            const timerBox = document.getElementById('timer-box');
            
            timerDisplay.textContent = timeLeft;
            timerBox.classList.remove('timer-low');
            
            timerInterval = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = timeLeft;
                
                if (timeLeft <= 3) {
                    timerBox.classList.add('timer-low');
                }
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    handleAnswer(null); // Trop tard
                }
            }, 1000);
        }

        // --- ANTI-TRICHE ---
        document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && quizActive) {
        warnings++;

        if (warnings > 1) {
    forceFailure("TRICHE DÉTECTÉE : sorties répétées du plein écran.");
    return;
}

        document.getElementById('warning-counter').style.display = 'block';

document.getElementById('warning-counter').textContent =
`Avertissements : ${warnings} / 2`;

   

        if (warnings >= 2) {
            forceFailure("TRICHE DÉTECTÉE : sorties répétées du plein écran.");
            return;
        }

        alert("ATTENTION ! Vous devez rester en plein écran pendant toute la durée du quiz. Toute nouvelle sortie sera considérée comme une tentative de triche et entraînera automatiquement un 0/20.");

        document.getElementById('warning-msg').style.display = 'block';

        const btnRetour = document.createElement('button');
        btnRetour.textContent = "Revenir en plein écran";
        btnRetour.style.margin = "15px auto";
        btnRetour.style.display = "block";
        btnRetour.style.padding = "12px 25px";
        btnRetour.style.background = "#e74c3c";
        btnRetour.style.color = "white";
        btnRetour.style.border = "none";
        btnRetour.style.borderRadius = "8px";
        btnRetour.style.cursor = "pointer";
        btnRetour.style.fontWeight = "bold";

        setTimeout(() => {
    if (quizActive && !document.fullscreenElement) {
        forceFailure("TRICHE DÉTECTÉE : vous n'êtes pas revenu en plein écran après l'avertissement.");
    }
}, 5000);

        btnRetour.onclick = () => {
             document.documentElement.requestFullscreen();
             btnRetour.remove();
        };

        document.getElementById('quiz-container').prepend(btnRetour);
    }
});

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && quizActive) {
                forceFailure("TRICHE DÉTECTÉE : Changement d'onglet ou fenêtre.");
            }
        });

        // Blocage clic droit
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// Blocage copier/coller
document.addEventListener('copy', function(e) {
    e.preventDefault();
});

document.addEventListener('paste', function(e) {
    e.preventDefault();
});

// Blocage de toute utilisation du clavier pendant le quiz
function blockKeyboard(e) {
    if (!quizActive) {
        return;
    }
    e.preventDefault();
    e.stopPropagation();
}

document.addEventListener('keydown', blockKeyboard);
document.addEventListener('keypress', blockKeyboard);
document.addEventListener('keyup', blockKeyboard);

        // --- LOGIQUE ---
        function renderQuestion() {
            if (currentIdx >= questions.length) {
                finishQuiz();
                return;
            }

            const q = questions[currentIdx];
            
            // Mise à jour interface
            document.getElementById('progress-text').textContent = `Question ${currentIdx + 1} / ${questions.length}`;
            document.getElementById('progress-fill').style.width = `${((currentIdx + 1) / questions.length) * 100}%`;
            
            // On utilise .innerHTML car le texte contient des entités protégées (&lt; &gt;)
            document.getElementById('question-text').innerHTML = q.intitule;
            
            const container = document.getElementById('options-container');
            container.innerHTML = '';

            ['a', 'b', 'c', 'd'].forEach(letter => {
                const btn = document.createElement('button');
                btn.className = 'option-btn';
                // Utilisation de innerHTML pour afficher correctement les balises HTML neutralisées
                btn.innerHTML = `<strong>${letter.toUpperCase()}.</strong> ${q['proposition_' + letter]}`;
                btn.onclick = () => handleAnswer(letter.toUpperCase());
                container.appendChild(btn);
            });

            startTimer();
        }

        function handleAnswer(selectedLetter) {
            const q = questions[currentIdx];
            const isCorrect = selectedLetter === q.reponse;
            
            if (isCorrect) {
                correctAnswers++;
                document.getElementById('score-live').textContent = `Score: ${correctAnswers}`;
            }
            
            // Enregistrer la réponse avec tous les détails
            userAnswers.push({
                question_id: q.id,
                reponse_utilisateur: selectedLetter || 'AUCUNE',
                correcte: isCorrect ? 1 : 0,
                intitule: q.intitule,
                proposition_a: q.proposition_a,
                proposition_b: q.proposition_b,
                proposition_c: q.proposition_c,
                proposition_d: q.proposition_d,
                reponse_correcte: q.reponse
            });
            
            currentIdx++;
            renderQuestion();
        }

        function finishQuiz() {
            quizActive = false;
            clearInterval(timerInterval);
            document.getElementById('main-content').style.display = 'none';
            document.getElementById('result-screen').style.display = 'flex';
            
            // Calculer le score final
            const totalQuestions = totalQuestionsReal;
            const score = (correctAnswers / totalQuestions) * 20; // Score sur 20
            const percentage = Math.round((correctAnswers / totalQuestions) * 100);
            
            document.getElementById('res-score').textContent = `${correctAnswers}/${totalQuestions}`;
            document.getElementById('res-percentage').textContent = `${percentage}%`;
            document.getElementById('res-rating').textContent = Math.round(score);
            
            let comment = "";
            if(correctAnswers >= totalQuestions * 0.8) comment = "Expert informatique ! 🏆";
            else if(correctAnswers >= totalQuestions * 0.5) comment = "Bien joué, la moyenne est là ! 👍";
            else if(correctAnswers >= totalQuestions * 0.25) comment = "C'est un bon début ! 💪";
            else comment = "Il faut encore réviser... 📚";
            
            document.getElementById('res-msg').textContent = comment;
            
            // Générer les résultats détaillés
            generateResultsDetails();
            
            if (document.exitFullscreen) document.exitFullscreen();
            
            // Sauvegarder le quiz en base de données
            saveQuizToDatabase(score);
        }

        function generateResultsDetails() {
            const container = document.getElementById('results-details');
            container.innerHTML = '';
            
            userAnswers.forEach((answer, index) => {
                const isCorrect = answer.correcte === 1;
                const userAnswerLetter = answer.reponse_utilisateur;
                const correctAnswerLetter = answer.reponse_correcte;
                
                const propositions = {
                    'A': answer.proposition_a,
                    'B': answer.proposition_b,
                    'C': answer.proposition_c,
                    'D': answer.proposition_d
                };
                
                const userAnswerText = userAnswerLetter !== 'AUCUNE' ? propositions[userAnswerLetter] : 'Pas de réponse';
                const correctAnswerText = propositions[correctAnswerLetter];
                
                const resultCard = document.createElement('div');
                resultCard.className = `question-result ${isCorrect ? 'correct' : 'incorrect'}`;
                
                resultCard.innerHTML = `
                    <div class="question-header">
                        <h3 class="question-title">Q${index + 1} : ${answer.intitule}</h3>
                        <span class="result-badge ${isCorrect ? '' : 'incorrect'}">
                            ${isCorrect ? '✓ CORRECT' : '✗ INCORRECT'}
                        </span>
                    </div>
                    <div class="answer-info">
                        <div class="answer-block user-answer">
                            <h4>Votre réponse</h4>
                            <p><strong>${userAnswerLetter !== 'AUCUNE' ? userAnswerLetter : '-'}.</strong> ${userAnswerText}</p>
                        </div>
                        <div class="answer-block correct-answer">
                            <h4>Bonne réponse</h4>
                            <p><strong>${correctAnswerLetter}.</strong> ${correctAnswerText}</p>
                        </div>
                    </div>
                `;
                
                container.appendChild(resultCard);
            });
        }

        function saveQuizToDatabase(score, motif = null) {
            // Nettoyer les réponses pour ne garder que ce qui est nécessaire en BDD
            const cleanedAnswers = userAnswers.map(answer => ({
                question_id: answer.question_id,
                reponse_utilisateur: answer.reponse_utilisateur,
                correcte: answer.correcte
            }));

            const payload = {
                score: score,
                reponses: cleanedAnswers,
                motif: motif
            };

            fetch('save_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Quiz sauvegardé avec succès ! ID tentative:', data.tentative_id);
                } else {
                    console.error('Erreur lors de la sauvegarde:', data.message);
                }
            })
            .catch(error => {
                console.error('Erreur réseau:', error);
            });
        }

        function forceFailure(reason) {
            quizActive = false;
            clearInterval(timerInterval);
            document.getElementById('main-content').style.display = 'none';
            document.getElementById('result-screen').style.display = 'flex';
            
            document.getElementById('res-status').textContent = "EXAMEN ANNULÉ ❌";
            document.getElementById('res-status').style.color = "red";
            document.getElementById('res-score').textContent = `00/${totalQuestionsReal}`;
            document.getElementById('res-score').style.color = "red";
            document.getElementById('res-msg').textContent = reason;
            
            if (document.exitFullscreen) document.exitFullscreen();
            saveQuizToDatabase(0, reason);
        }
    </script>
</body>
</html>