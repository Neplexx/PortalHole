<?php
session_start();

if (isset($_POST['pseudo']) && !empty(trim($_POST['pseudo']))) {
    $_SESSION['pseudo'] = htmlspecialchars(trim($_POST['pseudo']));
    header('Location: php/menu.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue - PortalHole</title>
    <style>
        body {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .welcome-box {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 30px;
            width: 350px;
            box-shadow: 0 0 12px rgba(0,0,0,0.3);
            text-align: center;
        }

        h1 {
            margin-bottom: 25px;
            font-size: 24px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: none;
            border-radius: 6px;
            background-color: rgba(255,255,255,0.2);
            color: #fff;
            font-size: 16px;
        }

        input[type="text"]::placeholder {
            color: rgba(255,255,255,0.7);
        }

        button {
            background-color: #00c9ff;
            color: #fff;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #009ec3;
        }

        .info {
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="welcome-box">
        <h1>Bienvenue sur PortalHole</h1>
        <form method="POST" action="">
            <input type="text" name="pseudo" placeholder="Entrez votre pseudo" required maxlength="20" />
            <button type="submit">Continuer</button>
        </form>
        <div class="info">Jouez jusqu'à 8 joueurs</div>
    </div>
</body>
</html>