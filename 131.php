<?php

$iUtcStart = microtime(true);

require 'inc.cls.cardgame.php';
require 'inc.cls.pokertexasholdem.php';

card::$__tostring = function($c) {
	return '<img suit="' . $c->suit . '" src="images/' . $c->suit . '_' . $c->short . '.gif" />';
};

$objDeck = new Deck();
$objDeck->shuffle();

$iPlayers = isset($_GET['players']) ? min(8, max(1, (int)$_GET['players'])) : 8;

?>
<!doctype html>
<html>

<head>
<title>PHP Poker Texas Hold'em Test</title>
<style>
table {
	border-collapse: collapse;
	width: 100%;
}
td {
	border: solid 2px white;
	padding: 10px;
}
.seat,
.flop {
	 text-align: center;
	 background-color: #ccc;
}
.seat.winner {
	background-color: #aaa;
}
a.card {
	display: inline-block;
	overflow: hidden;
	padding: 0px;
	position: relative;
	text-decoration: none !important;
	width: 71px;
	height: 96px;
}
a.card + a.card {
	margin-left: .6em;
}
a.card span {
	background: #222;
	display: block;
	opacity: 0.0;
	padding: 0;
	position: absolute;
	left: 0px;
	top: 0px;
	width: 71px;
	height: 96px;
}
a.card span.hidden {
	opacity: 0.7;
}
#readable_hand {
	text-align: center;
	padding: 3px;
	background-color: #ddd;
}

.show-winners .seat.winner .card.winner .hidden,
.show-winners .flop .card.winner .hidden {
	display: none;
}
</style>
</head>

<body class="show-winners">

<?php

$arrPublic = $arrPlayers = array();
for ( $i=1; $i<=$iPlayers; $i++ ) {
	$arrPlayers[$i] = array();
	array_push($arrPlayers[$i], $objDeck->next());
	array_push($arrPlayers[$i], $objDeck->next());
}
while ( 5 > count($arrPublic) ) {
	array_push($arrPublic, $objDeck->next());
}

/**/
$arrPublic = Card::named(['sj', 'h10', 'c9', 'h8', 'c7']);
$arrPlayers[1] = null;
$arrPlayers[2] = null;
$arrPlayers[3] = null;
$arrPlayers[4] = null;
$arrPlayers[5] = Card::named(['hq', 'c10']);
$arrPlayers[6] = Card::named(['h3', 'd7']);
$arrPlayers[7] = Card::named(['d3', 'c8']);
$arrPlayers[8] = null;//Card::named(['c6', 'c5']);
/**
$arrPublic = Card::named(['h9', 'c6', 'c5', 'c7', 's4']);
$arrPlayers[1] = Card::named(['h2', 'h3']);
$arrPlayers[2] = Card::named(['d6', 'ca']);
$arrPlayers[3] = Card::named(['sa', 'da']);
$arrPlayers[4] = Card::named(['s9', 's3']);
$arrPlayers[5] = Card::named(['d7', 'c10']);
$arrPlayers[6] = Card::named(['s2', 'ck']);
$arrPlayers[7] = Card::named(['sk', 'c4']);
$arrPlayers[8] = Card::named(['h4', 's8']);
/**/

$arrHands = array();
foreach (array_filter($arrPlayers) as $s => $cards) {
	$arrHands[$s] = PokerTexasHoldem::score(array_merge($arrPublic, $cards));
}

$fWinner = max($arrHands);
$arrWinners = array_keys($arrHands, $fWinner);

list($arrWinnerCards, $szWinnerSuit) = PokerTexasHoldem::winnerCardsAndSuit($fWinner);
$arrOriginalWinnerCards = $arrWinnerCards;
$szFlop = printPublic();

?>
<table>
	<tr valign="middle">
		<?php printSeat(1); ?>
		<?php printSeat(2); ?>
		<?php printSeat(3); ?>
	</tr>
	<tr valign="middle">
		<?php printSeat(8); ?>
		<?= $szFlop ?>
		<?php printSeat(4); ?>
	</tr>
	<tr valign="middle">
		<?php printSeat(7); ?>
		<?php printSeat(6); ?>
		<?php printSeat(5); ?>
	</tr>
</table>
<?php

?>

<p id="readable_hand">WINNER: <?= PokerTexasHoldem::readable_hand($fWinner) . ' (' . $fWinner . ')' ?></p>

<p><?= number_format(microtime(true)-$iUtcStart, 4) ?> sec</p>

<!-- <?php print_r($szWinnerSuit); ?> -->
<!-- <?php print_r($arrOriginalWinnerCards); ?> -->
<!-- <?php print_r($arrPlayers); ?> -->

<?php

function cardImgs($cards, &$arrWinnerCards) {
	global $szWinnerSuit;
	return implode(array_map(function($card) use (&$arrWinnerCards, $szWinnerSuit) {
		$szClass = !empty($arrWinnerCards[$card->pth]) && (!$szWinnerSuit || $card->suit == $szWinnerSuit) ? 'winner' : '';
		$szClass and !empty($arrWinnerCards[$card->pth]) and $arrWinnerCards[$card->pth]--;
		return '<a class="card ' . $szClass . '">' . $card . '<span class="hidden"></span></a>';
	}, $cards));
}

function printPublic() {
	global $arrPublic, $arrWinnerCards;
	return '<td class="flop">' . cardImgs($arrPublic, $arrWinnerCards) . '</td>';
}

function printSeat($s) {
	global $arrPlayers, $arrHands, $arrWinners, $arrWinnerCards;

	if ( isset($arrPlayers[$s]) ) {
		$arrWinnerCardsCopy = array_slice($arrWinnerCards, 0, 99, true);
		$szCards = cardImgs($arrPlayers[$s], $arrWinnerCardsCopy);
		$szTitle = ' (' . PokerTexasHoldem::readable_hand($arrHands[$s]) . ' (' . $arrHands[$s] . '))';
		$szClass = in_array($s, $arrWinners) ? 'winner' : '';
	}
	else {
		$szCards = '';
		$szTitle = '';
		$szClass = '';
	}

	echo '<td class="seat ' . $szClass . '" title="Seat ' . $s . $szTitle . '">' . $szCards . '</td>' . "\n";
}
