<?php
session_start();
header('Content-Type: application/json');

$response = ["success" => false, "message" => "An unknown error occurred."];

if (!isset($_SESSION['pseudo'])) {
    $response["message"] = "User not logged in.";
    echo json_encode($response);
    exit;
}

if (!isset($_POST['game_id'])) {
    $response["message"] = "Game ID not provided.";
    echo json_encode($response);
    exit;
}

$game_id_to_join = $_POST['game_id'];
$user_pseudo = $_SESSION['pseudo'];
$games_file = '../games.json';

if (!file_exists($games_file) || filesize($games_file) === 0) {
    $response["message"] = "Games file not found or is empty.";
    echo json_encode($response);
    exit;
}

$games_content = file_get_contents($games_file);
$games = json_decode($games_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $response["message"] = "Error decoding games.json.";
    echo json_encode($response);
    exit;
}

$game_found = false;
$game_index = -1;

foreach ($games as $index => $game) {
    if (isset($game['id']) && $game['id'] === $game_id_to_join) {
        $game_found = true;
        $game_index = $index;
        break;
    }
}

if (!$game_found) {
    $response["message"] = "Game not found.";
    echo json_encode($response);
    exit;
}

// --- Validations ---
if ($games[$game_index]['status'] !== 'waiting') {
    $response["message"] = "Game is not available for joining.";
    echo json_encode($response);
    exit;
}

if (in_array($user_pseudo, $games[$game_index]['players'])) {
    $response["message"] = "You are already in this game.";
    echo json_encode($response);
    exit;
}

if (count($games[$game_index]['players']) >= 2) {
    $response["message"] = "Game is full.";
    echo json_encode($response);
    exit;
}

// --- If validations pass ---
$games[$game_index]['players'][] = $user_pseudo;

if (count($games[$game_index]['players']) == 2) {
    $games[$game_index]['status'] = 'in_progress';
    // Set current turn to the game creator (first player in the array)
    if(isset($games[$game_index]['game_creator_pseudo'])){
        $games[$game_index]['current_turn_pseudo'] = $games[$game_index]['game_creator_pseudo'];
    } else if (!empty($games[$game_index]['players'])) {
        $games[$game_index]['current_turn_pseudo'] = $games[$game_index]['players'][0];
    }
    // Initialize an empty 6x7 board for Connect 4
    $empty_board = array_fill(0, 6, array_fill(0, 7, 0));
    $games[$game_index]['currentBoard'] = $empty_board;
}

// error_log("User " . $user_pseudo . " joined game " . $games[$game_index]['id'] . ". Game state before save: " . json_encode($games[$game_index])); // Debug log removed

if (file_put_contents($games_file, json_encode($games, JSON_PRETTY_PRINT))) {
    $response["success"] = true;
    $response["message"] = "Successfully joined game.";
    $response["game_id"] = $game_id_to_join;
} else {
    $response["message"] = "Failed to save game data.";
}

echo json_encode($response);
?>
