
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

class World {
	constructor(map) {
		this.map = map;
		this.carIndex = 0;
		this.cars = [];
		this.change = true;
	}

	addCar(grid, direction, position) {
		const car = new Car(this, grid, direction, position);
		car.name = String(++this.carIndex);
		this.cars.push(car);
		this.change = true;
		return Promise.resolve(car);
	}

	removeCar(car) {
		var ci = this.cars.indexOf(car);
		this.cars.splice(ci, 1);
	}
}

class Car {
	constructor(world, grid, direction, position) {
		this.world = world;
		this.name = null;
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
		return !this.world.cars.some((car) => {
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
					this.world.removeCar(this);
				}
			}
		}
	}

	chooseDirection() {
		var grid = this.world.map[this.grid.y][this.grid.x];
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
		const nextSquare = this.world.map[nextGrid.y] && this.world.map[nextGrid.y][nextGrid.x];
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

class Drawer {
	constructor(canvas, world) {
		this.canvas = canvas;
		this.ctx = canvas.getContext('2d');
		this.world = world;
		this.carshape = new CarShape();
	}

	line(from, to, color, width) {
		this.ctx.lineWidth = width;
		this.ctx.strokeStyle = color;
		this.ctx.beginPath();
		this.ctx.moveTo(from.x, from.y);
		this.ctx.lineTo(to.x, to.y);
		this.ctx.stroke();
	}

	structure() {
		this.canvas.width = this.world.map[0].length * 101 - 1;
		this.canvas.height = this.world.map.length * 101 - 1;

		const D = {
			n: [-25, -50, 50, 25],
			e: [ 25, -25, 25, 50],
			s: [-25,  25, 50, 25],
			w: [-50, -25, 25, 50],
		};

		this.world.map.forEach((line, y) => {
			line.forEach((square, x) => {
				var sx = x * 101;
				var sy = y * 101;
				var ex = sx + 100;
				var ey = sy + 100;

				// Square background
				this.ctx.fillStyle = 'green';
				this.ctx.fillRect(sx, sy, 100, 100);

				// Square center
				this.ctx.fillStyle = 'black';
				this.ctx.fillRect(sx + 25, sy + 25, 50, 50);

				// Side streets
				var sides = {};
				for ( var dir of square ) {
					sides[dir] = 1;

					var left = 50 + D[dir][0];
					var top = 50 + D[dir][1];
					this.ctx.fillRect(sx + left, sy + top, D[dir][2], D[dir][3]);
				}

				// White lines
				var lineOffset = 5;
				// N
				sides.n && this.line({x: sx + 50, y: sy + lineOffset}, {x: sx + 50, y: sy + 50 - lineOffset}, 'white', 2);
				// S
				sides.s && this.line({x: sx + 50, y: sy + 50 + lineOffset}, {x: sx + 50, y: sy + 100 - lineOffset}, 'white', 2);
				// W
				sides.w && this.line({x: sx + lineOffset, y: sy + 50}, {x: sx + 50 - lineOffset, y: sy + 50}, 'white', 2);
				// E
				sides.e && this.line({x: sx + 50 + lineOffset, y: sy + 50}, {x: sx + 100 - lineOffset, y: sy + 50}, 'white', 2);
			});
		});
	}

	car(car) {
		var pos = car.gridPosition(car.currentLocation());
		var center = new Coords2D(
			car.grid.x * 101 + pos.x * 25 + 12.5,
			car.grid.y * 101 + pos.y * 25 + 12.5,
		);

		var directions = ['n', 'e', 's', 'w'];
		var carshape = this.carshape.rotate(Math.PI/2 * directions.indexOf(car.direction));
		this.ctx.fillStyle = 'red';
		this.ctx.beginPath();
		carshape.points.forEach((point, i) => {
			this.ctx[i == 0 ? 'moveTo' : 'lineTo'](center.x + point.x * 3, center.y + point.y * 3);
		});
		this.ctx.closePath();
		this.ctx.fill();

		this.ctx.fillStyle = 'white';
		this.ctx.font = '13px sans-serif';
		this.ctx.fillText(car.name, center.x-3, center.y+4);
	}

	cars() {
		this.world.cars.forEach((car) => this.car(car));
	}

	redraw() {
		if ( !this.world.change ) return;
		this.world.change = false;

		// console.time('redraw');
		this.structure();
		this.cars();
		// console.timeEnd('redraw');
	}
}
