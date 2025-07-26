<?php
session_start();
if (!isset($_SESSION['pseudo'])) {
    header('Location: ../index.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$partie_id = $_GET['partie'] ?? null;

if (!$partie_id) die("Partie invalide");

// Récupérer les infos de la partie
$stmt = $pdo->prepare("SELECT current_player FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$gagnant_num = $stmt->fetchColumn();

// Récupérer le pseudo du gagnant
$stmt = $pdo->prepare("SELECT pseudo FROM joueurs WHERE partie_id = ? AND numero = ?");
$stmt->execute([$partie_id, $gagnant_num]);
$gagnant = $stmt->fetchColumn();

// Récupérer tous les joueurs
$stmt = $pdo->prepare("SELECT numero, pseudo FROM joueurs WHERE partie_id = ? ORDER BY numero");
$stmt->execute([$partie_id]);
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Victoire !</title>
    <style>
        body {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            color: white;
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding: 50px;
        }
        .trophy {
            font-size: 100px;
            margin: 20px;
        }
        .joueur {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            display: inline-block;
        }
        .gagnant {
            background: gold;
            color: black;
            font-weight: bold;
            transform: scale(1.1);
        }
        button {
            background: #00c9ff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 30px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="trophy">🏆</div>
    <h1>Partie terminée !</h1>
    <h2>Le gagnant est <?= htmlspecialchars($gagnant) ?></h2>
    
    <h3>Classement :</h3>
    <?php foreach ($joueurs as $joueur): ?>
        <div class="joueur <?= $joueur['numero'] == $gagnant_num ? 'gagnant' : '' ?>">
            Joueur <?= $joueur['numero'] ?>: <?= htmlspecialchars($joueur['pseudo']) ?>
        </div>
    <?php endforeach; ?>
    
    <div>
        <button onclick="window.location.href='menu.php'">Retour au menu</button>
    </div>
</body>
</html>