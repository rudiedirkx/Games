<?php
// SUDOKU

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

require __DIR__ . '/inc.cls.statesolver.php';

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

function De_Oplossing_In_Array($sudoku)
{
	$stateSolver = new StateSolver($sudoku);
	$stateSolver->findSolution();
	return $stateSolver->sudoku;
}

if (isset($_GET['vakje']) && isset($_GET['graad']) && isset($_GET['goforplay']))
{
	$vakje = array_values(array_map(fn($num) => (int) $num, $_GET['vakje']));
	$inhoud = implode(',', $vakje);
	if (!($id = $db->select_one('sudoku', 'id', ['inhoud' => $inhoud]))) {
		$db->insert('sudoku', [
			'type' => '9',
			'graad' => $_GET['graad'],
			'inhoud' => $inhoud,
			'time' => time(),
		]);
		$id = $db->insert_id();
	}
}

if (isset($_POST['oplossen']) && isset($_POST['vakje']) && isset($_POST['sudoku_id']))
{
	$inhoud = explode(',', $db->select_one('sudoku', 'inhoud', array('type' => 9, 'id' => $_POST['sudoku_id'])));
	$vakje = array_values(array_map(intval(...), $inhoud));
	$goede_oplossing = implode(",", De_Oplossing_In_Array($vakje));
	for ($i=0;$i<81;$i++)
	{
		if (isset($_POST['vakje'][$i]) && strlen($_POST['vakje'][$i])==1)
			$van_gebruiker[$i] = $_POST['vakje'][$i];
		else
			$van_gebruiker[$i] = $inhoud[$i];
	}
	$speler_oplossing = implode(",",$van_gebruiker);
	if ($goede_oplossing == $speler_oplossing)
	{
		// Goede oplossing...
		echo " JE HEBT M!!!<br><br>";
	}
	else
	{
		// Nope... Maar ga verder
		echo "Oplossing is NIET goed... Ga verder!<br><br>";
	}
}

?>
<html>

<head>
<title>SUDOKU</title>
<script>
function change_focus(i)
{
	// omhoog
	if (event.type=='keydown' && event.keyCode==38 && i+9>=0 && i+9<81)
	{
		document.nog_leeg.vakje[i+9].focus();
	}
	// omlaag
	if (event.type=='keydown' && event.keyCode==40 && i-9>=0 && i-9<81)
	{
		document.nog_leeg.vakje[i-9].focus();
	}
	// links
	if (event.type=='keydown' && event.keyCode==37 && i-1>=0 && i-1<81)
	{
		document.nog_leeg.vakje[i-1].focus();
	}
	// rechts
	if (event.type=='keydown' && event.keyCode==39 && i+1>=0 && i+1<81)
	{
		document.nog_leeg.vakje[i+1].focus();
	}
}
// document.onkeydown=change_focus;
</script>
</head>

<body>
<?php

if (isset($_GET['goforplay']) && (isset($_GET['vakje']) || (isset($_GET['id']) && is_numeric($_GET['id']))))
{
	// ingevulde veldjes (stuk of 25)
	$vakje = isset($_GET['id']) ? explode(',', $db->select_one("sudoku", "inhoud", "id='".(int)$_GET['id']."';")) : array_values($_GET['vakje']);
	$vakje = array_values(array_map(intval(...), $vakje));

	echo "<table border=1 cellpadding=0 cellspacing=0 style='border:solid 4px black;'><form name=oplossing method=post><input type=hidden name=oplossen value=1><input type=hidden name=sudoku_id value=".((isset($_GET['id']))?$_GET['id']:$id)."><tr>";
	for ($i=0;$i<9*9;$i++)
	{
		$a=$i+1;
		$style = '';
		if (($a>18 && $a<28) || ($a>45 && $a<55))
			$style = "border-bottom:solid 4px black;";
		if (floor(($a+6)/9)==ceil(($a+6)/9) || floor(($a+3)/9)==ceil(($a+3)/9))
			$style.= "border-right:solid 4px black;";
		$vi = (is_numeric($vakje[$i]) && $vakje[$i]!='' && $vakje[$i]>0) ? "<center style='font-family:verdana;font-size:32pt;font-weight:none;color: black;'>$vakje[$i]" : "<input name=vakje[$i]".((isset($_POST['vakje']))?" value='".$_POST['vakje'][$i]."'":"")." type=text style='width:100%;color:blue;height:100%;font-size:44px;text-align:center;vertical-align:middle;border:none;' OnClick=\"this.select();\">";
		echo "<td height=60 width=60 style='$style'>$vi</td>";
		echo (floor($a/9)==ceil($a/9)) ? "</tr><tr>" : "";
		unset($style);
	}
	echo "</table><b>".$_GET['graad']."<br><br><input type=submit value='CHECK'></form>";
	echo "Fill in the numbers 1-9 in each row (vertically & horizontally) and in every 3*3 field (9 in total, not overlapping), not having doubles in any row or 3*3 field.</b><br><br>";
//	print_r(De_Oplossing_In_Array($vakje));
//	displaySudoku(De_Oplossing_In_Array(explode(".",implode(".",$vakje))));
}
else
{
	// 1 t/m 81 is nog leeg
	echo "<form name=nog_leeg><input type=hidden name=goforplay value=1><table border=1 cellpadding=0 cellspacing=0 style='border:solid 4px black;'><tr>";
	for ($i=1;$i<=9*9;$i++)
	{
		$style = '';
		if (($i>18 && $i<28) || ($i>45 && $i<55))
			$style = "border-bottom:solid 4px black;";
		if (floor(($i+6)/9)==ceil(($i+6)/9) || floor(($i+3)/9)==ceil(($i+3)/9))
			$style.= "border-right:solid 4px black;";
		echo "<td height=60 width=60 style='$style'><input type=text name=vakje[$i] style='width:100%;color:black;height:100%;font-size:44px;text-align:center;vertical-align:middle;border:none;' OnKeyDown=\"change_focus($i);\"></td>\n";
		echo (floor($i/9)==ceil($i/9)) ? "</tr><tr>\n" : "";
		unset($style);
	}
	echo "</table><select name=graad><option value='medium'>DIFFICULTY<option value='easy'>EASY<option value='medium'>MEDIUM<option value='hard'>HARD</select><br><br><input type=submit value=\"SAVE & PLAY\"></form><br><br><br>";
	echo "Or open an old one:<br>";
	$q = $db->select("sudoku", "type = '9' AND graad != '' ORDER BY id DESC");
	$a = array();
	foreach ($q as $r) {
		if (isset($a[$r['graad']]))
			$a[$r['graad']]++;
		else
			$a[$r['graad']]=1;
		echo "<!--".$a[$r['graad']].".-->(".$r['id'].") ".$r['graad']." - <a href=\"?goforplay=1&graad=".$r['graad']."&id=".$r['id']."\">open</a> (".date("d-m-Y",$r['time']).")<br>";
	}
}


