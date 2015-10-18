<?php
// BLACKJACK

session_start();

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

/* START CONFIG */
$config['max_cards_in_hand']	= 10;	// Max. aantal kaarten in de hand. Voor dealer zelfde als player
$config['min_dealer_score']		= 17;	// Min. score die de dealer moet hebben om geen kaart meer te pakken
$config['show_recent_score']	= FALSE;
$config['starting_budget']		= 100;	// dit krijg je meteen weer als je op 0 zit
$config['min_balance_for_hof']	= 2*$config['starting_budget'];	// als je minder dan dit hebt kan je niet 'opslaan'
/* EIND CONFIG */

/* START FUNCTIONS */
function Nieuwe_Kaart($who) {
	// Met '$who' weet je voor wie de kaart is.
	// De kaart wordt opgeslagen in ['dealer'] of ['player'].
	$kaartkleuren = array("harten","ruiten","schoppen","klaveren");
	$kaartnamen = array("","a","2","3","4","5","6","7","8","9","10","b","v","k");
	$kaartwaarden = array("","1 of 11","2","3","4","5","6","7","8","9","10","10","10","10");
	$cardno = rand(1,13);
	$kleur = $kaartkleuren[rand(0,3)];
	$kaart = $kaartnamen[$cardno];
	$waarde = $kaartwaarden[$cardno];
	// Hieronder wordt de getrokken kaart opgeslagen in cookies
	$next_i = (isset($_SESSION['blackjack'][$who]['i']) && $_SESSION['blackjack'][$who]['i']>0) ? $_SESSION['blackjack'][$who]['i']+1 : 1;
	$cards['kleur'] = $kleur;
	$cards['kaart'] = $kaart;
	$cards['waarde'] = $waarde;
	if (isset($_SESSION['blackjack'][$who]['i']))
		$_SESSION['blackjack'][$who]['i']++;
	else
		$_SESSION['blackjack'][$who]['i']=1;

	$_SESSION['blackjack'][$who][$next_i] = $cards;
}
function Bereken_Score($who) {
	$score=0;
	// Met '$who' weet je wiens score te berekenen.
	// De waarden worden uit ['dealer'] of ['player'] gehaald.
	if (isset($_SESSION['blackjack'][$who]['i']))
		$max = $_SESSION['blackjack'][$who]['i']+1;
	else
		$max = 1;
	for ($i=1;$i<$max;$i++)
		if ($_SESSION['blackjack'][$who][$i]['kaart'] != 'a')
			$score += $_SESSION['blackjack'][$who][$i]['waarde'];

	for ($i=1;$i<$max;$i++)
	{
		if ($_SESSION['blackjack'][$who][$i]['kaart'] == 'a')
		{
			if ($score+11 < 22)
				$score += 11;
			else
				$score += 1;
		}
	}
	return $score;
}
function Cards_For_Dealer() {
	global $config;
	if (Bereken_Score('player') > 21)
		Score_Opslaan();
	for ($j=0;$j<3;$j++)
	{
		if (Bereken_Score('dealer')<$config['min_dealer_score'])
			Nieuwe_Kaart('dealer');
	}
	Score_Opslaan();
}
function Laat_Kaarten_Zien($who) {
	if ( !in_array($who, Array("player","dealer")) ) return;

	// Met '$who' weet je wiens kaarten af te beelden.
	// Op volgorde van $i, uit ['dealer'][$i] of ['player'][$i].
	if ( isset($_SESSION['blackjack'][$who]['i']) )	$max = $_SESSION['blackjack'][$who]['i']+1;
	else											$max = 1;

	for ($i=1;$i<$max;$i++)
	{
		$kaart = $_SESSION['blackjack'][$who][$i]['kaart'];
		$kleur = $_SESSION['blackjack'][$who][$i]['kleur'];
		$kaarturl = "./images/".$kleur."_".$kaart.".gif";
		$kleur[0] = strtoupper($kleur[0]);
		echo "<img src=\"$kaarturl\" title=\"\" alt=\"($kleur ".$kaart.")\"> \n";
	}
}
function Reset_Game() {
	// Alle waarden worden gewist.
	// Heel het spel is leeg.
	$_SESSION['blackjack'] = FALSE;
	$_SESSION['blackjack']['player']['balance']=0;
	$_SESSION['blackjack']['dealer']['i']=0;
	$_SESSION['blackjack']['player']['i']=0;
}
function End_Game() {
	// Alle waarden, behalve ['balance'], worden gewist.
	// Alle cookies zijn leeg. Een nieuw spel kan worden gestart!
	$balance = $_SESSION['blackjack']['player']['balance'];
	$_SESSION['blackjack'] = FALSE;
	$_SESSION['blackjack']['player']['balance'] = $balance;
}
function Score_Opslaan() {
	// Logboek. Resultaten, winsten, scores worden opgeslagen in logboek.
	// De nieuwe balance van de player wordt ook opgeslagen.
	$bet = $_SESSION['blackjack']['player']['bet'];
	if (Bereken_Score('player') > 21)
	{
		// PLAYER BUST -> DEALER WINT
		$_SESSION['blackjack']['player']['balance'] -= $bet;
		$winner = "dealer";
	}
	else if (Bereken_Score('player') < 22 && Bereken_Score('dealer') > 21)
	{
		// DEALER BUST -> PLAYER WINT
		$_SESSION['blackjack']['player']['balance'] += $bet;
		if (Bereken_Score('player') == 21 && $_SESSION['blackjack']['player']['i'] == 2)
			$_SESSION['blackjack']['player']['balance'] += floor($bet/2);
		$winner = "player";
	}
	else if (Bereken_Score('player') < 22 && Bereken_Score('dealer') < 22)
	{
		if (Bereken_Score('dealer') > Bereken_Score('player'))
		{
			// DEALER WINT
			$_SESSION['blackjack']['player']['balance'] -= $bet;
			$winner = "dealer";
		}
		else if (Bereken_Score('dealer') < Bereken_Score('player'))
		{
			// PLAYER WINT
			$_SESSION['blackjack']['player']['balance'] += $bet;
			if (Bereken_Score('player') == 21 && $_SESSION['blackjack']['player']['i'] == 2)
			{
				$_SESSION['blackjack']['player']['balance'] += $bet;
			}
			$winner = "player";
		}
		else if (Bereken_Score('dealer')==Bereken_Score('player'))	//  && $_SESSION['blackjack']['dealer']['i']<=$_SESSION['blackjack']['player']['i']
		{
			// DEALER WINT
			$_SESSION['blackjack']['player']['balance'] -= $bet;
			$winner = "dealer";
		}
		else
		{
			// DRAW -> NIEMAND WINT
			$winner = "niemand";
		}
	}
	$_SESSION['blackjack']['pauze']=1;
	$_SESSION['blackjack']['winner'] = $winner;
	Header("Location: ".BASEPAGE);
	exit();
}
/* EIND FUNCTIONS */



// if (isset($_SESSION['blackjack']['player']))
if (!isset($_SESSION['blackjack']['player']['balance']) || $_SESSION['blackjack']['player']['balance']<=0) {
	$_SESSION['blackjack']['player']['balance'] = $config['starting_budget'];
}


if ( isset($_GET['action']) )	$action = $_GET['action'];
else							$action = '';


if ( $action == "save" && Goede_Gebruikersnaam($_POST['name']) ) {
	if ( $config['min_balance_for_hof'] <= (FLOAT)$_SESSION['blackjack']['player']['balance'] )
	{
		// mysql_query("INSERT INTO blackjack (name,score) VALUES ('".$_POST['name']."','".$_SESSION['blackjack']['player']['balance']."');") or die(mysql_error());
		Reset_Game();
	}
	$_SESSION['blackjack']['player']['name'] = $_POST['name'];
	Header("Location: " . BASEPAGE);
	exit();
}

if ( $action == "deal") {
	if (!Bereken_Score('dealer'))
	{
		Nieuwe_Kaart('dealer');
	}
	if ($_POST['bet'] > $_SESSION['blackjack']['player']['balance'])
	{
		Reset_Game();

		Header("Location: " . BASEPAGE);
		exit();
	}
	if (!isset($_SESSION['blackjack']['player']['i']))
		$_SESSION['blackjack']['player']['i']=0;
	if ($_SESSION['blackjack']['player']['i']<1)
	{
		$_SESSION['blackjack']['player']['bet'] = $_POST['bet'];
		Nieuwe_Kaart('player');
	}
	Nieuwe_Kaart('player');

	Header("Location: " . BASEPAGE);
	exit();
}

if ( $action == "stand") {
	Cards_For_Dealer();

	Header("Location: " . BASEPAGE);
	exit();
}

if ( $action == "emergencyreset") {
	Reset_Game();

	Header("Location: " . BASEPAGE);
	exit();
}

if ( $action == "endgame") {
	End_Game();

	Header("Location: " . BASEPAGE);
	exit();
}

if (Bereken_Score('player') > 21 && empty($_SESSION['blackjack']['pauze'])) {
	Cards_For_Dealer();
}

if (isset($_SESSION['blackjack']['winner'])) {
	if ($_SESSION['blackjack']['winner']=="dealer")
		$whowins = "Y O U &nbsp; L O S E";
	else if ($_SESSION['blackjack']['winner']=="player")
		$whowins = "Y O U &nbsp; W I N";
}

?>
<!-- <!doctype html> -->
<html>

<head>
	<title>BLACKJACK</title>
	<style>
	* {
		cursor			: default;
	}
	BODY, TABLE, INPUT {
		font-family		: Verdana, Arial;
		font-size		: 11px;
	}
	TD {
		border-right	: solid 2px #000;
		border-bottom	: solid 2px #000;
	}
	TD.gb {
		border			: none;
	}
	</style>
	<script>
	function Place_Bet(getal) {
		var max = <?php echo 500 < $_SESSION['blackjack']['player']['balance'] ? 500 : $_SESSION['blackjack']['player']['balance']; ?>

		if (getal == 0) {
			document.bet.bet.value = 0;
			document.bet.balance.value = max;
		}
		else if (getal == "alles") {
			document.bet.bet.value = max;
			document.bet.balance.value = <?php echo $_SESSION['blackjack']['player']['balance']; ?>-max;
		}
		else if ((document.bet.bet.value - getal*(-1)) <= max) {
			document.bet.bet.value -= getal*(-1);
			document.bet.balance.value -= getal;
		}
	}
	<?php echo (isset($_GET['refresh']))?"<script>\ntop.location='./';\n</script>\n":""; ?>
	</script>
	<script>
	if (top.location != this.location) top.location = this.location;
	</script>
</head>

<body style='margin:0px;overflow:auto;'>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%"><tr valign="middle"><td align="center" class="gb">

	<form name="bet" method="post" action="?action=deal">
		<table border="1" cellpadding="3" cellspacing="0" width="700" height="400" style="border-left: solid 2px black; border-top: solid 2px black">
			<tr height="50" valign="middle">
				<td colspan="3"><center><?php echo (!empty($_SESSION['blackjack']['pauze']))?"<font style='font-size:20px;'><b>$whowins":"";?><br></td>
			</tr>
			<tr height="30">
				<td><?php echo ($config['show_recent_score'] && Bereken_Score('player') && Bereken_Score('dealer'))?"<center><b style='border:solid 1px #777;'>&nbsp;".Bereken_Score('player')."&nbsp;</b>":""?><br></td>
				<td colspan="2"><center>Your balance: <input type="text" name="balance" style="border: none; font-weight: bold" value="<?php echo (empty($_SESSION['blackjack']['pauze']) && isset($_SESSION['blackjack']['player']['bet']))?$_SESSION['blackjack']['player']['balance']-$_SESSION['blackjack']['player']['bet']:$_SESSION['blackjack']['player']['balance']?>"></font></td>
			</tr>
			<tr height="110">
				<td rowspan="3" width="210">
					<table border="0" cellpadding="4" cellspacing="0" width="160" height="100%">
						<tr valign="middle">
							<td rowspan="2" class="gb"><center><img <?php echo (!Bereken_Score('player'))?"OnClick=\"Place_Bet('alles');\"":""?> style="cursor: pointer" src="./images/fiche_max.gif"></td>
							<td class="gb"><center><img <?php echo (!Bereken_Score('player'))?"OnClick=\"Place_Bet(5);\"":""?> style="cursor: pointer" src="./images/fiche_5.gif"></td>
						</tr>
						<tr valign="middle">
							<td class="gb"><center><img <?php echo (!Bereken_Score('player'))?"OnClick=\"Place_Bet(25);\"":""?> style="cursor: pointer" src="./images/fiche_25.gif"></td>
						</tr>
						<tr valign="middle">
							<td rowspan="2" class="gb"><center><img <?php echo (!Bereken_Score('player'))?"OnClick=\"Place_Bet(0);\"":""?> style="cursor: pointer" src="./images/fiche_0.gif"></td>
							<td class="gb"><center><img <?php echo (!Bereken_Score('player'))?"OnClick=\"Place_Bet(100);\"":""?> style="cursor: pointer" src="./images/fiche_100.gif"></td>
						</tr>
						<tr valign="middle">
							<td class="gb"><center><img <?php echo (!Bereken_Score('player'))?"OnClick=\"Place_Bet(500);\"":""?> style="cursor: pointer" src="./images/fiche_500.gif"></td>
						</tr>
					</table>
				</td>
				<td colspan="2">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr valign="middle">
							<td class="gb">
								<?php Laat_Kaarten_Zien('dealer') ?>
								<?php echo (isset($_SESSION['blackjack']['dealer']['i']) && $_SESSION['blackjack']['dealer']['i']<2 && $_SESSION['blackjack']['dealer']['i']>0) ? '<img src="./images/kaart.gif" border="0" height="96" width="71" />' : '' ?>
							</td>
							<td class="gb" align="right" style="font-size: 30px"><b><?php echo ($config['show_recent_score'])?Bereken_Score('dealer'):""?>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr height="110">
				<td colspan="2">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr valign="middle">
							<td class="gb"><?php Laat_Kaarten_Zien('player')?><br></td>
							<td align="right" style="font-size: 30px" class="gb"><b><?php echo ($config['show_recent_score'])?Bereken_Score('player'):""?>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr height="30">
				<td colspan="2">BET: <input type="text" name="bet" style="border: none; font-weight: bold" value="<?php echo (isset($_SESSION['blackjack']['player']['bet']) && $_SESSION['blackjack']['player']['bet'] && empty($_SESSION['blackjack']['pauze']))?$_SESSION['blackjack']['player']['bet']:0?>"></font><br></td>
			</tr>
			<tr height="35">
				<td width="32%" align="center">
					<?php echo (empty($_SESSION['blackjack']['pauze']))?"<input style='width:50%;' type=submit value=\"DEAL\">":""?><br>
				</td>
				<td width="34%" align="center">
					<?php echo (Bereken_Score('player') && empty($_SESSION['blackjack']['pauze']))?"<input style='width:50%;' type=button value=\"STAND\" OnClick=\"location='?action=stand'\">":""?><br>
				</td>
				<td width="34%" align="center">
					<?php echo (!empty($_SESSION['blackjack']['pauze']))?"<input style='width:50%;' type=button value=\"OK\" OnClick=\"location='?action=endgame';\">":""?><br>
				</td>
			</tr>
		</table>
	</form>

	</td></tr></table>
</body>

<pre><?php print_r($_SESSION['blackjack']) ?></pre>
