<pre><?php

require_once('inc.cls.cardgame.php');
require_once('inc.cls.pokertexasholdem.php');
Card::$__tostring = create_function('$c', 'return strtoupper($c->short)." of ".$c->suit;');

$fStart = microtime(true);

$iRounds = isset($_GET['rounds']) ? max(10, (int)$_GET['rounds']) : 1500;

$objDeck = new Deck;

$arrPlayers = array(
	array(new Card(26), new Card(0)),
	array(new Card(40), new Card(45)),
);
$arrWinner = array(0, 0);

for ( $i=0; $i<$iRounds; $i++ ) {

	$objDeck->replenish();
	$arrPublic = array_slice($objDeck->cards, 0, 5);

//echo "[".implode(' + ', $arrPublic)."]\n\n";

	$arrPublic[5] = $arrPlayers[0][0];
	$arrPublic[6] = $arrPlayers[0][1];
	$a = pokertexasholdem::score($arrPublic);

	$arrPublic[5] = $arrPlayers[1][0];
	$arrPublic[6] = $arrPlayers[1][1];
	$b = pokertexasholdem::score($arrPublic);

	$arrWinner[ $a > $b ? 0 : 1 ]++;

}

print_r($arrWinner);

echo "\n".implode(' + ', $arrPlayers[0]).' vs '.implode(' + ', $arrPlayers[1])."\n";
echo "\n".( round($arrWinner[0]/$iRounds*100, 3) ).' % vs '.( round($arrWinner[1]/$iRounds*100, 3) ).' %';

$fParseTime = microtime(true) - $fStart;
echo "\n\n".$iRounds." rounds\n\n[".number_format($fParseTime, 4).' s]';


