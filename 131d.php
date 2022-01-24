<pre><?php

require 'inc.cls.cardgame.php';
require 'inc.cls.pokertexasholdem.php';

Card::$__tostring = function ($c) {
	return '<img suit="' . $c->suit . '" src="images/' . $c->suit . '_' . $c->short . '.gif" />';
};

$fStart = microtime(true);

$iRounds = isset($_GET['rounds']) ? max(10, (int)$_GET['rounds']) : 10000;

echo $iRounds . " rounds\n\n";

$objDeck = new Deck;

$arrPlayers = array(
	array(new Card(26), new Card(0)),
	array(new Card(40), new Card(45)),
);
$arrWinner = array(0, 0);

for ( $i=0; $i<$iRounds; $i++ ) {

	$objDeck->replenish();
	$arrPublic = array_slice($objDeck->cards, 0, 5);

	$arrPublic[5] = $arrPlayers[0][0];
	$arrPublic[6] = $arrPlayers[0][1];
	$a = PokerTexasHoldem::score($arrPublic);

	$arrPublic[5] = $arrPlayers[1][0];
	$arrPublic[6] = $arrPlayers[1][1];
	$b = PokerTexasHoldem::score($arrPublic);

	$arrWinner[ $a > $b ? 0 : 1 ]++;

}

echo implode(' ', $arrPlayers[0]) . ' vs ' . implode(' ', $arrPlayers[1]) . "\n\n";
echo $arrWinner[0] . ' (' . round($arrWinner[0]/$iRounds*100, 2) . ' %) vs ' . $arrWinner[1] . ' (' . round($arrWinner[1]/$iRounds*100, 2) . " %)\n\n";

$fParseTime = microtime(true) - $fStart;
echo number_format($fParseTime, 4) . ' s';
