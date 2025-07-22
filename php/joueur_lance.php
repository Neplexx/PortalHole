<?php
session_start();

$partie_id = $_GET['partie'] ?? 0;
$joueur = $_GET['joueur'] ?? 0;

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$de = random_int(1, 6);

$stmt = $pdo->prepare("SELECT position FROM joueurs WHERE partie_id = ? AND numero = ?");
$stmt->execute([$partie_id, $joueur]);
$position = $stmt->fetchColumn();

$nouvelle_position = min(100, $position + $de);

$stmt = $pdo->prepare("UPDATE joueurs SET position = ? WHERE partie_id = ? AND numero = ?");
$stmt->execute([$nouvelle_position, $partie_id, $joueur]);

$stmt = $pdo->prepare("SELECT numero FROM joueurs 
                      WHERE partie_id = ? AND est_hote = 0 
                      ORDER BY numero ASC");
$stmt->execute([$partie_id]);
$joueurs = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($joueurs)) {
    die(json_encode(["error" => "Aucun joueur trouvé"]));
}

$current_index = array_search($joueur, $joueurs);
$next_index = ($current_index + 1) % count($joueurs);
$suivant = $joueurs[$next_index];

$pdo->prepare("UPDATE parties SET current_player = ?, dice_result = ? WHERE id = ?")
    ->execute([$suivant, $de, $partie_id]);

$victoire = ($nouvelle_position >= 100);

header('Content-Type: application/json');
echo json_encode([
    "position" => $nouvelle_position,
    "de" => $de,
    "suivant" => $suivant,
    "victoire" => $victoire,
]);