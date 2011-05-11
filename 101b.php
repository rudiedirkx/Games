<?php
// BLACKJACK

require_once('inc.cls.cardgame.php');

card::$__tostring = create_function('$c', 'return \'<img src="/images/\'.$c->suit.\'_\'.$c->short.\'.gif" />\';');
$arrCardPoints = array('a' => -1, '2' => 1, '3' => 1, '4' => 1, '5' => 1, '6' => 1, '7' => 0, '8' => 0, '9' => 0, '10' => -1, 'j' => -1, 'q' => -1, 'k' => -1);


$g_fStartUtc = microtime(true);

$iDecks = 6;

$deck = new Deck();
while ( count($deck->cards) < $iDecks*52 ) {
	$deck->add_deck(new Deck());
}
$deck->shuffle();

echo 'Six decks, randomly ordered:<br />';

$iCount = 0;
foreach ( $deck->cards AS $i => $objCard ) {
	$iCount += $arrCardPoints[$objCard->short];
	echo '<img src="/images/'.$objCard->suit.'_'.$objCard->short.'.gif" style="display:none;" pt="'.$arrCardPoints[$objCard->short].'" count="'.$iCount.'" title="'.$iCount.'" />'."\n";
	if ( 0 == ($i+1)%13 && count($deck->cards) > $i+1 ) {
		echo "<br />\n";
	}
}

?>
<div style="position:absolute;top:10px;right:10px;">
	<input type="button" value="next" onclick="next();" />
	<input type="button" value="all" onclick="all();" />
	<input type="button" value="hide all" onclick="all('none');" />
</div>

<script type="text/javascript">
<!--//
var iInterval = 2500, iNext = 0, arrCards = document.getElementsByTagName('img'), iShowCards = 0, next = function() {
	if ( 0 < iShowCards && 0 <= iNext-iShowCards ) {
		arrCards[iNext-iShowCards].style.visibility = 'hidden';
	}
	if ( <?php echo (int)$iDecks*52; ?> <= iNext ) {
		clearInterval(iIntervalTimer);
		setTimeout('arrCards[<?php echo (int)$iDecks*52-2; ?>].style.visibility = \'hidden\';', iInterval);
		setTimeout('all(\'none\');', 2*iInterval);
		return false;
	}
	objButton.title = 'Count = ' + arrCards[iNext].getAttribute('count');
	arrCards[iNext++].style.display = '';
	return false;
}, objButton = document.getElementsByTagName('input')[0], all = function(d) {
	var d = d ? d : '';
	for ( var iNext=0; iNext<arrCards.length; iNext++ ) {
		arrCards[iNext].style.display = d;
	}
	clearInterval(iIntervalTimer);
	return false;
}, iIntervalTimer = null, startRealtimeTest = function() {
	next();
	iShowCards = 3;
	iIntervalTimer = setInterval(next, iInterval);
};

// Real-time test?
startRealtimeTest();
//-->
</script>
<?php

exit('<br />'.number_format(microtime(true)-$g_fStartUtc, 4));





session_start();
require_once('connect.php');
error_reporting(2095);





?>
