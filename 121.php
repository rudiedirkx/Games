<?php
// POKER

error_reporting(2047);
session_start();

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

$cards = array("","h.2","h.3","h.4","h.5","h.6","h.7","h.8","h.9","h.10","h.11","h.12","h.13","h.14","k.2","k.3","k.4","k.5","k.6","k.7","k.8","k.9","k.10","k.11","k.12","k.13","k.14","r.2","r.3","r.4","r.5","r.6","r.7","r.8","r.9","r.10","r.11","r.12","r.13","r.14","s.2","s.3","s.4","s.5","s.6","s.7","s.8","s.9","s.10","s.11","s.12","s.13","s.14");
$kleuren = array("h"=>"harten","k"=>"klaveren","r"=>"ruiten","s"=>"schoppen");
$LK = array("h"=>"REDS", "r"=>"REDS", "k"=>"BLACKS", "s"=>"BLACKS");	// lk = Letterlijke Kleur (rood of zwart)	// KAN veranderd worden. Zou niet weten waarom, maar is niet erg
$voor_rules_van=array("J","Q","K","A");		// niet veranderen
$voor_rekenen=array(11,12,13,14);			// idem

$IMAGE_DIR = "images/";						// Waar je 4 plaatjes (zie Array $kleuren+'.gif') staan. Als IMAGE_DIR niet leeg is, MOET het eindigen op een '/'

$voor_rules_naar=array("Jacks","Queens","Kings","Aces");

$MIN_VOOR_ONE_PAIR = 10;						// Voorbeeld: als dit 8 is, heb je geen ONE PAIR met twee zessen. Mogelijke waarden: 2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K' of 'A'

$CHEATING_IS_OK = FALSE;							// Als CHEATING aanstaat kan je na het inzetten de 10 gekozen kaarten al zien (ipv de 5 die je in je hand hebt)

$ALL_REDS_OR_BLACKS = FALSE;						// Eigenlijk onzin: Als al je kaarten rood, of al je kaarten zwart zijn, maar eigenlijk heb je een waardeloze hand, krijg je toch een traktatie

// Voor de iets minder bedeelden onder ons:	STRAIGHT: 5 kaarten met opeenvolgende waardes
// 											FLUSH: 5 kaarten met dezelfde kleur (kaartkleur, niet letterlijke kleur)
//											ROYAL is als het de 5 hoogste waarden zijn

$DEBUG_MODE = 0;			// Als je fouten tegen komt, kan je DEBUG_MODE aanzetten (1), dan gaat alles stap voor stap
//						.. DEBUG info:
//							je zou 'YOU LOSE 1' moeten krijgen als je ONE PAIR hebt die te laag is
//							je zou 'YOU LOSE 2' moeten krijgen

$UITBETALEN['all_reds_or_blacks']	= 0.5;	// Hoe ALL REDS/BLACKS uit wordt betaald	// Alleen als-ie aanstaat natuurlijk
$UITBETALEN['one_pair']				= 1;	// 2 dezelfde waarden, minstens MIN_FOR_ONE_PAIR
$UITBETALEN['two_pairs']			= 2;	// 2 dezelfde waarden + 2 dezelfde waarden
$UITBETALEN['three_of_a_kind']		= 3;	// 3 dezelfde waarden
$UITBETALEN['straight']				= 4;	// 5 opeenvolgende waarden
$UITBETALEN['flush']				= 5;	// 5 dezelfde kaartkleur
$UITBETALEN['full_house']			= 6;	// 3 dezelfde waarden + 2 dezelfde waarden
$UITBETALEN['four_of_a_kind']		= 7;	// 4 dezelfde waarden
$UITBETALEN['straight_flush']		= 8;	// Dit heb ik nog nooit gehad. En ik heb al HEEEL veel gespeeld (alleen maar op Enter blijven drukken en spelletje gaat gewoon door)
$UITBETALEN['royal_flush']			= 10;	// Dit heb ik nog nooit van me leven gezien! Niet eens op TV :) Voor de duidelijkheid: 10 t/m A in dezelfde kaartkleur

$MAX_BET = 25000;							// Als MAX_BET==0, is er geen maximale inzet

$MYSQL_ON = TRUE;								// Als je MySQL hebt _EN_ je bent verbonden met een database (!!), wordt er een Hall Of Fame bijgehouden
$MYSQL_TABLE_NAME = "poker";				// Volledige table name
















require_once('connect.php');

if (!isset($_SESSION['poker']['score']))
	$_SESSION['poker']['score'] = 1000;

if (isset($_SESSION['poker']['bet']))
	$BET = $_SESSION['poker']['bet'];

function Go_Random($max)
{
	global $used_randoms;
	$id = rand(1,$max);
	if (isset($used_randoms) && is_array($used_randoms) && in_array($id,$used_randoms))
		Go_Random($max);
	else
	{
		$used_randoms[] = $id;
		return $id;
	}
}

/* //// //// //// //// */

if (isset($_GET['otherpage']))
{
	$_SESSION['poker']['otherpage']=$_GET['otherpage'];

	Header("Location: ".BASEPAGE);
	exit;
}
if (isset($_GET['save'], $_GET['name']) && $MYSQL_ON)
{
	if ( 5000 < $_SESSION['poker']['score'] )
	{
		mysql_query("INSERT INTO $MYSQL_TABLE_NAME (name,score,score_time) VALUES ('".strtoupper(strip_tags(trim($_GET['name'])))." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")','".$_SESSION['poker']['score']."','".time()."')") or die(($DEBUG_MODE)?"MySQL error:<br>".mysql_error():"MySQL ERROR");
		$_SESSION['poker']['score'] = 0;
	}

	Header("Location: ".BASEPAGE);
	exit;
}

if (isset($_POST['action']) && $_POST['action']=="bet" && isset($_POST['bet']) && 0 <= $_POST['bet'] && 0 <= $_SESSION['poker']['score']-((int)trim($_POST['bet'])) )
{
	if ($MAX_BET>0)
		if ($_POST['bet'] <= $MAX_BET)
			$_SESSION['poker']['bet'] = (int)trim($_POST['bet']);
	Header("Location: ?deal=1");
	exit;
}
else if (isset($_GET['action']) && $_GET['action']=="unbet")
{
	$_SESSION['poker']['step'] = 0;
//	unset($_SESSION['poker']['bet']);
	unset($_SESSION['poker']['cards']);
	Header("Location: ".BASEPAGE);
	exit;
}
else if (isset($_GET['action']) && $_GET['action']=="start_over")
{
	unset($_SESSION['poker']);
	Header("Location: ".BASEPAGE);
	exit;
}

if (isset($_GET['deal']) && $_GET['deal']==1 && isset($BET))
{
	unset($_SESSION['poker']['cards']);
	$_SESSION['poker']['score']-=$BET;
	$_SESSION['poker']['step'] = 1;

	// 10 verschillende wilekeurige kaarten selecteren dmv 10x Go_Random().
	// De waarde en kleur van een kaart wordt vastgesteld door een waarde van 1 t/m 52,
	// dat is elke waarde (13) voor elke kleur (4). Ze worden opgeslagen in $used_randoms

	for ($i=0;$i<10;$i++)
	{
		Go_Random(4*13);
	}

	if ($DEBUG_MODE==1)
	{
		echo "<pre><b>10 randoms:</b>\n";
		print_r($used_randoms);
	}

	// Er staan nu 10 verschillende keys in $used_randoms, van 1 t/m 52
	// De array wordt afgelopen om de waarden onder goede naam (kleur en waarde) in een SESSION te stoppen
	// De SESSION poker[cards] bevat dadelijk 10 keys, elke key
	for ($i=0;$i<count($used_randoms);$i++)
	{
		// n is een key (van 1 t/m 52)
		$n = $used_randoms[$i];
		if ($DEBUG_MODE==1)
			echo "&n = $n<br>";

		// kleur+waarde van kaart $i, gekregen dmv n uit array cards. Voorbeeld r.8, h.10, k.13
		// De waarden van de kaarten zijn nog steeds numeriek (geen K voor King of A voor Ace, etc)
		$card0 = $cards[$n];
		$card = explode(".",$card0);
		if ($DEBUG_MODE==1)
		{
			echo "&card = $card0<br>";
			print_r($card);
			echo "\n";
		}
		// En alles in een array om in de SESSION te gooien
		$inh = array("num" => $n, "full" => $card0, "kleur" => $card[0], "waarde" => $card[1]);
		$_SESSION['poker']['cards'][] = $inh;
	}
	if ($DEBUG_MODE==1)
	{
		echo "\n<b>ARRAY KAARTEN:</b>\n";
		print_r($_SESSION['poker']['cards']);
	}
	if ($DEBUG_MODE!=1)
		Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit('<br><b><a href="'.basename($_SERVER['SCRIPT_NAME']).'">NEXT</a>');
}
if (isset($_POST['nextstep']) && $_POST['nextstep']==2)
{
	$_SESSION['poker']['step']=2;
	$keepers=0;
	for ($i=0;$i<5;$i++)
	{
		if (isset($_POST['cd'][$i]) && $_POST['cd'][$i]=="on")
		{
		//	$_SESSION['poker']['cards'][$i]['keep']=1;
			$keepers++;
		}
		else
		{
			unset($_SESSION['poker']['cards'][$i]);
		}
	}
	for ($i=9;$i>9-$keepers;$i--)
	{
		unset($_SESSION['poker']['cards'][$i]);
	}

	/* Er zijn nog 5 kaarten over. */
	// hoeveel er van 1 waarde is wordt opgeslagen in $counts (nooit meer dan 5 keys)
	$counts = Array();
	foreach ($_SESSION['poker']['cards'] AS $i => $ci)
	{
		$sk[] = $ci['kleur'];
		$sw[] = $ci['waarde'];
		 if (isset($counts[$ci['waarde']]))	$counts[$ci['waarde']]++;
		 else								$counts[$ci['waarde']]=1;
	}
	sort($sw);
	sort($sk);

// print_r( $sw );
// echo count(array_flip($sw));

	// $sk[x] is de kleur van kaart x
	// $sw[x] is de numerieke waarde van kaart x
	if ( (max($sw) == min($sw)+4 && 5 == count(array_flip($sw))) || (in_array(14,$sw) && in_array(2,$sw) && in_array(3,$sw) && in_array(4,$sw) && in_array(5,$sw)) )
	{	// sowieso een straight, maar misschien nog spannender

		if ( 1 == count(array_flip($sk)) )
		{	// Allemaal dezelfde kleur

			if (min($sw) == 10)
			{	// laagste kaart is een 10
				// ROYAL FLUSH
				if ($DEBUG_MODE==2) print("<b>ROYAL FLUSH");
				$_SESSION['poker']['result'] = "ROYAL FLUSH";
				$_SESSION['poker']['winnings']=$UITBETALEN['royal_flush'];
			}
			else
			{
				// STRAIGHT FLUSH
				if ($DEBUG_MODE==2) print("<b>STRAIGHT FLUSH");
				$_SESSION['poker']['result'] = "STRAIGHT FLUSH";
				$_SESSION['poker']['winnings']=$UITBETALEN['straight_flush'];
			}
		}
		else
		{
			// STRAIGHT
			if ($DEBUG_MODE==2) print("<b>STRAIGHT");
			$_SESSION['poker']['result'] = "STRAIGHT";
			$_SESSION['poker']['winnings']=$UITBETALEN['straight'];
		}
	}
	else if ( 1 == count(array_flip($sk)) )
	{
		// FLUSH
		// Allemaal dezelfde kleur. Welke kleur is helemaal niet boeiend
		// Kleur: current(array_flip($sk))
		if ($DEBUG_MODE==2) print("<b>FLUSH");
		$_SESSION['poker']['result'] = "FLUSH";
		$_SESSION['poker']['winnings']=$UITBETALEN['flush'];
	}
	else if (($sw[0] == $sw[1] && $sw[1] == $sw[2] && $sw[2] == $sw[3]) || ($sw[1] == $sw[2] && $sw[2] == $sw[3] && $sw[3] == $sw[4]))
	{
		// FOUR-OF-A-KIND
		// Vier kaarten met dezelfde waarde
		if ($DEBUG_MODE==2) print("<b>FOUR-OF-A-KIND");
		$_SESSION['poker']['result'] = "FOUR-OF-A-KIND";
		$_SESSION['poker']['winnings']=$UITBETALEN['four_of_a_kind'];
	}
	else if (($sw[0] == $sw[1] && $sw[1] == $sw[2] && $sw[3] == $sw[4]) || ($sw[0] == $sw[1] && $sw[2] == $sw[3] && $sw[3] == $sw[4]))
	{
		// FULL HOUSE
		// Twee kaarten met waarde A en drie met waarde B
		if ($DEBUG_MODE==2) print("<b>FULLHOUSE");
		$_SESSION['poker']['result'] = "FULLHOUSE";
		$_SESSION['poker']['winnings']=$UITBETALEN['full_house'];
	}
	else if (($sw[0] == $sw[1] && $sw[1] == $sw[2]) || ($sw[1] == $sw[2] && $sw[2] == $sw[3]) || ($sw[2] == $sw[3] && $sw[3] == $sw[4]))
	{
		// THREE-OF-A-KIND
		// Drie kaarten met waarde A en twee niet-dezelfden
		if ($DEBUG_MODE==2) print("<b>THREE-OF-A-KIND");
		$_SESSION['poker']['result'] = "THREE-OF-A-KIND";
		$_SESSION['poker']['winnings']=$UITBETALEN['three_of_a_kind'];
	}
	else if (($sw[0] == $sw[1] && $sw[1] != $sw[2] && $sw[1] != $sw[3] && $sw[1] != $sw[4]) || ($sw[1] == $sw[2] && $sw[2] != $sw[0] && $sw[2] != $sw[3] && $sw[2] != $sw[4]) || ($sw[2] == $sw[3] && $sw[3] != $sw[0] && $sw[3] != $sw[1] && $sw[3] != $sw[4]) || ($sw[3] == $sw[4] && $sw[4] != $sw[0] && $sw[4] != $sw[1] && $sw[4] != $sw[2]))
	{
		if (count($counts)== 3)
		{
			// TWO PAIR
			if ($DEBUG_MODE==2) print("<b>TWO PAIR");
			$_SESSION['poker']['result'] = "TWO PAIR";
			$_SESSION['poker']['winnings']=$UITBETALEN['two_pairs'];
		}
		else if (($sw[0] == $sw[1] && $sw[1] != $sw[2] && $sw[1] != $sw[3] && $sw[1] != $sw[4] && $sw[0]>=(int)str_ireplace($voor_rules_naar,$voor_rekenen,$MIN_VOOR_ONE_PAIR)) || ($sw[1] == $sw[2] && $sw[2] != $sw[0] && $sw[2] != $sw[3] && $sw[2] != $sw[4] && $sw[1]>=(int)str_ireplace($voor_rules_naar,$voor_rekenen,$MIN_VOOR_ONE_PAIR)) || ($sw[2] == $sw[3] && $sw[3] != $sw[0] && $sw[3] != $sw[1] && $sw[3] != $sw[4] && $sw[2]>=(int)str_ireplace($voor_rules_naar,$voor_rekenen,$MIN_VOOR_ONE_PAIR)) || ($sw[3] == $sw[4] && $sw[4] != $sw[0] && $sw[4] != $sw[1] && $sw[4] != $sw[2] && $sw[3]>=(int)str_ireplace($voor_rules_naar,$voor_rekenen,$MIN_VOOR_ONE_PAIR)))
		{
			// ONE PAIR
			if ($DEBUG_MODE==2) print("<b>ONE PAIR");
			$_SESSION['poker']['result'] = "ONE PAIR";
			$_SESSION['poker']['winnings']=$UITBETALEN['one_pair'];
		}
		else if ($ALL_REDS_OR_BLACKS)
		{
			for ($i=0;$i<count($sk);$i++)
				$lk[$i] = $LK[$sk[$i]];
			for ($i=0;$i<count($lk);$i++)
			{
				if (isset($ck[$lk[$i]]))	$ck[$lk[$i]]++;
				else						$ck[$lk[$i]]=1;
			}

			if ($DEBUG_MODE==2)
			{
				echo "<pre>\n";
				print_r($lk);
				print_r($ck);
				echo "</pre>";
			}

			if (count($ck)==1)	// Maar 1 verschillende kleur: $lk
			{
				// BONUS
				if ($DEBUG_MODE==2) print("<b>ALL $lk[0] ;)");
				$_SESSION['poker']['result'] = "ALL $lk[0] ;)";
				$_SESSION['poker']['winnings']=$UITBETALEN['all_reds_or_blacks'];
			}
			else
			{
				// YOU LOSE
				if ($DEBUG_MODE==2) print("<b>YOU LOSE 4");
				$_SESSION['poker']['winnings']=0;
			}
		}
		else
		{
			// YOU LOSE
			if ($DEBUG_MODE==2) print("<b>YOU LOSE 6");
			$_SESSION['poker']['winnings']=0;
		}
	}
	else if ($ALL_REDS_OR_BLACKS)
	{
		for ($i=0;$i<count($sk);$i++)
			$lk[$i] = $LK[$sk[$i]];
		for ($i=0;$i<count($lk);$i++)
		{
			if (isset($ck[$lk[$i]]))
				$ck[$lk[$i]]++;
			else
				$ck[$lk[$i]]=1;
		}

		if ($DEBUG_MODE==2)
		{
			echo "<pre>";
			print_r($lk);
			print_r($ck);
		}

		if (count($ck)==1)	// Maar 1 verschillende kleur: $lk
		{
			// BONUS
			if ($DEBUG_MODE==2) print("<b>ALL $lk[0] ;)");
			$_SESSION['poker']['result'] = "ALL $lk[0] ;)";
			$_SESSION['poker']['winnings']=$UITBETALEN['all_reds_or_blacks'];
		}
		else
		{
			// YOU LOSE
			if ($DEBUG_MODE==2) print("<b>YOU LOSE 8");
			$_SESSION['poker']['winnings']=0;
		}
	}
	else
	{
		// YOU LOSE
		if ($DEBUG_MODE==2) print("<b>YOU LOSE 10");
		$_SESSION['poker']['winnings']=0;
	}

	// Om niet op halven of kwarten oid uit te komen: CEIL. Moet de speler maar niet zo stom zijn voor een half fiche te spelen
	$_SESSION['poker']['score'] += floor($_SESSION['poker']['winnings']*$BET);

	if ($DEBUG_MODE!=2)
	{
		Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	}
	exit('<br><b><a href="'.basename($_SERVER['SCRIPT_NAME']).'">NEXT</a>');
}

/* //// //// //// //// */

/* REQUEST_URI schijnt niet overal te werken...
if (basename($_SERVER['REQUEST_URI']) != basename($_SERVER['SCRIPT_NAME']) || isset($_POST['bet']))
{
	Header("Location: ".basename($_SERVER['SCRIPT_NAME']));
	exit;
}	*/

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>POKER</title>
<style type="text/css">
BODY,TABLE,A { font-family:Verdana;font-size:11px;cursor:default;color:white;background:#444444; }
A { cursor:pointer;text-decoration:underline;color:orange; }
A:hover { text-decoration:none; }
.a { border:solid 1px black;background:#666666;color:white;width:100px;text-align:center;font-weight:none; }
input { border-radius:5px; -moz-border-radius:5px; }
</style>
<script type="text/javascript">
<!--//
function save_game() {
	<?php echo ( 5000 > $_SESSION['poker']['score'] ) ? "alert('Insufficient funds to save!');return false;\n" : ""; ?>
	name = prompt('What NAME do you want to save with?');
	if ( name ) {
		document.location = '?save=1&name=' + name;
	}
}
//-->
</script>
</head>

<body OnLoad="document.poker.<?php echo isset($_SESSION['poker']['step']) && $_SESSION['poker']['step'] > 0 ? "bt.focus()" : "bet.select()"; ?>;"><div align=center>
<?php

echo "<table border=0 cellpadding=7 cellspacing=0 style='border:solid 2px #000000;color:white;background:#444444;' width=250>";
if (isset($_SESSION['poker']['step']) && $_SESSION['poker']['step']>0)
{
	echo "<form name=poker method=post action=\"\" autocomplete=\"off\"><input type=hidden name=nextstep value=2>".(($_SESSION['poker']['step']==1)?"<input type=hidden name=bet value=".((isset($_SESSION['poker']['bet'])) ? $_SESSION['poker']['bet'] : 0).">":"");
	echo "<tr><td colspan=5 style='border-bottom:solid 2px black;background:#666666;'><center><b>YOUR CARDS</td></tr><tr>";
	$x=0;
	foreach($_SESSION['poker']['cards'] AS $i => $info)
	{
		$waarde = str_ireplace($voor_rekenen,$voor_rules_van,$info['waarde']);
		echo '<td width=50><center><label for="k'.$i.'"><img src="'.$IMAGE_DIR.$kleuren[$info['kleur']].'.gif"><b> '.$waarde.'</label>';
		echo ($_SESSION['poker']['step']<2) ? '<br><input type=checkbox name="cd['.$i.']" id="k'.$i.'" style="width:100%;">' : "";
		$x++;
		if ($x==5)
			break;
	}
	echo "</tr>";
	echo "<tr><td colspan=5><center>";
	if ($_SESSION['poker']['step']==2)
		echo (($_SESSION['poker']['winnings']) ? "<b>".(($_SESSION['poker']['winnings']>3)?"NICE: ":"").$_SESSION['poker']['result'].'</b><br>You win $'.($_SESSION['poker']['winnings']*$BET) : "<b>This is worthless!")."<br>";
	else
		echo "Select the cards you wish to keep";
	echo "</td></tr>";
	echo "<tr><td colspan=5><center><b>";
	echo (($_SESSION['poker']['step']<2)?"<input name=bt class=a type=submit value=\"NEXT\">":"<input name=bt class=a type=button value=\"OK\" OnClick=\"document.location='".basename($_SERVER['SCRIPT_NAME'])."?action=unbet';\">");
	echo "</td></tr></form></table>\n";
}
else
{
	// er moet nog ingezet worden. poker[step]==0 en !isset(poker[bet])
	echo "<form name=poker method=post action=\"\" autocomplete=\"off\"><input type=hidden name=action value=bet>\n";
	echo "<tr><td style='border-bottom:solid 2px black;background:#666666;'><center><b>PLACE YOUR BET</td></tr>\n";
	echo "<tr><td><center><input type=number class=a name=bet style='width:200px;font-weight:none;' OnClick=\"this.select();\" value='".( isset($BET) ? $BET : 0 )."'></td></tr>\n";
	echo "<tr><td><center><input type=submit class=a name=bt value='OK'></td></tr>\n";
	echo "</form></table>\n";
}

echo "<br>\n<br><b>SCORE: ".$_SESSION['poker']['score']."</b> (<a href=\"?action=start_over\">reset</a>) (BET: ".( isset($BET) ? $BET : 0 ).")<br>\n<br>\n</b>";
echo ($MYSQL_ON) ? "<a href=\"#\" onclick=\"return save_game();\">Save Score</a>" : "";
echo "<br>";

if ( ($CHEATING_IS_OK || $DEBUG_MODE) && isset($_SESSION['poker']['cards']) && is_array($_SESSION['poker']['cards']))
{
	$cards = Array( );
	foreach($_SESSION['poker']['cards'] AS $i => $info)
	{
		$waarde = str_ireplace($voor_rekenen,$voor_rules_van,$info['waarde']);
		$cards[] = "<b><img src=\"$IMAGE_DIR".$kleuren[$info['kleur']].".gif\"> $waarde</b>";
	}
	echo implode( " &nbsp; &nbsp; ", $cards );
//	echo "</center><pre>";
//	print_r($_SESSION['poker']['cards']);
}

if (isset($_SESSION['poker']['otherpage']) && $_SESSION['poker']['otherpage']=="pagerules")
{
	?>
<br>
<br>
<b><u>RULES</u></b><br>
<br>
<br>
MAX BET = <?php echo $MAX_BET > 0 ? $MAX_BET : "No max!"; ?><br>
<br>
<b>ONE PAIR</b><br>
Two cards (<?php echo str_ireplace($voor_rules_van,$voor_rules_naar,$MIN_VOOR_ONE_PAIR); ?> or higher) with the same value.<br>
Pays <?php echo $UITBETALEN['one_pair']; ?> times your bet.<br>
<br>
<b>TWO PAIR</b><br>
Two sets of cards with same values, any value.<br>
Pays <?php echo $UITBETALEN['two_pairs']; ?> times your bet.<br>
<br>
<b>THREE-OF-A-KIND</b><br>
Three cards with the same value.<br>
Pays <?php echo $UITBETALEN['three_of_a_kind']; ?> times your bet.<br>
<br>
<b>FULL HOUSE</b><br>
Three cards with the same value and two cards with the same value.<br>
Pays <?php echo $UITBETALEN['full_house']; ?> times your bet.<br>
<br>
<b>STRAIGTH</b><br>
Five subsequent cards.<br>
Pays <?php echo $UITBETALEN['straight']; ?> times your bet.<br>
<br>
<b>FOUR-OF-A-KIND</b><br>
Four cards with the same value.<br>
Pays <?php echo $UITBETALEN['four_of_a_kind']; ?> times your bet.<br>
<br>
<b>FLUSH</b><br>
Five cards in one suit.<br>
Pays <?php echo $UITBETALEN['flush']; ?> times your bet.<br>
<br>
<b>STRAIGHT FLUSH</b><br>
Five subsequent cards in one suit.<br>
Pays <?php echo $UITBETALEN['straight_flush']; ?> times your bet.<br>
<br>
<b>ROYAL FLUSH</b><br>
Five subsequent cards in one suit AND 10 as lowest.<br>
Pays <?php echo $UITBETALEN['royal_flush']; ?> times your bet.<br>
<br>
<br>
<a href="?otherpage=top10">Hall Of Fame</a>
	<?php

	unset($_SESSION['poker']['otherpage']);
}
else if (isset($_SESSION['poker']['otherpage']) && $_SESSION['poker']['otherpage']=="top10" && $MYSQL_ON)
{
	echo "<br><br><a href=\"?otherpage=pagerules\">Rules</a><br><br><b><u>HALL OF FAME</u></b><br><br>";
	$q = mysql_query('SELECT * FROM '.$MYSQL_TABLE_NAME.' ORDER BY score DESC, score_time DESC LIMIT 10;');
	$a=0;
	echo '<table border="1" cellpadding="4" cellspacing="2" id="top10">'.EOL;
	while ($qi = mysql_fetch_assoc($q)) {
		$a++;
		echo "<tr>".EOL;
		echo '<td align="right">'.$a.'.</td>'.EOL;
		echo '<td>'.$qi['name'].'</td>'.EOL;
		echo '<td align="right">'.number_format($qi['score'], 0, ".", ",").'</td>'.EOL;
		echo '<td align="right">'.date("D d-m-Y H:i",$qi['score_time']).'</td>'.EOL;
		echo "</tr>".EOL;
	}
	echo "</table>".EOL;
	unset($_SESSION['poker']['otherpage']);
}
else
{
	echo "<br>\n<br>\n<a href=\"?otherpage=pagerules\">Rules</a>";
	if ($MYSQL_ON)
		echo "<br>\n<br>\n<a href=\"?otherpage=top10\">Hall Of Fame</a>";
}
echo "</div>";

if ($DEBUG_MODE)
{
	echo "<pre>";
	print_r($_SESSION['poker']);
}

?>
