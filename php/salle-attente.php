<?php
session_start();

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$partie_id = $_GET['partie'] ?? null;
if (!$partie_id) die("Aucune partie sélectionnée.");

$pseudo = $_SESSION['pseudo'] ?? null;
if (!$pseudo) {
    header("Location: join.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM joueurs WHERE pseudo = ? AND partie_id = ?");
$stmt->execute([$pseudo, $partie_id]);
if ($stmt->rowCount() == 0) {
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(numero), 0) + 1 FROM joueurs WHERE partie_id = ?");
    $stmt->execute([$partie_id]);
    $numero = $stmt->fetchColumn();

    $pdo->prepare("INSERT INTO joueurs (pseudo, partie_id, est_hote, numero) VALUES (?, ?, 0, ?)")
        ->execute([$pseudo, $partie_id, $numero]);
}

$stmt = $pdo->prepare("SELECT est_hote FROM joueurs WHERE pseudo = ? AND partie_id = ?");
$stmt->execute([$pseudo, $partie_id]);
$joueur = $stmt->fetch();
$est_hote = $joueur['est_hote'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salle d'attente</title>
    <style>
        body {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            text-align: center;
            padding: 40px;
        }

        .salle-box {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            width: 350px;
            margin: auto;
            box-shadow: 0 0 12px rgba(0,0,0,0.3);
        }

        .joueur {
            background-color: rgba(255,255,255,0.2);
            margin: 8px;
            padding: 10px;
            border-radius: 6px;
        }

        button {
            background-color: #00c9ff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            margin: 15px 5px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #009ec3;
        }

        .danger {
            background-color: #ff5e5e;
        }

        .danger:hover {
            background-color: #e04a4a;
        }

        h1 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="salle-box">
        <h1>Salle d'attente – Partie #<?= htmlspecialchars($partie_id) ?></h1>
        <div id="joueurs">Chargement...</div>
        <?php if ($est_hote): ?>
            <form action="demarrer_partie.php" method="post">
                <input type="hidden" name="partie_id" value="<?= $partie_id ?>">
                <button type="submit">Lancer la partie</button>
            </form>

            <form action="supprimer_salle.php" method="post" onsubmit="return confirm('Supprimer cette salle ?');">
                <input type="hidden" name="partie_id" value="<?= $partie_id ?>">
                <button type="submit" class="danger">Supprimer la salle</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        setInterval(() => {
            fetch("get_joueurs.php?partie=<?= $partie_id ?>")
                .then(res => res.json())
                .then(data => {
                    if (data.deleted) {
                        window.location.href = "menu.php";
                    } else if (data.etat === 'en_cours') {
                        window.location.href = `joueur.php?partie=<?= $partie_id ?>&joueur=${data.joueur_numero}`;
                    } else {
                        document.getElementById("joueurs").innerHTML =
                            data.joueurs.map(j => `<div class='joueur'>${j.pseudo}</div>`).join('');
                    }
                });
        }, 1500);

    </script>
</body>
</html>
