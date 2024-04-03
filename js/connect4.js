const WINNING_LENGTH = 4;

const COLS = Math.floor(WINNING_LENGTH + 3);
const ROWS = Math.floor(WINNING_LENGTH + 2);


// set --cols et --rows in root values
document.documentElement.style.setProperty('--cols', COLS);
document.documentElement.style.setProperty('--rows', ROWS);

let allGameDatas = {
    "gameName": "Ma super chouette partie ⚪😋😋😋🔴⭕😅⚪⚫😊😄😁",
    "nbPlayers": 2,
    "players": [
        {
            "name": "Mathieu",
            "color": "green",
            "victoryCount": 159,
            "defeatCount": 2,
            "nullCount": 43,
            "colsPlayed": [],
            "winProba": "105.83%"
        },
        {
            "name": "Sébastien",
            "color": "blue",
            "victoryCount": 2,
            "defeatCount": 159,
            "nullCount": 43,
            "colsPlayed": [],
            "winProba": "92.57%"
        }
    ]
};

let currentPlayer = 1; // 1 for Player, 2 for AI

const gameBoard = document.getElementById('game-board'); // it's a table
gameBoard.dataset.cols = COLS;
gameBoard.dataset.rows = ROWS;
const cells = [];

// Create the game board
for (let i = 0; i < ROWS; i++) {
    const row = document.createElement('tr');
    cells.push([]);
    for (let j = 0; j < COLS; j++) {
        const cell = document.createElement('td');
        cell.classList.add('cell');
        cell.dataset.row = i;
        cell.dataset.col = j;
        cell.dataset.cols = COLS;
        cell.dataset.rows = ROWS;
        row.appendChild(cell);
        cells[i].push(cell);
    }
    gameBoard.appendChild(row);
}

function addNewPiece(col, player) {
    console.log("addNewPiece", col, player);
    for (let i = ROWS - 1; i >= 0; i--) {
        if (!cells[i][col].classList.contains('player1') && !cells[i][col].classList.contains('player2')) {
            cells[i][col].classList.add(`player${player}`);
            allGameDatas.players[player - 1].colsPlayed.push(col);
            return true;
        }
    }
    return false;
}

function checkWin() {
    // Check horizontal
    for (let i = 0; i < ROWS; i++) {
        for (let j = 0; j < COLS - WINNING_LENGTH + 1; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i][j + k].classList.contains(`player${currentPlayer}`)) {
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
                if (cells[i + k][j].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }

    // Check diagonal
    for (let i = 0; i < ROWS - WINNING_LENGTH + 1; i++) {
        for (let j = 0; j < COLS - WINNING_LENGTH + 1; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i + k][j + k].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }

    for (let i = 0; i < ROWS - WINNING_LENGTH + 1; i++) {
        for (let j = WINNING_LENGTH - 1; j < COLS; j++) {
            let count = 0;
            for (let k = 0; k < WINNING_LENGTH; k++) {
                if (cells[i + k][j - k].classList.contains(`player${currentPlayer}`)) {
                    count++;
                }
            }
            if (count === WINNING_LENGTH) {
                return currentPlayer;
            }
        }
    }
}


function checkDraw() {
    for (let i = 0; i < ROWS; i++) {
        for (let j = 0; j < COLS; j++) {
            if (!cells[i][j].classList.contains('player1') && !cells[i][j].classList.contains('player2')) {
                return false;
            }
        }
    }
    return true;
}

function resetGame() {
    for (let i = 0; i < ROWS; i++) {
        for (let j = 0; j < COLS; j++) {
            cells[i][j].classList.remove('player1', 'player2');
        }
    }
    currentPlayer = 1;
}

gameBoard.addEventListener('click', (e) => {
        if (currentPlayer === 1) {
            let col = e.target.dataset.col;
            if (col) {
                col = parseInt(col);
                if(!addNewPiece(col, currentPlayer)) return;
                if (checkWin() === 1) {
                    alert('Player 1 wins!');
                    allGameDatas.players[0].victoryCount++;
                    allGameDatas.players[1].defeatCount++;
                    resetGame();
                    fillBoard(allGameDatas.players[0].colsPlayed, allGameDatas.players[1].colsPlayed, 1000);
                    resetColHistory();
                    updateAllGameInfos();
                } else if (checkDraw()) {
                    alert('Draw!');
                } else {
                    currentPlayer = 2;
                }
            }
        } else {
            let col = Math.floor(Math.random() * COLS);
            if(!addNewPiece(col, currentPlayer)) return;
            if (checkWin() === 2) {
                alert('Player 2 wins!');
                allGameDatas.players[1].victoryCount++;
                allGameDatas.players[0].defeatCount++;
                resetGame();
                fillBoard(allGameDatas.players[0].colsPlayed, allGameDatas.players[1].colsPlayed, 1000);
                resetColHistory();
                updateAllGameInfos();
            } else if (checkDraw()) {
                alert('Draw!');
            } else {
                currentPlayer = 1;
            }
        }
    }
);


function fillBoard(colsPlayer1, colsPlayer2, speed = 0) {
    let concatCols = [];
    for (let i = 0; i < colsPlayer1.length; i++) {
        concatCols.push(colsPlayer1[i]);
        concatCols.push(colsPlayer2[i]);
    }
    for (let i = 0; i < concatCols.length; i++) {
        let col = concatCols[i];
        if (i % 2 === 0) {
            setTimeout(() => {
                addNewPiece(col, 1);
            },  i * speed);
        } else {
            setTimeout(() => {
                addNewPiece(col, 2);
            }, i * speed);
        }

    }
}

fillBoard(allGameDatas.players[0].colsPlayed, allGameDatas.players[1].colsPlayed, 200);

function setGameName(){
    document.getElementById('game-name').innerText = allGameDatas.gameName;
    const gameName = document.getElementById('game-name');
    let fontSize = 24;
    gameName.style.fontSize = `${fontSize}px`;
    while (gameName.scrollWidth > gameName.offsetWidth) {
        gameName.style.fontSize = `${fontSize--}px`;
    }

}


function resetColHistory(){
    allGameDatas.players.forEach(player => player.colsPlayed = []);
}

setGameName();


// check if resize or zoom
window.addEventListener('resize', setGameName);
window.addEventListener('zoom', setGameName);


function switchHelp() {
    const help = document.getElementById('help-window');
    if (help.style.display === 'none') {
        help.style.display = 'block';
    } else {
        help.style.display = 'none';
    }
}

function updatePlayerName(player, playerName){
    // get element by data-player attribute
    let playerCurrentName = document.querySelector(`[data-player="Player ${player}"]`);
    playerCurrentName.innerText = playerName;

}

function updatePlayerWins(player, wins){
    let playerWins = document.querySelector(`[data-player="${player}"]`);
    playerWins = playerWins.getElementsByClassName('player-wins')[0];
    if (wins > 1) {
        playerWins.innerText = `🏆 ${wins}`;
    }
    else {
        playerWins.innerText = `🏆 ${wins}`;
    }
}

function updatePlayerDefeats(player, defeats){
    let playerDefeats = document.querySelector(`[data-player="${player}"]`);
    playerDefeats = playerDefeats.getElementsByClassName('player-defeats')[0];
    if (defeats > 1) {
        playerDefeats.innerText = `☠️ ${defeats}`;
    }
    else {
        playerDefeats.innerText = `☠️ ${defeats}`;
    }
}

function updatePlayerNull(player, nulls){
    let playerNulls = document.querySelector(`[data-player="${player}"]`);
    playerNulls = playerNulls.getElementsByClassName('player-nulls')[0];
    if (nulls > 1) {
        playerNulls.innerText = `🟰 ${nulls}`;
    }
    else {
        playerNulls.innerText = `🟰 ${nulls}`;
    }
}

function updatePlayerProb(player, prob){
    let playerProb = document.querySelector(`[data-player="${player}"]`);
    playerProb = playerProb.getElementsByClassName('player-prob')[0];
    playerProb.innerText = `🎲 ${prob}`;
}


function updateAllGameInfos(){
    // player = 1 or 2
    for (let player = 1; player <= allGameDatas.nbPlayers; player++){
        updatePlayerName(player, allGameDatas.players[player - 1].name);
        updatePlayerWins(player, allGameDatas.players[player - 1].victoryCount);
        updatePlayerDefeats(player, allGameDatas.players[player - 1].defeatCount);
        updatePlayerNull(player, allGameDatas.players[player - 1].nullCount);
        updatePlayerProb(player, allGameDatas.players[player - 1].winProba);
    }
    setGameName();
}

updateAllGameInfos();



