<?php
session_start();
require 'bdd.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer les infos de l'utilisateur
$query = "SELECT role FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Vérifier que l'utilisateur est admin
if (!$user || $user['role'] != 1) {
    header('Location: accueil.php');
    exit;
}

// Traiter les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    
    if ($action === 'add_question') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "INSERT INTO questions (id_categorie, intitule, proposition_a, proposition_b, proposition_c, proposition_d, reponse, difficulte) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issssssi", 
            $data['id_categorie'],
            $data['intitule'],
            $data['proposition_a'],
            $data['proposition_b'],
            $data['proposition_c'],
            $data['proposition_d'],
            $data['reponse'],
            $data['difficulte']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'question_id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
    }
    
    elseif ($action === 'update_question') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "UPDATE questions SET id_categorie=?, intitule=?, proposition_a=?, proposition_b=?, proposition_c=?, proposition_d=?, reponse=?, difficulte=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issssssi", 
            $data['id_categorie'],
            $data['intitule'],
            $data['proposition_a'],
            $data['proposition_b'],
            $data['proposition_c'],
            $data['proposition_d'],
            $data['reponse'],
            $data['difficulte'],
            $data['question_id']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
    }
    
    elseif ($action === 'delete_question') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $query = "DELETE FROM questions WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $data['question_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
    }
    
    elseif ($action === 'delete_user') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Ne pas pouvoir supprimer le seul admin
        $admin_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 1");
        $admin_result = mysqli_fetch_assoc($admin_count);
        
        if ($admin_result['count'] <= 1 && $data['user_id'] == $_SESSION['id']) {
            echo json_encode(['success' => false, 'error' => 'Impossible de supprimer le seul admin']);
            exit;
        }
        
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $data['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
    }
    
    elseif ($action === 'toggle_admin') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Récupérer le rôle actuel
        $query = "SELECT role FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $data['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_to_update = mysqli_fetch_assoc($result);
        
        // Ne pas retirer le rôle admin au seul admin
        if ($user_to_update['role'] == 1) {
            $admin_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 1");
            $admin_result = mysqli_fetch_assoc($admin_count);
            if ($admin_result['count'] <= 1) {
                echo json_encode(['success' => false, 'error' => 'Impossible de retirer le rôle admin au seul admin']);
                exit;
            }
        }
        
        $new_role = $user_to_update['role'] == 1 ? 0 : 1;
        $query = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $new_role, $data['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'new_role' => $new_role]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
    }
    
    exit;
}

// Récupérer les catégories
$categories = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM categories"), MYSQLI_ASSOC);

// Récupérer les questions
$questions = mysqli_fetch_all(mysqli_query($conn, "SELECT q.*, c.nom as categorie_nom FROM questions q JOIN categories c ON q.id_categorie = c.id ORDER BY q.id DESC"), MYSQLI_ASSOC);

// Récupérer les utilisateurs
$users = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM users ORDER BY id"), MYSQLI_ASSOC);

// Récupérer les stats
$stats_query = "SELECT COUNT(*) as total_users FROM users";
$stats_users = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

$stats_query = "SELECT COUNT(*) as total_questions FROM questions";
$stats_questions = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

$stats_query = "SELECT COUNT(*) as total_tentatives FROM tentatives";
$stats_tentatives = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Quizly</title>
    <link rel="stylesheet" href="quizly.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }

        .section-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 12px 24px;
            background: #ecf0f1;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: #667eea;
            color: white;
        }

        .tab-btn:hover {
            background: #667eea;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: #f0f4ff;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .btn-submit {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #556ad5;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .questions-list {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .question-item {
            border-bottom: 1px solid #ecf0f1;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .question-item:last-child {
            border-bottom: none;
        }

        .question-info {
            flex: 1;
        }

        .question-text {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .question-meta {
            font-size: 13px;
            color: #7f8c8d;
        }

        .filter-bar .form-group {
            margin-bottom: 0;
        }

        .question-actions {
            display: flex;
            gap: 10px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .users-table thead {
            background: #f8f9fa;
        }

        .users-table th {
            padding: 15px;
            text-align: left;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #ecf0f1;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .users-table tbody tr:hover {
            background: #f8f9fa;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .role-admin {
            background: #d4edda;
            color: #155724;
        }

        .role-user {
            background: #e7e7ff;
            color: #667eea;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }

        .modal-close:hover {
            color: #2c3e50;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .question-item {
                flex-direction: column;
            }

            .question-actions {
                width: 100%;
            }

            .tab-buttons {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="topbar">
        🖥️ QUIZLY - Administration
    </div>

    <!-- Menu de navigation -->
    <div class="menu">
        <a href="accueil.php">Accueil</a>
        <a href="quizly.php">🚀 Commencer</a>
        <a href="historique.php">📊 Mon historique</a>
        <a href="admin.php" style="background: #667eea; color: white; border-radius: 4px;">⚙️ Administration</a>
        <a href="deconnexion.php">Déconnexion</a>
    </div>

    <!-- En-tête Admin -->
    <div class="admin-header">
        <h1>🔧 Panneau d'Administration</h1>
        <p>Gérez les questions et les utilisateurs du quiz</p>
    </div>

    <!-- Container principal -->
    <div class="admin-container">
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats_users['total_users']; ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats_questions['total_questions']; ?></div>
                <div class="stat-label">Questions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats_tentatives['total_tentatives']; ?></div>
                <div class="stat-label">Tentatives totales</div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="switchTab(event, 'questions')">📝 Gérer les Questions</button>
            <button class="tab-btn" onclick="switchTab(event, 'users')">👥 Gérer les Utilisateurs</button>
        </div>

        <!-- TAB 1: Questions -->
        <div id="questions" class="tab-content active">
            <div class="section-title">📝 Gestion des Questions</div>
            
            <button class="btn-submit" onclick="openQuestionModal()">➕ Ajouter une question</button>

            <div class="filter-bar" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-category">Filtrer par catégorie</label>
                    <select id="filter-category" onchange="filterQuestions()">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-difficulty">Filtrer par difficulté</label>
                    <select id="filter-difficulty" onchange="filterQuestions()">
                        <option value="">Toutes les difficultés</option>
                        <option value="1">Facile</option>
                        <option value="2">Moyen</option>
                        <option value="3">Difficile</option>
                    </select>
                </div>
                <div>
                    <button type="button" class="btn-secondary" onclick="resetQuestionFilters()" style="width: 100%;">Réinitialiser les filtres</button>
                </div>
            </div>
            <div id="filter-count" style="margin-top: 15px; color: #2c3e50; font-weight: 600;">Chargement du nombre de questions...</div>

            <div style="margin-top: 30px;">
                <div class="questions-list" id="questions-list">
                    <?php foreach ($questions as $q): ?>
                        <div class="question-item" id="question-<?php echo $q['id']; ?>" data-category-id="<?php echo $q['id_categorie']; ?>" data-difficulty="<?php echo $q['difficulte']; ?>">
                            <div class="question-info">
                                <div class="question-text"><?php echo htmlspecialchars($q['intitule']); ?></div>
                                <div class="question-meta">
                                    📂 <?php echo $q['categorie_nom']; ?> | 
                                    ⭐ <?php echo ['Facile', 'Moyen', 'Difficile'][$q['difficulte'] - 1] ?? 'Inconnu'; ?> | 
                                    ✓ Réponse: <strong><?php echo $q['reponse']; ?></strong>
                                </div>
                            </div>
                            <div class="question-actions">
                                <button class="btn-secondary" onclick="editQuestion(<?php echo $q['id']; ?>)">✏️ Modifier</button>
                                <button class="btn-danger" onclick="deleteQuestion(<?php echo $q['id']; ?>)">🗑️ Supprimer</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- TAB 2: Utilisateurs -->
        <div id="users" class="tab-content">
            <div class="section-title">👥 Gestion des Utilisateurs</div>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Moyenne</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr id="user-<?php echo $u['id']; ?>">
                            <td><?php echo htmlspecialchars($u['prenom'] . ' ' . $u['nom']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="role-badge <?php echo $u['role'] == 1 ? 'role-admin' : 'role-user'; ?>">
                                    <?php echo $u['role'] == 1 ? '👨‍💼 Admin' : '👤 Utilisateur'; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($u['moyenne_generale'], 2); ?>/20</td>
                            <td>
                                <button class="btn-secondary" onclick="toggleAdmin(<?php echo $u['id']; ?>)">
                                    <?php echo $u['role'] == 1 ? 'Retirer admin' : 'Faire admin'; ?>
                                </button>
                                <?php if ($u['id'] != $_SESSION['id']): ?>
                                    <button class="btn-danger" onclick="deleteUser(<?php echo $u['id']; ?>)">Supprimer</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour ajouter/modifier une question -->
    <div class="modal" id="question-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeQuestionModal()">&times;</span>
            <div class="modal-header" id="modal-title">Ajouter une question</div>

            <div id="form-alerts"></div>

            <form id="question-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="question-category">Catégorie *</label>
                        <select id="question-category" required>
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="question-difficulty">Difficulté *</label>
                        <select id="question-difficulty" required>
                            <option value="1">Facile</option>
                            <option value="2">Moyen</option>
                            <option value="3">Difficile</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="question-text">Question *</label>
                    <textarea id="question-text" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="option-a">Proposition A *</label>
                        <input type="text" id="option-a" required>
                    </div>
                    <div class="form-group">
                        <label for="option-b">Proposition B *</label>
                        <input type="text" id="option-b" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="option-c">Proposition C *</label>
                        <input type="text" id="option-c" required>
                    </div>
                    <div class="form-group">
                        <label for="option-d">Proposition D *</label>
                        <input type="text" id="option-d" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="correct-answer">Réponse correcte *</label>
                    <select id="correct-answer" required>
                        <option value="">-- Choisir --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>

                <button type="button" class="btn-submit" onclick="saveQuestion()">💾 Enregistrer</button>
                <button type="button" class="btn-secondary" onclick="closeQuestionModal()" style="margin-left: 10px;">Annuler</button>
            </form>
        </div>
    </div>

    <script>
        let currentEditingQuestionId = null;

        function switchTab(event, tabName) {
            // Masquer tous les onglets
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            // Afficher l'onglet sélectionné
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function openQuestionModal() {
            currentEditingQuestionId = null;
            document.getElementById('modal-title').textContent = 'Ajouter une question';
            document.getElementById('question-form').reset();
            document.getElementById('form-alerts').innerHTML = '';
            document.getElementById('question-modal').classList.add('active');
        }

        function closeQuestionModal() {
            document.getElementById('question-modal').classList.remove('active');
            currentEditingQuestionId = null;
        }

        function editQuestion(questionId) {
            // Récupérer les données de la question via AJAX (vous pouvez aussi les récupérer PHP côté)
            // Pour simplifier, on récupère les données du DOM
            currentEditingQuestionId = questionId;
            document.getElementById('modal-title').textContent = 'Modifier la question';
            document.getElementById('question-modal').classList.add('active');
            // TODO: Charger les données de la question dans le formulaire
        }

        function saveQuestion() {
            const formData = {
                id_categorie: parseInt(document.getElementById('question-category').value),
                intitule: document.getElementById('question-text').value,
                proposition_a: document.getElementById('option-a').value,
                proposition_b: document.getElementById('option-b').value,
                proposition_c: document.getElementById('option-c').value,
                proposition_d: document.getElementById('option-d').value,
                reponse: document.getElementById('correct-answer').value,
                difficulte: parseInt(document.getElementById('question-difficulty').value)
            };

            const action = currentEditingQuestionId ? 'update_question' : 'add_question';
            const url = `admin.php?action=${action}`;

            if (currentEditingQuestionId) {
                formData.question_id = currentEditingQuestionId;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Question enregistrée avec succès !', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Erreur : ' + (data.error || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                showAlert('Erreur réseau : ' + error, 'error');
            });
        }

        function deleteQuestion(questionId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
                fetch(`admin.php?action=delete_question`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ question_id: questionId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('question-' + questionId).remove();
                    } else {
                        alert('Erreur : ' + (data.error || 'Erreur inconnue'));
                    }
                })
                .catch(error => alert('Erreur réseau : ' + error));
            }
        }

        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                fetch(`admin.php?action=delete_user`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('user-' + userId).remove();
                    } else {
                        alert('Erreur : ' + (data.error || 'Erreur inconnue'));
                    }
                })
                .catch(error => alert('Erreur réseau : ' + error));
            }
        }

        function toggleAdmin(userId) {
            fetch(`admin.php?action=toggle_admin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + (data.error || 'Erreur inconnue'));
                }
            })
            .catch(error => alert('Erreur réseau : ' + error));
        }

        function showAlert(message, type) {
            const alertDiv = document.getElementById('form-alerts');
            alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }

        function filterQuestions() {
            const categoryFilter = document.getElementById('filter-category').value;
            const difficultyFilter = document.getElementById('filter-difficulty').value;
            const questions = document.querySelectorAll('.question-item');

            questions.forEach(question => {
                const itemCategory = question.dataset.categoryId || '';
                const itemDifficulty = question.dataset.difficulty || '';

                const matchesCategory = categoryFilter === '' || itemCategory === categoryFilter;
                const matchesDifficulty = difficultyFilter === '' || itemDifficulty === difficultyFilter;

                question.style.display = matchesCategory && matchesDifficulty ? 'flex' : 'none';
            });

            updateFilterCount();
        }

        function resetQuestionFilters() {
            document.getElementById('filter-category').value = '';
            document.getElementById('filter-difficulty').value = '';
            filterQuestions();
        }

        function updateFilterCount() {
            const questions = Array.from(document.querySelectorAll('.question-item'));
            const visibleCount = questions.filter(q => q.style.display !== 'none').length;
            const totalCount = questions.length;
            const categoryLabel = document.getElementById('filter-category').selectedOptions[0].textContent;
            const difficultyLabel = document.getElementById('filter-difficulty').selectedOptions[0].textContent;
            const activeFilters = [];

            if (document.getElementById('filter-category').value !== '') {
                activeFilters.push(`Catégorie : ${categoryLabel}`);
            }
            if (document.getElementById('filter-difficulty').value !== '') {
                activeFilters.push(`Difficulté : ${difficultyLabel}`);
            }

            const filterText = activeFilters.length ? ` (${activeFilters.join(' / ')})` : '';
            document.getElementById('filter-count').textContent = `${visibleCount} question(s) affichée(s) sur ${totalCount}${filterText}`;
        }

        // Fermer le modal en cliquant en dehors
        document.getElementById('question-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuestionModal();
            }
        });

        updateFilterCount();
    </script>
</body>
</html>
