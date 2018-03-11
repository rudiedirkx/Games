<?php
// SLITHER

require 'inc.functions.php';

$g_arrBoards = require '146_levels.php';
$allBoards = array();
foreach ($g_arrBoards as $difficulty => $boards) {
	$allBoards = array_merge($allBoards, array_map(function($board) {
		return array_merge(array_map(function($line) use ($board) {
			return str_pad($line, $board['size'][0], ' ');
		}, $board['board']), array_fill(0, $board['size'][1] - count($board['board']), str_repeat(' ', $board['size'][0])));
	}, $boards));
}

// var_dump($allBoards);

?>
<!doctype html>
<html>

<head>
	<title>Slither CANVAS</title>
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
		position: absolute;
		width: 6px;
		height: 6px;
		margin: -3px 0 0 -3px;
		background-color: red;
		pointer-events: none;
	}
	</style>
	<script>window.onerror = function(e) { alert(e); };</script>
</head>

<body>

<canvas width="300" height="300">No CANVAS?</canvas>

<p>
	<a id="restart" href="#">Restart</a>,
	<a id="share" href="#">share</a>,
	or switch level:
	<a class="goto" data-prev href="#">&lt; prev</a> |
	<span id="lvl">?</span> |
	<a class="goto" data-next href="#">next &gt;</a>
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
	// var conditions;
	var gameover;
	var slithering;

	// Start
	$(init);

	function init() {
		var level = stringToLevel(location.hash);
		level || (level = {n: _LEVEL, prep: ''});
		initLevel(level.n, level.prep);

		elCanvas.on(evType, function(e) {
			$('#red').css(e.pageXY.toCSS());

			if ( gameover ) return;

			var zoom = this.offsetWidth / _w;
			var c = e.subjectXY.multiply(1/zoom);

			var connector = getClosestConnector(c);
			if (connector) {
				var hilited = hiliteConnector(connector, true);
				updateConditions(connector, hilited);
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

	$$('.goto').on('click', function(e) {
		e.preventDefault();
		var d = this.data('prev') != null ? -1 : 1;
		if ( getMap(lvl.n + d) ) {
			initLevel(lvl.n + d);
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
		$('#lvl').setText(n);

		lvl = getLevel(n);
		lvl.connectors = getAllConnectors();

		connectors = [];
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

		var first = Connector.fromString(cons[0]);
		delete cons[0];

		var slither = [first];

		var last = first, next;
		while ( true ) {
			next = last.findNextIn(cons);

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

})(<?= json_encode($allBoards) ?>);
</script>

</body>

</html>
