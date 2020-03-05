<?php
// MAHJONG

require __DIR__ . '/inc.bootstrap.php';

/**
 * Hardcoded:
 * - GIF tiles are 4x6
 * - JS Tiles are 2x2
 * - Every tile is portrait: always 2x3, never 3x2
 */

$maps = array_map('basename', glob('images/mahjong/map_*.png'));
natcasesort($maps);
$maps = array_combine($maps, array_map(function($map) {
	return substr($map, 0, -4);
}, $maps));

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MAHJONG</title>
<style>
button:disabled {
	border-style: dotted;
	border-color: black;
	background: white;
}
button.ffw {
	font-weight: bold;
	color: green;
	box-shadow: 0 0 3px green;
}
canvas {
	outline: solid 1px black;
}
</style>
<? include 'tpl.onerror.php' ?>
</head>

<body>

<p>
	<select class="map"><?= do_html_options($maps, @$_GET['map']) ?></select>
	<button class="shuffle">Shuffle</button>
	<button class="moves" disabled>?</button>
</p>

<canvas width="400" height="400"></canvas>

<p><a href="170B.php">Build your own</a></p>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('170.js') ?>"></script>
<script>
var mapSelect = document.querySelector('select.map');
var shuffleButton = document.querySelector('button.shuffle');
var movesButton = document.querySelector('button.moves');
var canvas = document.querySelector('canvas');

var SQUARE_W = 14;
var SQUARE_H = 21;
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
			}, 50);

			Game.saveScore({
				time: Math.round((Date.now() - canvas.board.start) / 1000),
				level: canvas.board.level,
				moves: canvas.board.shuffles,
			});
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
	<? if (!empty($_POST['tiles']) ): ?>
		setTimeout(function() {
			var tiles = mahjong.Board.unserialize(<?= json_encode(json_decode($_POST['tiles'])) ?>);
console.log('tiles', tiles);
			var board = new mahjong.Board;
			tiles.map(board.addTile.bind(board));
	<? else: ?>
		var src = '/images/mahjong/' + mapSelect.value;
		mahjong.pixels(src).then(function(pixels) {
console.log('pixels', pixels);
			return mahjong.tiles(pixels);
		}).then(function(board) {
			board.level = parseInt(mapSelect.value.replace(/[^\d]+/g, '')) || 0;
	<? endif; ?>
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
