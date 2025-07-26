<?php
$partie_id = $_GET['partie'] ?? die(json_encode(['error' => 'Partie manquante']));

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$stmt = $pdo->prepare("SELECT etat, current_player FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$partie = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'etat' => $partie['etat'],
    'gagnant' => $partie['current_player']
]);
?>