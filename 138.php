<?php
// REAL SUDOKU

require __DIR__ . '/inc.bootstrap.php';

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

function displaySudoku($sudoku)
{
	$vakje=$sudoku;
	echo "<table border=1 cellpadding=0 cellspacing=0 style='border:solid 4px black;'><tr>";
	for ($i=0;$i<9*9;$i++)
	{
		$a=$i+1;
		$style = '';
		if (($a>18 && $a<28) || ($a>45 && $a<55))
			$style = "border-bottom:solid 4px black;";
		if (floor(($a+6)/9)==ceil(($a+6)/9) || floor(($a+3)/9)==ceil(($a+3)/9))
			$style.= "border-right:solid 4px black;";
		$vi = (is_numeric($vakje[$i]) && $vakje[$i]!='' && $vakje[$i]>0) ? "<center style='font-family:verdana;font-size:32pt;font-weight:none;color: black;'>$vakje[$i]" : "<input type=text style='width:100%;color:blue;height:100%;font-size:44px;text-align:center;vertical-align:middle;border:none;' OnClick=\"this.select();\">";
		echo "<td height=60 width=60 style='$style'>$vi</td>";
		echo (floor($a/9)==ceil($a/9)) ? "</tr><tr>" : "";
		unset($style);
	}
	echo "</table>";
}

if (isset($_POST['oplossen']) && isset($_POST['vakje']) && isset($_POST['sudoku_id']))
{
	$inhoud = explode(".",implode(".",unserialize(mysql_result(mysql_query("SELECT inhoud FROM sudoku WHERE id='".$_POST['sudoku_id']."';"),0,'inhoud'))));
	$goede_oplossing = implode(",",De_Oplossing_In_Array($inhoud));
	for ($i=0;$i<9*9;$i++)
	{
		if ( isset($_POST['vakje'][$i]) && 1 == strlen($_POST['vakje'][$i]) )
			$van_gebruiker[$i] = $_POST['vakje'][$i];
		else
			$van_gebruiker[$i] = $inhoud[$i];
	}
	$speler_oplossing = implode(",",$van_gebruiker);
	if ($goede_oplossing == $speler_oplossing)
	{
		// Goede oplossing...
		echo "<b style=\"color:green;\">OH HELLZ YEAH! You got it! Nice one :)</b><br><br>";
	}
	else
	{
		// Nope... Maar ga verder
		echo "<b style=\"color:red;\">Eehh! Seriously? This is your answer? Well, that is WRONG! IT'S WRONG, YOU STUPID!!!</b><br><br>";
	}
}

?>
<html>

<head>
<title>SUDOKU</title>
<script language="javascript">
function change_focus( e, i )
{
	if (!e) e = window.event;

	if ( e.keyCode )	kc = e.keyCode;
	else if ( e.which )	kc = e.which;
	else				kc = '!fck';

	debug = 0;

	if ( debug ) alert( 'KC = ' + kc + ', FIELD = ' + i );

	// omhoog
	if ( kc == 38 )
	{
		vto = (i-9);
		if ( debug )
		{
			alert('wil UP vanaf vakje ' + i);
			alert( vto );
		}
		if ( vto > 0 )
		{
			document.getElementById( 'nlv' + vto ).focus();
		}
	}
	// omlaag
	if ( kc == 40 )
	{
		vto = (i-(-9));
		if ( debug )
		{
			alert('wil DOWN vanaf vakje ' + i);
			alert( vto );
		}
		if ( vto <= 81 )
		{
			document.getElementById( 'nlv' + vto ).focus();
		}
	}
	// links
	if ( kc == 37 )
	{
		vto = (i-1);
		if ( debug )
		{
			alert('wil LEFT vanaf vakje ' + i);
			alert( vto );
		}
		if ( vto > 0 )
		{
			document.getElementById( 'nlv' + vto ).focus();
		}
	}
	// rechts
	if ( kc == 39 )
	{
		vto = (i-(-1));
		if ( debug )
		{
			alert('wil RIGHT vanaf vakje ' + i);
			alert( vto );
		}
		if ( vto <= 81 )
		{
			document.getElementById( 'nlv' + vto ).focus();
		}
	}
}
// document.onkeydown=change_focus;
</script>
</head>

<body>
<font face=verdana>
<?php

if (isset($_GET['goforplay']) && (isset($_GET['vakje']) || (isset($_GET['id']) && is_numeric($_GET['id']))))
{
	// ingevulde veldjes (stuk of 25)
	// $vakje = (isset($_GET['id'])) ? unserialize(mysql_result(mysql_query("SELECT inhoud FROM sudoku WHERE id='".$_GET['id']."';"),0,'inhoud')) : $_GET['vakje'];
	// $vakje = explode(".",implode(".",$vakje));
	$vakje = $_GET['vakje'];
	for ($i=0;$i<9*9;$i++)
	{
		if (isset($vakje[$i]))	$vakje[$i]=(int)$vakje[$i];
		else					$vakje[$i]=0;
	}
//	print_r($vakje);
//	echo implode(",",$vakje);

	echo "<table border=0 cellpadding=0 cellspacing=0 style='border:solid 4px black;'><form name=oplossing method=post><input type=hidden name=oplossen value=1><input type=hidden name=sudoku_id value=><tr>";
	for ($i=0;$i<9*9;$i++)
	{
		$a=$i+1;
		$style = 'border-right:solid 1px #000;border-bottom:solid 1px #000;';
		$style2 = 'width:100%;color:blue;height:100%;font-size:44px;text-align:center;vertical-align:middle;border:none;';
		if (($a>18 && $a<28) || ($a>45 && $a<55))
		{
			$style .= "border-bottom:solid 4px black;";
		}
		if (floor(($a+6)/9)==ceil(($a+6)/9) || floor(($a+3)/9)==ceil(($a+3)/9))
		{
			$style .= "border-right:solid 4px black;";
		}
		if ( in_array($a, array(11,12,13, 15,16,17, 47,48,49, 51,52,53,
								20,21,22, 24,25,26, 56,57,58, 60,61,62,
								29,30,31, 33,34,35, 65,66,67, 69,70,71 )) )
		{
			$style2 .= "background-color:#aaa;";
			$style .= "background-color:#aaa;";
		}
		$vi = (is_numeric($vakje[$i]) && $vakje[$i]!='' && $vakje[$i]>0) ? "<center style='font-family:verdana;font-size:32pt;font-weight:none;color:black;'>$vakje[$i]" : "<input name=vakje[$i]".((isset($_POST['vakje']))?" value='".$_POST['vakje'][$i]."'":"")." type=text style='".$style2."' OnClick=\"this.select();\">\n";
		echo '<td height="60" width="60" style="'.$style.'" bgcolor="#eeeeee">'.$vi.'</td>'."\n";
		echo (floor($a/9)==ceil($a/9)) ? "</tr><tr>\n" : "";
		unset($style);
	}
	echo "</table><b>".$_GET['graad']."<br><br><input type=submit value='CHECK'></form>";
	echo "Fill in the numbers 1-9 in each row (vertically & horizontally) and in every 3*3 field (13 in total, so overlapping), not having doubles in any row or 3*3 field.</b><br><br>";

}
else
{
	// 1 t/m 81 is nog leeg
	echo "<form name=\"nog_leeg\"><input type=hidden name=goforplay value=1><table border=0 cellpadding=0 cellspacing=0 style='border:solid 4px black;'><tr>";
	for ($i=1;$i<=9*9;$i++)
	{
		$style = 'border-right:solid 1px #000;border-bottom:solid 1px #000;';
		$style2 = 'width:100%;color:black;height:100%;font-size:44px;text-align:center;vertical-align:middle;border:none;';
		if (($i>18 && $i<28) || ($i>45 && $i<55))
		{
			$style .= "border-bottom:solid 4px black;";
		}
		if (floor(($i+6)/9)==ceil(($i+6)/9) || floor(($i+3)/9)==ceil(($i+3)/9))
		{
			$style .= "border-right:solid 4px black;";
		}
		// if ( in_array($a, array(11,12,13, 15,16,17, 47,48,49, 51,52,53,
		// 						20,21,22, 24,25,26, 56,57,58, 60,61,62,
		// 						29,30,31, 33,34,35, 65,66,67, 69,70,71 )) )
		// {
		// 	$style2 .= "background-color:#aaa;";
		// }
		echo "<td height=60 width=60 style='$style'><input type=text name=\"vakje[$i]\" style='".$style2."' id='nlv".$i."' OnKeyDown=\"change_focus( '$i' );\"></td>\n";
		echo (floor($i/9)==ceil($i/9)) ? "</tr><tr>\n" : "";
		unset($style);
	}
	echo "</table><select name=graad><option value='medium'>DIFFICULTY<option value='easy'>EASY<option value='medium'>MEDIUM<option value='hard'>HARD</select><br><br><input type=submit value=\"SAVE & PLAY\"></form><br><br><br><input type=reset value='reset' /><br>";

}
