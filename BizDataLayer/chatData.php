<?php
    session_name('user-session');
    if(!isset($_SESSION)){
        session_start();
    }
	require_once("../../dbInfo.php");


	// getLastMsgId() - method to query the 442chatMsg table to return the latest message
    //	    $data - previous timestamp
	function getLastMsgId($data){
		global $conn;
		$publicChatId = 0;
		if($data == null){
			$sql = "Select timestamp from 442chatMsg ORDER BY timestamp desc";
		} else {
			$sql = "Select sender, username, msg, timestamp from 442chatMsg join 442user on (442chatMsg.sender=442user.uId) where recipient = ? and timestamp > ? ORDER BY timestamp";
		}
		$lastMsgId;
		try {
			if($stmt=$conn->prepare($sql)){
				$stmt->bind_param("is", $publicChatId, $data);
				$output = returnJson($stmt);
				if($output != null) {
					return $output;
				} else {
					return null;
				}
			} else if(!$data){
				throw new Exception("an error occured in the db hookup");
			}
		}catch(Exception $e){

		}
	}

	// getPrivateChatData() - method to query the 442chatMsg table to all of
	// the messages for a private chat given the gameId
    //	    $gameId - game Id
	function getPrivateChatData($gameId) {
		global $conn;
		$sql = "Select sender, username, msg, timestamp from 442chatMsg join 442user on (442chatMsg.sender=442user.uId) where recipient = ? ORDER BY timestamp";		
		try {
			if($stmt=$conn->prepare($sql)){
				$stmt->bind_param("i", $gameId);
				
				echo returnJson($stmt);
			}
		}catch(Exception $e){

		}
	}

	// sendPublicChatMsg() - method to insert a new chat message
	// into the chat table
	// the messages for a private chat given the gameId
	//	    $chatMsg - the string message
	//		$uId - the id of the user seding the msg
	//		$recipient - the recipient (public chat or private/gameId)
    function sendPublicChatMsg($chatMsg,$uId,$recipient){
		global $conn; //I have to pull in the defined variable $conn to get it in the function scope
		$sql = "Insert into 442chatMsg (sender, recipient, msg, timestamp) VALUES (?, ?, ?, ?);";
		if($recipient == 0) {
			$chatId = 0;
		} else {
			$chatId = $recipient;
		}
        $currDateTime = new DateTime();
        $timestamp = $currDateTime->format('Y-m-d H:i:s');

		try{
			if($stmt=$conn->prepare($sql)){
                $stmt->bind_param("iiss", $uId,$chatId,filter_var($chatMsg, FILTER_SANITIZE_STRING),$timestamp);

				$stmt->execute();
				$stmt->store_result();
							
				$msgId = mysqli_insert_id($conn);
				echo $msgId;
			} else if(!$data){
				throw new Exception("an error occured in the db hookup");
			}
		}catch(Exception $e){

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
