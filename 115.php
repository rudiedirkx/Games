<?
// MASTERMIND

session_start();

include("connect.php");

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

$OPENSOURCE = 0;
$COLORS = Array("black","white","green","red","yellow","blue");

function Go_Random($max)
{
	global $gekozenkleuren,$COLORS;
	$id = rand(0,$max-1);
	if (isset($gekozenkleuren) && is_array($gekozenkleuren) && in_array($COLORS[$id],$gekozenkleuren))
		Go_Random($max);
	else
		$gekozenkleuren[] = $COLORS[$id];
	return $id;
}
function Create_Field($num=4)
{
	global $gekozenkleuren,$COLORS;

	for ($i=0;$i<$num;$i++)
	{
		Go_Random(count($COLORS));
	}
	$_SESSION['mm_veld'] = $gekozenkleuren;
}

if (isset($_GET['action']) && $_GET['action']=="changename")
{
	$_SESSION['mm_user']['name'] = $_POST['name'];

	Header("Location: ".$_SERVER['SCRIPT_NAME']);
	exit;
}

if (isset($_POST['action']) && $_POST['action']=="kieskleuren")
{
	/* Elke kleur mag maar 1x gekozen worden */
	$tellen[$_POST['veld1']]=0;
	$tellen[$_POST['veld2']]=0;
	$tellen[$_POST['veld3']]=0;
	$tellen[$_POST['veld4']]=0;

	$tellen[$_POST['veld1']]++;
	$tellen[$_POST['veld2']]++;
	$tellen[$_POST['veld3']]++;
	$tellen[$_POST['veld4']]++;
	if ($tellen[$_POST['veld1']]>1 || $tellen[$_POST['veld2']]>1 || $tellen[$_POST['veld3']]>1 || $tellen[$_POST['veld4']]>1)
	{
		Header("Location: ".$_SERVER['SCRIPT_NAME']);
		exit;
	}
	/* Elke kleur mag maar 1x gekozen worden */

	/* kleuren opslaan in de gebruikersession */
	if (isset($_SESSION['mm_user']['done']))
		$hoeveelste = count($_SESSION['mm_user']['done']);
	else
		$hoeveelste = 0;
	$_SESSION['mm_user']['done'][$hoeveelste][1] = $_POST['veld1'];
	$_SESSION['mm_user']['done'][$hoeveelste][2] = $_POST['veld2'];
	$_SESSION['mm_user']['done'][$hoeveelste][3] = $_POST['veld3'];
	$_SESSION['mm_user']['done'][$hoeveelste][4] = $_POST['veld4'];
	/* kleuren opslaan in de gebruikersession */

	/* Zwart: Hoeveel kleuren op de goede plaats? */
	$black=0;
	if ($_SESSION['mm_veld'][0] == $_POST['veld1'])
		$black++;
	if ($_SESSION['mm_veld'][1] == $_POST['veld2'])
		$black++;
	if ($_SESSION['mm_veld'][2] == $_POST['veld3'])
		$black++;
	if ($_SESSION['mm_veld'][3] == $_POST['veld4'])
		$black++;
	/* Zwart: Hoeveel kleuren op de goede plaats? */

	/* Wit: Hoeveel goede kleuren? */
	$white=0;
	if ($_SESSION['mm_veld'][0]==$_POST['veld1'] || $_SESSION['mm_veld'][1]==$_POST['veld1'] || $_SESSION['mm_veld'][2]==$_POST['veld1'] || $_SESSION['mm_veld'][3]==$_POST['veld1'])
		$white++;
	if ($_SESSION['mm_veld'][0]==$_POST['veld2'] || $_SESSION['mm_veld'][1]==$_POST['veld2'] || $_SESSION['mm_veld'][2]==$_POST['veld2'] || $_SESSION['mm_veld'][3]==$_POST['veld2'])
		$white++;
	if ($_SESSION['mm_veld'][0]==$_POST['veld3'] || $_SESSION['mm_veld'][1]==$_POST['veld3'] || $_SESSION['mm_veld'][2]==$_POST['veld3'] || $_SESSION['mm_veld'][3]==$_POST['veld3'])
		$white++;
	if ($_SESSION['mm_veld'][0]==$_POST['veld4'] || $_SESSION['mm_veld'][1]==$_POST['veld4'] || $_SESSION['mm_veld'][2]==$_POST['veld4'] || $_SESSION['mm_veld'][3]==$_POST['veld4'])
		$white++;
	/* Wit: Hoeveel goede kleuren? */

	$_SESSION['mm_user']['done'][$hoeveelste]['black'] = $black;
	$_SESSION['mm_user']['done'][$hoeveelste]['white'] = $white;
	if ($black==4)
	{
		$score = 10*$hoeveelste+10+(time()-$_SESSION['mm_user']['starttime']);
		$_SESSION['mm_user']['gameover'] = 1;
		$_SESSION['mm_user']['playtime'] = time()-$_SESSION['mm_user']['starttime'];
		$_SESSION['mm_user']['score'] = $score;
		mysql_query("INSERT INTO mastermind (name,score,attempts,playtime,time) VALUES ('".$_SESSION['mm_user']['name']."','$score','".(1+$hoeveelste)."','".$_SESSION['mm_user']['playtime']."','".time()."')");
	}
	if ($hoeveelste == 8)
		$_SESSION['mm_user']['gameover'] = 2;

	Header("Location: ".$_SERVER['SCRIPT_NAME']);
	exit;
}

if (isset($_GET['action']) && $_GET['action']=="stop")
{
	$name = $_SESSION['mm_user']['name'];
	$_SESSION['mm_veld'] = NULL;
	$_SESSION['mm_user'] = NULL;
	$_SESSION['mm_user']['name'] = $name;

	Header("Location: ".$_SERVER['SCRIPT_NAME']);
	exit;
}

if (!isset($_SESSION['mm_user']['play']) || $_SESSION['mm_user']['play']!=1)
{
	if (isset($_POST['check']) && Goede_Gebruikersnaam($_POST['name']))
	{
		$_SESSION['mm_user']['play'] = 1;
		$_SESSION['mm_user']['starttime'] = time();
		$_SESSION['mm_user']['name'] = $_POST['name'];
		Create_Field();

		Header("Location: ".$_SERVER['SCRIPT_NAME']);
		exit;
	}
	?>
	<html>
	<head><title>MASTERMIND</title></head>
<script>
if (top.location!=this.location)
	top.location='<?=$_SERVER['SCRIPT_NAME']?>';
</script>
	<body style='margin:0px;'>
	<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
	<tr>
	<td><center>
	<form method=post action="<?=$_SERVER['SCRIPT_NAME']?>"><input type=hidden name=check value=1>
	Name <input type=text name=name value="<?=(isset($_SESSION['mm_user']['name']))?$_SESSION['mm_user']['name']:"Anonymous"?>" maxlenght=22><br>
	<br>
	<input type=submit value="PLAY"></form>
	<?
	die("</td></tr></table></body>");
}

$OPENSOURCE = (isset($_SESSION['mm_user']['gameover'])) ? 1 : $OPENSOURCE;

?>
<html>

<head>
<title>MASTERMIND</title>
<style>
BODY,TABLE,INPUT { font-family:Verdana;font-size:11px;color:black;line-height:150%;cursor:default; }
</style>
<script>
if (top.location!=this.location)
	top.location='<?=$_SERVER['SCRIPT_NAME']?>';
</script>
</head>

<body style='margin:0px;overflow:auto;' bgcolor=#00ddff>
<?

if (isset($_GET['page']) && $_GET['page']=="gamerules")
{
	echo "<table border=0 cellpadding=0 cellspacing=0 width=50% height=100% align=center><tr valign=middle><td><center><b>GUIDE</b><br><br>Your target is a score as <b>low as possible</b>.<br>Your score is the sum of all absolute values of all fields.<br>Fields are named from 1 to 25, 1 being the upper left field and 25 being the lower right.<br>Every field has a value, by default from -12 to 12.<br>You can change the value of one field by clicking another.<br>For example: you click on field 1 (value = -12) -> you substract 12 from field 1-12=-11=14.<br>Another example: you click on field 6 (value = 4) -> you add 4 to field 6+4=10.<br>You can see how your clicking affects other fields by hoovering over the fields (Do x to y).</td></tr></table>";
	die;
}
if (isset($_GET['page']) && $_GET['page']=="top10")
{
	echo "<table border=0 cellpadding=0 cellspacing=0 width=50% height=100% align=center><tr valign=middle><td>";
	echo "<table border=0 cellpadding=4 cellspacing=0 align=center width=100%><tr><td style='border-bottom:solid 1px black;' width=1><b>Rank&nbsp;</td><td style='border-bottom:solid 1px black;'><b>Name</td><td style='border-bottom:solid 1px black;'><center><b>Score</td><td style='border-bottom:solid 1px black;'><b><center>Moves</td><td style='border-bottom:solid 1px black;'><b>PlayTime</td><td style='border-bottom:solid 1px black;'><b>Date&Time</td></tr>";
	$q = mysql_query("SELECT * FROM mastermind ORDER BY score,playtime LIMIT 10");
	$r=0;
	while ($l = mysql_fetch_assoc($q))
	{
		$r++;
		echo "<tr><td align=right>$r.&nbsp;</td><td>".$l['name']."</td><td><b><center>".$l['score']."</td><td><center>".$l['attempts']."</td><td>".$l['playtime']." seconds</td><td>".date("Y-m-d H:i:s",$l['time'])."</td></tr>";
	}
	echo "</table>";
	echo "</td></tr></table>";
	die;
}
if (isset($_GET['page']) && $_GET['page']=="changename")
{
	echo "<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><form name=changename method=post action=\"?action=changename\"><tr valign=middle><td><center>Name <input type=text name=name value=\"".(($_SESSION['mm_user']['name'])?$_SESSION['mm_user']['name']:"Anonymous")."\" maxlenght=22><br><br><input type=submit value=\"CHANGE\"></td></tr></table>";
	die;
}

?>
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
<tr valign=middle>
<td width=20%><center>
<a href="?action=stop"><?=(isset($_SESSION['mm_user']['gameover']) && $_SESSION['mm_user']['gameover']==2)?"New Game":"Stop"?></a><br><br>
<a href="?page=top10">Top 10</a><br><br>
<a href="?page=changename">Change Name</a>
<br>
</td>
<td><center>


<table border=0 cellpadding=0 cellspacing=20 bgcolor=#772200>
<tr height=30>
<td></td>
<td width=30 bgcolor=<?=($OPENSOURCE)?$_SESSION['mm_veld'][0]:"#774400"?>>&nbsp;</td>
<td width=30 bgcolor=<?=($OPENSOURCE)?$_SESSION['mm_veld'][1]:"#774400"?>>&nbsp;</td>
<td width=30 bgcolor=<?=($OPENSOURCE)?$_SESSION['mm_veld'][2]:"#774400"?>>&nbsp;</td>
<td width=30 bgcolor=<?=($OPENSOURCE)?$_SESSION['mm_veld'][3]:"#774400"?>>&nbsp;</td>
<td></td>
</tr>
<tr height=0>
<td colspan=6></td>
</tr>
<?

for ($i=0;$i<9;$i++)
{
	?>
<tr height=30>
<td width=30 align=right><b><?=($i+1)?></td>
<td bgcolor=<?=(isset($_SESSION['mm_user']['done'][$i][1]))?$_SESSION['mm_user']['done'][$i][1]:""?>></td>
<td bgcolor=<?=(isset($_SESSION['mm_user']['done'][$i][2]))?$_SESSION['mm_user']['done'][$i][2]:""?>></td>
<td bgcolor=<?=(isset($_SESSION['mm_user']['done'][$i][3]))?$_SESSION['mm_user']['done'][$i][3]:""?>></td>
<td bgcolor=<?=(isset($_SESSION['mm_user']['done'][$i][4]))?$_SESSION['mm_user']['done'][$i][4]:""?>></td>
<td width=20>
<?

if (isset($_SESSION['mm_user']['done'][$i]['black']) && $_SESSION['mm_user']['done'][$i]['black']>0)
	for ($j=0;$j<$_SESSION['mm_user']['done'][$i]['black'];$j++)
		echo "<font color=black>*</font> ";
if (isset($_SESSION['mm_user']['done'][$i]['white']) && $_SESSION['mm_user']['done'][$i]['white']>0)
	for ($j=0;$j<($_SESSION['mm_user']['done'][$i]['white']-$_SESSION['mm_user']['done'][$i]['black']);$j++)
		echo "<font color=white>*</font> ";

?>
</td>
</tr>
	<?
}

?>
<tr height=30>
<td></td>
<td width=30 bgcolor=#774400 id=kleurveld1>&nbsp;</td>
<td width=30 bgcolor=#774400 id=kleurveld2>&nbsp;</td>
<td width=30 bgcolor=#774400 id=kleurveld3>&nbsp;</td>
<td width=30 bgcolor=#774400 id=kleurveld4>&nbsp;</td>
<td></td>
</tr>
<tr>
<form name=kieskleuren method=post><input type=hidden name=check value=1><input type=hidden name=action value=kieskleuren>
<td></td>
<td><select name=veld1 style='width:30px;height:30px;' OnChange="document.getElementById('kleurveld1').bgColor=this.value"><option style='background-color:#774400;' value=black>&nbsp;<option style='background-color:black;' value=black>&nbsp;<option style='background-color:white;' value=white>&nbsp;<option style='background-color:green;' value=green>&nbsp;<option style='background-color:red;' value=red>&nbsp;<option style='background-color:yellow;' value=yellow>&nbsp;<option style='background-color:blue;' value=blue>&nbsp;</select></td>
<td><select name=veld2 style='width:30px;height:30px;' OnChange="document.getElementById('kleurveld2').bgColor=this.value"><option style='background-color:#774400;' value=black>&nbsp;<option style='background-color:black;' value=black>&nbsp;<option style='background-color:white;' value=white>&nbsp;<option style='background-color:green;' value=green>&nbsp;<option style='background-color:red;' value=red>&nbsp;<option style='background-color:yellow;' value=yellow>&nbsp;<option style='background-color:blue;' value=blue>&nbsp;</select></td>
<td><select name=veld3 style='width:30px;height:30px;' OnChange="document.getElementById('kleurveld3').bgColor=this.value"><option style='background-color:#774400;' value=black>&nbsp;<option style='background-color:black;' value=black>&nbsp;<option style='background-color:white;' value=white>&nbsp;<option style='background-color:green;' value=green>&nbsp;<option style='background-color:red;' value=red>&nbsp;<option style='background-color:yellow;' value=yellow>&nbsp;<option style='background-color:blue;' value=blue>&nbsp;</select></td>
<td><select name=veld4 style='width:30px;height:30px;' OnChange="document.getElementById('kleurveld4').bgColor=this.value"><option style='background-color:#774400;' value=black>&nbsp;<option style='background-color:black;' value=black>&nbsp;<option style='background-color:white;' value=white>&nbsp;<option style='background-color:green;' value=green>&nbsp;<option style='background-color:red;' value=red>&nbsp;<option style='background-color:yellow;' value=yellow>&nbsp;<option style='background-color:blue;' value=blue>&nbsp;</select></td>
<td></td>
</tr>
<tr><td colspan=6><input type=<?=(isset($_SESSION['mm_user']['gameover']))?"button":"submit"?> value="<?=(isset($_SESSION['mm_user']['gameover']))?"New Game":"Check"?>" style='width:100%;'<?=(isset($_SESSION['mm_user']['gameover']))?" OnClick=\"document.location='?action=stop';\"":""?>></td></tr>
</form>
</table>
<br>
<?=(isset($_SESSION['mm_user']['gameover']) && $_SESSION['mm_user']['gameover']==1)?"<br><b>GameOver!</b> You finished this level! It took you <b>".$_SESSION['mm_user']['playtime']." seconds</b>...<br>You're now in the Hall of Fame with the name <b>\"".$_SESSION['mm_user']['name']."\"</b>, with a score of <b>".$_SESSION['mm_user']['score']."</b>.":""?>

</td>
<td width=20%><center><b>GAME RULES</b><br><br></center>You must find the right order of right colors. There are too many colors for the slots. Find the right colors and than the right order.<br>Black star (<font color=black>*</font>) - right color, right place<br>White star (<font color=white>*</font>) - right color<br><a href="?page=gamerules">More...</a><br><br><b>6 colors</b><br>Time: <?=(isset($_SESSION['mm_user']['gameover']) && $_SESSION['mm_user']['gameover']==1)?$_SESSION['mm_user']['playtime']:(time()-$_SESSION['mm_user']['starttime'])?> sec<br><br>Your name: <?=$_SESSION['mm_user']['name']?></td>
</tr></table>

</body>
</html>


