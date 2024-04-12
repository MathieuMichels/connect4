import numpy as np
import time
import random

ROWS = 6
COLS = 7
PLAYER = 1
AI = 2
EMPTY = 0
DEPTH = 5  # Profondeur maximale de recherche pour l'algorithme Minimax


def create_board():
    return np.zeros((ROWS, COLS), dtype=int)


def is_valid_location(board, col):
    return board[ROWS-1][col] == 0


def drop_piece(board, row, col, piece):
    board[row][col] = piece


def get_next_open_row(board, col):
    for r in range(ROWS):
        if board[r][col] == 0:
            return r


def winning_move(board, piece):
    # Check horizontal locations
    for c in range(COLS - 3):
        for r in range(ROWS):
            if board[r][c] == piece and board[r][c+1] == piece and board[r][c+2] == piece and board[r][c+3] == piece:
                return True

    # Check vertical locations
    for c in range(COLS):
        for r in range(ROWS - 3):
            if board[r][c] == piece and board[r+1][c] == piece and board[r+2][c] == piece and board[r+3][c] == piece:
                return True

    # Check positively sloped diagonals
    for c in range(COLS - 3):
        for r in range(ROWS - 3):
            if board[r][c] == piece and board[r+1][c+1] == piece and board[r+2][c+2] == piece and board[r+3][c+3] == piece:
                return True

    # Check negatively sloped diagonals
    for c in range(COLS - 3):
        for r in range(3, ROWS):
            if board[r][c] == piece and board[r-1][c+1] == piece and board[r-2][c+2] == piece and board[r-3][c+3] == piece:
                return True


def evaluate_window(window, piece):
    score = 0
    opp_piece = PLAYER if piece == AI else AI

    if window.count(piece) == 4:
        score += 100
    elif window.count(piece) == 3 and window.count(EMPTY) == 1:
        score += 5
    elif window.count(piece) == 2 and window.count(EMPTY) == 2:
        score += 2

    if window.count(opp_piece) == 3 and window.count(EMPTY) == 1:
        score -= 4

    return score


def score_position(board, piece):
    score = 0

    # Score center column
    center_array = [int(i) for i in list(board[:, COLS//2])]
    center_count = center_array.count(piece)
    score += center_count * 3

    # Score horizontal
    for r in range(ROWS):
        row_array = [int(i) for i in list(board[r, :])]
        for c in range(COLS - 3):
            window = row_array[c:c+4]
            score += evaluate_window(window, piece)

    # Score vertical
    for c in range(COLS):
        col_array = [int(i) for i in list(board[:, c])]
        for r in range(ROWS - 3):
            window = col_array[r:r+4]
            score += evaluate_window(window, piece)

    # Score positively sloped diagonal
    for r in range(ROWS - 3):
        for c in range(COLS - 3):
            window = [board[r+i][c+i] for i in range(4)]
            score += evaluate_window(window, piece)

    # Score negatively sloped diagonal
    for r in range(ROWS - 3):
        for c in range(COLS - 3):
            window = [board[r+3-i][c+i] for i in range(4)]
            score += evaluate_window(window, piece)

    return score


def is_terminal_node(board):
    return winning_move(board, PLAYER) or winning_move(board, AI) or len(get_valid_locations(board)) == 0


def minimax(board, depth, alpha, beta, maximizingPlayer):
    valid_locations = get_valid_locations(board)
    is_terminal = is_terminal_node(board)
    if depth == 0 or is_terminal:
        if is_terminal:
            if winning_move(board, AI):
                return (None, 100000000000000)
            elif winning_move(board, PLAYER):
                return (None, -10000000000000)
            else:  # Game is over, no more valid moves
                return (None, 0)
        else:  # Depth is zero
            return (None, score_position(board, AI))
    if maximizingPlayer:
        value = float('-inf')
        column = np.random.choice(valid_locations)
        for col in valid_locations:
            row = get_next_open_row(board, col)
            b_copy = board.copy()
            drop_piece(b_copy, row, col, AI)
            new_score = minimax(b_copy, depth-1, alpha, beta, False)[1]
            if new_score > value:
                value = new_score
                column = col
            alpha = max(alpha, value)
            if alpha >= beta:
                break
        return column, value
    else:
        value = float('inf')
        column = np.random.choice(valid_locations)
        for col in valid_locations:
            row = get_next_open_row(board, col)
            b_copy = board.copy()
            drop_piece(b_copy, row, col, PLAYER)
            new_score = minimax(b_copy, depth-1, alpha, beta, True)[1]
            if new_score < value:
                value = new_score
                column = col
            beta = min(beta, value)
            if alpha >= beta:
                break
        return column, value


def get_valid_locations(board):
    valid_locations = []
    for col in range(COLS):
        if is_valid_location(board, col):
            valid_locations.append(col)
    return valid_locations


def print_board(board):
    print("-"*36)
    print("| 1  |  2 |  3 |  4 |  5 |  6 |  7 |")
    print("-"*36)
    for r in range(ROWS):
        print("|", end="")
        for c in range(COLS):
            if board[len(board) - r - 1][c] == 0:
                print(" ⚫ |", end="")
            elif board[len(board) - r - 1][c] == 1:
                print(" 🔴 |", end="")
            else:
                print(" ⚪ |", end="")
        print()
        print("-"*36)
    print("| 1  |  2 |  3 |  4 |  5 |  6 |  7 |")
    print("-"*36)



def pick_best_move(board, piece):
    valid_locations = get_valid_locations(board)
    best_score = -10000
    best_col = random.choice(valid_locations)
    for col in valid_locations:
        row = get_next_open_row(board, col)
        temp_board = board.copy()
        drop_piece(temp_board, row, col, piece)
        score = score_position(temp_board, piece)
        if score > best_score:
            best_score = score
            best_col = col
    return best_col


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
