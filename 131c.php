<?php

require_once('inc.cls.cardgame.php');
require_once('inc.cls.pokertexasholdem.php');

$public = @$_GET['public'] ? explode(' ', $_GET['public']) : ['c2', 's2', 'dk', 'hk', 'd2'];
$hands = @$_GET['hands'] ? array_map(function($hand) {
	return explode(' ', $hand);
}, array_filter($_GET['hands'])) : [['ck', 'h6']];

?>
<form method="get" action>
	<input name="public" value="<?= implode(' ', $public) ?>" /> +
	(<a href="?public=c2+s2+dk+hk+d2&hands[]=ck+h6">preset 1</a>
	<a href="?public=h9+c6+c5+c7+s4&hands[]=h4+s8">preset 2</a>
	<a href="?public=d6+d4+d5+dj+d7&hands[]=hk+dk&hands[]=h6+d8">preset 3</a>)<br>
	<input name="hands[]" value="<?= implode(' ', (array) @$hands[0]) ?>" /> |
	<input name="hands[]" value="<?= implode(' ', (array) @$hands[1]) ?>" /> |
	<input name="hands[]" value="<?= implode(' ', (array) @$hands[2]) ?>" /> |
	<input name="hands[]" value="<?= implode(' ', (array) @$hands[3]) ?>" /> |
	<input type="submit" style="position: absolute; visibility: hidden" />
</form>
<pre><?php

$public = Card::named($public);
$hands = array_map([Card::class, 'named'], $hands);

echo implode(' ', $public) . "\n";

foreach ($hands as $hand) {
	$cards = array_merge($public, $hand);

	echo "<hr>" . implode(' ', $hand) . ' ';

	$score = PokerTexasHoldem::score($cards, $o);
	echo $score . ' (' . PokerTexasHoldem::readable_hand($score) . ")\n";

	echo '<details><summary>Details</summary>';
	echo 'one pair     ' . ( $o->one_pair() ? 'Y' : 'N' ) . "\n";
	echo 'two pair     ' . ( $o->two_pair() ? 'Y' : 'N' ) . "\n";
	echo '3 of a kind  ' . ( $o->three_of_a_kind() ? 'Y' : 'N' ) . "\n";
	echo 'straight     ' . ( $o->straight() ? 'Y' : 'N' ) . "\n";
	echo 'flush        ' . ( $o->flush() ? 'Y' : 'N' ) . "\n";
	echo 'full house   ' . ( $o->full_house() ? 'Y' : 'N' ) . "\n";
	echo '4 of a kind  ' . ( $o->four_of_a_kind() ? 'Y' : 'N' ) . "\n";
	echo "\n";
	print_r($o);
	echo '</details>';
}
