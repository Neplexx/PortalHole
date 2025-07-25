<?php
$partie_id = $_GET['partie'] ?? die(json_encode([]));

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$stmt = $pdo->prepare("SELECT numero, LEAST(100, GREATEST(1, position)) as position FROM joueurs WHERE partie_id = ?");
$stmt->execute([$partie_id]);

$positions = [];
while ($row = $stmt->fetch()) {
    $positions[$row['numero']] = (int)$row['position'];
}
if (empty($positions)) {
    die(json_encode([]));
}

header('Content-Type: application/json');
echo json_encode($positions);
?>