<?php
// 9*9 SUDOKU

require_once('connect.php');

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

// save
if ( isset($_POST['sudoku'], $_POST['level']) ) {
	if ( 'easy' !== $_POST['level'] && 'medium' !== $_POST['level'] && 'hard' !== $_POST['level'] ) {
		exit('Invalid level! Only "easy", "medium" and "hard" are allowed!');
	}
	if ( mysql_query("INSERT INTO sudoku (graad, inhoud, time, type) VALUES ('".$_POST['level']."', '".$_POST['sudoku']."', ".time().", 9);") ) {
		exit('OK'.mysql_insert_id());
	}
	exit(mysql_error());
}

// check
else if ( isset($_POST['id'], $_POST['sudoku']) ) {
	$q = mysql_query("SELECT inhoud FROM sudoku WHERE type = 9 AND id = ".(int)$_POST['id'].";");
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
var g_max = 8;
function change_focus(e) {
	e = new Event(e);
	if ( !g_objField ) { return; }
	var c = g_objField.id.split('_');
	var nc = [c[0].toInt(), c[1].toInt()];
//console.log(e.code);
	switch ( e.code ) {
		case 72: // letter H
			helpForAllFields();
		break;
		case 38: // UP
			nc[1]--;
			if ( 0 > nc[1] ) { nc[1] = g_max; }
			e.stop();
		break;
		case 40: // DN
			nc[1]++;
			if ( g_max < nc[1] ) { nc[1] = 0; }
			e.stop();
		break;
		case 37: // LF
			nc[0]--;
			if ( 0 > nc[0] ) { nc[0] = g_max; }
			e.stop();
		break;
		case 39: // RT
			nc[0]++;
			if ( g_max < nc[0] ) { nc[0] = 0; }
			e.stop();
		break;
		case 49:
		case 50:
		case 51:
		case 52:
		case 53:
		case 54:
		case 55:
		case 56:
		case 57:
			if ( g_objField.getAttribute('o') === '1' ) {
				g_objField.innerHTML = (e.code-48).toString();
			}
			e.stop();
		break;
		case 97:
		case 98:
		case 99:
		case 100:
		case 101:
		case 102:
		case 103:
		case 104:
		case 105:
			if ( g_objField.getAttribute('o') === '1' ) {
				g_objField.innerHTML = (e.code-96).toString();
			}
			e.stop();
		break;
		case 96:
		case 48:
		case 46:
			if ( g_objField.getAttribute('o') === '1' ) {
				g_objField.innerHTML = '';
			}
			e.stop();
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
	$q = mysql_query("SELECT inhoud FROM sudoku WHERE type = 9 AND id = ".(int)$_GET['id'].";");
	if ( !$q || !mysql_num_rows($q) ) {
		exit('Is no puzzle!');
	}
	$arrSudoku = explode(',', mysql_result($q, 0));
	if ( !empty($_GET['solution']) ) {
		$arrSudoku = De_Oplossing_In_Array($arrSudoku);
	}
	printSudokuField($arrSudoku);
	echo '<br /><input type="button" value="check" onclick="checkSudoku(getSudoku($(\'sudtab\')), '.(int)$_GET['id'].');" /><br />';
}
else {
//	echo '<table id="sudtab" border="0" class="s"><tbody><tr><td class="o" id="0_0" style=""/><td class="o" id="1_0" style="">5</td><td class="rb o" id="2_0" style="">1</td><td class="o" id="3_0" style="">9</td><td class="o" id="4_0"/><td class="rb o" id="5_0"/><td class="o" id="6_0"/><td class="o" id="7_0" style="">3</td><td class="o" id="8_0" style="">2</td></tr><tr><td class="o" id="0_1" style="">2</td><td class="o" id="1_1" style="">4</td><td class="rb o" id="2_1" style=""/><td class="o" id="3_1" style="">5</td><td class="o" id="4_1" style="">1</td><td class="rb o" id="5_1" style=""/><td class="o" id="6_1" style=""/><td class="o" id="7_1" style=""/><td class="o" id="8_1" style="">8</td></tr><tr><td class="bb o" id="0_2"/><td class="bb o" id="1_2"/><td class="rb bb o" id="2_2" style="">6</td><td class="bb o" id="3_2" style=""/><td class="bb o" id="4_2" style="">3</td><td class="rb bb o" id="5_2"/><td class="bb o" id="6_2"/><td class="bb o" id="7_2"/><td class="bb o" id="8_2"/></tr><tr><td class="o" id="0_3"/><td class="o o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1" o="1"" id="1_3" style="">1</td><td class="rb o" id="2_3" style="">8</td><td class="o" id="3_3" style="">7</td><td class="o" id="4_3"/><td class="rb o" id="5_3"/><td class="o" id="6_3"/><td class="o" id="7_3"/><td class="o" id="8_3"/></tr><tr><td class="o" id="0_4" style="">9</td><td class="o" id="1_4" style="">2</td><td class="rb o" id="2_4" style="">7</td><td class="o" id="3_4" style=""/><td class="o" id="4_4" style=""/><td class="rb o" id="5_4" style=""/><td class="o" id="6_4" style="">3</td><td class="o" id="7_4" style="">4</td><td class="o" id="8_4" style="">1</td></tr><tr><td class="bb o" id="0_5" style=""/><td class="bb o" id="1_5"/><td class="rb bb o" id="2_5"/><td class="bb o" id="3_5"/><td class="bb o" id="4_5"/><td class="rb bb o" id="5_5" style="">2</td><td class="bb o" id="6_5" style="">5</td><td class="bb o" id="7_5" style="">8</td><td class="bb o" id="8_5" style=""/></tr><tr><td class="o" id="0_6" style=""/><td class="o" id="1_6"/><td class="rb o" id="2_6"/><td class="o" id="3_6"/><td class="o" id="4_6" style="">5</td><td class="rb o" id="5_6" style=""/><td class="o" id="6_6" style="">9</td><td class="o" id="7_6"/><td class="o" id="8_6"/></tr><tr><td class="o" id="0_7" style="">3</td><td class="o" id="1_7" style=""/><td class="rb o" id="2_7"/><td class="o" id="3_7"/><td class="o" id="4_7" style="">6</td><td class="rb o" id="5_7" style="">9</td><td class="o" id="6_7" style=""/><td class="o" id="7_7" style="">2</td><td class="o" id="8_7">4</td></tr><tr><td class="o" id="0_8" style="">1</td><td class="o" id="1_8" style="">6</td><td class="rb o" id="2_8" style=""/><td class="o" id="3_8" style=""/><td class="o" id="4_8" style=""/><td class="rb o" id="5_8" style="">7</td><td class="o" id="6_8" style="">8</td><td class="o" id="7_8" style="">5</td><td class="o" id="8_8"/></tr></tbody></table>';
	printSudokuField();
	echo '<br /><select id="sudgraad"><option value="">--level</option><option value="easy">= easy</option><option value="medium">= medium</option><option value="hard">= hard</option></select> <input type="button" value="save" onclick="saveSudoku(getSudoku($(\'sudtab\')), $(\'sudgraad\').value);" /><br />';
}

echo '<br /><table border="1" cellpadding="4">';
echo '<tr><th colspan="3">Play existing Sudoku:</td></tr>';
$q = mysql_query("SELECT * FROM sudoku WHERE 0 < oplosbaar AND 9 = type ORDER BY graad ASC, time DESC;") or die(mysql_error());
while ( $r = mysql_fetch_assoc($q) ) {
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
function helpForField( f ) {
	var col = f.cellIndex, row = f.parentNode.sectionRowIndex;
	var possibles = [1,2,3,4,5,6,7,8,9];
	// check row
	f.parentNode.getChildren().each(function(td) {
		if ( td.innerHTML && -1 != possibles.indexOf(parseInt(td.innerHTML)) ) {
			var i = possibles.indexOf(parseInt(td.innerHTML));
			possibles.splice(i, 1);
		}
	});
	// check column
	f.parentNode.parentNode.getChildren().each(function(tr) {
		var td = tr.cells[col];
		if ( td.innerHTML && -1 != possibles.indexOf(parseInt(td.innerHTML)) ) {
			var i = possibles.indexOf(parseInt(td.innerHTML));
			possibles.splice(i, 1);
		}
	});
	// check big field
	var rowA = 3*Math.floor(row/3), rowB = rowA+2;
	var colA = 3*Math.floor(col/3), colB = colA+2;
	var tb = f.parentNode.parentNode;
	for ( var y=rowA; y<=rowB; y++ ) {
		var row = tb.rows[y];
		for ( var x=colA; x<=colB; x++ ) {
			var td = row.cells[x];
			if ( td.innerHTML && -1 != possibles.indexOf(parseInt(td.innerHTML)) ) {
				var i = possibles.indexOf(parseInt(td.innerHTML));
				possibles.splice(i, 1);
			}
		}
	}
	if ( 1 == possibles.length ) {
		f.innerHTML = possibles[0];
	}
}
function helpForAllFields() {
	$$('#sudtab td').each(function(td) {
		helpForField(td);
	});
}

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
		if ( 9 !== count($f_arrSudoku) ) {
			$f_arrSudoku = array_chunk($f_arrSudoku, 9);
		}
		$arrSudoku = array_values(array_map('array_values', $f_arrSudoku));
	}
	else {
		$arrSudoku = array_fill(0, 9, array_fill(0, 9, 0));
	}
	echo '<table id="sudtab" class="s" border="0">';
	for ( $y=0; $y<9; $y++ ) {
		echo '<tr>';
		for ( $x=0; $x<9; $x++ ) {
			$v = !empty($arrSudoku[$y][$x]) && 0 < (int)$arrSudoku[$y][$x] ? (int)$arrSudoku[$y][$x] : 0;
			$c = array();
			if ( $x === 2 || $x === 5 ) { $c[] = 'rb'; }
			if ( $y === 2 || $y === 5 ) { $c[] = 'bb'; }
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
	// public
	var $sudoku; 

	////////////////////////// 
	/// PRIVATE ATTRIBUTES /// 
	////////////////////////// 

	// Holds the 27 candidatelists as an array 
	// private
	var $_candidates; 

	// Holds the list of empty cells as an array 
	// private
	var $_emptyCells; 

	// Determines whether or not the algorithm has found a solution 
	// private
	var $_ready; 

	//////////////////// 
	/// CONSTRUCTORS /// 
	//////////////////// 

	function __construct($sudoku)
	// public
	{ 
		$this->sudoku = $sudoku; 
	} 

	////////////////////// 
	/// PUBLIC METHODS /// 
	////////////////////// 

	// Initialize the solving algorithm 
	// public
	function findSolution()
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
	// private
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
	// private
	function _getCandidateLists($position, &$column, &$row, &$region)
	{ 
		$column = $position % 9; 
		$row = floor(9 + $position / 9); 
		$region = floor(18 + floor($column / 3) + 3 * floor(($row - 9) / 3)); 
	} 

	// Excludes a number from the list of candidates 
	// private
	function _exclude(&$bitSet, $bit)
	{ 
		$bitSet &= ~(1 << $bit -1); 
	} 

	// Includes a number into the list of candidates 
	// private
	function _include(&$bitSet, $bit)
	{ 
		$bitSet |= (1 << $bit - 1); 
	} 

	// Determines if number occurs in the specified list of candidates 
	// private
	function _isCandidate($bitSet, $bit)
	{ 
		return (($bitSet & (1 << $bit - 1)) == 0) ? false : true; 
	}
}


?>