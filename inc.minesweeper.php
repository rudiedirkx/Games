<?php

class MinesweeperMaker {
	public function create_map( $f_width, $f_height, $f_m, $f_x, $f_y ) {
		$arrMap = array_fill(0, $f_height, array_fill(0, $f_width, 0));

		$iMines = 0;
		while ( $iMines < $f_m ) {
			$x = rand(0, $f_width-1);
			$y = rand(0, $f_height-1);
			if ( 'm' !== $arrMap[$y][$x] ) {
				$arrMap[$y][$x] = 'm';
				$this->surrounders_plus_one($arrMap, $x, $y);
				$iMines++;
			}
		}

		$tile = $arrMap[$f_y][$f_x];
		if ( $tile !== 0 ) {
			return $this->create_map($f_width, $f_height, $f_m, $f_x, $f_y);
		}

		return $arrMap;
	}

	protected function surrounders() {
		return array(
			array(0, -1),
			array(1, -1),
			array(1, 0),
			array(1, 1),
			array(0, 1),
			array(-1, 1),
			array(-1, 0),
			array(-1, -1),
		);
	}

	protected function surrounders_plus_one(&$f_map, $f_x, $f_y) {
		foreach ( $this->surrounders() AS $d ) {
			if ( isset($f_map[$f_y+$d[0]][$f_x+$d[1]]) && 'm' !== $f_map[$f_y+$d[0]][$f_x+$d[1]] ) {
				$f_map[$f_y+$d[0]][$f_x+$d[1]]++;
			}
		}
	}

	public function click_on_surrounders(&$arrUpdates, $f_x, $f_y) {
		foreach ( $this->surrounders() AS $d ) {
			if ( isset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]]) ) {
				$f = $_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]];
				$arrUpdates[] = array($f_x+$d[1], $f_y+$d[0], $f);
				unset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]]);
				if ( 0 === $f ) {
					$this->click_on_surrounders($arrUpdates, $f_x+$d[1], $f_y+$d[0]);
				}
			}
		}
	}
}
