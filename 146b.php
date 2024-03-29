<?php
// SLITHER 2

require __DIR__ . '/inc.bootstrap.php';

$g_arrBoards = require '146_levels.php';

$allBoards = array();
foreach ($g_arrBoards as $difficulty => $boards) {
	$allBoards = array_merge($allBoards, array_map(function($board) use ($difficulty) {
		return [
			'difc' => $difficulty,
			'map' => array_map(function($line) use ($board) {
				return str_pad($line, $board['size'][0], ' ');
			}, $board['board']),
		];
	}, $boards));
}

// print_r($allBoards);

?>
<!doctype html>
<html>

<head>
	<title>SLITHER 2</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<style>
	* {
		-webkit-user-select: none;
	}
	canvas {
		background: #bde4a3;
		width: 100%;
		max-width: 500px;
		-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
	}
	#red {
		display: none;
		position: absolute;
		width: 6px;
		height: 6px;
		margin: -3px 0 0 -3px;
		background-color: red;
		pointer-events: none;
	}
	</style>
	<? include 'tpl.onerror.php' ?>
</head>

<body>

<canvas width="300" height="300">No CANVAS?</canvas>

<p>
	<a id="restart" href="#">Restart</a>,
	<a id="share" href="#">share</a>,
	or switch level:
	<select id="lvl"><?= implode('', array_map(function($board, $n) use ($allBoards) {
		return '<option value="' . $n . '">' . $board['difc'] . ' ' . ($n + 1) . ' / ' . count($allBoards);
	}, $allBoards, array_keys($allBoards))) ?></select>
</p>
<p>Click between dots to connect a slither and make every cell have the right number of connectors. White numbers = good.</p>

<div id="red"></div>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('146b.js') ?>"></script>
<script>
Array.repeat = function(length, value) {
	var arr = [length];
	for (var i=0; i<length; i++) {
		arr[i] = value;
	}
	return arr;
};

var _LEVEL = 1;

(function(levels) {
	// State
	// var lvl;
	var o;
	// var connectors;
	// var disabledConnectors;
	// var conditions;
	var gameover;
	var slithering;

	// Start
	$(init);

	function init() {
		var level = stringToLevel(location.hash);
		level || (level = {n: _LEVEL, prep: ''});
		initLevel(level.n, level.prep);

		elCanvas.on('contextmenu', function(e) {
			e.preventDefault();

			$('#red').css(e.pageXY.toCSS());

			var zoom = this.offsetWidth / _w;
			var c = e.subjectXY.multiply(1/zoom);

			var connector = getClosestConnector(c);
			if (connector) {
				var hilited = disableConnector(connector, true);
				drawLevel();
			}
		});

		elCanvas.on(evType, function(e) {
			$('#red').css(e.pageXY.toCSS());

			if ( gameover ) return;

			var zoom = this.offsetWidth / _w;
			var c = e.subjectXY.multiply(1/zoom);

			var connector = getClosestConnector(c);
			if (connector) {
				var hilited = hiliteConnector(connector, true);
				if (hilited != null) updateConditions(connector, hilited);
				drawLevel();
				checkWinStatus();
			}
		});
	}

	function stringToLevel(hash) {
		var match = hash.replace(/^#+/g, '').match(/^(\d+)(?:\.(\d+))?$/);
		if ( match ) {
			return {
				n: parseInt(match[1]),
				prep: match[2] || '',
			};
		}
	}

	function levelToString(n, prep) {
		return String(n) + (prep ? '.' + prep : '');
	}

	$('#lvl').on('change', function(e) {
		if ( getMap(this.value) ) {
			initLevel(this.value);
		}
	});

	$('#restart').on('click', function(e) {
		e.preventDefault();
		initLevel(lvl.n);
	});

	$('#share').on('click', function(e) {
		e.preventDefault();

		var bytes = Array.repeat((lvl.width+1) * (lvl.height+1), 0);
		for (var i=0; i<connectors.length; i++) {
			var con = Connector.fromString(connectors[i]);
			var index = con.y * (lvl.width+1) + con.x;
			bytes[index] += con.dir == 'hor' ? 1 : 2;
		}
		bytes = bytes.join('').replace(/0+$/g, '');
		location.hash = String(lvl.n) + '.' + bytes;
	});

	window.on('hashchange', function() {
		var level = stringToLevel(location.hash);
		if ( level ) {
			initLevel(level.n, level.prep);
		}
	});

	// Process
	function initLevel(n, prep) {
		clearInterval(slithering);
		location.hash = levelToString(n, prep);
		$('#lvl').value = n;

		lvl = getLevel(n);
		lvl.connectors = getAllConnectors();

		connectors = [];
		disabledConnectors = [];
		conditions = {};

		// Fill connectors from URL
		if ( prep ) {
			// drawLevel();
			for (var i=0; i<prep.length; i++) {
				var byte = parseInt(prep[i]);
				if ( byte ) {
					var x = i % (lvl.width+1);
					var y = Math.floor(i / (lvl.width+1));
					var hor = byte & 1;
					var ver = byte & 2;
					if ( hor ) {
						var con = new Connector(x, y, 'hor');
						connectors.push(con.toString());
						updateConditions(con, true);
					}
					if ( ver ) {
						var con = new Connector(x, y, 'ver');
						connectors.push(con.toString());
						updateConditions(con, true);
					}
				}
			}
		}

		drawLevel();

		gameover = false;
	}

	function updateConditions(connector, hilited) {
		var cs = getNeighborCells(connector),
			change = hilited ? 1 : -1;
		r.each(cs, function(c) {
			c = c.join('-');
			if ( conditions[c] == null ) {
				conditions[c] = 0;
			}
			conditions[c] += change;
		});
	}

	function checkWinStatus() {
		var slither = getWinStatus();
		if ( slither ) {
			setTimeout(function() {
				win(slither);
			}, 20);
		}
	}

	function getWinStatus() {
		return checkConditions() && checkSlither();
	}

	function checkConditions() {
		var	wrong = 0;
		lvl.map.each(function(cells, y) {
			cells.each(function(target, x) {
				if ( target != null ) {
					var current = conditions[String(x) + '-' + String(y)] || 0;
					if ( current != target ) {
						wrong++;
					}
				}
			});
		});
		return wrong == 0;
	}

	function checkSlither() {
		if ( connectors.length < 4 ) return false;

		var cons = JSON.parse(JSON.stringify(connectors));

		var first = Connector.fromString(cons.shift());

		var slither = [first];

		var last = first, next;
		while ( true ) {
			var mustHave = slither.length == 1 ? 2 : 1;
			next = last.findNextIn(cons, mustHave);

			// No next => end of slither => win or lose
			if ( !next ) {

				// All connectors have been matched
				if ( slither.length == connectors.length ) {
					if ( first.touches(last) ) {
						return slither;
					}
				}

				return false;
			}

			slither.push(next);
			last = next;
		}

		return false;
	}

	function win(slither) {
		gameover = true;

		var index = -1;
		var iterate = function() {
			var prev = slither[index];
			if ( !slither[++index] ) index = 0;
			var current = slither[index];

			if ( prev ) {
				hiliteConnector(prev, 'pink');
			}
			hiliteConnector(current, 'red');
		};
		iterate();
		slithering = setInterval(iterate, 50);
	}

	function drawLevel() {
		ctx.clearRect(0, 0, _w, _h);
		initGrid(lvl);
		drawGrid(lvl);
		drawNumbers(lvl);
		hiliteConnectors();
	}

	function drawNumbers(lvl) {
		for ( var y=0; y<lvl.height; y++ ) {
			for ( var x=0; x<lvl.width; x++ ) {
				var number = lvl.map[y][x];
				if ( number != null ) {
					var c = getCellCoords(x, y);

					// Draw to canvas
					var loc = new Coords2D(x, y);
					drawNumber(c, number, loc);
				}
			}
		}
	}

	function getLevel(n) {
		var map = getMap(n);
		map = prepMap(map);
		return {
			n: n,
			map: map,
			width: map[0].length,
			height: map.length
		};
	}

	function getMap(n) {
		return levels[n];
	}

	function prepMap(map) {
		map = map.map(function(row) {
			var cells = [];
			for ( var i=0, L=row.length; i<L; i++ ) {
				var n = row[i].trim();
				cells.push(n == '' ? null : parseFloat(n));
			}
			return cells;
		});
		return map;
	}

})(<?= json_encode(array_column($allBoards, 'map')) ?>);
</script>

</body>

</html>
