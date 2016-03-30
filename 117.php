<?php
// QUIZ

$g_arrQuizes = array(
	array(
		'name' => 'Quiz 1',
		//		vraag													antwoorden...................		goede antwoord
		array( '1. Hoevaak in deze Quiz is antwoord b. het goede?',		'3',	'2',	'1',	'5',		'c' ),
		array( '2. Wat is het antwoord op vraag 4?',					'c',	'b',	'd',	'a',		'd' ),
		array( '3. Wat is het antwoord op vraag 3?',					'd',	'b',	'a',	'c',		'b' ),
		array( '4. Wat is het antwoord op vraag 5?',					'c',	'd',	'a',	'b',		'a' ),
		array( '5. Wat is het antwoord op vraag 2?',					'b',	'a',	'd',	'c',		'c' ),
		array( '6. Hoevaak in deze Quiz is antwoord c. het goede?',		'1',	'3',	'4',	'2',		'd' ),
		array( '7. Wat is de abs waarde van -.5 tot de macht -1?',		'-1',	'-.5',	'.5',	'2',		'd' ),
		array( '8. Tot welke macht moet -1 geheven worden voor -1?',	'3',	'4',	'10',	'2',		'a' ),
	),
	array(
		'name' => 'MeesterQizz',
		//		vraag													antwoorden...................		goede antwoord
		array( '1. Hoevaak in deze Quiz is antwoord b. het goede?',		'2',	'4',	'3',	'5',		'a' ),
		array( '2. Wat is het antwoord op vraag 3?',					'd',	'b',	'a',	'c',		'b' ),
		array( '3. Wat is het antwoord op vraag 5?',					'a',	'c',	'd',	'b',		'b' ),
		array( '4. Wat is het antwoord op vraag 4?',					'd',	'a',	'c',	'b',		'c' ),
		array( '5. Wat is het antwoord op vraag 3?',					'd',	'c',	'b',	'a',		'c' ),
		array( '6. Wat is het antwoord op vraag 7?',					'd',	'c',	'b',	'a',		'd' ),
		array( '7. Hoevaak in deze Quiz is antwoord d. het goede?',		'1',	'2',	'3',	'4',		'a' ),
		array( '8. Wat is het antwoord op vraag 2?',					'b',	'a',	'd',	'c',		'a' )
	),
);

if ( isset($_POST['answers'], $_POST['quiz']) && is_array($_POST['answers']) )
{
	$arrQuiz = $g_arrQuizes[$_POST['quiz']];
	unset($arrQuiz['name']);

	$iWrong = 0;
	$i = 0;
	foreach ( $arrQuiz AS $arrQuestion ) {
		$szAnswer = array_pop($arrQuestion);
		$szUserAnswer = isset($_POST['answers'][$i]) ? $_POST['answers'][$i] : '';
		if ( $szUserAnswer != $szAnswer ) {
			exit("Wrong. Try again.");
			$iWrong++;
		}
		$i++;
	}

	exit("Yes! That's the one.");
}

$iQuiz = isset($_GET['quiz'], $g_arrQuizes[$_GET['quiz']]) ? $_GET['quiz'] : 0;
$arrQuiz = $g_arrQuizes[$iQuiz];
$abcd = array('a','b','c','d','e','f','g','h');
$perregel = 6;

?>
<html>

<head>
<title><?php echo $arrQuiz['name']; ?></title>
<style>
label {
	display		: block;
	width		: 100%;
}
</style>
</head>

<body onload="document.forms[0].reset();">
<p><select onchange="if(''!=this.value&&<?php echo $iQuiz; ?>!=this.value){document.location='?quiz='+this.value;}">
	<option value="">-- Select a quiz!</option>
<?php foreach ( $g_arrQuizes AS $iQ => $arrQ ) { echo '	<option value="'.$iQ.'">'.$arrQ['name'].'</option>'."\n"; } ?>
</select></p>

<form method="post" action="?" onsubmit="return postForm(this,function(a){w=parseInt(a.responseText);if(0<w){alert('Not all your answers were correct!');}else if(0==w){alert('All your answers were correct!! YEEEEEEH!');}else{alert('Something went wrong!!');alert(a.responseText);}});">
<input type="hidden" name="quiz" value="<?php echo $iQuiz; ?>" />
<table border="0" cellpadding="10" cellspacing="0" width="100%">
	<tr>
		<th colspan="<?php echo $perregel; ?>" style="padding:5px;"><b><?php echo $arrQuiz['name']; unset($arrQuiz['name']); ?></th>
	</tr>
	<tr valign="top">
<?php

$i = 0;
foreach ( $arrQuiz AS $bs => $arrQuestion ) {
	echo "\t\t".'<td width="'.(int)(100/$perregel).'%"><b>'.$arrQuestion[0].'</b><br /><br />';
	unset($arrQuestion[0]);

	$j = 0;
	echo '<label><input type="radio" name="answers['.$i.']" value="" checked />&nbsp;&nbsp;&nbsp;</label>';
	foreach ( $arrQuestion AS $szAnswer ) {
		$szKey = $abcd[$j];
		echo '<label><input type="radio" name="answers['.$i.']" value="'.$szKey.'" />'.$szKey.'. '.$szAnswer.'</label>';
		$j++;
	}
	echo '</td>'."\n";

	if ( ++$i%$perregel == 0 ) {
		echo "\t".'</tr>'."\n\t".'<tr valign="top">'."\n";
	}
}

?>
	</tr>
	<tr>
		<td colspan="<?php echo $perregel; ?>" align="center" style="padding:5px;"><input type="submit" value="Check" /></td>
	</tr>
</table>
</form>
</body>

</html>
