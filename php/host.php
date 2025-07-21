<?php
session_start();

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("INSERT INTO parties (etat) VALUES ('attente')");
$partie_id = $pdo->lastInsertId();

$pseudo = $_SESSION['pseudo'] ?? "Hôte";
$pdo->prepare("INSERT INTO joueurs (pseudo, partie_id, est_hote, numero) VALUES (?, ?, 1, 1)")
    ->execute([$pseudo, $partie_id]);

header("Location: salle-attente.php?partie=$partie_id");
exit;
?>

