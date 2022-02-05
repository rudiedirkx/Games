<?php
// PICROSS BUILDER

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>PICROSS BUILDER</title>
	<link rel="stylesheet" href="<?= html_asset('119.css') ?>" />
</head>

<body>
	<table id="picross">
		<thead>
			<tr>
				<th colspan="40">
					<button id="random">
						RANDOM
						<span class="random-progress">
							(<span id="random-attempt">0</span>: <span id="random-diff">0</span>)
						</span>
					</button>
					| Build-a-level |
					<button id="play">PLAY</button>
				</th>
			</tr>
		</thead>
		<tbody>
			<? for ($y=0; $y < 15; $y++): ?>
				<tr>
					<? for ($x=0; $x < 15; $x++): ?>
						<td data-state="inactive"><a href="#">&nbsp;</a></td>
					<? endfor ?>
				</tr>
			<? endfor ?>
		</tbody>
	</table>

	<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
	<script src="<?= html_asset('gridgame.js') ?>"></script>
	<script src="<?= html_asset('119.js') ?>"></script>
	<script>
	var ACTIVES = 0.55;

	var states = ['active', 'inactive'];

	var tbody = document.querySelector('tbody');
	tbody.addEventListener('click', function(e) {
		if (e.target.nodeName == 'A') {
			e.preventDefault();

			var cell = e.target.parentNode;
			g119.click(cell, states);
		}
	});

	document.querySelector('#play').addEventListener('click', function(e) {
		var map = g119.map(tbody);
		location = '119.php#' + encodeURIComponent(map);
	});

	localStorage.picrossDifficulty && localStorage.picrossDifficulty.match(/^\d+\-\d+$/) || (localStorage.picrossDifficulty = '13-18');
	var randomAttempt;
	function tryRandom() {
		[].forEach.call(tbody.querySelectorAll('td'), function(cell) {
			cell.dataset.state = Math.random() < ACTIVES ? 'active' : 'inactive';
		});

		var difficulty = g119.difficulty(tbody, true);
		randomAttempt++;

		document.querySelector('#random').classList.add('show');
		document.querySelector('#random-attempt').textContent = randomAttempt;
		document.querySelector('#random-diff').textContent = difficulty;

		setTimeout(function() {
			var diffTarget = localStorage.picrossDifficulty.split('-');
			if (difficulty < diffTarget[0] || difficulty > diffTarget[1]) {
				tryRandom();
			}
			else {
				document.querySelector('#play').click();
			}
		}, 10);
	}
	document.querySelector('#random').addEventListener('click', function(e) {
		randomAttempt = 0;
		tryRandom();
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
