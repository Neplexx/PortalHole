<?php
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'portalholedata';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$parties = $pdo->query("SELECT id FROM parties WHERE etat = 'attente'");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Rejoindre une partie</title>
  <style>
    body { font-family: Arial, sans-serif; text-align: center; padding: 40px; }
    .salle { margin: 20px auto; width: 300px; border: 1px solid #ccc; padding: 10px; }
  </style>
</head>
<body>
  <h1>Choisis une partie</h1>
  <?php foreach ($parties as $partie): ?>
    <div class="salle">
      <form action="salle-attente.php" method="get">
        <input type="hidden" name="partie" value="<?= $partie['id'] ?>">
        <input type="text" name="pseudo" placeholder="Ton pseudo" required>
        <button type="submit">Rejoindre</button>
      </form>
    </div>
  <?php endforeach; ?>
</body>
</html>
