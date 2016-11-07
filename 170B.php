<?php
// MAHJONG MAP BUILDER

require 'inc.functions.php';

$maps = array_map('basename', glob('images/mahjong/map_*.gif'));
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
.stats {
	position: absolute;
	right: 0;
	top: 0;
}
</style>
</head>

<body>

<p>
	<select class="map"><?= do_html_options($maps, @$_GET['map'], '-- map') ?></select>
	<button class="save">Save this map</button>
	<button class="unsave">Clear saved map</button>
</p>

<canvas width="800" height="500"></canvas>

<script src="//home.hotblocks.nl/tests/three/Stats.js"></script>
<script src="170.js"></script>
<script>
var mapSelect = document.querySelector('select.map');
var saveButton = document.querySelector('button.save');
var unsaveButton = document.querySelector('button.unsave');
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');
var change = true;

var SQUARE_W = 20;
var SQUARE_H = 30;
var TILE_W = 2;
var TILE_H = 2;
var MARGIN = 2;

var hilite;

var tiles = localStorage.mahjongMapBuilderMap && mahjong.Tile.unserialize(JSON.parse(localStorage.mahjongMapBuilderMap)) || [];

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
		drawTile(tile, getLevelColor(tile.level));
	}
}

function validTile(tile) {
	return true;
	// return tile.x % 2 == 0 && tile.y % 3 == 0;
}

// === //

canvas.onmousemove = function(e) {
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
	if (hilite) {
		if (validTile(hilite)) {
			tiles.push(hilite);
			change = true;
		}
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
		// console.log('pixels', pixels);
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

saveButton.onclick = function(e) {
	localStorage.mahjongMapBuilderMap = JSON.stringify(mahjong.Tile.serialize(tiles));
};

unsaveButton.onclick = function(e) {
	delete localStorage.mahjongMapBuilderMap;
	tiles.length = 0;

	change = true;
};

// === //

var stats = new Stats();
stats.getDomElement().className += ' stats';
document.body.appendChild(stats.getDomElement());

render();
function render() {
	if (change) {
		change = false;

		canvas.width = canvas.width;

		drawGrid();
		drawTiles();
		drawHilite();
	}

	window.stats && stats.update();
	requestAnimationFrame(render);
}
</script>

</body>

</html>
