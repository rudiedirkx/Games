<?php
// MINESWEEPER KNIGHTS PATHS

?>
<title>Knight's minesweeper</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="102.css" />
<?php

$builder = new MineSweeperBuilder(20, 20, 70);
$grid = $builder->build();

echo '<table border=0 cellpadding=0 cellspacing=0>';
echo '<tbody id="ms_tbody">';
foreach ($grid as $y => $row) {
	echo '<tr>';
	foreach ($row as $x => $mine) {
		$mines = $builder->mines([$x, $y]);
		echo '<td class="' . ($mines == -1 ? 'om' : ($mines == 0 ? 'o0' : "o$mines")) . '"></td>';
	}
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';

class MineSweeperBuilder {

	const MINE = 1;

	public $width;
	public $height;
	public $mines;

	function __construct($width, $height, $mines) {
		$this->width = $width;
		$this->height = $height;
		$this->mines = $mines;
	}

	function build() {
		$this->grid = array_fill(0, $this->height, array_fill(0, $this->width, 0));
		$mines = [];

		while (count($mines) < $this->mines) {
			$coord = [rand(0, $this->width-1), rand(0, $this->height-1)];
			$mine = implode(', ', $coord);
			if (!isset($mines[$mine])) {
				$mines[$mine] = $mine;
				$this->grid[ $coord[1] ][ $coord[0] ] = self::MINE;
			}
		}

// print_r($this->grid);

		return $this->grid;
	}

	function click($coord) {
		$cells = [];
		$this->open($cells, $coord);
	}

	function open(array &$cells, $coord) {
		$mines = $this->mines($coord);
		if ($mines == -1) {
			throw new MineSweeperMineException;
		}
	}

	function mines($coord) {
		$cell = $this->grid[ $coord[1] ][ $coord[0] ];
		if ($cell === self::MINE) {
			return -1;
		}

		$mines = 0;
		foreach ($this->realNeigbors($coord) as $neighbor) {
			$cell = $this->grid[ $neighbor[1] ][ $neighbor[0] ];
			$mines += ($cell === self::MINE);
		}

		return $mines;
	}

	function realNeigbors($coord) {
		$neighbors = [];
		foreach ($this->neighbors() as $neighbor) {
			$x = $neighbor[0] + $coord[0];
			$y = $neighbor[1] + $coord[1];

			if (isset($this->grid[$y][$x])) {
				$neighbors[] = [$x, $y];
			}
		}

		return $neighbors;
	}

	function neighbors() {
		return [
			[-1, -2],
			[ 1, -2],
			[ 2, -1],
			[ 2,  1],
			[ 1,  2],
			[-1,  2],
			[-2,  1],
			[-2, -1],
		];
	}

}
