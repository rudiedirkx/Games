<?php
// PICROSS

require __DIR__ . '/inc.functions.php';

$g_arrMaps = getMaps();
if (!($level = getLevelFromInput($map))) {
	$level = getLevel();
	$map = prepareMap($g_arrMaps[$level]);
}

$levelName = $level == 999 ? hashMap($map) : $level;

if (isset($_POST['cheat'])) {
	header('Content-type: text/json');
	exit(strtr(json_encode(array('map' => $map['map'])), ['x' => 1, '_' => 0]));
}

?>
<!doctype html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>PICROSS <?= $levelName ?></title>
	<link rel="stylesheet" href="119.css" />
</head>

<body>
	<table id="picross">
		<thead>
			<tr>
				<th colspan="40">
					<button id="reset">reset</button>
					<button id="cheat">cheat</button>
					<a class="<?= !isset($g_arrMaps[$level-1]) ? 'disabled' : '' ?>" href="?level=<?= $level-1 ?>">&lt;&lt;</a> &nbsp;
					Level <?= $levelName ?> &nbsp;
					<a class="<?= !isset($g_arrMaps[$level+1]) ? 'disabled' : '' ?>" href="?level=<?= $level+1 ?>">&gt;&gt;</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($map['map'] as $y => $line): ?>
				<tr>
					<? for ($x=0; $x < strlen($line); $x++): ?>
						<td><a href="#">x</a></td>
					<? endfor ?>
					<th class="hor" data-hints="<?= implode(',', $map['hor'][$y]) ?>">
						<span><?= implode('</span> <span>', $map['hor'][$y]) ?></span>
					</th>
				</tr>
			<? endforeach ?>
			<tr>
				<? for ($x=0; $x < strlen($map['map'][0]); $x++): ?>
					<th class="ver" data-hints="<?= implode(',', $map['ver'][$x]) ?>">
						<span><?= implode('</span> <span>', $map['ver'][$x]) ?></span>
					</th>
				<? endfor ?>
				<th></th>
			</tr>
		</tbody>
	</table>

	<p><a href="119B.php">Build your own</a></p>

	<p>Solution: <a id="export" href="#">export</a> | <a id="import" href="#">import</a></p>

	<script src="119.js"></script>
	<script>
	var solution = '<?= hashMap($map) ?>';

	var states = ['', 'active', 'inactive'];
	var tbody = document.querySelector('tbody');
	var winner, validator;
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

					sessionStorage.removeItem('g119_' + solution);

					setTimeout(function() {
						alert('YOU WIN!');
					});
				}, 500);
			}
			else {
				var map = g119.map(tbody, true);
				sessionStorage.setItem('g119_' + solution, map);

				// Check validity for made move:
				// Check X axis for cell.cellIndex
				// Check Y axis for cell.parentNode.sectionRowIndex

				// clearTimeout(validator);
				validator = setTimeout(function() {
					var line, hints, valid;
					line = g119.getLineForRow(tbody, cell.parentNode.sectionRowIndex);
					hints = g119.getHintsForRow(tbody, cell.parentNode.sectionRowIndex);
					valid = g119.validLine(line, hints);
					g119.getMetaCellForRow(tbody, cell.parentNode.sectionRowIndex).classList.toggle('invalid', !valid);

					line = g119.getLineForColumn(tbody, cell.cellIndex);
					hints = g119.getHintsForColumn(tbody, cell.cellIndex);
					valid = g119.validLine(line, hints);
					g119.getMetaCellForColumn(tbody, cell.cellIndex).classList.toggle('invalid', !valid);
				});
			}
		}
	});

	document.querySelector('#reset').addEventListener('click', function(e) {
		sessionStorage.removeItem('g119_' + solution);
		location.reload();
	});

	document.querySelector('#cheat').addEventListener('click', function(e) {
		var xhr = new XMLHttpRequest;
		xhr.open('post', location.href, true);
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.onload = function(e) {
			var rsp = JSON.parse(this.responseText);
			[].forEach.call(tbody.querySelectorAll('td'), function(cell) {
				var x = cell.cellIndex;
				var y = cell.parentNode.sectionRowIndex;
				var state = g119.getShortState(cell.dataset.state, true);
				cell.classList.toggle('invalid', state != '_' && state != rsp.map[y][x]);
			});
		};
		xhr.send('cheat=1');
	});

	document.querySelector('#export').addEventListener('click', function(e) {
		e.preventDefault();

		var map = g119.map(tbody, 1);
		prompt('Copy this:', map);
	});

	document.querySelector('#import').addEventListener('click', function(e) {
		e.preventDefault();

		var map = prompt('Paste an export:', '');
		if (map) {
			var bw = map.match(/^(\d+)\.([\d_]+)$/);
			if (bw) {
				var cells = tbody.querySelectorAll('td');
				for (var i=0; i<bw[2].length; i++) {
					if (cells[i]) {
						cells[i].dataset.state = g119.charToState(bw[2][i]);
					}
				}
			}
		}
	});

	var saved = sessionStorage.getItem('g119_' + solution);
	if (saved) {
		var map = saved.split('.');
		if (map[0] == tbody.rows[0].querySelectorAll('td').length) {
			var _states = ['inactive', 'active'];
			[].forEach.call(tbody.querySelectorAll('td'), function(cell, i) {
				if (_states[ map[1][i] ]) {
					cell.dataset.state = _states[ map[1][i] ];
				}
			});
		}
	}

	setTimeout(function() {
		var w = tbody.rows[0].querySelectorAll('td').length;
		var h = tbody.rows.length - 1;
		[].forEach.call(tbody.querySelectorAll('th[data-hints]'), function(cell) {
			var length = cell.classList.contains('hor') ? w : h;
			var options = g119.options(length, g119.getHintsForCell(cell)).length;
			cell.title = options + ' possible lines';
		});
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
