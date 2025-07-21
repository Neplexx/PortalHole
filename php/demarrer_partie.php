<?php
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$partie_id = $_POST['partie_id'] ?? null;

if ($partie_id) {
    $pdo->prepare("UPDATE parties SET etat = 'en_cours' WHERE id = ?")->execute([$partie_id]);
    header("Location: jeu.php?partie=$partie_id");
    exit;
}
