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
button.ffw {
	font-weight: bold;
	color: green;
	box-shadow: 0 0 3px green;
}
canvas {
	outline: solid 1px black;
}
</style>
</head>

<body>

<p>
	<select class="map"><?= do_html_options($maps, @$_GET['map']) ?></select>
	<button class="shuffle">Shuffle</button>
	<button class="moves" disabled>?</button>
</p>

<canvas width="400" height="400"></canvas>

<p><a href="170B.php">Build your own</a></p>

<script>
window.onerror = function(e) {
	alert(e);
};

window.Promise || document.write(unescape('%3Cscript%20src%3D%22https%3A//rawgit.com/taylorhakes/promise-polyfill/master/promise.js%22%3E%3C/script%3E'));
</script>
<script src="170.js"></script>
<script>
var mapSelect = document.querySelector('select.map');
var shuffleButton = document.querySelector('button.shuffle');
var movesButton = document.querySelector('button.moves');
var canvas = document.querySelector('canvas');

var SQUARE_W = 16;
var SQUARE_H = 24;
var TILE_W = 2;
var TILE_H = 2;
var MARGIN = 2;

// === //

var hilite;

mapSelect.onchange = function(e) {
	hilite = null;
	load();
};

shuffleButton.onclick = function(e) {
	canvas.board.shuffle();
	draw();
};

canvas.onmousedown = function(e) {
	e.preventDefault();
};
canvas.onclick = function(e) {
	e.preventDefault();

	var x = e.offsetX;
	var y = e.offsetY;

	var wait = 0;

	var tile = mahjong.target(canvas.board, x, y);
	if (tile && tile.isOnTop() && tile.sidesAreFree()) {
		if (hilite && hilite != tile) {
			if (hilite.value == tile.value) {
				// remove both
				hiliteTile(tile);
				hiliteTile(hilite);

				hilite.disabled = true;
				tile.disabled = true;

				hilite = null;

				wait = 150;
			}
			else {
				// change hilite
				hilite = tile;
			}
		}
		else {
			// hilite new tile
			hilite = tile;
		}
	}

	setTimeout(function() {
		draw();
		// point(x, y);
		if (canvas.board.activeTiles().length == 0) {
			setTimeout(function() {
				alert('YOU WIN!');
			}, 100);
		}
	}, wait);
};

// === //

load();

function point(x, y) {
	var ctx = canvas.getContext('2d');

	ctx.fillStyle = 'red';
	ctx.fillRect(x-1, y-1, 3, 3);
}

function drawHilite() {
	if (hilite) {
		hiliteTile(hilite);
	}
}

function hiliteTile(tile) {
	var rect = tile.rect();

	var ctx = canvas.getContext('2d');
	ctx.strokeStyle = '#00f';
	ctx.lineWidth = 1;
	ctx.strokeRect(rect[0]-1, rect[1]-1, rect[2]+2, rect[3]+2);
}

function draw() {
	mahjong.draw(canvas, canvas.board);
	drawHilite();

	var moves = canvas.board.moves();
	movesButton.textContent = moves;
	movesButton.classList.toggle('ffw', moves * 2 == canvas.board.activeTiles().length);
}

function load() {
	var src = '/images/mahjong/' + mapSelect.value;
	mahjong.pixels(src).then(function(pixels) {
		return mahjong.tiles(pixels);
	}).then(function(board) {
		console.log('board', board);
		canvas.board = board;

		board.assignValues();

		mahjong.Board.canvasSize(canvas, board.allTiles, true);
		draw();
	});
}
</script>

</body>

</html>
