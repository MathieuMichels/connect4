<?php

header('Content-Type: application/json');

define('GAMES_DATA_DIR', __DIR__ . '/../games_data/');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed. Only GET requests are accepted.']);
    exit;
}

// Input validation
if (empty($_GET['game_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'game_id is required.']);
    exit;
}

$gameId = $_GET['game_id'];

// Sanitize game_id to prevent directory traversal.
// basename() is a good way to get just the filename component.
// Also ensure it doesn't contain other problematic characters if necessary,
// though for uniqid based IDs, basename should be sufficient.
$safeGameId = basename($gameId);

// Prevent empty gameId after basename (e.g. if game_id was just "/" or "..")
if (empty($safeGameId) || $safeGameId !== $gameId) {
    // The second condition ($safeGameId !== $gameId) ensures no path manipulation attempts.
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

$gameState = json_decode($jsonData, true);
if ($gameState === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid game data format: ' . json_last_error_msg()]);
    exit;
}

// Successfully loaded and decoded game state
http_response_code(200);
echo json_encode($gameState);

?>
