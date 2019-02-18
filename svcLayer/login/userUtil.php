<?php
    include_once("BizDataLayer/checkUser.php");


    function userLogin($myData) {
        //security?
        //fail - return {"response":"you suck"}
        //prep the data...
        $h=explode('|',$myData);
        $username=$h[0];
        $password=$h[1];

        //call down the chain to the BizData Layer
        //all calls come from mid.php, so path is from there...
        echo(userLoginData($username,$password));
    }

    function userLogout($myData) {
        //call down the chain to the BizData Layer
        //all calls come from mid.php, so path is from there...
        $uId = $myData[0];

        echo(userLogoutData($uId));
    }

    function userRegister($myData) {
        //security?
        //fail - return {"response":"you suck"}
        //prep the data...
        $h=explode('|',$myData);
        $username=$h[0];
        $password=$h[1];

        if(checkIfUserExists($username)) {
            echo 0; 
        } else {
            //call down the chain to the BizData Layer
            //all calls come from mid.php, so path is from there...
            echo(userRegisterData($username,$password));
        }
    }

    function getUsers() {
        echo(getAllusers());
    }

    function currentUser() {
        
    }

?>