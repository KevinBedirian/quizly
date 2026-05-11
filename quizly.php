<?php
session_start();
require 'bdd.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer 20 questions aléatoires
$query = "SELECT * FROM questions ORDER BY RAND() LIMIT 20";
$result = mysqli_query($conn, $query);
$questions = [];

while ($row = mysqli_fetch_assoc($result)) {
    $questions[] = $row;
}

// Si pas assez de questions
if (count($questions) < 20) {
    die("Erreur : Pas assez de questions dans la base de données");
}

// Convertir les questions en JSON pour JavaScript
$questions_json = json_encode($questions);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizly - Quiz</title>
    <link rel="stylesheet" href="quizly.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f2f6fb;
            font-family: Arial, Helvetica, sans-serif;
            overflow: hidden;
        }

        .quiz-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .quiz-header {
            background: rgba(0, 0, 0, 0.3);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .quiz-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .quiz-info {
            display: flex;
            gap: 30px;
            font-size: 18px;
        }

        .quiz-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .question-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .question-number {
            font-size: 14px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .timer-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        .question-title {
            font-size: 22px;
            color: #2c3e50;
            margin: 20px 0 30px 0;
            font-weight: bold;
        }

        .answers {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .answer-btn {
            background: #f5f7fa;
            border: 2px solid #ddd;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
        }

        .answer-btn:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .answer-btn.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .answer-btn.correct {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }

        .answer-btn.incorrect {
            background: #f44336;
            color: white;
            border-color: #f44336;
        }

        .quiz-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            margin-top: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #667eea;
            width: 0%;
            transition: width 0.3s ease;
        }

        .results-screen {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        .results-screen h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .score-display {
            font-size: 72px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .results-screen p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .results-btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 30px;
            transition: all 0.3s ease;
        }

        .results-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="quiz-container" id="quizContainer">
        <!-- En-tête du quiz -->
        <div class="quiz-header">
            <h1>🖥️ QUIZLY - Quiz Informatique</h1>
            <div class="quiz-info">
                <div>Question <span id="currentQuestion">1</span>/20</div>
                <div class="timer-circle">
                    <span id="timer">10</span>
                </div>
            </div>
        </div>

        <!-- Contenu du quiz -->
        <div class="quiz-content">
            <div class="question-card" id="questionCard">
                <div class="question-number" id="questionNumber">Question 1</div>
                <div class="question-title" id="questionTitle">Chargement...</div>
                
                <div class="answers" id="answersContainer">
                    <!-- Les réponses seront injectées ici -->
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>

            <!-- Écran de résultats -->
            <div class="results-screen" id="resultsScreen">
                <h2>Quiz terminé ! 🎉</h2>
                <div class="score-display" id="scoreDisplay">0/20</div>
                <p id="scorePercentage">0%</p>
                <p id="scoreMessage">Bravo !</p>
                <button class="results-btn" onclick="location.href='accueil.php'">Retourner à l'accueil</button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        const questions = <?php echo $questions_json; ?>;
        let currentQuestionIndex = 0;
        let score = 0;
        let timeLeft = 10;
        let selectedAnswer = null;
        let answered = false;
        let timerInterval;
        let quizActive = true;

        // Forcer le mode plein écran
        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen().catch(err => {
                    console.log("Plein écran non disponible:", err);
                });
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        }

        // Détecter si l'utilisateur quitte le plein écran
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement && quizActive) {
                if (!confirm('Attention ! Vous avez quitté le mode plein écran. Le quiz peut être annulé. Voulez-vous continuer ?')) {
                    endQuiz();
                }
            }
        });

        // Avertissement si l'utilisateur essaie de quitter la page
        window.addEventListener('beforeunload', (e) => {
            if (quizActive) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Avertissement sur Alt+Tab ou changement d'onglet
        window.addEventListener('blur', () => {
            if (quizActive) {
                alert('Attention ! Vous ne devez pas quitter le quiz. Revenez à la fenêtre du quiz.');
            }
        });

        // Empêcher le clic droit
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });

        // Initialiser le quiz
        function initQuiz() {
            enterFullscreen();
            displayQuestion();
            startTimer();
        }

        // Afficher la question actuelle
        function displayQuestion() {
            if (currentQuestionIndex >= questions.length) {
                endQuiz();
                return;
            }

            const question = questions[currentQuestionIndex];
            answered = false;
            selectedAnswer = null;

            // Mettre à jour les informations
            document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;
            document.getElementById('questionNumber').textContent = `Question ${currentQuestionIndex + 1}/20`;
            document.getElementById('questionTitle').textContent = question.intitule;

            // Créer les boutons de réponse
            const answersContainer = document.getElementById('answersContainer');
            answersContainer.innerHTML = '';

            const answers = [
                { text: question.proposition_a, value: 'A' },
                { text: question.proposition_b, value: 'B' },
                { text: question.proposition_c, value: 'C' },
                { text: question.proposition_d, value: 'D' }
            ];

            answers.forEach(answer => {
                const btn = document.createElement('button');
                btn.className = 'answer-btn';
                btn.textContent = `${answer.value}. ${answer.text}`;
                btn.onclick = () => selectAnswer(answer.value, question.reponse, btn);
                answersContainer.appendChild(btn);
            });

            // Réinitialiser le timer
            timeLeft = 10;
            document.getElementById('timer').textContent = timeLeft;
            clearInterval(timerInterval);
            startTimer();

            // Mettre à jour la barre de progression
            updateProgressBar();
        }

        // Sélectionner une réponse
        function selectAnswer(selectedValue, correctAnswer, btnElement) {
            if (answered) return;

            answered = true;
            selectedAnswer = selectedValue;

            // Désactiver tous les boutons
            document.querySelectorAll('.answer-btn').forEach(btn => {
                btn.style.pointerEvents = 'none';
            });

            // Afficher la bonne réponse
            document.querySelectorAll('.answer-btn').forEach((btn, index) => {
                const answerValue = ['A', 'B', 'C', 'D'][index];
                if (answerValue === correctAnswer) {
                    btn.classList.add('correct');
                } else if (answerValue === selectedValue && selectedValue !== correctAnswer) {
                    btn.classList.add('incorrect');
                }
            });

            // Ajouter un point si la réponse est correcte
            if (selectedValue === correctAnswer) {
                score++;
            }

            // Passer à la question suivante après 1.5 secondes
            setTimeout(() => {
                currentQuestionIndex++;
                displayQuestion();
            }, 1500);
        }

        // Timer
        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    if (!answered) {
                        answered = true;
                        document.querySelectorAll('.answer-btn').forEach(btn => {
                            btn.style.pointerEvents = 'none';
                        });
                        setTimeout(() => {
                            currentQuestionIndex++;
                            displayQuestion();
                        }, 1500);
                    }
                }
            }, 1000);
        }

        // Mettre à jour la barre de progression
        function updateProgressBar() {
            const progress = ((currentQuestionIndex) / 20) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }

        // Terminer le quiz
        function endQuiz() {
            quizActive = false;
            clearInterval(timerInterval);

            // Calculer le pourcentage
            const percentage = Math.round((score / 20) * 100);

            // Afficher les résultats
            document.getElementById('questionCard').style.display = 'none';
            document.getElementById('resultsScreen').style.display = 'flex';
            document.getElementById('scoreDisplay').textContent = `${score}/20`;
            document.getElementById('scorePercentage').textContent = `${percentage}%`;

            // Message personnalisé selon le score
            let message = '';
            if (percentage >= 80) {
                message = 'Excellent ! Vous maîtrisez bien l\'informatique ! 🌟';
            } else if (percentage >= 60) {
                message = 'Très bien ! Vous avez de bonnes connaissances ! 👏';
            } else if (percentage >= 40) {
                message = 'Pas mal ! Continuez à apprendre ! 📚';
            } else {
                message = 'À bientôt pour un autre essai ! 💪';
            }

            document.getElementById('scoreMessage').textContent = message;

            // Sauvegarder le score (optionnel)
            saveScore(score);
        }

        // Sauvegarder le score (à adapter selon votre structure)
        function saveScore(finalScore) {
            // Vous pouvez ajouter une requête AJAX pour sauvegarder le score
            // fetch('save_score.php', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ score: finalScore })
            // });
        }

        // Lancer le quiz au chargement
        window.addEventListener('load', initQuiz);
    </script>
</body>
</html>
