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
	constructor(dirs, lights) {
		this.dirs = dirs;
		this.lights = lights || '';
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

	static goLeft( dir ) {
		return Coords2D.dir4Names[(Coords2D.dir4Names.indexOf(dir) + 3) % 4];
	}

	static goRight( dir ) {
		return Coords2D.dir4Names[(Coords2D.dir4Names.indexOf(dir) + 1) % 4];
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

	getOpposite( dir ) {
		return Coords2D.dir4Names[ (Coords2D.dir4Names.indexOf(dir) + 2) % 4 ];
	}

	chooseDirection() {
		var grid = this.world.map[this.grid.y][this.grid.x];
		var dir = this.nextDirections.shift() || grid.random();

		var uturn = dir == this.getOpposite(this.direction);
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
		var left = this.constructor.goLeft(this.direction);
		var turn = this.getOpposite(this.direction) == dir ? 'u' : ( left == dir ? 'l' : 'r' );

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
		const nextGrid = this.grid.add(Coords2D.dir4Coords[Coords2D.dir4Names.indexOf(this.direction)]);
		const nextSquare = this.world.map[nextGrid.y] && this.world.map[nextGrid.y][nextGrid.x];
		if ( !nextSquare || !nextSquare.includes(this.getOpposite(this.direction)) ) {
			return;
		}

		return nextGrid;
	}

	gridPosition(location) {
		const {direction, position} = location;

		var hor = direction == 'r' || direction == 'l';

		var dirMoves = direction == 'u' || direction == 'l' ? -1 : 1;
		var dirStart = dirMoves == 1 ? 0 : 3;

		var forwardAxis = hor ? 'x' : 'y';
		var sidewayAxis = hor ? 'y' : 'x';

		var sidewayPosition = ['d', 'l'].includes(direction) ? 0 : 1;

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
			u: [-25, -50, 50, 25],
			r: [ 25, -25, 25, 50],
			d: [-25,  25, 50, 25],
			l: [-50, -25, 25, 50],
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
				sides.u && this.line({x: sx + 50, y: sy + lineOffset}, {x: sx + 50, y: sy + 50 - lineOffset}, 'white', 2);
				// S
				sides.d && this.line({x: sx + 50, y: sy + 50 + lineOffset}, {x: sx + 50, y: sy + 100 - lineOffset}, 'white', 2);
				// W
				sides.l && this.line({x: sx + lineOffset, y: sy + 50}, {x: sx + 50 - lineOffset, y: sy + 50}, 'white', 2);
				// E
				sides.r && this.line({x: sx + 50 + lineOffset, y: sy + 50}, {x: sx + 100 - lineOffset, y: sy + 50}, 'white', 2);
			});
		});
	}

	car(car) {
		var pos = car.gridPosition(car.currentLocation());
		var center = new Coords2D(
			car.grid.x * 101 + pos.x * 25 + 12.5,
			car.grid.y * 101 + pos.y * 25 + 12.5,
		);

		var carshape = this.carshape.rotate(Math.PI/2 * Coords2D.dir4Names.indexOf(car.direction));
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
