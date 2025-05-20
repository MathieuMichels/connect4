<?php
session_start();
header('Content-Type: application/json');
require_once 'game_logic_utils.php'; // Includes ROWS, COLS, WINNING_LENGTH constants
require_once 'ai_logic.php';         // For get_ai_column_move

$response = ["success" => false, "message" => "An unknown error occurred."];

// --- Basic Validations ---
// Initial log for play_move.php call
// if (isset($_SESSION['pseudo']) && isset($_POST['game_id']) && isset($_POST['column'])) {
    // error_log("play_move.php called by: " . $_SESSION['pseudo'] . " for game: " . $_POST['game_id'] . " column: " . $_POST['column']); // Debug log removed
// }


if (!isset($_SESSION['pseudo'])) {
    $response["message"] = "User not logged in.";
    echo json_encode($response);
    exit;
}
$user_pseudo = $_SESSION['pseudo'];

if (!isset($_POST['game_id']) || !isset($_POST['column'])) {
    $response["message"] = "Game ID or column not provided.";
    echo json_encode($response);
    exit;
}
$game_id = $_POST['game_id'];
$column = (int)$_POST['column'];

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

// --- Game Specific Validations ---
$game_index = -1;
foreach ($games as $index => $g) {
    if (isset($g['id']) && $g['id'] === $game_id) {
        $game_index = $index;
        break;
    }
}

if ($game_index === -1) {
    $response["message"] = "Game not found.";
    echo json_encode($response);
    exit;
}

$game = $games[$game_index];

if ($game['status'] !== 'in_progress') {
    $response["message"] = "Game is not currently in progress.";
    $response["game_status"] = $game['status']; // Provide current status for debugging or UI update
    echo json_encode($response);
    exit;
}

if ($game['current_turn_pseudo'] !== $user_pseudo) {
    $response["message"] = "It's not your turn.";
    echo json_encode($response);
    exit;
}

$board = $game['currentBoard'];
if (!is_valid_location_php($board, $column)) {
    $response["message"] = "Invalid move. Column is full or out of bounds.";
    echo json_encode($response);
    exit;
}

// --- Apply Move ---
$row = get_next_open_row_php($board, $column);
if ($row === -1) { // Should be caught by is_valid_location_php, but as a safeguard
    $response["message"] = "Error determining row for the move.";
    echo json_encode($response);
    exit;
}
$board[$row][$column] = $user_pseudo; // Store pseudo directly
$games[$game_index]['currentBoard'] = $board;
$game = $games[$game_index]; // Refresh $game variable after board update


// --- Check for Win/Draw ---
if (check_win_php($board, $user_pseudo, ROWS, COLS, WINNING_LENGTH)) {
    $games[$game_index]['status'] = 'finished';
    $games[$game_index]['winner_pseudo'] = $user_pseudo;
    $game = $games[$game_index]; // Refresh game state
    // error_log("Game " . $game['id'] . " ended. Status: " . $game['status'] . " Winner: " . $game['winner_pseudo']); // Debug log removed
    
    // Determine loser
    $loser_pseudo = '';
    if (isset($game['players']) && is_array($game['players'])) {
        foreach ($game['players'] as $player_p) {
            if ($player_p !== $user_pseudo) {
                $loser_pseudo = $player_p;
                break;
            }
        }
    }
    if (!empty($loser_pseudo)) {
        update_stats_php($user_pseudo, $loser_pseudo, false);
    }

} elseif (check_draw_php($board)) {
    $games[$game_index]['status'] = 'finished_draw';
    $game = $games[$game_index]; // Refresh game state
    // error_log("Game " . $game['id'] . " ended. Status: " . $game['status']); // Debug log removed
    if (isset($game['players']) && is_array($game['players']) && count($game['players']) == 2) {
        // For a draw, order doesn't matter for update_stats_php as it checks both
        update_stats_php($game['players'][0], $game['players'][1], true);
    }
} else {
    // Switch turn
    $next_player_pseudo = '';
    if (isset($game['players']) && is_array($game['players'])) {
        foreach ($game['players'] as $player_p) {
            if ($player_p !== $user_pseudo) {
                $next_player_pseudo = $player_p;
                break;
            }
        }
    }
    $games[$game_index]['current_turn_pseudo'] = $next_player_pseudo;
    $game = $games[$game_index]; // Refresh game state
    // error_log("Human move processed. Board: " . json_encode($game['currentBoard']) . " Turn: " . $game['current_turn_pseudo']); // Debug log removed


    // --- AI Turn Logic (if applicable) ---
    if (isset($game['game_type']) && $game['game_type'] === 'ai' && 
        $game['status'] === 'in_progress' && // Use refreshed $game
        $game['current_turn_pseudo'] === "AI_PLAYER") {

        $human_player_pseudo = '';
        foreach ($game['players'] as $p_pseudo) {
            if ($p_pseudo !== "AI_PLAYER") {
                $human_player_pseudo = $p_pseudo;
                break;
            }
        }

        // 1. Convert board for AI: human_player_pseudo -> 1, "AI_PLAYER" -> 2, empty -> 0
        $board_for_ai = [];
        foreach ($games[$game_index]['currentBoard'] as $r_idx => $row_val) {
            $board_for_ai[$r_idx] = [];
            foreach ($row_val as $c_idx => $cell_val) {
                if ($cell_val == $human_player_pseudo) {
                    $board_for_ai[$r_idx][$c_idx] = 1; // Human piece for AI
                } elseif ($cell_val == "AI_PLAYER") {
                    $board_for_ai[$r_idx][$c_idx] = 2; // AI piece for AI
                } else {
                    $board_for_ai[$r_idx][$c_idx] = 0; // Empty
                }
            }
        }
        
        $ai_difficulty = isset($game['difficulty']) ? (int)$game['difficulty'] : 5;
        $ai_column = get_ai_column_move($board_for_ai, ROWS, COLS, $ai_difficulty, 2, 1);

        if ($ai_column !== null && is_valid_location_php($games[$game_index]['currentBoard'], $ai_column)) {
            $ai_row = get_next_open_row_php($games[$game_index]['currentBoard'], $ai_column);
            if ($ai_row !== -1) { // get_next_open_row_php returns -1 if column is full
                $games[$game_index]['currentBoard'][$ai_row][$ai_column] = "AI_PLAYER";

                if (check_win_php($games[$game_index]['currentBoard'], "AI_PLAYER", ROWS, COLS, WINNING_LENGTH)) {
                    $games[$game_index]['status'] = 'finished';
                    $games[$game_index]['winner_pseudo'] = "AI_PLAYER";
                    $game = $games[$game_index]; // Refresh
                    // error_log("AI move processed. AI chose col: " . $ai_column . " Board: " . json_encode($game['currentBoard']) . " Turn: " . $game['current_turn_pseudo']); // Debug log removed
                    // error_log("Game " . $game['id'] . " ended. Status: " . $game['status'] . " Winner: " . $game['winner_pseudo']); // Debug log removed
                    update_stats_php("AI_PLAYER", $human_player_pseudo, false); 
                } elseif (check_draw_php($games[$game_index]['currentBoard'])) {
                    $games[$game_index]['status'] = 'finished_draw';
                    $game = $games[$game_index]; // Refresh
                    // error_log("AI move processed. AI chose col: " . $ai_column . " Board: " . json_encode($game['currentBoard']) . " Turn: " . $game['current_turn_pseudo']); // Debug log removed
                    // error_log("Game " . $game['id'] . " ended. Status: " . $game['status']); // Debug log removed
                    update_stats_php($human_player_pseudo, "AI_PLAYER", true);
                } else {
                     // If game not ended by AI move, log AI move and set turn back to human
                    $games[$game_index]['current_turn_pseudo'] = $human_player_pseudo; // Switch turn back to human
                    $game = $games[$game_index]; // Refresh
                    // error_log("AI move processed. AI chose col: " . $ai_column . " Board: " . json_encode($game['currentBoard']) . " Turn: " . $game['current_turn_pseudo']); // Debug log removed
                }
            } else {
                 // error_log("AI Error: get_next_open_row_php returned -1 for a column chosen by AI. Game ID: " . $game_id . " Column: " . $ai_column); // Keep this specific AI error log
                 // Fallback: AI loses turn effectively, human gets turn back.
                 $games[$game_index]['current_turn_pseudo'] = $human_player_pseudo; 
            }
        } else {
            // error_log("AI Error: get_ai_column_move returned null or invalid column. Game ID: " . $game_id . " Chosen Column: " . var_export($ai_column, true)); // Keep this specific AI error log
            // Fallback: AI loses turn effectively, human gets turn back.
             $games[$game_index]['current_turn_pseudo'] = $human_player_pseudo;
        }
        // This specific "Switch turn back to human if game still in progress" block is now handled within the AI move logic above.
    }
}

// --- Save and Respond ---
// Reload the game state from the $games array before sending, as AI might have modified it.
$updated_game_state = $games[$game_index]; 

if (file_put_contents($games_file, json_encode($games, JSON_PRETTY_PRINT))) {
    $response["success"] = true;
    $response["message"] = "Move processed.";
    echo json_encode($updated_game_state); // Return the potentially modified game state
    exit;
} else {
    $response["message"] = "Failed to save game data after move.";
    echo json_encode($response);
    exit;
}
?>
