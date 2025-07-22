<?php
session_start();
if (!isset($_SESSION['pseudo']) || empty($_SESSION['pseudo'])) {
    header('Location: ../index.php');
    exit;
}

$partie_id = $_GET['partie'] ?? null;
$numero = $_GET['joueur'] ?? null;

if (!$partie_id || !$numero) {
    die("Paramètres manquants.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Joueur <?= htmlspecialchars($numero) ?> - PortalHole</title>
    <style>
        body {
            background: linear-gradient(to right, #0f0c29, #302b63, #24243e);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .box {
            background-color: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 30px 40px;
            box-shadow: 0 0 20px rgba(255,255,255,0.1);
            text-align: center;
            width: 300px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .dice {
            font-size: 60px;
            margin: 30px 0;
            transition: transform 0.3s ease;
        }

        .rolling {
            animation: roll 0.8s ease-in-out;
        }

        @keyframes roll {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }

        button {
            padding: 12px 20px;
            font-size: 18px;
            color: #fff;
            background-color: #00c9ff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #009ec3;
        }

        .waiting {
            font-size: 16px;
            margin-top: 15px;
            opacity: 0.8;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Joueur #<?= htmlspecialchars($numero) ?></h1>

    <div id="dice" class="dice">🎲</div>

    <button id="rollBtn" style="display:none;">Lancer le dé</button>
    <div class="waiting" id="waitingMsg">En attente de votre tour...</div>
</div>

<script>
    const partieId = <?= $partie_id ?>;
    const joueurNumero = <?= $numero ?>;
    const rollBtn = document.getElementById('rollBtn');
    const dice = document.getElementById('dice');
    const waitingMsg = document.getElementById('waitingMsg');

    // Initialisation
    checkTurn();
    setInterval(checkTurn, 2000);

    // Gestion du tour
    function checkTurn() {
        fetch(`get_current_player.php?partie=${partieId}`)
            .then(res => res.json())
            .then(data => {
                if (data.current == joueurNumero) {
                    enableRollButton();
                } else {
                    disableRollButton();
                }
            })
            .catch(err => console.error("Erreur:", err));
    }

    function enableRollButton() {
        rollBtn.style.display = "inline-block";
        waitingMsg.style.display = "none";
        rollBtn.onclick = handleRoll;
        rollBtn.disabled = false;
    }

    function disableRollButton() {
        rollBtn.style.display = "none";
        waitingMsg.style.display = "block";
        rollBtn.onclick = null;
    }

    // Gestion du lancer de dé
    function handleRoll() {
        rollBtn.disabled = true;
        dice.textContent = "🎲";
        dice.classList.add("rolling");

        fetch(`joueur_lance.php?partie=${partieId}&joueur=${joueurNumero}`)
            .then(res => res.json())
            .then(data => {
                setTimeout(() => {
                    dice.classList.remove("rolling");
                    dice.textContent = data.de ?? "?";
                    
                    if (data.victoire) {
                        setTimeout(() => {
                            alert("Félicitations ! Vous avez gagné !");
                        }, 500);
                    }
                    
                    disableRollButton();
                }, 1000);
            })
            .catch(err => {
                console.error("Erreur:", err);
                rollBtn.disabled = false;
            });
    }

    // Vibration pour notification (optionnel)
    function notifyPlayer() {
        if ("vibrate" in navigator) {
            navigator.vibrate(200);
        }
    }
</script>

</body>
</html>
