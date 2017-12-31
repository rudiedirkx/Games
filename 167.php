<?php
// FILLING

// header('Content-type: text/plain');

echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';

$builder = new FillingBuilder(7, 7);
$builder->build();
$builder->printTable();

echo '<pre>';
var_dump($builder->time);
print_r($builder->errors);

class FillingBuilder {

	protected $names;
	protected $directions;

	protected $width;
	protected $height;

	protected $map;
	protected $groupIndex;
	protected $groups;
	protected $colors;
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
		$this->groupIndex = 0;
		$this->groups = [];
		$this->colors = [];
		$this->errors = [];
	}

	public function build() {
		$start = microtime(1);

		// for ($i=0; $i < 100; $i++) {
			while ($loc = $this->findEmptyLocation()) {
				$this->createNewGroup($loc);
			}

			if ($this->checkIntegrity()) {
				return;
			}
var_dump(count($this->errors));

// 			$this->reset();
		// }

		$this->time = microtime(1) - $start;
	}

	protected function createNewGroup( array $location = null ) {
		$location or $location = $this->findEmptyLocation();
		$size = rand(1, 9);

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
	}

	protected function checkIntegrity() {
		$this->errors = [];

		foreach ($this->groups as $groupIndex => $group) {
			$size = count($group);
			foreach ($group as $location) {
				list($locations, $indexes, $sizes) = $this->getNeighborsExcept($location, $groupIndex);

				if (($i = array_search($size, $sizes)) !== false) {
					// if (!$verbose) {
					// 	return false;
					// }

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
		if (!isset($this->colors[$groupIndex])) {
			$this->colors[$groupIndex] = substr('00000' . dechex(rand(0, pow(256, 3) - 1)), -6);
		}

		return $this->colors[$groupIndex];
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

	public function printTable() {
		echo "<table>\n";
		foreach ($this->map as $y => $row) {
			echo "<tr>\n";
			foreach ($row as $x => $cell) {
				echo '<td bgcolor="#' . $this->getGroupColor($cell) . '">';
				echo str_pad($cell, 2, '0', STR_PAD_LEFT);
				echo "</td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

}
