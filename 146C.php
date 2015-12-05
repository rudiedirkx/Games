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
	<button id="export">Export</button>
</p>

<textarea id="exported" rows="10" cols="20"></textarea>

<div id="red"></div>

<script src="js/rjs-custom.js"></script>
<script src="146b.js"></script>
<script>
var _LEVEL = 1;
var LEVEL = location.hash ? (parseInt(location.hash.substr(1)) || _LEVEL) : _LEVEL;

var lvl = {
	width: 6,
	height: 6
};

$(init);

var connectors = [];
// debug //
// connectors = ["ver-4-1", "hor-3-2", "ver-3-2"];
// debug //
var conditions = [];

function init() {
	prepLevel(lvl);
// debug //
// lvl.map[2][3] = 1;
// debug //
	drawLevel();

	elCanvas.on('contextmenu', function(e) {
		e.preventDefault();
		$('#red').css(e.pageXY.toCSS());

		var zoom = this.offsetWidth / _w;
		var c = e.subjectXY.multiply(1/zoom);

		var cell = getClosestCell(c);
		if ( cell ) {
			lvl.map[cell.y][cell.x] = !lvl.map[cell.y][cell.x];
			drawLevel();
		}
	});

	elCanvas.on('click', function(e) {
		$('#red').css(e.pageXY.toCSS());

		var zoom = this.offsetWidth / _w;
		var c = e.subjectXY.multiply(1/zoom);

		var connector = getClosestConnector(c);
		if (connector) {
			var hilited = hiliteConnector(connector, true);
			drawLevel();
		}
	});

	$('#export').on('click', function(e) {
		var map = [];
		for (var y=0; y<lvl.height; y++) {
			var row = "'";
			for (var x=0; x<lvl.height; x++) {
				var draw = lvl.map[y][x];
				if ( draw ) {
					var cell = new Cell(x, y);
					var cons = cell.getHilitedConnecters(connectors);
					var number = cons.length;
					row += String(number);
				}
				else {
					row += ' ';
				}
			}
			row += "',";
			map.push(row);
		}
		$('#exported').value = map.join("\n");
	});
}

function getClosestCell(c) {
	var x = (c.x - outsidePadding) / cellSize;
	var y = (c.y - outsidePadding) / cellSize;
	if (x < 0 || y < 0 || x >= lvl.width || y >= lvl.height) {
		return;
	}

	x = parseInt(x);
	y = parseInt(y);
	return new Cell(x, y);
}

function prepLevel(lvl) {
	lvl.connectors = getAllConnectors();
	lvl.map = [];
	for (var y=0; y<lvl.height; y++) {
		var row = [];
		for (var x=0; x<lvl.height; x++) {
			row.push(false);
		}
		lvl.map.push(row);
	}
}

function drawLevel() {
	ctx.clearRect(0, 0, _w, _h);
	initGrid(lvl);
	drawGrid(lvl);
	drawNumbers();
	hiliteConnectors();
}

function drawNumbers() {
	for ( var y=0; y<lvl.height; y++ ) {
		for ( var x=0; x<lvl.width; x++ ) {
			var draw = lvl.map[y][x];
			if ( draw ) {
				var cell = new Cell(x, y);
				var cons = cell.getHilitedConnecters(connectors);
				var number = cons.length;

				var c = getCellCoords(x, y);
				drawNumber(c, number, []);
			}
		}
	}
}

</script>

</body>

</html>
