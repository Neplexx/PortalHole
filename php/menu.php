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
  <meta charset="UTF-8" />
  <title>Menu - Jeu multi joueurs</title>
  <style>
    body {
      background-color: #003366; /* bleu foncé */
      color: #ffcc00; /* jaune */
      font-family: Arial, sans-serif;
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      display: flex;
      justify-content: flex-end;
      padding: 15px 30px;
      background-color: #0059b3; /* bleu moyen */
      font-weight: bold;
      font-size: 1.1em;
    }
    main {
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 50px;
    }
    .btn {
      background-color: #ffcc00;
      color: #003366;
      padding: 25px 50px;
      font-size: 1.5em;
      font-weight: bold;
      border: none;
      border-radius: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      box-shadow: 0 4px 8px rgba(255, 204, 0, 0.5);
      text-decoration: none;
      text-align: center;
      display: inline-block;
      user-select: none;
    }
    .btn:hover {
      background-color: #e6b800;
    }
  </style>
</head>
<body>

<header>
  Bonjour, <?php echo $pseudo; ?>
</header>

<main>
  <a href="host.php" class="btn" role="button">Héberger une partie</a>
  <a href="join.php" class="btn" role="button">Rejoindre une partie</a>
</main>

</body>
</html>
