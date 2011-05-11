<?php
// STEPPING STONES

session_start();

require_once("connect.php");
define( "S_NAME", "st_2_user" );

require_once( "json_php".(int)PHP_VERSION.".php" );

$bShowCoordinates = false;

// LEVEL 1
$stones[0]	= 9;
$jumper[0]	= array(4,4);
$level[0]	= array(
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","s","o","x","x","x"),
	array("o","o","o","o","s","o","o","o","o"),
	array("o","o","s","s","s","s","s","o","o"),
	array("o","o","o","o","s","o","o","o","o"),
	array("x","x","x","o","s","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x")
);

// LEVEL 2
$stones[1]	= 16;
$jumper[1]	= array(2,4);
$level[1]	= array(
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","s","o","x","x","x"),
	array("o","o","o","s","s","s","o","o","o"),
	array("o","o","s","s","s","s","s","o","o"),
	array("o","s","s","s","s","s","s","s","o"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x")
);

// LEVEL 3
$stones[2]	= 17;
$jumper[2]	= array(1,4);
$level[2]	= array(
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","s","o","x","x","x"),
	array("x","x","x","s","s","s","x","x","x"),
	array("o","o","s","s","s","s","s","o","o"),
	array("o","o","o","o","s","o","o","o","o"),
	array("o","o","o","o","s","o","o","o","o"),
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","o","o","o","x","x","x")
);

// LEVEL 4
$stones[3]	= 24;
$jumper[3]	= array(1,4);
$level[3]	= array(
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","s","o","x","x","x"),
	array("x","x","x","s","s","s","x","x","x"),
	array("o","o","s","s","s","s","s","o","o"),
	array("o","s","s","s","o","s","s","s","o"),
	array("o","o","s","s","s","s","s","o","o"),
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","o","s","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x")
);

// LEVEL 5
$stones[4]	= 44;
$jumper[4]	= array(3,4);
$level[4]	= array(
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","s","s","s","x","x","x"),
	array("s","s","s","s","s","s","s","s","s"),
	array("s","s","s","s","o","s","s","s","s"),
	array("s","s","s","s","s","s","s","s","s"),
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","s","s","s","x","x","x"),
	array("x","x","x","s","s","s","x","x","x")
);

// LEVEL 6
$stones[5]	= 10;
$jumper[5]	= array(3,4);
$level[5]	= array(
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","s","o","x","x","x"),
	array("o","o","o","s","s","s","o","o","o"),
	array("o","o","o","s","s","s","o","o","o"),
	array("o","o","o","s","s","s","o","o","o"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x"),
	array("x","x","x","o","o","o","x","x","x")
);



if ( isset($_POST['check'], $_POST['name']) && empty($_SESSION[S_NAME]['play']) && Goede_Gebruikersnaam($_POST['name']) )
{
	$_SESSION[S_NAME]['play']		= 1;
	$_SESSION[S_NAME]['starttime']	= time();
	$_SESSION[S_NAME]['playtime']	= 0;
	$_SESSION[S_NAME]['saved']		= 0;
	$_SESSION[S_NAME]['name']		= $_POST['name'];
	$_SESSION[S_NAME]['level']		= 0;
	$_SESSION[S_NAME]['map']			= $level[$_SESSION[S_NAME]['level']];
	$_SESSION[S_NAME]['stones']		= $stones[$_SESSION[S_NAME]['level']];
//	$_SESSION[S_NAME]['jumper']		= $jumper[$_SESSION[S_NAME]['level']];

	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}
else if ( isset($_POST['new_name']) )
{
	if ( Goede_Gebruikersnaam($_POST['new_name']) )
	{
		$_SESSION[S_NAME]['name'] = $_POST['new_name'];
	}
	echo $_SESSION[S_NAME]['name'];
	exit;
}
else if ( isset($_GET['action']) && $_GET['action'] == "retry" )
{
	$l = !empty($_SESSION[S_NAME]['gameover']) ? 0 : $_SESSION[S_NAME]['level'];
	$_SESSION[S_NAME]['map']			= $level[$l];
	$_SESSION[S_NAME]['stones']		= $stones[$l];
//	$_SESSION[S_NAME]['jumper']		= $jumper[$l];
	$_SESSION[S_NAME]['gameover']	= 0;
	$_SESSION[S_NAME]['playtime']	= 0;
	$_SESSION[S_NAME]['starttime']	= time();
	$_SESSION[S_NAME]['saved']		= 0;
	$_SESSION[S_NAME]['level']		= $l;

	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}
else if ( isset($_GET['action']) && $_GET['action'] == "stop" )
{
	$name = $_SESSION[S_NAME]['name'];
	$_SESSION[S_NAME] = NULL;
	$_SESSION[S_NAME]['name'] = $name;

	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}
else if ( isset($_GET['action'], $_GET['newlevel']) && $_GET['action'] == "newlevel" )
{
	if ( isset($stones[$_GET['newlevel']]) )
	{
		$_SESSION[S_NAME]['gameover']	= 0;
		$_SESSION[S_NAME]['map']			= $level[$_GET['newlevel']];
		$_SESSION[S_NAME]['stones']		= $stones[$_GET['newlevel']];
//		$_SESSION[S_NAME]['jumper']		= $jumper[$_GET['newlevel']];
		$_SESSION[S_NAME]['starttime']	= time();
		$_SESSION[S_NAME]['level']		= $_GET['newlevel'];
		$_SESSION[S_NAME]['saved']		= 0;
	}

	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}
else if ( isset($_GET['action']) && $_GET['action'] == "save" )
{
	if ( empty($_SESSION[S_NAME]['playtime']) && $_SESSION[S_NAME]['stones'] <= floor($stones[$_SESSION[S_NAME]['level']]/2) )
	{
		$_SESSION[S_NAME]['playtime'] = time()-$_SESSION[S_NAME]['starttime'];
		mysql_query("INSERT INTO steppingstones (name, score, playtime, time, level) VALUES ('".$_SESSION[S_NAME]['name']."','".$_SESSION[S_NAME]['stones']."','".$_SESSION[S_NAME]['playtime']."',NOW(),'".($_SESSION[S_NAME]['level']+1)."');") or die(mysql_error());
	}

	Header("Location: ?action=retry");
	exit;
}

else if ( isset($_POST['to'], $_POST['from']) && is_array($_POST['to']) && is_array($_POST['from']) )
{
	if ($_SESSION[S_NAME]['stones'] == 1) exit("ERR".__LINE__);

	$map = $_SESSION[S_NAME]['map'];
	list($x, $y) = $_POST['from'];

	// Create direction (using [jumper] and [jump-coords])
	if ( $_POST['to'][0] == $x && $_POST['to'][1]+2 == $y )			$dir = "l";
	else if ( $_POST['to'][0] == $x && $_POST['to'][1]-2 == $y )	$dir = "r";
	else if ( $_POST['to'][1] == $y && $_POST['to'][0]+2 == $x )	$dir = "u";
	else if ( $_POST['to'][1] == $y && $_POST['to'][0]-2 == $x )	$dir = "d";
	else																exit("ERR".__LINE__."(".$_POST['jumpto_x'].":".$_POST['jumpto_y'].")");

	if ( $dir == "u" && $map[$x-1][$y] == "s" && $map[$x-2][$y] == "o" )
	{
		$map[$x-2][$y]	= "s";
		$map[$x-1][$y]	= "o";
		$map[$x][$y]	= "o";

//		$_SESSION[S_NAME]['jumper'] = array($x-2, $y);
		$_SESSION[S_NAME]['stones']--;

		$changes = array(
			$_SESSION[S_NAME]['stones'],
			array(
				"co" => array($x-2, $y),
				"cl" => "jumper",
			),
			array(
				"co" => array($x-1, $y),
				"cl" => "no-stone",
			),
			array(
				"co" => array($x-0, $y),
				"cl" => "no-stone",
			),
		);
		echo JSON::encode($changes);
	}
	else if ( $dir == "d" && $map[$x+1][$y] == "s" && $map[$x+2][$y] == "o" )
	{
		$map[$x+2][$y]	= "s";
		$map[$x+1][$y]	= "o";
		$map[$x][$y]	= "o";

//		$_SESSION[S_NAME]['jumper'] = array($x+2, $y);
		$_SESSION[S_NAME]['stones']--;

		$changes = array(
			$_SESSION[S_NAME]['stones'],
			array(
				"co" => array($x+2, $y),
				"cl" => "jumper",
			),
			array(
				"co" => array($x+1, $y),
				"cl" => "no-stone",
			),
			array(
				"co" => array($x+0, $y),
				"cl" => "no-stone",
			),
		);
		echo JSON::encode($changes);
	}
	else if ( $dir == "l" && $map[$x][$y-1] == "s" && $map[$x][$y-2] == "o" )
	{
		$map[$x][$y-2]	= "s";
		$map[$x][$y-1]	= "o";
		$map[$x][$y]	= "o";

//		$_SESSION[S_NAME]['jumper'] = array($x, $y-2);
		$_SESSION[S_NAME]['stones']--;

		$changes = array(
			$_SESSION[S_NAME]['stones'],
			array(
				"co" => array($x, $y-2),
				"cl" => "jumper",
			),
			array(
				"co" => array($x, $y-1),
				"cl" => "no-stone",
			),
			array(
				"co" => array($x, $y-0),
				"cl" => "no-stone",
			),
		);
		echo JSON::encode($changes);
	}
	else if ( $dir == "r" && $map[$x][$y+1] == "s" && $map[$x][$y+2] == "o" )
	{
		$map[$x][$y+2]	= "s";
		$map[$x][$y+1]	= "o";
		$map[$x][$y]	= "o";

//		$_SESSION[S_NAME]['jumper'] = array($x, $y+2);
		$_SESSION[S_NAME]['stones']--;

		$changes = array(
			$_SESSION[S_NAME]['stones'],
			array(
				"co" => array($x, $y+2),
				"cl" => "jumper",
			),
			array(
				"co" => array($x, $y+1),
				"cl" => "no-stone",
			),
			array(
				"co" => array($x, $y+0),
				"cl" => "no-stone",
			),
		);
		echo JSON::encode($changes);
	}
	else
	{
		exit("ERR".__LINE__);
	}

	$_SESSION[S_NAME]['map'] = $map;

	if ( 1 == $_SESSION[S_NAME]['stones'] )
	{
		// level completed!
		$_SESSION[S_NAME]['playtime'] = time()-$_SESSION[S_NAME]['starttime'];
		mysql_query("INSERT INTO steppingstones (name,score,playtime,time,level) VALUES ('".$_SESSION[S_NAME]['name']."','1','".$_SESSION[S_NAME]['playtime']."',NOW(),'".($_SESSION[S_NAME]['level']+1)."')") or die(mysql_error());
	}

//	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}
else if ( isset($_GET['settop10']) )
{
	if (!empty($_SESSION[S_NAME]['showpage']) )	unset($_SESSION[S_NAME]['showpage']);
	else											$_SESSION[S_NAME]['showpage'] = "top10";

	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>STEPPING STONES</title>
<script type="text/javascript" src="general_1_2_1.js"></script>
<script type="text/javascript" src="ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
var _page = '<?php echo $_SERVER['SCRIPT_NAME']; ?>';
if (top.location!=this.location) {
	top.location = _page;
}
//-->
</script>
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
	margin				: 0;
	overflow			: auto;
}
table#top10 {
	border-collapse		: collapse;
	border				: none;
}
table.top10 {
	border-collapse		: collapse;
	font-size			: 9px;
	width				: 80px;
	margin				: 5px;
}
table.top10 td {
	border				: solid 1px #888;
	padding				: 2px;
	text-align			: center;
}
table.top10 td.n {
	font-weight			: bold;
	background-color	: #ddd;
}
table.top10 td.s {
	background-color	: #ccc;
}
table.top10 td.d {
	background-color	: #bbb;
}
td.stone {
	background			: #fff url(images/ss_stone.gif) no-repeat 50% 50%;
	background-color	: #000;
	cursor				: pointer;
}
td.empty {
	background-color	: #fff;
}
td.no-stone {
	background			: #fff url(images/ss_no-stone.gif) no-repeat 50% 50%;
	background-color	: #ccc;
	cursor				: pointer;
}
td.jumper {
	background			: #fff url(images/ss_jumper.gif) no-repeat 50% 50%;
	background-color	: #f00;
	cursor				: pointer;
}
td.flds,
tr.flds td {
	width				: 24px;
	height				: 24px;
	font-size			: 10px;
	text-align			: center;
	color				: green;
}
div#loading {
	visibility			: hidden;
}
</style>
<script language="javascript" type="text/javascript">
<!--//
var _stones = <?php echo isset($_SESSION[S_NAME]['stones']) ? (int)$_SESSION[S_NAME]['stones'] : 0; ?>;
var _jumper = [];

var time = function()
{
	return parseInt( Math.floor( ( (new Date).getTime() ) / 1000 ) );
}

Ajax.setGlobalHandlers({
	onCreate : function() {
		$('loading').style.visibility = "visible";
	},
	onComplete : function() {
		if ( !Ajax.busy ) {
			$('loading').style.visibility = "hidden";
		}
	}
});

var set_field_props = function( f_field, f_class )
{
	if ( !f_field || !$(f_field) )
	{
//		debug("set_field_props reveived invalid f_field: "+f_field);
		return;
	}
	f_field = $(f_field);

	// Append className to the obj
	f_field.className = f_class;

	// Fetch coords from .id

	// Append possible event handlers to the obj
	if ( "no-stone" == f_class )
	{
		// make_jump()
		f_field.onclick = function() { make_jump( coords(this.id) ); }
	}
	else if ( "stone" == f_class )
	{
		// set_jumper()
		f_field.onclick = function() { set_jumper( coords(this.id) ); }
	}
	else if ( "jumper" == f_class )
	{
		// <none>
		f_field.onclick = function() {}
	}
}

var change_name = function( )
{
	new_name = prompt( 'New name?', $('your_name').innerHTML );
	if ( new_name )
	{
		new Ajax( _page, {
			params		: 'new_name=' + new_name,
			onComplete	: function(req) {
				$('your_name').innerHTML = req.responseText;
				$('loading').style.display = "none";
			}
		});
	}
}

var set_jumper = function( f_coords )
{
	new_td_id = 'fld_' + f_coords[0] + '_' + f_coords[1];
	nj = $(new_td_id);
	if ( nj.className == "stone" )
	{
		// set stone (old jumper)
		ojid = 'fld_' + _jumper[0] + '_' + _jumper[1];
		set_field_props( ojid, "stone" );

		// set jumper
		set_field_props( new_td_id, "jumper" );
		_jumper  = f_coords;
		$('stats__jumper').innerHTML = _jumper.join(":");
	}
}

var make_jump = function( f_coords )
{
	var params = 'jump&from[0]=' + _jumper[0] + '&from[1]=' + _jumper[1] + '&to[0]=' + f_coords[0] + '&to[1]=' + f_coords[1];
	new Ajax( _page, {
		params		: params,
		onComplete	: function(req) {
debug("RECEIVED: " + req.responseText);
			if ( req.responseText.substring(0,1) == "[" )
			{
				var ji	= eval('(' + req.responseText + ')');
				if ( ji )
				{
					_stones = ji[0];
					$('num_stones').innerHTML = _stones;
					for ( i=1; i<ji.length; i++ )
					{
						// set field props to new
						fld_id = "fld_" + ji[i].co[0] + "_" + ji[i].co[1];
						set_field_props( fld_id, ji[i].cl );

						// asign new jumper location
						if ( "jumper" == ji[i].cl )
						{
							_jumper = ji[i].co;
							$('stats__jumper').innerHTML = _jumper.join(":");
						}
					}
				}
			}
		}
	});
}

var coords = function( f_coords )
{
// alert( f_coords );
	f_coords = f_coords.replace(/_/g,',').replace(/ /g,'');
	x = f_coords.split(',');
	return [ parseInt(x[1]), parseInt(x[2]) ];
}

var startPlaytime;
var _timer;
var updateTimer = function()
{
	if ( $('playtime') )
	{
		if ( 1 < _stones )
		{
			nowPlaytime = Math.floor(((new Date).getTime())/1000);
			gespeeld = nowPlaytime-startPlaytime;

			mins = Math.floor(gespeeld/60).toString();
			if ( mins.length == 1 ) mins = "0" + mins;
			secs = (gespeeld - 60*Math.floor(gespeeld/60)).toString();
			if ( secs.length == 1 ) secs = "0" + secs;

			$('playtime').innerHTML = mins + ":" + secs + "";

			_timer = setTimeout("updateTimer()", 100);
		}
	}
	else
	{
		debug("TIMER ERROR");
	}
}

window.onload = function()
{
	startPlaytime = <?php echo isset($_SESSION[S_NAME]['starttime']) ? (int)$_SESSION[S_NAME]['starttime'] : time(); ?> - (<?php echo time(); ?>-time());
	updateTimer();
	$('stats__jumper').innerHTML = _jumper.join(":");
}

var debug = function( msg )
{
	if ( $('debug') ) $('debug').innerHTML = msg + "\r\n" + $('debug').innerHTML;
}
//-->
</script>
</head>

<?php

if ( !isset($_SESSION[S_NAME]['play']) || $_SESSION[S_NAME]['play'] != 1 )
{
	?>
<body onload="document.forms[0]['name'].select();">
<form method="post" action="">
<input type="hidden" name="check" value="" />
<table border="1">
<tr>
<td align="center">Name <input type="text" name="name" value="<?php echo isset($_SESSION[S_NAME]['name']) ? $_SESSION[S_NAME]['name'] : "Anonymous"; ?>" maxlength="9" /><br/>
<br/>
<input type="submit" value="PLAY" /></td>
</tr>
</table>
</form>

</body>

</html>
<?php
	exit;
}
else if ( isset($_SESSION[S_NAME]['showpage']) && $_SESSION[S_NAME]['showpage'] == "top10" )
{
	$limit = 15;
	for ( $i=1; $i<=count($stones); $i++ )
	{
		$qr[$i] = mysql_query("SELECT *,UNIX_TIMESTAMP(time) AS utc FROM steppingstones WHERE level='".$i."' ORDER BY score ASC, playtime ASC, time DESC LIMIT ".$limit.";") or die(mysql_error());
	}

	?>
<body>
<a href="?settop10"><b>&lt;&lt; BACK &lt;&lt;<b></a><br/><br/><table cellspacing="0" id="top10" align="center"><?php

	for ( $i=0; $i<=$limit; $i++ )
	{
		echo "<tr>";
		for ( $j=0; $j<=count($stones); $j++ )
		{
			if ( 0 < $i && 0 < $j )
			{	
				echo '<td>';
	
				if ( $name = @mysql_result($qr[$j],$i-1,'name') )
				{
					$score		= mysql_result($qr[$j],$i-1,'score');
					$date		= date('d-m-Y', (int)mysql_result($qr[$j],$i-1,'utc'));
					$playtime	= mysql_result($qr[$j],$i-1,'playtime');

					echo '<table title="Playtime: '.$playtime.' seconds" class="top10" cellspacing="0"><tr><td class="n">'.$name.'</td></tr><tr><td class="s">'.$score.'</td></tr><tr><td class="d">'.$date.'</td></tr></table>';
				}
				else
				{
					echo "&nbsp;";
				}
	
				echo '</td>';
			}
			else if ( 0 == $j && 0 < $i )
			{
				echo '<th style="width:60px;"># '.$i.'</th>';
			}
			else if ( 0 == $j && 0 == $i )
			{
				echo "<td>&nbsp;</td>";
			}
			else if ( 0 < $j && 0 == $i )
			{
				echo '<th style="height:30px;font-size:11px;' . ( isset($_SESSION[S_NAME]['level']) && $_SESSION[S_NAME]['level'] == $j-1 ? 'color:#f00;' : "") . '">LEVEL '.$j.'</th>';
			}
		}
		echo "</tr>";
	}

	?></table><br/><a href="?settop10"><b>&lt;&lt; BACK &lt;&lt;<?php
	exit;
}

$map = $_SESSION[S_NAME]['map'];

$qBestEver = mysql_query("SELECT score FROM steppingstones WHERE level='".($_SESSION[S_NAME]['level']+1)."' ORDER BY score,playtime,time LIMIT 1;") or die(mysql_error());
if ( 0 < mysql_num_rows($qBestEver) )	$szBestEver = mysql_result($qBestEver, 0);
else									$szBestEver = "-";

?>
<body>
<div id="loading"><img alt="loading" src="images/loading.gif" border="0" width="32" height="32" /></div>

<table border="1" cellpadding="15" cellspacing="0" width="700">
	<tr>
		<td align="center"><b>LEVEL <?php echo $_SESSION[S_NAME]['level']+1; ?></b></td>
		<td></td>
		<td align="center"><u>Legend</u></td>
	</tr>
	<tr>
		<td rowspan="2">
		<table style="font-size:11px;" border="0" cellpadding="0" cellspacing="1">
			<tr>
				<td colspan="4"><b>Stones: <span id="num_stones"><?php echo $_SESSION[S_NAME]['stones']; ?></span></b></td>
				<td colspan="5" align="right"><b>Best-Ever: <span id="best_ever"><?php echo $szBestEver; ?></span></b></td>
			</tr>
			<?php

			for ( $i=0; $i<9; $i++ )
			{
				$regel = $map[$i];
				echo '<tr class="flds">'.PHP_EOL;
				for ( $j=0; $j<9; $j++ )
				{
					if ( strstr($regel[$j], "j") )		$class = "jumper";
					else if ( strstr($regel[$j], "s") )	$class = "stone";
					else if ( strstr($regel[$j], "o") )	$class = "no-stone";
					else if ( strstr($regel[$j], "x") )	$class = "empty";

					if ( $regel[$j] == "s" )			$onclick = ' onclick="set_jumper( coords(this.id) );"';
					else if ( $regel[$j] == "o" )		$onclick = ' onclick="make_jump( coords(this.id) );"';
					else								$onclick = '';

					echo '<td' . ( "x" != $regel[$j] ? ' id="fld_'.$i.'_'.$j.'" class="'.$class.'"' : "" ) . ''.$onclick.'>' . ( $bShowCoordinates ? $i.",".$j : "" ) . '</td>' . PHP_EOL;
				}
				echo "</tr>";
			}

			$iSecsPlaytime = isset($_SESSION[S_NAME]['playtime']) ? $_SESSION[S_NAME]['playtime'] : time()-$_SESSION[S_NAME]['starttime'];
	//		$szPlaytime = $iSecsPlaytime . ' s.';
			$szPlaytime = str_pad((string)floor($iSecsPlaytime/60),2,'0',STR_PAD_LEFT) . ':' . str_pad((string)($iSecsPlaytime-60*floor($iSecsPlaytime/60)),2,'0',STR_PAD_LEFT);

			?>
			<tr>
				<td colspan=5>Your name: <b id="your_name"><?php echo $_SESSION[S_NAME]['name']; ?></b></td>
				<td colspan="4" align=right>Playtime: <span id="playtime"><?php echo $szPlaytime; ?></span></td>
			</tr>
		</table>
		</td>
		<td valign=top align=left><a href="?action=newlevel&amp;newlevel=<?php echo $_SESSION[S_NAME]['level']-1; ?>">&lt;&lt;</a> &nbsp; <a href="?action=newlevel&amp;newlevel=<?php echo $_SESSION[S_NAME]['level']+1; ?>">&gt;&gt;</a><br/>
			<br/>
			<a href="?action=stop">stop</a><br/>
			<br/>
			<a href="?action=retry"><?php echo !empty($_SESSION[S_NAME]['gameover']) ? "Again?" : "Retry"; ?></a><br/>
			<br/>
			<a href="?action=save">Save!</a>
		</td>
		<td valign=top>
		<table border="0" cellpadding="0" cellspacing="1">
			<tr valign="middle">
				<td class="flds no-stone">&nbsp;</td>
				<td>&nbsp;&nbsp;empty field (o)</td>
			</tr>
			<tr valign="middle">
				<td class="flds stone">&nbsp;</td>
				<td>&nbsp;&nbsp;stones (s)</td>
			</tr>
			<tr valign="middle">
				<td class="flds jumper">&nbsp;</td>
				<td>&nbsp;&nbsp;jumping stone (js)</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td valign="top">JumpeR: <span id="stats__jumper">x:y</span></td>
		<td align="center">
			<a href="?settop10">Top10</a><br/>
			<br/>
			<a href="#" onclick="change_name();return false;">Change Name</a>
		</td>
	</tr>
	<tr>
		<td colspan=3>Jumping over one stone with another (the red one) will make the one you jumped over disappear. In order to be able to jump, you need two stones next to or above eachother. Navigate the jumping stone (the red one) by clicking on the map. To jump, click on the fourpad (<u>U</u>p, <u>L</u>eft, <u>R</u>ight, <u>D</u>own). Continue jumping as long as possible. Try to beat the Best-Ever!<br/><b>Yes! One is possible!!</b></td>
	</tr>
</table>
<?php if ( !empty($_SESSION[S_NAME]['gameover']) ) echo "<br/><br/><font style='font-size:14px;'><b>GameOver!</b> You finished the game!</font>"; ?>

<pre id="debug" style="font-size:10pt;"></pre>
<?php

echo '<pre>';
print_r( $_SESSION[S_NAME] );
echo '</pre>';

?>
</body>

</html>