<?php

require_once __DIR__ . '/inc.minesweeper.php';

class KnightsMinesweeperMaker extends MinesweeperMaker {
	protected function surrounders() {
		return array(
			[1, -2],
			[2, -1],
			[2, 1],
			[1, 2],
			[-1, 2],
			[-2, 1],
			[-2, -1],
			[-1, -2],
		);
	}
}

$title = "KNIGHT'S MINESWEEPER";
$minesweeper = new KnightsMinesweeperMaker();

require '102.php';
