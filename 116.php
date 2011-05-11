<?php

if ( isset($_GET['source']) ) {
	highlight_file(__FILE__);
	exit;
}

error_reporting(2047);

function ThousandsToString($number)
{
	global	$arrNumbersToTwenty,
			$arrTensToHundred,
			$arrThousands;

	if (!is_numeric($number) || $number<0 || $number>999)
	{
		trigger_error("ERROR :: $number is not a valid number for ".__FUNCTION__);
	}
	$number = (INT)$number;
	$splitted = str_split($number, 1);
	if (0 == $number)
	{
		return '';
	}
	if ($number <= 20)
	{
		return $arrNumbersToTwenty[$number];
	}
	if ($number < 100)
	{
		return $arrTensToHundred[$splitted[0]].(($splitted[1]>0) ? '-'.$arrNumbersToTwenty[$splitted[1]] : '');
	}
	if (0 == $number%100)
	{
		return $arrNumbersToTwenty[($number/100)].' '.$arrTensToHundred[10];
	}
	if ($number > 100)
	{
		$p2 = $number-floor($number/100)*100;
		return $arrNumbersToTwenty[floor($number/100)].' '.$arrTensToHundred[10].(($p2<10) ? ' '.$arrTensToHundred[11].' ' : ' ').ThousandsToString($p2);
	}
}

function NumberToString( $iNumber, $delim = ' ' )
{
	// If the number is not a number, kill it!
	if (!is_numeric($iNumber))
	{
		trigger_error("Wrong argument passed in ".__FUNCTION__."! <b>Integer</b> needed, <b>".gettype($iNumber)."</b> passed", E_USER_WARNING);
	}

	global	$arrNumbersToTwenty,
			$arrTensToHundred,
			$arrThousands;

	//! Get this number in packages of 3, using number_format( ) and explode( )
	$arrTriples = explode(',',number_format($iNumber,0,'',','));
	$key = -1;
	$parts = Array();
	//! Walk this array of packages in reverse order
	for ($i=count($arrTriples)-1;$i>=0;$i--)
	{
		//! If this is the final (lowest value) package: no postfix (thousand, million, etc)
		$postfix = ($i == count($arrTriples)-1) ? NULL : $arrThousands[$key];
		//! The number as integer (can't be higer than 999 && can't be over 3 chars)
		$thisone['number'] = $arrTriples[$i];
		//! If it's 0, dont add a postfix: 'zero million' or 'zero thousand' is nonsense
		if (0 <= $thisone['number'])
		{
			//! This is where the INTnumber gets transformed into STRnumber using ThousandsToString( )
			$thisone['strNumber'] = ThousandsToString($arrTriples[$i]);
			//! Add the postfix. This can be NULL if this is the first (lowest) package
			$thisone['postfix'] = $postfix;

			//! Add the TMP array to the $parts array
			$parts[] = $thisone;
		}
		$key++;
	}

	//! Reverse the Array so the lowest package is first
	$parts = array_reverse($parts);

	//! Create array for strings
	$retval = Array();

	//! Walk array $parts to add strNumber (and a postfix if its number is > 0)
	for ($i=0;$i<count($parts);$i++)
	{
		$retval[] = $parts[$i]['strNumber'].(((INT)$parts[$i]['number']>0) ? ' '.$parts[$i]['postfix'] : '');
	}

	//! Return$szFields)
	return preg_replace("/ {2,}/", " ", implode($delim, $retval));
}



/**
 * 
 * CONFIGS
 * 
 * This part is language specific
 * Language-structurally, this script only works for english though :)
 * 
 **/
$arrNumbersToTwenty	= Array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten", "eleven", "twelve", "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eightteen", "nineteen", "twenty");
$arrTensToHundred	= Array("zero", "ten", "twenty", "thirty", "fourty", "fifty", "sixty", "seventy", "eighty", "ninety", "hundred", "and");
$arrThousands		= Array("thousand", "million", "billion", "trillion", "quadrillion", "quintillion");


/**
 * 
 * THE PAGE
 * 
 **/
$max_for_func = pow(1000,count($arrThousands))-1;
$getal = isset($_GET['getal']) ? max(0, (int)$_GET['getal']) : $max_for_func;

function getResult( $getal ) {
	return number_format($getal, 0, '', ',').' : '.NumberToString($getal);
}

if ( isset($_GET['ajax']) ) {
	exit(getResult($getal));
}

?>
<body>

<p><a href="?source">source</a></p>

<pre>
<?php
echo '<span id="result">'.getResult($getal).'</span>';

echo "<hr color=\"red\" />\n";

echo '<form action="">';
echo '<input type="text" name="getal" value="'.$getal.'" id="hetGetal"> <input type="submit" value="Talk">';
echo '</form>';

?>
<script src="/js/mootools_1_11.js"></script>
<script>
$('hetGetal').addEvent('directchange', function() {
	new Ajax('?ajax&getal='+this.value, {
		onComplete: function(t) {
			$('result').update(t);
		}
	}).request();
});
</script>
<?php

echo "<hr color=\"red\" />\n\n\n";

echo '162 : '.ThousandsToString(162);

echo "\n\n";

echo 'max_number for this function: '.$max_for_func;


