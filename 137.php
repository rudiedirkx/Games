<?php
// 4*4 SUDOKU

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

// save
if ( isset($_POST['sudoku'], $_POST['level']) ) {
	if ( 'easy' !== $_POST['level'] && 'medium' !== $_POST['level'] && 'hard' !== $_POST['level'] ) {
		exit('Invalid level! Only "easy", "medium" and "hard" are allowed!');
	}
	if ( mysql_query("INSERT INTO sudoku (graad, inhoud, time, type) VALUES ('".$_POST['level']."', '".$_POST['sudoku']."', ".time().", 4);") ) {
		exit('OK'.mysql_insert_id());
	}
	exit(mysql_error());
}

// check
else if ( isset($_POST['id'], $_POST['sudoku']) ) {
	$q = mysql_query("SELECT inhoud FROM sudoku WHERE type = 4 AND id = ".(int)$_POST['id'].";");
	if ( !$q || !mysql_num_rows($q) ) {
		exit('Is no puzzle!');
	}
	$arrDbSudoku = explode(',', mysql_result($q, 0));
	$szSolution = implode(',', De_Oplossing_In_Array($arrDbSudoku));
	if ( $szSolution === $_POST['sudoku'] ) {
		exit(strtoupper('You got it!'));
	}
	exit('Sorry, it\'s not correct!');
}

?>
<html>

<head>
<title>SUDOKU</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<script type="text/javascript">
<!--//
var g_max = 3;
function change_focus(e) {
	e = new Event(e).stop();
	if ( !g_objField ) { return; }
	var c = g_objField.id.split('_');
	var nc = [c[0].toInt(), c[1].toInt()];
	switch ( e.code ) {
		case 38: // UP
			nc[1]--;
			if ( 0 > nc[1] ) { nc[1] = g_max; }
		break;
		case 40: // DN
			nc[1]++;
			if ( g_max < nc[1] ) { nc[1] = 0; }
		break;
		case 37: // LF
			nc[0]--;
			if ( 0 > nc[0] ) { nc[0] = g_max; }
		break;
		case 39: // RT
			nc[0]++;
			if ( g_max < nc[0] ) { nc[0] = 0; }
		break;
		case 49:
		case 50:
		case 51:
		case 52:
			if ( g_objField.getAttribute('o') === '1' ) {
				g_objField.innerHTML = (e.code-48).toString();
			}
		break;
		case 8:
		case 46:
			g_objField.innerHTML = '';
		break;
		case 116: // F5
		case 27: // ESC
			document.location.reload();
		break;
		default:
			return;
	}
	var id = nc.join('_');
	if ( $(id) ) {
		g_objField.style.backgroundColor = '';
		g_objField = $(id);
		g_objField.style.backgroundColor = 'red';
	}
}
//-->
</script>
<style type="text/css">
table.s {
	border-collapse	: collapse;
	border			: solid 5px black;
}
table.s td {
	height			: 45px;
	width			: 45px;
	padding			: 0;
	margin			: 0;
	border			: solid 3px #bbb;
	font-family		: verdana;
	font-size		: 24pt;
	font-weight		: none;
	color			: black;
	text-align		: center;
	font-weight		: bold;
	background-color: #eee;
}
table.s td.o {
	background-color: #fff;
	color			: green;
}
table.s td.rb { border-right	: solid 5px black; }
table.s td.bb { border-bottom	: solid 5px black; }
</style>
</head>

<body>
<?php

if ( isset($_GET['goforplay'], $_GET['id']) ) {
	// ingevulde veldjes (stuk of 25)
	$r = $db->select_one("sudoku", "inhoud", "type = 4 AND id = ".(int)$_GET['id'].";");
	if ( !$r ) {
		exit('Is no puzzle!');
	}
	$arrSudoku = explode(',', $r);
	printSudokuField($arrSudoku);
	echo '<br /><input type="button" value="check" onclick="checkSudoku(getSudoku($(\'sudtab\')), '.(int)$_GET['id'].');" /><br />';
}
else {
	printSudokuField();
	echo '<br /><select id="sudgraad"><option value="">--level</option><option value="easy">= easy</option><option value="medium">= medium</option><option value="hard">= hard</option></select> <input type="button" value="save" onclick="saveSudoku(getSudoku($(\'sudtab\')), $(\'sudgraad\').value);" /><br />';
}

echo '<br /><table border="1" cellpadding="4">';
echo '<tr><th colspan="3">Play existing Sudoku:</td></tr>';
$q = $db->select("sudoku", "0 < oplosbaar AND 4 = type ORDER BY graad ASC, time DESC");
foreach ($q as $r) {
	echo '<tr>';
	echo '<td align="right">'.$r['id'].'</td>';
	echo '<td><a href="?goforplay=1&id='.$r['id'].'">'.$r['graad'].'</a></td>';
	echo '<td style="font-family:\'courier new\';font-size:9pt;">'.strtolower(date('d-M-Y', $r['time'])).'</td>';
	echo '</tr>';
}
echo '</table>';

?>
<p><a href="?">back</a></p>
<script type="text/javascript">
<!--//
var g_objField = $('0_0');
g_objField.style.backgroundColor = 'red';
$$('table.s td').each(function(el) {
	el.onclick = function(e) {
		e = new Event(e).stop();
		if ( g_objField ) { g_objField.style.backgroundColor = ''; }
		g_objField = this;
		g_objField.style.backgroundColor = 'red';
	}
});
function getSudoku(f_table) {
	var s = '';
	f_table.getElements('td').each(function(el) {
		s += ',' + ( el.innerHTML ? el.innerHTML : '0' );
	});
	s = s.substr(1);
	return s;
}
function saveSudoku(s, l) {
	new Ajax('?', {
		data		: 'level=' + l + '&sudoku=' + s,
		onComplete	: function ( t ) {
			if ( 'OK' == t.substr(0,2) ) {
				document.location = '?goforplay=1&id=' + t.substr(2);
			}
			else {
				alert(t);
			}
		}
	}).request();
	return false;
}
function checkSudoku(s, id) {
	new Ajax('?', {
		data		: 'id=' + id + '&sudoku=' + s,
		onComplete	: function ( t ) {
			alert(t);
		}
	}).request();
	return false;
}
document.onkeydown = change_focus;
//-->
</script>
<?php


function printSudokuField( $f_arrSudoku = array() ) {
	if ( $f_arrSudoku ) {
		if ( 4 !== count($f_arrSudoku) ) {
			$f_arrSudoku = array_chunk($f_arrSudoku, 4);
		}
		$arrSudoku = array_values(array_map('array_values', $f_arrSudoku));
	}
	else {
		$arrSudoku = array_fill(0, 4, array_fill(0, 4, 0));
	}
	echo '<table id="sudtab" class="s" border="0">';
	for ( $y=0; $y<4; $y++ ) {
		echo '<tr>';
		for ( $x=0; $x<4; $x++ ) {
			$v = !empty($arrSudoku[$y][$x]) && 0 < (int)$arrSudoku[$y][$x] ? (int)$arrSudoku[$y][$x] : 0;
			$c = array();
			if ( $x === 1 ) { $c[] = 'rb'; }
			if ( $y === 1 ) { $c[] = 'bb'; }
			if ( !$v ) { $c[] = 'o'; }
			echo '<td'.( !$v ? ' o="1"' : '' ).' id="'.$x.'_'.$y.'"'.( $c ? ' class="'.implode(' ', $c).'"' : '' ).'>'.( $v ? $v : '' ).'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
}

function De_Oplossing_In_Array($sudoku)
{
	$stateSolver = new StateSolver($sudoku);
	$stateSolver->findSolution();
	return $stateSolver->sudoku;
}

class StateSolver
{
	/////////////////////////
	/// PUBLIC ATTRIBUTES ///
	/////////////////////////

	// Holds the sudoku puzzel as an array
	var $sudoku;

	//////////////////////////
	/// PRIVATE ATTRIBUTES ///
	//////////////////////////

	// Holds the 12 candidatelists as an array
	var $_candidates;

	// Holds the list of empty cells as an array
	var $_emptyCells;

	// Determines whether or not the algorithm has found a solution
	var $_ready;

	////////////////////
	/// CONSTRUCTORS ///
	////////////////////

	function __construct($sudoku)
	{
		$this->sudoku = $sudoku;
	}

	//////////////////////
	/// PUBLIC METHODS ///
	//////////////////////

	// Initialize the solving algorithm
	function findSolution()
	{
		$column = 0;
		$row = 0;
		$region = 0;
		$eIndex = 0;

		// Fill the candidatelists with all 9 bits set
		for ($i = 0; $i < 12; $i++)
		{
			$this->_candidates[$i] = 511;
		}

		// Exclude invalid candidates and get empty cells
		for ($i = 0; $i < 16; $i++)
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
	function _solve($eIndex)
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
			for ($i = 1; $i < 5; $i++)
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
	function _getCandidateLists($position, &$column, &$row, &$region)
	{
		$column = $position % 4;
		$row = floor(4 + $position / 4);
		$region = floor(8 + floor($column / 2) + 2 * floor(($row - 4) / 2));
	}

	// Excludes a number from the list of candidates
	function _exclude(&$bitSet, $bit)
	{
		$bitSet &= ~(1 << $bit -1);
	}

	// Includes a number into the list of candidates
	function _include(&$bitSet, $bit)
	{
		$bitSet |= (1 << $bit - 1);
	}

	// Determines if number occurs in the specified list of candidates
	function _isCandidate($bitSet, $bit)
	{
		return (($bitSet & (1 << $bit - 1)) == 0) ? false : true;
	}
}
