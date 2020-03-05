<?php
// RECTANGLES

$width = 7;
$height = 7;

?>
<title>Rectangles</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
table {
	border-spacing: 1px;
}
td {
	width: 36px;
	height: 36px;
	padding: 0;
	vertical-align: middle;
	text-align: center;
}
</style>
<?php

$_time = microtime(1);
$grid = Rectangles::create($width, $height);
$_time = microtime(1) - $_time;
// print_r($grid);
$grid and Rectangles::debugTable($grid);
var_dump(round($_time * 1000, 3));

class Rectangles {

	static public $colors = ['#f00', '#ff0', '#0c0', '#0cc', '#00c', 'pink', 'purple'];
	static public $colored = [];

	static function randColor($group) {
		return self::$colored[$group] ?? (self::$colored[$group] = sprintf('#%06X', rand(0, 0xFFFFFF)));
	}

	static function color($group) {
		return self::$colors[ $group % count(self::$colors) ];
	}

	static function debugTable($grid) {
		echo "\n<!-- " . json_encode($grid) . " -->\n";
		echo '<table>';
		foreach ($grid as $y => $row) {
			echo '<tr>';
			foreach ($row as $x => $cell) {
				list($group, $size) = $cell;
				$color = $cell == -1 ? '#000' : self::randColor($group);
				echo '<td bgcolor="' . $color . '">' . $group . '</td>';
			}
			echo '</tr>';
		}
		echo "</table>\n";
		echo "<br>\n\n";
	}

	static public function create($width, $height) {
		for ($attempts=1; $attempts < 5000; $attempts++) {
			if ($grid = self::_create($width, $height)) {
var_dump($attempts);
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
// var_dump($leftX, $leftY);

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
// self::debugTable($grid);
// echo "\n\n";
			$group++;
		}
// ksort($sizes);
// print_r($sizes);

		// Max number of 1's
		if (isset($sizes[1]) && $sizes[1] > $width * $height / 10) {
			return;
		}

		// No neighboring N's
		if (!self::validateUniqueNeighbors($grid)) {
			return;
		}

		return $grid;
	}

	static public function validateUniqueNeighbors($grid) {
		foreach ($grid as $y => $cols) {
			foreach ($cols as $x => list($group, $size, $dir)) {
				foreach (self::neighbors($grid, $x, $y) as list($nx, $ny)) {
					list($ngroup, $nsize, $ndir) = $grid[$ny][$nx];
					if ($nsize == $size && $ngroup != $group && $ndir == $dir) {
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
