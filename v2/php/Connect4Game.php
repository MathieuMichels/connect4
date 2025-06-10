<?php

/**
 * Class Connect4Game
 * Represents a game of Connect 4.
 */
class Connect4Game {
    public const ROWS = 6;
    public const COLS = 7;
    public const AI_PLAYER_PSEUDO = "AI_PLAYER";
    public const WINNING_LENGTH = 4;

    private array $board;
    private string $currentPlayer; // Changed to string to store pseudo
    private array $players = [];
    private string $gameId;
    private string $gameName;
    private string $status;
    private ?string $winner = null;
    private bool $isAiGame;
    private int $aiDifficulty;

    /**
     * Connect4Game constructor.
     * @param string $gameId The ID of the game.
     * @param string $gameName The name of the game.
     * @param bool $isAiGame Whether the game is against AI.
     * @param int $aiDifficulty The AI difficulty level.
     * @param bool $initializeState If true, initializes board, status, players, winner. Set to false when restoring from state.
     */
    public function __construct(string $gameId, string $gameName = 'Connect 4 Game', bool $isAiGame = false, int $aiDifficulty = 5, bool $initializeState = true) {
        $this->gameId = $gameId;
        $this->gameName = $gameName;
        $this->isAiGame = $isAiGame;
        $this->aiDifficulty = $aiDifficulty;

        if ($initializeState) {
            $this->board = [];
            $this->initializeBoard();
            // Current player will be set when the game starts or by the first player joining a non-AI game.
            // For AI games, player 1 (human) starts. If not AI, first player added starts.
            $this->currentPlayer = "";
            $this->status = 'waiting';
            $this->players = [];
            $this->winner = null;
        }
        // Properties like board, currentPlayer, players, status, winner will be set by fromState if initializeState is false.
    }

    /**
     * Initializes the game board with empty cells (0).
     */
    private function initializeBoard(): void {
        $this->board = array_fill(0, self::ROWS, array_fill(0, self::COLS, 0));
    }

    /**
     * Adds a player to the game.
     * If the game is AI and it's the first player, AI player is also added.
     * Starts the game if it becomes full.
     * @param string $playerPseudo The pseudo of the player to add.
     * @return bool True if player was added, false otherwise (e.g., game full, pseudo exists).
     */
    public function addPlayer(string $playerPseudo): bool {
        if (count($this->players) >= 2) {
            return false; // Game is full
        }
        if (in_array($playerPseudo, $this->players)) {
            return false; // Player pseudo already exists
        }

        $this->players[] = $playerPseudo;

        if (count($this->players) === 1 && !$this->isAiGame) {
            $this->currentPlayer = $playerPseudo; // First player becomes current player
        }

        if ($this->isAiGame && count($this->players) === 1) {
            if (!in_array(self::AI_PLAYER_PSEUDO, $this->players)) {
                 $this->players[] = self::AI_PLAYER_PSEUDO;
            }
            // In AI game, the human player (first one added) always starts.
            $this->currentPlayer = $playerPseudo;
        }

        if ($this->isFull()) {
            $this->startGame();
        }

        return true;
    }

    /**
     * Returns the current game board.
     * @return array The game board.
     */
    public function getBoard(): array {
        return $this->board;
    }

    /**
     * Returns the current player's pseudo.
     * @return string The current player's pseudo.
     */
    public function getCurrentPlayer(): string {
        return $this->currentPlayer;
    }

    /**
     * Returns the list of players' pseudos.
     * @return array The list of players' pseudos.
     */
    public function getPlayers(): array {
        return $this->players;
    }

    /**
     * Returns the game ID.
     * @return string The game ID.
     */
    public function getGameId(): string {
        return $this->gameId;
    }

    /**
     * Returns the game name.
     * @return string The game name.
     */
    public function getGameName(): string {
        return $this->gameName;
    }

    /**
     * Returns the game status (e.g., 'waiting', 'in_progress', 'finished').
     * @return string The game status.
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * Returns the winner of the game.
     * @return ?string The winner's pseudo, or null if there is no winner or game is not finished.
     */
    public function getWinner(): ?string {
        return $this->winner;
    }

    /**
     * Checks if the game is against AI.
     * @return bool True if it's an AI game, false otherwise.
     */
    public function isAiGame(): bool {
        return $this->isAiGame;
    }

    /**
     * Returns the AI difficulty.
     * @return int The AI difficulty.
     */
    public function getAiDifficulty(): int {
        return $this->aiDifficulty;
    }

    /**
     * Checks if the game has reached the maximum number of players (2).
     * @return bool True if the game is full, false otherwise.
     */
    public function isFull(): bool {
        return count($this->players) === 2;
    }

    /**
     * Starts the game if it is full and waiting for players.
     * Sets the status to 'in_progress' and assigns the current player.
     * @return bool True if the game was successfully started, false otherwise.
     */
    public function startGame(): bool {
        if ($this->isFull() && $this->status === 'waiting') {
            $this->status = 'in_progress';
            // If currentPlayer is not set yet (e.g. non-AI game and addPlayer didn't set it,
            // or if we want to ensure first player in array always starts)
            if(empty($this->currentPlayer) && !empty($this->players[0])) {
                $this->currentPlayer = $this->players[0];
            }
            return true;
        }
        return false;
    }

    /**
     * Drops a piece into the specified column for the current player.
     * Does not yet check for wins or switch players.
     * @param int $column The column index to drop the piece into.
     * @param string $playerPseudo The pseudo of the player attempting to drop the piece.
     * @return bool True if the piece was dropped successfully, false otherwise.
     */
    public function dropPiece(int $column, string $playerPseudo): bool {
        if ($this->status !== 'in_progress') {
            // error_log("Game not in progress");
            return false;
        }
        if ($playerPseudo !== $this->currentPlayer) {
            // error_log("Not player's turn: expected {$this->currentPlayer}, got {$playerPseudo}");
            return false;
        }
        if (!$this->isValidLocation($column)) {
            // error_log("Invalid location: column {$column}");
            return false;
        }

        $row = $this->getNextOpenRow($column);
        if ($row === null) { // Should be caught by isValidLocation if top is full, but good practice
            // error_log("No open row in column {$column}, though isValidLocation passed");
            return false;
        }

        $this->board[$row][$column] = $playerPseudo;

        if ($this->checkWin($playerPseudo)) {
            $this->status = 'finished';
            $this->winner = $playerPseudo;
            return true;
        }

        if ($this->checkDraw()) {
            $this->status = 'finished_draw';
            return true;
        }

        $this->switchPlayer();
        return true;
    }

    /**
     * Checks if the specified column is a valid location to drop a piece.
     * A column is valid if it's within bounds and not full (topmost cell is empty).
     * @param int $column The column index to check.
     * @return bool True if the location is valid, false otherwise.
     */
    public function isValidLocation(int $column): bool {
        if ($column < 0 || $column >= self::COLS) {
            return false; // Column out of bounds
        }
        // Check if the topmost cell in the column is empty (0)
        if ($this->board[0][$column] === 0) {
            return true;
        }
        return false; // Column is full
    }

    /**
     * Gets the next available open row in the specified column.
     * Iterates from bottom up.
     * @param int $column The column index.
     * @return int|null The row index if an empty cell is found, or null if the column is full or invalid.
     */
    public function getNextOpenRow(int $column): ?int {
        if ($column < 0 || $column >= self::COLS) {
            return null; // Column out of bounds
        }
        for ($r = self::ROWS - 1; $r >= 0; $r--) {
            if ($this->board[$r][$column] === 0) {
                return $r;
            }
        }
        return null; // Column is full
    }

    /**
     * Checks if the specified player has won by connecting WINNING_LENGTH pieces.
     * @param string $playerPseudo The pseudo of the player (piece) to check for a win.
     * @return bool True if the player has won, false otherwise.
     */
    public function checkWin(string $playerPseudo): bool {
        $piece = $playerPseudo; // Assuming player pseudo is used as piece identifier

        // Horizontal Check
        for ($r = 0; $r < self::ROWS; $r++) {
            for ($c = 0; $c <= self::COLS - self::WINNING_LENGTH; $c++) {
                $count = 0;
                for ($i = 0; $i < self::WINNING_LENGTH; $i++) {
                    if ($this->board[$r][$c + $i] === $piece) {
                        $count++;
                    } else {
                        break;
                    }
                }
                if ($count === self::WINNING_LENGTH) return true;
            }
        }

        // Vertical Check
        for ($c = 0; $c < self::COLS; $c++) {
            for ($r = 0; $r <= self::ROWS - self::WINNING_LENGTH; $r++) {
                $count = 0;
                for ($i = 0; $i < self::WINNING_LENGTH; $i++) {
                    if ($this->board[$r + $i][$c] === $piece) {
                        $count++;
                    } else {
                        break;
                    }
                }
                if ($count === self::WINNING_LENGTH) return true;
            }
        }

        // Positive Diagonal Check (bottom-left to top-right)
        for ($r = self::ROWS - 1; $r >= self::WINNING_LENGTH - 1; $r--) {
            for ($c = 0; $c <= self::COLS - self::WINNING_LENGTH; $c++) {
                $count = 0;
                for ($i = 0; $i < self::WINNING_LENGTH; $i++) {
                    if ($this->board[$r - $i][$c + $i] === $piece) {
                        $count++;
                    } else {
                        break;
                    }
                }
                if ($count === self::WINNING_LENGTH) return true;
            }
        }

        // Negative Diagonal Check (top-left to bottom-right)
        for ($r = 0; $r <= self::ROWS - self::WINNING_LENGTH; $r++) {
            for ($c = 0; $c <= self::COLS - self::WINNING_LENGTH; $c++) {
                $count = 0;
                for ($i = 0; $i < self::WINNING_LENGTH; $i++) {
                    if ($this->board[$r + $i][$c + $i] === $piece) {
                        $count++;
                    } else {
                        break;
                    }
                }
                if ($count === self::WINNING_LENGTH) return true;
            }
        }

        return false;
    }

    /**
     * Checks if the game is a draw (board is full and no winner).
     * This method assumes checkWin was called prior and no winner was found.
     * @return bool True if the game is a draw, false otherwise.
     */
    public function checkDraw(): bool {
        // Check if the board is full
        for ($r = 0; $r < self::ROWS; $r++) {
            for ($c = 0; $c < self::COLS; $c++) {
                if ($this->board[$r][$c] === 0) { // 0 represents an empty cell
                    return false; // Found an empty cell, so not a draw
                }
            }
        }
        // If no empty cells are found, the board is full.
        // Since checkWin is called before this, if we reach here, it means no one has won.
        return true;
    }

    /**
     * Switches the current player to the other player.
     * Assumes there are exactly two players in the $this->players array.
     */
    public function switchPlayer(): void {
        if (count($this->players) === 2) {
            if ($this->currentPlayer === $this->players[0]) {
                $this->currentPlayer = $this->players[1];
            } else {
                $this->currentPlayer = $this->players[0];
            }
        }
        // If not 2 players, currentPlayer remains unchanged. This state should ideally be prevented by game logic.
    }

    /**
     * Returns the full game state as an associative array.
     * @return array The game state.
     */
    public function getState(): array {
        return [
            'gameId' => $this->getGameId(),
            'gameName' => $this->getGameName(),
            'rows' => self::ROWS,
            'cols' => self::COLS,
            'board' => $this->getBoard(),
            'currentPlayer' => $this->getCurrentPlayer(),
            'players' => $this->getPlayers(),
            'status' => $this->getStatus(),
            'winner' => $this->getWinner(),
            'isAiGame' => $this->isAiGame(),
            'aiDifficulty' => $this->getAiDifficulty(),
            'isFull' => $this->isFull(),
        ];
    }

    /**
     * Creates a Connect4Game instance from a state array.
     * @param array $state The state array, typically from getState().
     * @return Connect4Game The game instance.
     * @throws InvalidArgumentException If the state array is missing required fields.
     */
    public static function fromState(array $state): Connect4Game {
        $requiredKeys = [
            'gameId', 'gameName', 'isAiGame', 'aiDifficulty',
            'board', 'currentPlayer', 'players', 'status', 'winner',
            'rows', 'cols' // rows and cols are for validation/info, not directly set on consts
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $state)) {
                throw new InvalidArgumentException("State array is missing required key: $key");
            }
        }

        // Validate rows and cols from state against class constants for consistency
        if ($state['rows'] !== self::ROWS || $state['cols'] !== self::COLS) {
            throw new InvalidArgumentException(
                "Board dimensions in state (Rows: {$state['rows']}, Cols: {$state['cols']}) " .
                "do not match class constants (Rows: " . self::ROWS . ", Cols: " . self::COLS . ")"
            );
        }

        $game = new Connect4Game($state['gameId'], $state['gameName'], $state['isAiGame'], $state['aiDifficulty'], false);

        $game->board = $state['board'];
        $game->currentPlayer = $state['currentPlayer'];
        $game->players = $state['players'];
        $game->status = $state['status'];
        $game->winner = $state['winner'];
        // isAiGame and aiDifficulty are already set by constructor.
        // gameId and gameName are also set by constructor.

        return $game;
    }
}

?>
