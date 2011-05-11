<?php
// HOW FAST ARE YOU?

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

?>
<script language="javascript">
savetime = 0;

function change_some_color( )
{
	// Change color and start the timer...
	// Change color
	document.getElementById('table1').style.background = 'red';
	// Start timer (save current time)
	nu = new Date();
	savetime = nu.getTime();	// in Milliseconds
}

function start_random_count( )
{
	// Background to normal
	document.getElementById('table1').style.background = '';
	// savetime to -1
	savetime = -1;
	randtime = 1 + 4 * Math.random( );
	gotimer = setTimeout("change_some_color()", randtime*1000);
}

function user_clicks_stop( )
{
	if ( savetime == -1 )
	{
		alert('Dont cheat! Start again!');
		clearTimeout( gotimer );
	}
	else if ( savetime == 0 )
	{
		alert('Click START first!');
	}
	else
	{
		// Check current time and compare it to savetime
		nu = new Date();
		stoptime = nu.getTime();	// in Milliseconds
		tooktime = stoptime - savetime;
		if ( tooktime < 1 )
		{
			alert('You cheater! Start again.');
		}
		else
		{
			// alert( 'The average of this game is about 250 ms...' );
			if ( tooktime > 300 )		msg = 'Seriously!? You can do better than that!';
			else if ( tooktime > 250 )	msg = 'Lame!';
			else if ( tooktime > 200 )	msg = 'Is ok, but still rubbish!';
			else if ( tooktime > 150 )	msg = 'This is g00d! But you can do better ;)';
			else if ( tooktime > 100 )	msg = 'Damn you fast!!';
			else						msg = 'HOLIECOW! You must be superhuman!';
			alert( msg + ' - It took you ' + (tooktime/1000) + ' seconds.');
		}
	}
}
</script>

<table border="1" cellpadding="10" cellspacing="2" width="200" id="table1">
<tr>
<td colspan="2">Click the STOP button as fast as you can after the table turns red (after clicking START ofcourse)!</td>
</tr>
<tr>
<td><input type="button" value="START" onclick="start_random_count( );"></td>
<td><input type="button" value="STOP" onclick="user_clicks_stop( );"></td>
</tr>
</table>