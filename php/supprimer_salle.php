<?php
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$partie_id = $_POST['partie_id'] ?? null;

if ($partie_id) {
    $pdo->prepare("DELETE FROM parties WHERE id = ?")->execute([$partie_id]);
    header("Location: menu.php");
}
