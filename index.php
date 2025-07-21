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
  <meta charset="UTF-8" />
  <title>Bienvenue - Jeu multi joueurs</title>
  <style>
    body {
      background-color: #003366; /* bleu foncé */
      color: #ffcc00; /* jaune */
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      background-color: #0059b3; /* bleu moyen */
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(255, 204, 0, 0.7);
      text-align: center;
      width: 320px;
    }
    h1 {
      margin-bottom: 25px;
      font-weight: bold;
    }
    input[type="text"] {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      margin-bottom: 20px;
      outline: none;
    }
    button {
      background-color: #ffcc00;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      font-size: 18px;
      font-weight: bold;
      color: #003366;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #e6b800;
    }
    small {
      display: block;
      margin-top: 15px;
      color: #cfcf85;
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Bienvenue</h1>
    <form method="POST" action="">
      <input type="text" name="pseudo" placeholder="Entrez votre nom" required maxlength="20" />
      <button type="submit">Continuer</button>
    </form>
    <small>Jouez jusqu'à 8 joueurs</small>
  </div>

</body>
</html>
