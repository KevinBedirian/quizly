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
                <button class="btn-primary" id="btn-start" style="cursor: pointer;">🚀 Commencer une partie</button>
            <?php else: ?>
                <button class="btn-primary"><a href="connexion.php" style="color: #667eea; text-decoration: none;">🔓 Se connecter</a></button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de sélection des catégories et difficultés -->
    <div id="quiz-selection-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; flex-direction: column; align-items: center; justify-content: center;">
        <div style="background: white; padding: 40px; border-radius: 12px; max-width: 700px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <h2 style="color: #2c3e50; margin-bottom: 10px; text-align: center;">Configurez votre Quiz</h2>
            <p style="color: #666; text-align: center; margin-bottom: 30px;">Sélectionnez 1 à 3 catégories et choisissez un niveau pour chacune.</p>
            
            <form id="quiz-form" method="POST" action="quizly.php" style="display: flex; flex-direction: column; gap: 20px;">
                
                <!-- Catégorie 1 -->
                <div style="padding: 20px; border: 2px solid #ddd; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 16px; font-weight: 600; margin-bottom: 15px;">
                        <input type="checkbox" name="categories" value="1" class="category-checkbox" style="width: 20px; height: 20px; cursor: pointer;">
                        Cybersécurité
                    </label>
                    <div style="margin-left: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #555;">Choisir un niveau :</label>
                        <select name="difficulte_1" class="difficulty-select" data-category="1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 14px;">
                            <option value="">-- Sélectionner --</option>
                            <option value="1">🟢 Débutant</option>
                            <option value="2">🟡 Intermédiaire</option>
                            <option value="3">🔴 Avancé</option>
                        </select>
                    </div>
                </div>

                <!-- Catégorie 2 -->
                <div style="padding: 20px; border: 2px solid #ddd; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 16px; font-weight: 600; margin-bottom: 15px;">
                        <input type="checkbox" name="categories" value="2" class="category-checkbox" style="width: 20px; height: 20px; cursor: pointer;">
                        Développement Web
                    </label>
                    <div style="margin-left: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #555;">Choisir un niveau :</label>
                        <select name="difficulte_2" class="difficulty-select" data-category="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 14px;">
                            <option value="">-- Sélectionner --</option>
                            <option value="1">🟢 Débutant</option>
                            <option value="2">🟡 Intermédiaire</option>
                            <option value="3">🔴 Avancé</option>
                        </select>
                    </div>
                </div>

                <!-- Catégorie 3 -->
                <div style="padding: 20px; border: 2px solid #ddd; border-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 16px; font-weight: 600; margin-bottom: 15px;">
                        <input type="checkbox" name="categories" value="3" class="category-checkbox" style="width: 20px; height: 20px; cursor: pointer;">
                        Bases de données
                    </label>
                    <div style="margin-left: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #555;">Choisir un niveau :</label>
                        <select name="difficulte_3" class="difficulty-select" data-category="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 14px;">
                            <option value="">-- Sélectionner --</option>
                            <option value="1">🟢 Débutant</option>
                            <option value="2">🟡 Intermédiaire</option>
                            <option value="3">🔴 Avancé</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                    <button type="button" onclick="document.getElementById('quiz-selection-modal').style.display='none'" style="background: #95a5a6; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Annuler</button>
                    <button type="submit" id="submit-quiz" style="background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;" disabled>Lancer le Quiz</button>
                </div>
            </form>
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

    <script>
        // Afficher le modal au clic du bouton "Commencer"
        const btnStart = document.getElementById('btn-start');
        if (btnStart) {
            btnStart.addEventListener('click', function() {
                document.getElementById('quiz-selection-modal').style.display = 'flex';
            });
        }

        // Gestion des selects de difficulté
        const difficultySelects = document.querySelectorAll('.difficulty-select');
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
        const submitBtn = document.getElementById('submit-quiz');
        const quizForm = document.getElementById('quiz-form');

        difficultySelects.forEach(select => {
            select.addEventListener('change', function() {
                const categoryId = this.getAttribute('data-category');
                const categoryCheckbox = document.querySelector(`input[name="categories"][value="${categoryId}"]`);
                
                // Si un niveau est sélectionné, cocher la catégorie
                if (this.value !== '') {
                    categoryCheckbox.checked = true;
                } else {
                    categoryCheckbox.checked = false;
                }
                
                validateForm();
            });
        });

        // Gestion de la catégorie - la décoche automatiquement si on réinitialise le select
        categoryCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const categoryId = this.value;
                const selectElement = document.querySelector(`select[name="difficulte_${categoryId}"]`);
                
                // Si on décoche la catégorie, réinitialiser le select
                if (!this.checked) {
                    selectElement.value = '';
                }
                
                validateForm();
            });
        });

        function validateForm() {
            const checkedCategories = Array.from(categoryCheckboxes).filter(cb => cb.checked);
            
            // Vérifier que chaque catégorie a une difficulté sélectionnée
            const allValid = checkedCategories.every(cb => {
                const categoryNum = cb.value;
                const selectElement = document.querySelector(`select[name="difficulte_${categoryNum}"]`);
                return selectElement.value !== '';
            });
            
            submitBtn.disabled = checkedCategories.length === 0 || !allValid;
        }

        // Validation avant envoi du formulaire
        quizForm.addEventListener('submit', function(e) {
            const checkedCategories = Array.from(categoryCheckboxes).filter(cb => cb.checked);
            if (checkedCategories.length === 0 || checkedCategories.length > 3) {
                e.preventDefault();
                alert('Vous devez sélectionner entre 1 et 3 catégories.');
                return;
            }
            
            // Vérifier que chaque catégorie a une difficulté sélectionnée
            for (const cb of checkedCategories) {
                const categoryNum = cb.value;
                const selectElement = document.querySelector(`select[name="difficulte_${categoryNum}"]`);
                if (selectElement.value === '') {
                    e.preventDefault();
                    alert(`Veuillez sélectionner un niveau pour chaque catégorie.`);
                    return;
                }
            }
        });
    </script>

</body>
</html>