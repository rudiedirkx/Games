<?php
// HOW FAST ARE YOU?

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>How fast are you?</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
</head>

<body>
<script>
"use strict";

var savetime = 0;
var gotimer = 0;

function change_some_color()
{
	// Change color and start the timer...
	// Change color
	document.getElementById('table1').style.background = 'red';
	// Start timer (save current time)
	savetime = Date.now();	// in Milliseconds
}

function start_random_count()
{
	// Background to normal
	document.getElementById('table1').style.background = '';
	// savetime to -1
	savetime = -1;
	var randtime = 1 + 4 * Math.random();
	gotimer = setTimeout(change_some_color, randtime*1000);
}

function user_clicks_stop()
{
	if ( savetime == -1 )
	{
		alert('Dont cheat! Start again!');
		clearTimeout(gotimer);
	}
	else if ( savetime == 0 )
	{
		alert('Click START first!');
	}
	else
	{
		// Check current time and compare it to savetime
		var stoptime = Date.now();	// in Milliseconds
		var tooktime = stoptime - savetime;
		if ( tooktime < 1 )
		{
			alert('You cheater! Start again.');
		}
		else
		{
			var msg;
				 if ( tooktime > 400 )	msg = 'You can do better than that!';
			else if ( tooktime > 300 )	msg = "That's okay.";
			else if ( tooktime > 200 )	msg = "That's pretty good.";
			else						msg = "You're fast!";
			alert(msg + '\n\nIt took you ' + (tooktime/1000) + ' seconds.');
		}
	}
}
</script>

<table border="1" cellpadding="10" cellspacing="2" width="100%" id="table1">
	<tr>
		<td colspan="2">
			Click the STOP button as fast as you can after the table turns red (after clicking START ofcourse)!
		</td>
	</tr>
	<tr>
		<td><input type="button" value="START" onclick="start_random_count()"></td>
		<td><input type="button" value="STOP" onclick="user_clicks_stop()"></td>
	</tr>
</table>
