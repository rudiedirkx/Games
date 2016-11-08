<?php
// MAHJONG MAP BUILDER

require 'inc.functions.php';

$maps = array_map('basename', glob('images/mahjong/map_*.png'));
natcasesort($maps);
$maps = array_combine($maps, $maps);

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MAHJONG MAP BUILDER</title>
<style>
canvas {
	outline: solid 1px black;
}
</style>
</head>

<body>

<p>
	<select class="map"><?= do_html_options($maps, @$_GET['map'], '-- map') ?></select>
	<button class="save">Save this map</button>
	<button class="unsave">Clear saved map</button>
	<button class="export">EXPORT</button>
	<select class="levels"><?= do_html_options(array_combine(range(1, 9), range(1, 9)), '', '-- levels') ?></select>
</p>

<canvas width="800" height="500"></canvas>

<script>
window.onerror = function(e) {
	alert(e);
};
</script>
<!-- <script src="https://rawgit.com/taylorhakes/promise-polyfill/master/promise.js"></script> -->
<script src="170.js"></script>
<script>
var mapSelect = document.querySelector('select.map');
var saveButton = document.querySelector('button.save');
var unsaveButton = document.querySelector('button.unsave');
var exportButton = document.querySelector('button.export');
var levelsSelect = document.querySelector('select.levels');
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');
var change = true;

var SQUARE_W = 20;
var SQUARE_H = 30;
var TILE_W = 2;
var TILE_H = 2;
var MARGIN = 2;

var hilite;

var tiles = localStorage.mahjongMapBuilderMap && mahjong.Board.unserialize(JSON.parse(localStorage.mahjongMapBuilderMap)) || [];

function drawLine(x1, y1, x2, y2) {
	ctx.beginPath();
	ctx.moveTo(x1, y1);
	ctx.lineTo(x2, y2);
	ctx.closePath();
	ctx.stroke();
}

function drawGrid() {
	ctx.strokeStyle = '#ddd';
	ctx.lineWidth = 2;
	for (var y=-1; y<1000; y+=SQUARE_H+MARGIN) {
		drawLine(0, y, 1000, y);
	}
	for (var x=-1; x<1000; x+=SQUARE_W+MARGIN) {
		drawLine(x, 0, x, 1000);
	}
}

function getSquare(e) {
	var x = e.offsetX;
	var y = e.offsetY;

	return new mahjong.Tile(Math.floor(x / (SQUARE_W+MARGIN)), Math.floor(y / (SQUARE_H+MARGIN)));
}

function drawTile(tile, color) {
	tile.draw(ctx, color);
}

function getLevel(tile1) {
	var max = -1;
	for (var i = 0; i < tiles.length; i++) {
		var tile2 = tiles[i];
		if (tilesOverlap(tile1, tile2)) {
			if (tile2.level > max) {
				max = tile2.level;
			}
		}
	}

	return max+1;
}

function tilesOverlap(tile1, tile2) {
	if (tile1.x < tile2.x+TILE_W && tile1.x+TILE_W > tile2.x) {
		if (tile1.y < tile2.y+TILE_H && tile1.y+TILE_H > tile2.y) {
			return true;
		}
	}
	return false;
}

function getLevelColor(level) {
	var L = (12 - 3*level).toString(16);
	return '#' + L + L + L;
	// var colors = ['#bbb', '#999', '#777', '#555', '#333', '#111'];
	// return colors[level] || '#000';
}

function drawHilite() {
	if (hilite) {
		drawTile(hilite, getLevelColor(hilite.level));
	}
}

function drawTiles() {
	for (var i = 0; i < tiles.length; i++) {
		var tile = tiles[i];
		if (!tile.disabled) {
			drawTile(tile, getLevelColor(tile.level));
		}
	}
}

function colorImageData(data, r, g, b) {
	for (var j = 0; j < data.length; j+=4) {
		data[j+0] = r * 255;
		data[j+1] = g * 255;
		data[j+2] = b * 255;
		data[j+3] = 255;
	}
}

function drawThumb(ctx, x, y) {
	var data;
	// Horizontal
	data = ctx.createImageData(4, 1);
	colorImageData(data.data, 0, 0, 0);
	ctx.putImageData(data, x, y);
	ctx.putImageData(data, x, y+5);
	// Vertical
	data = ctx.createImageData(1, 6);
	colorImageData(data.data, 0, 0, 0);
	ctx.putImageData(data, x, y);
	ctx.putImageData(data, x+3, y);
	// Green
	data = ctx.createImageData(1, 1);
	colorImageData(data.data, 0, 1, 0);
	ctx.putImageData(data, x, y);
}

// === //

canvas.onmousemove = function(e) {
	if (levelsSelect.value) return;

	if (hilite = getSquare(e)) {
		hilite.level = getLevel(hilite);
	}
	change = true;
};

canvas.onmouseout = function(e) {
	hilite = null;
	change = true;
};

canvas.onclick = function(e) {
	if (levelsSelect.value) return;

	if (hilite) {
		tiles.push(hilite);
		change = true;
	}
};

canvas.oncontextmenu = function(e) {
	e.preventDefault();

	if (levelsSelect.value) return;

	var x = e.offsetX;
	var y = e.offsetY;

	var board = mahjong.Board.fromList(tiles);
	var target = mahjong.target(board, x, y);
	if (target && target.isOnTop()) {
		board = null;

		var index = tiles.indexOf(target);
		tiles.splice(index, 1);
		change = true;
	}
};

mapSelect.onchange = function(e) {
	if (this.value == '') {
		tiles.length = 0;
		change = true;
		return;
	}

	var src = '/images/mahjong/' + this.value;
	mahjong.pixels(src).then(function(pixels) {
		return mahjong.tiles(pixels);
	}).then(function(board) {
		tiles.length = 0;
		for (var i = 0; i < board.allTiles.length; i++) {
			var tile = board.allTiles[i];
			tiles.push(tile);
		}

		change = true;
	});
};

levelsSelect.onchange = function(e) {
	var max = this.value ? parseInt(this.value) : 99;
	for (var i = 0; i < tiles.length; i++) {
		var tile = tiles[i];
		tile.disabled = tile.level+1 > max;
	}

	hilite = null;
	change = true;
};

saveButton.onclick = function(e) {
	localStorage.mahjongMapBuilderMap = JSON.stringify(mahjong.Board.serialize(tiles));
};

unsaveButton.onclick = function(e) {
	delete localStorage.mahjongMapBuilderMap;
	tiles.length = 0;

	change = true;
};

exportButton.onclick = function(e) {
	// Get map size
	// Draw tiles on canvas
	// Download Blob/File

	var x = [999, 0], y = [999, 0];
	for (var i = 0; i < tiles.length; i++) {
		var tile = tiles[i];
		x[0] = Math.min(x[0], tile.x);
		x[1] = Math.max(x[1], tile.x);
		y[0] = Math.min(y[0], tile.y);
		y[1] = Math.max(y[1], tile.y);
	}
	var dx = -x[0];
	var dy = -y[0];
	var w = x[1] + dx + 2;
	var h = y[1] + dy + 2;

	var levels = tiles.reduce(function(levels, tile) {
		return Math.max(levels, tile.level + 1);
	}, -1);

	var mapCanvas = document.createElement('canvas');
	mapCanvas.width = w * mahjong.IMPORT_SCALE_X * levels + levels - 1;
	mapCanvas.height = h * mahjong.IMPORT_SCALE_Y;
	document.body.appendChild(mapCanvas);

	var ctx = mapCanvas.getContext('2d');
	ctx.fillStyle = '#fff';
	ctx.fillRect(0, 0, mapCanvas.width, mapCanvas.height);

	// Draw red lines
	for (var i = 1; i < levels; i++) {
		var x = ((w * mahjong.IMPORT_SCALE_X + 1) * i) - 1;

		var data = ctx.createImageData(1, mapCanvas.height);
		colorImageData(data.data, 1, 0, 0);
		ctx.putImageData(data, x, 0);
	}

	// Draw tiles
	for (var i = 0; i < tiles.length; i++) {
		var tile = tiles[i];
		var tdx = dx * mahjong.IMPORT_SCALE_X + tile.level * (w * mahjong.IMPORT_SCALE_X + 1);
		var x = tile.x * mahjong.IMPORT_SCALE_X + tdx;
		var y = (tile.y + dy) * mahjong.IMPORT_SCALE_Y;

		drawThumb(ctx, x, y);
	}

	// Download
	var a = document.createElement('a');
	a.href = mapCanvas.toDataURL('image/png');
	a.download = 'map_N.png';
	document.body.appendChild(a);
	a.click();

	a.remove();
	mapCanvas.remove();
};

// === //

render();
function render() {
	if (change) {
		change = false;

		canvas.width = canvas.width;

		drawGrid();
		drawTiles();
		drawHilite();
	}

	(window.requestAnimationFrame || window.webkitRequestAnimationFrame)(render);
}
</script>

</body>

</html>
