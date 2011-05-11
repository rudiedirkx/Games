<?php

//session_start();
if ( !isset($_SESSION['number_guessing']['tries']) || !is_numeric($_SESSION['number_guessing']['tries']) )
{
//	$_SESSION['number_guessing']['tries'] = 0;
}

if ( isset($_GET['save']) && "true" == $_GET['save'] )
{
	if ( $_SESSION['number_guessing']['tries'] )
	{
//		$fp = fopen("saves.127.txt", "a");
//		$newline = date("Y-m-d H:i:s")."\t".$_SERVER['REMOTE_ADDR']."\t".$_SESSION['number_guessing']['tries'];
//		fwrite($fp, $newline.PHP_EOL);
//		$_SESSION['number_guessing']['tries'] = 0;
	}
	exit;
}
else if ( isset($_GET['increment']) && "true" == $_GET['increment'] )
{
//	$_SESSION['number_guessing']['tries']++;
	exit;
}
else if ( $_SERVER['REQUEST_METHOD'] == "POST" )
{
//	print_r( $_POST );
	exit;
}

?>
<html>

<head>
<title>Guess my number from 1-100</title>
<script language="javascript" type="text/javascript" src="general_1_2_4.js"></script>
<script language="javascript" type="text/javascript">
<!--//
// Store the random number that the user has to guess
var my_number = rand(100), g_iTries = 0;
// Start a new game
function new_game() {
	// Clear the guess form field
	g_iTries = 0;
	$('user-guess').value = "";

	// Generate a new random number
	my_number = rand( 100 );

	// Tell the user that we're ready for them to guess
	alert("OK, I'm thinking of another number...");
}
// Process the user's guess
function make_guess( )
{
	// Get the user's guessed number
	guess_field = $('user-guess');
	var user_guess = guess_field.value;

	g_iTries++;

	// Increment count with one
//	new Ajax.Request( false, {
//		'method'		: 'get',
//		parameters		: 'increment=true'
//	});

	// Warn if they haven't entered a number between 1 and 100
	if ( isNaN( user_guess ) || 1 > user_guess || 100 < user_guess )
	{
		alert("Please enter a guess between 1 and 100");
		return;
	}

	// Compare the guessed number against the computer's number, and respond accordingly
	if ( user_guess > my_number ) {
		alert("Too high - try again!");
	}
	else if ( user_guess < my_number ) {
		alert("Too low - guess a higher number!");
	}
	else {
		alert('You got it! My number was ' + my_number + '.');
		alert('It took you ' + g_iTries + ' tries.');
		// Save 'score'
//		new Ajax.Request( false, {
//			'method'		: 'get',
//			parameters		: 'save=true'
//		});
		new_game();
	}

	guess_field.value = '';
	guess_field.select();
}
function rand( n )
{
	rand_num = Math.floor ( Math.random ( ) * n + 1 );
	return rand_num;
}
//-->
</script>
</head>

<body onload="document.getElementById('user-guess').focus();">
<script language="javascript" type="text/javascript">document.write(document.title + ":<br />\n");</script>
<form action="">
<input id="user-guess" type="text" size="2" value="" />
<input type="button" value="Guess" onclick="make_guess()" />
</form>
</body>

</html>