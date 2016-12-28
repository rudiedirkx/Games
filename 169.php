<?php
// RECTANGLES

echo "RECTANGLES<br>\n";

$width = 6;
$height = 8;

$_time = microtime(1);
$grid = Rectangles::create($width, $height);
$_time = microtime(1) - $_time;
// print_r($grid);
Rectangles::table($grid);
var_dump(round($_time * 1000, 3));

class Rectangles {

	static function table($grid) {
		$colors = ['#f00', '#ff0', '#0c0', '#0cc', '#00c', 'pink', 'purple'];

		echo '<br><table cellpadding="10" cellspacing="1">';
		foreach ($grid as $y => $row) {
			echo '<tr>';
			foreach ($row as $x => $group) {
				$color = $group == -1 ? '#000' : $colors[ $group % count($colors) ];
				echo '<td bgcolor="' . $color . '"></td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}

	static public function create($width, $height) {
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

			for ($y=0; $y < $sizeY; $y++) {
				for ($x=0; $x < $sizeX; $x++) {
					$grid[ $y + $next[1] ][ $x + $next[0] ] = $group;
				}
			}
// self::table($grid);

			$group++;
		}

// print_r($sizes);

		if (isset($sizes[1]) && $sizes[1] > 4) {
			return self::create($width, $height);
		}

		return $grid;
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
