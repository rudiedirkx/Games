<?php
// 9*9 SUDOKU

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

require __DIR__ . '/inc.cls.statesolver.php';

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

// save
if ( isset($_POST['sudoku'], $_POST['level']) ) {
	if ( !in_array($_POST['level'], array('easy', 'medium', 'hard')) ) {
		exit('Invalid level! Only "easy", "medium" and "hard" are allowed!');
	}

	$data = array(
		'graad' => $_POST['level'],
		'inhoud' => $_POST['sudoku'],
		'time' => time(),
		'type' => 9,
	);
	if ( $db->insert('sudoku', $data) ) {
		exit('OK' . $db->insert_id());
	}

	exit($db->error());
}

// check
else if ( isset($_POST['id'], $_POST['sudoku']) ) {
	$inhoud = $db->select_one('sudoku', 'inhoud', array('type' => 9, 'id' => $_GET['id']));
	$arrDbSudoku = explode(',', $inhoud);
	$szSolution = implode(',', De_Oplossing_In_Array($arrDbSudoku));

	if ( $szSolution === $_POST['sudoku'] ) {
		exit('You got it!');
	}

	exit("Sorry, it's not correct.");
}

?>
<!doctype html>
<html>

<head>
<meta name="viewport" content="width=481px" />
<meta charset="utf-8" />
<title>SUDOKU</title>
<script src="/js/rjs-custom.js"></script>
<style>
table.s {
	border-collapse: collapse;
	border: solid 5px black;
}
table.s td {
	padding: 0;
	margin: 0;
	border: solid 3px #bbb;
	background-color: #e7e7e7;
}
table.s td input {
	display: block;
	height: 45px;
	width: 45px;
	font-family: verdana;
	font-size: 30px;
	color: black;
	text-align: center;
	font-weight: bold;
	background-color: transparent;
	border: 0;
}
table.s td input:focus {
	background-color: red;
}
table.s td.o {
	background-color: #fff;
	color			: green;
}
table.s td.rb {
	border-right: solid 5px black;
}
table.s td.bb {
	border-bottom: solid 5px black;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
	-webkit-appearance: none;
	margin: 0;
}
</style>
</head>

<body>
<?php

if ( isset($_GET['play']) ) {
	$inhoud = explode(',', $db->select_one('sudoku', 'inhoud', array('type' => 9, 'id' => $_GET['play'])));
	$vakje = array_values(array_map(intval(...), $inhoud));
	$solution = De_Oplossing_In_Array($vakje);

	printSudokuField($vakje);
}
else {
	printSudokuField();
}

?>
<br />

<table border="1" cellpadding="4">
<tr><th colspan="3">Play existing Sudoku:</td></tr>
<?php

$q = $db->fetch("SELECT * FROM sudoku WHERE 0 < oplosbaar AND 9 = type ORDER BY id DESC");
foreach ( $q as $r ) {
	echo '<tr>';
	echo '<td align="right">' . $r['id'] . '</td>';
	echo '<td><a href="?play=' . $r['id'] . '">' . $r['graad'] . '</a></td>';
	echo '<td>' . date('d-M-Y', $r['time']) . '</td>';
	echo '</tr>' . "\n";
}

?>
</table>

<script>
document.on('change', 'input[type="number"]', function(e) {
	setTimeout((function() {
		if ( this.value == '0' || this.value == '10' ) {
			this.value = '';
		}
	}).bind(this));
});
</script>

<script>
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
				document.location = '?play=' + t.substr(2);
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

	echo '<table id="sudtab" class="s" border="0">' . "\n";
	for ( $y=0; $y<9; $y++ ) {
		echo '<tr>' . "\n";
		for ( $x=0; $x<9; $x++ ) {
			$v = !empty($arrSudoku[$y][$x]) && 0 < (int)$arrSudoku[$y][$x] ? (int)$arrSudoku[$y][$x] : 0;

			$c = array();
			if ( $x === 2 || $x === 5 ) $c[] = 'rb';
			if ( $y === 2 || $y === 5 ) $c[] = 'bb';
			if ( !$v ) $c[] = 'o';

			$readonly = $v ? 'readonly' : '';
			echo '<td id="' . $x . '_' . $y . '" class="' . implode(' ', $c) . '"><input type="number" min="0" max="10" value="' . ($v ?: '') . '" ' . $readonly . ' /></td>' . "\n";
		}
		echo '</tr>' . "\n";
	}
	echo '</table>' . "\n\n";
}

function De_Oplossing_In_Array($sudoku) {
	$stateSolver = new StateSolver($sudoku);
	$stateSolver->findSolution();
	return $stateSolver->sudoku;
}
