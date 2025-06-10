document.addEventListener('DOMContentLoaded', () => {
    // API Endpoints
    const API_BASE_URL = 'php/'; // Assuming PHP files are in v2/php/
    const CREATE_GAME_URL = `${API_BASE_URL}create_game.php`;
    const GET_GAME_STATE_URL = `${API_BASE_URL}get_game_state.php`;
    const PLAY_MOVE_URL = `${API_BASE_URL}play_move.php`;

    // DOM Elements
    const gameBoardContainer = document.getElementById('game-board-container');
    const turnIndicator = document.getElementById('turn-indicator');
    const player1Info = document.getElementById('player1-info'); // Will be updated later
    const player2Info = document.getElementById('player2-info'); // Will be updated later
    const newGameBtn = document.getElementById('new-game-btn');
    const messageArea = document.getElementById('message-area');

    // Game State Variables
    let currentGameId = null;
    let currentPlayerPseudo = 'Player1'; // Default, will be set by user/login later
    let boardRows = 6; // Default, should come from game state
    let boardCols = 7; // Default, should come from game state
    let cells = []; // For storing cell DOM elements

    // --- Utility Functions ---
    function showMessage(message, type = 'info') {
        messageArea.textContent = message;
        messageArea.className = `message-${type}`; // Uses classes like .message-info, .message-error
    }

    // --- Game Board Rendering ---
    function createBoardHTML(rows, cols) {
        gameBoardContainer.innerHTML = ''; // Clear existing board
        cells = []; // Reset cells array

        gameBoardContainer.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        gameBoardContainer.style.gridTemplateRows = `repeat(${rows}, 1fr)`; // Optional: if you want to enforce row height via grid

        for (let r = 0; r < rows; r++) {
            cells[r] = [];
            for (let c = 0; c < cols; c++) {
                const cell = document.createElement('div');
                cell.classList.add('board-cell');
                cell.dataset.row = r;
                cell.dataset.column = c;

                // Add click listener to the cell itself, or better, to column headers/buttons later
                // For now, clicking any cell in a column could signify choosing that column.
                // A more refined approach would be invisible buttons on top of each column.
                cell.addEventListener('click', () => handleColumnClick(c));

                gameBoardContainer.appendChild(cell);
                cells[r][c] = cell;
            }
        }
        boardRows = rows; // Update global rows
        boardCols = cols; // Update global columns
    }

    function renderBoard(boardData, gamePlayers) {
        if (!boardData || boardData.length === 0 || !cells || cells.length === 0) {
            console.error('Board data or HTML cells not initialized for renderBoard');
            return;
        }

        boardData.forEach((row, r) => {
            row.forEach((piece, c) => {
                if (cells[r] && cells[r][c]) {
                    cells[r][c].classList.remove('player1-piece', 'player2-piece'); // Clear previous pieces
                    if (piece !== 0 && gamePlayers && gamePlayers.length > 0) { // Piece is not empty
                        if (piece === gamePlayers[0]) {
                            cells[r][c].classList.add('player1-piece');
                        } else if (gamePlayers.length > 1 && piece === gamePlayers[1]) {
                            cells[r][c].classList.add('player2-piece');
                        }
                    }
                }
            });
        });
    }

    // --- API Interaction ---
    async function apiRequest(url, method = 'GET', body = null) {
        const options = { method };
        if (body && method !== 'GET') {
            options.body = new URLSearchParams(body); // For application/x-www-form-urlencoded
        }
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ error: `HTTP error! status: ${response.status}` }));
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('API Request Error:', error);
            showMessage(error.message, 'error');
            throw error; // Re-throw to be caught by caller
        }
    }

    async function fetchGameState(gameId) {
        if (!gameId) return;
        try {
            const data = await apiRequest(`${GET_GAME_STATE_URL}?game_id=${gameId}`);
            if (data) {
                updateUIWithGameState(data);
            }
        } catch (error) {
            // Error already shown by apiRequest
        }
    }

    // Polling for game state
    let pollingInterval = null;
    function startGamePolling(gameId) {
        if (pollingInterval) clearInterval(pollingInterval);
        fetchGameState(gameId); // Initial fetch
        pollingInterval = setInterval(() => fetchGameState(gameId), 3000); // Poll every 3 seconds
    }
    function stopGamePolling() {
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = null;
    }

    async function handleCreateGame() {
        // For now, playerPseudo is hardcoded. Later, this will come from login/input.
        // Similarly, AI settings are hardcoded for this initial version.
        const pseudo = prompt("Enter your pseudo:", currentPlayerPseudo);
        if (!pseudo) return;
        currentPlayerPseudo = pseudo;

        const gameParams = {
            playerPseudo: currentPlayerPseudo,
            isAiGame: 'true', // Assuming vs AI for this button
            aiDifficulty: '5',
            gameName: `${currentPlayerPseudo}'s AI Game`
        };

        try {
            const data = await apiRequest(CREATE_GAME_URL, 'POST', gameParams);
            if (data && data.success && data.game) {
                currentGameId = data.game.gameId;
                showMessage(`Game created! ID: ${currentGameId}`, 'success');
                updateUIWithGameState(data.game);
                startGamePolling(currentGameId);
            } else {
                showMessage(data.error || 'Failed to create game.', 'error');
            }
        } catch (error) {
            // Error message handled by apiRequest
        }
    }

    async function handleColumnClick(column) {
        if (!currentGameId) {
            showMessage('No active game. Create one first!', 'error');
            return;
        }
        // Add check: if it's not current player's turn, or if game is over, do nothing.
        // This requires having the full game state available client-side.

        const moveParams = {
            game_id: currentGameId,
            playerPseudo: currentPlayerPseudo, // This needs to be the actual current player from game state
            column: column
        };

        try {
            // Optimistic UI update can be added here if desired

            const data = await apiRequest(PLAY_MOVE_URL, 'POST', moveParams);
            if (data && data.board) { // Successful move returns the new game state
                updateUIWithGameState(data);
                // If AI game and it's AI's turn, AI will play on backend, next poll will show it.
            } else if (data && data.error) {
                showMessage(data.error, 'error');
                 // If the error response includes the current server state, update UI to sync
                if (data.currentState) {
                    updateUIWithGameState(data.currentState);
                }
            } else {
                showMessage('Move failed for an unknown reason.', 'error');
            }
        } catch (error) {
             // Error message handled by apiRequest
        }
    }

    function updateUIWithGameState(gameState) {
        if (!gameState) return;

        currentGameId = gameState.gameId; // Ensure currentGameId is updated
        boardRows = gameState.rows;
        boardCols = gameState.cols;

        // Check if board dimensions changed or not yet created
        if (cells.length !== boardRows || (cells[0] && cells[0].length !== boardCols) || gameBoardContainer.children.length === 0) {
            createBoardHTML(boardRows, boardCols);
        }
        renderBoard(gameState.board, gameState.players);

        // Update Player Info (basic)
        if (gameState.players && gameState.players.length > 0) {
            player1Info.querySelector('h2').textContent = `${gameState.players[0]} (P1)`;
            if (gameState.players.length > 1) {
                const p2name = gameState.isAiGame && gameState.players[1] === 'AI_PLAYER' ? 'AI Opponent' : gameState.players[1];
                player2Info.querySelector('h2').textContent = `${p2name} (P2)`;
            } else {
                player2Info.querySelector('h2').textContent = 'Waiting for P2...';
            }
        }

        // Update Turn Indicator / Game Status
        if (gameState.status === 'in_progress') {
            turnIndicator.textContent = `Turn: ${gameState.currentPlayer}`;
            // Update currentPlayerPseudo if the current player from state is one of the known players.
            // This is important if the initial currentPlayerPseudo was a default or from a previous game.
            if (gameState.players.includes(currentPlayerPseudo)) {
                 // currentPlayerPseudo is still valid within this game's context.
            } else if (gameState.players.length > 0) {
                // If the stored currentPlayerPseudo is not in the game's player list,
                // default to player 1 from the game's perspective for client-side logic.
                // This might happen if user reloads page for a game they didn't create or join with their current pseudo.
                // A more robust solution involves user sessions/login to firmly establish "who" this client is.
                // For now, if this client's pseudo is not in game.players, they are an observer or it's a mismatch.
                // We'll assume for now that the `currentPlayerPseudo` set at game creation or via prompt is "this client".
            }


            if (gameState.currentPlayer === currentPlayerPseudo) {
                // It's my turn
                turnIndicator.textContent = `Your Turn (${gameState.currentPlayer})`;
                gameBoardContainer.classList.add('my-turn');
                gameBoardContainer.classList.remove('opponent-turn');
            } else {
                // Opponent's turn
                turnIndicator.textContent = `Opponent's Turn (${gameState.currentPlayer})`;
                gameBoardContainer.classList.remove('my-turn');
                gameBoardContainer.classList.add('opponent-turn');
            }
        } else if (gameState.status === 'finished') {
            turnIndicator.textContent = `Game Over! Winner: ${gameState.winner}`;
            showMessage(`Game Over! Winner: ${gameState.winner}`, 'success');
            stopGamePolling();
            gameBoardContainer.classList.remove('my-turn', 'opponent-turn');
        } else if (gameState.status === 'finished_draw') {
            turnIndicator.textContent = 'Game Over! It\'s a Draw!';
            showMessage('Game Over! It\'s a Draw!', 'info');
            stopGamePolling();
            gameBoardContainer.classList.remove('my-turn', 'opponent-turn');
        } else if (gameState.status === 'waiting') {
            turnIndicator.textContent = 'Waiting for players...';
             gameBoardContainer.classList.remove('my-turn', 'opponent-turn');
        }
    }

    // --- Event Listeners Setup ---
    if (newGameBtn) {
        newGameBtn.addEventListener('click', handleCreateGame);
    } else {
        console.error('#new-game-btn not found');
    }

    // --- Initialization ---
    function init() {
        // Create a default empty board on page load
        createBoardHTML(boardRows, boardCols);
        showMessage('Welcome to Connect 4! Click "New Game" to start.');
        // Potentially load a game if ID is in URL or localStorage
        // For example:
        // const urlParams = new URLSearchParams(window.location.search);
        // const gameIdFromUrl = urlParams.get('game_id');
        // if (gameIdFromUrl) {
        //     currentGameId = gameIdFromUrl;
        //     startGamePolling(currentGameId);
        // }
    }

    init();
});
