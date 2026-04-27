<?php
$serveur = "localhost";
$utilisateur = "root";
$motdepasse = "root";
$base = "quizly";

$conn = mysqli_connect($serveur, $utilisateur, $motdepasse, $base);

if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
