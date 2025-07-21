<?php
session_start();
if (!isset($_SESSION['pseudo']) || empty($_SESSION['pseudo'])) {
    header('Location: index.php');
    exit;
}

$partie_id = $_GET['partie'] ?? null;
if (!$partie_id) die("Partie invalide");

// Simulation de la position actuelle (à remplacer par votre système de suivi)
$_SESSION['player_positions'] = $_SESSION['player_positions'] ?? [1, 1, 1, 1];
$current_player = 3; // Le joueur jaune (pawn-3)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Portal Game - Plateau</title>
    <style>
        body {
            background: #0f0c29;
            background: linear-gradient(to right, #0f0c29, #302b63, #24243e);
            perspective: 1000px;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
        }

        .game-container {
            width: 80vmin;
            height: 80vmin;
            margin: 5vh auto;
            position: relative;
            transform-style: preserve-3d;
            transform: rotateX(10deg) rotateZ(-5deg);
        }

        .game-board {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            grid-template-rows: repeat(10, 1fr);
            width: 100%;
            height: 100%;
            position: absolute;
            background: rgba(20, 20, 40, 0.8);
            border: 4px solid #6e45e2;
            box-shadow: 0 0 30px #6e45e2;
            border-radius: 5px;
        }

        .cell {
            position: relative;
            border: 1px solid rgba(110, 69, 226, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.5);
            font-size: 0.8em;
            font-weight: bold;
            transition: all 0.3s;
        }

        .cell:hover {
            background: rgba(110, 69, 226, 0.2);
        }

        /* Portails */
        .portal {
            position: absolute;
            width: 60%;
            height: 60%;
            top: -30%;
            border-radius: 50%;
            border: 2px solid;
            animation: portal-pulse 2s infinite;
            z-index: 10;
        }

        .portal-blue {
            border-color: #00b4db;
            box-shadow: 0 0 15px #00b4db, inset 0 0 8px #00b4db;
        }

        .portal-orange {
            border-color: #ff7e5f;
            box-shadow: 0 0 15px #ff7e5f, inset 0 0 8px #ff7e5f;
        }

        /* Trous noirs */
        .black-hole {
            position: absolute;
            width: 70%;
            height: 70%;
            border-radius: 50%;
            background: radial-gradient(circle, #434343 0%, #000000 70%);
            box-shadow: 0 0 20px #000;
            z-index: 5;
            animation: black-hole-spin 5s linear infinite;
        }

        /* Pions */
        .pawn {
            position: absolute;
            width: 60%;
            height: 60%;
            border-radius: 50%;
            z-index: 20;
            transition: all 0.5s ease-in-out;
            box-shadow: 0 0 10px 2px currentColor;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pawn::after {
            content: "";
            position: absolute;
            width: 50%;
            height: 50%;
            border-radius: 50%;
            background: currentColor;
            filter: brightness(1.5);
            opacity: 0.8;
        }

        .pawn-1 { background: #ff3366; color: #ff3366; }
        .pawn-2 { background: #00ccff; color: #00ccff; }
        .pawn-3 { background: #ffcc00; color: #ffcc00; } /* Pion jaune */
        .pawn-4 { background: #aa66ff; color: #aa66ff; }

        /* Dé */
        .dice-container {
            position: absolute;
            bottom: -80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
        }

        .dice {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            user-select: none;
        }

        .dice.rolling {
            animation: dice-roll 0.5s linear 3;
        }

        @keyframes dice-roll {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes portal-pulse {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        @keyframes black-hole-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pawn-glow {
            0% { box-shadow: 0 0 5px 2px currentColor; }
            50% { box-shadow: 0 0 15px 4px currentColor; }
            100% { box-shadow: 0 0 5px 2px currentColor; }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-board" id="gameBoard">
            <!-- Cases générées dynamiquement -->
            <?php 
            $portals = [4 => 25, 8 => 52, 36 => 77];
            $blackholes = [30 => 12, 75 => 45, 95 => 63];
            
            for ($i = 100; $i >= 1; $i--): 
                $row = 10 - floor(($i - 1) / 10);
                $col = ($row % 2 == 0) ? 10 - (($i - 1) % 10) : ($i - 1) % 10 + 1;
            ?>
                <div class="cell" style="grid-row: <?= $row ?>; grid-column: <?= $col ?>;">
                    <span><?= $i ?></span>
                    
                    <?php if (array_key_exists($i, $portals)): ?>
                        <div class="portal <?= $i % 2 ? 'portal-blue' : 'portal-orange' ?>"></div>
                    <?php endif; ?>
                    
                    <?php if (array_key_exists($i, $blackholes)): ?>
                        <div class="black-hole"></div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
            
            <!-- Pions joueurs -->
            <div class="pawn pawn-1" id="player1"></div>
            <div class="pawn pawn-2" id="player2"></div>
            <div class="pawn pawn-3" id="player3"></div> <!-- Pion jaune -->
            <div class="pawn pawn-4" id="player4"></div>
        </div>

        <!-- Bouton de dé -->
        <div class="dice-container">
            <div class="dice" id="dice">?</div>
        </div>
    </div>

    <script>
        // Variables du jeu
        let currentPosition = <?= $_SESSION['player_positions'][$current_player - 1] ?>;
        const portals = <?= json_encode($portals) ?>;
        const blackholes = <?= json_encode($blackholes) ?>;
        const playerId = 'player3'; // Pion jaune
        let isMoving = false;

        // Positionnement initial
        function positionPawns() {
            <?php for ($i = 1; $i <= 4; $i++): ?>
                positionPawn('player<?= $i ?>', <?= $_SESSION['player_positions'][$i - 1] ?>);
            <?php endfor; ?>
        }

        function positionPawn(pawnId, cellNumber) {
            const cell = document.querySelector(`.cell:nth-child(${101 - cellNumber})`);
            const pawn = document.getElementById(pawnId);
            if (!cell || !pawn) return;

            const rect = cell.getBoundingClientRect();
            const boardRect = document.getElementById('gameBoard').getBoundingClientRect();
            
            pawn.style.left = `${rect.left - boardRect.left + rect.width*0.2}px`;
            pawn.style.top = `${rect.top - boardRect.top + rect.height*0.2}px`;
            pawn.style.width = `${rect.width*0.6}px`;
            pawn.style.height = `${rect.height*0.6}px`;
            pawn.style.animation = 'pawn-glow 2s infinite';
        }

        // Système de dé
        document.getElementById('dice').addEventListener('click', rollDice);

        function rollDice() {
            if (isMoving) return;
            
            const dice = document.getElementById('dice');
            dice.textContent = '...';
            dice.classList.add('rolling');
            
            setTimeout(() => {
                const roll = Math.floor(Math.random() * 6) + 1;
                dice.textContent = roll;
                dice.classList.remove('rolling');
                
                movePlayer(roll);
            }, 1500);
        }

        // Déplacement du joueur
        function movePlayer(steps) {
            isMoving = true;
            let newPosition = currentPosition + steps;
            
            // Animation pas à pas
            let step = 0;
            const moveInterval = setInterval(() => {
                if (step < steps) {
                    step++;
                    positionPawn(playerId, currentPosition + step);
                } else {
                    clearInterval(moveInterval);
                    
                    // Vérifier les portails/trous noirs
                    if (portals[newPosition]) {
                        newPosition = portals[newPosition];
                        setTimeout(() => {
                            positionPawn(playerId, newPosition);
                            checkGameStatus();
                        }, 500);
                    } else if (Object.values(blackholes).includes(newPosition)) {
                        const entry = Object.entries(blackholes).find(([_, end]) => end === newPosition);
                        newPosition = parseInt(entry[0]);
                        setTimeout(() => {
                            positionPawn(playerId, newPosition);
                            checkGameStatus();
                        }, 500);
                    } else {
                        checkGameStatus();
                    }
                    
                    currentPosition = newPosition;
                    isMoving = false;
                    
                    // Mettre à jour la position en PHP (simulation)
                    fetch('update_position.php?player=3&position=' + newPosition)
                        .catch(err => console.error('Erreur de mise à jour:', err));
                }
            }, 300);
        }

        function checkGameStatus() {
            if (currentPosition >= 100) {
                setTimeout(() => {
                    alert('Félicitations ! Le joueur jaune a gagné !');
                }, 500);
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', positionPawns);
    </script>
</body>
</html>