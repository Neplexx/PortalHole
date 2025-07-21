<?php
header('Content-Type: application/json');

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$partie_id = $_GET['partie'] ?? null;
if (!$partie_id) {
    echo json_encode([]);
    exit;
}

// si salle suppr
$stmt = $pdo->prepare("SELECT COUNT(*) FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
if ($stmt->fetchColumn() == 0) {
    echo json_encode(['deleted' => true]);
    exit;
}

$stmt = $pdo->prepare("SELECT pseudo FROM joueurs WHERE partie_id = ?");
$stmt->execute([$partie_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>