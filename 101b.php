<?php
// BLACKJACK COUNTING CARDS

require_once 'inc.cls.cardgame.php';

Card::$__tostring = function($objCard) use (&$iCount, &$arrCardPoints) {
	return '<img class="future" src="/images/' . $objCard->suit . '_' . $objCard->short . '.gif" data-pt="' . $arrCardPoints[$objCard->short] . '" count="' . $iCount . '" title="So far: ' . $iCount . '" />';
};
$arrCardPoints = array('2' => 1, '3' => 1, '4' => 1, '5' => 1, '6' => 1, '7' => 0, '8' => 0, '9' => 0, '10' => -1, 'j' => -1, 'q' => -1, 'k' => -1, 'a' => -1);

$g_fStartUtc = microtime(1);

$iDecks = 6;

$deck = new Deck();
while ( count($deck->cards) < $iDecks*52 ) {
	$deck->add_deck(new Deck());
}
$deck->shuffle();

echo "<p>$iDecks decks, randomly ordered:</p>\n";

$iCount = 0;
foreach ( $deck->cards AS $i => $objCard ) {
	$iCount += $arrCardPoints[$objCard->short];
	echo $objCard . "\n";
}

?>

<style>
img.future { display: none; }
img.present { display: inline; visibility: visible; }
img.past { visibility: hidden; }

body.show-past img.past { visibility: visible; }
body.show-all img { display: inline; visibility: visible; }

body.show-past button.past, body.show-all button.all { font-weight: bold; }
</style>

<div style="position: fixed; top: 10px; right: 10px">
	<button class="next" onclick="next()">next</button>
	<button class="past" onclick="document.body.classList.toggle('show-past')">show past</button>
	<button class="all" onclick="document.body.classList.toggle('show-all')">show all</button>
</div>

<script>
var arrCards = document.getElementsByTagName('img');
var objNext = document.querySelector('button.next');

var iCards = <?= $iDecks*52 ?>;
var iInterval = 2000;
var iNext = 0;
var iShowCards = 0;
var iIntervalTimer = null;

function next() {
	if ( iShowCards > 0 && (iNext - iShowCards) >= 0 ) {
		arrCards[iNext - iShowCards].classList.add('past');
	}

	if ( iCards <= iNext ) {
		clearInterval(iIntervalTimer);
		setTimeout("arrCards[iCards - 2].classList.add('past')", iInterval);
		return false;
	}

	objNext.title = 'Count = ' + arrCards[iNext].getAttribute('count');

	arrCards[iNext++].classList.add('present');
}

function startRealtimeTest() {
	next();
	iShowCards = 3;
	iIntervalTimer = setInterval(next, iInterval);
}

startRealtimeTest();
</script>

<br />

<?= number_format(microtime(1) - $g_fStartUtc, 4) ?>
