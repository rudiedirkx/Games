<?php

session_start();
if ( !isset($_SESSION['number_guessing']['tries']) || !is_numeric($_SESSION['number_guessing']['tries']) )
{
	$_SESSION['number_guessing']['tries'] = 0;
}

if ( isset($_GET['save']) && empty($_GET['save']) )
{
	if ( $_SESSION['number_guessing']['tries'] )
	{
		$fp = fopen("saves.number_guessing.txt", "a");
		$newline = date("Y-m-d H:i:s")."\t".$_SERVER['REMOTE_ADDR']."\t".$_SESSION['number_guessing']['tries'];
		fwrite($fp, $newline.PHP_EOL);
		$_SESSION['number_guessing']['tries'] = 0;
	}
	exit;
}
else if ( isset($_GET['increment']) && empty($_GET['increment']) )
{
	$_SESSION['number_guessing']['tries']++;
	exit;
}
else if ( $_SERVER['REQUEST_METHOD'] == "POST" )
{
	print_r( $_POST );
	exit;
}

?>
<html>

<head>
<title>Guess my number from 1-100</title>
<script language="javascript">
var retVal = '';

function getHTTP( )
{
	var http_request = false;

	if (window.XMLHttpRequest) // Mozilla, Safari,...
	{
		// alert("new XMLHttpRequest()");
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType)
		{
			http_request.overrideMimeType('text/xml');
			// See note below about this line
		}
	}
	else if (window.ActiveXObject) // IE
	{
		try
		{
			// alert("Msxml2.XMLHTTP");
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				// alert("Microsoft.XMLHTTP");
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {}
		}
	}

	if ( !http_request )
	{
		alert('Giving up :( Cannot create an XMLHTTP instance');
		return false;
	}
	return http_request;
}
function sendRequest( url, method, content, async )
{
	if ( !method )	method	= "GET";
	if ( !async )	async	= true;
	if ( !content )	content	= null;

	http_request = getHTTP( );
	if ( http_request && url )
	{
		http_request.onreadystatechange = function() { handleRequest(http_request); };
		http_request.open(method, url, async);
		if ( "post" == method.toLowerCase() )
		{
			http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http_request.setRequestHeader("Content-length", content.length);
			http_request.setRequestHeader("Connection", "close");
		}
		http_request.send(content);

		return true;
	}

	return false;
}
function handleRequest( http_request )
{
	if ( fetchState( http_request ) )
	{
		retVal = http_request.responseText;
		// alert(retVal);
		// document.getElementById("debug-div").innerHTML = retVal;
		return;
	}
}
function fetchState( http_request )
{
	// [readyState]
	// 0 = uninitialized
	// 1 = loading
	// 2 = loaded
	// 3 = interactive
	// 4 = complete
	if (http_request.readyState == 4)
	{
		// [status]
		// 200 = OK
		// 404 = Not Found
		if (http_request.status == 200)
		{
			return true;
		}
		else
		{
			alert("Problem retrieving XML data");
		}
	}
	return false;
}
</script>
<script language="javascript">
// Store the random number that the user has to guess
var my_number = rand( 100 );
// Start a new game
function new_game( )
{
	// Clear the guess form field
	document.getElementById("user-guess").value = "";

	// Generate a new random number
	my_number = rand( 100 );

	// Tell the user that we're ready for them to guess
	alert("OK, I'm thinking of another number...");
}
// Process the user's guess
function make_guess( )
{
	// Get the user's guessed number
	guess_field = document.getElementById("user-guess");
	var user_guess = guess_field.value;

	guess_field.value = '';
	guess_field.focus();

	// Increment count with one
	sendRequest('?increment');

	// Warn if they haven't entered a number between 1 and 100
	if ( isNaN( user_guess ) || 1 > user_guess || 100 < user_guess )
	{
		alert("Please enter a guess between 1 and 100");
		return;
	}

	// Compare the guessed number against the computer's number, and respond accordingly
	if ( user_guess > my_number )
	{
		alert("Too high - try again!");
	}
	else if ( user_guess < my_number )
	{
		alert("Too low - guess a higher number!");
	}
	else
	{
		alert("You got it! My number was " + my_number);
		// Save 'score'
		sendRequest('?save');
		new_game ( );
	}
}
function rand( n )
{
	rand_num = Math.floor ( Math.random ( ) * n + 1 );
	return rand_num;
}
</script>
</head>

<body onload="document.getElementById('user-guess').focus();">
<script language="javascript">document.write(document.title + ":<br />\n");</script>
<input id="user-guess" type="text" size="2" value="" />
<input type="button" value="Guess" onclick="make_guess()" /> <?php /* echo $_SESSION['number_guessing']['tries']; */ ?>
<?php
/*
<br />

<input type="button" value="HTTP Request Test" onclick="sendRequest('?fetch');"><br />
<input type="button" value="alert retVal" onclick="alert(retVal);"><br />
<input type="button" value="request & print POST data" onclick="sendRequest('?','POST','user=rudie&pass=nasjarules&activate=1');"><br />

<br />

<div id="debug-div" style="white-space:pre;font-family:'courier new';font-size:10pt;"></div>
*/
?>
</body>

</html>