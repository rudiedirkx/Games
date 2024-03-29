<?php
// PICROSS

require __DIR__ . '/inc.bootstrap.php';

$g_arrMaps = getMaps();
$level = isset($_GET['level'], $g_arrMaps[$_GET['level']]) ? $_GET['level'] : 1;
$map = $g_arrMaps[$level];
$serialized = strlen($map[0]) . '.' . strtr(implode('', $map), ['x' => 1, '_' => 0]);

if (isset($_POST['cheat'])) {
	header('Content-type: text/json');
	exit(strtr(json_encode(array('map' => $map['map'])), ['x' => 1, '_' => 0]));
}

?>
<!doctype html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>PICROSS</title>
	<link rel="stylesheet" href="<?= html_asset('119.css') ?>" />
	<? include 'tpl.onerror.php' ?>
</head>

<body>
	<p>
		<a class="<?= !isset($g_arrMaps[$level-1]) ? 'disabled' : '' ?>" href="?level=<?= $level-1 ?>">&lt;&lt;</a>
		Level <span id="levelname"></span> (<span id="difficulty">?</span>&starf;)
		<a class="<?= !isset($g_arrMaps[$level+1]) ? 'disabled' : '' ?>" href="?level=<?= $level+1 ?>">&gt;&gt;</a>
		&nbsp;
		<button id="undo">undo (<span id="undo-steps">0</span>)</button>
		&nbsp;
		<!-- <button id="cheat1">cheat 1<span class="loading"> ...</span></button> -->
		<button id="cheat2">cheat 2</button>
		<button id="reset">reset</button>
	</p>
	<table id="picross"></table>

	<p><a href="119B.php">Build your own</a></p>

	<p>
		Solution:
		| <a id="export" href="#" title="Export to string">export</a>
		| <a id="import" href="#" title="Import exported string">import</a>
		| <a id="save" href="#" title="Save to the public cloud">save</a>
		| <a id="load" href="#" title="Load from the public cloud">load</a>
	</p>

	<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
	<script src="<?= html_asset('gridgame.js') ?>"></script>
	<script src="<?= html_asset('119.js') ?>"></script>
	<script>
	const tbody = document.querySelector('table');
	g119.serialized = location.hash.substr(1) || <?= json_encode($serialized) ?>;

	const grid = g119.serToGrid(g119.serialized);
	g119.buildEmptyTable(tbody, grid);
	// g119.gridToTable(tbody, grid);
	const groups = g119.gridToGroups(grid);
	g119.groupsToTable(tbody, groups);

	g119.solution = g119.shash(g119.serialized);
	document.title += ' ' + g119.solution;

	var $undoSteps = document.querySelector('#undo-steps');

	var states = ['', 'active', 'inactive'];
	var winner;
	var handle = function(e) {
		if (e.target.nodeName == 'A') {
			e.preventDefault();

			var cell = e.target.parentNode;
			g119.click(cell, states);
			$undoSteps.textContent = g119.history.length;

			clearTimeout(winner);

			g119.validateFromCell(cell);

			var solved = g119.solvedGrid(tbody);
			if (solved) {
				winner = setTimeout(function() {
					[].forEach.call(tbody.querySelectorAll('td:not([data-state="active"]):not([data-state="inactive"])'), function(cell) {
						cell.dataset.state = 'inactive';
					});

					setTimeout(function() {
						var xhr = new XMLHttpRequest;
						var query = [
							'store=' + encodeURIComponent(location.host),
							'delete=solutions.' + encodeURIComponent(g119.solution),
						].join('&');
						xhr.open('get', 'https://store.webblocks.nl/?' + query, true);
						xhr.send();

						sessionStorage.removeItem('g119_' + g119.solution);

						alert('YOU WIN!');
					}, 40);
				}, 800);
			}
		}
	};
	if ('ontouchstart' in document.body) {
		var touchElement;
		tbody.addEventListener('touchstart', function(e) {
			touchElement = e.target;
		});
		tbody.addEventListener('touchmove', function(e) {
			touchElement = null;
		});
		tbody.addEventListener('touchend', function(e) {
			if (touchElement) {
				handle.call(this, e);
			}
		});
		tbody.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
		});
	}
	else {
		tbody.addEventListener('click', function(e) {
			handle.call(this, e);
		});
	}

	var handle2 = function(e) {
		var cell = e.target.closest('td, th.meta');
		if (cell) {
			[].forEach.call(tbody.querySelectorAll('.hilite'), function(cell) {
				cell.classList.remove('hilite');
			});

			var col = cell.cellIndex + 1;
			var row = cell.parentNode.sectionRowIndex + 1;
			var selector = ['tr:not(.meta):nth-child(' + row + ') > *'];
			if (col != cell.parentNode.cells.length) {
				selector.push('tr > :nth-child(' + col + ')');
			}
			[].forEach.call(tbody.querySelectorAll(selector.join(', ')), function(cell) {
				cell.classList.add('hilite');
			});
		}
	};
	tbody.addEventListener('mouseover', handle2);
	tbody.addEventListener('touchstart', handle2);

	document.querySelector('#undo').addEventListener('click', function(e) {
		var cell = g119.history.pop();
		if (cell) {
			g119.click(cell, states, true);
			$undoSteps.textContent = g119.history.length;
			g119.validateFromCell(cell);
		}

		document.activeElement.blur();
	});

	var difficulty = g119.difficulty(tbody);
	document.querySelector('#difficulty').textContent = difficulty;
	document.title += ' (' + difficulty + ')';

	document.querySelector('#reset').addEventListener('click', function(e) {
		g119.reset(tbody);
		$undoSteps.textContent = g119.history.length;

		document.activeElement.blur();
	});

	// document.querySelector('#cheat1').addEventListener('click', function(e) {
	// 	this.classList.add('loading');

	// 	var xhr = new XMLHttpRequest;
	// 	xhr.button = this;
	// 	xhr.open('post', location.href, true);
	// 	xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	// 	xhr.onload = function(e) {
	// 		var rsp = JSON.parse(this.responseText);
	// 		[].forEach.call(tbody.querySelectorAll('td'), function(cell, i) {
	// 			var x = cell.cellIndex;
	// 			var y = cell.parentNode.sectionRowIndex;
	// 			var state = g119.stateToChar(cell.dataset.state, true);
	// 			cell.classList[state != '_' && state != rsp.map[y][x] ? 'add' : 'remove']('invalid');
	// 		});

	// 		this.button.classList.remove('loading');
	// 	};
	// 	xhr.send('cheat=1');
	// });

	document.querySelector('#cheat2').addEventListener('click', function(e) {
		var rows = tbody.rows.length - 1;
		for (var i=0; i<rows; i++) {
			g119.fillRowWithLine(tbody, i, g119.commonCells(g119.validLines(g119.getLineForRow(tbody, i), g119.getHintsForRow(tbody, i))));
		}

		var cols = tbody.rows[0].cells.length - 1;
		for (var i=0; i<cols; i++) {
			g119.fillColumnWithLine(tbody, i, g119.commonCells(g119.validLines(g119.getLineForColumn(tbody, i), g119.getHintsForColumn(tbody, i))));
		}

		document.activeElement.blur();
	});

	document.querySelector('#export').addEventListener('click', function(e) {
		e.preventDefault();

		var map = g119.map(tbody, 1);
		prompt('Copy this:', map);

		document.activeElement.blur();
	});

	function importString(map) {
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

	document.querySelector('#import').addEventListener('click', function(e) {
		e.preventDefault();

		var map = prompt('Paste an export:', '');
		map && importString(map);

		document.activeElement.blur();
	});

	document.querySelector('#save').addEventListener('click', function(e) {
		e.preventDefault();

		this.classList.add('loading');

		var map = g119.map(tbody, 1);
		var query = [
			'store=' + encodeURIComponent(location.host),
			'put=solutions.' + encodeURIComponent(g119.solution),
			'value=' + encodeURIComponent(JSON.stringify(map)),
		].join('&');

		var xhr = new XMLHttpRequest;
		xhr.link = this;
		xhr.open('post', 'https://store.webblocks.nl/?' + query, true);
		xhr.onload = function(e) {
			this.link.classList.remove('loading');
		};
		xhr.send();

		document.activeElement.blur();
	});

	function loadFromStore(done) {
		var query = [
			'store=' + encodeURIComponent(location.host),
			'get=solutions.' + encodeURIComponent(g119.solution),
		].join('&');

		var xhr = new XMLHttpRequest;
		xhr.link = this;
		xhr.open('post', 'https://store.webblocks.nl/?' + query, true);
		xhr.onload = function(e) {
			var rsp = JSON.parse(this.responseText.substr(9));
			if (rsp.exists) {
				importString(rsp.value);
			}

			done && done();
		};
		xhr.send();
	}

	document.querySelector('#load').addEventListener('click', function(e) {
		e.preventDefault();

		var a = this;
		a.classList.add('loading');
		loadFromStore(function() {
			a.classList.remove('loading');
		});

		document.activeElement.blur();
	});

	var saved = sessionStorage.getItem('g119_' + g119.solution);
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
	else {
		loadFromStore();
	}

	setTimeout(function() {
		console.time('validateTable');
		g119.validateTable(tbody);
		console.timeEnd('validateTable');

		console.time('hints');
		var w = tbody.rows[0].querySelectorAll('td').length;
		var h = tbody.rows.length - 1;
		[].forEach.call(tbody.querySelectorAll('th[data-hints]'), function(cell) {
			var length = cell.classList.contains('hor') ? w : h;
			var options = g119.options(length, g119.getHintsForCell(cell)).length;
			cell.title = options + ' possible lines';
		});
		console.timeEnd('hints');
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
	return shash(json_encode($map));
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

		if (preg_match('#^(\d+)\.([\d\. ]+)$#', $_GET['play'])) {
			$cells = explode('.', $_GET['play']);
			$size = array_shift($cells);
			$cells = array_map(function($cell) {
				return array_values(array_filter(explode(' ', $cell)));
			}, $cells);

			$ver = array_splice($cells, -$size);
			$map = ['hor' => $cells, 'ver' => $ver];
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
