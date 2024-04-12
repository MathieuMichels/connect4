const PLAYER = 1;
const AI = 2;
const EMPTY = 0;
const DEPTH = 5;



function create_board() {
    return Array.from({length: ROWS}, () => Array(COLS).fill(EMPTY));
}


function is_valid_location(board, col) {
    return board[ROWS - 1][col] === EMPTY;
}


function drop_piece(board, row, col, piece) {
    board[row][col] = piece;
}


function get_next_open_row(board, col) {
    for (let r = 0; r < ROWS; r++) {
        if (board[r][col] === EMPTY) {
            return r;
        }
    }
}


function winning_move(board, piece) {
    // Check horizontal locations
    for (let c = 0; c < COLS - 3; c++) {
        for (let r = 0; r < ROWS; r++) {
            if (board[r][c] === piece && board[r][c + 1] === piece && board[r][c + 2] === piece && board[r][c + 3] === piece) {
                return true;
            }
        }
    }

    // Check vertical locations
    for (let c = 0; c < COLS; c++) {
        for (let r = 0; r < ROWS - 3; r++) {
            if (board[r][c] === piece && board[r + 1][c] === piece && board[r + 2][c] === piece && board[r + 3][c] === piece) {
                return true;
            }
        }
    }

    // Check positively sloped diagonals
    for (let c = 0; c < COLS - 3; c++) {
        for (let r = 0; r < ROWS - 3; r++) {
            if (board[r][c] === piece && board[r + 1][c + 1] === piece && board[r + 2][c + 2] === piece && board[r + 3][c + 3] === piece) {
                return true;
            }
        }
    }

    // Check negatively sloped diagonals
    for (let c = 0; c < COLS - 3; c++) {
        for (let r = 3; r < ROWS; r++) {
            if (board[r][c] === piece && board[r - 1][c + 1] === piece && board[r - 2][c + 2] === piece && board[r - 3][c + 3] === piece) {
                return true;
            }
        }
    }

    return false;
}


function evaluate_window(window, piece) {

    let score = 0;
    let opp_piece = piece === AI ? PLAYER : AI;

    if (window.filter(cell => cell === piece).length === 4) {
        score += 100;
    }
    else if (window.filter(cell => cell === piece).length === 3 && window.filter(cell => cell === EMPTY).length === 1) {
        score += 5;
    }
    else if (window.filter(cell => cell === piece).length === 2 && window.filter(cell => cell === EMPTY).length === 2) {
        score += 2;
    }

    if (window.filter(cell => cell === opp_piece).length === 3 && window.filter(cell => cell === EMPTY).length === 1) {
        score -= 4;
    }

    return score;
}




function score_position(board, piece) {
    let score = 0;

    // Score center column
    const center_array = board.map(row => row[COLS / 2]);
    const center_count = center_array.filter(cell => cell === piece).length;
    score += center_count * 3;

    // Score horizontal
    for (let r = 0; r < ROWS; r++) {
        const row_array = board[r];
        for (let c = 0; c < COLS - 3; c++) {
            const window = row_array.slice(c, c + 4);
            score += evaluate_window(window, piece);
        }
    }

    // Score vertical
    for (let c = 0; c < COLS; c++) {
        const col_array = board.map(row => row[c]);
        for (let r = 0; r < ROWS - 3; r++) {
            const window = col_array.slice(r, r + 4);
            score += evaluate_window(window, piece);
        }
    }

    // Score positively sloped diagonal
    for (let r = 0; r < ROWS - 3; r++) {
        for (let c = 0; c < COLS - 3; c++) {
            // const window = [board[r + i][c + i] for i in range(4)];
            const window = [];
            for (let i = 0; i < 4; i++) {
                window.push(board[r + i][c + i]);
            }
            score += evaluate_window(window, piece);
        }
    }

    // Score negatively sloped diagonal
    for (let r = 0; r < ROWS - 3; r++) {
        for (let c = 0; c < COLS - 3; c++) {
            // const window = [board[r + 3 - i][c + i] for i in range(4)];
            const window = [];
            for (let i = 0; i < 4; i++) {
                window.push(board[r + 3 - i][c + i]);
            }
            score += evaluate_window(window, piece);
        }
    }


    return score;
}



function is_terminal_node(board) {
    return winning_move(board, PLAYER) || winning_move(board, AI) || get_valid_locations(board).length === 0;
}

function minimax(board, depth, alpha, beta, maximizingPlayer) {
    const valid_locations = get_valid_locations(board);
    const is_terminal = is_terminal_node(board);
    if (depth === 0 || is_terminal) {
        if (is_terminal) {
            if (winning_move(board, AI)) {
                return [null, 100000000000000];
            } else if (winning_move(board, PLAYER)) {
                return [null, -10000000000000];
            } else {
                return [null, 0];
            }
        } else {
            return [null, score_position(board, AI)];
        }
    }
    if (maximizingPlayer) {
        let value = -Infinity;
        let column = valid_locations[Math.floor(Math.random() * valid_locations.length)];
        for (let col of valid_locations) {
            let row = get_next_open_row(board, col);
            let b_copy = board.map(row => row.slice());
            drop_piece(b_copy, row, col, AI);
            let new_score = minimax(b_copy, depth - 1, alpha, beta, false)[1];
            if (new_score > value) {
                value = new_score;
                column = col;
            }
            alpha = Math.max(alpha, value);
            if (alpha >= beta) {
                break;
            }
        }
        return [column, value];
    } else {
        let value = Infinity;
        let column = valid_locations[Math.floor(Math.random() * valid_locations.length)];
        for (let col of valid_locations) {
            let row = get_next_open_row(board, col);
            let b_copy = board.map(row => row.slice());
            drop_piece(b_copy, row, col, PLAYER);
            let new_score = minimax(b_copy, depth - 1, alpha, beta, true)[1];
            if (new_score < value) {
                value = new_score;
                column = col;
            }
            beta = Math.min(beta, value);
            if (alpha >= beta) {
                break;
            }
        }
        return [column, value];
    }
}

function get_valid_locations(board) {
    const valid_locations = [];
    for (let col = 0; col < COLS; col++) {
        if (is_valid_location(board, col)) {
            valid_locations.push(col);
        }
    }
    return valid_locations;

}

function print_board(board) {
    console.log("-".repeat(36));
    console.log("| 1  |  2 |  3 |  4 |  5 |  6 |  7 |");
    console.log("-".repeat(36));
    for (let r = 0; r < ROWS; r++) {
        let row = "|";
        for (let c = 0; c < COLS; c++) {
            if (board[ROWS - r - 1][c] === EMPTY) {
                row += " ⚫ |";
            } else if (board[ROWS - r - 1][c] === PLAYER) {
                row += " 🔴 |";
            } else {
                row += " ⚪ |";
            }
        }
        console.log(row);
        console.log("-".repeat(36));
    }
    console.log("| 1  |  2 |  3 |  4 |  5 |  6 |  7 |");
    console.log("-".repeat(36));
}

function pick_best_move(board, piece) {
    const valid_locations = get_valid_locations(board);
    let best_score = -10000;
    let best_col = valid_locations[Math.floor(Math.random() * valid_locations.length)];
    for (let col of valid_locations) {
        let row = get_next_open_row(board, col);
        let temp_board = board.map(row => row.slice());
        drop_piece(temp_board, row, col, piece);
        let score = score_position(temp_board, piece);
        if (score > best_score) {
            best_score = score;
            best_col = col;
        }
    }
    return best_col;
}

/*

if __name__ == '__main__':
    nbCoups = 0
    board = create_board()
    print("\033[91m" + "Vous êtes les rouges 🔴" + "\033[0m")
    print("\033[97m" + "L'IA est les blancs ⚪" + "\033[0m")
    print_board(board)
    game_over = False
    turn = int(input("Voulez-vous commencer?" + "\033[91m" + " 0 pour vous, " + "\033[0m" + "\033[97m" + "1" + "\033[0m" + " pour l'IA: "))
    if turn == 1:
        print("\033[97m" + "C'est au tour de l'IA" + "\033[0m")
    else:
        print("\033[91m" + "C'est à votre tour" + "\033[0m")
    while not game_over:
        # Player's turn
        if turn == 0:
            print("\033[91m" + "C'est à votre tour" + "\033[0m")
            col = int(input("Colonne (1 - 7): ")) - 1
            #col, minimax_score = minimax(board, DEPTH, float('-inf'), float('inf'), False)
            if is_valid_location(board, col):
                row = get_next_open_row(board, col)
                drop_piece(board, row, col, PLAYER)
                if winning_move(board, PLAYER):
                    print_board(board)
                    print("\033[92m" + "="*36 + "\033[0m")
                    print("\033[92m" + "||" + "\033[0m" + "\033[97m" + "        Vous avez gagné!        " + "\033[0m" + "\033[92m" + "||" + "\033[0m")
                    print("\033[92m" + "="*36 + "\033[0m")
                    print("\033[92m" + "Vous avez gagné en " + str(nbCoups) + " coups" + "\033[0m")
                    game_over = True
                else:
                    print("\033[91m" + "Vous avez joué à la colonne " + str(col+1) + "\033[0m")
                    print_board(board)
                    print("\033[97m" + "C'est au tour de l'IA" + "\033[0m")
                    turn = 1
                    nbCoups += 1

            # AI's turn
        else:
            start = time.time()
            col, minimax_score = minimax(board, DEPTH, float('-inf'), float('inf'), True)
            print("Temps de réflexion de l'IA: ", time.time() - start, "secondes")
            if is_valid_location(board, col):
                row = get_next_open_row(board, col)
                drop_piece(board, row, col, AI)
                print("\033[97m" + "L'IA a joué à la colonne " + str(col+1) + "\033[0m")
                print_board(board)

                if winning_move(board, AI):
                    print("\033[92m" + "="*36 + "\033[0m")
                    print("\033[92m" + "||" + "\033[0m" + "\033[97m" + "          L'IA a gagné!         " + "\033[0m" + "\033[92m" + "||" + "\033[0m")
                    print("\033[92m" + "="*36 + "\033[0m")
                    print("\033[92m" + "L'IA a gagné en " + str(nbCoups) + " coups" + "\033[0m")
                    game_over = True
                else:
                    print("\033[92m" + "="*36 + "\033[0m")

            turn = 0
            nbCoups += 1

        if len(get_valid_locations(board)) == 0:
            print("It's a tie!")
            game_over = True

 */

/*
    let nbCoups = 0;
    let board = create_board();
    console.log("\033[91m" + "Vous êtes les rouges 🔴" + "\033[0m");
    console.log("\033[97m" + "L'IA est les blancs ⚪" + "\033[0m");
    print_board(board);
    let game_over = false;
    let turn = 1;
    if (turn === 1) {
        console.log("\033[97m" + "C'est au tour de l'IA" + "\033[0m");
    } else {
        console.log("\033[91m" + "C'est à votre tour" + "\033[0m");
    }
    while (!game_over) {
        // Player's turn
        if (turn === 0) {
            console.log("\033[91m" + "C'est à votre tour" + "\033[0m");
            let col = Math.floor(Math.random() * COLS);
            //col, minimax_score = minimax(board, DEPTH, float('-inf'), float('inf'), false)
            if (is_valid_location(board, col)) {
                let row = get_next_open_row(board, col);
                drop_piece(board, row, col, PLAYER);
                if (winning_move(board, PLAYER)) {
                    print_board(board);
                    console.log("\033[92m" + "=".repeat(36) + "\033[0m");
                    console.log("\033[92m" + "||" + "\033[0m" + "\033[97m" + "        Vous avez gagné!        " + "\033[0m" + "\033[92m" + "||" + "\033[0m");
                    console.log("\033[92m" + "=".repeat(36) + "\033[0m");
                    console.log("\033[92m" + "Vous avez gagné en " + nbCoups + " coups" + "\033[0m");
                    game_over = true;
                } else {
                    console.log("\033[91m" + "Vous avez joué à la colonne " + (col + 1) + "\033[0m");
                    print_board(board);
                    console.log("\033[97m" + "C'est au tour de l'IA" + "\033[0m");
                    turn = 1;
                    nbCoups++;
                }
            }
            // AI's turn
        }
        else {
            let start = Date.now();
            console.log(board);
            let [col, minimax_score] = minimax(board, DEPTH, -Infinity, Infinity, true);
            console.log("Temps de réflexion de l'IA: ", Date.now() - start, "secondes");
            if (is_valid_location(board, col)) {
                let row = get_next_open_row(board, col);
                drop_piece(board, row, col, AI);
                console.log("\033[97m" + "L'IA a joué à la colonne " + (col + 1) + "\033[0m");
                print_board(board);

                if (winning_move(board, AI)) {
                    console.log("\033[92m" + "=".repeat(36) + "\033[0m");
                    console.log("\033[92m" + "||" + "\033[0m" + "\033[97m" + "          L'IA a gagné!         " + "\033[0m" + "\033[92m" + "||" + "\033[0m");
                    console.log("\033[92m" + "=".repeat(36) + "\033[0m");
                    console.log("\033[92m" + "L'IA a gagné en " + nbCoups + " coups" + "\033[0m");
                    game_over = true;
                } else {
                    console.log("\033[92m" + "=".repeat(36) + "\033[0m");
                }
            }
            turn = 0;
            nbCoups++;
        }

        if (get_valid_locations(board).length === 0) {
            console.log("It's a tie!");
            game_over = true;
        }
    }

*/