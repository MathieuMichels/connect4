<?php require_once 'php/i18n_setup.php'; // Includes session_start() ?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('site_title'); ?></title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/board.css">
    <link rel="stylesheet" href="css/infos.css">
    <link rel="stylesheet" href="css/help.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        window.translations = <?php echo json_encode($translations); ?>;
        // Helper function for JS translations
        window.t = function(key, ...args) {
            let text = window.translations && window.translations[key] ? window.translations[key] : key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()); // Fallback to key, formatted
            if (args.length > 0 && text !== key) { // Only format if key was found and args provided
                args.forEach((arg, index) => {
                    // Replace %s placeholder (can be multiple for sprintf style)
                    text = text.replace(/%s/, arg); 
                    // Replace {index} style placeholder
                    text = text.replace(new RegExp('\\{' + index + '\\}', 'g'), arg);
                });
            }
            return text;
        };
        // For alerts that might need a generic error prefix if message is not from server
        window.t_error = function(baseKey, serverMessage = null) {
            let message = window.t(baseKey); // Get base translated message
            let suffix = "";
            if (serverMessage) {
                suffix = serverMessage;
            } else {
                // Try to find a generic error suffix or just use the base message
                suffix = window.translations['error_unknown'] ? window.t('error_unknown') : "";
            }
            // Check if the base message already expects a parameter
            if (message.includes('%s')) {
                 return message.replace('%s', suffix);
            } else {
                return message + (suffix ? ": " + suffix : "");
            }
        }
    </script>
</head>
<body>
<header>
    <div class="header-wrap">
        <h1><?php echo t('main_title'); ?></h1>
        <h2 id="game-name"></h2> <!-- JS will populate this -->
        <div class="actions">
            <?php if (isset($_SESSION['pseudo']) && !empty($_SESSION['pseudo'])): ?>
                <span><?php echo t('welcome_user', htmlspecialchars($_SESSION['pseudo'])); ?></span>
                <form action="php/logout_user.php" method="post" style="display: inline;">
                    <button type="submit"><?php echo t('logout_button'); ?></button>
                </form>
            <?php else: ?>
                <form action="php/register_user.php" method="post" style="display: inline;">
                    <label for="reg_pseudo"><?php echo t('register_pseudo_label'); ?></label>
                    <input type="text" id="reg_pseudo" name="pseudo" required>
                    <button type="submit"><?php echo t('register_button'); ?></button>
                </form>
                <form action="php/login_user.php" method="post" style="display: inline;">
                    <label for="login_pseudo"><?php echo t('login_pseudo_label'); ?></label>
                    <input type="text" id="login_pseudo" name="pseudo" required>
                    <button type="submit"><?php echo t('login_button'); ?></button>
                </form>
            <?php endif; ?>
            <button id="new-game"><?php echo t('new_game_button'); ?></button>
            <button id="reset-scores" onclick="resetGame()"><?php echo t('reset_scores_button'); ?></button>
            <button id="help" onclick="switchHelp()"><?php echo t('help_button'); ?></button>
            <label for="lang"><?php echo t('language_label'); ?></label>
            <select id="lang">
                <option value="fr" <?php if ($current_lang == 'fr') echo 'selected'; ?>><?php echo t('lang_fr'); ?></option>
                <option value="en" <?php if ($current_lang == 'en') echo 'selected'; ?>><?php echo t('lang_en'); ?></option>
            </select>
        </div>
    </div>
</header>
<div id="help-window" style="display:none;">
    <div id="help-window-header">
        <h1><?php echo t('help_window_title'); ?></h1>
        <button id="close-help" onclick="switchHelp()"><?php echo t('close_help_button'); ?></button>
    </div>
    <div class="help-content">
        <h2><?php echo t('help_objective_title'); ?></h2>
        <p><?php echo t('help_objective_p1'); ?></p>

        <h2><?php echo t('help_how_to_play_title'); ?></h2>
        <p><?php echo t('help_how_to_play_p1'); ?></p>
        <p><?php echo t('help_how_to_play_p2'); ?></p>

        <h2><?php echo t('help_game_modes_title'); ?></h2>
        
        <h3><?php echo t('help_vs_ai_title'); ?></h3>
        <p><?php echo t('help_vs_ai_p1'); ?></p>
        <p><?php echo t('help_vs_ai_p2'); ?></p>

        <h3><?php echo t('help_multiplayer_title'); ?></h3>
        <p><?php echo t('help_multiplayer_p1'); ?></p>
        <h4><?php echo t('help_multiplayer_create_title'); ?></h4>
        <p><?php echo t('help_multiplayer_create_p1'); ?></p>
        <p><?php echo t('help_multiplayer_create_p2'); ?></p>
        <h4><?php echo t('help_multiplayer_join_title'); ?></h4>
        <p><?php echo t('help_multiplayer_join_p1'); ?></p>
        <p><?php echo t('help_multiplayer_join_p2'); ?></p>
        <h4><?php echo t('help_multiplayer_share_title'); ?></h4>
        <p><?php echo t('help_multiplayer_share_p1'); ?></p>

        <h2><?php echo t('help_ui_elements_title'); ?></h2>
        <ul>
            <li><strong><?php echo t('help_ui_new_game'); ?>:</strong> <?php echo t('help_ui_new_game_desc'); ?></li>
            <li><strong><?php echo t('help_ui_reset_scores'); ?>:</strong> <?php echo t('help_ui_reset_scores_desc'); ?></li>
            <li><strong><?php echo t('help_ui_language_selector'); ?>:</strong> <?php echo t('help_ui_language_selector_desc'); ?></li>
        </ul>

        <h2><?php echo t('help_strategies_title'); ?></h2>
        <ul>
            <li><strong><?php echo t('help_strategy_center_title'); ?>:</strong> <?php echo t('help_strategy_center_desc'); ?></li>
            <li><strong><?php echo t('help_strategy_offensive_title'); ?>:</strong> <?php echo t('help_strategy_offensive_desc'); ?></li>
            <li><strong><?php echo t('help_strategy_defensive_title'); ?>:</strong> <?php echo t('help_strategy_defensive_desc'); ?></li>
            <li><strong><?php echo t('help_strategy_look_ahead_title'); ?>:</strong> <?php echo t('help_strategy_look_ahead_desc'); ?></li>
            <li><strong><?php echo t('help_strategy_traps_title'); ?>:</strong> <?php echo t('help_strategy_traps_desc'); ?></li>
        </ul>
    </div>
</div>

<main>
    <table id="game-board"></table>
    <div id="game-infos">
        <div class="player" data-player="1">
            <h2 data-player="Player 1"></h2> <!-- Empty by default -->
            <div class="player-info">
                <span class="player-wins" data-stat="wins">-</span>
                <span class="player-defeats" data-stat="defeats">-</span>
                <span class="player-nulls" data-stat="nulls">-</span>
                <span class="player-prob" style="display:none;">0%</span>
            </div>
        </div>
        <div class="player" data-player="2">
            <h2 data-player="Player 2"></h2> <!-- Empty by default -->
            <div class="player-info">
                <span class="player-wins" data-stat="wins">-</span>
                <span class="player-defeats" data-stat="defeats">-</span>
                <span class="player-nulls" data-stat="nulls">-</span>
                <span class="player-prob" style="display:none;">0%</span>
            </div>
        </div>
    </div>
    <p id="turn-indicator" data-translation-key="turn_indicator_loading"><?php echo t('turn_indicator_loading'); ?></p>
    
    <div id="share-game-section" style="display:none; margin-top: 15px; text-align: center;">
        <h4 data-translation-key="share_game_title"><?php echo t('share_game_title'); ?></h4>
        <input type="text" id="shareable-game-link" readonly style="width: 100%; max-width: 350px; padding: 8px; margin-bottom: 5px; border: 1px solid var(--color-border); border-radius: var(--border-radius); background-color: var(--color-surface); text-align: center;">
        <button id="copy-game-link-btn" data-translation-key="copy_link_button"><?php echo t('copy_link_button'); ?></button>
        <p id="copy-link-feedback" style="font-size: 0.9em; color: var(--color-success); margin-top: 5px; min-height: 1.2em;"></p>
    </div>

    <button id="backToLobbyBtn" style="display:none;" data-translation-key="back_to_lobby_button"><?php echo t('back_to_lobby_button'); ?></button>

    <div id="quick-join-pseudo-prompt" style="display:none; text-align: center; margin-top: 20px;">
        <h3 data-translation-key="quick_join_prompt_title"><?php echo t('quick_join_prompt_title'); ?></h3>
        <input type="text" id="quick-join-pseudo-input" placeholder="<?php echo t('quick_join_pseudo_placeholder'); ?>" style="padding: 8px; margin-right: 5px;">
        <button id="quick-join-submit-pseudo" style="padding: 8px 12px;" data-translation-key="quick_join_submit_button"><?php echo t('quick_join_submit_button'); ?></button>
        <p id="quick-join-error" style="color:red; margin-top: 10px;"></p>
    </div>

    <?php if (isset($_SESSION['pseudo']) && !empty($_SESSION['pseudo'])): ?>
    <div id="game-creation-lobby">
        <h2 data-translation-key="main_title"><?php echo t('main_title'); // Reusing main_title for Game Lobby ?></h2>
        <div id="create-game-form-section">
            <h3 data-translation-key="create_game_form_title"><?php echo t('create_game_form_title'); ?></h3>
            <form id="createGameForm" action="php/create_game.php" method="post">
                <div>
                    <label for="gameName" data-translation-key="game_name_label"><?php echo t('game_name_label'); ?></label>
                    <input type="text" id="gameName" name="gameName" placeholder="<?php echo t('site_title'); // Placeholder example ?>">
                </div>
                <div>
                    <label for="play_ai" data-translation-key="play_ai_checkbox_label"><?php echo t('play_ai_checkbox_label'); ?></label>
                    <input type="checkbox" id="play_ai" name="play_ai" value="true">
                </div>
                <div id="ai_difficulty_section" style="display: block;"> 
                    <label for="difficulty" data-translation-key="ai_difficulty_label"><?php echo t('ai_difficulty_label'); ?></label>
                    <input type="range" id="difficulty" name="difficulty" min="1" max="7" value="5">
                </div>
                <button type="submit" data-translation-key="create_game_button"><?php echo t('create_game_button'); ?></button>
            </form>
        </div>
        <hr>
        <div id="available-games-section">
            <h3 data-translation-key="available_games_title"><?php echo t('available_games_title'); ?></h3>
            <button id="refresh-games-list" data-translation-key="refresh_list_button"><?php echo t('refresh_list_button'); ?></button>
            <div id="available-games-list">
                <!-- Games will be listed here by JavaScript -->
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>
<script src="js/connect4.js"></script>
<script>
    // Note: The langSelect event listener is now inside the main DOMContentLoaded listener.
    // PHP translations are embedded via window.translations.
    // The main game logic script follows this.

    // This is the new updateUserStatsDisplay function
    function updateUserStatsDisplay(userPseudo, playerNumber) {
        if (!userPseudo || !playerNumber) return;

        fetch('php/get_user_stats.php?pseudo=' + encodeURIComponent(userPseudo))
            .then(response => response.json())
            .then(stats => {
                if (stats && !stats.error) {
                    if (typeof updatePlayerWins === 'function') updatePlayerWins(playerNumber, stats.wins);
                    if (typeof updatePlayerDefeats === 'function') updatePlayerDefeats(playerNumber, stats.defeats);
                    if (typeof updatePlayerNull === 'function') updatePlayerNull(playerNumber, stats.nulls);
                } else if (stats && stats.error) {
                    console.error('Error fetching stats for ' + userPseudo + ':', stats.error);
                    if (typeof updatePlayerWins === 'function') updatePlayerWins(playerNumber, 0);
                    if (typeof updatePlayerDefeats === 'function') updatePlayerDefeats(playerNumber, 0);
                    if (typeof updatePlayerNull === 'function') updatePlayerNull(playerNumber, 0);
                }
            })
            .catch(error => {
                console.error('Error fetching user stats for ' + userPseudo + ':', error);
                if (typeof updatePlayerWins === 'function') updatePlayerWins(playerNumber, 0);
                if (typeof updatePlayerDefeats === 'function') updatePlayerDefeats(playerNumber, 0);
                if (typeof updatePlayerNull === 'function') updatePlayerNull(playerNumber, 0);
            });
    }
    

    document.addEventListener('DOMContentLoaded', function() {
        const gameNameH2 = document.getElementById('game-name');
        if (gameNameH2) {
            gameNameH2.textContent = window.t('main_title'); 
        }

        const turnIndicatorEl = document.getElementById('turn-indicator');
        if (turnIndicatorEl && turnIndicatorEl.dataset.translationKey === "turn_indicator_loading") { // Check if it's the loading text
             turnIndicatorEl.textContent = window.t('turn_indicator_loading');
        }
        
        const availableGamesListDivStatic = document.getElementById('available-games-list');
        if (availableGamesListDivStatic && availableGamesListDivStatic.querySelector('p')) { 
            const pElement = availableGamesListDivStatic.querySelector('p');
            // Check if the current text is the default English "No games..." or if it's already translated
            // This is tricky because the text might already be in French from PHP if lang=fr was set.
            // A more robust way is to add a data attribute that JS can check, or just always set it from JS.
            // For now, we'll assume if it's not the exact English default, it might be translated or a different message.
            if (pElement.textContent === "No games available to join. Create one!") {
                 pElement.textContent = window.t('no_games_available');
            }
        }
        const copyGameLinkBtnEl = document.getElementById('copy-game-link-btn');
        if(copyGameLinkBtnEl) {
            copyGameLinkBtnEl.dataset.copiedText = window.t('link_copied_feedback');
            copyGameLinkBtnEl.dataset.copyFailedText = window.t('failed_copy_feedback');
        }
        const gameNameInput = document.getElementById('gameName');
        if(gameNameInput) gameNameInput.placeholder = window.t('site_title');


        // Language selector functionality
        const langSelect = document.getElementById('lang');
        if(langSelect) {
            langSelect.value = "<?php echo htmlspecialchars($current_lang); ?>"; 
            langSelect.addEventListener('change', function() {
                const selectedLang = this.value;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('lang', selectedLang);
                window.location.href = currentUrl.toString();
            });
        }
        
        <?php if (isset($_SESSION['pseudo']) && !empty($_SESSION['pseudo'])): ?>
        const availableGamesListDiv = document.getElementById('available-games-list');
        const refreshButton = document.getElementById('refresh-games-list');
        const gameCreationLobbyDiv = document.getElementById('game-creation-lobby');
        const gameBoardTable = document.getElementById('game-board');
        const gameInfosDiv = document.getElementById('game-infos');
        
        let currentGameId = null; 
        const currentUserPseudo = "<?php echo isset($_SESSION['pseudo']) ? htmlspecialchars($_SESSION['pseudo']) : ''; ?>";
        let currentGameState = null;
        let pollingInterval = null;
        const JS_ROWS = 6; 
        const JS_COLS = 7; 
        let cells = []; 

        function createBoardHTML() {
            if (!gameBoardTable) {
                console.error("game-board table not found for createBoardHTML");
                return;
            }
            gameBoardTable.innerHTML = ''; 
            cells = [];
            for (let r = 0; r < JS_ROWS; r++) {
                const row = gameBoardTable.insertRow();
                cells[r] = [];
                for (let c = 0; c < JS_COLS; c++) {
                    const cell = row.insertCell();
                    cell.dataset.column = c;
                    cell.dataset.row = r; 
                    cells[r][c] = cell;
                }
            }
        }

        function drawBoard(boardData, playersPseudoArray) {
            if (!cells || cells.length === 0) {
                console.warn("HTML board (cells array) not initialized. Calling createBoardHTML.");
                createBoardHTML(); 
                if (!cells || cells.length === 0) { 
                     console.error("Failed to initialize HTML board for drawBoard.");
                     return;
                }
            }
            if (!boardData || !Array.isArray(boardData)) {
                console.error("Invalid boardData for drawBoard:", boardData);
                return;
            }

            for (let r = 0; r < JS_ROWS; r++) {
                if (!boardData[r] || !Array.isArray(boardData[r])) {
                    console.warn(`Row ${r} is missing or not an array in boardData.`);
                    continue;
                }
                for (let c = 0; c < JS_COLS; c++) {
                    if (!cells[r] || !cells[r][c]) {
                        console.warn(`Cell [${r}][${c}] not found in HTML board.`);
                        continue;
                    }
                    cells[r][c].className = ''; 
                    const piecePseudo = boardData[r][c];
                    if (piecePseudo && piecePseudo !== 0) { 
                        if (playersPseudoArray && playersPseudoArray.length > 0 && piecePseudo === playersPseudoArray[0]) {
                            cells[r][c].classList.add('player1');
                        } else if (playersPseudoArray && playersPseudoArray.length > 1 && piecePseudo === playersPseudoArray[1]) {
                            cells[r][c].classList.add('player2');
                        }
                    }
                }
            }
        }
        
        function updateGameUI(gameState) {
            currentGameState = gameState;
            console.log("Updating UI with gameState:", gameState);

            if (!gameState || typeof gameState.players === 'undefined' || typeof gameState.currentBoard === 'undefined') {
                console.error("Incomplete gameState for UI update:", gameState);
                return; 
            }
            
            const siteTitleForJs = window.t('site_title');
            if (typeof setGameName === 'function') {
                setGameName(gameState.gameName || siteTitleForJs); 
            } else { 
                const gameNameH2 = document.getElementById('game-name');
                if (gameNameH2) gameNameH2.textContent = gameState.gameName || siteTitleForJs;
            }

            drawBoard(gameState.currentBoard, gameState.players);

            const waitingPlayerNameForJs = window.t('waiting_player_name');
            const aiPlayerNameForJs = window.t('ai_player_name');

            if (typeof updatePlayerName === 'function') {
                if (gameState.players && gameState.players.length > 0) {
                    updatePlayerName(1, gameState.players[0]);
                    if (currentUserPseudo === gameState.players[0]) {
                        updateUserStatsDisplay(currentUserPseudo, 1);
                    } else { 
                        updatePlayerWins(1, '-'); updatePlayerDefeats(1, '-'); updatePlayerNull(1, '-'); 
                    }
                } else {
                    updatePlayerName(1, "Player 1"); 
                     updatePlayerWins(1, '-'); updatePlayerDefeats(1, '-'); updatePlayerNull(1, '-');
                }

                if (gameState.players && gameState.players.length > 1) {
                    const player2DisplayName = gameState.players[1] === "AI_PLAYER" ? aiPlayerNameForJs : gameState.players[1];
                    updatePlayerName(2, player2DisplayName);
                    if (currentUserPseudo === gameState.players[1]) {
                        updateUserStatsDisplay(currentUserPseudo, 2);
                    } else { 
                        updatePlayerWins(2, '-'); updatePlayerDefeats(2, '-'); updatePlayerNull(2, '-');
                    }
                } else { 
                    updatePlayerName(2, waitingPlayerNameForJs);
                    updatePlayerWins(2, '-'); updatePlayerDefeats(2, '-'); updatePlayerNull(2, '-');
                }
            } else { 
                 const player1NameH2 = document.querySelector('.player[data-player="1"] h2');
                 const player2NameH2 = document.querySelector('.player[data-player="2"] h2');
                 if (player1NameH2 && gameState.players && gameState.players.length > 0) player1NameH2.textContent = gameState.players[0];
                 if (player2NameH2 && gameState.players && gameState.players.length > 1) {
                     player2NameH2.textContent = gameState.players[1] === "AI_PLAYER" ? aiPlayerNameForJs : gameState.players[1];
                 } else {
                     if(player2NameH2) player2NameH2.textContent = waitingPlayerNameForJs;
                 }
            }
            
            const turnIndicator = document.getElementById('turn-indicator');
            if (turnIndicator) {
                if (gameState.status === 'in_progress') {
                    const turnTextKey = gameState.current_turn_pseudo === currentUserPseudo ? 'turn_indicator_your_turn' : 'turn_indicator_opponents_turn';
                    turnIndicator.innerText = window.t(turnTextKey, gameState.current_turn_pseudo);
                    turnIndicator.style.display = 'block';
                } else if (gameState.status === 'finished' || gameState.status === 'finished_draw') {
                     turnIndicator.innerText = gameState.winner_pseudo ? window.t('game_won_message', gameState.winner_pseudo) : window.t('game_draw_message');
                     turnIndicator.style.display = 'block';
                } else if (gameState.status === 'waiting') {
                    turnIndicator.innerText = window.t('turn_indicator_waiting_opponent');
                    turnIndicator.style.display = 'block';
                } else {
                    turnIndicator.style.display = 'none';
                }
            }

            const shareSection = document.getElementById('share-game-section');
            const shareLinkInput = document.getElementById('shareable-game-link');
            const copyFeedback = document.getElementById('copy-link-feedback');

            if (gameState.status === 'waiting' && 
                gameState.players && gameState.players.length === 1 && 
                gameState.game_creator_pseudo === currentUserPseudo &&
                gameState.game_type !== 'ai') {
                
                const fullLink = window.location.origin + window.location.pathname + '?joingame=' + gameState.id;
                if(shareLinkInput) shareLinkInput.value = fullLink;
                if(shareSection) shareSection.style.display = 'block';
            } else {
                if(shareSection) shareSection.style.display = 'none';
                if(copyFeedback) copyFeedback.textContent = ''; 
            }


            if (gameState.status === 'finished' || gameState.status === 'finished_draw') {
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = null;
                const alertMessage = gameState.winner_pseudo ? window.t('game_won_message', gameState.winner_pseudo) : window.t('game_draw_message');
                alert(window.t('alert_game_over') + " " + alertMessage);
                
                const backButton = document.getElementById('backToLobbyBtn');
                if(backButton) backButton.style.display = 'block';
                
                if (currentUserPseudo === gameState.players[0]) {
                    updateUserStatsDisplay(currentUserPseudo, 1);
                } else if (currentUserPseudo === gameState.players[1]) {
                     updateUserStatsDisplay(currentUserPseudo, 2);
                }
            }
        }

        function goBackToLobby() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = null;
            currentGameId = null;
            currentGameState = null;

            const gameBoard = document.getElementById('game-board');
            const gameInfos = document.getElementById('game-infos');
            const lobby = document.getElementById('game-creation-lobby');
            const backButton = document.getElementById('backToLobbyBtn');
            const turnInfo = document.getElementById('turn-indicator');
            const gameNameDisplay = document.getElementById('game-name');


            if (gameBoard) gameBoard.style.display = 'none';
            if (gameInfos) gameInfos.style.display = 'none';
            if (lobby) lobby.style.display = 'block';
            if (backButton) backButton.style.display = 'none';
            if (turnInfo) {
                turnInfo.style.display = 'none';
                turnInfo.textContent = window.t('turn_indicator_loading'); 
            }
            if (gameNameDisplay) gameNameDisplay.textContent = window.t('main_title');


            if (typeof fetchAvailableGames === 'function') {
                fetchAvailableGames();
            }
        }
        const backButtonInstance = document.getElementById('backToLobbyBtn');
        if (backButtonInstance) {
            backButtonInstance.addEventListener('click', goBackToLobby);
        }

        function fetchGameState(gameId) {
            if (!gameId) {
                console.log("fetchGameState called without gameId");
                if (pollingInterval) clearInterval(pollingInterval);
                return;
            }
            fetch(`php/get_game_state.php?game_id=${gameId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data && data.id) {
                        updateGameUI(data);
                    } else if (data && data.error) {
                        console.error("Error fetching game state:", data.error);
                        if (pollingInterval && (data.error.includes("not found") || data.error.includes("decode"))) {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                            alert(window.t_error('alert_error_fetching_game_state', data.error) + ". " + window.t('alert_returning_to_lobby'));
                            if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'block';
                            if (gameBoardTable) gameBoardTable.style.display = 'none';
                            if (gameInfosDiv) gameInfosDiv.style.display = 'none';
                            currentGameId = null; currentGameState = null;
                            fetchAvailableGames();
                        }
                    }
                })
                .catch(error => {
                    console.error("Exception in fetchGameState:", error);
                });
        }
        
        function startGamePolling(gameId) {
            if (pollingInterval) clearInterval(pollingInterval);
            currentGameId = gameId; 
            console.log('Starting game polling for game_id:', gameId);
            fetchGameState(gameId); 
            pollingInterval = setInterval(() => fetchGameState(gameId), 3000); 
        }

        function joinGame(gameId) {
            fetch('php/join_game.php', {
                method: 'POST',
                body: new URLSearchParams({game_id: gameId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(window.t('alert_successfully_joined_game', data.game_id) + ". " + window.t('alert_game_board_shown'));
                    
                    if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
                    if (gameBoardTable) {
                        createBoardHTML(); 
                        gameBoardTable.style.display = 'table';
                    }
                    if (gameInfosDiv) gameInfosDiv.style.display = 'flex';

                    startGamePolling(data.game_id); 
                } else {
                    alert(window.t_error('alert_could_not_join_game', data.message));
                    fetchAvailableGames(); 
                }
            })
            .catch(error => {
                console.error('Error joining game:', error);
                alert(window.t('alert_error_joining_game'));
            });
        }

        function fetchAvailableGames() {
            if (!availableGamesListDiv) return; 

            fetch('php/list_games.php')
                .then(response => response.json())
                .then(games => {
                    availableGamesListDiv.innerHTML = ''; 
                    if (games.length === 0) {
                        availableGamesListDiv.innerHTML = `<p>${window.t('no_games_available')}</p>`;
                        return;
                    }
                    const ul = document.createElement('ul');
                    ul.className = 'games-ul';
                    games.forEach(game => {
                        const li = document.createElement('li');
                        li.className = 'game-item';
                        const playersDisplay = Array.isArray(game.players) ? game.players.join(', ') : 'N/A';
                        
                        const createdByText = window.t('game_created_by', game.game_creator_pseudo || 'Unknown');
                        const playersText = `Players: ${playersDisplay}`; // "Players:" could be a key if needed
                        const joinButtonText = window.t('join_game_button');

                        li.innerHTML = `
                            <strong>${game.gameName || window.t('unnamed_game')}</strong> (ID: ${game.id})<br>
                            ${createdByText}<br>
                            ${playersText}
                            <button onclick="joinGame('${game.id}')">${joinButtonText}</button>
                        `;
                        ul.appendChild(li);
                    });
                    availableGamesListDiv.appendChild(ul);
                })
                .catch(error => {
                    console.error('Error fetching available games:', error);
                    availableGamesListDiv.innerHTML = `<p>${window.t('error_loading_games')}</p>`;
                });
        }

        if (refreshButton) {
            refreshButton.addEventListener('click', fetchAvailableGames);
        }

        if (currentUserPseudo) { 
             fetchAvailableGames();
        }

        if (!currentGameId) {
            if (gameBoardTable) gameBoardTable.style.display = 'none';
            if (gameInfosDiv) gameInfosDiv.style.display = 'none';
            const turnInfo = document.getElementById('turn-indicator');
            if(turnInfo) turnInfo.style.display = 'none';
        }

        const playAiCheckbox = document.getElementById('play_ai');
        const aiDifficultySection = document.getElementById('ai_difficulty_section');

        if (playAiCheckbox && aiDifficultySection) {
            aiDifficultySection.style.display = playAiCheckbox.checked ? 'block' : 'none';
            playAiCheckbox.addEventListener('change', function() {
                aiDifficultySection.style.display = this.checked ? 'block' : 'none';
            });
        }

        function startDefaultAiGame() {
            if (!currentUserPseudo) return; 

            console.log("Starting default AI game...");
            const formData = new URLSearchParams();
            formData.append('play_ai', 'true');
            formData.append('difficulty', '5'); 
            formData.append('gameName', window.t('ai_challenge_game_name'));
            formData.append('source_request', 'js_default_ai');

            fetch('php/create_game.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.game) {
                    console.log("Default AI game created:", data.game);
                    currentGameId = data.game.id;
                    
                    if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
                    if (gameBoardTable) {
                         createBoardHTML(); 
                         gameBoardTable.style.display = 'table';
                    }
                    if (gameInfosDiv) gameInfosDiv.style.display = 'flex';
                    const turnInfo = document.getElementById('turn-indicator');
                    if(turnInfo) turnInfo.style.display = 'block';

                    updateGameUI(data.game); 
                    startGamePolling(data.game.id); 
                } else {
                    console.error("Failed to create default AI game:", data.message || window.t('error_unknown'));
                    alert(window.t_error('alert_could_not_start_default_ai_game', data.message));
                    if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'block'; 
                }
            })
            .catch(error => {
                console.error("Error starting default AI game:", error);
                alert(window.t('alert_error_starting_default_ai_game'));
                if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'block'; 
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        const gameIdToQuickJoin = urlParams.get('joingame');
        const gameIdFromCreated = urlParams.get('game_created'); 
        const gameIdFromDirectLink = urlParams.get('game_id');   
        
        const quickJoinPseudoPrompt = document.getElementById('quick-join-pseudo-prompt');
        const quickJoinError = document.getElementById('quick-join-error');
        const quickJoinSubmitPseudoBtn = document.getElementById('quick-join-submit-pseudo');
        const quickJoinPseudoInput = document.getElementById('quick-join-pseudo-input');
        const turnInfoDisplay = document.getElementById('turn-indicator');
        const copyGameLinkBtn = document.getElementById('copy-game-link-btn');
        const copyLinkFeedbackEl = document.getElementById('copy-link-feedback');

        let currentUserPseudoInternal = currentUserPseudo; // Use a modifiable var for JS context if needed after pseudo set by quick join

        function attemptQuickJoin(gameId, pseudo) {
            console.log("Attempting quick join for game:", gameId, "with pseudo (JS var):", pseudo);
            if (quickJoinPseudoPrompt) quickJoinPseudoPrompt.style.display = 'none'; 
            
            if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
            if (gameBoardTable) {
                createBoardHTML(); 
                gameBoardTable.style.display = 'table'; 
            }
            if (gameInfosDiv) gameInfosDiv.style.display = 'flex'; 
            if (turnInfoDisplay) turnInfoDisplay.style.display = 'block'; 

            joinGame(gameId); 
        }

        if (copyGameLinkBtn) {
            copyGameLinkBtn.addEventListener('click', function() {
                const linkInput = document.getElementById('shareable-game-link');
                if (!linkInput || !copyLinkFeedbackEl) return;

                linkInput.select(); 
                linkInput.setSelectionRange(0, 99999); 

                try {
                    document.execCommand('copy'); 
                    copyLinkFeedbackEl.textContent = window.t('link_copied_feedback');
                } catch (err) {
                    navigator.clipboard.writeText(linkInput.value).then(function() {
                        copyLinkFeedbackEl.textContent = window.t('link_copied_feedback'); // Fallback success
                    }, function(clipboardErr) {
                        console.error('Could not copy text: ', clipboardErr);
                        copyLinkFeedbackEl.textContent = window.t('failed_copy_feedback');
                    });
                }
                setTimeout(() => { if(copyLinkFeedbackEl) copyLinkFeedbackEl.textContent = ''; }, 3000);
            });
        }


        if (gameIdToQuickJoin) {
            console.log("Quick Join detected for game_id:", gameIdToQuickJoin);
            if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
            if (gameBoardTable) gameBoardTable.style.display = 'none';
            if (gameInfosDiv) gameInfosDiv.style.display = 'none';
            if (turnInfoDisplay) turnInfoDisplay.style.display = 'none';


            if (currentUserPseudoInternal) { // Check internal JS var
                attemptQuickJoin(gameIdToQuickJoin, currentUserPseudoInternal);
            } else {
                if (quickJoinPseudoPrompt) quickJoinPseudoPrompt.style.display = 'block';
                if (quickJoinSubmitPseudoBtn) {
                    quickJoinSubmitPseudoBtn.onclick = function() {
                        const pseudo = quickJoinPseudoInput.value.trim();
                        if (pseudo) {
                            if(quickJoinError) quickJoinError.textContent = '';
                            fetch('php/register_user.php', {
                                method: 'POST',
                                body: new URLSearchParams({ pseudo: pseudo, source_request: 'js_quick_join' })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const welcomeSpan = document.querySelector('header .actions span');
                                    const loginForms = document.querySelectorAll('header .actions form[action="php/register_user.php"], header .actions form[action="php/login_user.php"]');
                                    if (welcomeSpan) {
                                         welcomeSpan.innerHTML = window.t('welcome_user', data.pseudo); 
                                    } else { 
                                        window.location.reload(); 
                                        return; 
                                    }
                                    const logoutForm = document.createElement('form');
                                    logoutForm.action = 'php/logout_user.php';
                                    logoutForm.method = 'post';
                                    logoutForm.style.display = 'inline';
                                    logoutForm.innerHTML = `<button type="submit">${window.t('logout_button')}</button>`;
                                    if(welcomeSpan && welcomeSpan.parentNode) welcomeSpan.parentNode.insertBefore(logoutForm, welcomeSpan.nextSibling);

                                    if(loginForms) loginForms.forEach(form => form.style.display = 'none');
                                    
                                    currentUserPseudoInternal = data.pseudo; // Update JS var
                                    attemptQuickJoin(gameIdToQuickJoin, data.pseudo); 
                                } else {
                                    if(quickJoinError) quickJoinError.textContent = data.message || window.t('error_set_pseudo');
                                }
                            })
                            .catch(error => {
                                console.error('Error setting pseudo:', error);
                                if(quickJoinError) quickJoinError.textContent = window.t('error_set_pseudo');
                            });
                        } else {
                            if(quickJoinError) quickJoinError.textContent = window.t('error_enter_pseudo');
                        }
                    };
                }
            }
            if (window.history.replaceState) {
                 const cleanUrl = new URL(window.location);
                 cleanUrl.searchParams.delete('joingame');
                 window.history.replaceState({path:cleanUrl.toString()}, '', cleanUrl.toString());
            }

        } else if (currentUserPseudoInternal) { 
            createBoardHTML(); 
            const gameIdFromFormOrLink = gameIdFromCreated || gameIdFromDirectLink;

            if (gameIdFromFormOrLink) { 
                console.log("Loading game from URL parameter (created/direct_link):", gameIdFromFormOrLink);
                if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
                if (gameBoardTable) gameBoardTable.style.display = 'table';
                if (gameInfosDiv) gameInfosDiv.style.display = 'flex';
                if (turnInfoDisplay) turnInfoDisplay.style.display = 'block';
                startGamePolling(gameIdFromFormOrLink);
                if (window.history.replaceState) { 
                    const cleanUrl = new URL(window.location);
                    cleanUrl.searchParams.delete('game_created');
                    cleanUrl.searchParams.delete('game_id');
                    window.history.replaceState({path:cleanUrl.toString()}, '', cleanUrl.toString());
                }
            } else if (!currentGameId) { 
                console.log("No active game found, starting default AI game.");
                if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none'; 
                startDefaultAiGame();
            } else {
                console.log("User logged in, no specific game in URL, lobby should be shown or existing game resumed.");
                if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'block';
                if (gameBoardTable) gameBoardTable.style.display = 'none';
                if (gameInfosDiv) gameInfosDiv.style.display = 'none';
                if (turnInfoDisplay) turnInfoDisplay.style.display = 'none';
                fetchAvailableGames();
            }
        } else { 
            if (quickJoinPseudoPrompt) quickJoinPseudoPrompt.style.display = 'none'; 
            if (gameCreationLobbyDiv) gameCreationLobbyDiv.style.display = 'none';
            if (gameBoardTable) gameBoardTable.style.display = 'none';
            if (gameInfosDiv) gameInfosDiv.style.display = 'none';
            if (turnInfoDisplay) turnInfoDisplay.style.display = 'none';
        }


        <?php endif; ?> 
    });
</script>
<!--
<script src="js/minimax.js"></script>
-->
<!-- 
    The old jQuery AJAX 'send()' function for minimax.php has been removed as it's legacy.
    AI moves are now handled via PHP includes in play_move.php and the newer AI logic.
    The 'ROWS', 'COLS' JS constants it relied on are also part of the legacy scope.
    Modern game logic uses JS_ROWS and JS_COLS defined within the main script block.
-->
<footer>
    <p>&copy; <?php echo date("Y"); ?> <a href="https://github.com/MathieuMichels/connect4" target="_blank">Mathieu Michels</a></p>
</footer>
</body>
</html>
