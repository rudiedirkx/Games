<?php
// MONDRIAN PUZZLE

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MONDRIAN PUZZLE</title>
<style>
canvas {
	outline: solid 1px black;
}
</style>
</head>

<body>

<canvas width="800" height="600"></canvas>

<script>
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');

var SQUARE_SIZE = 100;
var BOARD_SIZE = 5;
var BOARD_MARGIN = 20;
var COLORS = ['#0f0', '#00f', '#ff0', '#f0f', '#0ff'];

var squares = [];
var squaring = [];
var change = true;

// debug //
squares.push(new Square(new Point(0, 0), new Point(1, 2)));
// debug //

function Point(x, y) {
	this.x = x;
	this.y = y;

	this.findClosestLine = function() {
		var ox = (this.x - BOARD_MARGIN) / (SQUARE_SIZE + 1);
		var dx = Math.abs(Math.round(ox) - ox);
		var oy = (this.y - BOARD_MARGIN) / (SQUARE_SIZE + 1);
		var dy = Math.abs(Math.round(oy) - oy);

		// Vertical
		if (dx < dy) {
			var x = Math.round(ox);
			var y = Math.floor(oy);

			return new Line(
				new Point(x, y),
				new Point(x, y+1)
			);
		}
		// Horizontal
		else {
			var x = Math.floor(ox);
			var y = Math.round(oy);

			return new Line(
				new Point(x, y),
				new Point(x+1, y)
			);
		}
	}

	this.rect = function() {
		return new Point(BOARD_MARGIN + this.x * (SQUARE_SIZE+1), BOARD_MARGIN + this.y * (SQUARE_SIZE+1));
	};

	this.equals = function(point) {
		return this.x == point.x && this.y == point.y;
	};
}

function Line(from, to) {
	this.from = from;
	this.to = to;

	this.equals = function(line) {
		return (this.from.equals(line.from) && this.to.equals(line.to)) || (this.from.equals(line.to) && this.to.equals(line.from));
	};
}

function Square(tl, br) {
	this.tl = tl;
	this.br = br;

	this.rect = function() {
		return [
			this.tl.rect(),
			(new Point(this.br.x, this.tl.y)).rect(),
			this.br.rect(),
			(new Point(this.tl.x, this.br.y)).rect(),
		];
	};

	this.draw = function(color) {
		var rect = this.rect();
		drawSquare(this.tl, this.br, color.replace(/0/g, 'b'));
		drawLines(rect.concat(rect[0]), 1, color);
	};
}
Square.invalid = function(lines) {
	// If any line is not on a bound
	return false;
};
Square.valid = function(lines) {
	// If all lines exist, and are not invalid
	return false;
};

function drawSquare(tl, br, color) {
	ctx.fillStyle = color;
	tl = tl.rect();
	br = br.rect();
	ctx.fillRect(tl.x, tl.y, br.x - tl.x, br.y - tl.y);
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
		squares[i].draw(getColor(i));
	}
}

function drawSquaring() {
	for (var i = 0; i < squaring.length; i++) {
		var line = squaring[i];
		drawLine(line.from.rect(), line.to.rect(), 2, 'red');
	}
}

function updateSize() {
	canvas.width = canvas.height = BOARD_SIZE * SQUARE_SIZE + BOARD_MARGIN * 2 + BOARD_SIZE + 1;
}

// === //

canvas.onclick = function(e) {
	var point = new Point(e.offsetX, e.offsetY);
	var line = point.findClosestLine();

	if (!squaring.length || !line.equals(squaring[squaring.length - 1])) {
		squaring.push(line);

		// Invalid square => reset
		if (Square.invalid(squaring)) {
			squaring.length = 0;
		}
		// Complete square => save
		else if (Square.valid(squaring)) {
			squares.push(Square.fromLines(squaring));
			squaring = [];
		}

		change = true;
	}
};

canvas.oncontextmenu = function(e) {
	e.preventDefault();

	squaring.length = 0;
	change = true;
};

// === //

updateSize();
render();

function render() {
	if (change) {
		change = false;

		canvas.width = canvas.width;

		drawGrid();
		drawSquares();
		drawSquaring();
	}

	(window.requestAnimationFrame || window.webkitRequestAnimationFrame)(render);
}
</script>

</body>

</html>
