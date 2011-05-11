<?php

$utcStart = microtime( TRUE );
include_once("inc.cls.poker_class.php");

session_start( );

define( "EOL", "\n" );

// 11 = J
// 12 = Q
// 13 = K
// 14 = A


if ( 1 OR isset($_GET['deal']) )
{
	while ( empty($used_randoms) || 7 > count($used_randoms) )
	{
		Go_Random(13*4);
	}

	$_cards = Array( );
	foreach ( $used_randoms AS $card )
	{
		$_cards[] = $STATIC['cards'][$card];
	}
}




/** INPUT **/
/** PARAM: ARRAY( CARD, CARD, ETC ) **/
$USER['cards'] = $_cards;
// $USER['cards'] = array ( 0 => 'c.14', 1 => 'd.4', 2 => 's.5', 3 => 'h.6', 4 => 'c.3', 5 => 'd.2', 6 => 'c.5', );
// $USER['cards'] = array ( 0 => 'd.13', 1 => 'd.9', 2 => 'd.10', 3 => 'd.4', 4 => 'c.6', 5 => 'd.11', 6 => 'd.12', );
$tmp_cards = var_export( $USER['cards'], TRUE );
echo "<br/>\n";


$voor_rules_van	= Array("J","Q","K","A");		// niet veranderen
$voor_rekenen	= Array(11,12,13,14);			// idem

$cards = Array( );
foreach ( $USER['cards'] AS $info )
{
	$kleur = substr($info, 0, 1);
	$waarde = substr($info, 2);
	$waarde = str_replace($voor_rekenen, $voor_rules_van, $waarde);
	$cards[] = '<b><img src="images/'.$STATIC['kleuren'][$info['kleur']].'.gif"> '.$waarde.'</b>';
}

$hands = return_hand($USER['cards']);

if ( $hands >= 4 )
{
	$_SESSION['mpp']['hihand_cards'] = $tmp_cards;
}

// unset($_SESSION['mpp']);
$_SESSION['mpp']['num_hands']++;
if ( empty($_SESSION['mpp']['hihand']) || $hands > $_SESSION['mpp']['hihand'] )
{
	$_SESSION['mpp']['hihand'] = $hands;
}

/*
if ( 7 > count($hands) )
{
	// Go( );
	echo "-- NIETS!!!<br/>";
	echo '<font style="font-family:Verdana;font-size:12px;">' . implode( " &nbsp; &nbsp; ", $cards ) . "</font><br/>";
}
else
{
*/
	// $old_utc = $_SESSION['mpp']['utc'];
	// $_SESSION['mpp']['utc'] = microtime( TRUE );

	echo '<font style="font-family:Verdana;font-size:12px;">' . implode( " &nbsp; &nbsp; ", $cards ) . "</font><br/>";

	echo '<pre>' . EOL;
	print_r( $hands );
	echo EOL . make_readable_hand($hands);
	echo EOL . '</pre>';

	if ( isset($_SESSION['mpp']['hihand_cards']) ) echo $_SESSION['mpp']['hihand_cards'];

	echo "Hi-est Hand: ".$_SESSION['mpp']['hihand']." (in ".$_SESSION['mpp']['num_hands'].")<br/>\n";

	$utcEnd = microtime( TRUE );
	echo "Parsetime: " . number_format($utcEnd-$utcStart, 4, '.', ',') . " seconds";
	// die;
/*
}
*/


$num = "37.124334d";
echo "<hr/>\n";
echo $num . "<br/>\n";
echo floor($num).'.'.intval(substr($num,2)) . "<br/>\n";
echo my_intval( $num );

function my_intval( $num )
{
	return floor($num).'.'.intval(substr($num,strlen(floor($num))+1));
}

function Go_Random( $max )
{
	global $used_randoms;

	$id = rand(1,$max);
	if (isset($used_randoms) && is_array($used_randoms) && in_array($id,$used_randoms))
		Go_Random($max);
	else
		$used_randoms[] = $id;
	return $id;

} // END Go_Random( )

function Go( )
{
	Header( "Location: " . basename($_SERVER['SCRIPT_NAME']) );
	exit;

} // END Go( )


?>