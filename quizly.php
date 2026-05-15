<?php
session_start();
require 'bdd.php';

if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupération de 20 questions aléatoires (puisées dans tes 100 questions SQL)
$query = "SELECT * FROM questions ORDER BY RAND() LIMIT 20";
$result = mysqli_query($conn, $query);
$questions = [];

while ($row = mysqli_fetch_assoc($result)) {
    // PROTECTION : On convertit les caractères spéciaux HTML pour éviter que 
    // des balises comme <script> ne cassent le JSON ou l'affichage.
    $row['intitule'] = htmlspecialchars($row['intitule'], ENT_QUOTES, 'UTF-8');
    $row['proposition_a'] = htmlspecialchars($row['proposition_a'], ENT_QUOTES, 'UTF-8');
    $row['proposition_b'] = htmlspecialchars($row['proposition_b'], ENT_QUOTES, 'UTF-8');
    $row['proposition_c'] = htmlspecialchars($row['proposition_c'], ENT_QUOTES, 'UTF-8');
    $row['proposition_d'] = htmlspecialchars($row['proposition_d'], ENT_QUOTES, 'UTF-8');
    
    $questions[] = $row;
}
$questions_json = json_encode($questions);
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
    </style>
</head>
<body oncontextmenu="return false;">

    <div id="start-screen" class="warning-overlay">
        <h1 style="color: #2c3e50;">Mode Marathon Informatique</h1>
        <div style="max-width: 500px; text-align: left; background: #fff3f3; padding: 25px; border-radius: 8px; border-left: 5px solid #e74c3c; margin: 20px 0;">
            <p><strong>⚡ Paramètres de la session :</strong></p>
            <ul>
                <li>Nombre de questions : <strong>20</strong></li>
                <li>Temps par question : <strong>10 secondes</strong></li>
                <li>Plein écran : <strong>Obligatoire</strong></li>
                <li><strong>Anti-triche :</strong> 1 avertissement max, sinon 0/20.</li>
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
        <h1 id="res-status" style="font-size: 2.5rem;">Quiz Terminé</h1>
        <div id="res-score" style="font-size: 80px; font-weight: bold; margin: 20px 0; color: #667eea;">0/20</div>
        <p id="res-msg" style="font-size: 20px; color: #7f8c8d; margin-bottom: 30px;"></p>
        <a href="accueil.php" class="btn-primary" style="text-decoration: none; padding: 15px 40px; background:#2c3e50; color:white; border-radius:8px;">Retour au Menu</a>
    </div>

    <script>
        const questions = <?php echo $questions_json; ?>;
        let currentIdx = 0;
        let correctAnswers = 0;
        let warnings = 0;
        let quizActive = false;
        
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
                if (warnings === 1) {
                    alert("ATTENTION ! Ne quittez pas le plein écran. Prochaine fois, c'est le 0/20 immédiat.");
                    document.getElementById('warning-msg').style.display = 'block';
                } else {
                    forceFailure("TRICHE DÉTECTÉE : Sorties répétées du mode sécurisé.");
                }
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && quizActive) {
                forceFailure("TRICHE DÉTECTÉE : Changement d'onglet ou fenêtre.");
            }
        });

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
            if (selectedLetter === q.reponse) {
                correctAnswers++;
                document.getElementById('score-live').textContent = `Score: ${correctAnswers}`;
            }
            
            currentIdx++;
            renderQuestion();
        }

        function finishQuiz() {
            quizActive = false;
            clearInterval(timerInterval);
            document.getElementById('main-content').style.display = 'none';
            document.getElementById('result-screen').style.display = 'flex';
            
            document.getElementById('res-score').textContent = `${correctAnswers}/20`;
            
            let comment = "";
            if(correctAnswers >= 16) comment = "Expert informatique ! 🏆";
            else if(correctAnswers >= 10) comment = "Bien joué, la moyenne est là ! 👍";
            else comment = "Il faut encore réviser... 💪";
            
            document.getElementById('res-msg').textContent = comment;
            
            if (document.exitFullscreen) document.exitFullscreen();
        }

        function forceFailure(reason) {
            quizActive = false;
            clearInterval(timerInterval);
            document.getElementById('main-content').style.display = 'none';
            document.getElementById('result-screen').style.display = 'flex';
            
            document.getElementById('res-status').textContent = "EXAMEN ANNULÉ ❌";
            document.getElementById('res-status').style.color = "red";
            document.getElementById('res-score').textContent = "00/20";
            document.getElementById('res-score').style.color = "red";
            document.getElementById('res-msg').textContent = reason;
            
            if (document.exitFullscreen) document.exitFullscreen();
        }
    </script>
</body>
</html>