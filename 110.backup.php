<?php
// CUMULARI ABSOLUTUS

session_start();

require_once( "connect.php" );
require_once( "json_php".(int)PHP_VERSION.".php" );
define( "S_NAME", "nn_user" );

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );


$_page		= isset($_POST['page'])		? strtolower(trim($_POST['page']))		: ( isset($_GET['page'])	? strtolower(trim($_GET['page']))	: '' );
$_action	= isset($_POST['action'])	? strtolower(trim($_POST['action']))	: ( isset($_GET['action'])	? strtolower(trim($_GET['action']))	: '' );


$COLORS	= array("#dbdbff","#dedeff","#e1e1ff","#e4e4ff","#e7e7ff","#eaeaff","#ededff","#f0f0ff","#f3f3ff","#f6f6ff","#fgfgff","#fcfcff","#ffffff","#fffcfc","#fff9f9","#fff6f6","#fff3f3","#fff0f0","#ffeded","#ffeaea","#ffe7e7","#ffe4e4","#ffe1e1","#ffdede","#ffdbdb");
$COLORS = array (
  0 => '0000ff',
  1 => '1313ff',
  2 => '2727ff',
  3 => '3a3aff',
  4 => '4e4eff',
  5 => '6262ff',
  6 => '7575ff',
  7 => '8989ff',
  8 => '9c9cff',
  9 => 'b0b0ff',
  10 => 'c4c4ff',
  11 => 'd7d7ff',
  12 => 'ebebff',
  13 => 'ffffff',
  14 => 'ffebeb',
  15 => 'ffd7d7',
  16 => 'ffc4c4',
  17 => 'ffb0b0',
  18 => 'ff9c9c',
  19 => 'ff8989',
  20 => 'ff7575',
  21 => 'ff6262',
  22 => 'ff4e4e',
  23 => 'ff3a3a',
  24 => 'ff2727',
  25 => 'ff1313',
);
$COLORS2= array("mediumblue","blue","#ff2200","red");

function Create_Field( $special = "no" )
{
	if ( $special == "special" )
	{
		for ($i=0;$i<25;$i++)
		{
			$_SESSION[S_NAME][$i+1] = 0;
		}
		$_SESSION[S_NAME][12] = 1;
		$_SESSION[S_NAME][14] = -1;
	}
	else
	{
		for ($i=0;$i<25;$i++)
		{
			$_SESSION[S_NAME][$i+1] = $i-12;
		}
	}
}

/* SCORE BEREKENEN */
$score = $_SESSION[S_NAME]['score'] = 0;
for ($i=1;$i<26;$i++)
{
	$score += isset($_SESSION[S_NAME][$i]) ? abs((int)$_SESSION[S_NAME][$i]) : 0;
}
$_SESSION[S_NAME]['score'] = $score;
$_SESSION[S_NAME]['max'] = max($_SESSION[S_NAME]['max'], $score);
$_SESSION[S_NAME]['min'] = min($_SESSION[S_NAME]['min'], $score);
$score=0;
/* SCORE BEREKENEN */

if ( $_page == "midden" )
{
	if ( isset($_GET['click_on']) )
	{
		// Clicked on field $_GET['i']
		if ( isset($_SESSION[S_NAME][(int)$_GET['click_on']]) )
		{
			// Value of field $_GET['i'] = 
			$val = $_SESSION[S_NAME][(int)$_GET['click_on']];

			// Field to change = 
			$voorvakje = (int)$_GET['click_on'] + $val;
			// Make sure to-field is positive
			if ( 0 > $voorvakje )
			{
				$voorvakje += 25 * ceil(abs($voorvakje)/25);
			}
			$voorvakje = $voorvakje % 25;
			if ( !$voorvakje ) $voorvakje = 25;

			// Add val to voorvakje
			$_SESSION[S_NAME][$voorvakje] += $val;
			// One more move used
			$_SESSION[S_NAME]['moves']++;
		}

		Header("Location: ?page=midden");
		exit;
	}

	?>
<html>

<head>
<title>CUMULARI ABSOLUTUS</title>
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
table#ca {
	border-collapse		: collapse;
	border				: solid 1px #000;
}
table#ca td {
	border				: solid 1px #000;
	width				: 70px;
	height				: 70px;
	cursor				: pointer;
	vertical-align		: top;
}
table#ca td div {
	font-size			: 10px;
	text-align			: right;
}
table#ca td div.num_o {
	text-align			: center;
	font-weight			: bold;
	font-size			: 13px;
}
table#ca td div.num_i {
	font-size			: 10px;
	text-align			: left;
}
</style>
<script type="text/javascript">
var move = function( f_fld )
{
	document.location = '?page=midden&click_on=' + f_fld;

}
</script>
</head>

<body style="text-align:center;"><center>
<table id="ca" border="0" cellpadding="2" cellspacing="0">
<tr>
<?php

	for ( $i=1; $i<=25; $i++ )
	{
		$n		= $_SESSION[S_NAME][$i];
		$o		= ($n) ? $n : '';
		$cn		= ($n<-12) ? -12 : $n;
		$cn		= ($cn>12) ? 12 : $cn;
		$bgc	= $COLORS[$cn+12];

		$voorvakje = $i+$n;
		$voorvakje = (25+$voorvakje) % 25;
		if ( !$voorvakje ) $voorvakje = 25;

		$c = max( 0, min( 25, $o+12 ) );
		$bgc = $COLORS[$c];

		$title = "Add ".$n." to field ".$voorvakje."";

		echo '<td id="fld_'.$i.'" style="background:#'.$bgc.';" title="'.$title.'" onclick="move('.$i.')"><div class="num_i">('.$i.')<br/></div><div class="num_o">'.$o.'<br/></div><!--<div>'.$c.'<br/></div>--></td>'.PHP_EOL;
		echo ( 0==$i%5 && $i<25 ) ? "</tr>".PHP_EOL."<tr>".PHP_EOL : "";
	}

?>
</tr>
</table>

</center></body>

</html>
<?php
	exit;

}
if ( $_page == "vakje" && isset($_GET['vakje']) && is_numeric($_GET['vakje']) && $_GET['vakje']>=1 && $_GET['vakje']<=25)
{
	
}
if ( $_page == "niks" )
{
	die("<body style='margin:0px;overflow:auto;'></body>");
}
if ( $_page == "top10" )
{
	echo "<style>";
	echo "BODY,TABLE,INPUT { font-family:Verdana;font-size:11px;color:black;line-height:150%;cursor:default; }";
	echo "</style>";
	echo "<table border=0 cellpadding=0 cellspacing=0 width=50% height=100% align=center><tr valign=middle><td>";
	echo "<table border=0 cellpadding=4 cellspacing=0 align=center width=100%><tr><td style='border-bottom:solid 1px black;' width=1><b>Rank</td><td style='border-bottom:solid 1px black;'><b>Name</td><td style='border-bottom:solid 1px black;'><b>Score</td><td style='border-bottom:solid 1px black;'><b>Min</td><td style='border-bottom:solid 1px black;'><b>Max</td><td style='border-bottom:solid 1px black;'><b>Moves</td><td style='border-bottom:solid 1px black;'><b>PlayTime</td><td style='border-bottom:solid 1px black;'><b>Date&Time</td></tr>";
	$q = mysql_query("SELECT * FROM cumulariabsolutus ORDER BY score,gametime LIMIT 10");
	$r=0;
	while ($l = mysql_fetch_assoc($q))
	{
		$r++;
		$seconds = min(600, $l['gametime']);
		$minutes = floor($seconds/60);
		$seconds = str_pad((string)($seconds%60),2,'0',STR_PAD_LEFT);
		echo "<tr><td align=right>".$r.".</td><td>".$l['name']."</td><td><b>".$l['score']."</td><td>".$l['min']."</td><td>".$l['max']."</td><td>".$l['moves']."</td><td>".$minutes.":".$seconds."</td><td>".strftime("%Y-%m-%d %H:%M:%S",$l['time'])."</td></tr>";
	}
	echo "</table>";
	echo "</td></tr></table>";
	die;
}
if ( $_page == "links" )
{
	echo "<style>";
	echo "BODY,TABLE,INPUT { font-family:Verdana;font-size:11px;color:black;line-height:150%;cursor:default; }";
	echo "</style>";
	?>
	<body style='margin:0px;overflow:auto;'>
	<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr valign=middle><td><center>
	<a href="?action=retry" target=midden>Retry</a><br><br>
	<a href="?page=top10" target=_top>Top 10</a><br><br>
	</td></tr></table>
	<?php
	die;
}
if ( $_page == "rechts" )
{
	$seconds = time()-$_SESSION[S_NAME]['starttime'];
	$minutes = floor($seconds/60);
	$seconds = $seconds-60*$minutes;
	echo "<style>";
	echo "BODY,TABLE,INPUT { font-family:Verdana;font-size:11px;color:black;line-height:150%;cursor:default; }";
	echo "</style>";
	?>
	<META http-equiv=Refresh content=10>
	<body style='margin:0px;overflow:auto;'>
	<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr valign=middle><td><center>
	<table border=0 cellpadding=10 cellspacing=0><tr valign=middle><td><center><b>GAME RULES</b><br></center><br>Your target is a score as <b>low as possible</b>.<br>Your score is the sum of all absolute values of all fields.<br><a href="?page=gamerules">More...</a><br><br><b>Current score: <?php echo $_SESSION[S_NAME]['score']; ?></b><br>Time: <?php echo $minutes; ?> min, <?php echo $seconds; ?> sec<br>Moves: <?php echo (INT)$_SESSION[S_NAME]['moves']; ?><br><br>Your name: <?php echo $_SESSION[S_NAME]['name']; ?><br>Max.score: <?php echo $_SESSION[S_NAME]['max']; ?><br>Min.score: <?php echo $_SESSION[S_NAME]['min']; ?><br><br><br><input type=button value="SAVE THIS SCORE and start over" OnClick="window.open('?action=savegame','midden');"><br><br><a href="?action=retry&special=1" target=midden>Challenge 1</a>: Get a score of 1!</td></tr></table>
	</td></tr></table><pre>
	<?php
print_r($_SESSION['nn_user']);
	die;
}
if ( $_page == "gamerules" )
{
	echo "<style>";
	echo "BODY,TABLE,INPUT { font-family:Verdana;font-size:11px;color:black;line-height:150%;cursor:default; }";
	echo "</style>";
	?>
	<body style='margin:0px;overflow:auto;'>
	<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr valign=middle><td><center>
<b>GUIDE</b><br>
<br>
Your target is a score as low as possible.<br>
Your score is the sum of all absolute values of all fields.<br>
<br>
Fields are named from 1 to 25, 1 being the upper left field and 25 being the lower right.<br>
Every field has a value, by default from -12 to 12.<br>
You can change the value of one field by clicking another.<br>
For example: you click on field 1 (value = -12) -> you substract 12 from field 1-12=-11=14.<br>
Another example: you click on field 6 (value = 4) -> you add 4 to field 6+4=10.<br>
You can see how your clicking affects other fields by hoovering over the fields (Do A to B).<br>
<br>
<br>
<a href="?page=rechts">back</a>
</td></tr></table>
	<?php
	die;
}

if ( $_action == "savegame" && $_SESSION[S_NAME]['score']<156 && (isset($_SESSION[S_NAME]['min_to_save']) && $_SESSION[S_NAME]['score']<=$_SESSION[S_NAME]['min_to_save'] || !isset($_SESSION[S_NAME]['min_to_save'])))
{
	$sql = "INSERT INTO cumulariabsolutus (name,userhost,score,min,max,moves,gametime,time) VALUES ('".$_SESSION[S_NAME]['name']."','".gethostbyaddr($_SERVER['REMOTE_ADDR'])."','".$_SESSION[S_NAME]['score']."','".$_SESSION[S_NAME]['min']."','".$_SESSION[S_NAME]['max']."','".$_SESSION[S_NAME]['moves']."','".(time()-$_SESSION[S_NAME]['starttime'])."','".time()."')";
	mysql_query($sql) or die(mysql_error());

	Header("Location: ?action=retry");
	exit;
}

if ( $_action == "retry" )
{
	$name = $_SESSION[S_NAME]['name'];
	$_SESSION[S_NAME] = array();
	Create_Field();
	if ( isset($_GET['special']) )
	{
		Create_Field("special");
		$_SESSION[S_NAME]['min_to_save'] = 1;
	}
	$_SESSION[S_NAME]['play'] = 1;
	$_SESSION[S_NAME]['name'] = $name;
	$_SESSION[S_NAME]['starttime'] = time();
	$_SESSION[S_NAME]['min'] = 156;
	$_SESSION[S_NAME]['max'] = 156;
	$_SESSION[S_NAME]['score'] = 156;
	$_SESSION[S_NAME]['moves']		= 0;

	Header("Location: ?page=midden");
	exit;
}

if ( empty($_SESSION[S_NAME]['play']) )
{
	if ( isset($_POST['newgame_name']) && Goede_Gebruikersnaam($_POST['newgame_name']) )
	{
		Create_Field();
		$_SESSION[S_NAME]['play']		= true;
		$_SESSION[S_NAME]['starttime']	= time();
		$_SESSION[S_NAME]['name']		= $_POST['newgame_name'];
		$_SESSION[S_NAME]['min']		= 156;
		$_SESSION[S_NAME]['max']		= 156;
		$_SESSION[S_NAME]['score']		= 156;
		$_SESSION[S_NAME]['moves']		= 0;

		Header("Location: ".BASEPAGE);
		exit;
	}

	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>CUMULARI ABSOLUTUS</title>
</head>

<body style='margin:0px;' onload="document.forms[0]['name'].select();">
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
<tr>
<td><center>
<form method=post action="<?php echo BASEPAGE; ?>"><input type=hidden name=check value=1>
Name <input type=text name=newgame_name value="<?php echo isset($_SESSION[S_NAME]['name']) ? $_SESSION[S_NAME]['name'] : "Anonymous"; ?>" maxlength="12"><br>
<br>
<input type=submit value="PLAY"></form>
</td></tr></table></body>
<?php

	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>CUMULARI ABSOLUTUS</title>
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
</style>
</head>

<frameset cols=*,400,* border="0">
	<frame src="?page=links" border="0" scroll="no" noresize="noresize">
	<frame src="?page=midden" name="midden" border="0" scroll="no" noresize="noresize">
	<frame src="?page=rechts" border="0" scroll="no" noresize="noresize">
</frameset>

</html>
