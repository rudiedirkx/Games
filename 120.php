<?
// WORDMIX

session_start();

include("connect.php");

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );


function Go_Random($max,$niveau) {
	global $list;
	$id = rand(0,$max-1);
	if (@in_array($id,$list[$niveau]))
		Go_Random($max,$niveau);
	else
		$list[$niveau][] = $id;
}

$woordenmixen=1;
$lang="nl";
if ($lang == "nl") {
	$zin[0] = "Ik zat laatst bij de kapper.";
	$zin[1] = "Ik was aan het schaatsen, een tijdje geleden.";
	$zin[2] = "Mijn hemel, wat ben jij mooi!";
	$zin[3] = "Ik wil gewoon lekker neuken!!";
	$zin[4] = "Doe mij nog een pilsje van de zaak!";
	$zin[5] = "Deze computer maakt mijn hoofd dol.";
	$zin[6] = "een zin zonder hoofdletter of punt is erg vervelend";
	$zin[7] = "Twee hoofdletters vindt Bert ook erg irritant, is het niet";
}
else {
	$zin[0] = "My dick is big.";
	$zin[1] = "I have a bigger dick than your momma!";
	$zin[2] = "I would like to fuck my teacher!";
	$zin[3] = "I once saw a cat climbing a tree.";
	$zin[4] = "That were some remarkable moves, he made.";
	$zin[5] = "now a sentence without a capital or a dot";
	$zin[6] = "That should be a lot harder to construct";
	$zin[7] = "The last word in this sentence is not bitch but slut.";
}
$ZIN = @$zin[ @$_SESSION['wm_user']['level'] ];

if (@$_POST['check'] && !@$_SESSION['wm_user']['play'] && Goede_Gebruikersnaam(@$_POST['name'])) {
	$_SESSION['wm_user']['play'] = 1;
	$_SESSION['wm_user']['starttime'] = time();
	$_SESSION['wm_user']['name'] = $_POST['name'];
	$_SESSION['wm_user']['level'] = 0;
	$_SESSION['wm_user']['gameover'] = NULL;

	Header("Location: " . $_SERVER['SCRIPT_NAME']);
	exit;
}

if (@$_POST['wmcheck'] && @$_SESSION['wm_user']['play']) {
	if ($_POST['dezin'] == $zin[$_SESSION['wm_user']['level']]) {
		$_SESSION['wm_user']['level']++;
	}
	if (!$zin[$_SESSION['wm_user']['level']]) {
		// mysql_query("INSERT INTO wordmix (name,levels,playtime,time) VALUES ('".$_SESSION['wm_user']['name']."','".$_SESSION['wm_user']['level']."','".(1+time()-$_SESSION['wm_user']['starttime'])."','".time()."')") or die(mysql_error());
		$_SESSION['wm_user']['gameover'] = 1;
	}

	Header("Location: " . $_SERVER['SCRIPT_NAME']);
	exit;
}

if (@$_GET['action'] == "retry") {
	$_SESSION['wm_user']['starttime'] = time();
	$_SESSION['wm_user']['level'] = 0;
	$_SESSION['wm_user']['gameover'] = NULL;

	Header("Location: " . $_SERVER['SCRIPT_NAME']);
	exit;
}

?>
<html>

<head>
<title>WORDMIX</title>
<style>
BODY,TABLE,INPUT { font-family:Verdana;font-size:12px;color:black;line-height:150%;cursor:default; }
</style>
</head>

<body style='margin:0px;overflow:auto;'>
<?

if (!@$_SESSION['wm_user']['play']) {
	?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
	<tr>
	<td><center>
	<form method=post action="<?= $_SERVER['SCRIPT_NAME'] ?>"><input type=hidden name=check value=1>
	Name <input type=text name=name value="<?= @$_SESSION['wm_user']['name'] ?: "Anonymous" ?>" maxlenght="22" /><br>
	<br>
	<input type=submit value="PLAY"></form>
	<?
	die("</td></tr></table></body>");
}

$woorden = explode(" ", $ZIN);

$zofw=0;
for ($i=0;$i<count($woorden);$i++) {
	Go_Random(count($woorden), 0);
}

echo "<table border=0 cellpadding=15 cellspacing=0><form name=wordmix method=post><input type=hidden name=wmcheck value=1><tr valign=top><td><center>";
if ($_SESSION['wm_user']['gameover']==1)
{
	echo "<font face=\"verdana\" style='font-size:13px;'><b>GameOver!</b> You succesfully finished this game, in ".(1+time()-$_SESSION['wm_user']['starttime'])." sec!<br><a href=\"?action=retry\">Again</a>";
}
else
{
	echo "<b>LEVEL ".($_SESSION['wm_user']['level']+1)."</b><br><br><font face=\"courier new\" style='font-size:13px;'>";
	for ($i=0;$i<count($woorden);$i++)
	{
		$zofw++;
		$woord = $woorden[$list[0][$i]];
		if ($woordenmixen)
		{
			$max = strlen($woord);
			for ($j=0;$j<$max;$j++)
			{
				Go_Random($max,$zofw);
			}
			$showzin = "";
			for ($k=0;$k<$max;$k++)
			{
				$showzin .= $woord[$list[$zofw][$k]];
			}
			$showzin .= " ";
		}
		else
		{
			$showzin .= $woord." ";
		}
	}
	echo trim($showzin);
	echo "<br><input style='font-family:courier new;font-size:13px;' name=dezin size=".(strlen($ZIN))." maxlength=".(strlen($ZIN))."><br><input type=submit value=\"CHECK\">";
}
echo "</td></tr></form></table>";


