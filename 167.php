<?php
// FILLING

require __DIR__ . '/inc.bootstrap.php';

$size = $_GET['size'] ?? 7;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Filling</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
table {
	border-collapse: collapse;
	user-select: none;
	margin-bottom: 1em;
}
td {
	border: solid 1px #999;
	width: 36px;
	height: 36px;
	padding: 0;
	vertical-align: middle;
	text-align: center;
	font-weight: bold;
}
</style>
<style id="colors"></style>
<? include 'tpl.onerror.php' ?>
</head>

<body>
<?php

$builder = new FillingBuilder($size, $size);
$builder->build() or die('Random build failed. Try again.');
// $builder->printTableDebug();
$builder->printTablePlayable();

?>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
var fromCell = null;
var winTimer;
const $table = $$('table').last();

function winCheck() {
	const starts = $table.getElements('td').filter(td => td.textContent != '');
	const groups = starts.map(cell => {
		const group = [];
		extendGroup(group, cell);
		return group;
	});

	const wrongs = groups.filter((group, i) => group.length != parseInt(starts[i].textContent));
	if (wrongs.length == 0) {
		alert("You win!");
	}
}

function extendGroup(group, cell, groupColor) {
	if (!cell || group.includes(cell)) return;
	groupColor || (groupColor = cell.bgColor);
	if (!groupColor || cell.bgColor !== groupColor) return;

	group.push(cell);
	// cell.textContent += 'x';

	const grid = cell.parentNode.parentNode;
	const x = cell.cellIndex;
	const y = cell.parentNode.rowIndex;

	extendGroup(group, grid.rows[y-1] && grid.rows[y-1].cells[x], groupColor);
	extendGroup(group, grid.rows[y+1] && grid.rows[y+1].cells[x], groupColor);
	extendGroup(group, grid.rows[y].cells[x-1], groupColor);
	extendGroup(group, grid.rows[y].cells[x+1], groupColor);
}

$table.on('click', 'td', function(e) {
	if (this.textContent) {
		fromCell = e.target;
	}
	else if (fromCell && this.bgColor == fromCell.bgColor) {
		this.bgColor = '';
	}
	else if (fromCell) {
		this.bgColor = fromCell.bgColor;
	}

	clearTimeout(winTimer);
	winTimer = setTimeout(winCheck, 500);
});
</script>
</body>

</html>
<?php

class FillingBuilder {

	protected $names;
	protected $directions;

	protected $width;
	protected $height;

	protected $map;
	protected $groups;
	protected $groupColors;
	protected $sizeColors;
	public $errors;

	public $log = [];
	public $time = 0;

	public function __construct( $width, $height ) {
		$this->width = $width;
		$this->height = $height;

		$this->setUpConstants();
		$this->reset();
	}

	protected function setUpConstants() {
		$this->names = range('A', 'Z');
		$this->directions = [[0, -1], [1, 0], [0, 1], [-1, 0]];
	}

	protected function reset() {
		$this->map = array_fill(0, $this->height, array_fill(0, $this->width, -1));
		$this->groups = [];
		$this->groupColors = [];
		$this->sizeColors = [];
		$this->errors = [];
	}

	public function build() {
		$start = microtime(1);

		for ($i = 0; $i < 5000; $i++) {
// echo "$i ";
			$this->reset();

			while ($loc = $this->findEmptyLocation()) {
				$groupOk = $this->createNewGroup($loc);
// $this->printTableDebug();
				if (!$groupOk) {
					continue 2;
				}
			}

			if ($this->checkIntegrity()) {
				$this->time = microtime(1) - $start;
				return true;
			}
		}

		return false;
	}

	protected function createNewGroup( array $location = null ) {
		$location or $location = $this->findEmptyLocation();
		$size = rand(2, 9);

		$groupIndex = count($this->groups);

		$this->log("group size: $size");

		$group = [];
		for ($i=0; $i < $size; $i++) {

			$this->log("marking: {$location[0]}, {$location[1]}");

			// Mark current
			$this->map[ $location[1] ][ $location[0] ] = $groupIndex;
			$group[] = $location;

			// Move NESW
			shuffle($this->directions);
			foreach ($this->directions as $direction) {

				$this->log("direction: {$direction[0]}, {$direction[1]}");

				if ($this->getCell($location, $direction) === -1) {
					$location[0] += $direction[0];
					$location[1] += $direction[1];
					continue 2;
				}
			}

			$this->log("cul de sac");

			break;
		}

		$this->groups[] = $group;

		if (count($group) == 1) {
			return false;
		}

		return true;
	}

	protected function checkIntegrity() {
		// return true;

		$this->errors = [];

		foreach ($this->groups as $groupIndex => $group) {
			$size = count($group);
			if ($size == 1) {
				return false;
			}

			foreach ($group as $location) {
				list($locations, $indexes, $sizes) = $this->getNeighborsExcept($location, $groupIndex);

				if (($i = array_search($size, $sizes)) !== false) {
					return false;

					$this->errors[] = [ $groupIndex, $indexes[$i] ];
					break;
				}

			}
		}

		return empty($this->errors);
	}

	protected function getNeighborsExcept( $location, $notGroupIndex ) {
		$locations = $indexes = $sizes = [];
		foreach ($this->directions as $direction) {
			if (!in_array($cell = $this->getCell($location, $direction), [null, -1, $notGroupIndex], true)) {
				$x = $location[0] + (int) $direction[0];
				$y = $location[1] + (int) $direction[1];

				$locations[] = [$x, $y];
				$indexes[] = $cell;
				$sizes[] = count($this->groups[$cell]);
			}
		}

		return [$locations, $indexes, $sizes];
	}

	protected function findEmptyLocation() {
		$empties = [];
		foreach ($this->map as $y => $row) {
			foreach ($row as $x => $cell) {
				if ($this->getCell([$x, $y]) === -1) {
					$empties[] = [$x, $y];
				}
			}
		}

		if ($empties) {
			return $empties[ array_rand($empties) ];
		}
	}

	protected function getRandomLocation() {
		return [
			rand(0, $this->width - 1),
			rand(0, $this->height - 1),
		];
	}

	protected function getCell( array $location, array $direction = [0, 0] ) {
		$x = $location[0] + (int) $direction[0];
		$y = $location[1] + (int) $direction[1];
		return @$this->map[$y][$x];
	}

	protected function log( $msg ) {
		$this->log = $msg;
	}

	protected function getGroupColor( $groupIndex ) {
		if ($groupIndex == -1) {
			return '';
		}

		if (!isset($this->groupColors[$groupIndex])) {
			$this->groupColors[$groupIndex] = '#' . substr('00000' . dechex(rand(0, pow(256, 3) - 1)), -6);
		}

		return $this->groupColors[$groupIndex];
	}

	protected function getSizeColor( $size ) {
		if (!isset($this->sizeColors[$size])) {
			$this->sizeColors[$size] = '#' . substr('00000' . dechex(rand(0, pow(256, 3) - 1)), -6);
		}

		return $this->sizeColors[$size];
	}

	public function printText() {
		foreach ($this->map as $y => $row) {
			foreach ($row as $x => $cell) {
				echo $cell !== -1 ? str_pad($cell, 2, '0', STR_PAD_LEFT) : '  ';
				echo ' ';
			}
			echo "\n";
		}
	}

	public function printTablePlayable() {
		$shows = array_map(function($group) {
			return implode('-', $group[array_rand($group)]);
		}, $this->groups);

		echo "<table>\n";
		foreach ($this->map as $y => $row) {
			echo "<tr>\n";
			foreach ($row as $x => $groupIndex) {
				$group = $this->groups[$groupIndex];
				$size = count($group);

				$show = count($group) > 1 && in_array("$x-$y", $shows);
				$bgcolor = $show ? ' bgcolor="' . $this->getSizeColor($size) . '"' : '';

				echo '<td' . $bgcolor . '>';
				echo $show ? $size : '';
				echo "</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

	public function printTableDebug() {
		echo "<table>\n";
		foreach ($this->map as $y => $row) {
			echo "<tr>\n";
			foreach ($row as $x => $groupIndex) {
				$group = $this->groups[$groupIndex] ?? [];

				echo '<td bgcolor="' . $this->getGroupColor($groupIndex) . '">';
				echo count($group) ?: '';
				echo "</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

}
