<?php
// TRAFFIC

?>
<!doctype html>
<html>

<head>
<title>Traffic</title>
<style>
canvas {
	border: solid 1px black;
}
</style>
</head>

<body xonload="init()">

<canvas width="403" height="403"></canvas>

<div id="cars"></div>

<script>
var $cars, $canvas, ctx;

$cars = document.querySelector('#cars');
$canvas = document.querySelector('canvas');
ctx = $canvas.getContext('2d');

var D = {n: [-25, -50, 50, 25], e: [25, -25, 25, 50], s: [-25, 25, 50, 25], w: [-50, -25, 25, 50]};
var O = {n: [0, -1], e: [1, 0], s: [0, 1], w: [-1, 0]};
var OP = {n: 's', s: 'n', e: 'w', w: 'e'};



var GRID = [
	[
		'es',
		'esw',
		'esw',
		'swe',
	],
	[
		'nes',
		'nesw',
		'nsw',
		'n',
	],
	[
		'ns',
		'ns',
		'nes',
		'sw',
	],
	[
		'new',
		'new',
		'new',
		'nw',
	],
];



function Car(grid, direction, position) {
	this.grid = grid || [0, 0]; // 0,0 - 3,3
	this.direction = direction || 'e'; // nesw
	this.position = position || 0; // 0 - 3
	this.nextMoves = [];
}
Car.goLeft = function(dir) {
	var di = 'nesw'.indexOf(dir);
	return di == 0 ? 'w' : 'nesw'[di-1];
};
Car.goRight = function(dir) {
	var di = 'nesw'.indexOf(dir);
	return di == 3 ? 'n' : 'nesw'[di+1];
};
Car.prototype.move = function() {
	// Pre-defined move
	if ( this.nextMoves.length ) {
		var nextMove = this.nextMoves.shift();

		// Move forward
		if ( nextMove == 'position' ) {
			this.position++;
		}
		else {
			nextMove.direction && (this.direction = nextMove.direction);
			nextMove.position && (this.position = nextMove.position);
		}
	}
	// Pick a direction
	else if ( this.position == 1 ) {
		var dir = this.chooseDirection();
		this.assignNextMoves(dir);

// console.log(dir, JSON.stringify(this.nextMoves, ''));
		this.move();
	}
	else {
		this.position++;

		// Advance a square
		if ( this.position == 4 ) {
			var nextSquare = this.nextSquare();
			if ( nextSquare ) {
				this.position = 0;
				this.grid = nextSquare;
			}
			else {
				// Remove this car from the grid
				var ci = cars.indexOf(this);
				cars.splice(ci, 1);
			}
		}
	}
};
Car.prototype.chooseDirection = function() {
	var grid = GRID[this.grid[1]][this.grid[0]],
		dir = grid[Math.floor(Math.random() * grid.length)];

	var uturn = dir == OP[this.direction];
	if ( uturn && !(grid.length == 1 || grid.length == 4) ) {
		return this.chooseDirection();
	}

	return dir;
};
Car.prototype.assignNextMoves = function(dir) {
	// If straight ahead, don't do anything
	if ( dir == this.direction ) {
		return this.nextMoves.push('position');
	}

	// u = U turn
	// l = left
	// r = right
	var di = 'nesw'.indexOf(this.direction),
		left = di == 0 ? 'w' : 'nesw'[di-1],
		turn = OP[this.direction] == dir ? 'u' : ( left == dir ? 'l' : 'r' );

	switch ( turn ) {
		case 'u':
			this.nextMoves.push('position');
			var d2 = Car.goLeft(this.direction);
			this.nextMoves.push({direction: d2, position: 2});
			// this.nextMoves.push('position');
			var d3 = Car.goLeft(d2);
			this.nextMoves.push({direction: d3, position: 2});
			this.nextMoves.push('position');
			break;

		case 'l':
			this.nextMoves.push('position');
			var d2 = Car.goLeft(this.direction);
			this.nextMoves.push({direction: d2, position: 2});
			this.nextMoves.push('position');
			break;

		case 'r':
			var d2 = Car.goRight(this.direction);
			this.nextMoves.push({direction: d2, position: 3});
			// this.nextMoves.push('position');
			break;
	}
};
Car.prototype.nextSquare = function() {
	var nextGrid = JSON.parse(JSON.stringify(this.grid));
	nextGrid[0] += O[this.direction][0];
	nextGrid[1] += O[this.direction][1];
	var nextSquare = GRID[nextGrid[1]] && GRID[nextGrid[1]][nextGrid[0]];
	if ( !nextSquare || nextSquare.indexOf(OP[this.direction]) == -1 ) {
		return;
	}

	return nextGrid;
};
Car.prototype.draw = function() {
	var hor = this.direction == 'e' || this.direction == 'w',
		w = hor ? 15 : 9,
		h = hor ? 9 : 15;

	var sx = this.grid[0] * 101,
		sy = this.grid[1] * 101;

	var d = this.direction == 'n' || this.direction == 'w' ? -1 : 1,
		s = d == 1 ? 0 : 100;

	var sp = this.direction == 'w' || this.direction == 's' ? 25 : 50;

	var p = {x: sx, y: sy},
		a1 = hor ? 'x' : 'y',
		a2 = hor ? 'y' : 'x';

	p[a1] += s + this.position * 25 * d + 5 * d;
	if ( d == -1 ) {
		p[a1] -= hor ? w: h;
	}

	p[a2] += sp + 5 + 3;

	ctx.fillStyle = 'red';
	ctx.fillRect(p.x, p.y, w, h);
};



var draw = {
	line: function(from, to, color, width) {
		ctx.lineWidth = width;
		ctx.strokeStyle = color;
		ctx.beginPath();
		ctx.moveTo(from.x, from.y);
		ctx.lineTo(to.x, to.y);
		ctx.stroke();
	},
	structure: function() {
		GRID.forEach(function(line, y) {
			line.forEach(function(square, x) {
				var sx = x * 101,
					sy = y * 101,
					ex = sx + 100,
					ey = sy + 100;

				// Square background
				ctx.fillStyle = 'green';
				ctx.fillRect(sx, sy, 100, 100);

				// Square center
				ctx.fillStyle = 'black';
				ctx.fillRect(sx + 25, sy + 25, 50, 50);

				// Side streets
				var sides = {};
				for ( var dir, i=0; i<square.length; i++ ) {
					dir = square[i]
					sides[dir] = 1;

					var left = 50 + D[dir][0],
						top = 50 + D[dir][1];
					ctx.fillRect(sx + left, sy + top, D[dir][2], D[dir][3]);
				}

				// White lines
				var lineOffset = 5;
				// N
				sides.n && draw.line({x: sx + 50, y: sy + lineOffset}, {x: sx + 50, y: sy + 50 - lineOffset}, 'white', 2);
				// S
				sides.s && draw.line({x: sx + 50, y: sy + 50 + lineOffset}, {x: sx + 50, y: sy + 100 - lineOffset}, 'white', 2);
				// W
				sides.w && draw.line({x: sx + lineOffset, y: sy + 50}, {x: sx + 50 - lineOffset, y: sy + 50}, 'white', 2);
				// E
				sides.e && draw.line({x: sx + 50 + lineOffset, y: sy + 50}, {x: sx + 100 - lineOffset, y: sy + 50}, 'white', 2);
			});
		});
	},
	cars: function() {
		cars.forEach(function(car) {
			car.draw();
		});
	},
	redraw: function() {
		draw.structure();
		draw.cars();
	}
};



var car1 = new Car([0, 0], 's', 1);
// console.log(car1);
car1.draw();

var car2 = new Car([0, 1], 'w', 1);
// console.log(car2);
car2.draw();

var car3 = new Car([0, 2], 'n', 1);
// console.log(car3);
car3.draw();

var car4 = new Car([0, 3], 'e', 0);
// console.log(car4);
car4.draw();



cars = [car1, car2, car3, car4];

function addCarButton(label, onclick) {
	var btn = document.createElement('button');
	btn.textContent = label;
	btn.onclick = onclick;
	$cars.appendChild(btn);
	return btn;
}

function moveAllCars() {
	cars.forEach(function(car) {
		car.move();
	});
	draw.redraw();
}

addCarButton('All', function(e) {
	moveAllCars();
}).autofocus = 1;

var timer = 0;
addCarButton('Start/stop', function(e) {
	if ( timer ) {
		clearInterval(timer);
		timer = 0;
	}
	else {
		timer = setInterval(function() {
			moveAllCars();
		}, 200);
		moveAllCars();
	}
}).click();

cars.forEach(function(car, i) {
	addCarButton('Car ' + (i+1), function(e) {
		car.move();
		draw.redraw();
	});
});



draw.redraw();
</script>

</body>

</html>
