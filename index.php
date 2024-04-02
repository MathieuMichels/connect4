<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puissance 4</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="header-wrap">
        <h1>Puissance 4</h1>
        <h2 id="game-name"></h2>
        <div class="actions">
            <button id="new-game">Nouvelle partie</button>
            <button id="reset-scores" onclick="resetGame()">Réinitialiser les scores</button>
            <button id="help" onclick="switchHelp()">Aide</button>
            <!-- list de langues (en, fr) -->
            <select id="lang">
                <option value="fr">Français</option>
                <option value="en">English</option>
            </select>
        </div>
    </div>
</header>
<div id="help-window" style="display:none;">
    <div id="help-window-header">
        <h1>Règles du jeu</h1>
        <button id="close-help" onclick="switchHelp()">Fermer</button>
    </div>
    <div class="help-content">
    <p>Le Puissance 4 est un jeu de stratégie dans lequel deux joueurs s'affrontent pour aligner quatre jetons de leur
        couleur dans une grille verticale, horizontale ou diagonale.</p>

    <h2>Comment jouer</h2>
    <p>Le jeu se joue sur une grille de 6 lignes et 7 colonnes. Les joueurs prennent tour à tour à déposer un jeton de
        leur couleur dans l'une des colonnes. Le jeton tombe ensuite en bas de la colonne. Le premier joueur à aligner
        quatre jetons de sa couleur gagne la partie.</p>

    <h2>Nouvelle partie</h2>
    <p>Vous pouvez démarrer une nouvelle partie en cliquant sur le bouton <button class="new-game" onclick="">Nouvelle Partie</button>
        en haut à droite de la
        fenêtre.</p>

    <h2>Variantes</h2>
    <p>Il est possible de jouer à un <i>puissance 5</i> ou <i>puissance 6</i> en suivant le même principe, simplement avec un plateau plus grand.</p>

    <h2>Réinitialiser les scores</h2>
    <p>Si vous souhaitez réinitialiser les scores des parties précédentes, cliquez sur le bouton <button class="reset-scores" onclick="resetGame()">Réinitialiser les Scores</button> en haut à droite.</p>

    <h2>Changer de langue</h2>
    <p>Vous pouvez changer la langue de l'interface en sélectionnant une langue dans le menu déroulant <select class="language-select">
            <option value="fr">Français</option>
            <option value="en">English</option>
        </select>.</p>

    <h2>Stratégies</h2>
    <p>Pour améliorer vos compétences au Puissance 4, voici quelques stratégies utiles à garder à l'esprit :</p>
    <ul>
        <li><strong>Bloquer l'adversaire :</strong> Essayez de bloquer les alignements potentiels de votre adversaire tout en construisant vos propres alignements.</li>
        <li><strong>Occuper le centre :</strong> Contrôler le centre du plateau peut vous donner un avantage stratégique en vous permettant de bloquer plus facilement les alignements de l'adversaire.</li>
        <li><strong>Anticiper les coups :</strong> Essayez d'anticiper les mouvements de votre adversaire et de prévoir vos propres alignements.</li>
    </ul>

    <h2>Conseils supplémentaires</h2>
    <p>En plus des stratégies mentionnées, voici quelques conseils supplémentaires pour améliorer votre jeu :</p>
    <ul>
        <li><strong>Restez flexible :</strong> Adaptez votre stratégie en fonction des mouvements de votre adversaire et de l'évolution du plateau.</li>
        <li><strong>Pratiquez régulièrement :</strong> Plus vous jouez, plus vous comprendrez les schémas et les tactiques du jeu.</li>
        <li><strong>Apprenez des autres :</strong> Observer les jeux de joueurs expérimentés peut vous aider à découvrir de nouvelles stratégies et techniques.</li>
    </ul>
    </div>
</div>

<main>
    <table id="game-board"></table>
    <div id="game-infos">
        <!--
        | Joueur 1                            | Joueur 2                            | Joueur actuel | Nombre de coups joués |
        | Nom | Victoires | Probabilité de gagner | Nom | Victoires | Probabilité de gagner |  Nom          |                       |
        -->
        <div class="player" data-player="1">
            <h2 data-player="Player 1">Joueur 1</h2>
            <div class="player-info">
                <span class="player-wins">0</span>
                <span class="player-defeats">0</span>
                <span class="player-nulls">0</span>
                <span class="player-prob">0%</span>
            </div>
        </div>
        <div class="player" data-player="2">
            <h2 data-player="Player 2">Joueur 2</h2>
            <div class="player-info">
                <span class="player-wins">0</span>
                <span class="player-defeats">0</span>
                <span class="player-nulls">0</span>
                <span class="player-prob">0%</span>
            </div>
        </div>
</main>
<script src="connect4.js"></script>
<footer>
    <p>&copy; 2024 <a href="https://www.github.com/MathieuMichels">Mathieu Michels</a></p>
</footer>
</body>
</html>
