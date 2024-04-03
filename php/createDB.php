<?php

require_once 'dbConnect.php';

$db = new dbConnect();

$conn = $db->connect();

Const siteName = 'connect4_';



echo "<h1 style='color: green'>Successfully connected to database. </h1>";





echo "<h1 style='color: green'>Initalisation creation table " . siteName . "</h1>";

function dbTableCreate($tableName, $inputs)

{

    // ALTER TABLE Tablename CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
    $query = mysqli_query($GLOBALS['conn'], "ALTER TABLE " . siteName . $tableName . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
    if ($query) {
        echo "<h1 style='color:green'>Table " . siteName .  $tableName . " converted to utf8mb4 successfully. </h1>";
    } else {
        echo "<h1 style='color:red';>Error converting table to utf8mb4:</h1>";
    }

    echo "<h1 style='color: green'>Initalisation creation table $tableName... </h1>";



    // Check if table already exists

    $sql = "SHOW TABLES LIKE '" . siteName . $tableName . "'";

    if ($sqlResult = $GLOBALS['conn']->query($sql)) {

        if ($sqlResult->num_rows > 0) {

            echo "<h1 style='color:blue'>Table " . siteName . "_" . $tableName . " already exists. </h1>";

            return;

        }

    }

    $sql = "CREATE TABLE " . siteName . $tableName . " (";

    $sql .= "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";

    foreach ($inputs as $input) {

        $sql .= "," . $input[0] . " " . $input[1];

    }

    $sql .= ")";

    echo "<h1 style='color: green'>Creating table " . siteName .  $tableName . "...</h1>";

    echo "<p style='color: green'>SQL query: $sql</p>";

    $query = mysqli_query($GLOBALS['conn'], $sql);

    if ($query) {

        echo "<h1 style='color:green'>Table " . siteName .  $tableName . " created successfully. </h1>";

    } else {

        echo "<h1 style='color:red';>Error creating table:</h1>";

    }



}



/* Create roles table

 * Identifiant de l'utilisateur (nombre à 6 chiffres),

 * Role de l'utilisateur (string),

 * Vérifié (boolean)

 * Adresse mail (string)

 * Mot de passe (string)

 * Langue (string)

 * Adresse MAC (list de string)

 * list of games (list de int)
 *
 * Nombre de victoires
 * Nombre de défaites
 * Nombre de matchs nuls


 */

$inputsRoles = [

    ['userId', 'INT(6) UNSIGNED'],

    ['pseudo', 'VARCHAR(255)'],

    ['role', 'VARCHAR(255)'],

    ['verified', 'BOOLEAN'],

    ['email', 'VARCHAR(255)'],

    ['password', 'VARCHAR(255)'],

    ['lang', 'VARCHAR(255)'],

    ['mac', 'VARCHAR(255)'],

    ['gameId', 'VARCHAR(1500)'],

    ['wins', 'INT(6) UNSIGNED'],

    ['defeats', 'INT(6) UNSIGNED'],

    ['nulls', 'INT(6) UNSIGNED']

];

/* Create game table

 * Identifiant de la partie (chiffre),

 * Nom de la partie (string),

 * Nombre de joueurs (de 1 à 4),

 * Liste des joueurs (list de int),

 * Liste des scores (list de int),

 * Liste des colonnes joueur 1

 * Liste des colonnes joueur 2

 * Liste des colonnes joueur 3

 * Liste des colonnes joueur 4
 *
 * Couleurs des joueurs (list de string)

 * Joueur actuel (int)
 *
 * Probabilité de victoire (list de float)

 */

$inputsGames = [

    ['gameId', 'INT(6) UNSIGNED'],

    ['gameName', 'VARCHAR(255)'],

    ['nbPlayers', 'INT(1)'],

    ['nbTeams', 'INT(1)'],

    ['players', 'VARCHAR(255)'],

    ['scores', 'VARCHAR(255)'],

    ['initialBoard', 'VARCHAR(255)'],

    ['solution', 'VARCHAR(255)'],

    ['currentBoard', 'VARCHAR(255)'],

    ['startTime', 'VARCHAR(255)'],

    ['endTime', 'VARCHAR(255)'],

    ['gameTime', 'VARCHAR(255)'],

    ['currentPlayer', 'INT(1)'],

    ['playerColors', 'VARCHAR(255)'],

    ['playerProb', 'VARCHAR(255)'],

    ['playerWins', 'VARCHAR(255)'],

    ['playerDefeats', 'VARCHAR(255)'],

    ['playerNulls', 'VARCHAR(255)'],

    ['playerTurn', 'INT(1)']

];



dbTableCreate('users', $inputsRoles);

dbTableCreate('games', $inputsGames);



?>