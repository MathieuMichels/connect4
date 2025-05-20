<?php

const ROWS = 6;
const COLS = 7;
const WINNING_LENGTH = 4;

function is_valid_location_php($board, $col) {
    // Check if the column is within bounds and the top row is empty (0)
    return $col >= 0 && $col < COLS && isset($board[0][$col]) && $board[0][$col] == 0;
}

function get_next_open_row_php($board, $col) {
    for ($r = ROWS - 1; $r >= 0; $r--) {
        if (isset($board[$r][$col]) && $board[$r][$col] == 0) {
            return $r;
        }
    }
    return -1; // Should not happen if is_valid_location was called
}

function check_win_php($board, $piece_pseudo, $ROWS, $COLS, $WINNING_LENGTH) {
    // Check horizontal locations
    for ($c = 0; $c <= $COLS - $WINNING_LENGTH; $c++) {
        for ($r = 0; $r < $ROWS; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r][$c + $i]) && $board[$r][$c + $i] == $piece_pseudo) {
                    $count++;
                }
            }
            if ($count == $WINNING_LENGTH) {
                return true;
            }
        }
    }

    // Check vertical locations
    for ($c = 0; $c < $COLS; $c++) {
        for ($r = 0; $r <= $ROWS - $WINNING_LENGTH; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r + $i][$c]) && $board[$r + $i][$c] == $piece_pseudo) {
                    $count++;
                }
            }
            if ($count == $WINNING_LENGTH) {
                return true;
            }
        }
    }

    // Check positively sloped diagonals
    for ($c = 0; $c <= $COLS - $WINNING_LENGTH; $c++) {
        for ($r = 0; $r <= $ROWS - $WINNING_LENGTH; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r + $i][$c + $i]) && $board[$r + $i][$c + $i] == $piece_pseudo) {
                    $count++;
                }
            }
            if ($count == $WINNING_LENGTH) {
                return true;
            }
        }
    }

    // Check negatively sloped diagonals
    for ($c = 0; $c <= $COLS - $WINNING_LENGTH; $c++) {
        for ($r = $WINNING_LENGTH - 1; $r < $ROWS; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r - $i][$c + $i]) && $board[$r - $i][$c + $i] == $piece_pseudo) {
                    $count++;
                }
            }
            if ($count == $WINNING_LENGTH) {
                return true;
            }
        }
    }
    return false;
}

function check_draw_php($board) {
    // Check if the top row is full
    for ($c = 0; $c < COLS; $c++) {
        if (!isset($board[0][$c]) || $board[0][$c] == 0) {
            return false; // Found an empty spot, not a draw
        }
    }
    return true; // No empty spots in the top row, it's a draw
}

function update_stats_php($player1_pseudo, $player2_pseudo, $is_draw = false) {
    $users_file = '../users.json';
    if (!file_exists($users_file)) {
        return false; // Users file not found
    }

    $users_content = file_get_contents($users_file);
    $users = json_decode($users_content, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($users)) {
        return false; // Error decoding JSON or not an array
    }

    $player1_found = false;
    $player2_found = false;

    foreach ($users as $key => $user) {
        if (isset($user['pseudo'])) {
            if ($user['pseudo'] === $player1_pseudo) {
                if ($is_draw) {
                    $users[$key]['nulls'] = (isset($users[$key]['nulls']) ? $users[$key]['nulls'] : 0) + 1;
                } else { // player1 is the winner
                    $users[$key]['wins'] = (isset($users[$key]['wins']) ? $users[$key]['wins'] : 0) + 1;
                }
                $player1_found = true;
            } elseif ($user['pseudo'] === $player2_pseudo) {
                if ($is_draw) {
                    $users[$key]['nulls'] = (isset($users[$key]['nulls']) ? $users[$key]['nulls'] : 0) + 1;
                } else { // player2 is the loser
                    $users[$key]['defeats'] = (isset($users[$key]['defeats']) ? $users[$key]['defeats'] : 0) + 1;
                }
                $player2_found = true;
            }
        }
        if ($player1_found && $player2_found) {
            break;
        }
    }
    
    // If one of the players is "IA", we don't update its stats in users.json
    // The logic assumes player1 is winner if not draw, player2 is loser if not draw.
    // If player2 is IA and lost, no update needed for IA. If player1 is IA and won, no update needed for IA.
    // If draw and one player is IA, only the human player's 'nulls' count is updated.

    // Determine winner and loser for logging based on the function's parameters.
    // $player1_pseudo is the winner if not $is_draw. $player2_pseudo is the loser if not $is_draw.
    $log_winner = $is_draw ? "N/A (Draw)" : $player1_pseudo;
    $log_loser = $is_draw ? "N/A (Draw)" : $player2_pseudo;

    // Log before writing, ensuring $users is captured before modification for the log.
    // Note: $users array is modified in place in the loop above. To log "before", this log should ideally be earlier,
    // or we log the state of specific users. For simplicity, logging the current state of $users.
    // error_log("Updating stats. Winner: " . $log_winner . " Loser: " . $log_loser . " Is Draw: " . ($is_draw ? "Yes" : "No") . " | users.json state for update: " . json_encode($users)); // Debug log removed


    if (($player1_found || $player1_pseudo === 'AI_PLAYER' || $player1_pseudo === 'IA') && ($player2_found || $player2_pseudo === 'AI_PLAYER' || $player2_pseudo === 'IA')) {
        // Allow 'IA' for compatibility if old games exist, but new ones use 'AI_PLAYER'
        return file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
    }
    
    // error_log("Stats update skipped: One or both players not found (and not AI_PLAYER/IA). Player1: " . $player1_pseudo . " Player2: " . $player2_pseudo); // Debug log removed
    return false; // One or both players not found (and not AI_PLAYER/IA)
}

?>
