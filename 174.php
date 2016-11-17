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

<p>Click &amp; drag to draw rectangles.</p>

<p>Score: <code id="score">?</code> (low is good)</p>

<script>
var scoreElement = document.querySelector('#score');
var canvas = document.querySelector('canvas');
var ctx = canvas.getContext('2d');

var SQUARE_SIZE = 80;
var BOARD_SIZE = 5;
var BOARD_MARGIN = 20;
var COLORS = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff'];

var squares = [];
var squaring = [];
var error;
var change = true;
var drawing = false;

// debug //
// squares.push(new Square(new Point(0, 0), new Point(1, 2)));
// debug //

function Point(x, y) {
	this.x = x;
	this.y = y;

	this.findClosestIntersection = function() {
		var x = Math.round((this.x - BOARD_MARGIN) / (SQUARE_SIZE + 1));
		var y = Math.round((this.y - BOARD_MARGIN) / (SQUARE_SIZE + 1));
		return new Point(x, y);
	};

	this.rect = function() {
		return new Point(BOARD_MARGIN + this.x * (SQUARE_SIZE+1), BOARD_MARGIN + this.y * (SQUARE_SIZE+1));
	};

	this.equals = function(point) {
		return this.x == point.x && this.y == point.y;
	};

	this.validNext = function(point) {
		return Math.abs(point.x - this.x) + Math.abs(point.y - this.y) == 1;
	};
}
Point.contains = function(points, point) {
	for (var i = 0; i < points.length; i++) {
		if (points[i].equals(point)) {
			return true;
		}
	}
};

function Line(from, to) {
	this.from = from;
	this.to = to;

	this.equals = function(line) {
		return (this.from.equals(line.from) && this.to.equals(line.to)) || (this.from.equals(line.to) && this.to.equals(line.from));
	};
}

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

	this.points = function() {
		var points = [];

		for (var x = this.from.x; x <= this.to.x; x++) {
			points.push(new Point(x, this.from.y));
			points.push(new Point(x, this.to.y));
		}

		for (var y = this.from.y + 1; y <= this.to.y - 1; y++) {
			points.push(new Point(this.from.x, y));
			points.push(new Point(this.to.x, y));
		}

		return points;
	};

	this.sameSize = function(square) {
		return this.to.x - this.from.x == square.to.x - square.from.x && this.to.y - this.from.y == square.to.y - square.from.y;
	};

	this.sameSizes = function(squares) {
		for (var i = 0; i < squares.length; i++) {
			if (squares[i].sameSize(this)) {
				return true;
			}
		}

		return false;
	};

	this.overlap = function(square) {
		return this.from.x < square.to.x && this.to.x > square.from.x && this.from.y < square.to.y && this.to.y > square.from.y;
	};

	this.overlaps = function(squares) {
		for (var i = 0; i < squares.length; i++) {
			if (squares[i].overlap(this)) {
				return true;
			}
		}

		return false;
	};
}
Square.bounds = function(points) {
	var from = new Point(99, 99);
	var to = new Point(-1, -1);

	for (var i = 0; i < points.length; i++) {
		var point = points[i];

		from.x = Math.min(from.x, point.x);
		from.y = Math.min(from.y, point.y);
		to.x   = Math.max(to.x  , point.x);
		to.y   = Math.max(to.y  , point.y);
	}

	return new Square(from, to);
};
Square.valid = function(points) {
	var square = Square.bounds(points);
	if (square.coverage() == 0) {
		return false;
	}

	var squarePoints = square.points();
	if (squarePoints.length != points.length) {
		return false;
	}

	// @todo Catch snake trail: all points covered, but wrong lines:
	//    _
	// |_| |

	for (var i = 0; i < squarePoints.length; i++) {
		if (!Point.contains(points, squarePoints[i])) {
			return false;
		}
	}

	return square;
};

function drawSquare(square, color, number) {
	// Outer lines
	var rect = square.rect();
	drawLines(rect.concat(rect[0]), 3, color);

	// Inner fill
	ctx.fillStyle = color.replace(/0/g, 'b');
	var from = square.from.rect();
	var to = square.to.rect();
	ctx.fillRect(from.x, from.y, to.x - from.x, to.y - from.y);

	// Number
	number || (number = square.coverage());
	var center = square.center();

	ctx.font = '60px sans-serif';
	ctx.fillStyle = color == 'black' ? 'white' : color;
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

function drawError() {
	if (error) {
		drawSquare(error, 'black', 'x');
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
	var max = -1;
	var min = 99;

	for (var i = 0; i < squares.length; i++) {
		var coverage = squares[i].coverage();
		max = Math.max(max, coverage);
		min = Math.min(min, coverage);
	}

	// @todo Check full coverage

	return max - min;
}

function updateScore() {
	var score = getScore();
	scoreElement.textContent = score ? String(score) : '?';
}

// === //

canvas.onmousedown = function(e) {
	drawing = true;
};
canvas.onmousemove = function(e) {
	if (drawing) {
		var point = new Point(e.offsetX, e.offsetY);
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
	}
};
document.onmouseup = function(e) {
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
	}

	(window.requestAnimationFrame || window.webkitRequestAnimationFrame)(render);
}
</script>

</body>

</html>
