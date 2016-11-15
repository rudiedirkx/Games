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

<p>Click left to line, click right to undo.</p>

<script>
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');

var SQUARE_SIZE = 80;
var BOARD_SIZE = 5;
var BOARD_MARGIN = 20;
var COLORS = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff'];

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
Line.contains = function(lines, line) {
	for (var i = 0; i < lines.length; i++) {
		if (lines[i].equals(line)) {
			return true;
		}
	}

	return false;
};

function Square(from, to) {
	this.from = from;
	this.to = to;

	this.rect = function() {
		return [
			this.from.rect(),
			(new Point(this.to.x, this.from.y)).rect(),
			this.to.rect(),
			(new Point(this.from.x, this.to.y)).rect(),
		];
	};

	this.center = function() {
		return (new Point(
			this.from.x + (this.to.x - this.from.x)/2,
			this.from.y + (this.to.y - this.from.y)/2
		)).rect();
	};

	this.coverage = function() {
		return (this.to.x - this.from.x) * (this.to.y - this.from.y);
	};

	this.lines = function() {
		var lines = [];

		for (var x = this.from.x; x < this.to.x; x++) {
			lines.push(new Line(new Point(x, this.from.y), new Point(x+1, this.from.y)));
			lines.push(new Line(new Point(x, this.to.y), new Point(x+1, this.to.y)));
		}

		for (var y = this.from.y; y < this.to.y; y++) {
			lines.push(new Line(new Point(this.from.x, y), new Point(this.from.x, y+1)));
			lines.push(new Line(new Point(this.to.x, y), new Point(this.to.x, y+1)));
		}

		return lines;
	};
}
Square.bounds = function(lines) {
	var sx = -1;
	var sy = -1;
	var ex = -1;
	var ey = -1;

	for (var i = 0; i < lines.length; i++) {
		var line = lines[i];
		if ( sx == -1 || sx > Math.min(line.from.x, line.to.x) )	sx = Math.min(line.from.x, line.to.x);
		if ( sy == -1 || sy > Math.min(line.from.y, line.to.y) )	sy = Math.min(line.from.y, line.to.y);
		if ( ex == -1 || ex < Math.max(line.from.x, line.to.x) )	ex = Math.max(line.from.x, line.to.x);
		if ( ey == -1 || ey < Math.max(line.from.y, line.to.y) )	ey = Math.max(line.from.y, line.to.y);
	}

	return new Square(new Point(sx, sy), new Point(ex, ey));
};
Square.invalid = function(lines) {
	var square = Square.bounds(lines);

	for (var i = 0; i < lines.length; i++) {
		var line = lines[i];

		// Vertical
		if (line.from.x == line.to.x) {
			// X must be From or To
			if (line.from.x != square.from.x && line.from.x != square.to.x) {
				return true;
			}
		}
		// Horizontal
		else {
			// Y must be From or To
			if (line.from.y != square.from.y && line.from.y != square.to.y) {
				return true;
			}
		}
	}

	return false;
};
Square.valid = function(lines) {
	var square = Square.bounds(lines);
	if (square.coverage() == 0) {
		return false;
	}

	var squareLines = square.lines();
	if (squareLines.length != lines.length) {
		return false;
	}

	for (var i = 0; i < squareLines.length; i++) {
		if (!Line.contains(lines, squareLines[i])) {
			return false;
		}
	}

	return square;
};

function drawSquare(square, color) {
	// Outer lines
	var rect = square.rect();
	drawLines(rect.concat(rect[0]), 3, color);

	// Inner fill
	ctx.fillStyle = color.replace(/0/g, 'b');
	var from = square.from.rect();
	var to = square.to.rect();
	ctx.fillRect(from.x, from.y, to.x - from.x, to.y - from.y);

	// Number
	var index = squares.indexOf(square);
	if (index != -1) {
		var center = square.center();

		ctx.font = '60px sans-serif';
		ctx.fillStyle = color;
		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';
		ctx.fillText(String(index + 1), center.x, center.y);
	}
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
		drawSquare(squares[i], getColor(i));
	}
}

function drawSquaring() {
	for (var i = 0; i < squaring.length; i++) {
		var line = squaring[i];
		drawLine(line.from.rect(), line.to.rect(), 2, 'black');
	}
}

function updateSize() {
	canvas.width = canvas.height = BOARD_SIZE * SQUARE_SIZE + BOARD_MARGIN * 2 + BOARD_SIZE + 1;
}

// === //

canvas.onclick = function(e) {
	var point = new Point(e.offsetX, e.offsetY);
	var line = point.findClosestLine();

	if (!Line.contains(squaring, line)) {
		squaring.push(line);
		var square;

		// Invalid square => undo
		if (Square.invalid(squaring)) {
			squaring.pop();
		}
		// Complete square => save
		else if (square = Square.valid(squaring)) {
			squares.push(square);
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

		drawSquares();
		drawGrid();
		drawSquaring();
	}

	(window.requestAnimationFrame || window.webkitRequestAnimationFrame)(render);
}
</script>

</body>

</html>
