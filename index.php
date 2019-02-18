<?php
  session_name('user-session');
  //include exceptions
  if(!isset($_SESSION)){
    session_start();
  }
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Login</title>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script>
      $(document).ready(function(){
        //default view is login
        $("#register-form").hide();

        //if user clicks register, hide login form and show register form
        $("#signup").click(function(e){
          $("#login-form").hide();
          $("#register-form").show();
          e.preventDefault();
        });

        //if user clicks signin, hide register form and show login form
        $("#signin").click(function(e){
          $("#login-form").show();
          $("#register-form").hide();
          e.preventDefault();
        });

        //when user submits the login form, check credentials
        //if correct, forward to lobby page
        //else display error message
        $('#login-form').submit(function(e){
          var username = $("#inputUsername").val();
          var password = $("#inputPassword").val();
          MyXHR('get',{method:"userLogin",a:"user",data: username+"|"+password}).done(function(json){
            if(json == 1) {
                window.location.href = "lobby.php";
            } else {
                $("#error-message").html("<div id='pass-match-msg' class='alert alert-warning' role='alert'>Invalid Login or Password. Try again.</div>")
            }
          });
          e.preventDefault();
        });

        //when user submit the register form, validate
        //input to make sure it meets requirements
        //add user to table if correct, otherwise
        //dispay error message.
        $('#register-form').submit(function(e){
          var username = $("#registerUsername").val();
          var password = $("#registerPassword").val();
          var confirmPassword = $("#confirmPassword").val();
          if (password == confirmPassword) {
            console.log(username+"--"+password);

            // getUser(username, password);
            MyXHR('post',{method:"userRegister",a:"user",data: username+"|"+password}).done(function(json){
                //   console.log(json)
                if(json >= 1) {
                    window.location.href = "index.php";
                } else if(json == 0) {
                    $("#error-message").html("<div id='pass-match-msg' class='alert alert-warning' role='alert'>Username already exists! please choose another name.</div>")
                } else if (json == -1){
                    $("#error-message").html("<div id='pass-match-msg' class='alert alert-warning' role='alert'>Username can't be more than 8 characters and password must be between 8-16 characters!</div>")

                }
            });
            } else {
                console.log("no match");
                $("#error-message").html("<div id='pass-match-msg' class='alert alert-warning' role='alert'>Passwords do not match!</div>")
            }
            e.preventDefault();

        });
      });

			////////////////
			// utility stuff
			////////////////
			// MyXHR() - method to call the mid.php file...
			//		getPost - get or post
			//		d - data, looks like {name:value;name2:val2;...}
			//		id - id of the parent for the spinner....
			function MyXHR(getPost,d,id){
					//ajax shortcut
					return $.ajax({
							type: getPost,
							async: true,
							cache: false,
							url:'mid.php',
							data:d,
							dataType:'json',
					});
    	} 
    </script>
  </head><!-- /head -->
  <body> 
  	<nav id="nav">
      <div class='navbar navbar-expand-md navbar-custom shadow-sm'>
        <div class='nav col-11'>
          <h4 class='nav-link nav-title'>Connect 4</h4>        
        </div>
      </div>
    </nav>
  	<div id="error-message">
  	</div>    
  	<div id="form-wrapper">
      <form id="login-form" class="shadow-lg" method="post">
        <h2>User Login</h2>
        <div class="form-group">
          <label for="inputUsername">Username</label>
          <input type="text" name="username" class="form-control " id="inputUsername" placeholder="Username" maxlength="8">
        </div> <!-- /.form-group -->
        <div class="form-group">
          <label for="inputPassword">Password</label>
          <input type="password" name="password" class="form-control" id="inputPassword" placeholder="Password" minlength="8" maxlength="16">
        </div> <!-- /.form-group -->
        <button id="login-btn" type="submit" name="login-btn" class="btn btn-primary custombtn">Login</button>
        <p id="two">Don't have account? <a class="signup" href="#" id="signup">Sign up here</a></p>
      </form><!-- login-form -->
      <form id="register-form" class="shadow-lg" method="post">
        <h2>User Registration</h2>
        <div class="form-group">
          <label for="inputUsername" >Username</label>
          <input type="text" name="username" class="form-control " id="registerUsername" placeholder="Username" maxlength="8">
      	</div> <!-- /.form-group -->
        <div class="form-group">
          <label for="inputPassword">Password</label>
          <input type="password" name="password" class="form-control" id="registerPassword" placeholder="Password" minlength="8" maxlength="16">
        </div> <!-- /.form-group -->
        <div class="form-group">
          <label for="inputPassword">Re-enter Password</label>
          <input type="password" name="password" class="form-control" id="confirmPassword" placeholder="Password" minlength="8" maxlength="16">
        </div> <!-- /.form-group -->
          <button id="register-btn" type="submit" name="register-btn" class="btn btn-primary custombtn">Register</button>
          <p id="two">Already have an account? <a class="signin" href="#" id="signin">Sign in</a></p>
      </form><!-- register-form -->
    </div>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body><!-- /body -->
</html>


