<?php
session_start();
if (!isset($_SESSION['pseudo']) || empty($_SESSION['pseudo'])) {
    header('Location: index.php');
    exit;
}

$partie_id = $_GET['partie'] ?? null;
if (!$partie_id) die("Partie invalide");

$pdo = new PDO("mysql:host=localhost;dbname=portalholedata", 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT numero, position FROM joueurs WHERE partie_id = ? ORDER BY numero");
$stmt->execute([$partie_id]);
$joueurs_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM joueurs WHERE partie_id = ?");
$stmt->execute([$partie_id]);
$nombre_joueurs = $stmt->fetchColumn();

$positions = [];
foreach ($joueurs_data as $joueur) {
    $positions[$joueur['numero']] = $joueur['position'];
}

$stmt = $pdo->prepare("SELECT current_player FROM parties WHERE id = ?");
$stmt->execute([$partie_id]);
$current_player = $stmt->fetchColumn();

// Portails (téléportation vers le haut uniquement)
$portals_array = [
    3 => 22,
    14 => 67,
    28 => 48,
    19 => 37, 
    59 => 87,
    36 => 63
];

$portalColors = [
    '3-22' => 'portal-blue',
    '14-67' => 'portal-pink',
    '28-48' => 'portal-green',
    '37-19' => 'portal-orange',
    '59-87' => 'portal-cyan',
    '63-36' => 'portal-red'
];

// Trous noirs (chute vers le bas uniquement)
$blackholes_array = [
    11 => 4,
    24 => 8,
    45 => 32,
    53 => 42,
    72 => 56,
    94 => 77
];

$blackholeColors = [
    '4-11' => 'bh-rouge',
    '8-24' => 'bh-bleu',
    '32-45' => 'bh-violet',
    '42-53' => 'bh-vert',
    '56-72' => 'bh-gris',
    '77-94' => 'bh-ciel'
];
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
            border-radius: 50%;
            border: 2px solid;
            animation: portal-pulse 2s infinite;
            z-index: 10;
        }

        .portal-blue   { border-color: #00d4ff; box-shadow: 0 0 15px #00d4ff; }
        .portal-pink   { border-color: #ff33cc; box-shadow: 0 0 15px #ff33cc; }
        .portal-green  { border-color: #00ff99; box-shadow: 0 0 15px #00ff99; }
        .portal-orange { border-color: #ffaa00; box-shadow: 0 0 15px #ffaa00; }
        .portal-cyan   { border-color: #66ffff; box-shadow: 0 0 15px #66ffff; }
        .portal-red    { border-color: #ff4444; box-shadow: 0 0 15px #ff4444; }

        /* Trous noirs */
        .black-hole {
            position: absolute;
            width: 70%;
            height: 70%;
            border-radius: 50%;
            box-shadow: 0 0 20px #000;
            z-index: 5;
            animation: black-hole-spin 5s linear infinite;
        }

        .bh-rouge { background: radial-gradient(circle, #270909ff 0%, #000 85%); }
        .bh-violet { background: radial-gradient(circle, #20133bff 0%, #000 85%); }
        .bh-bleu { background: radial-gradient(circle, #12133eff 0%, #000 85%); }
        .bh-vert { background: radial-gradient(circle, #0a2513ff 0%, #000 85%); }
        .bh-ciel { background: radial-gradient(circle, #292a05ff 0%, #000 85%); }
        .bh-gris { background: radial-gradient(circle, #111111ff 0%, #000 85%); }

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
        @keyframes portal-tilt {
            0%, 100% { transform: rotateX(60deg) rotateZ(45deg) scale(1); }
            50% { transform: rotateX(60deg) rotateZ(45deg) scale(1.1); }
        }

        @keyframes black-hole-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

                    <?php 
                    foreach ($portalColors as $duo => $class) {
                        [$a, $b] = explode('-', $duo);
                        if ($i == (int)$a || $i == (int)$b) {
                            echo "<div class='portal {$class}'></div>";
                        }
                    }

                    foreach ($blackholeColors as $duo => $class) {
                        [$a, $b] = explode('-', $duo);
                        if ($i == (int)$a || $i == (int)$b) {
                            echo "<div class='black-hole {$class}'></div>";
                        }
                    }
                    ?>
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
                    console.log(`Appel checkSpecialCells(${pawnId}, ${to}, ${from})`);
                    checkSpecialCells(pawnId, to, from);
                    isMoving = false;
                }
            }, 300);
        }

        function checkSpecialCells(pawnId, position, fromPosition) {
            const portals = <?php echo json_encode($portals_array ?? []); ?>;
            const blackholes = <?php echo json_encode($blackholes_array ?? []); ?>;

            if (portals[position] !== undefined) {
                const newPos = portals[position];
                playerPositions[pawnId.replace('player', '')] = newPos;
                updatePlayerPosition(pawnId.replace('player', ''), newPos);
                animatePortalEffect(pawnId, newPos);
                console.groupEnd();
                return true;
            }
            
            if (blackholes[position] !== undefined) {
                const newPos = blackholes[position];
                playerPositions[pawnId.replace('player', '')] = newPos;
                updatePlayerPosition(pawnId.replace('player', ''), newPos);
                animateBlackHoleEffect(pawnId, newPos);
                console.groupEnd();
                return true;
            }
        }


        // Animation spécifique pour les portails
        function animatePortalEffect(pawnId, newPos, callback) {
            const pawn = document.getElementById(pawnId);
            pawn.style.transition = "all 0.5s ease-out";
            pawn.style.transform = "scale(0.1) rotate(360deg)";
            pawn.style.opacity = "0";
            
            setTimeout(() => {
                positionPawn(pawnId, newPos);
                pawn.style.transform = "scale(1.2) rotate(0deg)";
                pawn.style.opacity = "1";
                pawn.style.boxShadow = "0 0 15px 5px cyan";
                setTimeout(() => {
                    pawn.style.boxShadow = "";
                    if (callback) callback();
                }, 1000);
            }, 500);
        }

        function animateBlackHoleEffect(pawnId, newPos, callback) {
            const pawn = document.getElementById(pawnId);
            pawn.style.transition = "all 0.7s cubic-bezier(0.68, -0.6, 0.32, 1.6)";
            pawn.style.transform = "scale(0.1) rotate(720deg)";
            pawn.style.opacity = "0.5";

            setTimeout(() => {
                positionPawn(pawnId, newPos);
                pawn.style.transition = "all 0.5s ease-out";
                pawn.style.transform = "scale(1.2)";
                pawn.style.opacity = "1";
                pawn.style.boxShadow = "0 0 10px 2px rgba(255,0,0,0.7)";
                setTimeout(() => {
                    pawn.style.boxShadow = "";
                    if (callback) callback();
                }, 800);
            }, 700);
        }
        function updatePlayerPosition(playerNum, newPosition) {
            fetch('update_position.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `partie=${partieId}&joueur=${playerNum}&position=${newPosition}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    playerPositions[playerNum] = newPosition;
                }
            });
        }

    </script>
</body>
</html>