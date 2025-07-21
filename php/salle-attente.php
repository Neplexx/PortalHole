<?php

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$partie_id = $_GET['partie'] ?? null;

if (!$partie_id) {
  die("Aucune partie sélectionnée.");
}

if (isset($_GET['pseudo'])) {
  $pseudo = $_GET['pseudo'];

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM joueurs WHERE partie_id = ?");
  $stmt->execute([$partie_id]);
  $numero = $stmt->fetchColumn() + 1;

  $stmt = $pdo->prepare("INSERT INTO joueurs (pseudo, partie_id, est_hote, numero) VALUES (?, ?, 0, ?)");
  $stmt->execute([$pseudo, $partie_id, $numero]);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Salle d'attente</title>
  <style>
    body { font-family: Arial, sans-serif; text-align: center; padding: 40px; }
    #joueurs { margin-top: 20px; }
    .joueur { margin: 5px; font-weight: bold; }
  </style>
</head>
<body>
  <h1>Salle d'attente – Partie #<?= htmlspecialchars($partie_id) ?></h1>
  <div id="joueurs">Chargement...</div>

  <script>
    setInterval(() => {
      fetch("get_joueurs.php?partie=<?= $partie_id ?>")
        .then(res => res.json())
        .then(data => {
          document.getElementById("joueurs").innerHTML =
            data.map(j => `<div class='joueur'>${j.pseudo}</div>`).join('');
        });
    }, 1500);
  </script>
</body>
</html>
