<?php
	session_name('user-session');
	if(!isset($_SESSION)){
		session_start();
	}
	require_once("../../dbInfo.php");


	// getUsersListData() - method to query the 442user table and return
	// all users
	function getUsersListData(){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Select * from 442user;";
		try{
			if ($stmt = $conn->prepare($sql)) {
				return returnJson($stmt);
			}
			} catch(Exception $e){
		}
	}

	// userLoginData() - method to query the user table to get a user based on
	// the username trying to log in. Once retreieved it will hash the password 
	// the was passed in and compare it to the hashed password in the table to verify
	// when successful it sets a SESSION with the users info
	//		$username - username being checked
	//		$password - password being checked
  	function userLoginData($username,$pass){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Select * from 442user where username = ?;";
		$numRows = 0;
		$newhash = password_hash(filter_var($pass, FILTER_SANITIZE_STRING), PASSWORD_DEFAULT);
		$currDateTime = new DateTime();
		$timestamp = $currDateTime->format('Y-m-d H:i:s');
		try{
			$stmt;
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("s", filter_var($username, FILTER_SANITIZE_STRING));
				$stmt->execute();
				$stmt->bind_result($uId, $uName, $password);
				$stmt->store_result();
				$numRows = $stmt->affected_rows;
				$stmt->fetch();
				if(password_verify($pass, $password)) {
					$_SESSION['uId'] = $uId;
					$_SESSION['username'] = $uName;
					$_SESSION['loggedIn'] = true;
					$_SESSION['timeLoggedIn'] = $timestamp;
					echo $numRows;
				} else {
					echo $numRows;
				}
			}
		} catch(Exception $e){
			
		}
	}


	// userLogoutData() - method to delete the users SESSION to log them out
	//		$uid - the users Id
	function userLogoutData($uId){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope

		$_SESSION = array(); 
		session_unset();
		session_destroy();
		return true;
	}

	// getUserId() - method to query the user table to retrieve the ID
	//		$username - username to get ID
	function getUserId($username) {
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Select uId from 442user where username = ?;";
		$userId;
		try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("s", $username);
				$stmt->execute();
				$stmt->store_result();
				$userId = $stmt->affected_rows;
			}
			return $userId;

		} catch(Exception $e){
			
		}
	}


	// checkIfUserExists() - method to query the user table to check if user already 
	// exists in the DB.
	//		$username - current username
	function checkIfUserExists($username) {
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Select * from 442user where username = ?;";
		$numRows = 0;
		try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("s", $username);
				$stmt->execute();
				$stmt->store_result();
				$numRows = $stmt->affected_rows;
			}
			return $numRows;

		} catch(Exception $e){

		}
	}


	// userRegisterData() - method to insert a new user record into the database
	//		$username - inputted username
	//		$password - inputted password
	function userRegisterData($username,$password){
		if(strlen($username)<=0 || strlen($password) < 8 || strlen($username) > 8 || strlen($password) > 16) {
			return -1;
		} else {
			global $conn; //I have to pull in the defined variable $conn to get it in the function scope
			$sql = "Insert into 442user (username, password) VALUES (?, ?);";
			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			try{
				if ($stmt = $conn->prepare($sql)) {
					/* bind parameters for markers */
					$stmt->bind_param("ss", filter_var($username, FILTER_SANITIZE_STRING), filter_var($hashedPassword, FILTER_SANITIZE_STRING));
					$stmt->execute();
					$stmt->store_result();		
				}
				echo mysqli_insert_id($conn);
			} catch(Exception $e){

			}
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
