<?php
  session_name('user-session');
  //include exceptions
  if(!isset($_SESSION)){
    session_start();
  }

  if(!$_SESSION['loggedIn']) {
    header("location: index.php");
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
      //global scope variables
      let lastMsgTimestamp = null;
      let uId = "<?php echo $_SESSION['uId']; ?>";
      let opponentName = "";

      $(document).ready(function(){
				//display challenges and games
				displayMessages();
        displayChallenges();
        displayActiveGames();
        //update avtice games, challenges and messages on a timer
        setInterval(displayActiveGames, 5000);
        setInterval(displayMessages, 2000);
				setInterval(displayChallenges, 5000);
				
        // when a user clicks logout, kill the session and return them to 
        // index.php
        $('#logout-form').submit(function(e){                     
          MyXHR('get',{method:"userLogout",a:"user",data: uId}).done(function(json){
            if(json) {
              window.location.href = "index.php";
            } else {
              console.log("else");
            }
          });
          e.preventDefault();
        });

				// when a user submits a chat message, grab the input and send it to
				// the db using ajax call.
				$('#chat-form').submit(function(e){
					let msg = $("#chat-msg").val();
					let publicChatId = 0;
					MyXHR('get',{method:"sendMsg",a:"chat",data: msg+"|"+uId+"|"+publicChatId}).done(function(json){                    
						// displayMessages();
						$("#chat-msg").val('');
					});
					e.preventDefault();
				});

				//display the list of active users
				//append a div with the players info in the window 
				//with a button to challenge them.
				MyXHR('get',{method:"getUsersList",a:"user"}).done(function(json){
					console.log(json);
					for(let i = 0; i < json.length; i++) {
						if(json[i].uId != uId) {
							$('#users-list').append( "<div id='player-list-row'><p class='msgBlock'>"+json[i].username+"</p><button id='challenge-btn_"+json[i].uId+"' type='submit' name='msg-btn' class='btn btn-primary custombtn challenge-btn'>Challenge</button></div>" );
						}
					}
				});

				//when a user click on the challenge button of another user
				//create a new challenge in the challenge table and change
				//change the button to "Pending"
				//Also, display the challenge in the other users interface; 
				$(document).on('click', ".challenge-btn", function() {
					let challengeeId = parseInt($(this).attr('id').split("_")[1]);	
					MyXHR('get',{method:"sendChallenge",a:"game",data: uId+"|"+challengeeId}).done(function(json){
					});  
					$(this).prop("disabled", true );
					$(this).html("Pending");     
				});

				//when a user clicks reject on a challenge it will remove
				//the challenge from the table and no longer display it in
				//the users challenges list
				$(document).on('click', ".reject-btn", function() {
					let challengeId = parseInt($(this).attr('id').split("_")[1]);
					$(this).closest("div").remove();
					MyXHR('get',{method:"rejectChallenge",a:"game",data: challengeId}).done(function(json){                    
					});  
				});

				//when a user clicks accept on a challenge call the acceptChallenge
				//ajax function which will add a new game in the database.
				$(document).on('click', ".accept-btn", function() {
					let challengeId = parseInt($(this).attr('id').split("_")[1]);
					$(this).closest("div").remove();
					MyXHR('get',{method:"acceptChallenge",a:"game",data: challengeId}).done(function(json){                    
						startGame(json);
					});  
				});

				//when a user clicks on the "enter game" button the
				//startGame function will be called and will take the player
				//to the game page for that specific session
				$(document).on('click', ".game-btn", function() {
					let gameId = parseInt($(this).attr('id').split("_")[1]);
					MyXHR('get',{method:"setGameSession",a:"game",data: gameId}).done(function(json){  
						startGame(json);
					});            
      	});
			});//End of document.ready
			


			// startGame() - method to take player to game
			//		gameId - gameId for that game record 
      function startGame(gameId) {
        window.location.href = "game.php";
      }

			// displayChallenges() - method to get a list of challenges
			//and display only the challenges for the current user
			//session that is logged in by appending a div with the 
			//challenge info and button
			function displayChallenges() {
				MyXHR('get',{method:"getChallenges",a:"game",data: uId}).done(function(json){
					$('#challenge-list').empty();
					$('#challenge-list').append('<div class="card-header text-white bg-warning">Challenges</div>');
					if(json !== null) {
						for (let i = 0; i < json.length; i++) {
							//if the current user is the one being challenged, display the challenge
							if(json[i].challengeeId == uId) {
								$('#challenge-list').append( "<div id='player-list-row'><p class='msgBlock'>"+json[i].username+"</p><button id='accept-btn_"+json[i].challengeId+"' type='submit' name='accept-btn' class='btn btn-primary custombtn accept-btn'>Accept</button><button id='reject-btn_"+json[i].challengeId+"' type='submit' name='reject-btn' class='btn btn-danger custombtn reject-btn'>Reject</button></div>" );
							}
							//if the current user is the one who initiated the challenge, 
							//prohibit them from challenging player again
							if (json[i].challengerId == uId) {
								let oppId = json[i].challengeeId;
								let eleId = "challenge-btn_"+oppId;
								$('#'+eleId).html('Pending');
								$('#'+eleId).prop( "disabled", true );
							}
						}
					}                    
				}); 
			}

			// displayActiveGames() - method to get a list of game
			//and display only the games for the current user
			//session that is logged in by appending a div with the Enter Game button
			function displayActiveGames() {
				MyXHR('get',{method:"getGames",a:"game",data: uId}).done(function(json){
					$('#active-games-list').empty();
					$('#active-games-list').append('<div class="card-header text-white bg-warning">Games</div>');
					if(json !== null) {
						for (let i = 0; i < json.length; i++) {
							let you;
							let opponent;
							if(json[i].uId == uId) { 
								opponent = json[i].player2Id;
							} else {
								opponent = json[i].player1Id;
							}
							if(json[i].uId != uId) {
								$('#active-games-list').append( "<div id='game-list-row'><p class='msgBlock'>VS: "+json[i].username+" <small>(gameId: "+json[i].gameId+")</small></p><button id='game-btn_"+json[i].gameId+"' type='submit' name='game-btn' class='btn btn-primary custombtn game-btn'>Enter Game</button></div>" );
							}

						}
					}                    
				}); 
			}

			function displayMessages() {
				let userLoginTime = "<?php echo $_SESSION['timeLoggedIn']; ?>";
				MyXHR('get',{method:"getLastChatMsg",a:"chat", data:lastMsgTimestamp}).done(function(json){	
					if(json !== null && lastMsgTimestamp === null) {
						lastMsgTimestamp = json[0].timestamp;
					} else if (json !== null && lastMsgTimestamp !== null) {
						for(let i = 0; i < json.length; i++) {
							$('#chat-window').append( "<div class='chat-msg-div'><p class='msgBlock card-text username-col col-1'>"+json[i].username+": </p><div class='chat-msg-bubble col-10'><p class='col-9'>"+json[i].msg+"</p><p class='col-3'>"+json[i].timestamp+"</p></div></div>" );
						}
						lastMsgTimestamp = json[json.length-1].timestamp;
					} else if (json === null && lastMsgTimestamp !== null) {
					}
				});		
			}

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
				})
			} 
    </script>
  </head><!-- /head -->
  <body> 
    <nav>
      <div class='navbar navbar-expand-md navbar-custom'>
        <div class='nav col-11'>
          <h4 class='nav-link nav-title'>Connect 4</h4>        
        </div>
	      <form class='col-1 form-inline my-1 my-lg-0' name='logout-form' id='logout-form'>
          <button class='btn btn-outline-primary my-2 my-sm-0' type='submit'>Logout</button>
        </form>
      </div>
		</nav>
		
    <div class='row container-fluid' id="main-wrapper">
      <div id="public-chat" class='card border-warning col-9'>
      <div class="card-header text-white bg-warning" id="chat-header">Chat</div>
        <div id="chat-window" class="card-body text-dark"></div>
    		<form id="chat-form" method="post">
      		<input type="text" name="chat-msg" class="form-control border-warning" id="chat-msg" placeholder="Enter Message">
      		<button id="msg-btn" type="submit" name="msg-btn" class="btn btn-primary custombtn">Send</button>
    		</form>
    	</div><!-- public-chat -->
      <div id="stacked-lists" class='col-3'>
        <div id="users-list" class="card border-warning">
        	<div class="card-header text-white bg-warning">Players</div>
        </div>
        <div id="challenge-list" class="card border-warning">
          <div class="card-header text-white bg-warning">Challenges</div>
        </div>
        <div id="active-games-list" class="card border-warning">
        	<div class="card-header text-white bg-warning">Games</div>
        </div>
      </div><!-- stacked-lists -->
    </div><!-- main-wrapper -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body><!-- /body -->
</html>


