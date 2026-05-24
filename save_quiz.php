<?php
session_start();
require 'bdd.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// Vérifier que les données sont envoyées en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['reponses']) || !isset($data['score'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$user_id = $_SESSION['id'];
$score = floatval($data['score']);
$reponses = $data['reponses'];
$motif = isset($data['motif']) ? $data['motif'] : NULL;

// Démarrer une transaction
mysqli_begin_transaction($conn);

try {
    // 1. Créer une nouvelle tentative
    $query = "INSERT INTO tentatives (utilisateur_id, score, date_passage, motif) VALUES (?, ?, NOW(), ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Erreur de préparation : " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ids", $user_id, $score, $motif);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erreur d'exécution : " . mysqli_stmt_error($stmt));
    }
    
    $tentative_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // 2. Insérer chaque réponse
    $query = "INSERT INTO reponses (tentative_id, question_id, reponse_utilisateur, correcte) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Erreur de préparation : " . mysqli_error($conn));
    }
    
    foreach ($reponses as $rep) {
        $question_id = intval($rep['question_id']);
        $user_answer = $rep['reponse_utilisateur'];
        $is_correct = intval($rep['correcte']);
        
        mysqli_stmt_bind_param($stmt, "iisi", $tentative_id, $question_id, $user_answer, $is_correct);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erreur lors de l'insertion de la réponse : " . mysqli_stmt_error($stmt));
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // 3. Mettre à jour la moyenne générale de l'utilisateur
    $query = "SELECT AVG(score) as moyenne FROM tentatives WHERE utilisateur_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $nouvelle_moyenne = floatval($row['moyenne']);
    mysqli_stmt_close($stmt);
    
    $query = "UPDATE users SET moyenne_generale = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "di", $nouvelle_moyenne, $user_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erreur lors de la mise à jour de la moyenne : " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    
    // Valider la transaction
    mysqli_commit($conn);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Quiz sauvegardé avec succès',
        'tentative_id' => $tentative_id
    ]);

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    mysqli_rollback($conn);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()
    ]);
}
?>
