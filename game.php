<?php
  session_name('user-session');
  if(!isset($_SESSION)){
    session_start();
  }

  if (!$_SESSION['loggedIn']) {
    header('Location: index.php');
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
    <script src="Objects/Cell.js" type="text/javascript"></script>
    <script src="Objects/Piece.js" type="text/javascript"></script>
    <script src="gameFunctions.js" type="text/javascript"></script>
	<!-- <script src="gameFunctions.js" type="text/javascript"></script> -->
    <script>

			//variables
			let gameId = "<?php echo $_SESSION['curr_game'];?>";
			let uId = "<?php echo $_SESSION['uId']; ?>";
			let turn;
			let svgns = "http://www.w3.org/2000/svg";
			let xhtmlns = "http://www.w3.org/1999/xhtml";
			var pieceEle=document.createElementNS(svgns,'circle');
			let pieceColor;
			let player1;
			let player2;
			let winner = false;
			let BOARDX = 50;
			let BOARDY = 200;				
			let boardArr = new Array();		
			let pieceArr = new Array();		
			let BOARDWIDTH = 7;
			let BOARDHEIGHT = 6;			
			let CELLSIZE = 100;
			let player = null;

        $(document).ready(function(){
						
					//variables to use inside of document.ready
          let currentColumn;
          let turnText;
					//build board
					init(gameId);
					if(!winner) { //if there is still no winner, allow game to be played
						setInterval(loadMoves, 1000);
						setInterval(checkTurn, 1000);
						setInterval(displayMessages, 2000);

						//build game piece and append above board
						pieceEle.setAttributeNS(null,'r','40px');
						pieceEle.setAttributeNS(null, 'cx','100px');
						pieceEle.setAttributeNS(null, 'cy','150px');
						pieceEle.setAttributeNS(null,'fill','black');
						pieceEle.setAttributeNS(null,'stroke','gray');
						pieceEle.setAttributeNS(null,'stroke-width','5px');
						pieceEle.setAttributeNS(null,'id',"active-piece");
						document.getElementById('svgEle').appendChild(pieceEle);

						//while the user moves there mouse, move the game piece
						document.onmousemove = function(evt){
							//variables for game piece (positions, boundries)
							var currentPos = document.getElementById("gId_"+gameId).getBoundingClientRect();
							var currentWidth = currentPos.right - currentPos.left;
							var board = document.getElementById('gId_'+gameId);
							let pieceX = evt.pageX;
							let pieceY = evt.pageY;
							let leftBoundry = currentPos.left;
							let rightBoundry = currentPos.right;
							//keep game piece above the board
							document.getElementById("active-piece").setAttributeNS(null, 'cy','150px');
							
							//move game piece back and forth within the bounds
							if(pieceX >= (leftBoundry+50) && pieceX <= (rightBoundry-50)) {
								document.getElementById("active-piece").setAttributeNS(null, 'cx',(pieceX-currentPos.left+50)+'px');
							} else if (pieceX < (leftBoundry+50) ) {
								document.getElementById("active-piece").setAttributeNS(null, 'cx',(100)+'px');
								currentColumn = 1;
							} else if (pieceX > (rightBoundry-50)) {
								document.getElementById("active-piece").setAttributeNS(null, 'cx',(currentWidth)+'px');
								currentColumn = 7;
							}       
							if (pieceX > leftBoundry && pieceX <= (leftBoundry + 95)) {
								currentColumn = 0;
							} else if (pieceX >= (leftBoundry + 105) && pieceX <= (leftBoundry + 195)) {
								currentColumn = 1;
							} else if (pieceX >= (leftBoundry + 205) && pieceX <= (leftBoundry + 295)) {
								currentColumn = 2;
							} else if (pieceX >= (leftBoundry + 305) && pieceX <= (leftBoundry + 395)) {
								currentColumn = 3;
							} else if(pieceX >= (leftBoundry + 405) && pieceX <= (leftBoundry + 495)) {
								currentColumn = 4;
							} else if(pieceX >= (leftBoundry + 505) && pieceX <= (leftBoundry + 595)) {
								currentColumn = 5;
							} else if (pieceX >= (leftBoundry + 605) && pieceX <= (leftBoundry + 695)) {
								currentColumn = 6;
							} else {
								currentColumn = null;
							}

							//when user clicks add their move to the db
							//fill in spot on board with their piece color
							document.onclick = function(e){
								//make sure it is their turn
								if (turn == uId) {
									let moveCell;
									//add to the moves table in DB
									MyXHR('get',{method:"makeMove",a:"game",data: gameId + "|" + turn + "|" + currentColumn}).done(function(json){
										if (json != null && json.length > 0) {
											if(uId == player1) {
												pieceColor = "black";
											} else {
												pieceColor = "red";
											}
											
											//fill in cell
											moveCell = `cell_${json[0].row}${json[0].col}`;
											let moveCellEle = document.getElementById(moveCell);
											let moveCellX = moveCellEle.getBoundingClientRect().left;
											let moveCellY = moveCellEle.getBoundingClientRect().top;
											let board = document.getElementById("gId_"+gameId);		
											let dropPiece = document.createElementNS(svgns,'circle');
											dropPiece.setAttributeNS(null,'r','45px');
											dropPiece.setAttributeNS(null, 'cx',((json[0].col+1)*100)-50+'px');
											dropPiece.setAttributeNS(null, 'cy', ((json[0].row+1)*100)-50+'px');
											dropPiece.setAttributeNS(null,'fill',pieceColor);
											dropPiece.setAttributeNS(null,'stroke','gray');
											dropPiece.setAttributeNS(null,'stroke-width','1px');
											dropPiece.setAttributeNS(null,'id',"boardPiece_"+moveCell);
											board.appendChild(dropPiece);

											//after cell is filled in change the turn in the game table in DB
											changeTurn(gameId);
											checkTurn();
										}
									});
								} else {
										//not your turn, you need to wait
								}
							}
						}
					}

					//when user submits chat form, send message to chat table in DB
					$('#private-chat-form').submit(function(e){
						let msg = $("#chat-msg").val();
						let chatId = gameId;
						MyXHR('get',{method:"sendMsg",a:"chat",data: msg+"|"+uId+"|"+gameId}).done(function(json){                    
								$("#chat-msg").val('');
						});
						e.preventDefault();
					});

					// when a user clicks logout, kill the session and return them to 
					// index.php					
					$('#logout-form').submit(function(e){            
						MyXHR('get',{method:"userLogout",a:"user",data: uId}).done(function(json){
								if(json) {
										window.location.href = "index.php";
								} else {
								}
						});
						e.preventDefault();
					});
				});
		
			
				// init() - method to build the game board
				//		gameId - gameId from table		
				function init(gameId){
					//create a parent to stick board in.
					var gEle=document.createElementNS(svgns,'g');
					gEle.setAttributeNS(null,'transform','translate('+BOARDX+','+BOARDY+')');
					gEle.setAttributeNS(null,'id','gId_'+gameId);
					//stick g on board
					document.getElementsByTagName('svg')[0].insertBefore(gEle,document.getElementsByTagName('svg')[0].childNodes[5]);
					//create the board...
					//var x = new Cell(document.getElementById('someIDsetByTheServer'),'cell_00',CELLSIZE,0,0);
					for(i=0;i<BOARDWIDTH;i++){
						boardArr[i]=new Array();
						for(j=0;j<BOARDHEIGHT;j++){
							boardArr[i][j]=new Cell(document.getElementById('gId_'+gameId),'cell_'+j+i,CELLSIZE,j,i);
						}
					}
				}

				// displayMessages() - method to display message in chat window
    		function displayMessages() {
					MyXHR('get',{method:"getPriavateChatMsgs",a:"chat", data: gameId}).done(function(json){
						$('#private-chat-window').html("");
						if(json != null) {
							for(let i = 0; i < json.length; i++) {
								$('#private-chat-window').append("<div class='private-chat-msg-bubble'><p class='msgBlock card-text'>"+json[i].username+": "+json[i].msg+"</p></div>" );
							}
						}
					});  
				}
				

				// loadMoves() - method to display the moves for this specific game
				// on the svg board
    		function loadMoves() {
        	MyXHR('get',{method:"getMoves",a:"game",data: gameId}).done(function(json){
            if(json != null) {
							for(let i = 0; i < json.length; i ++) {
								moveCell = `cell_${json[i].row}${json[i].col}`;
									if(json[i].playerId == player1) {
										pieceColor = "black";
									} else {
										pieceColor = "red";
									}
									let moveCellEle = document.getElementById(moveCell);
									let moveCellX = moveCellEle.getBoundingClientRect().left;
									let moveCellY = moveCellEle.getBoundingClientRect().top;
									let board = document.getElementById("gId_"+gameId);									
									let dropPiece = document.createElementNS(svgns,'circle');
									dropPiece.setAttributeNS(null,'r','45px');
									dropPiece.setAttributeNS(null, 'cx',((json[i].col+1)*100)-50+'px');
									dropPiece.setAttributeNS(null, 'cy', ((json[i].row+1)*100)-50+'px');
									dropPiece.setAttributeNS(null,'fill', pieceColor);
									dropPiece.setAttributeNS(null,'stroke','gray');
									dropPiece.setAttributeNS(null,'stroke-width','1px');
									dropPiece.setAttributeNS(null,'id',"boardPiece_"+moveCell);
									board.appendChild(dropPiece);
							}
						
							// when there are enough moves for a win, start checking
							// for a winning combination
							if (json.length >= 7) {
								//call checkWinner with the current list of moves for this game
								checkWinner(json);
							}
						}
					});
    		}

			// checkWinner() - method to display the moves for this specific game
			// on the svg board
			//		json - list of moves from current game
    	function checkWinner(json) {
        //get last move
        let lastMove = json[json.length-1]; // checking win on last move
        let player = lastMove.playerId; // playerId who placed last move
        let lastMoveRow = lastMove.row; // row
        let lastMoveCol = lastMove.col; // col
        let count = 1; //lastMove is 1 out of 4 needed to win
        let adjCells = getAllAdjCells(lastMoveRow, lastMoveCol); // get all surrounding adj cells of last move
        let adjOccupiedCells = [] // get all ajd cells that have pieces in them for checking
        let direction = ""; //direction that we're currently checking in

        //get list of all adj cells to the last move that have pieces in them that belong to same player
        for (let i = 0; i < json.length; i++) {
					for(let j = 0; j < adjCells.length; j++) {
						if(json[i].row == adjCells[j].rowNum && json[i].col == adjCells[j].colNum && json[i].playerId == player) {
							adjOccupiedCells.push({rowNum: adjCells[j].rowNum, colNum: adjCells[j].colNum});
						} else {
							//keep looping
						}
					}
        }

				//loop through adjCells and start traversing
				//check the direction of the adj cell and check both sides
				//to see if there are 4 matches
        for(let j = 0; j < adjOccupiedCells.length; j++) {
          if(count != 4) {
						count++; //second piece in row
						let adjPieceRow = adjOccupiedCells[j].rowNum;
						let adjPieceCol = adjOccupiedCells[j].colNum;
						let direction = getDirection(lastMoveRow,lastMoveCol,adjPieceRow,adjPieceCol);
						let match = false;

						//traverse left
						if (direction == "w"){
							match = checkLeft(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 to the left
								count++;
								match = checkLeft(adjPieceRow,(adjPieceCol-1),player,json);
								if (match) { //match #4 to the left
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}
												
								} else { //no match #4 to left, check right
									match = checkRight(lastMoveRow,lastMoveCol,player,json);
									if(match) { //match number 4 to the right
										count++;
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else { //no win for this adjCell
										break;
									}
								}
							} else { //no match #3 to the left, check right
								match = checkRight(lastMoveRow,lastMoveCol,player,json);
								if(match) { //match #3 to the right
									count++
									match = checkRight(lastMoveRow,(lastMoveCol+1),player,json);
									if(match) { //match #4 to the right
										count++
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else {
										break;
									}
								} else {
									break;
								}
							}
						}
						else if (direction == "e"){
							match = checkRight(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 to the right
								count++;
								match = checkRight(adjPieceRow,(adjPieceCol+1),player,json);
								if (match) { //match #4 to the right
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}

								} else { //no match #4 to right, check left
									match = checkLeft(lastMoveRow,lastMoveCol,player,json);
									if(match) { //match number 4 to the left
										count++;
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else { //no win for this adjCell
										break;
									}
								}
							}
						}


						else if (direction == "s"){
							match = checkDown(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 below
								count++;
								match = checkDown((adjPieceRow+1),adjPieceCol,player,json);
								if (match) { //match #4 below
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}
								}
							}
						}


						else if (direction == "nw"){
							match = checkUpLeft(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 to the upper left
								count++;
								match = checkLeft((adjPieceRow-1),(adjPieceCol-1),player,json);
								if (match) { //match #4 to the upper left
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}
								} else { //no match #4 to upper left, check bottom right
									match = checkDownRight(lastMoveRow,lastMoveCol,player,json);
									if(match) { //match number 4 to the bottom right
										count++;
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else { //no win for this adjCell
										break;
									}
								}
							} else { //no match #3 to the upper left, check bottom right
								match = checkDownRight(lastMoveRow,lastMoveCol,player,json);
								if(match) { //match #3 to the bottom right
									count++
									match = checkDownRight((lastMoveRow+1),(lastMoveCol+1),player,json);
									if(match) { //match #4 to the bottom right
										count++
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else {
										break;
									}
								} else {
									break;
								}
							}
						}
						else if (direction == "ne"){
							match = checkUpRight(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 to the upper right
								count++;
								match = checkUpRight((adjPieceRow-1),(adjPieceCol+1),player,json);
								if (match) { //match #4 to the upper right
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}
								} else { //no match #4 to upper right, check bottom left
									match = checkDownLeft(lastMoveRow,lastMoveCol,player,json);
									if(match) { //match number 4 to the bottom left
										count++;
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else { //no win for this adjCell
										break;
									}
								}
							} else { //no match #3 to the upper right, check bottom left
								match = checkDownLeft(lastMoveRow,lastMoveCol,player,json);
								if(match) { //match #3 to the bottom left
									count++
									match = checkDownLeft((lastMoveRow+1),(lastMoveCol-1),player,json);
									if(match) { //match #4 to the bottom left
										count++
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else {
										break;
									}
								} else {
										break;
								}
							}
						}
						else if (direction == "sw"){
							match = checkDownLeft(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 to the bottom left
								count++;
								match = checkDownLeft((adjPieceRow+1),(adjPieceCol-1),player,json);
								if (match) { //match #4 to the bottom left
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}

								} else { //no match #4 to bottom left, check upper right
									match = checkUpRight(lastMoveRow,lastMoveCol,player,json);
									if(match) { //match number 4 to the upper right
										count++;
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else { //no win for this adjCell
										break;
									}
								}
							}
						}
						else if (direction == "se"){
							match = checkDownRight(adjPieceRow,adjPieceCol,player,json);
							if(match) { //match # 3 to the bottom right
								// console.log("First Match");
								count++;
								match = checkDownRight((adjPieceRow+1),(adjPieceCol+1),player,json);
								if (match) { //match #4 to the bottom right
									count++;
									winner = true;
									$('#turn-text').hide();
									$('#active-piece').hide();
									$('#svgEle').click(false);
									if (player == uId) {
										$('#winner-text').html("Congratulations, you have won!")
									} else {
										$('#winner-text').html("You have lost to your opponent!")
									}
								} else { //no match #4 to bottom right, check upper left
									match = checkUpLeft(lastMoveRow,lastMoveCol,player,json);
									if(match) { //match number 4 to the upper left
										count++;
										winner = true;
										$('#turn-text').hide();
										$('#active-piece').hide();
										$('#svgEle').click(false);
										if (player == uId) {
											$('#winner-text').html("Congratulations, you have won!")
										} else {
											$('#winner-text').html("You have lost to your opponent!")
										}
									} else { //no win for this adjCell
										break;
									}
								}
							}
						}
          } 
        }
			}// checkWinner

			
			// getDirection() - method to get the current direction of the check
			//		lastRow - the row number of the last piece played
			//		lastCol - the col number of the last piece played
			//		adjRow - the row number of the adj cell currently being checked
			//		adjCol - the col number of the adj cell currently being checked
			function getDirection(lastRow, lastCol, adjRow, adjCol) {
        if (adjRow == lastRow) {
          if (adjCol < lastCol) {
            //moving left
            return "w";
          }
          if (adjCol > lastCol) {
            //moving right
            return "e";
          }
        } else if (adjCol == lastCol) {
          //moving down
          return "s";
        } else if (adjRow < lastRow) { //diagonal up
          if (adjCol < lastCol) {
            //moving up-left
            return "nw";
          } else if (adjCol > lastCol) {
            //moving up-right
            return "ne";
          }
        } else if (adjRow > lastRow) { //diagonal down
          if (adjCol < lastCol) {
            //moving down-left
            return "sw";
        	} else if (adjCol > lastCol) {
          	//moving down-right
            return "se";
          }
        } else {
          return null;
        }
    	}


			// checkLeft() - method to check the cell to the left of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
    	function checkLeft(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == curRow && json[i].col == (curCol - 1) && json[i].playerId == player) {
            return true;
          }
   	   	}
    	}

			// checkRight() - method to check the cell to the right of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
    	function checkRight(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == curRow && json[i].col == (curCol + 1) && json[i].playerId ==player) {
            return true;
          }
        }
      }

			// checkDown() - method to check the cell to the bottom of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
    	function checkDown(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == (curRow+1) && json[i].col == curCol && json[i].playerId ==player) {
            return true;
          }
        }
    	}

		
			// checkUpLeft() - method to check the cell to the upper left of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
			function checkUpLeft(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == (curRow-1) && json[i].col == (curCol-1) && json[i].playerId ==player) {
            return true;
          }
        }
    	}


			// checkUpRight() - method to check the cell to the upper right of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
    	function checkUpRight(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == (curRow-1) && json[i].col == (curCol+1) && json[i].playerId ==player) {
            return true;
  	      }
        }
    	}


			// checkDownLeft() - method to check the cell to the bottom left of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
    	function checkDownLeft(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == (curRow+1) && json[i].col == (curCol-1) && json[i].playerId ==player) {
            return true;
          }
        }
    	}


			// checkDownRight() - method to check the cell to the bottom right of the current cell
			//		curRow - the row number of the current piece we are looking at
			//		curCol - the col number of the current piece we are looking at
			//		player - the id of the playe we are checking the win for
			//		json - list of all moves for this game 
    	function checkDownRight(curRow, curCol, player, json) {
        for (let i = 0; i < json.length; i++) {
          if (json[i].row == (curRow+1) && json[i].col == (curCol+1) && json[i].playerId ==player) {
            return true;
          }
        }
    	}



		// 	// getWinningCombo() - method to check the cell to the left of the current cell
		// 	//		row1 - the row number of the current piece we are looking at
		// 	//		col1 - the col number of the current piece we are looking at
		// 	//		ro2 - the id of the playe we are checking the win for
		// 	//		col2 - list of all moves for this game 
    // function getWinningCombo(row1,col1,row2,col2) {
    //     if(row1 < row2 && col1 == col2 && row1 <= 2) { //4 down
    //         return [{rowNum: row2+1,colNum: col1},{rowNum: row2+2,colNum: col1}];
    //     } 
    //     // else if (row1 > row2 && col1 == col2) { //4 up
    //     //     return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2-1,colNum: col1},{rowNum: row2-2,colNum: col1}];
    //     // } 
    //     else if (row1 == row2 && col1 > col2 && col1 >= 3) { //4 left
    //         return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2,colNum: col2-1},{rowNum: row2,colNum: col2-2}];
    //     } else if (row1 == row2 && col1 < col2 && col1 <= 3) { //4 right
    //         return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2,colNum: col2+1},{rowNum: row2,colNum: col2+2}];
    //     } else if (row1 > row2 && col1 > col2 && (row1 >= 3 && col1 >= 3)) { //4 up left
    //         return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2-1,colNum: col2-1},{rowNum: row2-2,colNum: col2-2}];
    //     } else if (row1 < row2 && col1 < col2  && (row1 <= 2 && col1 <= 3)) { //4 down right
    //         return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2+1,colNum: col2+1},{rowNum: row2+2,colNum: col2+2}];
    //     } else if (row1 < row2 && col1 > col2  && (row1 <= 2 && col1 >= 3)) { // 4 down left
    //         return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2+1,colNum: col2-1},{rowNum: row2+2,colNum: col2-2}];
    //     } else if (row1 > row2 && col1 < col2  && (row1 >= 3 && col1 <= 3)) { //4 up right
    //         return [{rowNum: row1,colNum: col1}, {rowNum: row2,colNum: col2},{rowNum: row2-1,colNum: col2+1},{rowNum: row2-2,colNum: col2+2}];
    //     } else {
    //         return null;
    //     }
    // }


			// getAllAdjCells() - method to get a list of all of the adjacent
			// cells to the cell passed in. Looks at boundries and edge of the board.
			//		row - row of piece to check
			//		col - col of piece to check
    	function getAllAdjCells(row,col) {
        let cells = [];
        if (row == 0) { //top row
          if (col >= 1 && col <= 5) {
						cells.push({rowNum: row,colNum:col-1}); //left
						cells.push({rowNum: row+1,colNum:col-1}); //bottom-left
						cells.push({rowNum: row+1,colNum:col}); //bottom
						cells.push({rowNum: row+1,colNum:col+1}); //bottom-right
						cells.push({rowNum:row, colNum:col+1}); //right
						return cells;
					} else if (col == 0) {
						cells.push({rowNum: row+1,colNum:col}); //bottom
						cells.push({rowNum: row+1,colNum:col+1}); //bottom-right
						cells.push({rowNum: row,colNum:col+1}); //right
						return cells;
					} else if (col == 6) {
						cells.push({rowNum: row,colNum:col-1}); //left
						cells.push({rowNum: row+1,colNum:col-1}); //bottom-left
						cells.push({rowNum: row+1,colNum:col}); //bottom
						return cells;
					}		
				} else if (row == 5) { //bottom row
					if (col >= 1 && col <= 5) {
						cells.push({rowNum: row,colNum:col-1}); //left
						cells.push({rowNum: row-1,colNum:col-1}); //top-left
						cells.push({rowNum: row-1,colNum:col}); //top
						cells.push({rowNum: row-1,colNum:col+1}); //top-right
						cells.push({rowNum:row,colNum:col+1}); //right
						return cells;
					} else if (col == 0) {
						cells.push({rowNum: row-1,colNum:col}); //top
						cells.push({rowNum: row-1,colNum:col+1}); //top-right
						cells.push({rowNum:row,colNum:col+1}); //right
						return cells;
					} else if (col == 6) {
						cells.push({rowNum: row,colNum:col-1}); //left
						cells.push({rowNum: row-1,colNum:col-1}); //top-left
						cells.push({rowNum: row-1,colNum:col}); //top
						return cells;
					}
				} else if (col == 0) { //left col
					if (row >= 1 && row <= 4) {
						cells.push({rowNum: row-1,colNum:col}); //top
						cells.push({rowNum: row-1,colNum:col+1}); //top-right
						cells.push({rowNum: row,colNum:col+1}); //right
						cells.push({rowNum: row+1,colNum:col+1}); //bottom-right
						cells.push({rowNum: row+1,colNum:col}); //bottom
						return cells;
					} else if (row == 0) {
						cells.push({rowNum: row,colNum:col+1}); //right
						cells.push({rowNum: row+1,colNum:col+1}); //bottom-right
						cells.push({rowNum: row+1,colNum:col}); //bottom
						return cells;
					} else if (row == 5) {
						cells.push({rowNum: row-1,colNum:col}); //top
						cells.push({rowNum: row-1,colNum:col+1}); //top-right
						cells.push({rowNum: row,colNum:col+1}); //right
						return cells;
					}
				} else if (col == 6) { //left col
					if (row >= 1 && row <= 4) {
						cells.push({rowNum: row-1,colNum:col}); //top
						cells.push({rowNum: row-1,colNum:col-1}); //top-left
						cells.push({rowNum: row,colNum:col-1}); //left
						cells.push({rowNum: row+1,colNum:col-1}); //bottom-left
						cells.push({rowNum: row+1,colNum:col}); //bottom
						return cells;
					} else if (row == 0) {
						cells.push({rowNum: row,colNum:col+1}); //right
						cells.push({rowNum: row+1,colNum:col+1}); //bottom-right
						cells.push({rowNum: row+1,colNum:col}); //bottom
						return cells;
					} else if (row == 5) {
						cells.push({rowNum: row-1,colNum:col}); //top
						cells.push({rowNum: row-1,colNum:col-1}); //top-left
						cells.push({rowNum: row,colNum:col-1}); //left
						return cells;
					}
				} else { //center cells
					cells.push({rowNum: row-1,colNum:col}); //top
					cells.push({rowNum: row-1,colNum:col-1}); //top-left
					cells.push({rowNum: row,colNum:col-1}); //left
					cells.push({rowNum: row+1,colNum:col-1}); //bottom-left
					cells.push({rowNum: row+1,colNum:col}); //bottom
					cells.push({rowNum: row-1,colNum:col+1}); //top-right
					cells.push({rowNum: row,colNum:col+1}); //right
					cells.push({rowNum: row+1,colNum:col+1}); //bottom-right
					return cells;
				}
    	}


			// changeTurn() - method to get make a update to the game table in
			// the DB to change the player turn
			//		gameId - gameId of current game
			function changeTurn(gameId) {
        let nextTurn;
        if (turn == player1) {
            nextTurn = player2;
        } else {
            nextTurn = player1;
        }
        MyXHR('get',{method:"changeTurn",a:"game",data: gameId + "|" + nextTurn}).done(function(json){
        });
        turn = nextTurn;
    	}

			// checkTurn() - method to get current turn and update the user interface
			// as well as the game piece color
    	function checkTurn() {
        MyXHR('get',{method:"checkTurn",a:"game",data: gameId}).done(function(json){
					player1 = json[0].player1Id;
					player2 = json[0].player2Id;
					turn = json[0].whoseTurn;
					if (uId == player1) {
						pieceEle.setAttributeNS(null,'fill','black');
					} else {
						pieceEle.setAttributeNS(null,'fill','red');
					}
					if (turn == uId) {
						pieceEle.style.display = "block";
						turnText = "It is your turn!";
						$("#turn-text").html(turnText);
					} else {
						pieceEle.style.display = "none";
						turnText = "It is not your turn!";
						$("#turn-text").html(turnText);
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
    <style type="text/css">
		#background { fill: #ffc107; rx: 3px; ry: 3px; }
		body{padding:0px;margin:0px;}
    text{pointer-events:none;user-select:none;}
    .cell-rect{fill:yellow;stroke-width:2px;stroke:red;}    
	</style>
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
    <div class='row container-fluid' id="game-wrapper">
      <svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="900px" id="svgEle" class="col-9">
        <rect x="50px" y="50px" width="700px" height="50px" id="background">        
        </rect>
        <text x="60px" y="70px" fill="white" id="turn-text">
        </text>
        <text x="60px" y="80px" fill="white" id="winner-text">
        </text>            
      </svg>
      <div id="private-chat" class='card border-warning col-3'>
        <div class="card-header text-white bg-warning" id="chat-header">Chat</div>
        <div id="private-chat-window" class="card-body text-dark">
        </div>
        <form id="private-chat-form" method="post">
          <input type="text" name="chat-msg" class="form-control border-warning" id="chat-msg" placeholder="Enter Message">
          <button id="msg-btn" type="submit" name="msg-btn" class="btn btn-primary custombtn">Send</button>
        </form>
      </div><!-- private-chat -->
    </div><!-- game-wrapper -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body><!-- /body -->
</html>