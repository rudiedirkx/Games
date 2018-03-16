<?php
// SUDOKU

require __DIR__ . '/inc.bootstrap.php';

class StateSolver
{
	/////////////////////////
	/// PUBLIC ATTRIBUTES ///
	/////////////////////////

	// Holds the sudoku puzzel as an array
	public $sudoku;

	//////////////////////////
	/// PRIVATE ATTRIBUTES ///
	//////////////////////////

	// Holds the 27 candidatelists as an array
	private $_candidates;

	// Holds the list of empty cells as an array
	private $_emptyCells;

	// Determines whether or not the algorithm has found a solution
	private $_ready;

	////////////////////
	/// CONSTRUCTORS ///
	////////////////////

	public function __construct($sudoku)
	{
		$this->sudoku = $sudoku;
	}

	//////////////////////
	/// PUBLIC METHODS ///
	//////////////////////

	// Initialize the solving algorithm
	public function findSolution()
	{
		$column = 0;
		$row = 0;
		$region = 0;
		$eIndex = 0;

		// Fill the candidatelists with all 9 bits set
		for ($i = 0; $i < 27; $i++)
		{
			$this->_candidates[$i] = 511;
		}

		// Exclude invalid candidates and get empty cells
		for ($i = 0; $i < 81; $i++)
		{
			if ($this->sudoku[$i] == 0)
			{
				// Add this empty cell to the list
				$this->_emptyCells[$eIndex++] = $i;
			}
			else
			{
				// Exclude this number from the candidatelists
				$this->_getCandidateLists($i, $column, $row, $region);

				$this->_exclude($this->_candidates[$column], $this->sudoku[$i]);
				$this->_exclude($this->_candidates[$row], $this->sudoku[$i]);
				$this->_exclude($this->_candidates[$region], $this->sudoku[$i]);
			}
		}

		// Set the ready flag to false
		$this->_ready = false;

		// Run the recursive backtracking algorithm
		$this->_solve(0);
	}

	///////////////////////
	/// PRIVATE METHODS ///
	///////////////////////

	// Recursive backtracking solver
	private function _solve($eIndex)
	{
		$column = 0;
		$row = 0;
		$region = 0;

		// See if haven't reached the end of the pattern
		if ($eIndex < count($this->_emptyCells))
		{
			// Get the corresponding candidatelists
			$this->_getCandidateLists($this->_emptyCells[$eIndex], $column, $row, $region);

			// Check if $i occurs in all three candidatelists
			for ($i = 1; $i < 10; $i++)
			{
				if ($this->_isCandidate($this->_candidates[$column], $i) && $this->_isCandidate($this->_candidates[$row], $i) && $this->_isCandidate($this->_candidates[$region], $i))
				{
					// Suitable candidate found, use it!
					$this->sudoku[$this->_emptyCells[$eIndex]] = $i;

					// Exclude this number from the candidatelists
					$this->_exclude($this->_candidates[$column], $i);
					$this->_exclude($this->_candidates[$row], $i);
					$this->_exclude($this->_candidates[$region], $i);

					// Don't advance if a solution has been found
					if ($this->_ready)
						return;

					// Advance to the next cell
					$this->_solve($eIndex + 1);

					// Don't revert if a solution has been found
					if ($this->_ready)
						return;

					// Reset the cell
					$this->sudoku[$this->_emptyCells[$eIndex]] = 0;

					// Put the candidates back in the lists
					$this->_include($this->_candidates[$column], $i);
					$this->_include($this->_candidates[$row], $i);
					$this->_include($this->_candidates[$region], $i);
				}
			}
		}
		else
		{
			// A solution has been found, get out of recursion
			$this->_ready = true;
		}
	}

	// Obtains the corresponding candidatelist indices
	private function _getCandidateLists($position, &$column, &$row, &$region)
	{
		$column = $position % 9;
		$row = floor(9 + $position / 9);
		$region = floor(18 + floor($column / 3) + 3 * floor(($row - 9) / 3));
	}

	// Excludes a number from the list of candidates
	private function _exclude(&$bitSet, $bit)
	{
		$bitSet &= ~(1 << $bit -1);
	}

	// Includes a number into the list of candidates
	private function _include(&$bitSet, $bit)
	{
		$bitSet |= (1 << $bit - 1);
	}

	// Determines if number occurs in the specified list of candidates
	private function _isCandidate($bitSet, $bit)
	{
		return (($bitSet & (1 << $bit - 1)) == 0) ? false : true;
	}
}

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
	$sql = "INSERT INTO sudoku (graad,inhoud,time) VALUES ('".$_GET['graad']."','".serialize($_GET['vakje'])."','".time()."')";
	if (mysql_result(mysql_query("SELECT COUNT(*) AS a FROM sudoku WHERE inhoud='".serialize($_GET['vakje'])."';"),0,'a') == 0)
		mysql_query($sql) or die(mysql_error());
}

if (isset($_POST['oplossen']) && isset($_POST['vakje']) && isset($_POST['sudoku_id']))
{
	$inhoud = explode(".",implode(".",unserialize(mysql_result(mysql_query("SELECT inhoud FROM sudoku WHERE id='".$_POST['sudoku_id']."';"),0,'inhoud'))));
	$goede_oplossing = implode(",",De_Oplossing_In_Array($inhoud));
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
<font face=verdana>
<?

if (isset($_GET['goforplay']) && (isset($_GET['vakje']) || (isset($_GET['id']) && is_numeric($_GET['id']))))
{
	// ingevulde veldjes (stuk of 25)
	$vakje = (isset($_GET['id'])) ? unserialize(mysql_result(mysql_query("SELECT inhoud FROM sudoku WHERE id='".$_GET['id']."';"),0,'inhoud')) : $_GET['vakje'];
	$vakje = explode(".",implode(".",$vakje));
	for ($i=0;$i<9*9;$i++)
		$vakje[$i]=(int)$vakje[$i];
//	print_r($vakje);
//	echo implode(",",$vakje);
	echo "<table border=1 cellpadding=0 cellspacing=0 style='border:solid 4px black;'><form name=oplossing method=post><input type=hidden name=oplossen value=1><input type=hidden name=sudoku_id value=".((isset($_GET['id']))?$_GET['id']:mysql_insert_id())."><tr>";
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
	$q = mysql_query("SELECT id,graad,time FROM sudoku WHERE graad!='' ORDER BY graad,time DESC;") or die(mysql_error());
	$a = array();
	while ($r = mysql_fetch_assoc($q))
	{
		if (isset($a[$r['graad']]))
			$a[$r['graad']]++;
		else
			$a[$r['graad']]=1;
		echo "<--".$a[$r['graad']].".-->(".$r['id'].") ".$r['graad']." - <a href=\"?goforplay=1&graad=".$r['graad']."&id=".$r['id']."\">open</a> (".date("d-m-Y",$r['time']).")<br>";
	}
}


