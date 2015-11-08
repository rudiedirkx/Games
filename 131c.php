<pre><?php

require_once('inc.cls.cardgame.php');
require_once('inc.cls.pokertexasholdem.php');

card::$__tostring = function($c) {
	return '<img title="' . $c->id . '" src="images/' . $c->suit . '_' . $c->short . '.gif" />';
};

if ( !isset($_GET['cards']) || count($arrCards = array_map('intval', explode(',', $_GET['cards']))) < 3 ) {
	$arrCards = array(1, 40, 25, 38, 14, 12, 31);
}

$arrCards = array_map(function($c) {
	return new Card($c);
}, $arrCards);

echo implode(' ', array_map('strval', $arrCards)) . "\n\n";

$hand = pokertexasholdem::score($arrCards, $o);
echo $hand . ' (' . pokertexasholdem::readable_hand($hand) . ")\n\n";

echo 'one pair     ' . ( $o->one_pair() ? 'Y' : 'N' ) . "\n";
echo 'two pair     ' . ( $o->two_pair() ? 'Y' : 'N' ) . "\n";
echo '3 of a kind  ' . ( $o->three_of_a_kind() ? 'Y' : 'N' ) . "\n";
echo 'straight     ' . ( $o->straight() ? 'Y' : 'N' ) . "\n";
echo 'flush        ' . ( $o->flush() ? 'Y' : 'N' ) . "\n";
echo 'full house   ' . ( $o->full_house() ? 'Y' : 'N' ) . "\n";
echo '4 of a kind  ' . ( $o->four_of_a_kind() ? 'Y' : 'N' ) . "\n";

echo "\n";
print_r($o);
