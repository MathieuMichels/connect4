<?php session_start(); ?>
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
            <?php if (isset($_SESSION['pseudo']) && !empty($_SESSION['pseudo'])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['pseudo']); ?>!</span>
                <form action="php/logout_user.php" method="post" style="display: inline;">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <form action="php/register_user.php" method="post" style="display: inline;">
                    <label for="reg_pseudo">Choose Pseudo:</label>
                    <input type="text" id="reg_pseudo" name="pseudo" required>
                    <button type="submit">Register</button>
                </form>
                <form action="php/login_user.php" method="post" style="display: inline;">
                    <label for="login_pseudo">Use Pseudo:</label>
                    <input type="text" id="login_pseudo" name="pseudo" required>
                    <button type="submit">Login</button>
                </form>
            <?php endif; ?>
            <button id="new-game">Nouvelle partie</button> <!-- This button might be for a different type of new game (e.g. local vs local, or default AI) -->
            <button id="reset-scores" onclick="resetGame()">Réinitialiser les scores</button>
            <!-- Difficulty slider moved to Create Game form -->
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
                <span class="player-wins" data-stat="wins">0</span>
                <span class="player-defeats" data-stat="defeats">0</span>
                <span class="player-nulls" data-stat="nulls">0</span>
                <span class="player-prob">0%</span>
            </div>
        </div>
        <div class="player" data-player="2">
            <h2 data-player="Player 2">Joueur 2</h2>
            <div class="player-info">
                <span class="player-wins" data-stat="wins">0</span>
                <span class="player-defeats" data-stat="defeats">0</span>
                <span class="player-nulls" data-stat="nulls">0</span>
                <span class="player-prob">0%</span>
            </div>
        </div>
    </div>
    <p id="turn-indicator"></p>
    <button id="backToLobbyBtn" style="display:none;">Back to Lobby</button>

    <?php if (isset($_SESSION['pseudo']) && !empty($_SESSION['pseudo'])): ?>
    <div id="game-creation-lobby">
        <h2>Game Lobby</h2>
        <div id="create-game-form-section">
            <h3>Create a New Game</h3>
            <form id="createGameForm" action="php/create_game.php" method="post">
                <div>
                    <label for="gameName">Game Name (optional):</label>
                    <input type="text" id="gameName" name="gameName" placeholder="My Connect4 Game">
                </div>
                <div>
                    <label for="play_ai">Play against AI?</label>
                    <input type="checkbox" id="play_ai" name="play_ai" value="true">
                </div>
                <div id="ai_difficulty_section" style="display: block;"> <!-- Initially show, JS can hide if play_ai is unchecked -->
                    <label for="difficulty">AI Difficulty:</label>
                    <input type="range" id="difficulty" name="difficulty" min="1" max="7" value="5">
                </div>
                <button type="submit">Create New Game</button>
            </form>
        </div>
        <hr>
        <div id="available-games-section">
            <h3>Available Games</h3>
            <button id="refresh-games-list">Refresh List</button>
            <div id="available-games-list">
                <!-- Games will be listed here by JavaScript -->
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>
<script src="js/connect4.js"></script>
<script>
    // This is the new updateUserStatsDisplay function
    function updateUserStatsDisplay(userPseudo, playerNumber) {
        if (!userPseudo || !playerNumber) return;

        fetch('php/get_user_stats.php?pseudo=' + encodeURIComponent(userPseudo))
            .then(response => response.json())
            .then(stats => {
                if (stats && !stats.error) {
                    if (typeof updatePlayerWins === 'function') updatePlayerWins(playerNumber, stats.wins);
                    if (typeof updatePlayerDefeats === 'function') updatePlayerDefeats(playerNumber, stats.defeats);
                    if (typeof updatePlayerNull === 'function') updatePlayerNull(playerNumber, stats.nulls); // Corrected: updatePlayerNull
                } else if (stats && stats.error) {
                    console.error('Error fetching stats for ' + userPseudo + ':', stats.error);
                    if (typeof updatePlayerWins === 'function') updatePlayerWins(playerNumber, 0);
                    if (typeof updatePlayerDefeats === 'function') updatePlayerDefeats(playerNumber, 0);
                    if (typeof updatePlayerNull === 'function') updatePlayerNull(playerNumber, 0); // Corrected
                }
            })
            .catch(error => {
                console.error('Error fetching user stats for ' + userPseudo + ':', error);
                if (typeof updatePlayerWins === 'function') updatePlayerWins(playerNumber, 0);
                if (typeof updatePlayerDefeats === 'function') updatePlayerDefeats(playerNumber, 0);
                if (typeof updatePlayerNull === 'function') updatePlayerNull(playerNumber, 0); // Corrected
            });
    }
    
    // Old updateUserStats (from original template) is removed as it's fully replaced by updateUserStatsDisplay
    // For now, let's assume the specific `updateUserStatsDisplay` called from `updateGameUI` is the primary way.
    // document.addEventListener('DOMContentLoaded', function() {
        // if (currentUserPseudo) updateUserStatsDisplay(currentUserPseudo, 1); // Example: update player 1 by default
    // });


    document.addEventListener('DOMContentLoaded', function() {
        // Hide probability elements once
        document.querySelectorAll('.player-prob').forEach(el => el.style.display = 'none');

        // Game Lobby JavaScript
        <?php if (isset($_SESSION['pseudo']) && !empty($_SESSION['pseudo'])): ?>
        const availableGamesListDiv = document.getElementById('available-games-list');
        const refreshButton = document.getElementById('refresh-games-list');
        const gameCreationLobbyDiv = document.getElementById('game-creation-lobby');
        const gameBoardTable = document.getElementById('game-board');
        const gameInfosDiv = document.getElementById('game-infos');
        
        // --- New Global JS Variables ---
        let currentGameId = null; 
        const currentUserPseudo = "<?php echo isset($_SESSION['pseudo']) ? htmlspecialchars($_SESSION['pseudo']) : ''; ?>";
        let currentGameState = null;
        let pollingInterval = null;
        const JS_ROWS = 6; 
        const JS_COLS = 7; 
        let cells = []; 

        // --- Helper: Create HTML board structure ---
        function createBoardHTML() {
            if (!gameBoardTable) {
                console.error("game-board table not found for createBoardHTML");
                return;
            }
            gameBoardTable.innerHTML = ''; 
            cells = [];
            for (let r = 0; r < JS_ROWS; r++) {
                const row = gameBoardTable.insertRow();
                cells[r] = [];
                for (let c = 0; c < JS_COLS; c++) {
                    const cell = row.insertCell();
                    cell.dataset.column = c;
                    cell.dataset.row = r; 
                    cells[r][c] = cell;
                }
            }
        }

        // --- `drawBoard` Function ---
        function drawBoard(boardData, playersPseudoArray) {
            if (!cells || cells.length === 0) {
                console.warn("HTML board (cells array) not initialized. Calling createBoardHTML.");
                createBoardHTML(); // Attempt to create it if missing
                if (!cells || cells.length === 0) { // Check again
                     console.error("Failed to initialize HTML board for drawBoard.");
                     return;
                }
            }
            if (!boardData || !Array.isArray(boardData)) {
                console.error("Invalid boardData for drawBoard:", boardData);
                return;
            }

            for (let r = 0; r < JS_ROWS; r++) {
                if (!boardData[r] || !Array.isArray(boardData[r])) {
                    console.warn(`Row ${r} is missing or not an array in boardData.`);
                    continue;
                }
                for (let c = 0; c < JS_COLS; c++) {
                    if (!cells[r] || !cells[r][c]) {
                        console.warn(`Cell [${r}][${c}] not found in HTML board.`);
                        continue;
                    }
                    cells[r][c].className = ''; 
                    const piecePseudo = boardData[r][c];
                    if (piecePseudo && piecePseudo !== 0) { 
                        if (playersPseudoArray && playersPseudoArray.length > 0 && piecePseudo === playersPseudoArray[0]) {
                            cells[r][c].classList.add('player1');
                        } else if (playersPseudoArray && playersPseudoArray.length > 1 && piecePseudo === playersPseudoArray[1]) {
                            cells[r][c].classList.add('player2');
                        }
                    }
                }
            }
        }
        
        // --- `updateGameUI` Function ---
        function updateGameUI(gameState) {
            currentGameState = gameState;
            console.log("Updating UI with gameState:", gameState);

            if (!gameState || typeof gameState.players === 'undefined' || typeof gameState.currentBoard === 'undefined') {
                console.error("Incomplete gameState for UI update:", gameState);
                return; 
            }
            
            // Call setGameName from connect4.js
            if (typeof setGameName === 'function') {
                setGameName(gameState.gameName ? `${gameState.gameName} (ID: ${gameState.id})` : `Game ID: ${gameState.id}`);
            } else { // Fallback if connect4.js not loaded or function removed
                const gameNameH2 = document.getElementById('game-name');
                if (gameNameH2) gameNameH2.textContent = gameState.gameName ? `${gameState.gameName} (ID: ${gameState.id})` : `Game ID: ${gameState.id}`;
            }

            drawBoard(gameState.currentBoard, gameState.players);

            // Update Player Names using connect4.js functions
            if (typeof updatePlayerName === 'function') {
                if (gameState.players && gameState.players.length > 0) {
                    updatePlayerName(1, gameState.players[0]);
                    // Update stats for player 1 if they are the current user
                    if (currentUserPseudo === gameState.players[0]) {
                        updateUserStatsDisplay(currentUserPseudo, 1);
                    } else { // Clear stats for player 1 if not current user
                        updatePlayerWins(1, '-'); updatePlayerDefeats(1, '-'); updatePlayerNull(1, '-'); // Corrected
                    }
                } else {
                    updatePlayerName(1, "Player 1");
                     updatePlayerWins(1, '-'); updatePlayerDefeats(1, '-'); updatePlayerNull(1, '-'); // Corrected
                }

                if (gameState.players && gameState.players.length > 1) {
                    updatePlayerName(2, gameState.players[1]);
                     // Update stats for player 2 if they are the current user
                    if (currentUserPseudo === gameState.players[1]) {
                        updateUserStatsDisplay(currentUserPseudo, 2);
                    } else { // Clear stats for player 2 if not current user (and not AI)
                        if(gameState.players[1] !== "AI_PLAYER"){
                           updatePlayerWins(2, '-'); updatePlayerDefeats(2, '-'); updatePlayerNull(2, '-'); // Corrected
                        } else { // AI, clear stats
                           updatePlayerWins(2, '-'); updatePlayerDefeats(2, '-'); updatePlayerNull(2, '-'); // Corrected
                        }
                    }
                } else {
                    updatePlayerName(2, (gameState.status === 'waiting' && gameState.game_type !== 'ai') ? "Waiting..." : "Player 2");
                    updatePlayerWins(2, '-'); updatePlayerDefeats(2, '-'); updatePlayerNull(2, '-'); // Corrected
                }
            } else { // Fallback if connect4.js functions not available
                 const player1NameH2 = document.querySelector('.player[data-player="1"] h2');
                 const player2NameH2 = document.querySelector('.player[data-player="2"] h2');
                 if (player1NameH2 && gameState.players && gameState.players.length > 0) player1NameH2.textContent = gameState.players[0];
                 if (player2NameH2 && gameState.players && gameState.players.length > 1) player2NameH2.textContent = gameState.players[1];
            }
            
            // Turn Indicator
            const turnIndicator = document.getElementById('turn-indicator');
            if (turnIndicator) {
                if (gameState.status === 'in_progress') {
                    turnIndicator.innerText = 'Turn: ' + gameState.current_turn_pseudo + 
                                              (gameState.current_turn_pseudo === currentUserPseudo ? " (Your turn!)" : "");
                    turnIndicator.style.display = 'block';
                } else if (gameState.status === 'finished' || gameState.status === 'finished_draw') {
                     turnIndicator.innerText = gameState.winner_pseudo ? `Winner: ${gameState.winner_pseudo}` : "It's a Draw!";
                     turnIndicator.style.display = 'block';
                } else {
                    turnIndicator.style.display = 'none';
                }
            }


            if (gameState.status === 'finished' || gameState.status === 'finished_draw') {
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = null;
                alert(gameState.status === 'finished' ? `Game Over! Winner: ${gameState.winner_pseudo}` : "Game Over! It's a Draw!");
                
                const backButton = document.getElementById('backToLobbyBtn');
                if(backButton) backButton.style.display = 'block';
                
                // Refresh overall user stats for the logged-in user (e.g. if they were player 1 or 2)
                if (currentUserPseudo === gameState.players[0]) {
                    updateUserStatsDisplay(currentUserPseudo, 1);
                } else if (currentUserPseudo === gameState.players[1]) {
                     updateUserStatsDisplay(currentUserPseudo, 2);
                }
            }
        }

        // --- goBackToLobby Function ---
        function goBackToLobby() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = null;
            currentGameId = null;
            currentGameState = null;

            const gameBoard = document.getElementById('game-board');
            const gameInfos = document.getElementById('game-infos');
            const lobby = document.getElementById('game-creation-lobby');
            const backButton = document.getElementById('backToLobbyBtn');
            const turnInfo = document.getElementById('turn-indicator');
            const gameNameDisplay = document.getElementById('game-name');


            if (gameBoard) gameBoard.style.display = 'none';
            if (gameInfos) gameInfos.style.display = 'none';
            if (lobby) lobby.style.display = 'block';
            if (backButton) backButton.style.display = 'none';
            if (turnInfo) turnInfo.style.display = 'none';
            if (gameNameDisplay) gameNameDisplay.textContent = '';


            if (typeof fetchAvailableGames === 'function') {
                fetchAvailableGames();
            }
        }
        const backButtonInstance = document.getElementById('backToLobbyBtn');
        if (backButtonInstance) {
            backButtonInstance.addEventListener('click', goBackToLobby);
        }


        // --- `fetchGameState` Function ---
        function fetchGameState(gameId) {
            if (!gameId) {
                console.log("fetchGameState called without gameId");
                if (pollingInterval) clearInterval(pollingInterval);
                return;
            }
            fetch(`php/get_game_state.php?game_id=${gameId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data && data.id) {
                        updateGameUI(data);
                    } else if (data && data.error) {
                        console.error("Error fetching game state:", data.error);
                        if (pollingInterval && (data.error.includes("not found") || data.error.includes("decode"))) {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                            alert("Error fetching game state: " + data.error + ". Returning to lobby.");
                            if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'block';
                            if (gameBoardTable) gameBoardTable.style.display = 'none';
                            if (gameInfosDiv) gameInfosDiv.style.display = 'none';
                            currentGameId = null; currentGameState = null;
                            fetchAvailableGames();
                        }
                    }
                })
                .catch(error => {
                    console.error("Exception in fetchGameState:", error);
                });
        }
        
        // --- `startGamePolling` Function (Modified) ---
        function startGamePolling(gameId) {
            if (pollingInterval) clearInterval(pollingInterval);
            currentGameId = gameId; 
            console.log('Starting game polling for game_id:', gameId);
            fetchGameState(gameId); 
            pollingInterval = setInterval(() => fetchGameState(gameId), 3000); 
        }

        function joinGame(gameId) {
            fetch('php/join_game.php', {
                method: 'POST',
                body: new URLSearchParams({game_id: gameId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully joined game: ' + data.game_id + '. The game board will now be shown.');
                    
                    if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
                    if (gameBoardTable) {
                        createBoardHTML(); 
                        gameBoardTable.style.display = 'table';
                    }
                    if (gameInfosDiv) gameInfosDiv.style.display = 'flex';

                    startGamePolling(data.game_id); 
                } else {
                    alert('Could not join game: ' + (data.message || 'Unknown error'));
                    fetchAvailableGames(); 
                }
            })
            .catch(error => {
                console.error('Error joining game:', error);
                alert('An error occurred while trying to join the game.');
            });
        }

        function fetchAvailableGames() {
            if (!availableGamesListDiv) return; // Make sure the div exists

            fetch('php/list_games.php')
                .then(response => response.json())
                .then(games => {
                    availableGamesListDiv.innerHTML = ''; // Clear current list
                    if (games.length === 0) {
                        availableGamesListDiv.innerHTML = '<p>No games available to join. Create one!</p>';
                        return;
                    }
                    const ul = document.createElement('ul');
                    ul.className = 'games-ul';
                    games.forEach(game => {
                        const li = document.createElement('li');
                        li.className = 'game-item';
                        // Ensure game.players is an array before calling join
                        const playersDisplay = Array.isArray(game.players) ? game.players.join(', ') : 'N/A';
                        
                        li.innerHTML = `
                            <strong>${game.gameName || 'Unnamed Game'}</strong> (ID: ${game.id})<br>
                            Created by: ${game.game_creator_pseudo || 'Unknown'}<br>
                            Players: ${playersDisplay}
                            <button onclick="joinGame('${game.id}')">Join Game</button>
                        `;
                        ul.appendChild(li);
                    });
                    availableGamesListDiv.appendChild(ul);
                })
                .catch(error => {
                    console.error('Error fetching available games:', error);
                    availableGamesListDiv.innerHTML = '<p>Error loading games. Please try again.</p>';
                });
        }

        if (refreshButton) {
            refreshButton.addEventListener('click', fetchAvailableGames);
        }

        // Initial fetch of available games if the user is logged in
        if (currentUserPseudo) { 
             fetchAvailableGames();
        }


        // Initially hide game board and infos if not in a game
        if (!currentGameId) {
            if (gameBoardTable) gameBoardTable.style.display = 'none';
            if (gameInfosDiv) gameInfosDiv.style.display = 'none';
            const turnInfo = document.getElementById('turn-indicator');
            if(turnInfo) turnInfo.style.display = 'none';
        }

        // Toggle AI difficulty slider visibility based on checkbox
        const playAiCheckbox = document.getElementById('play_ai');
        const aiDifficultySection = document.getElementById('ai_difficulty_section');

        if (playAiCheckbox && aiDifficultySection) {
            // Initial state based on checkbox (e.g. if it can be checked by default)
            aiDifficultySection.style.display = playAiCheckbox.checked ? 'block' : 'none';

            playAiCheckbox.addEventListener('change', function() {
                aiDifficultySection.style.display = this.checked ? 'block' : 'none';
            });
        }


        <?php endif; ?> // End of: if (isset($_SESSION['pseudo']))
    });
</script>
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
