<?php
session_start();

if (!isset($_SESSION['pseudo']) || empty($_SESSION['pseudo'])) {
    header('Location: index.php');
    exit;
}

$pseudo = htmlspecialchars($_SESSION['pseudo']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Menu - PortalHole</title>
    <style>
        body {
            background: linear-gradient(135deg, #4b6cb7, #182848);
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.2);
            text-align: right;
            font-weight: bold;
        }

        .menu-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .menu-title {
            margin-bottom: 40px;
            font-size: 24px;
        }

        .menu-options {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 300px;
        }

        .menu-btn {
            background-color: #00c9ff;
            color: #fff;
            border: none;
            padding: 15px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        .menu-btn:hover {
            background-color: #009ec3;
        }
    </style>
</head>
<body>
    <header>
        Bonjour, <?php echo $pseudo; ?>
    </header>

    <div class="menu-container">
        <h1 class="menu-title">Menu Principal</h1>
        <div class="menu-options">
            <a href="host.php" class="menu-btn">Héberger une partie</a>
            <a href="join.php" class="menu-btn">Rejoindre une partie</a>
        </div>
    </div>
</body>
</html>