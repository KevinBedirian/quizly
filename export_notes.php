<?php
session_start();
require 'bdd.php';

if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['id'];

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="mes_notes_quiz.csv"');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

fputcsv($output, ['Nom', 'Prénom', 'Score', 'Date', 'Motif'], ';');

$query = "
    SELECT u.nom, u.prenom, t.score, t.date_passage, t.motif
    FROM tentatives t
    JOIN users u ON u.id = t.utilisateur_id
    WHERE t.utilisateur_id = ?
    ORDER BY t.date_passage DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['nom'],
        $row['prenom'],
        $row['score'],
        $row['date_passage'],
        $row['motif']
    ], ';');
}

fclose($output);
exit;
?>