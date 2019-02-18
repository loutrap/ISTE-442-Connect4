<?php
    require_once("BizDataLayer/chatData.php");

    // getLastChatMsg() - method to call the getLastMsgId() method in
    // the BizDataLayer/chatData.php file.
    //	    $data - the timestamp of the last message in the DB table
    function getLastChatMsg($data){
        echo(getLastMsgId($data));
    }
    
    // sendMsg() - method to call the sendPublicChatMsg() method in
    // the BizDataLayer/chatData.php file.	
    //      $data - contains the message, userId and publicChatRoomId
    function sendMsg($data) {
        $h=explode('|',$data);
        $chatMsg = $h[0];
        $uId = $h[1];
        $chatId = $h[2];
        echo(sendPublicChatMsg($chatMsg, $uId, $chatId));
    }

    // getPriavateChatMsgs() - method to call the getPrivateChatData() method in
    // the BizDataLayer/chatData.php file.	
    //      $gameData - contains gameId
    function getPriavateChatMsgs($gameData) {
        echo(getPrivateChatData($gameData));
    }

?>