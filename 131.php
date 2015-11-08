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

$arrHands = array();
foreach ($arrPlayers as $s => $cards) {
	$arrHands[$s] = pokertexasholdem::score(array_merge($arrPublic, $cards));
}

$fWinner = max($arrHands);
$arrWinners = array_keys($arrHands, $fWinner);

list($arrWinnerCards, $szWinnerSuit) = pokertexasholdem::winnerCardsAndSuit($fWinner);

?>
<table>
	<tr valign="middle">
		<?php printSeat(5); ?>
		<?php printSeat(1); ?>
		<?php printSeat(6); ?>
	</tr>
	<tr valign="middle">
		<?php printSeat(4); ?>
		<td class="flop"><?= cardImgs($arrPublic) ?></td>
		<?php printSeat(2); ?>
	</tr>
	<tr valign="middle">
		<?php printSeat(8); ?>
		<?php printSeat(3); ?>
		<?php printSeat(7); ?>
	</tr>
</table>
<?php

?>

<p id="readable_hand">WINNER: <?= pokertexasholdem::readable_hand($fWinner) . ' (' . $fWinner . ')' ?></p>

<p><?= number_format(microtime(true)-$iUtcStart, 4) ?> sec</p>

<!-- <?php print_r($arrWinnerCards); ?> -->
<!-- <?php print_r($arrPlayers); ?> -->

<?php

function cardImgs($cards) {
	global $arrWinnerCards, $szWinnerSuit;
	$arrHandWinnerCards = $arrWinnerCards;
	return implode(array_map(function($card) use (&$arrHandWinnerCards, $szWinnerSuit) {
		$szClass = !empty($arrHandWinnerCards[$card->pth]) && (!$szWinnerSuit || $card->suit == $szWinnerSuit) ? 'winner' : '';
		empty($arrHandWinnerCards[$card->pth]) or $arrHandWinnerCards[$card->pth]--;
		return '<a class="card ' . $szClass . '">' . $card . '<span class="hidden"></span></a>';
	}, $cards));
}

function printSeat($s) {
	global $arrPlayers, $arrPublic, $arrHands, $arrWinners;

	if ( isset($arrPlayers[$s]) ) {
		$szCards = cardImgs($arrPlayers[$s]);
		$szTitle = ' (' . pokertexasholdem::readable_hand($arrHands[$s]) . ' (' . $arrHands[$s] . '))';
		$szClass = in_array($s, $arrWinners) ? 'winner' : '';
	}
	else {
		$szCards = '';
		$szTitle = '';
		$szClass = '';
	}

	echo '<td class="seat ' . $szClass . '" title="Seat ' . $s . $szTitle . '">' . $szCards . '</td>' . "\n";
}
