<?php
// MONDRIAN PUZZLE

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('mondrian.js') ?>"></script>
<title>MONDRIAN PUZZLE</title>
<style>
canvas {
	outline: solid 1px black;
	max-width: 100%;
	touch-action: none;
}
.complete {
	font-weight: bold;
	color: green;
}
</style>
</head>

<body>

<canvas width="100" height="100"></canvas>

<p>
	Click &amp; drag to draw rectangles.
	Size: <select id="size"><?= do_html_options(array_combine(range(4, 9), range(4, 9))) ?></select>
	<button id="reset">Reset</button>
	<button id="undo">Undo</button>
</p>

<p>Score: <code id="score">?</code> (the lower the better)</p>

<script>
var sizeElement = document.querySelector('#size');
var resetElement = document.querySelector('#reset');
var undoElement = document.querySelector('#undo');
var scoreElement = document.querySelector('#score');
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');

var SQUARE_SIZE = 80;
var BOARD_SIZE = 5;
var BOARD_MARGIN = 20;
var COLORS = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff'];

var start = Date.now();
var squares = [];
var squaring = [];
var error;
var change = true;
var drawing = false;

function drawSquare(square, color, number) {
	// Outer lines
	var rect = square.rect();
	drawLines(rect.concat(rect[0]), 3, color);

	// Inner fill
	ctx.fillStyle = color == 'error' ? 'black' : color.replace(/0/g, 'b');
	var from = square.from.rect();
	var to = square.to.rect();
	ctx.fillRect(from.x, from.y, to.x - from.x, to.y - from.y);

	// Number
	if (number) {
		drawSquareNumber(square, color, number);
	}
}

function drawSquareNumber(square, color, number) {
	// number || (number = square.coverage());
	var center = square.center();

	ctx.font = '60px sans-serif';
	ctx.fillStyle = color == 'error' ? 'white' : color;
	ctx.textAlign = 'center';
	ctx.textBaseline = 'middle';
	ctx.fillText(String(number), center.x, center.y);
}

function drawLines(ps, width, color) {
	ctx.strokeStyle = color;
	ctx.lineWidth = width;

	ctx.beginPath();
	ctx.moveTo(ps[0].x, ps[0].y);
	for (var i = 1; i < ps.length; i++) {
		ctx.lineTo(ps[i].x, ps[i].y);
	}
	ctx.closePath();
	ctx.stroke();
}

function drawLine(p1, p2, width, color) {
	drawLines([p1, p2], width, color);
}

function drawPoint(point) {
	ctx.fillStyle = 'black';
	ctx.fillRect(point.x-1, point.y-1, 3, 3);
}

function drawGrid() {
	for (var y = BOARD_MARGIN; y < canvas.width; y+=SQUARE_SIZE+1) {
		drawLine(new Point(0, y), new Point(canvas.width, y), 1, '#ddd');
	}

	for (var x = BOARD_MARGIN; x < canvas.width; x+=SQUARE_SIZE+1) {
		drawLine(new Point(x, 0), new Point(x, canvas.height), 1, '#ddd');
	}
}

function getColor(i) {
	return COLORS[i % COLORS.length];
}

function drawSquares() {
	for (var i = 0; i < squares.length; i++) {
		drawSquare(squares[i], getColor(i));
	}
}

function drawSquareNumbers() {
	for (var i = 0; i < squares.length; i++) {
		drawSquareNumber(squares[i], getColor(i), squares[i].coverage());
	}
}

function drawError() {
	if (error) {
		drawSquare(error, 'error', 'x');
	}
}

function drawSquaring() {
	for (var i = 1; i < squaring.length; i++) {
		drawLine(squaring[i-1].rect(), squaring[i].rect(), 2, 'black');
	}
}

function updateSize() {
	canvas.width = canvas.height = BOARD_SIZE * SQUARE_SIZE + BOARD_MARGIN * 2 + BOARD_SIZE + 1;
}

function finishDrawing() {
	if (drawing && squaring.length) {
		var square = Square.valid(squaring);
		if (square) {
			if (square.overlaps(squares) || square.sameSizes(squares)) {
				error = square;
				setTimeout(function() {
					error = null;
					change = true;
				}, 500);
			}
			else {
				squares.push(square);
				squaring = [];

				updateScore();
			}
		}
	}

	squaring.length = 0;
	change = true;
}

function getScore() {
	if (squares.length < 2) {
		return 0;
	}

	var max = -1;
	var min = 99;

	for (var i = 0; i < squares.length; i++) {
		var coverage = squares[i].coverage();
		max = Math.max(max, coverage);
		min = Math.min(min, coverage);
	}

	return max - min;
}

function getComplete() {
	return BOARD_SIZE * BOARD_SIZE == squares.reduce(function(area, square) {
		return area + square.coverage();
	}, 0);
}

function updateScore() {
	var score = getScore();
	var complete = getComplete();
	scoreElement.textContent = score ? String(score) : '?';
	scoreElement.parentNode.classList.toggle('complete', complete);

	if (complete) {
		Game.saveScore({
			time: Math.round((Date.now() - start) / 1000),
			score: score,
			level: BOARD_SIZE,
		});
	}
}

function reset() {
	start = Date.now();

	error = null;
	squares.length = 0;
	squaring.length = 0;

	updateScore();

	change = true;
}

// === //

sizeElement.value = String(BOARD_SIZE);
sizeElement.onchange = function(e) {
	BOARD_SIZE = Number(this.value);
	updateSize();
	reset();
};

resetElement.onclick = function(e) {
	reset();
};

undoElement.onclick = function(e) {
	squares.pop();
	change = true;
	updateScore();
};

canvas.on(['mousedown', 'touchstart'], function(e) {
	// e.preventDefault();
	drawing = true;
});
canvas.on(['mousemove', 'touchmove'], function(e) {
	if (!drawing) return;
	// e.preventDefault();

	var point = (new Point(e.subjectXY.x, e.subjectXY.y)).factor(canvas.width / canvas.offsetWidth);
	var intersect = point.findClosestIntersection();
	if (!squaring.length || squaring[squaring.length-1].validNext(intersect)) {
		if (!Point.contains(squaring, intersect)) {
			squaring.push(intersect);
			change = true;
		}
		else if (squaring.length > 1 && squaring[0].equals(intersect)) {
			finishDrawing();
			drawing = false;
		}
	}
});
document.onmouseup = document.ontouchend = function(e) {
	// e.preventDefault();

	finishDrawing();
	drawing = false;
};

// === //

updateSize();
render();

function render() {
	if (change) {
		change = false;

		canvas.width = canvas.width;

		drawSquares();
		drawGrid();
		drawError();
		drawSquaring();
		drawSquareNumbers();
	}

	(window.requestAnimationFrame || window.webkitRequestAnimationFrame)(render);
}
</script>

</body>

</html>
