<?php
// MAHJONG

require 'inc.functions.php';

/**
 * Hardcoded:
 * - GIF tiles are 4x6
 * - JS Tiles are 2x2
 * - Every tile is portrait: always 2x3, never 3x2
 */

$maps = array_map('basename', glob('images/mahjong/map_*.png'));
natcasesort($maps);
$maps = array_combine($maps, $maps);

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MAHJONG</title>
<style>
canvas {
	outline: solid 1px black;
}
</style>
</head>

<body>

<p>
	<select class="map"><?= do_html_options($maps, @$_GET['map']) ?></select>
	<select class="levels"><option>1</option></select>
</p>

<canvas width="1000" height="500"></canvas>

<script>
window.onerror = function(e) {
	alert(e.error || e.message);
};
</script>
<!-- <script src="https://rawgit.com/taylorhakes/promise-polyfill/master/promise.js"></script> -->
<script src="170.js"></script>
<script>
var mapSelect = document.querySelector('select.map');
var levelSelect = document.querySelector('select.levels');

var canvas = document.querySelector('canvas');

var SQUARE_W = 16;
var SQUARE_H = 24;
var TILE_W = 2;
var TILE_H = 2;
var MARGIN = 2;

mapSelect.onchange = function(e) {
	draw(true);
};
levelSelect.onchange = function(e) {
	draw(false);
};

canvas.onmousedown = function(e) {
	e.preventDefault();
};
canvas.onclick = function(e) {
	e.preventDefault();

	var x = e.offsetX;
	var y = e.offsetY;
	point(x, y);

	var tile = mahjong.target(canvas.board, x, y);
	if (tile) {
		if (tile.isOnTop()) {
			tile.disabled = true;
			mahjong.draw(canvas, canvas.board, canvas.levels);
			point(x, y);
		}
	}
};

draw(true);

function point(x, y) {
	var ctx = canvas.getContext('2d');

	ctx.fillStyle = 'red';
	ctx.fillRect(x-1, y-1, 3, 3);
}

function setLevelOptions(levels) {
	var options = '';
	for (var i=0; i<levels; i++) {
		options += '<option>' + (i + 1);
	}
	levelSelect.innerHTML = options;
	levelSelect.value = levels;
}

function draw(resetLevels) {
	var src = '/images/mahjong/' + mapSelect.value;
	mahjong.pixels(src).then(function(pixels) {
		console.log('pixels', pixels);
		return mahjong.tiles(pixels);
	}).then(function(board) {
		console.log('board', board);
		canvas.board = board;

		resetLevels && setLevelOptions(board.levels.length);

		// console.time('tiles on top');
		// var tilesOnTop = board.allTiles.filter(tile => tile.isOnTop());
		// console.timeEnd('tiles on top');
		// console.log('tiles on top', tilesOnTop.map(tile => tile.level));

		var levels = parseInt(levelSelect.value);
		for (var i = 0; i < board.allTiles.length; i++) {
			var tile = board.allTiles[i];
			if (tile.level > levels-1) {
				tile.disabled = true;
			}
		}

		mahjong.draw(canvas, board);
	}).catch(function() {
		alert(this);
	});
}
</script>

</body>

</html>
