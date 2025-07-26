<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');

$partie_id = $_POST['partie'] ?? die(json_encode(["status" => "error"]));
$joueur = $_POST['joueur'] ?? die(json_encode(["status" => "error"]));
$position = $_POST['position'] ?? die(json_encode(["status" => "error"]));

$position = max(1, min(100, (int)$position));

$stmt = $pdo->prepare("UPDATE joueurs SET position = ? WHERE partie_id = ? AND numero = ?");
$stmt->execute([$position, $partie_id, $joueur]);

echo json_encode([
    "status" => "success",
    "confirmed_position" => $position
]);