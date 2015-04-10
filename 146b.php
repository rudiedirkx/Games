<!doctype html>
<html>

<head>
	<title>Slither CANVAS</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.5" />
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
</head>

<body>

<canvas width="300" height="300">No CANVAS?</canvas>

<p>
	<a id="restart" href="#">Restart</a>,
	or switch level:
	<a class="goto" data-prev href="#">&lt; prev</a> |
	<span id="lvl">?</span> |
	<a class="goto" data-next href="#">next &gt;</a>
</p>
<p>Click between dots to connect a slither and make every cell have the right number of connectors. White numbers = good.</p>

<div id="red"></div>

<script src="js/rjs-custom.js"></script>
<script src="146b.js"></script>
<script>
var _LEVEL = 1;
var LEVEL = location.hash ? (parseInt(location.hash.substr(1)) || _LEVEL) : _LEVEL;

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
		initLevel(LEVEL);

		elCanvas.on(evType, function(e) {
			$('red').css(e.pageXY.toCSS());

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

	$$('.goto').on('click', function(e) {
		e.preventDefault();
		var d = this.data('prev') != null ? -1 : 1;
		if ( getMap(lvl.n + d) ) {
			initLevel(lvl.n + d);
		}
	});

	$('restart').on('click', function(e) {
		e.preventDefault();
		initLevel(lvl.n);
	});

	window.on('hashchange', function() {
		var n = parseInt(location.hash.substr(1));
		if ( !isNaN(n) ) {
			initLevel(n);
		}
	});

	// Process
	function initLevel(n) {
		clearInterval(slithering);
		location.hash = n;
		$('lvl').setText(n);

		connectors = [];
		conditions = {};

		lvl = getLevel(n);
		lvl.connectors = getAllConnectors();
		drawLevel(true);

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
		slithering = setInterval(iterate, 100);
	}

	function drawLevel(initial) {
		ctx.clearRect(0, 0, _w, _h);
		initGrid(lvl);
		drawGrid(lvl);
		drawNumbers(lvl, initial);
		hiliteConnectors();
	}

	function hiliteConnectors() {
		r.each(connectors, function(key) {
			var c = key.split('-'),
				con = new Connector(c[1], c[2], c[0]);
			hiliteConnector(con, false);
		});
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

})([
	[
		'  3  ',
		'  3  ',
		'     ',
		'     ',
		'     ',
	],
	[
		'  0  ',
		'  3  ',
		'1  2 ',
		' 23  ',
		'     ',
	],
	[
		'01010',
		' 3  2',
		'2 3  ',
		'1 1 1',
		'  3  ',
	],
	[
		' 313 ',
		'  2  ',
		' 202 ',
		'  2  ',
		'3 1 3',
	],
	[
		' 231 ',
		'  20 ',
		' 2   ',
		'1 2 1',
		'0   0',
	],
	[
		'     ',
		' 222 ',
		'2 3 2',
		'  2  ',
		'31  1',
	],
	[
		'0 1 22',
		' 2  01',
		'1 2 22',
		'   0  ',
		'202 0 ',
		'212  0',
	],
]);
</script>

</body>

</html>
