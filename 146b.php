<!doctype html>
<html>

<head>
	<title>Slither CANVAS</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.5" />
	<style>
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

<p>Switch level: <a class="goto" data-prev href="#">&lt; prev</a> | <span id="lvl">1</span> | <a class="goto" href="#">next &gt;</a></p>
<p>Click between dots to connect a slither and make every cell have the right number of connectors. White numbers = good.</p>

<div id="red"></div>

<script src="rjs.js"></script>
<script>
var lvl;

window.on('error', function(e) {
	alert(e.originalEvent.message);
});

(function(before, levels) {
	before();

	// Config
	var cellSize = 50,
		outsidePadding = 25,
		lineWidth = 9,
		gridColor = '#cccccc',
		gridXColor = '#aaaaaa',
		gridHiliteColor = '#aaaaaa',
		textColor = '#000000',
		textCorrectColor = '#ffffff';

	// Context
	var html = document.documentElement,
		elCanvas = $('canvas', 1),
		ctx = elCanvas.getContext('2d'),
		_w = elCanvas.width,
		_h = elCanvas.height,
		evType = 'ontouchstart' in html ? 'touchstart' : 'click';

	// State
	// var lvl;
	var o,
		connectors,
		conditions;

	// Start
	$(init);

	$extend(Coords2D, {
		distanceTo: function(C) {
			return Math.sqrt( Math.pow(this.x - C.x, 2) + Math.pow(this.y - C.y, 2) );
		}
	});


	function Connector(x, y, dir) {
		this.x = x;
		this.y = y;
		this.dir = dir;
	}
	$extend(Connector, {
		getEnds: function() {
			if ( this.dir == 'hor' ) {
				return [
					new End(this.x, this.y),
					new End(this.x + 1, this.y)
				];
			}
			return [
				new End(this.x, this.y),
				new End(this.x, this.y + 1)
			];
		},
		valid: function() {
			return this.x >= 0 && this.y >= 0 && this.x < lvl.width && this.y < lvl.height;
		},
		getCenterPosition: function() {
			var plus = cellSize/2,
				dir = this.dir == 'hor' ? 'x' : 'y',
				pos = new Coords2D(outsidePadding + this.x * cellSize, outsidePadding + this.y * cellSize);
			pos[dir] += plus;
			pos.source = this;
			return pos;
		}
	}, Coords2D.prototype);

	function End(x, y) {
		this.x = x;
		this.y = y;
	}
	$extend(End, {
		getConnectors: function() {
			var cons = []
			$each([[0, 1], [0, -1], [1, 0], [-1, 0]], function(vector) {
				var dir = vextor[0] == 0 ? 'ver' : 'hor',
					con = new Connector(this.x + vector[0], this.y + vector[1], dir);
				con.valid(lvl) && cons.push(con);
			}, this);
			return cons;
		},
		getPosition: function() {
			return new Coords2D(outsidePadding + this.x * cellSize, outsidePadding + this.y * cellSize);
		}
	}, Coords2D.prototype);

	function init() {
		initLevel(0);

		elCanvas.on(evType, function(e) {
			$('red').css(e.pageXY.toCSS());
			var zoom = this.offsetWidth / _w;
			var c = e.subjectXY.multiply(1/zoom);

			var connector = getClosestConnector(c);

			// var connector = getConnector(c);
			if (connector) {
				var hilited = hiliteConnector(connector, true);
console.log('hilited', hilited);
				updateConditions(connector, hilited);
				drawLevel();
			}
		});
	}

	$$('.goto').on('click', function(e) {
		e.preventDefault();
		var d = this.data('prev') != null ? -1 : 1;
		if ( getMap(lvl.n + d) ) {
			initLevel(lvl.n + d);
			$('lvl').setText(lvl.n+1);
		}
	});

	document.on('touchstart', function(e) {
		if ( !['input', 'a'].contains(e.target.nodeName.toLowerCase()) ) {
			e.preventDefault();
		}
	});

	// Process
	function initLevel(n) {
		connectors = [];
		conditions = {};

		lvl = getLevel(n);
		lvl.connectors = getAllConnectors();
		drawLevel(true);
	}

	function getClosestConnector(C) {
		var minDistance = 999,
			closestConnector = lvl.connectors[0];
console.log(lvl.connectors.length);
		for ( var i=1, L=lvl.connectors.length; i<L; i++ ) {
			var distance = lvl.connectors[i].getCenterPosition().distanceTo(C);
			if ( distance < minDistance ) {
				minDistance = distance;
				closestConnector = lvl.connectors[i];
			}
		}
		return closestConnector;
	}

	function getAllConnectors() {
		var connectors = [];
		for ( var y=0; y<=lvl.height; y++ ) {
			for ( var x=0; x<=lvl.width; x++ ) {
				if ( x < lvl.width ) {
					connectors.push(new Connector(x, y, 'hor'));
				}
				if ( y < lvl.height ) {
					connectors.push(new Connector(x, y, 'ver'));
				}
			}
		}
		return connectors;
	}

	function getAllEnds() {
		var ends = [];
		for ( var y=0; y<=lvl.height; y++ ) {
			for ( var x=0; x<=lvl.width; x++ ) {
				ends.push(new End(x, y));
			}
		}
		return ends;
	}

	function updateConditions(connector, hilited) {
		var cs = getNeighborCells(connector),
			change = hilited ? 1 : -1;
		$each(cs, function(c) {
			c = c.join('-');
			if ( conditions[c] == null ) {
				conditions[c] = 0;
			}
			conditions[c] += change;
		});
	}

	function getNeighborCells(connector) {
		var cells = [],
			hor = connector.x,
			ver = connector.y,
			dx = connector.dir == 'ver' ? 1 : 0,
			dy = connector.dir == 'hor' ? 1 : 0;
		if ( hor - dx >= 0 && ver - dy >= 0 ) {
			cells.push(new Coords2D(hor - dx, ver - dy));
		}
		if ( hor < lvl.width && ver < lvl.height ) {
			cells.push(new Coords2D(hor, ver));
		}
		return cells;
	}

	function hiliteConnector(connector, withState) {
		var dir = connector.dir,
			hor = connector.x,
			ver = connector.y,
			method = dir == 'ver' ? getVerticalConnectorCoords : getHorizontalConnectorCoords,
			cs = method(hor, ver);

		var ckey = dir + '-' + hor + '-' + ver,
			eIndex = connectors.indexOf(ckey),
			exists = eIndex != -1,
			color = withState && exists ? gridColor : gridHiliteColor;

		drawLine(cs[0], cs[1], color, true);
		if ( withState ) {
			exists ? connectors.splice(eIndex, 1) : connectors.push(ckey);
		}

		return !exists;
	}

	function getConnector(c) {
		var o = Math.floor(lineWidth / 2);

		var hor, ver;
		for ( var x=0; x<=lvl.width; x++ ) {
			var center = outsidePadding + x * cellSize;
			if ( c.x >= center-o && c.x <= center+o ) {
				hor = x;
				break;
			}
		}
		for ( var y=0; y<=lvl.height; y++ ) {
			var center = outsidePadding + y * cellSize;
			if ( c.y >= center-o && c.y <= center+o ) {
				ver = y;
				break;
			}
		}

		// Matched vertical connector
		if ( hor != null && ver == null ) {
			ver = Math.floor(c.y / (_h / lvl.height));
			return ['ver', hor, ver];
		}

		// Matched horizontal connector
		else if ( ver != null && hor == null ) {
			hor = Math.floor(c.x / (_w / lvl.width));
			return ['hor', hor, ver];
		}
	}

	function getHorizontalConnectorCoords(x, y) {
		var o = 0; // Math.floor(lineWidth / 2);
		var y = outsidePadding + y * cellSize - o,
			x = outsidePadding + x * cellSize + o;
		return [
			new Coords2D(x, y),
			new Coords2D(x + cellSize, y)
		];
	}

	function getVerticalConnectorCoords(x, y) {
		var o = 0; // Math.floor(lineWidth / 2);
		var y = outsidePadding + y * cellSize + o,
			x = outsidePadding + x * cellSize - o;
		return [
			new Coords2D(x, y),
			new Coords2D(x, y + cellSize)
		];
	}

	function drawLevel(initial) {
		ctx.clearRect(0, 0, _w, _h);
		initGrid(lvl);
		drawGrid(lvl);
		drawNumbers(lvl, initial);
		hiliteConnectors();
	}

	function hiliteConnectors() {
		$each(connectors, function(key) {
			var c = key.split('-'),
				con = new Connector(c[1], c[2], c[0]);
			hiliteConnector(con, false);
		});
	}

	function drawNumbers(lvl, initial) {
		for ( var y=0; y<lvl.height; y++ ) {
			for ( var x=0; x<lvl.width; x++ ) {
				var number = lvl.map[y][x];
				if ( number ) {
					var c = getCellCoords(x, y);

					// Store in conditions cache
					if ( initial ) {
						conditions[ x + '-' + y ] = 0;
					}

					// Draw to canvas
					var loc = new Coords2D(x, y);
					drawNumber(c, number, loc);
				}
			}
		}
	}

	function drawNumber(c, number, loc) {
		var ckey = loc.join('-'),
			correct = conditions[ckey] == null || conditions[ckey] == number;
		ctx.font = '30px sans-serif';
		ctx.fillStyle = correct ? textCorrectColor : textColor;
		ctx.fillText(number, c.x + 11, c.y + 32);
	}

	function getCellCoords(x, y) {
		var o = Math.floor(lineWidth / 2);
		return new Coords2D(
			outsidePadding + x * cellSize + o,
			outsidePadding + y * cellSize + o
		);
	}

	function drawGrid(lvl) {
		for ( var x=0; x<=lvl.width; x++ ) {
			// Vertical lines
			var xc = outsidePadding + x * cellSize;
			drawLine({y: 0, x: xc}, {y: _h, x: xc});
		}
		for ( var y=0; y<=lvl.height; y++ ) {
			// Horizontal lines
			var yc = outsidePadding + y * cellSize;
			drawLine({x: 0, y: yc}, {x: _w, y: yc});

			for ( var x=0; x<=lvl.width; x++ ) {
				var xc = outsidePadding + x * cellSize;
				drawDot({x: xc, y: yc}, gridXColor);
			}
		}
	}

	function drawDot(c, color) {
		var o = Math.floor(lineWidth / 2);
		ctx.fillStyle = color || gridXColor;
		ctx.fillRect(c.x - o, c.y - o, lineWidth, lineWidth);
	}

	function drawLine(p1, p2, color, dots) {
		ctx.lineWidth = lineWidth;
		ctx.strokeStyle = color || gridColor;
		ctx.beginPath();
		ctx.moveTo(p1.x, p1.y);
		ctx.lineTo(p2.x, p2.y);
		ctx.stroke();

		if ( dots ) {
			drawDot(p1);
			drawDot(p2);
		}
	}

	function initGrid(lvl) {
		_w = outsidePadding * 2 + cellSize * lvl.width;
		_h = outsidePadding * 2 + cellSize * lvl.height;
		elCanvas.width = _w;
		elCanvas.height = _h;
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
				cells.push(row[i].trim());
			}
			return cells;
		});
		return map;
	}

})(function() {

	$extend(Coords2D, {
		multiply: function(f) {
			return new Coords2D(
				Math.round(this.x * f),
				Math.round(this.y * f)
			);
		},
		log: function() {
			console.log(this.x, this.y);
		}
	});

}, [
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