<?php
session_start();

$partie_id = $_GET['partie'] ?? 0;
$joueur = $_GET['joueur'] ?? 0;

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT etat FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$partie = $stmt->fetch();

if (!$partie || $partie['etat'] !== 'en_cours') {
    die(json_encode(["error" => "Partie non disponible"]));
}

$stmt = $pdo->prepare("SELECT position FROM joueurs WHERE partie_id = ? AND numero = ?");
$stmt->execute([$partie_id, $joueur]);
$position = $stmt->fetchColumn();

if ($position >= 100) {
    $pdo->prepare("UPDATE parties SET etat = 'terminee', current_player = ? WHERE id = ?")
        ->execute([$joueur, $partie_id]);
    
    echo json_encode([
        "position" => 100,
        "de" => 0,
        "victoire" => true,
        "redirect" => "victoire.php?partie=".$partie_id."&gagnant=".$joueur
    ]);
    exit;
}

$de = random_int(1, 6);
$nouvelle_position = $position + $de;

$stmt = $pdo->prepare("UPDATE joueurs SET position = ? WHERE partie_id = ? AND numero = ?");
$stmt->execute([$nouvelle_position, $partie_id, $joueur]);

if ($nouvelle_position >= 100) {
    $pdo->prepare("UPDATE parties SET etat = 'terminee', current_player = ? WHERE id = ?")
        ->execute([$joueur, $partie_id]);
    
    echo json_encode([
        "position" => $nouvelle_position,
        "de" => $de,
        "victoire" => true,
        "redirect" => "victoire.php?partie=".$partie_id."&gagnant=".$joueur
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT numero FROM joueurs 
                      WHERE partie_id = ? AND est_hote = 0 AND position < 100
                      ORDER BY numero ASC");
$stmt->execute([$partie_id]);
$joueurs_actifs = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($joueurs_actifs)) {
    $pdo->prepare("UPDATE parties SET etat = 'terminee', current_player = 0 WHERE id = ?")
        ->execute([$partie_id]);
    $suivant = 0;
} else {
    $current_index = array_search($joueur, $joueurs_actifs);
    
    if ($current_index === false) {
        $suivant = $joueurs_actifs[0];
    } else {
        $next_index = ($current_index + 1) % count($joueurs_actifs);
        $suivant = $joueurs_actifs[$next_index];
    }
}

$pdo->prepare("UPDATE parties SET current_player = ?, dice_result = ? WHERE id = ?")
    ->execute([$suivant, $de, $partie_id]);

header('Content-Type: application/json');
echo json_encode([
    "position" => $nouvelle_position,
    "de" => $de,
    "suivant" => $suivant,
    "victoire" => false
]);
?>