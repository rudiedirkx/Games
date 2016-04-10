<?php
// PICROSS BUILDER

require __DIR__ . '/inc.functions.php';

?>
<!doctype html>
<html>

<head>
	<title>PICROSS BUILDER</title>
	<link rel="stylesheet" href="119.css" />
</head>

<body>
	<table id="picross">
		<thead>
			<tr>
				<th colspan="40">
					Build-a-level |
					<button id="play">PLAY</button>
				</th>
			</tr>
		</thead>
		<tbody>
			<? for ($y=0; $y < 15; $y++): ?>
				<tr>
					<? for ($x=0; $x < 15; $x++): ?>
						<td data-state="inactive" data-state-index="1"><a href="#">&nbsp;</a></td>
					<? endfor ?>
				</tr>
			<? endfor ?>
		</tbody>
	</table>

	<script src="119.js"></script>
	<script>
	var states = ['active', 'inactive'];
	document.querySelector('tbody').addEventListener('click', function(e) {
		if (e.target.nodeName == 'A') {
			e.preventDefault();

			var cell = e.target.parentNode;
			g119.click(cell, states);
		}
	});

	document.querySelector('#play').addEventListener('click', function(e) {
		var grid = document.querySelector('tbody');
		var map = g119.map(grid);
		location = '119.php?play=' + encodeURIComponent(map);
	});
	</script>

</body>

</html>
<?php

function getLevel() {
	global $g_arrMaps;
	return isset($_GET['level'], $g_arrMaps[ (int) $_GET['level'] ]) ? (int) $_GET['level'] : 1;
}

function prepareLevel($level) {
	global $g_arrMaps;

	$map = $g_arrMaps[$level];

	$hor = prepareAxis($map, true);
	$ver = prepareAxis($map, false);

	return compact('map', 'hor', 'ver');
}

function prepareAxis($map, $hor) {
	$d1max = $hor ? count($map) : strlen($map[0]);
	$d2max = $hor ? strlen($map[0]) : count($map);

	$streak = false;
	$numbers = [];
	for ( $d1=0; $d1 < $d1max; $d1++ ) {
		if ($d1 > 0) {
			$streak = false;
			$numbers[] = '';
		}

		for ( $d2=0; $d2 < $d2max; $d2++ ) {
			$y = $hor ? $d1 : $d2;
			$x = $hor ? $d2 : $d1;

			if ( $map[$y][$x] == 'x' ) {
				if ( !$streak ) {
					$streak = true;
					$numbers[] = 0;
				}
				$numbers[ count($numbers)-1 ]++;
			}
			else {
				$streak = false;
			}
		}
	}

	$lines = [[]];
	foreach ( $numbers as $number ) {
		if ( !$number ) {
			$lines[] = [];
		}
		else {
			$lines[ count($lines)-1 ][] = $number;
		}
	}

	return $lines;
}
