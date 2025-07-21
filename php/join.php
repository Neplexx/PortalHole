<?php
session_start();

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pseudo = $_SESSION['pseudo'] ?? null;
if (!$pseudo) {
    header("Location: ../index.php");
    exit;
}

// Récupérer les parties en attente
$parties = $pdo->query("SELECT id FROM parties WHERE etat = 'attente' ORDER BY created_at ASC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rejoindre une partie</title>
    <style>
        body {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            text-align: center;
            padding: 40px;
        }

        .lobby {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            width: 300px;
            margin: 20px auto;
            box-shadow: 0 0 12px rgba(0,0,0,0.3);
        }

        button {
            background-color: #00c9ff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #009ec3;
        }

        h1 {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <h1>Rejoindre une partie existante</h1>

    <?php if (empty($parties)): ?>
        <p>Aucune partie disponible pour le moment.</p>
    <?php else: ?>
        <?php foreach ($parties as $partie): ?>
            <div class="lobby">
                <p>Partie #<?= htmlspecialchars($partie['id']) ?></p>
                <form action="salle-attente.php" method="get">
                    <input type="hidden" name="partie" value="<?= $partie['id'] ?>">
                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($pseudo) ?>">
                    <button type="submit">Rejoindre</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
