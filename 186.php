<?php
// 0h h1

$build = new OhhiGrid($_GET['size'] ?? 6);
$build->make();
$build->hide();

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>0h h1</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<style>
table {
	border-spacing: 3px;
	border: solid 1px #999;
}
td {
	border: 0;
	padding: 0;
	width: 30px;
	height: 30px;
	background-color: #eee;
	text-align: center;
	vertical-align: middle;
}
td[data-color="on"] {
	background-color: green;
	color: lightseagreen;
}
td[data-color="off"] {
	background-color: gold;
	color: orange;
}
</style>
</head>

<body>

<?= $build->htmlTable() ?>

<p>Took <?= number_format(($build->time1 + $build->time2) * 1000, 1) ?> ms.</p>

<script>
document.querySelector('table').addEventListener('click', function(e) {
	const cell = e.target;
	if (!cell.matches('td:not([data-initial])')) return;

	const curColor = cell.dataset.color;
	if (curColor === 'on') {
		cell.dataset.color = 'off';
	}
	else if (curColor === 'off') {
		delete cell.dataset.color;
	}
	else {
		cell.dataset.color = 'on';
	}
});
</script>
</body>

</html>
<?php

class OhhiGrid {

	protected $size = 0;
	protected $grid = [];

	public $time1 = 0;
	public $time2 = 0;
	public $failedRows = 0;
	public $failedCols = 0;

	public function __construct($size = null) {
		if ($size) {
			$this->reset($size);
		}
	}

	public function reset($size) {
		$this->size = $size;
		$this->grid = array_fill(0, $size, array_fill(0, $size, null));
	}

	public function make() {
		$time = microtime(1);

		$this->failedRows = 1;
		while (!$this->isValidGrid($grid = $this->makeRandomRows())) {
			$this->failedRows++;
		}

		$this->grid = $grid;
		$this->time1 = microtime(1) - $time;
	}

	public function hide($keepPct = 40) {
		$time = microtime(1);

		$coords = [];
		foreach ($this->grid as $row => $cells) {
			foreach ($cells as $col => $value) {
				$coords[] = [$col, $row];
			}
		}

		shuffle($coords);

		$keep = round($keepPct / 100 * count($coords));
		foreach (array_slice($coords, $keep) as $coord) {
			[$col, $row] = $coord;
			$this->grid[$row][$col] = null;
		}

		$this->time2 = microtime(1) - $time;
	}

	protected function isValidGrid(array $grid) {
		if (!count($grid)) {
			return false;
		}

		$rows = array_map(function($cells) {
			return implode('', $cells);
		}, $grid);
		if (count($rows) != count(array_unique($rows))) {
			$this->failedRows++;
			return false;
		}

		$cols = array_map(function($col) use ($grid) {
			return implode('', array_column($grid, $col));
		}, range(0, $this->size - 1));
		if (count($cols) != count(array_unique($cols))) {
			$this->failedCols++;
			return false;
		}

		return true;
	}

	protected function isValidLine(array $line) {
		$values = array_count_values($line);
		if (implode('', $values) !== ($this->size / 2) . ($this->size / 2)) {
			return false;
		}

		$lastValue = null;
		$lastLength = 0;
		foreach ($line as $value) {
			if ($lastLength == 0) {
				$lastValue = $value;
				$lastLength = 1;
			}
			elseif ($lastValue != $value) {
				$lastValue = $value;
				$lastLength = 1;
			}
			elseif ($lastLength >= 2) {
				return false;
			}
			else {
				$lastLength++;
			}
		}

		return true;
	}

	protected function makeRandomRows() {
		$grid = [];
		foreach ($this->grid as $row => $cells) {
			$grid[] = $this->makeValidRandomLine();
		}

		for ($col = 0; $col < $this->size; $col++) {
			if (!$this->isValidLine(array_column($grid, $col))) {
				$this->failedCols++;
				return [];
			}
		}

		return $grid;

		// $half = $this->size * $this->size / 2;
		// $all = array_merge(
		// 	array_fill(0, $half, 0),
		// 	array_fill(0, $half, 1)
		// );
		// shuffle($all);
		// return array_chunk($all, $this->size);
	}

	protected function makeValidRandomLine() {
		while (!$this->isValidLine($line = $this->makeRandomLine())) {
			// again
		}

		return $line;
	}

	protected function makeRandomLine() {
		$half = $this->size / 2;
		$all = array_merge(
			array_fill(0, $half, 0),
			array_fill(0, $half, 1)
		);
		shuffle($all);
		return $all;
	}

	public function htmlTable() {
		$html = '';

		$html .= '<table>';
		foreach ($this->grid as $row => $cells) {
			$html .= '<tr>';
			foreach ($cells as $col => $value) {
				$class = $value === null ? '' : ' data-color="' . ($value ? 'on' : 'off') . '" data-initial';
				$html .= '<td' . $class . '>';
				$html .= $value === null ? '' : 'x';
				$html .= '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>';

		return $html;
	}

}
