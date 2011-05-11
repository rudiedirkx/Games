<?php
// PICROSS

session_start();

include_once('connect.php');
require_once('json_php'.(int)PHP_VERSION.'.php');

define( 'BASEPAGE',	basename($_SERVER['SCRIPT_NAME']) );
define( 'EOL',		defined('PHP_EOL') ? PHP_EOL : '\n' );


$map = array(
	1 => array(
		array('x','x','x','x','4'),
		array('x','_','_','x','1,1'),
		array('_','_','x','x','2'),
		array('_','x','_','_','1'),
		array('2','1,1','1,1','3')
	),
	array(
		array('x','x','_','x','2,1'),
		array('_','x','x','x','3'),
		array('x','x','x','_','3'),
		array('x','_','x','x','1,2'),
		array('1,2','3','3','2,1')
	),
	array(
		array('_','x','x','x','x','x','_','5'),
		array('_','x','_','x','_','x','_','1,1,1'),
		array('_','x','x','x','x','x','_','5'),
		array('_','_','_','x','_','_','_','1'),
		array('_','x','x','x','x','x','_','5'),
		array('_','x','_','x','_','x','x','1,1,2'),
		array('_','_','_','x','_','_','_','1'),
		array('_','x','x','x','x','x','_','5'),
		array('_','x','_','_','_','x','_','1,1'),
		array('x','x','_','_','_','x','x','2,2'),
		array('1','3,2,3','1,1,1,1','8','1,1,1,1','3,2,3','1,1')
	),
	array(
		array('x','x','x','x','x','x','x','x','x','x','x','x','x','x','_', '14'),
		array('x','_','_','x','x','x','x','x','x','x','x','x','_','_','x', '1,9,1'),
		array('x','_','_','x','x','x','x','x','_','_','x','x','_','_','x', '1,5,2,1'),
		array('x','_','_','x','x','x','x','x','_','_','x','x','_','_','x', '1,5,2,1'),
		array('x','_','_','x','x','x','x','x','_','_','x','x','_','_','x', '1,5,2,1'),
		array('x','_','_','x','x','x','x','x','x','x','x','x','_','_','x', '1,9,1'),
		array('x','_','_','_','_','_','_','_','_','_','_','_','_','_','x', '1,1'),
		array('x','_','x','x','x','x','x','x','x','x','x','x','x','_','x', '1,11,1'),
		array('x','_','x','_','_','_','_','_','_','_','_','_','x','_','x', '1,1,1,1'),
		array('x','_','x','_','x','x','x','x','x','x','x','_','x','_','x', '1,1,7,1,1'),
		array('x','_','x','_','_','_','_','_','_','_','_','_','x','_','x', '1,1,1,1'),
		array('x','_','x','_','x','x','x','x','x','x','x','_','x','_','x', '1,1,7,1,1'),
		array('x','_','x','_','_','_','_','_','_','_','_','_','x','_','x', '1,1,1,1'),
		array('x','_','x','_','_','_','_','_','_','_','_','_','x','_','x', '1,1,1,1'),
		array('x','x','x','x','x','x','x','x','x','x','x','x','x','x','x', '15'),
		array('15','1,1','1,8','6,1,1','6,1,1,1,1','6,1,1,1,1','6,1,1,1,1','6,1,1,1,1','2,1,1,1,1,1','2,1,1,1,1,1','6,1,1,1,1','6,1,1','1,8','1,1','14')
	),
	array(
		array('x','x','x','_','x','x','x','x','x','x','x','x','x','x','x', '3,11'),
		array('x','x','_','_','_','x','x','x','x','x','x','x','x','x','x', '2,10'),
		array('x','_','_','_','x','x','x','x','x','x','x','x','x','x','x', '1,11'),
		array('_','_','_','x','x','x','x','x','x','x','x','x','x','x','x', '12'),
		array('_','_','x','x','x','x','x','x','_','_','_','x','x','x','x', '6,4'),
		array('_','_','_','x','x','x','x','_','_','_','_','_','x','x','x', '4,3'),
		array('x','_','_','_','x','x','_','_','x','x','_','_','x','x','x', '1,2,2,3'),
		array('x','x','_','x','x','_','_','x','x','x','_','_','x','x','x', '2,2,3,3'),
		array('x','x','x','x','_','_','x','x','x','_','_','x','x','_','x', '4,3,2,1'),
		array('x','x','x','x','_','_','x','x','_','_','x','x','_','_','_', '4,2,2'),
		array('x','x','x','x','x','_','_','_','_','x','x','_','_','_','x', '5,2,1'),
		array('x','x','x','x','x','x','_','_','x','x','_','_','_','x','x', '6,2,2'),
		array('x','x','x','x','x','x','x','x','x','_','_','_','x','x','x', '9,3'),
		array('x','x','x','x','x','x','x','x','x','x','_','_','_','x','x', '10,2'),
		array('x','x','x','x','x','x','x','x','x','x','x','_','_','_','x', '11,1'),
		array('3,9','2,8','1,1,7','3,8','1,6,5','7,4','6,2,3','5,3,3','4,3,4','4,2,2,2','4,2,1','5,2','9,1','8,3','9,5'),
	),
);

// $_SESSION['pc_user']['level'] = 4;
$LEVEL = isset($_SESSION['pc_user']['level']) ? (int)$_SESSION['pc_user']['level'] : min(array_keys($map));

if ( isset($_GET['setbg_i'], $_GET['setbg_j']) )
{
	$i = (int)$_GET['setbg_i'];
	$j = (int)$_GET['setbg_j'];

	$arrNextBgs = array( '_' => 'x', 'x' => '_' );

	$bgnu	= isset($_SESSION['pc_user']['map'][$i][$j]) ? $_SESSION['pc_user']['map'][$i][$j] : '_';
	$nxtbg	= isset($arrNextBgs[$bgnu]) ? $arrNextBgs[$bgnu] : $arrNextBgs['_'];
	if ( isset($_GET['dot']) ) {
		$nxtbg = 'd';
	}

	$_SESSION['pc_user']['map'][$i][$j] = $nxtbg;
	echo $nxtbg;

//	Header('Location: '.BASEPAGE);
	exit;
}
else if ( isset($_GET['editmap_i'], $_GET['editmap_j']) )
{
	$i = (int)$_GET['editmap_i'];
	$j = (int)$_GET['editmap_j'];

	$arrNextBgs = Array(
		'_'		=> 'x',
		'x'		=> '_',
	);

	$bgnu	= isset($_SESSION['pc_user']['map'][$i][$j]) ? $_SESSION['pc_user']['map'][$i][$j] : '_';
	$nxtbg	= isset($arrNextBgs[$bgnu]) ? $arrNextBgs[$bgnu] : $arrNextBgs['_'];

	$_SESSION['pc_user']['map'][$i][$j] = $nxtbg;
	echo $nxtbg;

	exit;
}
else if ( isset($_GET['action']) && $_GET['action'] == "retry" )
{
	$name = $_SESSION['pc_user']['name'];
	$l = $LEVEL;
	unset($_SESSION['pc_user']);
	$_SESSION['pc_user']['play']		= 1;
	$_SESSION['pc_user']['starttime']	= time();
	$_SESSION['pc_user']['name']		= $name;
	$_SESSION['pc_user']['level']		= $l;

	Header("Location: ".BASEPAGE);
	exit;
}
else if ( isset($_GET['action']) && $_GET['action'] == "stop" )
{
	$name = $_SESSION['pc_user']['name'];
	unset($_SESSION['pc_user']);
	$_SESSION['pc_user']['name'] = $name;

	Header("Location: ".BASEPAGE);
	exit;
}
else if ( isset($_GET['action']) && $_GET['action'] == "check" )
{
	$fout = false;
	$s = count($map[$LEVEL])-1;
	for ( $i=0; $i<$s; $i++ )
	{
		$s = count($map[$LEVEL][0])-1;
		for ( $j=0; $j<$s; $j++ )
		{
			$userfield = isset($_SESSION['pc_user']['map'][$i][$j]) ? $_SESSION['pc_user']['map'][$i][$j] : '_';
			if ( $map[$LEVEL][$i][$j] != $userfield )
			{
				header("location: ?WRONG");
				exit;
//				echo $i . " : " . $j . "<br/>";
			}
		}
	}

	// GameOver
	$_SESSION['pc_user']['level']++;
	header("location: ?action=retry");
	exit;
}
else if ( isset($_POST['check']) && ( !isset($_SESSION['pc_user']['play']) || $_SESSION['pc_user']['play'] != 1 ) && Goede_Gebruikersnaam($_POST['name']) )
{
	$_SESSION['pc_user']['play']		= 1;
	$_SESSION['pc_user']['starttime']	= time();
	$_SESSION['pc_user']['name']		= $_POST['name'];
	$_SESSION['pc_user']['level']		= min(array_keys($map));

	Header("Location: ".BASEPAGE);
	exit;
}

?>
<html>

<head>
<title>PICROSS</title>
<style type="text/css">
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
	margin				: 0px;
	overflow			: auto;
}
table#picross {
	border-collapse		: collapse;
	padding				: 0px;
}
table#outer {
	margin				: 10px 0 0 10px;
}
td.pc {
	border				: solid 1px #bbb;
	cursor				: pointer;
	width				: 25px;
	height				: 25px;
}
</style>
<script type="text/javascript" src="/ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
var _bg = {
	_ : 'white',
	d : 'pink url(images/dot.gif) 50% 50% no-repeat',
	x : 'black'
};

var _map = <?php echo json::encode(array_fill(0,count($map[$LEVEL])-1,array_fill(0,count($map[$LEVEL][0])-1,'_'))); ?>;

var set_bg = function( objTD, f_i, f_j, d ) {
	new Ajax('119.php?setbg_i=' + f_i + '&setbg_j=' + f_j + ( d ? '&dot=1' : '' ), {
		method		: 'get',
		onComplete	: function(req) {
			bg = _bg[req.responseText] ? _bg[req.responseText] : _bg['_'];
			objTD.style.background = bg;
		}
	});
	return false;
}

var edit_bg = function( objTD, f_i, f_j ) {
	new Ajax('119.php?editmap_i=' + f_i + '&editmap_j=' + f_j, {
		method		: 'get',
		onComplete	: function(req) {
			bg = _bg[req.responseText] ? _bg[req.responseText] : _bg['_'];
			objTD.style.background = bg;
		}
	});
	return false;
}
//-->
</script>
</head>

<body>
<?php

if ( !isset($_SESSION['pc_user']['play']) || $_SESSION['pc_user']['play'] != 1 )
{
	?>
	<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
	<tr>
	<td><center>
	<form method=post action="<?php echo BASEPAGE; ?>"><input type=hidden name=check value=1>
	Name <input type=text name=name value="<?php echo isset($_SESSION['pc_user']['name']) ? $_SESSION['pc_user']['name'] : "Anonymous"; ?>" maxlenght=22><br>
	<br>
	<input type=submit value="PLAY"></form>
	<?php
	exit('</td></tr></table></body>');
}

if ( isset($map[$LEVEL]) ) {
	$map	= $map[$LEVEL];
	$final	= false;
}
else {
	$map	= array(array());
	$final	= true;
}

$szJsFunc = "set_bg";
// $szJsFunc = "edit_bg";

$usermap = isset($_SESSION['pc_user']['map']) ? $_SESSION['pc_user']['map'] : array();
// $usermap = $map;
echo '<table id="outer" border="0" cellspacing="0"><tr><td>' . PHP_EOL;
echo '<table id="picross" border="0" cellpadding="0" cellspacing="0">' . PHP_EOL;
echo '<tr><td align="center" colspan="'.(count($map)-1).'"><b>LEVEL ' . $LEVEL . '</b></td></tr>' . PHP_EOL;
if ( false === $final )
{
	for ($i=0;$i<count($map)-1;$i++)
	{
		echo '<tr valign="top">' . PHP_EOL;
		for ($j=0;$j<count($map[0])-1;$j++)
		{
			$v	= isset($usermap[$i][$j]) ? $usermap[$i][$j] : '_';
			if ( $v == "d" )		$bg = "pink url(images/dot.gif) 50% 50% no-repeat";
			else if ( $v == "x" )	$bg = "#000";
			else if ( $v == "_" )	$bg = "#fff";
			else					$bg = "#f00";
//			$bg = 'x' === $map[$i][$j] ? 'black' : 'white';
			echo '<td class="pc" style="background:'.$bg.';" onclick="'.$szJsFunc.'(this, '.$i.', '.$j.');" oncontextmenu="'.$szJsFunc.'(this,'.$i.','.$j.',1);"></td>' . PHP_EOL;
		}
		echo '<td valign="middle">&nbsp;'.str_replace(",","&nbsp;&nbsp;",$map[$i][$j])."</td>" . PHP_EOL;
		echo "</tr>" . PHP_EOL;
	}
	echo '<tr valign="top">' . PHP_EOL;
	for ( $k=0; $k<count($map[0])-1; $k++ )
	{
		echo '<td align="center">'.str_replace(",","<br/>".PHP_EOL,$map[$i][$k])."</td>" . PHP_EOL;
	}
	echo "</tr>" . PHP_EOL;
}
echo "</table>" . PHP_EOL;

?>
</td>
<td><a href="?action=stop">stop</a><br/><br/><a href="?action=retry">retry</a><br></td>
</tr>
<tr valign="top">
<td align="center"><input type="button"<?php echo TRUE === $final ? ' disabled="disabled"' : ''; ?> value="CHECK" onclick="location='?action=check';"></td>
</tr>
</table>
