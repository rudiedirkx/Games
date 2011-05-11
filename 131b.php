<?php

require_once('inc.cls.cardgame.php');

card::$__tostring = create_function('$c', 'return \'<img src="images/\'.$c->suit.\'.gif" /> \'.strtoupper($c->short);');

$objDeck = new Deck();
$objDeck->shuffle();

$iPlayers = 12;
define('LOWEST_WORTHY_HAND', 6);

$arrPublic = $arrPlayers = array();
for ( $i=0; $i<$iPlayers; $i++ ) {
	$arrPlayers[$i] = array();
	array_push($arrPlayers[$i], $objDeck->next());
	array_push($arrPlayers[$i], $objDeck->next());
}
while ( 5 > count($arrPublic) ) {
	array_push($arrPublic, $objDeck->next());
}

$fUtcStart = microtime(true);

/* test *
$arrPublic = array(
	new Card(8),
	new Card(0),
	new Card(3),
	new Card(7),
	new Card(6),
);
$arrPlayers[0] = array(
	new Card(5),
	new Card(4),
);
/* test */

require_once('inc.cls.pokertexasholdem.php');

?>
<html>

<head>
<style type="text/css">
body, table {
	background-color: #444;
	font-family		: verdana, arial;
	font-size		: 10px;
	color			: white;
}
</style>
</head>

<body>
<?php

$bNothingWorthy = true;
$iMaxHand = 0;
$arrHands = array();
echo '<table border="0" cellpadding="2" cellspacing="1">';
foreach ( $arrPlayers AS $k => $arrPlayer ) {
	echo '<tr>';
	if ( 0 == $k ) {
		echo '<td rowspan="'.count($arrPlayers).'">'.implode(', ', $arrPublic).'</td>';
		echo '<td rowspan="'.count($arrPlayers).'">&nbsp;+&nbsp;</td>';
	}
	$fHand = pokertexasholdem::score(array_merge($arrPublic, $arrPlayer));
	$arrHands[] = $fHand;
	if ( (float)$fHand > $iMaxHand ) {
		$iMaxHand = (float)$fHand;
	}
	if ( (float)LOWEST_WORTHY_HAND <= (float)$fHand ) {
		$bNothingWorthy = false;
		$szHand = '<b class="worthy h'.str_replace('.', '_', (float)$fHand).'">'.$fHand.'</b>';
	}
	else {
		$szHand = $fHand;
	}
	echo '<td>'.implode(', ', $arrPlayer).'</td>';
	echo '<td>&nbsp;=&nbsp;</td>';
	echo '<td>'.$szHand.'</td>';
	echo '<td>&nbsp;=&nbsp;</td>';
	echo '<td>'.pokertexasholdem::readable_hand($fHand).'</td></tr>';
}
echo '<tr><td colspan="7" align="center"><b'.( $bNothingWorthy ? '' : ' style="color:yellow;"' ).'>Winner</b>: '.pokertexasholdem::readable_hand($m=max($arrHands)).' ('.$m.')</td></tr>';
echo '</table>';

?><style type="text/css">.worthy{color:red;}table tr td b.h<?php echo str_replace('.', '_', (float)$m); ?>{color:yellow;}</style><?php

if ( $bNothingWorthy ) {
	echo '<meta http-equiv="refresh" content="0" />';
}

echo '<p>'.number_format(($fTime=microtime(true)-$fUtcStart), 4).'</p>';

?>
</body>

<title><?php echo $iMaxHand; ?></title>

</html>
