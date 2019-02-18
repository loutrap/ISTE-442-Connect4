<?php
	session_name('user-session');
	if(!isset($_SESSION)){
		session_start();		
	}
    require_once("../../dbInfo.php");

	// insertChallengeData() - method to insert a challenge into the DB
	//		$challenger - the id of the person sending the challenge
	//		$challengee - the id of the person being challenged
    function insertChallengeData($challenger,$challengee){
		global $conn;
		$sql = "Insert into 442Challenge (challengerId, challengeeId) VALUES (?, ?);";
		try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("ii", $challenger, $challengee);
				$stmt->execute();
				$stmt->store_result();
				return mysqli_insert_id($conn);				
			}
		} catch(Exception $e){

		}
	}


	// getUserChallenges() - method to query the challenge table and get all challenges
	//		$uId - uId of the user getting challenges
	function getUserChallenges($uId) {
		global $conn;
		$sql = "Select *, 442user.username from 442Challenge join 442user on (442Challenge.challengerId = 442user.uId)";
		try{
			if($stmt=$conn->prepare($sql)){
				// $stmt->bind_param("i", $uId);
				echo returnJson($stmt);
			} else if(!$data){
				throw new Exception("an error occured in the db hookup");
			}
		}catch(Exception $e){

		}
	}

	// removeChallenge() - method to delete a challenge from the challenge table
	//		$challengeId - challengeId to be deleted
	function removeChallenge($challengeId) {
		global $conn;
		$sql = "Delete from 442Challenge where challengeId = ?";
		$numRows = 0;
		try{
			$stmt;
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("s", $challengeId);
				$stmt->execute();
				$stmt->store_result();
				$numRows = $stmt->affected_rows;
			}
		} catch(Exception $e){

		}

	}
    

	// createNewGame() - method to query the challenge table to pull a challenge and then
	// with the data from that challenge create a new game in the games table
	//		$challengeId - challengeId
    function createNewGame($challengeId) {
        global $conn;
        $sql = "Select * from 442Challenge where challengeId = ?";
        try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("i", $challengeId);
				$stmt->execute();

                $stmt->bind_result($challengeId, $challengerId, $challengeeId);
				$stmt->store_result();
                $stmt->fetch();
                $stmt->close();
                
                $sql2 = "Insert into 442game (player1Id, player2Id, whoseTurn) VALUES (?, ?, ?);";
                try{
                	if ($stmt2 = $conn->prepare($sql2)) {
                		$stmt2->bind_param("iii", $challengerId, $challengeeId, $challengerId);
                		$stmt2->execute();
                        $stmt2->store_result();
                        $gameId = mysqli_insert_id($conn);
                        removeChallenge($challengeId);
                        $_SESSION['curr_game'] = $gameId;
                        return $gameId;
                    }
                } catch(Exception $e){

                }
			}
		} catch(Exception $e){

		}	
    }

	
	// getPlayerGames() - method to query the games table to return all of the active games
	// that belong a specific user
	//		$uId - userId
    function getPlayerGames($uId) {
        global $conn;
		$sql = "select gameId, uId, username from 442game join 442user ON (442game.player1Id = 442user.uId OR 442game.player2Id = 442user.uId) where player1Id = ? OR player2Id = ?";
        try{
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("ii", $uId, $uId);
				$stmt->execute();
                echo returnJson($stmt);
			}
		} catch(Exception $e){

		}	
	}
	

	// getCurrentTurn() - method to query the games table to get the id of the user
	// whose turn it currently is
	//		$gameId - game id
	function getCurrentTurn($gameId) {
        global $conn;
        $sql = "Select player1Id, player2Id, whoseTurn from 442game where gameId = ?;";
        try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("i", $gameId);
				$stmt->execute();
                echo returnJson($stmt);   
			}
		} catch(Exception $e){

		}	
	}


	// updateTurn() - method to update the games table to change the current turnId
	//		$gameId - game id
	//		$turnId - id of player whose turn it is
	function updateTurn($gameId, $turnId) {
		global $conn;
		$sql = "Update 442game SET whoseTurn = ? where gameId = ?;";
		try {
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("ii", $turnId, $gameId);
				$stmt->execute();
				$numRows = $stmt->affected_rows;
				echo $numRows;
			}
		} catch(Exception $e) {

		}
	}
	

	// insertNewMove() - method to insert a move into the moves table
	//		$gameId - game id
	//		$turn - id of player whose turn it is
	//		$selectedColumn - column number
	function insertNewMove($gameId, $turn, $selectedColumn) {
		//get all moves that are in the db
		//if there are none, then drop the piece in the current column at row 6
		global $conn;
		$sql = "Select moveId, playerId, row from 442moves where gameId = ? AND col = ?";
		$numRows;
		try{
			if ($stmt = $conn->prepare($sql)) {
				
				$stmt->bind_param("ii", $gameId, $selectedColumn);
				$stmt->execute();
				$stmt->bind_result($moveId, $playerId, $row);
				$stmt->store_result();
				$numRows = $stmt->affected_rows;
				$stmt->fetch();

				if ($numRows == 0) {
					$setRow = 5;
					$sql2 = "Insert INTO 442moves (gameId, playerId, row, col) VALUES (?,?,?,?)";
					try {

						if ($stmt2 = $conn->prepare($sql2)) {
							$stmt2->bind_param("iiii", $gameId, $turn, $setRow, $selectedColumn);
							$stmt2->execute();
							$stmt2->store_result();
							
							$moveId = mysqli_insert_id($conn);
							return $moveId;
						}
					} catch(Exception $e) {

					}
				} else if($numRows > 0 && $numRows < 6){
					//if there are moves for that column, get the last row and drop at that -1
					$sql3 = "Select row from 442moves where gameId = ? AND col = ? ORDER BY row";
					try {
						if ($stmt3 = $conn->prepare($sql3)) {
							$stmt3->bind_param("ii", $gameId, $selectedColumn);
							$stmt3->execute();
							$stmt3->bind_result($row);
							$stmt3->store_result();
							$stmt3->fetch();
							$setRow = $row-1;

							$sql4 = "Insert INTO 442moves (gameId, playerId, row, col) VALUES (?,?,?,?)";

							try {
								if ($stmt4 = $conn->prepare($sql4)) {
									$stmt4->bind_param("iiii", $gameId, $turn, $setRow, $selectedColumn);
									$stmt4->execute();
									$stmt4->store_result();
									
									$moveId = mysqli_insert_id($conn);
									return $moveId;
								}
							} catch(Exception $e) {

							}
							return $moveId;
						}
					} catch(Exception $e) {

					}
				} else {
				}                
			}
		} catch(Exception $e){
		}		
	}


	// getLastMove() - method to query the last move for a specific game
	//		$moveId - move id
	function getLastMove($moveId) {
		global $conn;
		$sql = "Select row, col from 442moves where moveId = ?";
		try{
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("i", $moveId);
				echo returnJson($stmt);
			}
		} catch(Exception $e) {

		}
	}

	// getAllMoves() - method to query all of the moves for a specific game
	//		$gameId - game id
	function getAllMoves($gameId) {
		global $conn;
		$sql = "Select * from 442moves where gameId = ?";
		try{
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("i", $gameId);
				echo returnJson($stmt);
			}
		} catch(Exception $e) {

		}
	}

    /*********************************Utilities*********************************/
	/*************************
	returnJson
	takes: prepared statement
	-parameters already bound
	returns: json encoded multi-dimensional associative array
	*/
	function returnJson ($stmt){
		$stmt->execute();
		$stmt->store_result();
		$meta = $stmt->result_metadata();
		$bindVarsArray = array();
		//using the stmt, get it's metadata (so we can get the name of the name=val pair for the associate array)!
		while ($column = $meta->fetch_field()) {
			$bindVarsArray[] = &$results[$column->name];
		}
		//bind it!
		call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
		//now, go through each row returned,
		while($stmt->fetch()) {
			$clone = array();
			foreach ($results as $k => $v) {
				$clone[$k] = $v;
			}
			$data[] = $clone;
		}
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		//MUST change the content-type
		header("Content-Type:text/plain");
		// This will become the response value for the XMLHttpRequest object
		return json_encode($data);
	}

?>
