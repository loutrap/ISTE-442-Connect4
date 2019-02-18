<?php
    include_once("BizDataLayer/userData.php");

    // userLogin() - method to call the userLoginData() method in
    // the BizDataLayer/userData.php file.
    //	    $data - username and password inputted by the user
    function userLogin($userData) {
        $h=explode('|',$userData);
        $username=$h[0];
        $password=$h[1];
        echo(userLoginData($username,$password));
    }

    // userLogout() - method to call the userLogoutData() method in
    // the BizDataLayer/userData.php file.
    //	    $data - the current users ID
    function userLogout($data) {
        $uId = $data[0];
        echo(userLogoutData($uId));
    }

    // userRegister() - method to call the userRegisterData() method in
    // the BizDataLayer/userData.php file.
    //	    $data - the username and password inputted by user
    function userRegister($data) {

        $h=explode('|',$data);
        $username=$h[0];
        $password=$h[1];

        //first check is the username that they are trying to create
        //exists already. If so return 0 to display error.
        if(checkIfUserExists($username)) {
            echo 0; 
        } else {
            //if not continue with making query
            $response = userRegisterData($username,$password);
            if ($response == -1) {
                echo -1;
            } else {
                echo 1;
            }   
        }
    }

    // getUsersList() - method to call the getUsersListData() method in
    // the BizDataLayer/userData.php file.
    function getUsersList() {
        echo(getUsersListData());
    }
?>