<?php
header('Content-Type: application/json');

$games_file = '../games.json';
$response_games = [];

if (file_exists($games_file)) {
    $games_content = file_get_contents($games_file);
    if ($games_content === false) {
        // Error reading file
        echo json_encode(['error' => 'Failed to read games.json']);
        exit;
    }
    
    $games = json_decode($games_content, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($games)) {
        foreach ($games as $game) {
            if (isset($game['status']) && $game['status'] === 'waiting') {
                $response_games[] = $game;
            }
        }
    } else {
        // Handle JSON decode error or if $games is not an array
        // If the file is empty or invalid JSON, treat as no games available
        // No need to output an error, just an empty list.
    }
}

echo json_encode($response_games);
?>
