<?php

// 3 columns (R, G, B)
// 2 fields per column (R, G, B = 00 or FF)
// 6 Fields:
//  00-XX-XX
//  11-XX-XX
//  XX-00-XX
//  XX-11-XX
//  XX-XX-00
//  XX-XX-11

// The array containing 0-15 in HEX
$hex = Array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');

// How many of every 16 colours do you want printed?
// 4 is recommended, 8 will take a LONG time to output, 16 is impossible
$NumOfSicsteen = 2;


function int2hex( $int )
{
	global $hex;

	$int = max( 0, min(255, $int) );

	$out1	= floor( $int / 16 );
	$out2	= $hex[$int%16];
	$out1	= $hex[$out1];
	$out	= $out1.$out2;

	return $out;
}

?>
<style>
BODY, TABLE
{
	font-family:	courier new;
	font-size:		10pt;
	text-align:		center;
}
</style>

<table border="0" cellpadding="0" cellspacing="0">
<tr><td>
<?php

$NotOfSicsteen = 16 / $NumOfSicsteen;

// Let's start with Field One (static RED 00 :: 00 XX XX)
$szColorPrefix = '00';
echo '<table border="0" cellpadding="0" cellspacing="0" width="120">';
for ( $i=0; $i<=16*$NumOfSicsteen; $i++ )
{
	for ( $j=0; $j<=16*$NumOfSicsteen; $j++ )
	{
		$bgc = '#' . $szColorPrefix . int2hex( $i*$NotOfSicsteen ) . int2hex( $j*$NotOfSicsteen );
		echo '<tr><td bgcolor="'.$bgc.'">' . $bgc . "</td></tr>";
	}
}
echo '</table>';

echo '</td><td>';

// Continue with Field Two (static RED ff :: ff XX XX)
$szColorPrefix = 'ff';
echo '<table border="0" cellpadding="0" cellspacing="0" width="120">';
for ( $i=0; $i<=16*$NumOfSicsteen; $i++ )
{
	for ( $j=0; $j<=16*$NumOfSicsteen; $j++ )
	{
		$bgc = '#' . $szColorPrefix . int2hex( $i*$NotOfSicsteen ) . int2hex( $j*$NotOfSicsteen );
		echo '<tr><td bgcolor="'.$bgc.'">' . $bgc . "</td></tr>";
	}
}
echo '</table>';

echo '</td><td>';

// Continue with Field Three (static GREEN 00 :: XX 00 XX)
$szColorPrefix = '00';
echo '<table border="0" cellpadding="0" cellspacing="0" width="120">';
for ( $i=0; $i<=16*$NumOfSicsteen; $i++ )
{
	for ( $j=0; $j<=16*$NumOfSicsteen; $j++ )
	{
		$bgc = '#' . int2hex( $i*$NotOfSicsteen ) . $szColorPrefix . int2hex( $j*$NotOfSicsteen );
		echo '<tr><td bgcolor="'.$bgc.'">' . $bgc . "</td></tr>";
	}
}
echo '</table>';

echo '</td><td>';

// Continue with Field Four (static GREEN ff :: XX ff XX)
$szColorPrefix = 'ff';
echo '<table border="0" cellpadding="0" cellspacing="0" width="120">';
for ( $i=0; $i<=16*$NumOfSicsteen; $i++ )
{
	for ( $j=0; $j<=16*$NumOfSicsteen; $j++ )
	{
		$bgc = '#' . int2hex( $i*$NotOfSicsteen ) . $szColorPrefix . int2hex( $j*$NotOfSicsteen );
		echo '<tr><td bgcolor="'.$bgc.'">' . $bgc . "</td></tr>";
	}
}
echo '</table>';

echo '</td><td>';

// Continue with Field Three (static BLUE 00 :: XX XX 00)
$szColorPrefix = '00';
echo '<table border="0" cellpadding="0" cellspacing="0" width="120">';
for ( $i=0; $i<=16*$NumOfSicsteen; $i++ )
{
	for ( $j=0; $j<=16*$NumOfSicsteen; $j++ )
	{
		$bgc = '#' . int2hex( $i*$NotOfSicsteen ) . int2hex( $j*$NotOfSicsteen ) . $szColorPrefix;
		echo '<tr><td bgcolor="'.$bgc.'">' . $bgc . "</td></tr>";
	}
}
echo '</table>';

echo '</td><td>';

// Continue with Field Four (static BLUE ff :: XX XX ff)
$szColorPrefix = 'ff';
echo '<table border="0" cellpadding="0" cellspacing="0" width="120">';
for ( $i=0; $i<=16*$NumOfSicsteen; $i++ )
{
	for ( $j=0; $j<=16*$NumOfSicsteen; $j++ )
	{
		$bgc = '#' . int2hex( $i*$NotOfSicsteen ) . int2hex( $j*$NotOfSicsteen ) . $szColorPrefix;
		echo '<tr><td bgcolor="'.$bgc.'">' . $bgc . "</td></tr>";
	}
}
echo '</table>';

echo '</td></tr>';
echo '</table>';



?>