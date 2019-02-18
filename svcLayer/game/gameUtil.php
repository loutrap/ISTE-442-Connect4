<?php
    include_once("BizDataLayer/gameData.php");

    // sendChallenge() - method to call the insertChallengeData() method in
    // the BizDataLayer/gameData.php file.
    //	    $challengeData - the challengerId and the challengeeId
    function sendChallenge($challengeData) {
        $h=explode('|',$challengeData);
        $challenger=$h[0];
        $challengee=$h[1];
        echo(insertChallengeData($challenger, $challengee));
    }

    // getChallenges() - method to call the getUserChallenges() method in
    // the BizDataLayer/gameData.php file.
    //	    $uId - the current users Id
    function getChallenges($uId) {
        echo(getUserChallenges($uId));
    }

    // rejectChallenge() - method to call the removeChallenge() method in
    // the BizDataLayer/gameData.php file.
    //	    $challengeId - the challengeId
    function rejectChallenge($challengeId) {
        echo(removeChallenge($challengeId));
    }

    // acceptChallenge() - method to call the createNewGame() method in
    // the BizDataLayer/gameData.php file.
    //	    $challengeId - the challengeId
    function acceptChallenge($challengeId) {
        echo(createNewGame($challengeId));
    }

    // getGames() - method to call the getPlayerGames() method in
    // the BizDataLayer/gameData.php file.
    //	    $uId - the current users Id
    function getGames($uId) {
        echo(getPlayerGames($uId));
    }

    // setGameSession() - method to set the curr_game Session var
    //	    $gameId - the gameId
    function setGameSession($gameId) {
        $_SESSION['curr_game'] = $gameId;
        echo($gameId);
    }

    // checkTurn() - method to call the getCurrentTurn() method in
    // the BizDataLayer/gameData.php file.
    //	    $gameId - the current gameId
    function checkTurn($gameId) {
        echo(getCurrentTurn($gameId));
    }

    // checkchangeTurnTurn() - method to call the updateTurn() method in
    // the BizDataLayer/gameData.php file.
    //	    $turnData - the gameId and turnId
    function changeTurn($turnData) {
        $h=explode('|',$turnData);
        $gameId=$h[0];
        $turnId=$h[1];
        echo(updateTurn($gameId, $turnId));
    }

    // makeMove() - method to call the getLastMove() method in
    // the BizDataLayer/gameData.php file.
    //	    $moveData - the gameId, turnId, selectedColumn number and moveId
    function makeMove($moveData) {
        $h=explode('|',$moveData);
        $gameId=$h[0];
        $turn=$h[1];
        $selectedColumn=$h[2];
        $moveId = insertNewMove($gameId, $turn, $selectedColumn);
        echo(getLastMove($moveId));
    }

    // getMoves() - method to call the getAllMoves() method in
    // the BizDataLayer/gameData.php file.
    //	    $gameId - the current gameId
    function getMoves($gameId){
        echo(getAllMoves($gameId));
    }

?>