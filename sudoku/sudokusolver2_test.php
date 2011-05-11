<?php

/** 3 * 3 **
$arrGridSudoku = unserialize(file_get_contents('3_x_3_sudoku.txt'));
$arrSingleLevelSudoku = array_fill(0, 81, 0);
foreach ( $arrGridSudoku AS $x => $col ) {
	foreach ( $col AS $y => $n ) {
		$arrSingleLevelSudoku[9*$y+$x] = $n;
	}
}
$szSSClass = 'sudokusolver';

/** 3 * 2 **/
$arrGridSudoku = unserialize(file_get_contents('3_x_2_sudoku.txt'));
$arrSingleLevelSudoku = array_fill(0, 36, 0);
foreach ( $arrGridSudoku AS $x => $col ) {
	foreach ( $col AS $y => $n ) {
		$arrSingleLevelSudoku[6*$y+$x] = $n;
	}
}
$szSSClass = 'sudokusolver_3_2';
/**/

require_once 'inc.cls.sudokusolver.2.php';

$sudoku = new $szSSClass($arrSingleLevelSudoku);

echo $sudoku->table();


