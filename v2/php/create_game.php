<?php

require_once 'Connect4Game.php';

header('Content-Type: application/json');

define('GAMES_DATA_DIR', __DIR__ . '/../games_data/');

// Ensure the games_data directory exists
if (!is_dir(GAMES_DATA_DIR)) {
    if (!mkdir(GAMES_DATA_DIR, 0777, true) && !is_dir(GAMES_DATA_DIR)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create games data directory. Check server permissions.']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Only POST requests are accepted.']);
    exit;
}

// Input validation
if (empty($_POST['playerPseudo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'playerPseudo is required.']);
    exit;
}

$playerPseudo = htmlspecialchars($_POST['playerPseudo']); // Basic sanitization
$gameName = isset($_POST['gameName']) ? htmlspecialchars($_POST['gameName']) : 'New Connect 4 Game';
$isAiGameInput = $_POST['isAiGame'] ?? 'false';
$isAiGame = filter_var($isAiGameInput, FILTER_VALIDATE_BOOLEAN);
$aiDifficulty = isset($_POST['aiDifficulty']) ? (int)$_POST['aiDifficulty'] : 5;

// Game ID Generation
$gameId = uniqid('game_', true);

// Create Connect4Game Instance
try {
    $game = new Connect4Game($gameId, $gameName, $isAiGame, $aiDifficulty);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to initialize game: ' . $e->getMessage()]);
    exit;
}

// Add Creator as Player
$playerAdded = $game->addPlayer($playerPseudo);
if (!$playerAdded) {
    // This case should ideally not happen for a new game unless playerPseudo is invalid,
    // or if addPlayer logic changes to prevent adding even the first player under some conditions.
    // For now, assume it's a general error if the first player cannot be added.
    http_response_code(400); // Or 500 if it's an internal logic error
    echo json_encode(['error' => 'Failed to add player to the game.']);
    exit;
}
// Note: addPlayer in Connect4Game handles adding AI automatically if $isAiGame is true
// and one player (the human) has been added.

// Save Game State
$gameState = $game->getState();
$filePath = GAMES_DATA_DIR . $gameId . '.json';

if (file_put_contents($filePath, json_encode($gameState, JSON_PRETTY_PRINT))) {
    http_response_code(201); // 201 Created
    echo json_encode([
        'success' => true,
        'message' => 'Game created successfully.',
        'gameId' => $gameId, // It's good to return the gameId explicitly
        'game' => $gameState
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save game state. Check server permissions.']);
}

?>
