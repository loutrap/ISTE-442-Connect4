<?php
	//all xhr calls will go through here!
	//sent it:
	//	a- area (like 'game' or 'chat' or 'x'
	//	method - method I'm going to call in area
	//	data - data to send in to the method...
	if( isset($_GET['method']) || isset($_POST['method']) ){
		foreach(glob("./svcLayer/".$_REQUEST['a']."/*.php") as $filename){
			include $filename;
		}
		$serviceMethod = $_REQUEST['method'];
		$request=@call_user_func($serviceMethod,$_REQUEST['data']);
		
		if($request){
			//throw the json back up!
			header('Content-type:text/plain');
			echo $request;
		}
	}
?>