<?php
	session_name('user-session');
	if(!isset($_SESSION)){
		

		session_start();

		
	}
	//include dbInfo
	require_once("../../dbInfo.php");
	//include exceptions

	//sessionStart
	//sessionName
	//if(!$_POST[login] { boot them to login page })

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

	// require_once('./BizDataLayer/exception.php');
  function userLoginData($username,$pass){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		//bind_param -> ('s', password_hash($_POST['password'],PASSWORD_DEFAULT)
		//if(password_verify($_POST['pass'], $res))


		$sql = "Select * from 442user where username = ?;";
		$numRows = 0;
		$username;
		$uId;
		$newhash = password_hash($pass, PASSWORD_DEFAULT);
		$currDateTime = new DateTime();
		$timestamp = $currDateTime->format('Y-m-d H:i:s');
		// echo '---'.$newhash.'---';
		try{
			$stmt;
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				
				$stmt->bind_param("s", $username);
				$stmt->execute();
				$stmt->bind_result($uId, $username, $password);
				$stmt->store_result();
				
				$numRows = $stmt->affected_rows;


				$stmt->fetch();
				// echo("this---".$password."---");
				if(password_verify($pass, $password)) {
			 	$_SESSION['uId'] = $uId;
			 	$_SESSION['username'] = $username;
				$_SESSION['loggedIn'] = true;
				$_SESSION['timeLoggedIn'] = $timestamp;

			 	$sql2 = "Insert into 442loggedIn (uId) VALUES (?);";
				try{
					$stmt2;
					if($stmt2 = $conn->prepare($sql2)) {
						/* bind parameters for markers */
						$stmt2->bind_param("s", $uId);
						$stmt2->execute();
						$stmt2->store_result();
					}

				} catch(Exception $e){

				}
			// 	// 	if ($stmt2 = $conn->prepare($sql2)) {
			// 	// 		/* bind parameters for markers */
			// 	// 		$stmt2->bind_param("s", $username);
			// 	// 		$stmt2->execute();
			// 	// 		$stmt2->store_result();
			// 	// 		$numRows = $stmt2->affected_rows;
			// 	// 	}

			 	return $numRows;

			// 	// } catch(Exception $e){
		
			// 	// }
				}
			}
			// if($numRows == 1) {

			// 	$stmt->fetch();
			// 	$_SESSION['uId'] = $uId;
			// 	$_SESSION['username'] = $username;
			// 	$_SESSION['loggedIn'] = true;

			// 	// $sql2 = "Update 442user SET loggedIn = 1 WHERE username = ?";
			// 	// try{
			// 	// 	if ($stmt2 = $conn->prepare($sql2)) {
			// 	// 		/* bind parameters for markers */
			// 	// 		$stmt2->bind_param("s", $username);
			// 	// 		$stmt2->execute();
			// 	// 		$stmt2->store_result();
			// 	// 		$numRows = $stmt2->affected_rows;
			// 	// 	}

			// 	return $numRows;

			// 	// } catch(Exception $e){
		
			// 	// }

			// }

		} catch(Exception $e){

		}
    //hard coded - will need to change to a db call eventually
    // $t = '[{"username":admin,"password":"pass"}]';
    // return $t;
	}

	function userLogoutData($uId){
		// session_start(); 
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Delete from 442loggedIn where uId = ?";
		$numRows = 0;
		try{
			$stmt;
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("s", $uId);
				$stmt->execute();
				$stmt->store_result();
				$numRows = $stmt->affected_rows;

			}
		
		} catch(Exception $e){

		}



		$_SESSION = array(); 
		session_unset();
		session_destroy();
		return true;
	}

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


	function userRegisterData($username,$password){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Insert into 442user (username, password) VALUES (?, ?);";
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("ss", $username, $hashedPassword);
				$stmt->execute();
				$stmt->store_result();
				
			}
			return mysqli_insert_id($conn);

		} catch(Exception $e){

		}
    //hard coded - will need to change to a db call eventually
    // $t = '[{"username":admin,"password":"pass"}]';
    // return $t;
	}

	function insertChallengeData($challenger,$challengee){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Insert into 442Challenge (challengerId, challengeeId) VALUES (?, ?);";
		// $pendingStatus = "pending";
		try{
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("ii", $challenger, $challengee);
				$stmt->execute();
				$stmt->store_result();
				// echo returnJson($stmt);
				return mysqli_insert_id($conn);

				
			}
			// return mysqli_insert_id($conn);

		} catch(Exception $e){

		}
    //hard coded - will need to change to a db call eventually
    // $t = '[{"username":admin,"password":"pass"}]';
    // return $t;
	}

	function getUserChallenges($uId) {
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
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

	function removeChallenge($challengeId) {
		// session_start(); 
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Delete from 442Challenge where challengeId = ?";
		$numRows = 0;
		try{
			$stmt;
			if ($stmt = $conn->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("s", $challengeId);
				$stmt->execute();
				$stmt->store_result();
				$numRows = $stmt->affected_rows;
				return $numRows;

			}
		
		} catch(Exception $e){

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
