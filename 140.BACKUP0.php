<?php
// THE BOX
// drunkmenworkhere: Q3, 8iR, rZ9a2, Dxg20aj, Hdf7, K4sU, 

session_start();

$bShowCoords	= false;
$bDebug			= false;

include("connect.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . "/_inc/json.php" );
define( "S_NAME", "bxb_user" );

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );


$_page		= isset($_POST['page'])		? strtolower(trim($_POST['page']))		: ( isset($_GET['page'])	? strtolower(trim($_GET['page']))	: '' );
$_action	= isset($_POST['action'])	? strtolower(trim($_POST['action']))	: ( isset($_GET['action'])	? strtolower(trim($_GET['action']))	: '' );



$boxes[0]	= 3;
$pusher[0]	= array(5,3);
$level[0]	= array(
	array("_","x","x","x","x","x","x"),
	array("x","x","o","o","t","o","x"),
	array("x","o","bt","o","x","o","x"),
	array("x","o","t","b","o","o","x"),
	array("x","o","o","x","b","o","x"),
	array("x","x","o","m","o","o","x"),
	array("_","x","o","o","o","o","x"),
	array("_","x","x","x","x","x","x")
);

$boxes[1]	= 3;
$pusher[1]	= array(1,4);
$level[1]	= array(
	array("x","x","x","x","x","x","x"),
	array("x","o","o","t","m","o","x"),
	array("x","o","x","t","x","o","x"),
	array("x","o","o","o","b","o","x"),
	array("x","t","b","b","o","o","x"),
	array("x","o","o","x","x","x","x"),
	array("x","x","x","x","_","_","_")
);

$boxes[2]	= 3;
$pusher[2]	= array(1,5);
$level[2]	= array(
	array("_","_","_","x","x","x","x"),
	array("x","x","x","x","o","m","x"),
	array("x","o","o","bt","b","o","x"),
	array("x","o","o","o","o","o","x"),
	array("x","x","o","t","x","x","x"),
	array("_","x","b","o","x","_","_"),
	array("_","x","o","t","x","_","_"),
	array("_","x","x","x","x","_","_")
);

$boxes[3]	= 3;
$pusher[3]	= array(3,5);
$level[3]	= array(
	array("x","x","x","_","x","x","x"),
	array("x","t","x","x","x","t","x"),
	array("x","o","x","o","o","t","x"),
	array("x","o","b","b","o","m","x"),
	array("x","o","o","b","o","o","x"),
	array("x","o","o","x","o","o","x"),
	array("x","o","o","x","x","x","x"),
	array("x","x","x","x","_","_","_")
);

$boxes[4]	= 3;
$pusher[4]	= array(1,5);
$level[4]	= array(
	array("_","_","_","x","x","x","x","_"),
	array("_","_","_","x","o","m","x","x"),
	array("x","x","x","x","o","o","o","x"),
	array("x","t","o","x","b","b","o","x"),
	array("x","o","o","o","o","o","x","x"),
	array("x","t","o","o","b","x","x","_"),
	array("x","x","t","o","o","x","_","_"),
	array("_","x","x","x","x","x","_","_")
);

$boxes[5]	= 3;
$pusher[5]	= array(4,2);
$level[5]	= array(
	array("x","x","x","x","x","_","_","_"),
	array("x","o","t","t","x","x","x","x"),
	array("x","o","b","o","o","o","o","x"),
	array("x","o","o","x","b","x","o","x"),
	array("x","o","m","o","t","b","o","x"),
	array("x","x","x","x","x","x","x","x")
);

$boxes[6]	= 3;
$pusher[6]	= array(4,4);
$level[6]	= array(
	array("_","_","x","x","x","x","x"),
	array("x","x","x","o","o","t","x"),
	array("x","o","b","o","x","o","x"),
	array("x","o","bt","b","o","o","x"),
	array("x","o","t","x","m","o","x"),
	array("x","o","o","o","o","x","x"),
	array("x","o","o","o","x","x","_"),
	array("x","x","x","x","x","_","_")
);

$boxes[7]	= 3;
$pusher[7]	= array(1,4);
$level[7]	= array(
	array("x","x","x","x","x","x","x","_"),
	array("x","t","o","o","m","t","x","_"),
	array("x","o","o","b","x","o","x","_"),
	array("x","o","x","o","b","t","x","x"),
	array("x","o","o","o","b","x","o","x"),
	array("x","x","x","x","o","o","o","x"),
	array("_","_","_","x","x","x","x","x")
);

$boxes[8]	= 3;
$pusher[8]	= array(3,4);
$level[8]	= array(
	array("x","x","x","x","x","_","_"),
	array("x","t","o","t","x","x","x"),
	array("x","t","x","b","b","o","x"),
	array("x","o","o","o","m","o","x"),
	array("x","o","b","x","o","o","x"),
	array("x","x","o","o","o","x","x"),
	array("_","x","x","x","x","x","_")
);

$boxes[9]	= 3;
$pusher[9]	= array(5,2);
$level[9]	= array(
	array("x","x","x","x","x","_","_"),
	array("x","t","o","o","x","x","x"),
	array("x","o","x","o","o","o","x"),
	array("x","o","t","o","x","o","x"),
	array("x","o","b","bt","b","o","x"),
	array("x","x","m","o","x","x","x"),
	array("_","x","o","o","x","_","_"),
	array("_","x","x","x","x","_","_")
);

$boxes[10]	= 3;
$pusher[10]	= array(3,1);
$level[10]	= array(
	array("x","x","x","x","x","x","x","x"),
	array("x","t","o","o","o","t","o","x"),
	array("x","o","x","o","x","o","o","x"),
	array("x","m","b","o","o","b","t","x"),
	array("x","x","x","x","x","o","b","x"),
	array("_","_","_","_","x","o","o","x"),
	array("_","_","_","_","x","x","x","x")
);

$boxes[11] = 3;
$pusher[11] = array(6,4);
$level[11] = array(
	array("x","x","x","x","_","_","_","_"),
	array("x","o","o","x","_","_","_","_"),
	array("x","o","o","x","x","x","x","x"),
	array("x","o","t","bt","o","o","o","x"),
	array("x","x","b","o","o","o","o","x"),
	array("_","x","o","x","b","x","x","x"),
	array("_","x","t","o","m","x","_","_"),
	array("_","x","x","x","x","x","_","_")
);

$boxes[12] = 3;
$pusher[12] = array(1,3);
$level[12] = array(
	array("_","x","x","x","x","x","_","_"),
	array("_","x","o","m","o","x","x","x"),
	array("x","x","o","t","o","o","o","x"),
	array("x","t","o","b","t","b","o","x"),
	array("x","x","b","x","o","x","x","x"),
	array("_","x","o","o","o","x","_","_"),
	array("_","x","x","x","x","x","_","_")
);

$boxes[13] = 3;
$pusher[13] = array(3,4);
$level[13] = array(
	array("_","x","x","x","x","x","_"),
	array("x","x","o","o","o","x","_"),
	array("x","o","b","x","o","x","_"),
	array("x","o","t","o","m","x","x"),
	array("x","o","bt","o","o","o","x"),
	array("x","x","o","x","b","o","x"),
	array("_","x","t","o","o","x","x"),
	array("_","x","x","x","x","x","_")
);

$boxes[14] = 3;
$pusher[14] = array(4,1);
$level[14] = array(
	array("_","x","x","x","x","_","_","_"),
	array("x","x","o","o","x","x","x","x"),
	array("x","t","t","b","o","o","t","x"),
	array("x","o","x","b","o","b","o","x"),
	array("x","m","o","o","x","o","o","x"),
	array("x","x","x","x","x","o","o","x"),
	array("_","_","_","_","x","x","x","x")
);

$boxes[15] = 3;
$pusher[15] = array(1,5);
$level[15] = array(
	array("_","x","x","x","x","x","x","_"),
	array("_","x","o","o","t","m","x","x"),
	array("_","x","o","o","o","b","t","x"),
	array("_","x","x","x","bt","x","o","x"),
	array("x","x","o","o","o","o","o","x"),
	array("x","o","o","b","o","o","x","x"),
	array("x","o","o","o","x","x","x","_"),
	array("x","x","x","x","x","_","_","_")
);

$boxes[16] = 3;
$pusher[16] = array(1,2);
$level[16] = array(
	array("_","x","x","x","x","_","_","_"),
	array("_","x","m","o","x","_","_","_"),
	array("_","x","o","o","x","_","_","_"),
	array("x","x","t","o","x","x","x","x"),
	array("x","o","b","b","t","o","t","x"),
	array("x","o","o","b","o","x","x","x"),
	array("x","x","x","o","o","x","_","_"),
	array("_","_","x","x","x","x","_","_")
);

$boxes[17] = 3;
$pusher[17] = array(5,3);
$level[17] = array(
	array("x","x","x","x","x","_","_"),
	array("x","t","o","o","x","_","_"),
	array("x","o","x","o","x","x","x"),
	array("x","o","bt","b","o","o","x"),
	array("x","o","o","b","t","o","x"),
	array("x","o","o","m","x","x","x"),
	array("x","x","x","x","x","_","_")
);

$boxes[18] = 3;
$pusher[18] = array(4,1);
$level[18] = array(
	array("_","_","x","x","x","x","x"),
	array("_","_","x","o","o","o","x"),
	array("_","_","x","o","x","t","x"),
	array("x","x","x","o","o","t","x"),
	array("x","m","o","b","b","o","x"),
	array("x","o","o","t","b","o","x"),
	array("x","x","x","x","x","x","x")
);

$boxes[19] = 3;
$pusher[19] = array(1,1);
$level[19] = array(
	array("x","x","x","x","x","x","_","_"),
	array("x","o","o","o","m","x","_","_"),
	array("x","o","b","x","o","x","x","x"),
	array("x","o","bt","o","b","o","o","x"),
	array("x","o","o","o","x","x","o","x"),
	array("x","x","t","o","o","t","o","x"),
	array("_","x","x","o","o","o","x","x"),
	array("_","_","x","x","x","x","x","_")
);


$eerste_level = 0;
if ( isset($_SESSION[S_NAME]['level']) )	$LEVEL = $_SESSION[S_NAME]['level'];
else											$LEVEL = $eerste_level;


/** NEW GAME **/
if ( isset($_POST['newgame_name']) )
{
	if ( goede_gebruikersnaam($_POST['newgame_name']) )
	{
		$_SESSION[S_NAME]['name'] = $_POST['newgame_name'];
		reset_game( $eerste_level );
	}

	Header("Location: ".BASEPAGE);
	exit;
}

/** RESET GAME **/
else if ( isset($_GET['action']) && $_GET['action'] == "retry")
{
	$l = ( isset($_SESSION[S_NAME]['gameover']) ) ? 0 : $_SESSION[S_NAME]['level'];
	reset_game( $l );

	Header("Location: ".BASEPAGE);
	exit;
}

/** STOP **/
else if ( isset($_GET['action']) && $_GET['action'] == "stop")
{
	$name = $_SESSION[S_NAME]['name'];
	$_SESSION[S_NAME] = NULL;
	$_SESSION[S_NAME]['name'] = $name;

	Header("Location: ".BASEPAGE);
	exit;
}

/** NEW LEVEL **/
else if ( isset($_GET['action']) && $_GET['action'] == "newlevel" && isset($_GET['newlevel']) )
{
	if ( isset($pusher[$_GET['newlevel']], $level[$_GET['newlevel']], $boxes[$_GET['newlevel']]) )
	{
		reset_game( $_GET['newlevel'] );
	}

	Header("Location: ".BASEPAGE);
	exit;
}

/** MOVE PUSHER **/
else if ( "move" == $_action && isset($_POST['to'][0], $_POST['to'][1]) )
{
	$_pusher = $_SESSION[S_NAME]['pusher'];
	$_coords = $_POST['to'];

	if ( $_coords[1] == $_pusher[1] && $_coords[0]+1 == $_pusher[0] )
	{
		// UP
		$dx1 = -1;
		$dx2 = -2;
		$dy1 = 0;
		$dy2 = 0;
	}
	else if ( $_coords[1] == $_pusher[1] && $_coords[0]-1 == $_pusher[0] )
	{
		// DOWN
		$dx1 = 1;
		$dx2 = 2;
		$dy1 = 0;
		$dy2 = 0;
	}
	else if ( $_coords[0] == $_pusher[0] && $_coords[1]+1 == $_pusher[1] )
	{
		// LEFT
		$dx1 = 0;
		$dx2 = 0;
		$dy1 = -1;
		$dy2 = -2;
	}
	else if ( $_coords[0] == $_pusher[0] && $_coords[1]-1 == $_pusher[1] )
	{
		// RIGHT
		$dx1 = 0;
		$dx2 = 0;
		$dy1 = 1;
		$dy2 = 2;
	}
	else
	{
		exit("ERR".__LINE__."(".$_coords[0].":".$_coords[1].")");
	}

	$map	= $_SESSION[S_NAME]['map'];
	$orig	= $level[$_SESSION[S_NAME]['level']];

	// The following is checked in Javascript too, but always check in PHP, so...:
	if ( $map[$_coords[0]][$_coords[1]] != "b" && $map[$_coords[0]][$_coords[1]] != "o" && $orig[$_coords[0]][$_coords[1]] != "t" && $orig[$_coords[0]][$_coords[1]] != "bt" )
	{
		exit("ERR".__LINE__."('".$map[$_coords[0]][$_coords[1]]."')");
	}

	$changes = array();
	$changes[0] = array();
	list($px,$py) = $_pusher;

	$szCoords	= "co";
	$szClass	= "cl";

	if ( $orig[$px+$dx1][$py+$dy1] == "x" || ( $map[$px+$dx1][$py+$dy1] == "b" && ($map[$px+$dx2][$py+$dy2] == "b" || $orig[$px+$dx2][$py+$dy2] == "x") ) )
	{
		// cant stand on wall, cant push box with something behind that box
		exit("ERR".__LINE__);
	}
	else if ( strstr($map[$px+$dx1][$py+$dy1], "b") )
	{
		// pusher pushes a box
		if ( strstr($orig[$px][$py], "t") )		$changes[] = array( $szCoords => $_pusher, $szClass => "target" );
		else									$changes[] = array( $szCoords => $_pusher, $szClass => "empty" );

		$changes[] = array( $szCoords => array($_pusher[0]+$dx1,$_pusher[1]+$dy1), $szClass => "pusher" );

		$changes[] = array( $szCoords => array($_pusher[0]+$dx2,$_pusher[1]+$dy2), $szClass => "box" );
	}
	else
	{
		if ( strstr($orig[$px][$py], "t") )		$changes[] = array( $szCoords => $_pusher, $szClass => "target" );
		else									$changes[] = array( $szCoords => $_pusher, $szClass => "empty" );

		$changes[] = array( $szCoords => array($_pusher[0]+$dx1,$_pusher[1]+$dy1), $szClass => "pusher" );
	}
	$pusher = array($px+$dx1, $py+$dy1);

	// Update map
	$arrClassToChar = array(
		"pusher"	=> "m",
		"box"		=> "b",
		"empty"		=> "o",
		"target"	=> "t",
	);
	for ( $i=1; $i<count($changes); $i++ )
	{
		$map[$changes[$i][$szCoords][0]][$changes[$i][$szCoords][1]] = $arrClassToChar[$changes[$i][$szClass]];
	}

	$_SESSION[S_NAME]['map']	= $map;
	$_SESSION[S_NAME]['pusher']	= $pusher;

	// Count 'bad boxes'
	$boxes_not_in_the_right_place = $boxes[$LEVEL];
	for ( $i=0; $i<count($map); $i++ )
	{
		for ( $j=0; $j<count($map[$i]); $j++ )
		{
			if ( strstr($orig[$i][$j], "t") && strstr($map[$i][$j], "b") )
			{
				$boxes_not_in_the_right_place--;
			}
		}
	}

	// Add some stats to beginning of array
	$changes[0] = array(
		"_p"	=> $pusher,
		"b"		=> $boxes_not_in_the_right_place,
//		"w"		=> $_SESSION[S_NAME]['walks'][$_SESSION[S_NAME]['level']],
//		"p"		=> $_SESSION[S_NAME]['pushes'][$_SESSION[S_NAME]['level']],
	);

	if ( 0 == $boxes_not_in_the_right_place )
	{
		$_SESSION[S_NAME]['level']++;
	}

	echo JSON::encode($changes);

	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>THE BOX -MULTIPLE TARGETS</title>
<script language="javascript" type="text/javascript" src="/_inc/prototype.js"></script>
<script language="javascript" type="text/javascript">
<!--//
if (top.location!=this.location)
	top.location='<?php echo BASEPAGE; ?>';
//-->
</script>
<?php if ( !empty($_SESSION[S_NAME]['play']) ) { ?>
<style type="text/css">
* {
	margin				: 0;
	padding				: 0;
}
body,
table,
input {
	font-family			: verdana;
	font-size			: 12px;
	color				: #000;
	line-height			: 150%;
	cursor				: default;
}
body {
	width				: 100%;
	height				: 100%
}
table#thebox,
table.bcollapse,
table#editor {
	border-collapse		: collapse;
}
table#thebox td,
td.fld {
	border				: solid 1px #fff;
	width				: 30px;
	height				: 30px;
	text-align			: center;
	font-weight			: bold;
	font-size			: 18px;
}
table#editor td {
	border				: solid 1px #000;
	width				: 30px;
	height				: 30px;
	text-align			: center;
}
td.wall1 {
	background-color	: #000;
	color				: #fff;
}
td.wall2 {
	background-color	: #222;
	color				: #fff;
}
td.box {
	background-color	: red;
	cursor				: pointer;
}
td.pusher {
	background-color	: green;
}
td.target {
	background-color	: grey;
	cursor				: pointer;
}
td.empty {
	background-color	: #ddd;
	cursor				: pointer;
}
td.out {
	background-color	: #fff;
}

th.pad,
td.pad {
	padding				: 10px;
}

div#loading {
	position			: absolute;
	top					: 10px;
	left				: 10px;
/*	visibility			: hidden;*/
}
</style>
<script language="javascript" type="text/javascript">
<!--//
var g_bDebug = <?php echo $bDebug ? 'true' : 'false'; ?>;

var myGlobalHandlers =
{
	onCreate: function()
	{
		$('loading').style.visibility = "visible";
	},
	onComplete: function()
	{
		if(Ajax.activeRequestCount == 0)
		{
			$('loading').style.visibility = "hidden";
		}
	}
}
Ajax.Responders.register(myGlobalHandlers);

var move = function( f_coords )
{
	// If 0 boxes left, wait for reload. No moving boxes or walking around when the correct level is not displayed!
	if ( '0' == $('stats_bad_boxes').innerHTML ) return;

	// first we check if this move is valid...
	/**/
	if ( !( f_coords[0] == _pusher[0] && ( f_coords[1]+1 == _pusher[1] || f_coords[1]-1 == _pusher[1] ) ) &&
		 !( f_coords[1] == _pusher[1] && ( f_coords[0]+1 == _pusher[0] || f_coords[0]-1 == _pusher[0] ) ) )
	{
		if ( g_bDebug ) debug('-- Only straight moves allowed!');
		return;
	}

	// Then we check again :)
	fld = $('fld_'+f_coords[0]+'_'+f_coords[1]).className;
	if ( fld != "box" && fld != "empty" && fld != "target" )
	{
		if ( g_bDebug ) debug('-- Cannot walk into walls');
		return;
	}
	/**/

	// Valid move, let's give it a shot! PHP, here I come!!
	params = 'action=move&to[0]=' + f_coords[0] + '&to[1]=' + f_coords[1];
	new Ajax.Request( false,
	{
		'method'		: 'post',
		'parameters'	: params,
		'onComplete'	: function(req)
		{
			if ( g_bDebug ) debug('RECEIVED: ' + req.responseText );
			if ( "[" == req.responseText.substring(0,1) )
			{
				changes = eval('('+req.responseText+')');
				// Update stats
				_pusher = changes[0]._p;
				if ( _pusher && $('stats__pusher') )			$('stats__pusher').innerHTML = _pusher.join(":");

				if ( changes[0].b && $('stats_bad_boxes') )		$('stats_bad_boxes').innerHTML	= changes[0].b;
				if ( changes[0].w && $('stats_walks') )			$('stats_walks').innerHTML		= changes[0].w;
				if ( changes[0].p && $('stats_pushes') )		$('stats_pushes').innerHTML		= changes[0].p;

				// Update map
				for ( i=1; i<changes.length; i++ )
				{
					$('fld_'+changes[i].co[0]+'_'+changes[i].co[1]).className = changes[i].cl;
				}
			}
			if ( '0' == changes[0].b )
			{
				setTimeout( "document.location = '?action=retry';", 100 );
			}
		}
	});
	return;
}

var time = function()
{
	return parseInt( Math.floor( ( (new Date).getTime() ) / 1000 ) );
}

var updateTimer = function()
{
	if ( $('playtime') )
	{
		nowPlaytime = time();
		gespeeld = nowPlaytime-startPlaytime;

		mins = Math.floor(gespeeld/60).toString();
		if ( mins.length == 1 ) mins = "0" + mins;
		secs = (gespeeld - 60*Math.floor(gespeeld/60)).toString();
		if ( secs.length == 1 ) secs = "0" + secs;

		output = mins + ":" + secs + "";

		$('playtime').innerHTML = output;

		_timer = setTimeout("updateTimer()", 100);
	}
	else
	{
		if ( g_bDebug ) debug("TIMER ERROR");
	}
}

var change_name = function( )
{
	new_name = prompt( 'New name?', $('your_name').innerHTML );
	if ( new_name )
	{
		new Ajax.Request( false,
		{
			'method'		: 'post',
			'parameters'	: 'new_name=' + new_name,
			'onComplete'	: function(req)
			{
				$('your_name').innerHTML = req.responseText;
			}
		});
	}
}

window.onload = function()
{
	updateTimer();

	document.body.focus();

	if ( $('stats__pusher') && _pusher ) $('stats__pusher').innerHTML = _pusher.join(":");
}

document.onkeydown = function( e )
{
	if ( !e )	e = window.event;

	if ( e.keyCode )	kc = e.keyCode;
	else if ( e.which )	kc = e.which;
	else				kc = '!fck';

	if ( kc == 37 || kc == 38 || kc == 39 || kc == 40 )
	{
		p = _pusher;
		// UP
		if ( kc == 38 ) move( [p[0]-1, p[1]] );
		// DOWN
		if ( kc == 40 ) move( [p[0]+1, p[1]] );
		// LEFT
		if ( kc == 37 ) move( [p[0], p[1]-1] );
		// RIGHT
		if ( kc == 39 ) move( [p[0], p[1]+1] );
		return false;
	}
}

var debug = function( msg )
{
	if ( $('debug') ) $('debug').innerHTML = msg + "\r\n" + $('debug').innerHTML;
}

//-->
</script>
<?php } ?>
</head>

<?php

if ( empty($_SESSION[S_NAME]['play']) )
{
	?>
	<body onload="$('newgame_name').select();">

	<form method="post" action="">
	<table align="center" border="1">
	<tr>
	<td align="center">Name <input type="text" name="newgame_name" id="newgame_name" value="<?php echo isset($_SESSION[S_NAME]['name']) ? $_SESSION[S_NAME]['name'] : "Anonymous"; ?>" maxlength="12" /></td>
	</tr>
	<tr>
	<td align="center"><input type="submit" value="PLAY" /></td>
	</tr>
	</table>
	</form>

	</body>

	</html>
<?php
	exit;
}

$map = $_SESSION[S_NAME]['map'];

?>
<body>
<script language="javascript" type="text/javascript">
<!--//
var startPlaytime	= <?php echo $_SESSION[S_NAME]['starttime']; ?> - (<?php echo time(); ?>-time());
var _timer;
var _pusher			= <?php echo JSON::encode( $_SESSION[S_NAME]['pusher'] ); ?>;
//-->
</script>
<div id="loading"><img alt="loading" src="images/loading.gif" border="0" width="32" height="32" /></div>
<table border="1" cellpadding=15 cellspacing=0>
	<tr>
		<td class="pad" align="center"><b>LEVEL <?php echo $_SESSION[S_NAME]['level']+1; ?></b></td>
		<td colspan=2></td>
	</tr>
	<tr>
		<td class="pad">
		<table id="thebox">
<?php

$orig = $level[$LEVEL];
for ( $i=0; $i<count($map); $i++ )
{
	$regel = $map[$i];
	echo "			<tr valign=middle>".PHP_EOL;
	for ( $j=0; $j<count($regel); $j++ )
	{
		if ( strstr($orig[$i][$j], "x") )		$cl = 'wall' . rand(1,2);
		else if ( strstr($regel[$j], "b") )		$cl = 'box';
		else if ( strstr($regel[$j], "m") )		$cl = 'pusher';
		else if ( strstr($orig[$i][$j], "t") )	$cl = 'target';
		else if ( strstr($orig[$i][$j], "_") )	$cl = 'out';
		else									$cl = 'empty';

		$txt = strstr($orig[$i][$j],"t") ? "T" : ( $bShowCoords ? $i.','.$j : '' );

		echo '				<td id="fld_'.$i.'_'.$j.'" class="'.$cl.'" onclick="move(['.$i.','.$j.']);">'.$txt.'</td>'.PHP_EOL;
	}
	echo "			</tr>".PHP_EOL;
}

if ( !isset($_SESSION[S_NAME]['walks'][$LEVEL]) )
{
	$_SESSION[S_NAME]['walks'][$LEVEL] = 0;
}
if ( !isset($_SESSION[S_NAME]['pushes'][$LEVEL]) )
{
	$_SESSION[S_NAME]['pushes'][$LEVEL] = 0;
}

echo '		</table>'.PHP_EOL;
echo '		Boxes: <span id="stats_bad_boxes">'.$boxes[$LEVEL].'</span><br/>'.PHP_EOL;
echo '		Walks: <span id="stats_walks">'.$_SESSION[S_NAME]['walks'][$LEVEL].'</span><br/>'.PHP_EOL;
echo '		Pushes: <span id="stats_pushes">'.$_SESSION[S_NAME]['pushes'][$LEVEL].'</span><br/>'.PHP_EOL;
echo $bDebug ? '		PusheR: <span id="stats__pusher">'.implode(",",$_SESSION[S_NAME]['pusher']).'</span><br/>'.PHP_EOL : "";
echo '		</td>'.PHP_EOL;
echo '		<td valign="top" align="left"><a href="?action=newlevel&amp;newlevel='.($_SESSION[S_NAME]['level']-1).'">&lt;&lt;</a> &nbsp; <a href="?action=newlevel&amp;newlevel='.($_SESSION[S_NAME]['level']+1).'">&gt;&gt;</a><br/>'.PHP_EOL;
echo '			<br/>'.PHP_EOL;
echo '			<a href="?action=stop">stop</a><br/>'.PHP_EOL;
echo '			<br/>'.PHP_EOL;
echo '			<a href="?action=retry">Retry</a><br/>'.PHP_EOL;
// echo '			<br/>'.PHP_EOL;
// echo '			<a href="?page=editor">Editor</a><br/>'.PHP_EOL;
echo '		<br/></td>'.PHP_EOL;
echo '		<td valign="top" class="pad">'.PHP_EOL;
echo '		<table class="bcollapse" border="0" cellpadding="0" cellspacing="0">'.PHP_EOL;
echo '			<tr>'.PHP_EOL;
echo '				<td class="fld wall'.rand(1,2).'"></td>'.PHP_EOL;
echo '				<td>&nbsp;&nbsp;wall</td>'.PHP_EOL;
echo '			</tr>'.PHP_EOL;
echo '			<tr>'.PHP_EOL;
echo '				<td class="fld pusher"></td>'.PHP_EOL;
echo '				<td>&nbsp;&nbsp;you</td>'.PHP_EOL;
echo '			</tr>'.PHP_EOL;
echo '			<tr>'.PHP_EOL;
echo '				<td class="fld box"></td>'.PHP_EOL;
echo '				<td>&nbsp;&nbsp;boxes</td>'.PHP_EOL;
echo '			</tr>'.PHP_EOL;
echo '			<tr>'.PHP_EOL;
echo '				<td class="fld target">T</td>'.PHP_EOL;
echo '				<td>&nbsp;&nbsp;target</td>'.PHP_EOL;
echo '			</tr>'.PHP_EOL;
echo '			<tr>'.PHP_EOL;
echo '				<td class="fld empty"></td>'.PHP_EOL;
echo '				<td>&nbsp;&nbsp;inner</td>'.PHP_EOL;
echo '			</tr>'.PHP_EOL;
echo '			<tr>'.PHP_EOL;
echo '				<td class="fld out"></td>'.PHP_EOL;
echo '				<td>&nbsp;&nbsp;outer</td>'.PHP_EOL;
echo '			</tr>'.PHP_EOL;
echo '		</table>'.PHP_EOL;
echo '		</td>'.PHP_EOL;
echo '	</tr>'.PHP_EOL;
echo '</table>'.PHP_EOL;

echo isset( $_SESSION[S_NAME]['gameover'] ) ? "<br/><font style='font-size:14px;'><b>GameOver!</b> You finished the game!</font>" : "";

if ( $bDebug )
{
	echo '<pre id="debug"></pre>'.PHP_EOL;
	echo "<pre>";
	$pr = $_SESSION[S_NAME];
	$pr['map'] = array('..'=>'[..]');
	print_r( $pr );
	echo "</pre>";
}

?>
</body>

</html>
<?php

function reset_game( $f_iLevel = 0 )
{
	global	$level,
			$pusher,
			$boxes;
	
	$f_iLevel = (int)$f_iLevel;
	if ( !isset($level[$f_iLevel], $pusher[$f_iLevel], $boxes[$f_iLevel]) )
	{
		$f_iLevel = 0;
	}

	$_SESSION[S_NAME]['play']				= true;
	$_SESSION[S_NAME]['starttime']			= time();
	$_SESSION[S_NAME]['map']				= $level[$f_iLevel];
	$_SESSION[S_NAME]['pusher']				= $pusher[$f_iLevel];
	$_SESSION[S_NAME]['boxes']				= $boxes[$f_iLevel];
	$_SESSION[S_NAME]['level']				= $f_iLevel;
	$_SESSION[S_NAME]['walks'][$f_iLevel]	= 0;
	$_SESSION[S_NAME]['pushes'][$f_iLevel]	= 0;
	unset($_SESSION[S_NAME]['gameover']);
}

function add_walk()
{
	$_SESSION[S_NAME]['walks'][$_SESSION[S_NAME]['level']]++;
}

function add_push()
{
	$_SESSION[S_NAME]['pushes'][$_SESSION[S_NAME]['level']]++;
	add_walk();
}

?>