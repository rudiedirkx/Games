<?php
// RECTANGLES

$width = 7;
$height = 7;

echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';

$_time = microtime(1);
$grid = Rectangles::create($width, $height);
$_time = microtime(1) - $_time;
// print_r($grid);
Rectangles::debugTable($grid);
var_dump(round($_time * 1000, 3));

class Rectangles {

	static function debugTable($grid) {
		$colors = ['#f00', '#ff0', '#0c0', '#0cc', '#00c', 'pink', 'purple'];

		$allLabels = array_merge(range('a', 'z'), range(0, 9), range('A', 'Z'));
		$usedLabels = [];

		echo '<br><table cellpadding="10" cellspacing="1">';
		foreach ($grid as $y => $row) {
			echo '<tr>';
			foreach ($row as $x => $cell) {
				list($group, $size) = $cell;
				$color = $group == -1 ? '#000' : $colors[ $group % count($colors) ];
				// $label = isset($usedLabels[$group]) ? $usedLabels[$group] : ($usedLabels[$group] = $allLabels[count($usedLabels)]);
				$label = $group;
				echo '<td bgcolor="' . $color . '">' . $label . '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}

	static public function create($width, $height) {
		for ($i=0; $i < 5000; $i++) {
			if ($grid = self::_create($width, $height)) {
				return $grid;
			}
		}
	}

	static public function _create($width, $height) {
		$grid = array_fill(0, $height, array_fill(0, $width, -1));
		$sizes = [];

		$group = 0;
		while ($next = self::next($grid)) {
			$leftX = self::leftX($grid, $next);
			$leftY = self::leftY($grid, $next);

			$sizeX = self::length($leftX);
			$sizeY = self::length($leftY, $sizeX);
// var_dump($sizeX, $sizeY);

			@$sizes[ $sizeX * $sizeY ]++;

			$dir = $sizeX > $sizeY ? 'hor' : ($sizeX < $sizeY ? 'ver' : 'square');
			for ($y = 0; $y < $sizeY; $y++) {
				for ($x = 0; $x < $sizeX; $x++) {
					$grid[ $y + $next[1] ][ $x + $next[0] ] = [$group, $sizeX * $sizeY, $dir];
				}
			}
// self::table($grid);

			$group++;
		}
// print_r($sizes);

		// Max number of 1's
		if (isset($sizes[1]) && $sizes[1] > $width * $height / 10) {
			return;
		}

		// No neighboring N's
		if (!self::validateUniqueNeighbors($grid)) {
			// return;
		}

		return $grid;
	}

	static public function validateUniqueNeighbors($grid) {
		foreach ($grid as $y => $cols) {
			foreach ($cols as $x => list($group, $size, $dir)) {
				foreach (self::neighbors($grid, $x, $y) as list($nx, $ny)) {
					list($ngroup, $nsize, $ndir) = $grid[$ny][$nx];
					if ($nsize == $size && $ngroup != $group && $ndir == $dir) {
var_dump($group, $ngroup);
						return false;
					}
				}
			}
		}

		return true;
	}

	static public function neighbors($grid, $x, $y) {
		$neighbors = [];
		foreach ([[0, -1], [1, 0], [0, 1], [-1, 0]] as list($ox, $oy)) {
			if (isset($grid[$y+$ox][$x+$oy])) {
				$neighbors[] = [$x+$oy, $y+$ox];
			}
		}

		return $neighbors;
	}

	static public function left($cells) {
		$left = 0;
		foreach ($cells as $cell) {
			if ($cell != -1) {
				return $left;
			}

			$left++;
		}

		return $left;
	}

	static public function leftX($grid, $coord) {
		$cells = $grid[ $coord[1] ];
		$cells = array_slice($cells, $coord[0]);
		return self::left($cells);
	}

	static public function leftY($grid, $coord) {
		$cells = array_column($grid, $coord[0]);
		$cells = array_slice($cells, $coord[0]);
		return self::left($cells);
	}

	static public function length($max, $other = 0) {
		$length = rand(1, min(5, $max));

		if ($other * $length > 10) {
			return self::length($max, $other);
		}

		return $length;
	}

	static public function next($grid) {
		foreach ($grid as $y => $row) {
			foreach ($row as $x => $cell) {
				if ($cell == -1) {
					return [$x, $y];
				}
			}
		}
	}

}
