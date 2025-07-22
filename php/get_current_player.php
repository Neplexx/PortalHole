<?php
$partie = $_GET['partie'] ?? 0;

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", "root", "root");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT current_player FROM parties WHERE id = ?");
$stmt->execute([$partie]);
$current = $stmt->fetchColumn();

echo json_encode(["current" => (int)$current]);
?>