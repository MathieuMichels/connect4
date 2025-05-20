<?php
session_start();

if (!isset($_SESSION['pseudo'])) {
    // User not logged in, redirect to index or return error
    header('Location: ../index.php?error=not_logged_in_create');
    exit;
}

$games_file = '../games.json';
$games = [];

// Read existing games or initialize if file doesn't exist/is empty/invalid
if (file_exists($games_file) && filesize($games_file) > 0) {
    $games_content = file_get_contents($games_file);
    $decoded_games = json_decode($games_content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $games = $decoded_games;
    } else {
        // Handle JSON decode error, perhaps log it and re-initialize
        // For now, re-initialize to prevent further issues
        file_put_contents($games_file, json_encode([])); // Overwrite with empty array
        $games = [];
    }
} else {
    // Initialize with an empty array if the file doesn't exist or is empty
    file_put_contents($games_file, json_encode([]));
    $games = [];
}

// Get game name from POST, use default if empty
$gameName = !empty(trim($_POST['gameName'])) ? trim($_POST['gameName']) : "Connect4 Game";
$user_pseudo = $_SESSION['pseudo'];
$game_id = uniqid("game_");

$play_against_ai = isset($_POST['play_ai']) && $_POST['play_ai'] == 'true';
$ai_difficulty = isset($_POST['difficulty']) ? (int)$_POST['difficulty'] : 5; // Default difficulty

if ($play_against_ai) {
    // AI Game
    $new_game = [
        "id" => $game_id,
        "gameName" => $gameName,
        "players" => [$user_pseudo, "AI_PLAYER"],
        "status" => "in_progress", // Game starts immediately
        "currentBoard" => array_fill(0, 6, array_fill(0, 7, 0)), // Initial empty board
        "current_turn_pseudo" => $user_pseudo, // Human plays first
        "game_creator_pseudo" => $user_pseudo,
        "difficulty" => $ai_difficulty,
        "game_type" => "ai" // Mark as AI game
    ];
} else {
    // Human vs Human Game (existing logic)
    $new_game = [
        "id" => $game_id,
        "gameName" => $gameName,
        "players" => [$user_pseudo],
        "status" => "waiting",
        "currentBoard" => array_fill(0, 6, array_fill(0, 7, 0)), // Initialize board for HvsH too
        "current_turn_pseudo" => $user_pseudo,
        "game_creator_pseudo" => $user_pseudo,
        "game_type" => "human" // Mark as Human game
    ];
}

$games[] = $new_game;

if (file_put_contents($games_file, json_encode($games, JSON_PRETTY_PRINT))) {
    // error_log("Game created: " . $new_game['id'] . " by " . $new_game['game_creator_pseudo'] . " | Type: " . ($play_against_ai ? "AI" : "Human") . " | games.json content: " . json_encode($games)); // Debug log removed

    if (isset($_POST['source_request']) && $_POST['source_request'] == 'js_default_ai') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'game' => $new_game]);
        exit;
    } else {
        header('Location: ../index.php?game_created=' . $game_id);
        exit;
    }
} else {
    // Handle file write error
    // error_log("Error creating game: Failed to write to games.json. Game ID attempted: " . $game_id); // Debug log removed
    if (isset($_POST['source_request']) && $_POST['source_request'] == 'js_default_ai') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to save game data.']);
        exit;
    } else {
        header('Location: ../index.php?error=failed_to_save_game');
        exit;
    }
}
?>
