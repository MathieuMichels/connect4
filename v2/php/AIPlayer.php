<?php

require_once 'Connect4Game.php'; // May not be strictly needed if game state is passed, but good for COLS constant etc.

class AIPlayer {
    private $aiPseudo;
    private $difficulty;

    public function __construct(string $aiPseudo = Connect4Game::AI_PLAYER_PSEUDO, int $difficulty = 5) {
        $this->aiPseudo = $aiPseudo;
        $this->difficulty = $difficulty; // Difficulty not used in this basic version yet
    }

    /**
     * Determines the AI's next move.
     * For now, implements a simple strategy: pick a random valid column.
     *
     * @param Connect4Game $game The current game object.
     * @return int|null The column number for the AI's move, or null if no valid move.
     */
    public function getBestMove(Connect4Game $game): ?int {
        $validColumns = [];
        for ($col = 0; $col < Connect4Game::COLS; $col++) {
            if ($game->isValidLocation($col)) {
                $validColumns[] = $col;
            }
        }

        if (empty($validColumns)) {
            return null; // No valid moves
        }

        // Simple strategy: pick a random valid column
        $randomIndex = array_rand($validColumns);
        return $validColumns[$randomIndex];

        // Placeholder for future Minimax or more advanced logic based on $this->difficulty
        // switch ($this->difficulty) {
        //     case 1: // Easiest
        //         // return simple_move_strategy($game);
        //     case 5: // Medium
        //         // return minimax_strategy($game, $this->aiPseudo, 4); // Depth 4 for medium
        //     case 7: // Hardest
        //         // return minimax_strategy($game, $this->aiPseudo, 6); // Depth 6 for hard
        //     default:
        //         // return random_valid_move($validColumns);
        // }
    }

    // Future methods for Minimax:
    // private function minimax(Connect4Game $board_node, int $depth, bool $maximizingPlayer, string $playerPiece, string $opponentPiece) { ... }
    // private function scorePosition(Connect4Game $board_node, string $playerPiece, string $opponentPiece) { ... }
    // private function evaluateWindow(array $window, string $playerPiece, string $opponentPiece) { ... }
}
?>
