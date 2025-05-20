<?php
header('Content-Type: application/json');

if (!isset($_GET['game_id'])) {
    echo json_encode(['error' => 'Game ID not provided.']);
    exit;
}

$game_id = $_GET['game_id'];
$games_file = '../games.json';

if (!file_exists($games_file)) {
    echo json_encode(['error' => 'Games file not found.']);
    exit;
}

$games_content = file_get_contents($games_file);
$games = json_decode($games_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Error decoding games.json.']);
    exit;
}

$found_game = null;
foreach ($games as $game) {
    if (isset($game['id']) && $game['id'] === $game_id) {
        $found_game = $game;
        break;
    }
}

if ($found_game) {
    // Ensure all expected fields are present, even if null or default
    $response = [
        'id' => $found_game['id'],
        'gameName' => isset($found_game['gameName']) ? $found_game['gameName'] : 'Connect 4 Game',
        'players' => isset($found_game['players']) ? $found_game['players'] : [],
        'status' => isset($found_game['status']) ? $found_game['status'] : 'unknown',
        'currentBoard' => isset($found_game['currentBoard']) ? $found_game['currentBoard'] : array_fill(0, 6, array_fill(0, 7, 0)), // Default to empty board
        'current_turn_pseudo' => isset($found_game['current_turn_pseudo']) ? $found_game['current_turn_pseudo'] : null,
        'game_creator_pseudo' => isset($found_game['game_creator_pseudo']) ? $found_game['game_creator_pseudo'] : null,
        'winner_pseudo' => isset($found_game['winner_pseudo']) ? $found_game['winner_pseudo'] : null
    ];
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Game not found.']);
}
?>
