<?php
session_start();

$partie_id = $_GET['partie'] ?? null;
if (!$partie_id) exit(json_encode(['error' => 'partie manquante']));

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT etat FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$partie = $stmt->fetch();

if (!$partie) {
    echo json_encode(['deleted' => true]);
    exit;
}

$stmt = $pdo->prepare("SELECT pseudo FROM joueurs WHERE partie_id = ? AND est_hote = 0");
$stmt->execute([$partie_id]);
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$numero = null;
if (isset($_SESSION['pseudo'])) {
    $stmt = $pdo->prepare("SELECT numero FROM joueurs WHERE pseudo = ? AND partie_id = ?");
    $stmt->execute([$_SESSION['pseudo'], $partie_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $numero = $result ? $result['numero'] : null;
}

echo json_encode([
    'etat' => $partie['etat'],
    'joueur_numero' => $numero,
    'joueurs' => $joueurs
]);
