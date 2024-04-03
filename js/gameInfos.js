class GameInfos {
    /*
    Contains infos about:
    - gameName
    - nbPlayers
    for each players:
      - name
      - color
      - victoryCount
      - defeatCount
      - colsPlayed
      - winProba
     */
    constructor(gameName, nbPlayers, players) {
        this.gameName = gameName;
        this.nbPlayers = nbPlayers;
        this.players = players;
    }

    getGameName() {
        return this.gameName;
    }

    getNbPlayers() {
        return this.nbPlayers;
    }

    getPlayers() {
        return this.players;
    }

    getPlayer(playerName) {
        return this.players.find(player => player.getName() === playerName);
    }

    addPlayer(player) {
        this.players.push(player);
    }

    removePlayer(playerName) {
        this.players = this.players.filter(player => player.getName() !== playerName);
    }

}


class Player {
    /*
    Contains infos about:
    - name
    - color
    - victoryCount
    - defeatCount
    - colsPlayed
    - winProba
     */
    constructor(name, color, victoryCount, defeatCount, colsPlayed, winProba) {
        this.name = name;
        this.color = color;
        this.victoryCount = victoryCount;
        this.defeatCount = defeatCount;
        this.colsPlayed = colsPlayed;
        this.winProba = winProba;
    }

    getName() {
        return this.name;
    }

    getColor() {
        return this.color;
    }

    getVictoryCount() {
        return this.victoryCount;
    }

    getDefeatCount() {
        return this.defeatCount;
    }

    getColsPlayed() {
        return this.colsPlayed;
    }

    getWinProba() {
        return this.winProba;
    }

    setVictoryCount(victoryCount) {
        this.victoryCount = victoryCount;
    }

    setDefeatCount(defeatCount) {
        this.defeatCount = defeatCount;
    }

    setColsPlayed(colsPlayed) {
        this.colsPlayed = colsPlayed;
    }

    setWinProba(winProba) {
        this.winProba = winProba;
    }

}