<?php
// TRAFFIC

require __DIR__ . '/inc.bootstrap.php';

// - reuse nextLocation() in move()

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
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<div id="cars"></div>

<script>
var $cars, $canvas, ctx;

$cars = document.querySelector('#cars');
$canvas = document.querySelector('canvas');
ctx = $canvas.getContext('2d');

Coords2D.dirOffset = {
	n: new Coords2D(0, -1),
	e: new Coords2D(1, 0),
	s: new Coords2D(0, 1),
	w: new Coords2D(-1, 0),
};
Coords2D.dirOpposite = {
	n: 's',
	s: 'n',
	e: 'w',
	w: 'e',
};

class CarShape {
	constructor(points) {
		this.points = points || [
			new Coords2D( 0, -2.5),
			new Coords2D( 2, -1.5),
			new Coords2D( 2,  2.5),
			new Coords2D(-2,  2.5),
			new Coords2D(-2, -1.5),
		];
	}

	rotate(angle) {
		return new CarShape(this.points.map((C) => C.rotate(angle)));
	}
}

class Square {
	constructor(dirs) {
		this.dirs = dirs;
	}
	get length() {
		return this.dirs.length;
	}
	random() {
		return this.dirs[Math.floor(Math.random() * this.length)];
	}
	includes(dir) {
		return this.dirs.includes(dir);
	}
	[Symbol.iterator]() {
		return this.dirs[Symbol.iterator]();
	}
}

var GRID = [
	[
		new Square('es'),
		new Square('esw'),
		new Square('esw'),
		new Square('sw'),
	],
	[
		new Square('nes'),
		new Square('nesw'),
		new Square('nsw'),
		new Square('n'),
	],
	[
		new Square('ns'),
		new Square('ns'),
		new Square('nes'),
		new Square('sw'),
	],
	[
		new Square('new'),
		new Square('new'),
		new Square('new'),
		new Square('nw'),
	],
];



class Car {
	constructor(name, grid, direction, position) {
		this.name = String(name);
		this.grid = grid;
		this.direction = direction;
		this.position = position;
		this.nextMoves = [];
		this.nextDirections = [];
	}

	static goLeft(dir) {
		var di = 'nesw'.indexOf(dir);
		return di == 0 ? 'w' : 'nesw'[di-1];
	}

	static goRight(dir) {
		var di = 'nesw'.indexOf(dir);
		return di == 3 ? 'n' : 'nesw'[di+1];
	}

	isFree(location) {
		if ( !location ) return true;

		var pos = this.locationPosition(location);
		return !cars.some((car) => {
			return car != this && car.locationPosition(car.currentLocation()).equal(pos);
		});
	}

	locationPosition(location) {
		var gridPos = this.gridPosition(location);
		return new Coords2D(
			location.grid.x * 4 + gridPos.x,
			location.grid.y * 4 + gridPos.y
		);
	}

	currentLocation() {
		return {
			grid: this.grid,
			direction: this.direction,
			position: this.position,
		};
	}

	nextLocation() {
		var grid = this.grid;
		var direction = this.direction;
		var position = this.position;

		if ( this.nextMoves.length ) {
			var nextMove = this.nextMoves[0];
			if ( nextMove === 'position' ) {
				position++;
			}
			else {
				nextMove.direction && (direction = nextMove.direction);
				nextMove.position && (position = nextMove.position);
			}
		}
		else {
			position++;

			if ( position == 4 ) {
				var nextSquare = this.nextSquare();
				if ( nextSquare ) {
					position = 0;
					grid = nextSquare;
				}
				else {
					return;
				}
			}
		}

		return {grid, direction, position};
	}

	move() {
		// Pre-defined move
		if ( this.nextMoves.length ) {
			if ( !this.isFree(this.nextLocation()) ) return;

			var nextMove = this.nextMoves.shift();

			// Move forward
			if ( nextMove === 'position' ) {
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

			this.move();
		}
		else {
			if ( !this.isFree(this.nextLocation()) ) return;

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
	}

	chooseDirection() {
		var grid = GRID[this.grid.y][this.grid.x];
		var dir = this.nextDirections.shift() || grid.random();

		var uturn = dir == Coords2D.dirOpposite[this.direction];
		if ( uturn && grid.length > 1 ) {
			return this.chooseDirection();
		}

		return dir;
	}

	assignNextMoves(dir) {
		// If straight ahead, don't do anything
		if ( dir == this.direction ) {
			return this.nextMoves.push('position');
		}

		// u = U turn
		// l = left
		// r = right
		var di = 'nesw'.indexOf(this.direction);
		var left = di == 0 ? 'w' : 'nesw'[di-1];
		var turn = Coords2D.dirOpposite[this.direction] == dir ? 'u' : ( left == dir ? 'l' : 'r' );

		switch ( turn ) {
			case 'u':
				var d2 = Car.goLeft(this.direction);
				var d3 = Car.goLeft(d2);
				this.nextMoves.push('position');
				this.nextMoves.push({direction: d2, position: 1});
				this.nextMoves.push('position');
				this.nextMoves.push({direction: d3, position: 1});
				this.nextMoves.push('position');
				break;

			case 'l':
				var d2 = Car.goLeft(this.direction);
				this.nextMoves.push('position');
				this.nextMoves.push({direction: d2, position: 1});
				this.nextMoves.push({direction: d2, position: 2});
				this.nextMoves.push('position');
				break;

			case 'r':
				var d2 = Car.goRight(this.direction);
				this.nextMoves.push({direction: d2, position: 2});
				break;
		}
	}

	nextSquare() {
		const nextGrid = this.grid.add(Coords2D.dirOffset[this.direction]);
		const nextSquare = GRID[nextGrid.y] && GRID[nextGrid.y][nextGrid.x];
		if ( !nextSquare || !nextSquare.includes(Coords2D.dirOpposite[this.direction]) ) {
			return;
		}

		return nextGrid;
	}

	gridPosition(location) {
		const {direction, position} = location;

		var hor = direction == 'e' || direction == 'w';

		var dirMoves = direction == 'n' || direction == 'w' ? -1 : 1;
		var dirStart = dirMoves == 1 ? 0 : 3;

		var forwardAxis = hor ? 'x' : 'y';
		var sidewayAxis = hor ? 'y' : 'x';

		var sidewayPosition = ['s', 'w'].includes(direction) ? 0 : 1;

		var pos = new Coords2D(0, 0);
		pos[forwardAxis] = dirStart + position * dirMoves;
		pos[sidewayAxis] = 1 + sidewayPosition;
		return pos;
	}
}


var draw = {
	carshape: new CarShape(),
	line(from, to, color, width) {
		ctx.lineWidth = width;
		ctx.strokeStyle = color;
		ctx.beginPath();
		ctx.moveTo(from.x, from.y);
		ctx.lineTo(to.x, to.y);
		ctx.stroke();
	},
	structure() {
		$canvas.width = GRID[0].length * 101 - 1;
		$canvas.height = GRID.length * 101 - 1;

		const D = {
			n: [-25, -50, 50, 25],
			e: [ 25, -25, 25, 50],
			s: [-25,  25, 50, 25],
			w: [-50, -25, 25, 50],
		};

		GRID.forEach(function(line, y) {
			line.forEach(function(square, x) {
				var sx = x * 101;
				var sy = y * 101;
				var ex = sx + 100;
				var ey = sy + 100;

				// Square background
				ctx.fillStyle = 'green';
				ctx.fillRect(sx, sy, 100, 100);

				// Square center
				ctx.fillStyle = 'black';
				ctx.fillRect(sx + 25, sy + 25, 50, 50);

				// Side streets
				var sides = {};
				for ( var dir of square ) {
					sides[dir] = 1;

					var left = 50 + D[dir][0];
					var top = 50 + D[dir][1];
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
	car(car) {
		var pos = car.gridPosition(car.currentLocation());
		var center = new Coords2D(
			car.grid.x * 101 + pos.x * 25 + 12.5,
			car.grid.y * 101 + pos.y * 25 + 12.5,
		);

		var directions = ['n', 'e', 's', 'w'];
		var carshape = draw.carshape.rotate(Math.PI/2 * directions.indexOf(car.direction));
		ctx.fillStyle = 'red';
		ctx.beginPath();
		carshape.points.forEach((point, i) => {
			ctx[i == 0 ? 'moveTo' : 'lineTo'](center.x + point.x * 3, center.y + point.y * 3);
		});
		ctx.closePath();
		ctx.fill();

		ctx.fillStyle = 'white';
		ctx.font = '13px sans-serif';
		ctx.fillText(car.name, center.x-3, center.y+4);
	},
	cars() {
		cars.forEach(this.car);
	},
	redraw() {
		// console.time('redraw');
		draw.structure();
		draw.cars();
		// console.timeEnd('redraw');
	}
};



var change = true;

var cars = [];
cars.push(new Car(cars.length+1, new Coords2D(0, 0), 'w', 0));
cars.push(new Car(cars.length+1, new Coords2D(0, 1), 'w', 1));
cars.push(new Car(cars.length+1, new Coords2D(0, 2), 'n', 1));
cars.push(new Car(cars.length+1, new Coords2D(0, 3), 'e', 0));

// Tick 2 will collide the next 2 cars
cars.push(new Car(cars.length+1, new Coords2D(2, 0), 'n', 0));
cars[cars.length-1].nextDirections.push('w');
cars.push(new Car(cars.length+1, new Coords2D(3, 0), 'w', 3));

// U-turn coming up
cars.push(new Car(cars.length+1, new Coords2D(3, 1), 's', 0));

function addCarButton(label, onclick) {
	var btn = document.createElement('button');
	btn.textContent = label;
	btn.onclick = onclick;
	$cars.append(btn);
	$cars.append(' ');
	return btn;
}

function moveAllCars() {
	cars.forEach((car) => car.move());
	change = true;
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
		timer = setInterval(() => moveAllCars(), 40);
		moveAllCars();
	}
});//.click();

cars.forEach(function(car, i) {
	addCarButton('Car ' + (i+1), (e) => {
		car.move();
		change = true;
	});
});



function keepDrawing() {
	change && draw.redraw();
	change = false;
	requestAnimationFrame(keepDrawing);
}
keepDrawing();
</script>

</body>

</html>
