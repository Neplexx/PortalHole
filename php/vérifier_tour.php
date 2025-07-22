<?php
$partie_id = $_GET['partie'] ?? 0;
$joueur_numero = $_GET['joueur'] ?? 0;

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT current_player FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$current = $stmt->fetchColumn();

echo ($current == $joueur_numero) ? "1" : "0";
?>
