<?
// BLACKBOX (JS)

session_start();
include("connect.php");

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

$SIDES = (isset($_SESSION['bb_user']['sides'])) ? $_SESSION['bb_user']['sides'] : 9;
$ATOMS = (isset($_SESSION['bb_user']['atoms'])) ? $_SESSION['bb_user']['atoms'] : 6;
$OPENSOURCE = 1;

function Save_Atom($sides)
{
	$x = rand(0,$sides);
	$y = rand(0,$sides);
	if ($_SESSION['bb_veld'][$x][$y] != "x")
	{
		$_SESSION['bb_veld'][$x][$y] = "x";
	}
	else
	{
		Save_Atom($sides);
	}
}

function Create_Field($sides,$atoms)
{
	for ($i=0;$i<$atoms;$i++)
	{
		Save_Atom($sides-1);
	}
}

if (isset($_GET['action']) && $_GET['action']=="stop")
{
	$name = $_SESSION['bb_user']['name'];
	$_SESSION['bb_veld'] = NULL;
	$_SESSION['bb_user'] = NULL;
	$_SESSION['bb_user']['name'] = $name;

	Header("Location: ".$_SERVER['SCRIPT_NAME']);
	exit;
}

//	/*
if (isset($_GET['action']) && $_GET['action']=="retry")
{
	$name = $_SESSION['bb_user'][name];
	$_SESSION['bb_user'] = NULL;
	$_SESSION['bb_user'][name] = $name;

	Header("Location: ".$_SERVER['SCRIPT_NAME']);
	exit;
}
//	*/

if ($_SESSION['bb_user']['play']!=2)
{
	if ($_POST[check] == 1 && Goede_Gebruikersnaam($_POST[name]))
	{
		$_SESSION['bb_user']['play'] = 2;
		$_SESSION['bb_user']['name'] = $_POST['name'];
		$_SESSION['bb_user']['starttime'] = time();
		Create_Field($SIDES,$ATOMS);

		Header("Location: ".$_SERVER['SCRIPT_NAME']);
		exit;
	}
	?>
	<html>
	<head><title>BLACKBOX</title></head>
<script>
if (top.location!=this.location)
	top.location='<?=$_SERVER[SCRIPT_NAME]?>';
</script>
	<body style='margin:0px;'>
	<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
	<tr>
	<td><center>
	<form method=post action="<?=$_SERVER[SCRIPT_NAME]?>"><input type=hidden name=check value=1>
	Name <input type=text name=name value="Anonymous" maxlenght=22><br>
	<br>
	<input type=submit value="PLAY"></form>
	<?
	die("</td></tr></table></body>");
}

?>
<html>

<head>
<title>BLACKBOX</title>
<script>
if (top.location!=this.location)
	top.location='<?=basename($_SERVER['SCRIPT_NAME'])?>';
</script>
<style>
BODY,TABLE { font-family:Verdana;font-size:11px;color:black;line-height:150%;cursor:default; }
</style>
<script src="./blackbox.js"></script>
<script>
var sides = <?=$SIDES?>;
var atom = new Array();
<?

foreach ($_SESSION['bb_veld'] AS $x => $atom)
{
//	echo "var atom[".($x+1)."] = new Array();\n";
	foreach ($_SESSION['bb_veld'][$x] AS $y => $atom2)
		echo 'var atom['.($x+1).']['.($y+1).'] = "x";'."\n";
}

?>
</script>
</head>
<body style='margin:0px;'>
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
<tr>
<td width=20%><center><a href="?action=stop">Stop</a><br><br><br><br><a href="?action=retry">Retry</a></td>
<td><center>

<table border=1 cellpadding=0 cellspacing=3 style='border:none;'>
<tr height=48 valign=bottom><td></td><? for ($j=1;$j<=$SIDES;$j++) { echo "<td OnClick=\"Track_Beam('naaronder',$j,1,this)\" title='naaronder,$j,1'><center><img src=\"./images/onder.gif\" width=16 height=16></td>"; } ?></tr>
<tr><td align=right OnClick="Track_Beam('naarrechts',1,1,this)" title='naarrechts,1,1'><img src="./images/rechts.gif" width=16 height=16></td>
<?

for ($i=0;$i<$SIDES;$i++)
{
	for ($j=0;$j<$SIDES;$j++)
	{
		$atom = (isset($_SESSION['bb_veld'][$i][$j]) && $OPENSOURCE) ? "&#134;" : "";
		echo "<td height=48 width=48 bgcolor=#aaaaaa OnClick=\"Change_FieldColor(this);\"><center>$atom</td>\n";
	}
	echo ($i<$SIDES-1) ? "<td width=48 OnClick=\"Track_Beam('naarlinks',$SIDES,".($i+1).",this)\" title='naarlinks,$SIDES,".($i+1)."'><img src=\"./images/links.gif\" width=16 height=16></td></tr>\n<tr><td width=48 align=right OnClick=\"Track_Beam('naarrechts',1,".($i+2).",this)\" title='naarrechts,1,".($i+2)."'><img src=\"./images/rechts.gif\" width=16 height=16></td>" : "";
	echo ($i == $SIDES-1) ? "<td width=48 OnClick=\"Track_Beam('naarlinks',$SIDES,".($i+1).",this)\" title='naarlinks,$SIDES,".($i+1)."'><img src=\"./images/links.gif\" width=16 height=16></td>\n":"";
}

?>
</tr>
<tr height=48><td></td><? for ($j=1;$j<=$SIDES;$j++) { echo "<td valign=top OnClick=\"Track_Beam('naarboven',$j,$SIDES,this)\" title='naarboven,$j,$SIDES'><center><img src=\"./images/boven.gif\" width=16 height=16></td>"; } ?></tr>
</table>

</td>
<td width=20%><center>uitleg..?</td>
</tr></table>

</body>
</html>


