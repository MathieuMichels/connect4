<?php

$board = $_POST['board'];
$ROWS = $_POST['ROWS'];
$COLS = $_POST['COLS'];
foreach ($board as $key => $value) {
    foreach ($value as $k => $v) {
        $board[$key][$k] = intval($v);
    }
}
$ROWS = intval($ROWS);
$COLS = intval($COLS);
const PLAYER = 1;
const AI = 2;
$MAX_DEPTH = $_POST['MAX_DEPTH'];
$MAX_DEPTH = intval($MAX_DEPTH);

function is_valid_location($board, $col, $ROWS)
{
    return $board[$ROWS - 1][$col] == 0;
}


function drop_piece($board, $row, $col, $piece)
{
    $board[$row][$col] = $piece;
    return $board;
}


function get_next_open_row($board, $col, $ROWS)
{
    for ($r = 0; $r < $ROWS; $r++) {
        if ($board[$r][$col] == 0) {
            return $r;
        }
    }
}



function winning_move($board, $piece, $ROWS, $COLS)
{
    // Check horizontal locations
    for ($c = 0; $c < $COLS - 3; $c++) {
        for ($r = 0; $r < $ROWS; $r++) {
            if ($board[$r][$c] == $piece && $board[$r][$c + 1] == $piece && $board[$r][$c + 2] == $piece && $board[$r][$c + 3] == $piece) {
                return true;
            }
        }
    }

    // Check vertical locations
    for ($c = 0; $c < $COLS; $c++) {
        for ($r = 0; $r < $ROWS - 3; $r++) {
            if ($board[$r][$c] == $piece && $board[$r + 1][$c] == $piece && $board[$r + 2][$c] == $piece && $board[$r + 3][$c] == $piece) {
                return true;
            }
        }
    }

    // Check positively sloped diagonals
    for ($c = 0; $c < $COLS - 3; $c++) {
        for ($r = 0; $r < $ROWS - 3; $r++) {
            if ($board[$r][$c] == $piece && $board[$r + 1][$c + 1] == $piece && $board[$r + 2][$c + 2] == $piece && $board[$r + 3][$c + 3] == $piece) {
                return true;
            }
        }
    }

    // Check negatively sloped diagonals
    for ($c = 0; $c < $COLS - 3; $c++) {
        for ($r = 3; $r < $ROWS; $r++) {
            if ($board[$r][$c] == $piece && $board[$r - 1][$c + 1] == $piece && $board[$r - 2][$c + 2] == $piece && $board[$r - 3][$c + 3] == $piece) {
                return true;
            }
        }
    }

}


function evaluate_window($window, $piece)
{
    $score = 0;
    $opp_piece = $piece == AI ? PLAYER : AI;

    if (array_count_values($window)[$piece] == 4) {
        $score += 100;
    } elseif (array_count_values($window)[$piece] == 3 && array_count_values($window)[0] == 1) {
        $score += 5;
    } elseif (array_count_values($window)[$piece] == 2 && array_count_values($window)[0] == 2) {
        $score += 2;
    }

    if (array_count_values($window)[$opp_piece] == 3 && array_count_values($window)[0] == 1) {
        $score -= 4;
    }


    return $score;
}


function score_position($board, $piece, $ROWS, $COLS)
{
    $score = 0;

    // Score center column
    $center_array = [];
    for ($i = 0; $i < $ROWS; $i++) {
        $center_array[] = $board[$i][$COLS / 2];
    }
    $center_count = array_count_values($center_array)[$piece];
    $score += $center_count * 3;

    // Score horizontal
    for ($r = 0; $r < $ROWS; $r++) {
        $row_array = [];
        for ($i = 0; $i < $COLS; $i++) {
            $row_array[] = $board[$r][$i];
        }
        for ($c = 0; $c < $COLS - 3; $c++) {
            $window = array_slice($row_array, $c, 4);
            $score += evaluate_window($window, $piece);
        }
    }

    // Score vertical
    for ($c = 0; $c < $COLS; $c++) {
        $col_array = [];
        for ($i = 0; $i < $ROWS; $i++) {
            $col_array[] = $board[$i][$c];
        }
        for ($r = 0; $r < $ROWS - 3; $r++) {
            $window = array_slice($col_array, $r, 4);
            $score += evaluate_window($window, $piece);
        }
    }

    // Score positively sloped diagonal
    for ($r = 0; $r < $ROWS - 3; $r++) {
        for ($c = 0; $c < $COLS - 3; $c++) {
            $window = [];
            for ($i = 0; $i < 4; $i++) {
                $window[] = $board[$r + $i][$c + $i];
            }
            $score += evaluate_window($window, $piece);
        }
    }

    // Score negatively sloped diagonal
    for ($r = 0; $r < $ROWS - 3; $r++) {
        for ($c = 0; $c < $COLS - 3; $c++) {
            $window = [];
            for ($i = 0; $i < 4; $i++)
                $window[] = $board[$r + 3 - $i][$c + $i];
            $score += evaluate_window($window, $piece);
        }
    }

    return $score;
}

function is_terminal_node($board, $ROWS, $COLS)
{
    return winning_move($board, PLAYER, $ROWS, $COLS) || winning_move($board, AI, $ROWS, $COLS) || count(get_valid_locations($board, $ROWS, $COLS)) == 0;
}


function minimax($board, $depth, $alpha, $beta, $maximizingPlayer, $ROWS, $COLS)
{
    $valid_locations = get_valid_locations($board, $ROWS, $COLS);
    $is_terminal = is_terminal_node($board, $ROWS, $COLS);
    if ($depth == 0 || $is_terminal) {
        if ($is_terminal) {
            if (winning_move($board, AI, $ROWS, $COLS)) {
                return [null, 100000];
            } elseif (winning_move($board, PLAYER, $ROWS, $COLS)) {
                return [null, -100000];
            } else {  // Game is over, no more valid moves
                return [null, 0];
            }
        } else {  // Depth is zero
            return [null, score_position($board, AI, $ROWS, $COLS)];
        }
    }
    if ($maximizingPlayer) {
        $value = -10000000000;
        $index = rand(0, count($valid_locations) - 1); // random column
        $column = $valid_locations[$index];
        foreach ($valid_locations as $col) {
            $row = get_next_open_row($board, $col, $ROWS);
            $b_copy = $board;
            $b_copy = drop_piece($b_copy, $row, $col, AI);
            $new_score = minimax($b_copy, $depth - 1, $alpha, $beta, false, $ROWS, $COLS)[1];
            if ($new_score > $value) {
                $value = $new_score;
                $column = $col;
            }
            $alpha = max($alpha, $value);
            if ($alpha >= $beta) {
                break;
            }
        }
        return [$column, $value];
    } else {
        $value = 10000000000;
        $index = rand(0, count($valid_locations) - 1); // random column
        $column = $valid_locations[$index];
        foreach ($valid_locations as $col) {
            $row = get_next_open_row($board, $col, $ROWS);
            $b_copy = $board;
            $b_copy = drop_piece($b_copy, $row, $col, PLAYER);
            $new_score = minimax($b_copy, $depth - 1, $alpha, $beta, true, $ROWS, $COLS)[1];
            if ($new_score < $value) {
                $value = $new_score;
                $column = $col;
            }
            $beta = min($beta, $value);
            if ($alpha >= $beta) {
                break;
            }
        }
        return [$column, $value];
    }
}


function get_valid_locations($board, $ROWS, $COLS)
{
    $valid_locations = [];
    for ($col = 0; $col < $COLS; $col++) {
        if (is_valid_location($board, $col, $ROWS)) {
            $valid_locations[] = $col;
        }
    }
    return $valid_locations;
}


function main($board, $ROWS, $COLS, $MAX_DEPTH)
{
    $depth = $MAX_DEPTH;
    $alpha = -1000000000;
    $beta = 1000000000;
    $maximizingPlayer = true; //
    $start = microtime(true);
    $move = minimax($board, $depth, $alpha, $beta, $maximizingPlayer, $ROWS, $COLS);
    $end = microtime(true);
    $time = $end - $start;
    // update board
    $row = get_next_open_row($board, $move[0], $ROWS);
    $board = drop_piece($board, $row, $move[0], AI);
    // return {status: success, move:'cols number', board: [], time for minimax []
    return ['status' => 'success', 'move' => $move[0], 'board' => $board, 'time' => $time, 'score' => $move[1]];
}

$main = main($board, $ROWS, $COLS, $MAX_DEPTH);
echo json_encode($main);