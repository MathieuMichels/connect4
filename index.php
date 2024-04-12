<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puissance 4</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/board.css">
    <link rel="stylesheet" href="css/infos.css">
    <link rel="stylesheet" href="css/help.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
<header>
    <div class="header-wrap">
        <h1>Puissance 4</h1>
        <h2 id="game-name"></h2>
        <div class="actions">
            <button id="new-game">Nouvelle partie</button>
            <button id="reset-scores" onclick="resetGame()">Réinitialiser les scores</button>
            <!-- slider de difficulté entre easy, medium, hard (nombre allant de 1 à 7) -->
            <label for="difficulty">Difficulté</label><input type="range" id="difficulty" min="1" max="7" value="5">
            <button id="help" onclick="switchHelp()">Aide</button>
            <!-- list de langues (en, fr) -->
            <label for="lang">Langue</label><select id="lang">
                <option value="fr">Français</option>
                <option value="en">English</option>
            </select>
        </div>
    </div>
</header>
<div id="help-window" style="display:none;">
    <div id="help-window-header">
        <h1>Règles du jeu</h1>
        <button id="close-help" onclick="switchHelp()">Fermer</button>
    </div>
    <div class="help-content">
    <p>Le Puissance 4 est un jeu de stratégie dans lequel deux joueurs s'affrontent pour aligner quatre jetons de leur
        couleur dans une grille verticale, horizontale ou diagonale.</p>

    <h2>Comment jouer</h2>
    <p>Le jeu se joue sur une grille de 6 lignes et 7 colonnes. Les joueurs prennent tour à tour à déposer un jeton de
        leur couleur dans l'une des colonnes. Le jeton tombe ensuite en bas de la colonne. Le premier joueur à aligner
        quatre jetons de sa couleur gagne la partie.</p>

    <h2>Nouvelle partie</h2>
    <p>Vous pouvez démarrer une nouvelle partie en cliquant sur le bouton <button class="new-game" onclick="">Nouvelle Partie</button>
        en haut à droite de la
        fenêtre.</p>

    <h2>Variantes</h2>
    <p>Il est possible de jouer à un <i>puissance 5</i> ou <i>puissance 6</i> en suivant le même principe, simplement avec un plateau plus grand.</p>

    <h2>Réinitialiser les scores</h2>
    <p>Si vous souhaitez réinitialiser les scores des parties précédentes, cliquez sur le bouton <button class="reset-scores" onclick="resetGame()">Réinitialiser les Scores</button> en haut à droite.</p>

    <h2>Changer de langue</h2>
    <p>Vous pouvez changer la langue de l'interface en sélectionnant une langue dans le menu déroulant <select class="language-select">
            <option value="fr">Français</option>
            <option value="en">English</option>
        </select>.</p>

    <h2>Stratégies</h2>
    <p>Pour améliorer vos compétences au Puissance 4, voici quelques stratégies utiles à garder à l'esprit :</p>
    <ul>
        <li><strong>Bloquer l'adversaire :</strong> Essayez de bloquer les alignements potentiels de votre adversaire tout en construisant vos propres alignements.</li>
        <li><strong>Occuper le centre :</strong> Contrôler le centre du plateau peut vous donner un avantage stratégique en vous permettant de bloquer plus facilement les alignements de l'adversaire.</li>
        <li><strong>Anticiper les coups :</strong> Essayez d'anticiper les mouvements de votre adversaire et de prévoir vos propres alignements.</li>
    </ul>

    <h2>Conseils supplémentaires</h2>
    <p>En plus des stratégies mentionnées, voici quelques conseils supplémentaires pour améliorer votre jeu :</p>
    <ul>
        <li><strong>Restez flexible :</strong> Adaptez votre stratégie en fonction des mouvements de votre adversaire et de l'évolution du plateau.</li>
        <li><strong>Pratiquez régulièrement :</strong> Plus vous jouez, plus vous comprendrez les schémas et les tactiques du jeu.</li>
        <li><strong>Apprenez des autres :</strong> Observer les jeux de joueurs expérimentés peut vous aider à découvrir de nouvelles stratégies et techniques.</li>
    </ul>
    </div>
</div>

<main>
    <table id="game-board"></table>
    <div id="game-infos">
        <!--
        | Joueur 1                            | Joueur 2                            | Joueur actuel | Nombre de coups joués |
        | Nom | Victoires | Probabilité de gagner | Nom | Victoires | Probabilité de gagner |  Nom          |                       |
        -->
        <div class="player" data-player="1">
            <h2 data-player="Player 1">Joueur 1</h2>
            <div class="player-info">
                <span class="player-wins">0</span>
                <span class="player-defeats">0</span>
                <span class="player-nulls">0</span>
                <span class="player-prob">0%</span>
            </div>
        </div>
        <div class="player" data-player="2">
            <h2 data-player="Player 2">Joueur 2</h2>
            <div class="player-info">
                <span class="player-wins">0</span>
                <span class="player-defeats">0</span>
                <span class="player-nulls">0</span>
                <span class="player-prob">0%</span>
            </div>
        </div>
</main>
<script src="js/connect4.js"></script>
<!--
<script src="js/minimax.js"></script>
-->
<script>
    // give board, ROWS, COLS, MAX_DEPTH to php/minimax.php in jquery and console.log response

    function send() {
        MAX_DEPTH = document.getElementById('difficulty').value;
        let board = [];
        for (let i = 0; i < ROWS; i++) {
            board.push([]);
            for (let j = 0; j < COLS; j++) {
                if (cells[i][j].classList.contains('player1')) {
                    board[i].push(1);
                } else if (cells[i][j].classList.contains('player2')) {
                    board[i].push(2);
                } else {
                    board[i].push(0);
                }
            }
        }
        console.log(board);
        /*
        rotate the board 180 degrees
         */
        //board = board.map(row => row.reverse());
        board = board.reverse();
        console.log(board);
        $.ajax({
            url: 'php/minimax.php',
            type: 'POST',
            data: {
                board: board,
                ROWS: ROWS,
                COLS: COLS,
                MAX_DEPTH: MAX_DEPTH
            },
            success: function (response) {
                console.log(response);
                response = JSON.parse(response);
                if(!addNewPiece(response['move'], 2)){
                    resetGame();
                    fillBoard(allGameDatas.players[0].colsPlayed, allGameDatas.players[1].colsPlayed, 1000);
                    resetColHistory();
                    updateAllGameInfos();
                    alert('Player 2 wins!');
                }
                if(checkWin() === 2){
                    allGameDatas.players[1].victoryCount++;
                    allGameDatas.players[0].defeatCount++;
                    resetGame();
                    fillBoard(allGameDatas.players[0].colsPlayed, allGameDatas.players[1].colsPlayed, 1000);
                    resetColHistory();
                    updateAllGameInfos();
                    alert('Player 2 wins!');
                }
                console.log(response);
                currentPlayer = 1;
            }
        });
    }
</script>
<footer>
    <p>&copy; 2024 <a href="https://github.com/MathieuMichels/connect4" target="_blank">Mathieu Michels</a></p>
</footer>
</body>
</html>
