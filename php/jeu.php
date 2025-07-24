<?php
session_start();
if (!isset($_SESSION['pseudo']) || empty($_SESSION['pseudo'])) {
    header('Location: index.php');
    exit;
}

$partie_id = $_GET['partie'] ?? null;
if (!$partie_id) die("Partie invalide");

// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les positions depuis la base de données
$stmt = $pdo->prepare("SELECT numero, position FROM joueurs WHERE partie_id = ? ORDER BY numero");
$stmt->execute([$partie_id]);
$joueurs_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le nombre total de joueurs dans la partie
$stmt = $pdo->prepare("SELECT COUNT(*) FROM joueurs WHERE partie_id = ?");
$stmt->execute([$partie_id]);
$nombre_joueurs = $stmt->fetchColumn();

// Préparer les positions pour le JavaScript
$positions = [];
foreach ($joueurs_data as $joueur) {
    $positions[$joueur['numero']] = $joueur['position'];
}

// Déterminer le joueur courant
$stmt = $pdo->prepare("SELECT current_player FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$current_player = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PortalHole - Plateau</title>
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
        .pawn-3 { background: #ffcc00; color: #ffcc00; }
        .pawn-4 { background: #aa66ff; color: #aa66ff; }
        .pawn-5 { background: #00ff99; color: #00ff99; }
        .pawn-6 { background: #ff6600; color: #ff6600; }
        .pawn-7 { background: #66ff33; color: #66ff33; }
        .pawn-8 { background: #ff0099; color: #ff0099; }

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
            <?php 
            $portals = [];
            $blackholes = [];
            
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
            
            <?php for ($i = 1; $i <= $nombre_joueurs; $i++): ?>
                <div class="pawn pawn-<?= $i ?>" id="player<?= $i ?>"></div>
            <?php endfor; ?>
        </div>
    </div>

    <script>
        // Configuration de base
        const partieId = <?= $partie_id ?>;
        const currentPlayer = <?= $current_player ?>;
        const playerId = 'player' + currentPlayer;
        let playerPositions = <?= json_encode($positions) ?>;
        let isMoving = false;
        const nombreJoueurs = <?= $nombre_joueurs ?>;

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            positionPawns();
            setInterval(checkTurn, 2000);
        });

        // Positionnement des pions
        function positionPawns() {
            for (let i = 1; i <= nombreJoueurs; i++) {
                positionPawn(`player${i}`, playerPositions[i] || 1);
            }
        }

        function positionPawn(pawnId, cellNumber) {
            const pawn = document.getElementById(pawnId);
            if (!pawn) return;

            // zigzag de la grille
            const row = 10 - Math.floor((cellNumber - 1) / 10);
            const isEvenRow = row % 2 === 0;
            const col = isEvenRow ? 10 - ((cellNumber - 1) % 10) : ((cellNumber - 1) % 10) + 1;
            // centre case
            const cellSize = 10;
            const topPercent = (row - 1 + 0.5) * cellSize;
            const leftPercent = (col - 1 + 0.5) * cellSize;

            // Ajustement pour centrer le pion
            pawn.style.top = `calc(${topPercent}% - 3%)`;
            pawn.style.left = `calc(${leftPercent}% - 3%)`;
            pawn.style.width = `6%`;
            pawn.style.height = `6%`;
            pawn.style.animation = 'none';
        }

        // Vérification du tour et des positions
        function checkTurn() {
            fetch(`get_current_player.php?partie=${partieId}`)
                .then(res => res.json())
                .then(data => {
                    fetchPlayerPositions();
                });
        }

        function fetchPlayerPositions() {
            fetch(`get_positions.php?partie=${partieId}`)
                .then(res => res.json())
                .then(data => {
                    for (let i = 1; i <= nombreJoueurs; i++) {
                        if (playerPositions[i] !== data[i]) {
                            animateMovement(`player${i}`, playerPositions[i], data[i]);
                            playerPositions[i] = data[i];
                        }
                    }
                });
        }

        // Animation des mouvements
        function animateMovement(pawnId, from, to) {
            if (isMoving) return;
            isMoving = true;
            
            const steps = Math.abs(to - from);
            const direction = to > from ? 1 : -1;
            let currentStep = 0;
            
            const interval = setInterval(() => {
                if (currentStep < steps) {
                    positionPawn(pawnId, from + (direction * currentStep));
                    currentStep++;
                } else {
                    clearInterval(interval);
                    positionPawn(pawnId, to);
                    checkSpecialCells(pawnId, to);
                    isMoving = false;
                }
            }, 300);
        }

        function checkSpecialCells(pawnId, position) {
            const portals = <?= json_encode($portals) ?>;
            const blackholes = <?= json_encode($blackholes) ?>;
            
            if (portals[position]) {
                setTimeout(() => {
                    animateMovement(pawnId, position, portals[position]);
                }, 500);
            } else if (Object.values(blackholes).includes(position)) {
                const entry = Object.entries(blackholes).find(([_, end]) => end === position);
                setTimeout(() => {
                    animateMovement(pawnId, position, parseInt(entry[0]));
                }, 500);
            }
        }
    </script>
</body>
</html>