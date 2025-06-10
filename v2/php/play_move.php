<?php

require_once 'Connect4Game.php';
require_once 'AIPlayer.php'; // Added for AI integration

header('Content-Type: application/json');

define('GAMES_DATA_DIR', __DIR__ . '/../games_data/');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Only POST requests are accepted.']);
    exit;
}

// Input validation
$requiredParams = ['game_id', 'playerPseudo', 'column'];
foreach ($requiredParams as $param) {
    if (empty($_POST[$param]) && $_POST[$param] !== '0') { // Allow '0' for column index
        http_response_code(400);
        echo json_encode(['error' => "$param is required."]);
        exit;
    }
}

$gameId = $_POST['game_id'];
$playerPseudo = htmlspecialchars($_POST['playerPseudo']); // Basic sanitization
$columnInput = $_POST['column'];

if (!is_numeric($columnInput)) {
    http_response_code(400);
    echo json_encode(['error' => 'Column must be an integer.']);
    exit;
}
$column = (int)$columnInput;

// Sanitize game_id
$safeGameId = basename($gameId);
if (empty($safeGameId) || $safeGameId !== $gameId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid game_id format.']);
    exit;
}

$filePath = GAMES_DATA_DIR . $safeGameId . '.json';

if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Game not found.']);
    exit;
}

$jsonData = file_get_contents($filePath);
if ($jsonData === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read game data file.']);
    exit;
}

$gameStateData = json_decode($jsonData, true);
if ($gameStateData === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid game data format: ' . json_last_error_msg()]);
    exit;
}

// Reconstruct Game Object
try {
    $game = Connect4Game::fromState($gameStateData);
} catch (InvalidArgumentException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to reconstruct game from state: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) { // Catch any other potential exceptions from fromState or constructor
    http_response_code(500);
    echo json_encode(['error' => 'An unexpected error occurred while loading the game: ' . $e->getMessage()]);
    exit;
}

// Attempt to Make a Move
$moveResult = $game->dropPiece($column, $playerPseudo);

if (!$moveResult) {
    // More specific error messages could be obtained if Connect4Game class provided them.
    // For now, using a general message.
    // We can check game status to provide slightly more context.
    $currentStatusAfterHumanMove = $game->getStatus(); // Status after human attempted move
    $errorMsg = 'Invalid move.';

    if ($currentStatusAfterHumanMove === 'finished' || $currentStatusAfterHumanMove === 'finished_draw') {
        $errorMsg = 'Game is already finished.';
    } elseif ($game->getCurrentPlayer() !== $playerPseudo && $currentStatusAfterHumanMove === 'in_progress') {
        // Check whose turn it was *before* dropPiece might have switched it.
        // This requires dropPiece to not switch player if the move was invalid.
        // A better check: get original state's current player, or have dropPiece return more info.
        // For now, this might be slightly off if dropPiece switches player even on some failed attempts.
        // However, the current dropPiece returns false for "not player's turn" *before* switching.
        $errorMsg = 'Not your turn.';
    } elseif (!$game->isValidLocation($column)) {
        $errorMsg = 'Invalid column or column is full.';
    } else if ($currentStatusAfterHumanMove !== 'in_progress' && $game->getStatus() === 'in_progress') {
        // This implies the game was 'waiting' or some other state, and dropPiece failed.
        $errorMsg = 'Game not in progress or move not allowed in current game state.';
    }

    http_response_code(400);
    echo json_encode(['error' => $errorMsg, 'currentState' => $game->getState()]); // Sending current state for client to sync
    exit;
}

// --- AI's Turn, if applicable ---
$statusAfterHumanMove = $game->getStatus();
$currentPlayerForAIState = $game->getCurrentPlayer(); // Player after human move (and potential switch)

if ($statusAfterHumanMove === 'in_progress' &&
    $game->isAiGame() &&
    $currentPlayerForAIState === Connect4Game::AI_PLAYER_PSEUDO) {

    $ai = new AIPlayer(Connect4Game::AI_PLAYER_PSEUDO, $game->getAiDifficulty());
    $aiColumn = $ai->getBestMove($game);

    if ($aiColumn !== null) {
        // AI makes its move
        $aiMoveResult = $game->dropPiece($aiColumn, Connect4Game::AI_PLAYER_PSEUDO);

        if (!$aiMoveResult) {
            // This scenario implies an issue with AI's move logic or game state integrity.
            // For example, if getBestMove returned a column that became invalid,
            // or if dropPiece had an internal error.
            // Log this for debugging. The game state will be saved as is (after human move).
            error_log("AI move failed. GameID: {$safeGameId}, AI Column: {$aiColumn}, Player: " . Connect4Game::AI_PLAYER_PSEUDO);
            // We don't send an error to client here, as human's move was successful.
            // The game state will reflect the board after human's move only if AI fails catastrophically.
        }
        // dropPiece internally handles status updates (win/draw) and player switching.
        // If AI wins/draws, status is updated. If game continues, it's human's turn.
    } else {
        // AI could not find a move (e.g., board full, but checkDraw should have caught it for human).
        // This is highly unlikely if AI logic and game logic are correct. Log for debugging.
        error_log("AI getBestMove returned null. GameID: {$safeGameId}");
    }
}

// Save Updated Game State (after human move and potential AI move)
$newGameState = $game->getState();

if (file_put_contents($filePath, json_encode($newGameState, JSON_PRETTY_PRINT))) {
    http_response_code(200); // OK
    echo json_encode($newGameState);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save updated game state.']);
}

?>
