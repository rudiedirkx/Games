<?php

if ( isset($_GET['source']) ) {
	highlight_file(__FILE__);
	exit;
}

session_start();
define( "EOL", "\n" );
$iUtcStart = microtime(true);

require_once('inc.cls.json.php');
require_once('inc.cls.cardgame.php');
require_once('inc.cls.pokertexasholdem.php');

card::$__tostring = create_function('$c', 'return \'<img suit="\'.$c->suit.\'" value="\'.$c->pth.\'" src="images/\'.$c->suit.\'_\'.$c->short.\'.gif" />\';');

$objDeck = new Deck();
$objDeck->shuffle();

$iPlayers = isset($_GET['players']) ? min(8, max(1, (int)$_GET['players'])) : 8;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
<title>PHP Poker Texas Hold'em Test</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<style type="text/css">
body {
	font-family		: 'courier new';
	font-size		: 10pt;
}
table { border-collapse:collapse; }
td {
	font-size		: 6px;
	border			: solid 1px white;
}
ul.cards {
	padding:0;
	margin:0;
	width:150px;
	height:100px;
}
ul.cards li {
	padding : 0;
	display : inline;
	float : left;
	margin : 2px;
	list-style-type : none;
}
a.card {
	display:block;
	overflow:hidden;
	padding:0px;
	position:relative;
	text-decoration:none !important;
	width:71px;
	height:96px;
}
a.card span {
	background:#222;
	display:block;
	opacity:0.0;
	filter:alpha(opacity=0);
	padding:0;
	position:absolute;
	left:0px;
	top:0px;
	width:71px;
	height:96px;
}
a.card span.hidden {
	opacity:0.7;
	filter:alpha(opacity=70);
}
td#flop ul.cards {
	width:375px;
}
</style>
</head>

<body>
<div><a href="?source">-source-</a></div>
<div><a href="?players=<?php echo $iPlayers; ?>&spoil=1">-spoil-</a></div>
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

//$arrPublic = array(new Card(5), new Card(18), new Card(4), new Card(33), new Card(28));
//$arrPlayers[1] = array(new Card(13), new Card(13));

function toString($c) {
	return (string)$c;
}
function cardImgs($c) {
	return '<ul class="cards"><li><a class="card">'.implode('<span class="hidden"></span></a></li><li><a class="card">', array_map('toString', $c)).'<span class="hidden"></span></a></li></ul>';
}
function printSeat($s) {
	global $arrPlayers, $arrPublic, $arrHands;
	if ( isset($arrPlayers[$s]) ) {
		$szCards = cardImgs($arrPlayers[$s]);
		$arrHands[$s] = pokertexasholdem::score(array_merge($arrPublic, $arrPlayers[$s]));
		$szTitle = ' ('.pokertexasholdem::readable_hand($arrHands[$s]).' ('.$arrHands[$s].'))';
	}
	else {
		$szCards = '';	
		$szTitle = '';
	}
	echo '<td id="seat_'.$s.'" title="Seat '.$s.$szTitle.'" align="center" bgcolor="#cccccc">'.$szCards.'</td>'."\n";
}

$arrHands = array();

?>
<table border="0" cellpadding="10" cellspacing="0" width="100%">
	<tr valign="middle">
		<?php printSeat(5); ?>
		<?php printSeat(1); ?>
		<?php printSeat(6); ?>
	</tr>
	<tr valign="middle">
		<?php printSeat(4); ?>
		<td bgcolor="#cccccc" align="center" id="flop"><?php echo cardImgs($arrPublic); ?></td><!-- FLOP -->
		<?php printSeat(2); ?>
	</tr>
	<tr valign="middle">
		<?php printSeat(8); ?>
		<?php printSeat(3); ?>
		<?php printSeat(7); ?>
	</tr>
</table>
<?php

$fWinner = max($arrHands);
$arrWinners = array_keys($arrHands, $fWinner);
$arrFiveCards = pokertexasholdem::winnerCardsAndSuit($fWinner);

?>

<p id="readable_hand" align="center" onclick="hiliteCards();" style="cursor:pointer;padding:3px;background-color:#ddd;color:#ddd;">WINNER: <?php echo pokertexasholdem::readable_hand($fWinner).' ('.$fWinner.')'; ?></p>

<p><?php echo number_format(microtime(true)-$iUtcStart, 4); ?> sec</p>

<script type="text/javascript">
<!--//
function clone(obj) { if ( 'function' == typeof obj.clone ) { return obj.clone(); } if ( -1 != ['function', 'number', 'boolean', 'string', 'null'].indexOf(typeof obj) ) { return obj; } var o = {}; for ( x in obj ) { o[x] = obj[x].clone(); } return o; }
function hiliteCards() {
	$('readable_hand').style.color='black';
	var cards = <?php echo json::encode($arrFiveCards[0]); ?>, suit = <?php echo json::encode($arrFiveCards[1]); ?>;
	// Hilite public cards
	$$('#flop span').each(function(sp) {
		var img = sp.parentNode.getElements('img')[0], v = img.getAttribute('value'), s = img.getAttribute('suit');
		if ( 'undefined' != typeof cards[v] && 0 < cards[v] ) {
			if ( !suit || s === suit ) {
				cards[v]--;
				sp.className = '';
			}
		}
	});
	$A([<?php echo implode(',', $arrWinners); ?>]).each(function(s) {
		$('seat_'+s).style.backgroundColor = '#aaa';
		var wcards = clone(cards);
		$$('#seat_'+s+' span').each(function(sp) {
			var img = sp.parentNode.getElements('img')[0], v = img.getAttribute('value'), s = img.getAttribute('suit');
			if ( 'undefined' != typeof cards[v] && 0 < wcards[v] ) {
				if ( !suit || s === suit ) {
					wcards[v]--;
					sp.className = '';
				}
			}
		});
	});
}
<?php if ( !empty($_GET['spoil']) ) { echo "hiliteCards();\n"; } ?>
//-->
</script>
