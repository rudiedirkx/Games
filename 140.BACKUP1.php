<?php
// THE BOX
// drunkmenworkhere: Q3, 8iR, rZ9a2, Dxg20aj, Hdf7, K4sU, 

session_start();

$bShowCoords	= false;
$bDebug			= true;

$iFuelPerWalk		= 1;
$iExtraFuelPerPush	= 2;

include("connect.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . "/_inc/json.php" );
define( "S_NAME", "bxb_user" );

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );


$_page		= isset($_POST['page'])		? strtolower(trim($_POST['page']))		: ( isset($_GET['page'])	? strtolower(trim($_GET['page']))	: '' );
$_action	= isset($_POST['action'])	? strtolower(trim($_POST['action']))	: ( isset($_GET['action'])	? strtolower(trim($_GET['action']))	: '' );



// $boxes[0]	= 3;
$pusher[0]	= array(5,3);
$level[0]	= array(
	array("_","x","x","x","x","x","x"),
	array("x","x","o","o","t","o","x"),
	array("x","o","bt","o","x","o","x"),
	array("x","o","t","b","o","o","x"),
	array("x","o","o","x","b","o","x"),
	array("x","x","o","o","o","o","x"),
	array("_","x","o","o","o","o","x"),
	array("_","x","x","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(1,4);
$level[]	= array(
	array("x","x","x","x","x","x","x"),
	array("x","o","o","t","o","o","x"),
	array("x","o","x","t","x","o","x"),
	array("x","o","o","o","b","o","x"),
	array("x","t","b","b","o","o","x"),
	array("x","o","o","x","x","x","x"),
	array("x","x","x","x","_","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(1,5);
$level[]	= array(
	array("_","_","_","x","x","x","x"),
	array("x","x","x","x","o","o","x"),
	array("x","o","o","bt","b","o","x"),
	array("x","o","o","o","o","o","x"),
	array("x","x","o","t","x","x","x"),
	array("_","x","b","o","x","_","_"),
	array("_","x","o","t","x","_","_"),
	array("_","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(3,5);
$level[]	= array(
	array("x","x","x","_","x","x","x"),
	array("x","t","x","x","x","t","x"),
	array("x","o","x","o","o","t","x"),
	array("x","o","b","b","o","o","x"),
	array("x","o","o","b","o","o","x"),
	array("x","o","o","x","o","o","x"),
	array("x","o","o","x","x","x","x"),
	array("x","x","x","x","_","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(1,5);
$level[]	= array(
	array("_","_","_","x","x","x","x","_"),
	array("_","_","_","x","o","o","x","x"),
	array("x","x","x","x","o","o","o","x"),
	array("x","t","o","x","b","b","o","x"),
	array("x","o","o","o","o","o","x","x"),
	array("x","t","o","o","b","x","x","_"),
	array("x","x","t","o","o","x","_","_"),
	array("_","x","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(4,2);
$level[]	= array(
	array("x","x","x","x","x","_","_","_"),
	array("x","o","t","t","x","x","x","x"),
	array("x","o","b","o","o","o","o","x"),
	array("x","o","o","x","b","x","o","x"),
	array("x","o","o","o","t","b","o","x"),
	array("x","x","x","x","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(4,4);
$level[]	= array(
	array("_","_","x","x","x","x","x"),
	array("x","x","x","o","o","t","x"),
	array("x","o","b","o","x","o","x"),
	array("x","o","bt","b","o","o","x"),
	array("x","o","t","x","o","o","x"),
	array("x","o","o","o","o","x","x"),
	array("x","o","o","o","x","x","_"),
	array("x","x","x","x","x","_","_")
);

// $boxes[]	= 10;
$pusher[]	= array(4,7);
$level[]	= array(
	array("x","x","x","x","x","x","x","x","x","x","x","x","_","_"),
	array("x","t","t","o","o","x","o","o","o","o","o","x","x","x"),
	array("x","t","t","o","o","x","o","b","o","o","b","o","o","x"),
	array("x","t","t","o","o","x","b","x","x","x","x","o","o","x"),
	array("x","t","t","o","o","o","o","o","o","x","x","o","o","x"),
	array("x","t","t","o","o","x","o","x","o","o","b","o","o","x"),
	array("x","x","x","x","x","x","o","x","x","b","o","b","o","x"),
	array("_","_","x","o","b","o","o","b","o","b","o","b","o","x"),
	array("_","_","x","o","o","o","o","x","o","o","o","o","o","x"),
	array("_","_","x","x","x","x","x","x","x","x","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(1,4);
$level[]	= array(
	array("x","x","x","x","x","x","x","_"),
	array("x","t","o","o","o","t","x","_"),
	array("x","o","o","b","x","o","x","_"),
	array("x","o","x","o","b","t","x","x"),
	array("x","o","o","o","b","x","o","x"),
	array("x","x","x","x","o","o","o","x"),
	array("_","_","_","x","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(3,4);
$level[]	= array(
	array("x","x","x","x","x","_","_"),
	array("x","t","o","t","x","x","x"),
	array("x","t","x","b","b","o","x"),
	array("x","o","o","o","o","o","x"),
	array("x","o","b","x","o","o","x"),
	array("x","x","o","o","o","x","x"),
	array("_","x","x","x","x","x","_")
);

// $boxes[]	= 3;
$pusher[]	= array(5,2);
$level[]	= array(
	array("x","x","x","x","x","_","_"),
	array("x","t","o","o","x","x","x"),
	array("x","o","x","o","o","o","x"),
	array("x","o","t","o","x","o","x"),
	array("x","o","b","bt","b","o","x"),
	array("x","x","o","o","x","x","x"),
	array("_","x","o","o","x","_","_"),
	array("_","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(3,1);
$level[]	= array(
	array("x","x","x","x","x","x","x","x"),
	array("x","t","o","o","o","t","o","x"),
	array("x","o","x","o","x","o","o","x"),
	array("x","o","b","o","o","b","t","x"),
	array("x","x","x","x","x","o","b","x"),
	array("_","_","_","_","x","o","o","x"),
	array("_","_","_","_","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(6,4);
$level[]	= array(
	array("x","x","x","x","_","_","_","_"),
	array("x","o","o","x","_","_","_","_"),
	array("x","o","o","x","x","x","x","x"),
	array("x","o","t","bt","o","o","o","x"),
	array("x","x","b","o","o","o","o","x"),
	array("_","x","o","x","b","x","x","x"),
	array("_","x","t","o","o","x","_","_"),
	array("_","x","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(1,3);
$level[]	= array(
	array("_","x","x","x","x","x","_","_"),
	array("_","x","o","o","o","x","x","x"),
	array("x","x","o","t","o","o","o","x"),
	array("x","t","o","b","t","b","o","x"),
	array("x","x","b","x","o","x","x","x"),
	array("_","x","o","o","o","x","_","_"),
	array("_","x","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(3,4);
$level[]	= array(
	array("_","x","x","x","x","x","_"),
	array("x","x","o","o","o","x","_"),
	array("x","o","b","x","o","x","_"),
	array("x","o","t","o","o","x","x"),
	array("x","o","bt","o","o","o","x"),
	array("x","x","o","x","b","o","x"),
	array("_","x","t","o","o","x","x"),
	array("_","x","x","x","x","x","_")
);

// $boxes[]	= 3;
$pusher[]	= array(4,1);
$level[]	= array(
	array("_","x","x","x","x","_","_","_"),
	array("x","x","o","o","x","x","x","x"),
	array("x","t","t","b","o","o","t","x"),
	array("x","o","x","b","o","b","o","x"),
	array("x","o","o","o","x","o","o","x"),
	array("x","x","x","x","x","o","o","x"),
	array("_","_","_","_","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(1,5);
$level[]	= array(
	array("_","x","x","x","x","x","x","_"),
	array("_","x","o","o","t","o","x","x"),
	array("_","x","o","o","o","b","t","x"),
	array("_","x","x","x","bt","x","o","x"),
	array("x","x","o","o","o","o","o","x"),
	array("x","o","o","b","o","o","x","x"),
	array("x","o","o","o","x","x","x","_"),
	array("x","x","x","x","x","_","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(1,2);
$level[]	= array(
	array("_","x","x","x","x","_","_","_"),
	array("_","x","o","o","x","_","_","_"),
	array("_","x","o","o","x","_","_","_"),
	array("x","x","t","o","x","x","x","x"),
	array("x","o","b","b","t","o","t","x"),
	array("x","o","o","b","o","x","x","x"),
	array("x","x","x","o","o","x","_","_"),
	array("_","_","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(5,3);
$level[]	= array(
	array("x","x","x","x","x","_","_"),
	array("x","t","o","o","x","_","_"),
	array("x","o","x","o","x","x","x"),
	array("x","o","bt","b","o","o","x"),
	array("x","o","o","b","t","o","x"),
	array("x","o","o","o","x","x","x"),
	array("x","x","x","x","x","_","_")
);

// $boxes[]	= 3;
$pusher[]	= array(4,1);
$level[]	= array(
	array("_","_","x","x","x","x","x"),
	array("_","_","x","o","o","o","x"),
	array("_","_","x","o","x","t","x"),
	array("x","x","x","o","o","t","x"),
	array("x","o","o","b","b","o","x"),
	array("x","o","o","t","b","o","x"),
	array("x","x","x","x","x","x","x")
);

// $boxes[]	= 3;
$pusher[]	= array(1,1);
$level[]	= array(
	array("x","x","x","x","x","x","_","_"),
	array("x","o","o","o","o","x","_","_"),
	array("x","o","b","x","o","x","x","x"),
	array("x","o","bt","o","b","o","o","x"),
	array("x","o","o","o","x","x","o","x"),
	array("x","x","t","o","o","t","o","x"),
	array("_","x","x","o","o","o","x","x"),
	array("_","_","x","x","x","x","x","_")
);


$eerste_level = 0;
if ( isset($_SESSION[S_NAME]['level']) )	$LEVEL = $_SESSION[S_NAME]['level'];
else										$LEVEL = $eerste_level;


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

/** FETCH MAPS **/
else if ( "get_maps" == $_action )
{
	if ( isset($_POST['level']) )	$lvl = max(0,$_POST['level']);
	else							$lvl = $_SESSION[S_NAME]['level'];

	$_SESSION[S_NAME]['level'] = (int)$lvl;

	echo JSON::encode( array($_SESSION[S_NAME]['map'], $level[$lvl], $pusher[$lvl], boxes($level[$lvl])) );
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
	if ( isset($pusher[$_GET['newlevel']], $level[$_GET['newlevel']]) )
	{
		reset_game( $_GET['newlevel'] );
	}

	Header("Location: ".BASEPAGE);
	exit;
}

/** MOVE PUSHER **/
else if ( "move" == $_action && isset($_POST['to'][0], $_POST['to'][1]) )
{
//	usleep( 200000 );

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
		$changes[] = array( $szCoords => array($_pusher[0]+$dx2,$_pusher[1]+$dy2), $szClass => "box" );
		add_push();
	}
	else
	{
		add_walk();
	}

	$changes[] = array( $szCoords => array($_pusher[0]+$dx1,$_pusher[1]+$dy1), $szClass => "empty" );

	if ( strstr($orig[$px][$py], "t") )
	{
		$changes[] = array( $szCoords => $_pusher, $szClass => "target" );
	}
	else
	{
		$changes[] = array( $szCoords => $_pusher, $szClass => "empty" );
	}

	// save pusher coords
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
	$boxes_not_in_the_right_place = $_SESSION[S_NAME]['boxes'];
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
		echo "NEXT LEVEL";
		exit;
//		$_SESSION[S_NAME]['level']++;
	}

	echo "OKAY";
//	echo JSON::encode($changes);

	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>THE BOX -MULTIPLE TARGETS</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
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
<script language="javascript">
<!--//
var startPlaytime;
var _timer;
var _pusher;

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

var time = function()
{
	return parseInt( Math.floor( ( (new Date).getTime() ) / 1000 ) );
}

var debug = function( msg )
{
	if ( $('debug') ) $('debug').innerHTML = msg + "\r\n" + $('debug').innerHTML;
}



function TheBox()
{
	this.m_debug			= <?php echo $bDebug ? 'true' : 'false'; ?>;

	this.m_startPlaytime	= 0;
	this.m_timer			= 0;

	// map with moving boxes ( << only boxes come from here )
	this.m_userMap			= [];
	// map with boxes, walls, targets, etc ( << everything but boxes comes from here )
	this.m_origMap			= [];

	this.m_iFuelPerWalk		= <?php echo (int)$iFuelPerWalk; ?>;
	this.m_iExtraFuelPerPush= <?php echo (int)$iExtraFuelPerPush; ?>;

	this.m_iLevel			= <?php echo $_SESSION[S_NAME]['level']; ?>;
	this.m_iBoxes;
	this.m_iBadBoxes		= -1;
	this._pusher			= [];
	this.m_arrWalks			= <?php echo JSON::encode($_SESSION[S_NAME]['walks']); ?>;
	this.m_arrPushes		= <?php echo JSON::encode($_SESSION[S_NAME]['pushes']); ?>;
	this.m_arrFuelUsed		= {};

	this.m_GameOver			= <?php echo !empty($_SESSION[S_NAME]['gameover']) ? 'true' : 'false'; ?>;

	this.m_arrStack			= [];


	/** MOVE **/
	this.Move = function( f_coords )
	{
		// If 0 boxes left, wait for reload. No moving boxes or walking around when the correct level is not displayed!
		if ( '0' == $('stats_bad_boxes').innerHTML ) return;

		if ( f_coords[1] == this._pusher[1] && f_coords[0]+1 == this._pusher[0] )
		{
			// UP
			dir = "up";
			dx1 = -1;
			dx2 = -2;
			dy1 = 0;
			dy2 = 0;
		}
		else if ( f_coords[1] == this._pusher[1] && f_coords[0]-1 == this._pusher[0] )
		{
			// DOWN
			dir = "down";
			dx1 = 1;
			dx2 = 2;
			dy1 = 0;
			dy2 = 0;
		}
		else if ( f_coords[0] == this._pusher[0] && f_coords[1]+1 == this._pusher[1] )
		{
			// LEFT
			dir = "left";
			dx1 = 0;
			dx2 = 0;
			dy1 = -1;
			dy2 = -2;
		}
		else if ( f_coords[0] == this._pusher[0] && f_coords[1]-1 == this._pusher[1] )
		{
			// RIGHT
			dir = "right";
			dx1 = 0;
			dx2 = 0;
			dy1 = 1;
			dy2 = 2;
		}
		else
		{
			if ( this.m_debug ) debug("ERR(clicked on " + f_coords.join(":") + " while pusher is" + _pusher.join(":") + ")");
			return;
		}

		// 'Click-on-field' cannot be wall
//		if ( this.m_userMap[f_coords[0]][f_coords[1]] != "b" && this.m_userMap[f_coords[0]][f_coords[1]] != "o" && this.m_origMap[f_coords[0]][f_coords[1]] != "t" && this.m_origMap[f_coords[0]][f_coords[1]] != "bt" )
		if ( this.m_userMap[f_coords[0]][f_coords[1]].indexOf("x") != -1 )
		{
			if ( this.m_debug ) debug("'Click-on-field' cannot be wall (don't click on wall!!!)");
			return;
		}

		changes = [];

		px = parseInt(this._pusher[0]);
		py = parseInt(this._pusher[1]);

		if ( !this.m_arrWalks[this.m_iLevel] )		this.m_arrWalks[this.m_iLevel] = 0;
		if ( !this.m_arrPushes[this.m_iLevel] )		this.m_arrPushes[this.m_iLevel] = 0;
		if ( !this.m_arrFuelUsed[this.m_iLevel] )	this.m_arrFuelUsed[this.m_iLevel] = 0;


		if ( this.m_userMap[px+dx1][py+dy1].indexOf("b") != -1 && (this.m_userMap[px+dx2][py+dy2].indexOf("o") == -1 && this.m_origMap[px+dx2][py+dy2].indexOf("t") == -1) )
		{
			// Can't push box with box or wall behind it
			if ( this.m_debug ) debug("Can't push box with box or wall behind it");
			return;
		}
		else if ( this.m_userMap[px+dx1][py+dy1].indexOf("b") != -1 )
		{
			// clicked on box
			changes.push( {'co':[px+dx2,py+dy2],'cl':'box'} );
			this.AddPush();
		}
		else
		{
			this.AddWalk();
		}

		changes.push( {'co':[px+dx1,py+dy1],'cl':'empty'} );

		if ( this.m_origMap[px][py].indexOf("t") != -1 )
		{
			changes.push( {'co':[px,py],'cl':'target'} );
		}
		else
		{
			changes.push( {'co':[px,py],'cl':'empty'} );
		}

		this._pusher = [px+dx1, py+dy1];
		if ( this.m_debug ) $('stats__pusher').innerHTML = this._pusher.join(":");

		arrClassToChar = {
			"pusher"	: "m",
			"box"		: "b",
			"empty"		: "o",
			"target"	: "t",
		};

		// Update map (both HTML and JS (m_userMap))
		for ( i=0; i<changes.length; i++ )
		{
			if ( $('fld_'+changes[i].co[0]+'_'+changes[i].co[1]) )
			{
				$('fld_'+changes[i].co[0]+'_'+changes[i].co[1]).className = changes[i].cl;
				this.m_userMap[changes[i].co[0]][changes[i].co[1]] = arrClassToChar[changes[i].cl];
			}
		}

		// Update map to pusher (not in m_userMap)
		$('fld_'+this._pusher[0]+'_'+this._pusher[1]).className = "pusher";

		this.CountBadBoxes();

		$('stats_bad_boxes').innerHTML	= this.m_iBadBoxes;
		$('stats_walks').innerHTML		= this.m_arrWalks[this.m_iLevel];
		$('stats_pushes').innerHTML		= this.m_arrPushes[this.m_iLevel];
		$('stats_fuel_used').innerHTML	= this.CalculateFuelUsed();

		// Send request to PHP for final end verification (who cares!!?)
		this.AddToStack( f_coords );

		if ( 0 == this.m_iBadBoxes )
		{
			// level completed!
			debug( " --- You might have completed this level!!" );

//			alert("You completed this level!!");
//			document.location = '?action=newlevel&newlevel=' + (this.m_iLevel+1);

			// Load new level
			this.m_iLevel = this.m_iLevel + 1;
			this.LoadAndPrintMap( this.m_iLevel );

			return;
		}
		return;
	}



	/** STACK FUNCTIONS **/
	this.AddToStack = function( f_coords )
	{
		this.m_arrStack.push( f_coords )

		// If the stack has just this one coords-set in itself, let's run it!
		// If it has more ('older') coords-sets, it must already be running...
		if ( 1 == this.m_arrStack.length )
		{
			if ( this.m_debug ) debug( "Stack has 1 entry -> PROCESS" );
			this.ProcessStack();
		}

	}

	this.ProcessStack = function()
	{
		coords = this.m_arrStack.shift();

		params = 'action=move&to[0]=' + coords[0] + '&to[1]=' + coords[1];
		if ( this.m_debug ) debug( "Stack-request-params: " + params );
		new Ajax.Request( false,
		{
			'method'		: 'post',
			'parameters'	: params,
			'onComplete'	: function(req)
			{
				debug( "AJAX RETVAL: " + req.responseText );
				if ( "NEXT LEVEL" == req.responseText )
				{
					// Level over...
					
				}
				else if ( req.responseText.indexOf("ERR") != -1 )
				{
					// Some error has occured...
					
				}
				else
				{
					// Just a valid move...
					
				}
			}
		});

	}

	this.LoadAndPrintMap = function( f_level )
	{
// alert( typeof f_level + " === " + f_level );

		if ( 0 > f_level ) return;

		params = 'action=get_maps&level=' + f_level;
// alert( params );
		new Ajax.Request( false,
		{
			'method'		: 'post',
			'parameters'	: params,
			'onComplete'	: function(req)
			{
				retval			= eval( "(" + req.responseText + ")" );
				objTheBox.m_userMap	= retval[0];
				objTheBox.m_origMap	= retval[1];
				objTheBox._pusher	= retval[2];
				objTheBox.m_iBoxes	= retval[3];
				objTheBox.m_iLevel	= f_level;

// alert( objTheBox.m_debug );
				debug( "USERMAP: " + objTheBox.m_userMap.join(" || ") );
				debug( "ORIGMAP: " + objTheBox.m_origMap.join(" || ") );

				$('stats_level').innerHTML = objTheBox.m_iLevel+1;

				$('thebox').innerHTML = '';
				for ( i=0; i<objTheBox.m_userMap.length; i++ )
				{
					NR = $('thebox').insertRow( i );
					for ( j=0; j<objTheBox.m_userMap[i].length; j++ )
					{
						fld = objTheBox.m_userMap[i][j];
						orig = objTheBox.m_origMap[i][j];
						NC = NR.insertCell( j );
						if ( orig.indexOf("x") != -1 )		cl = "wall" + Math.ceil(2*Math.random());
						else if ( fld.indexOf("b") != -1 )	cl = "box";
						else if ( orig.indexOf("t") != -1 )	cl = "target";
						else if ( orig.indexOf("_") != -1 )	cl = "out";
						else								cl = "empty";

						if ( ([i,j]).join(":") == objTheBox._pusher.join(":") ) cl = "pusher";

						txt = ( orig.indexOf("t") != -1 ) ? "T" : "";

						NC.className	= cl;
						NC.innerHTML	= txt;
						NC.id			= "fld_" + i + "_" + j;
						NC.onclick		= function(){ objTheBox.Move([i,j]); }
					}
				}
			}
		});

		myGlobalHandlers.onComplete();

	}



	/** STATISTICS FUNCTIONS **/
	this.AddWalk = function()
	{
		if ( !this.m_arrWalks[this.m_iLevel] ) this.m_arrWalks[this.m_iLevel] = 0;
		this.m_arrWalks[this.m_iLevel] = this.m_arrWalks[this.m_iLevel]+1;

		if ( this.m_debug ) debug( "New Walks: " + this.m_arrWalks[this.m_iLevel] );

		return this.m_arrWalks[this.m_iLevel];

	}

	this.AddPush = function()
	{
		if ( !this.m_arrPushes[this.m_iLevel] ) this.m_arrPushes[this.m_iLevel] = 0;
		this.m_arrPushes[this.m_iLevel] = this.m_arrPushes[this.m_iLevel]+1;
		this.AddWalk();

		if ( this.m_debug ) debug( "New Pushes: " + this.m_arrPushes[this.m_iLevel] );

		return this.m_arrPushes[this.m_iLevel];

	}

	this.CalculateFuelUsed = function( f_level )
	{
		if ( !f_level ) f_level = this.m_iLevel;

		this.m_arrFuelUsed[f_level] = this.m_arrWalks[f_level] * this.m_iFuelPerWalk + this.m_arrPushes[f_level] * this.m_iExtraFuelPerPush;

		if ( this.m_debug ) debug( "New FuelUsed: " + this.m_arrFuelUsed[this.m_iLevel] );

		return this.m_arrFuelUsed[this.m_iLevel];

	}

	this.CountBadBoxes = function()
	{
		// 'Calculate' bad boxes...
		bad_boxes = this.m_iBoxes;

		if ( this.m_debug ) debug("Num boxes: "+bad_boxes);

		for ( x = 0; x < this.m_origMap.length; x++ )
		{
			for ( y = 0; y < this.m_origMap[x].length; y++ )
			{
				if ( this.m_origMap[x][y].indexOf("t") != -1 )
				{
					if ( this.m_debug ) debug( "Fld [" + x + "," + y + "]: " + this.m_userMap[x][y] );

					// This fld is a Target, so if a box is on it: one more less bad box :)
					if ( this.m_userMap[x][y].indexOf("b") != -1 )
					{
						// Yeeeeh
						bad_boxes = bad_boxes - 1;
					}
				}
			}
		}

		if ( this.m_debug ) debug("New bad_boxes: "+bad_boxes);

		this.m_iBadBoxes = bad_boxes;
		return bad_boxes;

	}



	/** PREFERENCES **/
	this.UpdateTimer = function()
	{
		if ( $('playtime') )
		{
			nowPlaytime = time();
			gespeeld = nowPlaytime-this.m_startPlaytime;

			mins = Math.floor(gespeeld/60).toString();
			if ( mins.length == 1 ) mins = "0" + mins;
			secs = (gespeeld - 60*Math.floor(gespeeld/60)).toString();
			if ( secs.length == 1 ) secs = "0" + secs;

			output = mins + ":" + secs + "";

//			debug("TIMER: "+output);
			if ( $('playtime').innerHTML != output ) $('playtime').innerHTML = output;

			this.m_timer = setTimeout("objTheBox.UpdateTimer();", 100);
		}
		else
		{
			debug("TIMER ERROR");
		}
	}

	this.ChangeName = function( )
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

}


var objTheBox;
window.onload = function()
{
	objTheBox = new TheBox();

	objTheBox.m_startPlaytime	= <?php echo isset($_SESSION[S_NAME]['starttime']) ? $_SESSION[S_NAME]['starttime'].' - ('.time().'-time())' : 0; ?>;

	objTheBox.UpdateTimer();

	// fetch map using the secret
	new Ajax.Request( false,
	{
		'method'		: 'post',
		'parameters'	: 'action=get_maps',
		'onComplete'	: function(req)
		{
			retval				= eval( "(" + req.responseText + ")" );
			objTheBox.m_userMap	= retval[0];
			objTheBox.m_origMap	= retval[1];
			objTheBox._pusher	= retval[2];
			objTheBox.m_iBoxes	= retval[3];
			if ( objTheBox.m_debug ) debug( "USERMAP: " + objTheBox.m_userMap.join(" || ") );
			if ( objTheBox.m_debug ) debug( "ORIGMAP: " + objTheBox.m_origMap.join(" || ") );
		}
	});

//	$('stats_bad_boxes').innerHTML = objTheBox.CountBadBoxes();

	document.body.focus();

	myGlobalHandlers.onComplete();
}

document.onkeydown = function( e )
{
	if ( !e )	e = window.event;

	if ( e.keyCode )	kc = e.keyCode;
	else if ( e.which )	kc = e.which;
	else				kc = '!fck';

	if ( kc == 37 || kc == 38 || kc == 39 || kc == 40 )
	{
		p = objTheBox._pusher;
		// UP
		if ( kc == 38 ) objTheBox.Move( [p[0]-1, p[1]] );
		// DOWN
		if ( kc == 40 ) objTheBox.Move( [p[0]+1, p[1]] );
		// LEFT
		if ( kc == 37 ) objTheBox.Move( [p[0], p[1]-1] );
		// RIGHT
		if ( kc == 39 ) objTheBox.Move( [p[0], p[1]+1] );
		return false;
	}
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
		<th class="pad">LEVEL <span id="stats_level"><?php echo $_SESSION[S_NAME]['level']+1; ?></span></th>
		<td colspan=2></td>
	</tr>
	<tr>
		<td class="pad">
		<table id="thebox">
<?php

$map	= $_SESSION[S_NAME]['map'];
$orig	= $level[$LEVEL];
for ( $i=0; $i<count($map); $i++ )
{
	$regel = $map[$i];
	echo "			<tr valign=middle>".PHP_EOL;
	for ( $j=0; $j<count($regel); $j++ )
	{
		if ( strstr($orig[$i][$j], "x") )		$cl = 'wall' . rand(1,2);
		else if ( strstr($regel[$j], "b") )		$cl = 'box';
		else if ( strstr($orig[$i][$j], "t") )	$cl = 'target';
		else if ( strstr($orig[$i][$j], "_") )	$cl = 'out';
		else									$cl = 'empty';

		if ( array($i,$j) == $_SESSION[S_NAME]['pusher'] ) $cl = 'pusher';

		$txt = strstr($orig[$i][$j],"t") ? "T" : ( $bShowCoords ? $i.','.$j : '' );

		echo '				<td id="fld_'.$i.'_'.$j.'" class="'.$cl.'" onclick="objTheBox.Move(['.$i.','.$j.']);">'.$txt.'</td>'.PHP_EOL;
	}
	echo "			</tr>".PHP_EOL;
}
echo '		</table>'.PHP_EOL;

?>

<span onclick="objTheBox.LoadAndPrintMap(1);" style="font-weight:bold;cursor:pointer;float:right;text-decoration:underline;">new map</span>

<?php

if ( !isset($_SESSION[S_NAME]['walks'][$LEVEL]) )
{
	$_SESSION[S_NAME]['walks'][$LEVEL] = 0;
}
if ( !isset($_SESSION[S_NAME]['pushes'][$LEVEL]) )
{
	$_SESSION[S_NAME]['pushes'][$LEVEL] = 0;
}

echo '		<br/>'.PHP_EOL.PHP_EOL;
echo '			Bad Boxes: <span id="stats_bad_boxes">?</span><br/>'.PHP_EOL;
echo '			Walks: <span id="stats_walks">'.$_SESSION[S_NAME]['walks'][$LEVEL].'</span><br/>'.PHP_EOL;
echo '			Pushes: <span id="stats_pushes">'.$_SESSION[S_NAME]['pushes'][$LEVEL].'</span><br/>'.PHP_EOL;
echo $bDebug ? '		PusheR: <span id="stats__pusher">'.implode(",",$_SESSION[S_NAME]['pusher']).'</span><br/>'.PHP_EOL : "";
echo '			Fuel used: <span id="stats_fuel_used">0</span>'.PHP_EOL;
echo '		</td>'.PHP_EOL;
echo '		<td valign="top" align="left" class="pad"><a accesskey="z" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel-1);">&lt;&lt;</a> &nbsp; <a accesskey="x" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel+1);">&gt;&gt;</a><br/>'.PHP_EOL;
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
//	$pr['map'] = array('..'=>'[..]');
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
			$pusher;

	$f_iLevel = (int)$f_iLevel;
	if ( !isset($level[$f_iLevel], $pusher[$f_iLevel]) )
	{
		$f_iLevel = 0;
	}

	$_SESSION[S_NAME]['play']				= true;
	$_SESSION[S_NAME]['starttime']			= time();
	$_SESSION[S_NAME]['map']				= $level[$f_iLevel];
	$_SESSION[S_NAME]['pusher']				= $pusher[$f_iLevel];
	$_SESSION[S_NAME]['boxes']				= boxes($level[$f_iLevel]);
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

function boxes( $f_arrMap )
{
	$iBoxes = 0;
	for ( $i=0; $i<count($f_arrMap); $i++ )
	{
		for ( $j=0; $j<count($f_arrMap[$i]); $j++ )
		{
			if ( strstr($f_arrMap[$i][$j], "b") )
			{
				$iBoxes++;
			}
		}
	}
	return $iBoxes;
}

?>