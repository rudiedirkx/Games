<?php
// THE BOX
// drunkmenworkhere: Q3, 8iR, rZ9a2, Dxg20aj, Hdf7, K4sU, 

session_start();

$bShowCoords	= false;
$bDebug			= false;

$iFuelPerWalk		= 1;
$iExtraFuelPerPush	= 2;

$bStandOnBoxesAllowed = false;

require_once( "connect.php" );
require_once( 'inc.cls.json.php' );
define( "S_NAME", "bx_user" );

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );

$_page		= isset($_POST['page'])		? strtolower(trim($_POST['page']))		: ( isset($_GET['page'])	? strtolower(trim($_GET['page']))	: '' );
$_action	= isset($_POST['action'])	? strtolower(trim($_POST['action']))	: ( isset($_GET['action'])	? strtolower(trim($_GET['action']))	: '' );


$boxes[0]	= 2;
$pusher[0]	= array(2,2);
$level[0]	= array(
	array("x","x","x","x","x"),
	array("x","o","o","o","x"),
	array("x","o","o","o","x"),
	array("x","o","o","o","x"),
	array("x","o","o","b","x"),
	array("x","o","o","o","x"),
	array("x","o","b","o","x"),
	array("x","x","x","t","x")
);

$boxes[1]	= 3;
$pusher[1]	= array(1,2);
$level[1]	= array(
	array("_","x","x","x","x","x","_","_","_"),
	array("_","x","o","o","t","x","_","_","_"),
	array("_","x","o","o","o","x","x","x","x"),
	array("x","x","x","b","o","o","o","o","x"),
	array("x","o","o","o","o","o","o","o","x"),
	array("x","o","b","o","x","b","x","o","x"),
	array("x","o","o","o","x","o","o","o","x"),
	array("x","x","x","x","x","x","x","x","x")
);

$boxes[2]	= 5;
$pusher[2]	= array(1,3);
$level[2]	= array(
	array("x","x","x","x","x","x"),
	array("x","o","o","o","o","x"),
	array("x","o","b","o","b","x"),
	array("x","x","o","b","o","x"),
	array("_","x","b","o","b","x"),
	array("_","x","o","o","o","x"),
	array("_","x","x","x","t","x")
);

$boxes[3]	= 7;
$pusher[3]	= array(1,4);
$level[3]	= array(
	array("_","_","_","_","x","_","_","_","_","_"),
	array("_","_","_","x","o","x","_","_","_","_"),
	array("_","_","x","o","b","o","x","_","_","_"),
	array("_","x","o","o","o","b","o","x","_","_"),
	array("x","t","b","o","o","o","b","o","x","_"),
	array("_","x","o","b","o","o","o","o","o","x"),
	array("_","_","x","o","b","o","b","o","x","_"),
	array("_","_","_","x","o","o","o","x","_","_"),
	array("_","_","_","_","x","o","x","_","_","_"),
	array("_","_","_","_","_","x","_","_","_","_")
);

$boxes[4]	= 4;
$pusher[4]	= array(1,1);
$level[4]	= array(
	array("x","x","x","x","x","x","x"),
	array("x","o","o","o","o","o","x"),
	array("x","o","o","x","o","o","x"),
	array("x","o","o","b","o","o","x"),
	array("x","x","b","o","b","x","x"),
	array("x","o","o","b","o","o","t"),
	array("x","o","o","x","o","o","x"),
	array("x","o","o","o","o","o","x"),
	array("x","x","x","x","x","x","x")
);

$boxes[5]	= 4;
$pusher[5]	= array(3,1);
$level[5]	= array(
	array("x","x","x","x","x","x","x","_"),
	array("x","o","o","o","o","o","x","x"),
	array("x","b","x","x","o","o","o","x"),
	array("x","o","b","o","o","b","o","x"),
	array("x","o","x","o","b","o","x","x"),
	array("x","t","x","o","o","o","x","_"),
	array("x","x","x","x","x","x","x","_")
);

$boxes[6]	= 4;
$pusher[6]	= array(8,6);
$level[6]	= array(
	array("x","x","x","x","x","x","x","x","x"),
	array("x","o","o","o","o","o","o","o","x"),
	array("x","o","o","o","x","x","x","o","x"),
	array("x","o","o","x","o","o","o","o","x"),
	array("x","o","o","b","o","b","x","t","x"),
	array("x","x","o","o","b","o","o","x","x"),
	array("_","x","x","b","o","o","o","x","_"),
	array("_","_","x","o","o","x","o","x","_"),
	array("_","_","x","x","x","x","o","x","_"),
	array("_","_","_","_","_","x","x","x","_")
);

$boxes[7]	= 11;
$pusher[7]	= array(5, 5);
$level[7]	= array(
	array("_","x","x","x","x","x","x","x","x","_","_"),
	array("_","x","o","o","x","o","o","o","x","_","_"),
	array("x","x","o","o","b","o","b","o","x","x","x"),
	array("t","o","b","o","x","o","o","o","b","o","x"),
	array("x","x","x","x","x","b","x","x","b","o","x"),
	array("_","x","o","o","o","o","o","o","o","o","x"),
	array("_","x","o","x","x","x","x","x","b","o","x"),
	array("_","x","o","b","o","o","b","o","b","o","x"),
	array("_","x","o","b","o","o","o","o","o","o","x"),
	array("_","x","x","x","x","x","x","x","o","o","x"),
	array("_","_","_","_","_","_","_","x","x","x","x")
);

$boxes[8]	= 7;
$pusher[8]	= array(3,3);
$level[8]	= array(
	array("_","_","x","x","x","x","x","x","_"),
	array("_","_","x","o","o","o","o","x","_"),
	array("x","x","x","o","o","b","o","x","_"),
	array("t","o","o","o","b","b","b","x","x"),
	array("x","x","x","o","o","b","o","o","x"),
	array("_","_","x","o","x","x","x","o","x"),
	array("_","_","x","o","b","o","b","o","x"),
	array("_","_","x","o","o","o","o","o","x"),
	array("_","_","x","x","x","x","x","x","x")
);

$boxes[9]	= 10;
$pusher[9]	= array(1,5);
$level[9]	= array(
	array("_","_","_","_","x","x","x","x","x","x","_","_"),
	array("_","_","_","_","t","o","o","o","o","x","_","_"),
	array("_","_","_","_","x","x","o","o","o","x","_","_"),
	array("_","_","_","_","_","x","b","b","b","x","_","_"),
	array("x","x","x","x","x","x","o","x","o","x","x","x"),
	array("x","o","o","o","o","o","o","x","o","o","o","x"),
	array("x","o","x","b","b","o","o","b","o","b","o","x"),
	array("x","o","o","o","o","o","o","x","o","o","o","x"),
	array("x","x","x","x","b","x","x","x","x","x","x","x"),
	array("_","_","_","x","o","o","o","o","o","o","o","x"),
	array("_","_","_","x","o","o","x","b","o","b","o","x"),
	array("_","_","_","x","o","o","x","o","o","o","o","x"),
	array("_","_","_","x","x","x","x","x","x","x","x","x")
);

$boxes[10]	= 7;
$pusher[10]	= array(1,2);
$level[10]	= array(
	array("x","x","x","x","x","x","_","_","_","_","_"),
	array("x","o","o","o","o","x","x","x","x","x","_"),
	array("x","b","o","o","b","x","o","o","o","x","_"),
	array("t","o","o","o","o","o","o","o","o","x","_"),
	array("x","o","o","o","b","x","o","o","o","x","_"),
	array("x","o","b","o","o","x","x","o","x","x","x"),
	array("x","x","o","x","x","x","o","o","b","o","x"),
	array("x","o","o","b","o","x","x","o","x","o","x"),
	array("x","o","o","o","o","b","o","o","x","o","x"),
	array("x","x","x","x","x","x","o","o","x","o","x"),
	array("_","_","_","_","_","x","o","o","o","o","x"),
	array("_","_","_","_","_","x","x","x","x","x","x")
);

$boxes[11]	= 12;
$pusher[11]	= array(1,5);
$level[11]	= array(
	array("_","_","_","_","x","x","x","x","x","x","_","_","_"),
	array("_","_","_","_","x","o","o","o","o","x","_","_","_"),
	array("_","_","_","_","x","o","o","b","o","x","_","_","_"),
	array("_","_","_","_","x","x","o","x","x","x","x","x","x"),
	array("x","t","x","_","x","o","o","b","o","o","b","o","x"),
	array("x","o","x","x","x","o","o","o","o","o","x","o","x"),
	array("x","b","o","o","o","x","x","x","x","o","x","o","x"),
	array("x","o","o","o","o","x","o","o","b","o","b","o","x"),
	array("x","x","x","o","x","x","o","o","b","o","x","o","x"),
	array("_","_","x","b","o","o","o","b","o","b","b","o","x"),
	array("_","_","x","o","o","o","b","o","o","o","o","o","x"),
	array("_","_","x","x","x","x","x","x","x","x","o","o","x"),
	array("_","_","_","_","_","_","_","_","_","x","x","x","x")
);

$boxes[12]	= 7;
$pusher[12]	= array(2,4);
$level[12]	= array(
	array("_","_","_","x","x","x","x","x","x","x","x","_"),
	array("_","_","_","x","o","o","o","o","o","o","x","_"),
	array("_","_","_","x","o","x","o","b","o","o","x","_"),
	array("x","t","x","x","o","o","b","b","x","x","x","_"),
	array("x","o","x","o","o","o","o","o","x","_","_","_"),
	array("x","o","x","o","x","x","x","o","x","x","x","x"),
	array("x","o","o","o","o","x","o","o","o","b","o","x"),
	array("x","o","x","o","b","x","o","o","o","o","o","x"),
	array("x","o","o","b","o","x","x","x","x","o","x","x"),
	array("x","x","o","o","o","x","o","b","o","o","o","x"),
	array("_","x","o","o","o","o","o","o","o","o","o","x"),
	array("_","x","x","o","o","x","x","x","x","x","x","x"),
	array("_","_","x","x","x","x","_","_","_","_","_","_")
);

/**
// MULTIPLE BOXES //
$boxes[13]	= 3;
$pusher[13]	= array(5,3);
$level[13]	= array(
	array("_","x","x","x","x","x","x"),
	array("x","x","o","o","o","o","x"),
	array("x","o","b","o","x","o","x"),
	array("x","o","t","b","o","o","x"),
	array("x","o","o","x","b","o","x"),
	array("x","x","o","o","o","o","x"),
	array("_","x","o","o","o","o","x"),
	array("_","x","x","x","x","x","x")
);

// MULTIPLE BOXES //
$boxes[14]	= 3;
$pusher[14]	= array(1,4);
$level[14]	= array(
	array("x","x","x","x","x","x","x"),
	array("x","o","o","t","o","o","x"),
	array("x","o","x","t","x","o","x"),
	array("x","o","o","o","b","o","x"),
	array("x","t","b","b","o","x","x"),
	array("x","o","o","x","x","x","_"),
	array("x","x","x","x","_","_","_")
);

// MULTIPLE BOXES //
$boxes[15]	= 3;
$pusher[15]	= array(1,5);
$level[15]	= array(
	array("_","_","_","x","x","x","x"),
	array("x","x","x","x","o","o","x"),
	array("x","o","o","bt","b","o","x"),
	array("x","o","o","o","o","o","x"),
	array("x","x","o","t","x","x","x"),
	array("_","x","b","o","x","_","_"),
	array("_","x","o","t","x","_","_"),
	array("_","x","x","x","x","_","_")
);
/**/

$eerste_level = 0;
if ( isset($_SESSION[S_NAME]['level']) )	$LEVEL = $_SESSION[S_NAME]['level'];
else										$LEVEL = $eerste_level;


/** NEW GAME **/
if ( isset($_POST['newgame_name']) )
{
	if ( goede_gebruikersnaam($_POST['newgame_name']) )
	{
		reset_game();
	}

	Header("Location: ".BASEPAGE);
	exit;
}

/** FETCH MAPS **/
else if ( "get_map" == $_action )
{
	echo JSON::encode( array($_SESSION[S_NAME]['map'], $level[$_SESSION[S_NAME]['level']]) );
	exit;
}

/** RETRY LEVEL **/
else if ( "retry" == $_action )
{
	$l = !empty($_SESSION[S_NAME]['gameover']) ? 0 : $_SESSION[S_NAME]['level'];

	if ( !isset($level[$l]) ) $l = 0;

	reset_game($l);

	Header("Location: ".BASEPAGE);
	exit;
}

/** STOP **/
else if ( "stop" == $_action )
{
	$name = isset($_SESSION[S_NAME]['name']) ? $_SESSION[S_NAME]['name'] : "Anonymous";
	$_SESSION[S_NAME] = array();
	$_SESSION[S_NAME]['name'] = $name;

	Header("Location: ".BASEPAGE);
	exit;
}

/** NEW LEVEL **/
else if ( "newlevel" == $_action && isset($_GET['newlevel']) )
{
	if ( isset($pusher[$_GET['newlevel']]) )
	{
		reset_game($_GET['newlevel']);
	}

	Header("Location: ".BASEPAGE);
	exit;
}

/** MOVE PUSHER **/
else if ( "move" == $_action && isset($_POST['to'], $_POST['to'][0], $_POST['to'][1]) )
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
		exit("ERR");
	}

	$map	= $_SESSION[S_NAME]['map'];
	$orig	= $level[$_SESSION[S_NAME]['level']];

	// The following is checked in Javascript too, but always check in PHP, so...:
	if ( $map[$_coords[0]][$_coords[1]] != "b" && $map[$_coords[0]][$_coords[1]] != "o" && $orig[$_coords[0]][$_coords[1]] != "t" )
	{
		exit("ERR");
	}

	$changes = array();
	$changes[0] = array();
	list($px,$py) = $_pusher;

	$szCoords	= "co";
	$szClass	= "cl";


	if ( $orig[$px+$dx1][$py+$dy1] == "x" || ( !$bStandOnBoxesAllowed && $orig[$px+$dx1][$py+$dy1] == "t" ) || ( $map[$px+$dx1][$py+$dy1] == "b" && ($map[$px+$dx2][$py+$dy2] == "b" || $orig[$px+$dx2][$py+$dy2] == "x") ) )
	{
		// cant stand on wall, cant push box with something behind it and maybe cant stand on [target]
		exit("ERR");
	}
	else if ( strstr($map[$px+$dx1][$py+$dy1], "b") )
	{
		// pusher pushes a box
		if ( $orig[$px+$dx2][$py+$dy2] == "t" )
		{
			$_SESSION[S_NAME]['boxes']--;
		}
		else
		{
			$changes[] = array( $szCoords => array($px+$dx2,$py+$dy2), $szClass => "box" );
		}
		add_push();
	}
	else
	{
		add_walk();
	}

	$changes[] = array( $szCoords => array($px+$dx1,$py+$dy1), $szClass => "pusher" );

	if ( strstr($orig[$px][$py],"t") )
	{
		$changes[] = array( $szCoords => array($px,$py), $szClass => "target" );
	}
	else
	{
		$changes[] = array( $szCoords => array($px,$py), $szClass => "empty" );
	}
	$pusher = array($px+$dx1, $py+$dy1);

	// Add number of boxes to beginning of array
	$changes[0] = array(
		"_p"	=> $pusher,
		"b"		=> $_SESSION[S_NAME]['boxes'],
		"w"		=> $_SESSION[S_NAME]['walks'][$_SESSION[S_NAME]['level']],
		"p"		=> $_SESSION[S_NAME]['pushes'][$_SESSION[S_NAME]['level']],
	);

	// Update map
	$arrClassToChar = array(
		"pusher"	=> "m",
		"box"		=> "b",
		"empty"		=> "o",
		"target"	=> "t"
	);
	for ( $i=1; $i<count($changes); $i++ )
	{
		$map[$changes[$i][$szCoords][0]][$changes[$i][$szCoords][1]] = $arrClassToChar[$changes[$i][$szClass]];
	}

	// Update map & pusher
	$_SESSION[S_NAME]['map']	= $map;
	$_SESSION[S_NAME]['pusher']	= $pusher;

/**
	if ( 0 == $_SESSION[S_NAME]['boxes'] )
	{
		// level completed!
		$_SESSION[S_NAME]['level']++;
		if ($level[$_SESSION[S_NAME]['level']])
		{
			// Volgende level!
		}
		else
		{
			// Alle levels uitgespeeld!
			$_SESSION[S_NAME]['gameover'] = 1;
		}
	}
/**/

	echo JSON::encode($changes);

	exit;
}

/** EDITOR ACTIONS **/
else if ( "editor" == $_page )
{
	if ( empty($_SESSION['bx_editor']['dot']) )
	{
		$_SESSION['bx_editor']['dot'] = "x";
	}

	if ( empty($_SESSION['bx_editor']['map']) )
	{
		for ( $i=0; $i<13; $i++ )
		{
			for ( $j=0; $j<13; $j++ )
			{
				$_SESSION['bx_editor']['map'][$i][$j] = "_";
			}
		}
	}

	if ( isset($_GET['setdot']) )
	{
		$_SESSION['bx_editor']['dot'] = $_GET['setdot'];
		header("location: ?page=editor");
		exit;
	}
	else if ( $_action == "makefield" )
	{
/*		if ($_SESSION['bx_editor']['dot'] == "o")
		{
			if ($_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] == "m")
				$_SESSION['bx_editor']['man'] = 0;
			if ($_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] == "t")
				$_SESSION['bx_editor']['target'] = 0;
		}*/

		if ($_SESSION['bx_editor']['dot'] == "t")
		{
			if ( $_SESSION['bx_editor']['man'] != array($_GET['i'], $_GET['j']) && $_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] != "x" )
			{
				$_SESSION['bx_editor']['target'] = array($_GET['i'], $_GET['j']);
				$_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] = "o";
			}
//			$_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] = "t";
		}
		else if ($_SESSION['bx_editor']['dot'] == "m")
		{
			if ( $_SESSION['bx_editor']['target'] != array($_GET['i'], $_GET['j']) && $_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] != "x" )
			{
				$_SESSION['bx_editor']['man'] = array($_GET['i'], $_GET['j']);
				$_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] = "o";
			}
//			$_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] = "m";
		}
		else
		{
			if ( $_SESSION['bx_editor']['target'] != array($_GET['i'], $_GET['j']) && $_SESSION['bx_editor']['man'] != array($_GET['i'], $_GET['j']) )
			{
				$_SESSION['bx_editor']['map'][$_GET['i']][$_GET['j']] = $_SESSION['bx_editor']['dot'];
			}
		}
		header("location: ?page=editor");
		exit;
	}
/*	else if ( $_action == "opslaan" )
	{
		$code = serialize($_SESSION['bx_editor']);
		$_SESSION['bx_editor']['code'] = $code;

		Header("Location: ?page=editor&showcode=true");
		exit;
	}*/
}

/** EDITOR CODE **/
else if ( "editor_code" == $_page )
{
	header("content-type: text/plain");
	$save['map']	= $_SESSION['bx_editor']['map'];
	$save['pusher']	= $_SESSION['bx_editor']['man'];
	$target	= $_SESSION['bx_editor']['target'];
	$save['map'][$target[0]][$target[1]] = "t";
	$save['boxes'] = 0;
	for ( $i=0; $i<count($save['map']); $i++ )
	{
		for ( $j=0; $j<count($save['map'][$i]); $j++ )
		{
			if ( $save['map'][$i][$j] == "b" ) $save['boxes']++;
		}
	}
//	print_r( $save );
	echo serialize($save);
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>THE BOX -ONE TARGET</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
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
<script type="text/javascript">
<!--//
var startPlaytime;
var _timer;
var _pusher;

Ajax.setGlobalHandlers({
	onStart : function() {
		$('loading').style.visibility = "visible";
	},
	onComplete: function() {
		if ( !Ajax.busy ) {
			$('loading').style.visibility = "hidden";
		}
	}
});

var debug = function() {}



function TheBox()
{
	this.m_debug				= <?php echo $bDebug ? 'true' : 'false'; ?>;

	this.m_startPlaytime		= 0;
	this.m_timer				= 0;

	// map with atoms (only atoms)
	this.m_userMap				= {};
	// map with hilighted fields (only highlighted)
	this.m_origMap				= {};

	this.m_iFuelPerWalk			= <?php echo (int)$iFuelPerWalk; ?>;
	this.m_iExtraFuelPerPush	= <?php echo (int)$iExtraFuelPerPush; ?>;
	this.bStandOnBoxesAllowed	= <?php echo $bStandOnBoxesAllowed ? 'true' : 'false'; ?>;

	this.m_iLevel				= <?php echo $_SESSION[S_NAME]['level']; ?>;
	this.m_iBoxes				= <?php echo $boxes[$_SESSION[S_NAME]['level']]; ?>;
	this._pusher				= [];
	this.m_arrWalks				= <?php echo JSON::encode($_SESSION[S_NAME]['walks']); ?>;
	this.m_arrPushes			= <?php echo JSON::encode($_SESSION[S_NAME]['pushes']); ?>;
	this.m_arrFuelUsed			= {};

	this.m_GameOver				= <?php echo !empty($_SESSION[S_NAME]['gameover']) ? 'true' : 'false'; ?>;

//	this.m_arrUpdates			= [];


	this.Move = function( f_coords )
	{
		// If 0 boxes left, wait for reload. No moving boxes or walking around when the correct level is not displayed!
		if ( '0' == $('stats_boxes').innerHTML ) return;

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
			if ( this.m_debug ) alert("ERR(" + f_coords[0] + ":" + f_coords[1] + ")");
			return;
		}

		// The following is checked in Javascript too, but always check in PHP, so...:
		if ( this.m_userMap[f_coords[0]][f_coords[1]] != "b" && this.m_userMap[f_coords[0]][f_coords[1]] != "o" && this.m_origMap[f_coords[0]][f_coords[1]] != "t" )
		{
			if ( this.m_debug ) alert("The following is checked in Javascript too, but always check in PHP, so...");
			return;
		}

		changes			= [];

		px = parseInt(this._pusher[0]);
		py = parseInt(this._pusher[1]);

		if ( !this.m_arrWalks[this.m_iLevel] )		this.m_arrWalks[this.m_iLevel] = 0;
		if ( !this.m_arrPushes[this.m_iLevel] )		this.m_arrPushes[this.m_iLevel] = 0;
		if ( !this.m_arrFuelUsed[this.m_iLevel] )	this.m_arrFuelUsed[this.m_iLevel] = 0;


		szCoords	= "co";
		szClass		= "cl";

		if ( this.origMap[px+dx1][py+dy1] == "x" || ( !this.bStandOnBoxesAllowed && this.origMap[px+dx1][py+dy1] == "t" ) || ( this.userMap[px+dx1][py+dy1] == "b" && (this.userMap[px+dx2][py+dy2] == "b" || this.origMap[px+dx2][py+dy2] == "x") ) )
		{
			// cant stand on wall, cant push box with something behind it and maybe cant stand on [target]
			if ( this.m_debug ) alert("cant stand on wall, cant push box with something behind it and maybe cant stand on [target]");
			return;
		}
		else if ( this.userMap[px+dx1][py+dy1] == "b" )
		{
			// pusher pushes a box
			if ( this.origMap[px+dx2][py+dy2] == "t" )
			{
				this.m_iBoxes = this.m_iBoxes-1;
			}
			else
			{
				changes.push( {'co':[px+dx2,py+dy2],'cl':'box'} );
			}
			this.AddPush();
		}
		else
		{
			this.AddWalk();
		}

		changes.push( {'co':[px+dx1,py+dy1],'cl':'pusher'} );

		if ( this.origMap[px][py] == "t" )
		{
			changes.push( {'co':[px,py],'cl':'target'} );
		}
		else
		{
			changes.push( {'co':[px,py],'cl':'empty'} );
		}
		this._pusher = [px+dx1, py+dy1];

		$('stats_boxes').innerHTML		= this.m_iBoxes;
		$('stats_walks').innerHTML		= this.m_arrWalks[this.m_iLevel];
		$('stats_pushes').innerHTML		= this.m_arrPushes[this.m_iLevel];
		$('stats_fuel_used').innerHTML	= this.CalculateFuelUsed();


		arrClassToChar = {
			"pusher"	: "m",
			"box"		: "b",
			"empty"		: "o",
			"target"	: "t",
		};

		// Send request to PHP for final end verification (who cares!!?)
		params = 'action=move&to[0]=' + f_coords[0] + '&to[1]=' + f_coords[1];
		new Ajax.Request( false,
		{
			'method'		: 'post',
			'parameters'	: params,
			'onComplete'	: function(req)
			{
				if ( this.m_debug ) debug( "AJAX RETVAL: " + req.responseText );
				if ( req.responseText == "ERR" )
				{
					if ( this.m_debug ) alert("ERROR ENCOUNTERED!!!! HELP HELP!!!!");
					return;
				}
			}
		});

		// Update map (both HTML and JS (userMap))
		for ( i=0; i<changes.length; i++ )
		{
			this.userMap[changes[i].co[0]][changes[i].co[1]] = arrClassToChar[changes[i].cl];
			$('fld_'+changes[i].co[0]+'_'+changes[i].co[1]).className = changes[i].cl;
		}

		if ( 0 == this.m_iBoxes )
		{
			// level completed!
			alert("You completed this level!!");
			document.location = '?action=newlevel&newlevel=' + (this.m_iLevel+1);
			return;
		}
		return;
	}


	this.AddWalk = function()
	{
		if ( !this.m_arrWalks[this.m_iLevel] ) this.m_arrWalks[this.m_iLevel] = 0;
		this.m_arrWalks[this.m_iLevel] = this.m_arrWalks[this.m_iLevel]+1;

//		this.CalculateFuelUsed();

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


	this.UpdateTimer = function() {}

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


var TheBox;
window.onload = function()
{
	TheBox = new TheBox();

	TheBox._pusher			= [<?php echo isset($_SESSION[S_NAME]['pusher']) ? implode(",", $_SESSION[S_NAME]['pusher']) : '0,0'; ?>];
	TheBox.m_startPlaytime	= <?php echo isset($_SESSION[S_NAME]['starttime']) ? $_SESSION[S_NAME]['starttime'].' - ('.time().'-$time())' : 0; ?>;

	// fetch map using the secret
	new Ajax('?', {
		data : 'action=get_map',
		onComplete : function(t) {
			try {
			var rv = eval( "(" + t + ")" );
			} catch(e) { console.debug(e); }

			TheBox.userMap	= rv[0];
			TheBox.origMap	= rv[1];
		}
	});

	document.body.focus();
}

document.onkeydown = function( e )
{
	if ( !e )	e = window.event;

	if ( e.keyCode )	kc = e.keyCode;
	else if ( e.which )	kc = e.which;
	else				kc = '!fck';

	if ( kc == 37 || kc == 38 || kc == 39 || kc == 40 )
	{
		p = TheBox._pusher;
		// UP
		if ( kc == 38 ) TheBox.Move( [p[0]-1, p[1]] );
		// DOWN
		if ( kc == 40 ) TheBox.Move( [p[0]+1, p[1]] );
		// LEFT
		if ( kc == 37 ) TheBox.Move( [p[0], p[1]-1] );
		// RIGHT
		if ( kc == 39 ) TheBox.Move( [p[0], p[1]+1] );
		return false;
	}
}
//-->
</script>
<?php } ?>
<?php if ( $_page == "editor") { ?>
<script type="text/javascript">
<!--//
var F = function( f_coords )
{
	document.location = '?page=editor&action=makefield&i=' + f_coords[0] + '&j=' + f_coords[1];
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

<form method="post" action="<?php echo basename($_SERVER['SCRIPT_NAME']); ?>">
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


/** GUI EDITOR **/
else if ( $_page == "editor")
{
	if ( !isset($_SESSION['bx_editor']['target']) )	$_SESSION['bx_editor']['target'] = array();
	if ( !isset($_SESSION['bx_editor']['man']) )	$_SESSION['bx_editor']['man'] = array();

	echo '<body>'.PHP_EOL;
	echo '<table border="0" cellpadding="15" cellspacing="0" style="margin:10px;">'.PHP_EOL;
	echo '<tr>'.PHP_EOL;
	echo '<th class="pad">EDITOR</th>'.PHP_EOL;
	echo '<td></td>'.PHP_EOL;
	echo '</tr>'.PHP_EOL;
	echo '<tr>'.PHP_EOL;
	echo '<td class="pad">'.PHP_EOL;
	echo '<table border="1" cellpadding="0" cellspacing="0" id="editor">'.PHP_EOL;
	for ( $i=0; $i<13; $i++ )
	{
		$regel = $_SESSION['bx_editor']['map'][$i];

		echo '<tr valign="middle">'.PHP_EOL;
		for ( $j=0; $j<13; $j++ )
		{
			if ( strstr($regel[$j], "x") )	$cl = 'wall' . rand(1,2);
			else if ( strstr($regel[$j], "b") )	$cl = 'box';
			else if ( strstr($regel[$j], "_") )	$cl = 'out';
			else								$cl = 'empty';

			$txt = "";
			if ( $_SESSION['bx_editor']['target'] == array($i,$j) )		{$cl = "target"; $txt = "T";}
			else if ( $_SESSION['bx_editor']['man'] == array($i,$j) )	$cl = "pusher";

			echo '<td id="fld_'.$i.'_'.$j.'" class="'.$cl.'" onclick="F(['.$i.','.$j.']);">'.$txt.'</td>'.PHP_EOL;
		}
		echo '</tr>'.PHP_EOL;
	}
	echo '</table>'.PHP_EOL;
	echo '</td>'.PHP_EOL;
	echo '		<td valign="top" class="pad">'.PHP_EOL;
	echo '		<table class="bcollapse" border="0" cellpadding="0" cellspacing="0">'.PHP_EOL;
	echo '			<tr>'.PHP_EOL;
	echo '				<td class="fld wall'.rand(1,2).'" onclick="document.location=\'?page=editor&amp;setdot=x\';"></td>'.PHP_EOL;
	echo '				<td>&nbsp;&nbsp;wall</td>'.PHP_EOL;
	echo '			</tr>'.PHP_EOL;
	echo '			<tr>'.PHP_EOL;
	echo '				<td class="fld pusher" onclick="document.location=\'?page=editor&amp;setdot=m\';"></td>'.PHP_EOL;
	echo '				<td>&nbsp;&nbsp;you</td>'.PHP_EOL;
	echo '			</tr>'.PHP_EOL;
	echo '			<tr>'.PHP_EOL;
	echo '				<td class="fld box" onclick="document.location=\'?page=editor&amp;setdot=b\';"></td>'.PHP_EOL;
	echo '				<td>&nbsp;&nbsp;boxes</td>'.PHP_EOL;
	echo '			</tr>'.PHP_EOL;
	echo '			<tr>'.PHP_EOL;
	echo '				<td class="fld target" onclick="document.location=\'?page=editor&amp;setdot=t\';">T</td>'.PHP_EOL;
	echo '				<td>&nbsp;&nbsp;target</td>'.PHP_EOL;
	echo '			</tr>'.PHP_EOL;
	echo '			<tr>'.PHP_EOL;
	echo '				<td class="fld empty" onclick="document.location=\'?page=editor&amp;setdot=o\';"></td>'.PHP_EOL;
	echo '				<td>&nbsp;&nbsp;inner</td>'.PHP_EOL;
	echo '			</tr>'.PHP_EOL;
	echo '			<tr>'.PHP_EOL;
	echo '				<td class="fld out" onclick="document.location=\'?page=editor&amp;setdot=_\';"></td>'.PHP_EOL;
	echo '				<td>&nbsp;&nbsp;outer</td>'.PHP_EOL;
	echo '			</tr>'.PHP_EOL;
	echo '		</table>'.PHP_EOL;
	echo '		</td>'.PHP_EOL;
	echo '</tr>';
	echo '<tr>'.PHP_EOL;
	echo '<th colspan="2"><a style="display:block;width:100%;padding:10px;" href="?page=editor&amp;showcode=true">Opslaan</a></th>'.PHP_EOL;
	echo '</tr>'.PHP_EOL;
	echo isset($_SESSION['bx_editor']['code'], $_GET['showcode']) ? '<tr><td colspan=2><iframe width="700" height="55" src="?page=editor_code"></iframe></td></tr>' : "";
	echo "</table>";
	exit;
}

$map	= $_SESSION[S_NAME]['map'];
$orig	= $level[$_SESSION[S_NAME]['level']];

?>
<body>
<div id="loading"><img alt="loading" src="images/loading.gif" border="0" width="32" height="32" /></div>
<table border="1" cellpadding="15" cellspacing="0">
	<tr>
		<td class="pad" align="center"><b>LEVEL <?php echo $_SESSION[S_NAME]['level']+1; ?></b></td>
		<td colspan=2></td>
	</tr>
	<tr>
		<td class="pad">
		<table id="thebox">
<?php

for ($i=0;$i<count($map);$i++)
{
	$regel = $map[$i];
	echo "			<tr>".PHP_EOL;
	for ( $j=0; $j<count($regel); $j++ )
	{
		if ( strstr($orig[$i][$j], "x") )	$cl = 'wall' . rand(1,2);
		else if ( strstr($regel[$j], "b") )	$cl = 'box';
		else if ( strstr($orig[$i][$j], "t") )	$cl = 'target';
		else if ( strstr($regel[$j], "_") )	$cl = 'out';
		else								$cl = 'empty';

		if ( array($i,$j) == $pusher[$LEVEL] )	$cl = 'pusher';

		$txt = strstr($orig[$i][$j],"t") ? "T" : ( $bShowCoords ? $i.','.$j : '' );

		echo '				<td id="fld_'.$i.'_'.$j.'" class="'.$cl.'" onclick="TheBox.Move(['.$i.','.$j.']);">'.$txt.'</td>'.PHP_EOL;
	}
	echo "			</tr>".PHP_EOL;
}

if ( !isset($_SESSION[S_NAME]['walks'][$_SESSION[S_NAME]['level']]) )
{
	$_SESSION[S_NAME]['walks'][$_SESSION[S_NAME]['level']] = 0;
}
if ( !isset($_SESSION[S_NAME]['pushes'][$_SESSION[S_NAME]['level']]) )
{
	$_SESSION[S_NAME]['pushes'][$_SESSION[S_NAME]['level']] = 0;
}



echo '		</table>'.PHP_EOL.PHP_EOL;
echo '		<br/>'.PHP_EOL.PHP_EOL;
echo '			Boxes: <span id="stats_boxes">'.(float)$_SESSION[S_NAME]['boxes'].'</span><br/>'.PHP_EOL;
echo '			Walked: <span id="stats_walks">'.(float)$_SESSION[S_NAME]['walks'][$_SESSION[S_NAME]['level']].'</span><br/>'.PHP_EOL;
echo '			Pushed: <span id="stats_pushes">'.(float)$_SESSION[S_NAME]['pushes'][$_SESSION[S_NAME]['level']].'</span><br/>'.PHP_EOL;
echo $bDebug ? '		PusheR: <span id="stats__pusher">'.implode(",",$_SESSION[S_NAME]['pusher']).'</span><br/>'.PHP_EOL : "";
echo '			Fuel used: <span id="stats_fuel_used">0</span>'.PHP_EOL;
echo '		</td>'.PHP_EOL;
echo '		<td class="pad" valign="top" align="left"><a href="?action=newlevel&amp;newlevel='.($_SESSION[S_NAME]['level']-1).'">&lt;&lt;</a> &nbsp; <a href="?action=newlevel&amp;newlevel='.($_SESSION[S_NAME]['level']+1).'">&gt;&gt;</a><br/>'.PHP_EOL;
echo '		<br/>'.PHP_EOL;
echo '		<a href="?action=stop">stop</a><br/>'.PHP_EOL;
echo '		<br/>'.PHP_EOL;
echo '		<a href="?action=retry">' . ( ( isset($_SESSION[S_NAME]['gameover']) && $_SESSION[S_NAME]['gameover'] ) ? "Again?" : "Retry" ) . '</a><br/>'.PHP_EOL;
echo '		<br/>'.PHP_EOL;
echo '		<a href="?page=editor">Editor</a><br/>'.PHP_EOL;
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
echo '		</table>'.PHP_EOL;
echo '		</td>'.PHP_EOL;
echo '	</tr>'.PHP_EOL;
echo '</table>'.PHP_EOL;

echo ( isset($_SESSION[S_NAME]['gameover']) && $_SESSION[S_NAME]['gameover'] ) ? "<br/><font style='font-size:14px;'><b>GameOver!</b> You finished the game!</font>" : "";

if ( $bDebug )
{
	echo '<pre id="debug"></pre>';
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