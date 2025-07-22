<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');

$partie_id = $_GET['partie'] ?? die("Partie manquante");
$joueur = $_GET['joueur'] ?? die("Joueur manquant");
$position = $_GET['position'] ?? die("Position manquante");

$stmt = $pdo->prepare("UPDATE joueurs SET position = ? WHERE partie_id = ? AND numero = ?");
$stmt->execute([$position, $partie_id, $joueur]);

echo json_encode(["status" => "success"]);