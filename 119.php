<?php
// PICROSS

require __DIR__ . '/inc.functions.php';

$g_arrMaps = getMaps();
if (!($level = getLevelFromInput($map))) {
	$level = getLevel();
	$map = prepareMap($g_arrMaps[$level]);
}

?>
<!doctype html>
<html>

<head>
	<title>PICROSS</title>
	<link rel="stylesheet" href="119.css" />
</head>

<body>
	<table id="picross">
		<thead>
			<tr>
				<th colspan="40">
					<a class="<?= !isset($g_arrMaps[$level-1]) ? 'disabled' : '' ?>" href="?level=<?= $level-1 ?>">&lt;&lt;</a> &nbsp;
					Level <?= $level ?> &nbsp;
					<a class="<?= !isset($g_arrMaps[$level+1]) ? 'disabled' : '' ?>" href="?level=<?= $level+1 ?>">&gt;&gt;</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($map['map'] as $y => $line): ?>
				<tr>
					<? for ($x=0; $x < strlen($line); $x++): ?>
						<td><a href="#">&nbsp;</a></td>
					<? endfor ?>
					<th class="hor"><span><?= implode('</span> <span>', $map['hor'][$y]) ?></span></th>
				</tr>
			<? endforeach ?>
			<tr>
				<? for ($x=0; $x < strlen($map['map'][0]); $x++): ?>
					<th class="ver"><span><?= implode('</span> <span>', $map['ver'][$x]) ?></span></th>
				<? endfor ?>
				<th></th>
			</tr>
		</tbody>
	</table>

	<p><a href="119B.php">Build your own</a></p>

	<script src="119.js"></script>
	<script>
	var solution = '<?= hashMap($map) ?>';

	var states = ['', 'active', 'inactive'];
	var tbody = document.querySelector('tbody');
	var winner;
	tbody.addEventListener('click', function(e) {
		if (e.target.nodeName == 'A') {
			e.preventDefault();

			var cell = e.target.parentNode;
			g119.click(cell, states);

			clearTimeout(winner);
			var hash = g119.shash(g119.map(tbody));
			if (hash == solution) {
				winner = setTimeout(function() {
					[].forEach.call(tbody.querySelectorAll('td:not([data-state="active"]):not([data-state="inactive"])'), function(cell) {
						cell.dataset.state = 'inactive';
					});

					setTimeout(function() {
						alert('YOU WIN!');
					});
				}, 500);
			}
		}
	});
	</script>

</body>

</html>
<?php

function getLevel() {
	global $g_arrMaps;
	return isset($_GET['level'], $g_arrMaps[ (int) $_GET['level'] ]) ? (int) $_GET['level'] : 1;
}

function prepareMap($map) {
	$hor = prepareAxis($map, true);
	$ver = prepareAxis($map, false);

	return compact('map', 'hor', 'ver');
}

function hashMap($map) {
	$width = strlen($map['map'][0]);
	$cells = rtrim(strtr(implode($map['map']), ['_' => 0, 'x' => 1]), '0');
	return shash($width . '.' . $cells);
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

function getLevelFromInput(&$map) {
	if (isset($_GET['play']) && is_string($_GET['play'])) {
		if (preg_match('#^(\d+)\.([01]+)$#', $_GET['play'], $match)) {
			$lines = str_split(strtr($match[2], ['_', 'x']), $match[1]);
			$max = array_reduce($lines, function($max, $line) {
				return max($max, strlen(rtrim($line, '_')));
			});
			$lines = array_map(function($line) use ($max) {
				return substr($line . str_repeat('_', $max), 0, $max);
			}, $lines);

			$map = prepareMap($lines);
			return 999;
		}
	}
}

function getMaps() {
	return array(
		1 => array(
			'xxxx',
			'x__x',
			'__xx',
			'_x__',
		),
		2 => array(
			'xx_x',
			'_xxx',
			'xxx_',
			'x_xx',
		),
		3 => array(
			'_xxxxx_',
			'_x_x_x_',
			'_xxxxx_',
			'___x___',
			'_xxxxx_',
			'_x_x_xx',
			'___x___',
			'_xxxxx_',
			'_x___x_',
			'xx___xx',
		),
		4 => array(
			'xxxxxxxxxxxxxx_',
			'x__xxxxxxxxx__x',
			'x__xxxxx__xx__x',
			'x__xxxxx__xx__x',
			'x__xxxxx__xx__x',
			'x__xxxxxxxxx__x',
			'x_____________x',
			'x_xxxxxxxxxxx_x',
			'x_x_________x_x',
			'x_x_xxxxxxx_x_x',
			'x_x_________x_x',
			'x_x_xxxxxxx_x_x',
			'x_x_________x_x',
			'x_x_________x_x',
			'xxxxxxxxxxxxxxx',
		),
		5 => array(
			'xxx_xxxxxxxxxxx',
			'xx___xxxxxxxxxx',
			'x___xxxxxxxxxxx',
			'___xxxxxxxxxxxx',
			'__xxxxxx___xxxx',
			'___xxxx_____xxx',
			'x___xx__xx__xxx',
			'xx_xx__xxx__xxx',
			'xxxx__xxx__xx_x',
			'xxxx__xx__xx___',
			'xxxxx____xx___x',
			'xxxxxx__xx___xx',
			'xxxxxxxxx___xxx',
			'xxxxxxxxxx___xx',
			'xxxxxxxxxxx___x',
		),
	);
}
