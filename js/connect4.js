const WINNING_LENGTH = 4;

const COLS = Math.floor(WINNING_LENGTH + 3);
const ROWS = Math.floor(WINNING_LENGTH + 2);


// set --cols et --rows in root values
document.documentElement.style.setProperty('--cols', COLS);
document.documentElement.style.setProperty('--rows', ROWS);

// let allGameDatas = { // Removed: Game data is now server-driven
//     "gameName": "Ma super chouette partie ⚪😋😋😋🔴⭕😅⚪⚫😊😄😁",
//     "nbPlayers": 2,
//     "players": [
//         {
//             "name": "Mathieu",
//             "color": "green",
//             "victoryCount": 159,
//             "defeatCount": 2,
//             "nullCount": 43,
//             "colsPlayed": [],
//             "winProba": "105.83%"
//         },
//         {
//             "name": "Sébastien",
//             "color": "blue",
//             "victoryCount": 2,
//             "defeatCount": 159,
//             "nullCount": 43,
//             "colsPlayed": [],
//             "winProba": "92.57%"
//         }
//     ]
// };

let currentPlayer = 1; // Note: This is now primarily managed by server state for multiplayer games.

const gameBoard = document.getElementById('game-board'); // it's a table
if (gameBoard) { // Ensure gameBoard exists before setting dataset properties
    gameBoard.dataset.cols = COLS;
    gameBoard.dataset.rows = ROWS;
}
const cells = []; // This will be populated by createBoardHTML in index.php

// Create the game board - This is now handled by createBoardHTML in index.php
// for (let i = 0; i < ROWS; i++) {
//     const row = document.createElement('tr');
//     cells.push([]);
//     for (let j = 0; j < COLS; j++) {
//         const cell = document.createElement('td');
//         cell.classList.add('cell');
//         cell.dataset.row = i;
//         cell.dataset.col = j;
//         cell.dataset.cols = COLS;
//         cell.dataset.rows = ROWS;
//         row.appendChild(cell);
//         cells[i].push(cell);
//     }
//     if (gameBoard) gameBoard.appendChild(row);
// }

function addNewPiece(col, player) { // Legacy function, multiplayer logic is server-side
    if(isNaN(col)) return false;
    console.log("addNewPiece (legacy)", col, player);
    for (let i = ROWS - 1; i >= 0; i--) {
        // Ensure cells[i] and cells[i][col] exist before trying to access classList
        if (cells[i] && cells[i][col] && !cells[i][col].classList.contains('player1') && !cells[i][col].classList.contains('player2')) {
            cells[i][col].classList.add(`player${player}`);
            // allGameDatas.players[player - 1].colsPlayed.push(col); // allGameDatas removed
            return true;
        }
    }
    return false;
}

function checkWin() { // This is a legacy client-side check, server is authoritative
    // Check horizontal
    for (let i = 0; i < ROWS; i++) {
        for (let j = 0; j < COLS - WINNING_LENGTH + 1; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i] && cells[i][j+k] && cells[i][j + k].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }
    // Check vertical
    for (let i = 0; i < ROWS - WINNING_LENGTH + 1; i++) {
        for (let j = 0; j < COLS; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i+k] && cells[i+k][j] && cells[i + k][j].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }
    // Check positively sloped diagonal
    for (let i = 0; i < ROWS - WINNING_LENGTH + 1; i++) {
        for (let j = 0; j < COLS - WINNING_LENGTH + 1; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i+k] && cells[i+k][j+k] && cells[i + k][j + k].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }
    // Check negatively sloped diagonal
    for (let i = 0; i < ROWS - WINNING_LENGTH + 1; i++) {
        for (let j = WINNING_LENGTH - 1; j < COLS; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i+k] && cells[i+k][j-k] && cells[i + k][j - k].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }
    return 0; // Return 0 if no winner
}


function checkDraw() { // Legacy client-side check
    for (let i = 0; i < ROWS; i++) {
        for (let j = 0; j < COLS; j++) {
            if (cells[i] && cells[i][j] && !cells[i][j].classList.contains('player1') && !cells[i][j].classList.contains('player2')) {
                return false;
            }
        }
    }
    return true;
}

function resetGame() { // Legacy, server now manages game state. Client UI updates via polling.
    console.log("Legacy resetGame() called. Game state is server-authoritative.");
    // Actual board clearing is handled by drawBoard in index.php by rendering server state.
}

if (gameBoard) {
    gameBoard.addEventListener('click', (e) => { // This listener is now mostly superseded by the one in index.php
        // Content related to allGameDatas, send(), etc., is removed/commented.
        // The main click logic is in index.php.
        console.log("Legacy gameBoard click listener in connect4.js fired. Should be disabled or removed for multiplayer.");
    });
}

// function fillBoard(colsPlayer1, colsPlayer2, speed = 0) { // Deprecated
//     // ... content removed ...
// }
// fillBoard(allGameDatas.players[0].colsPlayed, allGameDatas.players[1].colsPlayed, 200); // Removed

function setGameName(name = "Connect 4"){ // Added default name parameter
    const gameNameEl = document.getElementById('game-name');
    if (gameNameEl) {
        gameNameEl.textContent = name; // Use textContent
        let fontSize = 24;
        gameNameEl.style.fontSize = `${fontSize}px`;
        while (gameNameEl.scrollWidth > gameNameEl.offsetWidth && fontSize > 10) { // Added min font size
            gameNameEl.style.fontSize = `${fontSize--}px`;
        }
    }
}

function resetColHistory(){ // Legacy function
    // allGameDatas.players.forEach(player => player.colsPlayed = []); // Removed
}

// setGameName(); // Initial call removed, handled by index.php's DOMContentLoaded or updateGameUI

// check if resize or zoom
window.addEventListener('resize', () => {
    // This needs access to currentGameState or gameName.
    // For now, set to default. index.php's updateGameUI will correct it on next poll if a game is active.
    if (typeof currentGameState !== 'undefined' && currentGameState && currentGameState.gameName) {
         setGameName(currentGameState.gameName);
    } else if (typeof currentGameState !== 'undefined' && currentGameState && currentGameState.id) { // If game object exists but no name
         setGameName(`Game ID: ${currentGameState.id}`);
    } else {
        setGameName(); // Default "Connect 4"
    }
});

function switchHelp() {
    const help = document.getElementById('help-window');
    const main = document.getElementsByTagName('main')[0];
    const header = document.getElementsByTagName('header')[0];
    const footer = document.getElementsByTagName('footer')[0];
    if (help && main && header && footer) { // Ensure elements exist
        if (help.style.display === 'none') {
            help.style.display = 'block';
            main.style.opacity = '0.5';
            header.style.opacity = '0.5';
            footer.style.opacity = '0.5';
        } else {
            help.style.display = 'none';
            main.style.opacity = '1';
            header.style.opacity = '1';
            footer.style.opacity = '1';
        }
    }
}

function updatePlayerName(player, playerName){
    // Selector for the H2 tag within the .player div that has data-player="Player X"
    let playerHeader = document.querySelector(`.player[data-player="${player}"] h2[data-player="Player ${player}"]`);
    if (playerHeader) {
        playerHeader.textContent = playerName || ""; // Default to empty string if playerName is null/undefined
    } else {
        // Fallback for older structure if data-player on H2 is just the number
        playerHeader = document.querySelector(`.player[data-player="${player}"] h2`);
        if(playerHeader) playerHeader.textContent = playerName || "";
    }
}

function updatePlayerWins(player, wins){
    let playerWinsEl = document.querySelector(`.player[data-player="${player}"] .player-wins`);
    if (playerWinsEl) {
        playerWinsEl.textContent = wins === '-' ? '-' : `🏆 ${wins}`;
    }
}

function updatePlayerDefeats(player, defeats){
    let playerDefeatsEl = document.querySelector(`.player[data-player="${player}"] .player-defeats`);
    if (playerDefeatsEl) {
        playerDefeatsEl.textContent = defeats === '-' ? '-' : `☠️ ${defeats}`;
    }
}

function updatePlayerNull(player, nulls){
    let playerNullsEl = document.querySelector(`.player[data-player="${player}"] .player-nulls`);
    if (playerNullsEl) {
        playerNullsEl.textContent = nulls === '-' ? '-' : `🟰 ${nulls}`;
    }
}

function updatePlayerProb(player, prob){ // This function is not actively used as probs are hidden
    let playerProbEl = document.querySelector(`.player[data-player="${player}"] .player-prob`);
    if (playerProbEl) {
        playerProbEl.textContent = `🎲 ${prob}`;
    }
}

// function updateAllGameInfos(){ // Removed: Game data is now server-driven
//     // ... content removed ...
// }
// updateAllGameInfos(); // Removed

console.log("connect4.js loaded: Legacy initializations (allGameDatas, fillBoard, updateAllGameInfos, board creation loop) removed/commented. Utility functions updated.");
