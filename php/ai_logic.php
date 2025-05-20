<?php

// Helper function: is_valid_location
function is_valid_location_ai($board, $col, $current_rows, $current_cols) {
    return $col >= 0 && $col < $current_cols && isset($board[0][$col]) && $board[0][$col] == 0;
}

// Helper function: drop_piece
function drop_piece_ai(&$board, $row, $col, $piece) {
    $board[$row][$col] = $piece;
}

// Helper function: get_next_open_row
function get_next_open_row_ai($board, $col, $current_rows) {
    for ($r = $current_rows - 1; $r >= 0; $r--) {
        if (isset($board[$r][$col]) && $board[$r][$col] == 0) {
            return $r;
        }
    }
    return -1; 
}

// Helper function: winning_move
function winning_move_ai($board, $piece, $current_rows, $current_cols) {
    $WINNING_LENGTH = 4;

    for ($c = 0; $c <= $current_cols - $WINNING_LENGTH; $c++) {
        for ($r = 0; $r < $current_rows; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r][$c + $i]) && $board[$r][$c + $i] == $piece) $count++;
            }
            if ($count == $WINNING_LENGTH) return true;
        }
    }
    for ($c = 0; $c < $current_cols; $c++) {
        for ($r = 0; $r <= $current_rows - $WINNING_LENGTH; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r + $i][$c]) && $board[$r + $i][$c] == $piece) $count++;
            }
            if ($count == $WINNING_LENGTH) return true;
        }
    }
    for ($c = 0; $c <= $current_cols - $WINNING_LENGTH; $c++) {
        for ($r = 0; $r <= $current_rows - $WINNING_LENGTH; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r + $i][$c + $i]) && $board[$r + $i][$c + $i] == $piece) $count++;
            }
            if ($count == $WINNING_LENGTH) return true;
        }
    }
    for ($c = 0; $c <= $current_cols - $WINNING_LENGTH; $c++) {
        for ($r = $WINNING_LENGTH - 1; $r < $current_rows; $r++) {
            $count = 0;
            for ($i = 0; $i < $WINNING_LENGTH; $i++) {
                if (isset($board[$r - $i][$c + $i]) && $board[$r - $i][$c + $i] == $piece) $count++;
            }
            if ($count == $WINNING_LENGTH) return true;
        }
    }
    return false;
}

// Helper function: evaluate_window
function evaluate_window_ai($window, $ai_piece_val, $human_piece_val) {
    $score = 0;
    
    $counts = array_count_values($window); // Counts occurrences of each piece value in the window
    $my_pieces = isset($counts[$ai_piece_val]) ? $counts[$ai_piece_val] : 0;
    $opp_pieces = isset($counts[$human_piece_val]) ? $counts[$human_piece_val] : 0;
    $empty_slots = isset($counts[0]) ? $counts[0] : 0; // Assuming 0 is empty

    if ($my_pieces == 4) $score += 1000; // Strong preference for winning
    elseif ($my_pieces == 3 && $empty_slots == 1) $score += 10; // Three in a row with an empty slot
    elseif ($my_pieces == 2 && $empty_slots == 2) $score += 5; // Two in a row with two empty slots

    if ($opp_pieces == 4) $score -= 10000; // Opponent wins, very strong negative preference
    elseif ($opp_pieces == 3 && $empty_slots == 1) $score -= 80; // Opponent has three in a row, block
    elseif ($opp_pieces == 2 && $empty_slots == 2) $score -= 8; // Opponent has two in a row

    return $score;
}

// Helper function: score_position
function score_position_ai($board, $ai_piece_val, $human_piece_val, $current_rows, $current_cols) {
    $score = 0;
    $WINNING_LENGTH = 4;

    // Score center column (heuristic: center control is good)
    $center_col_idx = floor($current_cols / 2);
    for ($r = 0; $r < $current_rows; $r++) {
        if (isset($board[$r][$center_col_idx]) && $board[$r][$center_col_idx] == $ai_piece_val) $score += 3;
    }

    // Score horizontal, vertical, and diagonals using evaluate_window
    for ($r = 0; $r < $current_rows; $r++) { // Horizontal
        for ($c = 0; $c <= $current_cols - $WINNING_LENGTH; $c++) {
            $window = array_slice($board[$r], $c, $WINNING_LENGTH);
            $score += evaluate_window_ai($window, $ai_piece_val, $human_piece_val);
        }
    }
    for ($c = 0; $c < $current_cols; $c++) { // Vertical
        $col_array = array_column($board, $c);
        for ($r = 0; $r <= $current_rows - $WINNING_LENGTH; $r++) {
            $window = array_slice($col_array, $r, $WINNING_LENGTH);
            $score += evaluate_window_ai($window, $ai_piece_val, $human_piece_val);
        }
    }
    for ($r = 0; $r <= $current_rows - $WINNING_LENGTH; $r++) { // Positively sloped diagonal
        for ($c = 0; $c <= $current_cols - $WINNING_LENGTH; $c++) {
            $window = [];
            for ($i = 0; $i < $WINNING_LENGTH; $i++) $window[] = $board[$r + $i][$c + $i];
            $score += evaluate_window_ai($window, $ai_piece_val, $human_piece_val);
        }
    }
    for ($r = $WINNING_LENGTH - 1; $r < $current_rows; $r++) { // Negatively sloped diagonal
        for ($c = 0; $c <= $current_cols - $WINNING_LENGTH; $c++) {
            $window = [];
            for ($i = 0; $i < $WINNING_LENGTH; $i++) $window[] = $board[$r - $i][$c + $i];
            $score += evaluate_window_ai($window, $ai_piece_val, $human_piece_val);
        }
    }
    return $score;
}

// Helper function: is_terminal_node
function is_terminal_node_ai($board, $ai_piece_val, $human_piece_val, $current_rows, $current_cols) {
    return winning_move_ai($board, $human_piece_val, $current_rows, $current_cols) ||
           winning_move_ai($board, $ai_piece_val, $current_rows, $current_cols) ||
           count(get_valid_locations_ai($board, $current_rows, $current_cols)) == 0;
}

// Minimax algorithm (recursive part)
function minimax_recursive($board, $depth, $alpha, $beta, $maximizingPlayer, $ai_piece_val, $human_piece_val, $current_rows, $current_cols) {
    $valid_locations = get_valid_locations_ai($board, $current_rows, $current_cols);
    $is_terminal = is_terminal_node_ai($board, $ai_piece_val, $human_piece_val, $current_rows, $current_cols);

    if ($depth == 0 || $is_terminal) {
        if ($is_terminal) {
            if (winning_move_ai($board, $ai_piece_val, $current_rows, $current_cols)) return [null, 10000000]; // AI wins
            if (winning_move_ai($board, $human_piece_val, $current_rows, $current_cols)) return [null, -10000000]; // Human wins
            return [null, 0]; // Draw
        }
        // Depth is zero, return heuristic score
        return [null, score_position_ai($board, $ai_piece_val, $human_piece_val, $current_rows, $current_cols)];
    }

    if ($maximizingPlayer) { // AI's turn (Maximizer)
        $value = -INF;
        $column = $valid_locations[array_rand($valid_locations)]; // Default to a random valid move
        foreach ($valid_locations as $col) {
            $row = get_next_open_row_ai($board, $col, $current_rows);
            if ($row === -1) continue; // Should not happen if $col is from $valid_locations
            $b_copy = $board; // Create a copy of the board for simulation
            drop_piece_ai($b_copy, $row, $col, $ai_piece_val);
            $new_score = minimax_recursive($b_copy, $depth - 1, $alpha, $beta, false, $ai_piece_val, $human_piece_val, $current_rows, $current_cols)[1];
            if ($new_score > $value) {
                $value = $new_score;
                $column = $col;
            }
            $alpha = max($alpha, $value);
            if ($alpha >= $beta) break; // Alpha-beta pruning
        }
        return [$column, $value];
    } else { // Human's turn (Minimizer)
        $value = INF;
        $column = $valid_locations[array_rand($valid_locations)]; // Default to a random valid move
        foreach ($valid_locations as $col) {
            $row = get_next_open_row_ai($board, $col, $current_rows);
            if ($row === -1) continue;
            $b_copy = $board;
            drop_piece_ai($b_copy, $row, $col, $human_piece_val);
            $new_score = minimax_recursive($b_copy, $depth - 1, $alpha, $beta, true, $ai_piece_val, $human_piece_val, $current_rows, $current_cols)[1];
            if ($new_score < $value) {
                $value = $new_score;
                $column = $col;
            }
            $beta = min($beta, $value);
            if ($alpha >= $beta) break; // Alpha-beta pruning
        }
        return [$column, $value];
    }
}

// Helper function: get_valid_locations
function get_valid_locations_ai($board, $current_rows, $current_cols) {
    $valid_locations = [];
    for ($col = 0; $col < $current_cols; $col++) {
        if (is_valid_location_ai($board, $col, $current_rows, $current_cols)) {
            $valid_locations[] = $col;
        }
    }
    return $valid_locations;
}

// Main function to be called from play_move.php
function get_ai_column_move($board_input, $current_rows, $current_cols, $ai_difficulty_level, $ai_piece_val, $human_piece_val) {
    
    $valid_locations = get_valid_locations_ai($board_input, $current_rows, $current_cols);
    if (empty($valid_locations)) {
        error_log("AI Error: get_ai_column_move called with no valid locations."); // Keep this error
        return null; // No valid moves left
    }
    
    // Difficulty mapping: depth = difficulty level. Max depth around 5-7 is reasonable for PHP.
    $depth = min(max(1, (int)$ai_difficulty_level), 7);

    // Call minimax
    $result = minimax_recursive($board_input, $depth, -INF, INF, true, $ai_piece_val, $human_piece_val, $current_rows, $current_cols);
    
    // Fallback if minimax returns null column but valid moves exist (e.g., only losing moves found at max depth)
    if ($result[0] === null && !empty($valid_locations)) {
        // error_log("AI Warning: Minimax returned null column, picking random valid move. Depth: $depth"); // Remove this warning for polish
        return $valid_locations[array_rand($valid_locations)];
    }
    
    return $result[0]; // Return only the column index
}

?>
