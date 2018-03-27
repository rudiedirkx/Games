class Point {
	constructor(x, y) {
		this.x = x;
		this.y = y;
	}

	factor(factor) {
		this.x *= factor;
		this.y *= factor;
		return this;
	}

	distanceTo(point) {
		return Math.sqrt(Math.pow(Math.abs(point.x - this.x), 2) + Math.pow(Math.abs(point.y - this.y), 2));
	}

	findClosestIntersection() {
		var x = Math.round((this.x - BOARD_MARGIN) / (SQUARE_SIZE + 1));
		var y = Math.round((this.y - BOARD_MARGIN) / (SQUARE_SIZE + 1));
		return new Point(x, y);
	}

	rect() {
		return new Point(BOARD_MARGIN + this.x * (SQUARE_SIZE+1), BOARD_MARGIN + this.y * (SQUARE_SIZE+1));
	}

	equals(point) {
		return this.x == point.x && this.y == point.y;
	}

	validNext(point) {
		return Math.abs(point.x - this.x) + Math.abs(point.y - this.y) == 1;
	}

	static contains(points, point) {
		for (var i = 0; i < points.length; i++) {
			if (points[i].equals(point)) {
				return true;
			}
		}
	}
}

class Line {
	constructor(from, to) {
		this.from = from;
		this.to = to;
	}

	equals(line) {
		return (this.from.equals(line.from) && this.to.equals(line.to)) || (this.from.equals(line.to) && this.to.equals(line.from));
	}
}

class Square {
	constructor(from, to) {
		this.from = from;
		this.to = to;
	}

	rect() {
		return [
			this.from.rect(),
			(new Point(this.to.x, this.from.y)).rect(),
			this.to.rect(),
			(new Point(this.from.x, this.to.y)).rect(),
		];
	}

	center() {
		return (new Point(
			this.from.x + (this.to.x - this.from.x)/2,
			this.from.y + (this.to.y - this.from.y)/2
		)).rect();
	}

	coverage() {
		return (this.to.x - this.from.x) * (this.to.y - this.from.y);
	}

	points() {
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
	}

	sameSize(square) {
		return this.to.x - this.from.x == square.to.x - square.from.x && this.to.y - this.from.y == square.to.y - square.from.y;
	}

	sameSizes(squares) {
		for (var i = 0; i < squares.length; i++) {
			if (squares[i].sameSize(this)) {
				return true;
			}
		}

		return false;
	}

	overlap(square) {
		return this.from.x < square.to.x && this.to.x > square.from.x && this.from.y < square.to.y && this.to.y > square.from.y;
	}

	overlaps(squares) {
		for (var i = 0; i < squares.length; i++) {
			if (squares[i].overlap(this)) {
				return true;
			}
		}

		return false;
	}

	static bounds(points) {
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
	}

	static valid(points) {
		var square = Square.bounds(points);
		if (square.coverage() == 0) {
			return false;
		}

		var squarePoints = square.points();
		if (squarePoints.length != points.length) {
			return false;
		}

		var distance = points[0].distanceTo(points[points.length-1]);
		if (distance !== 1) {
			return false;
		}

		for (var i = 0; i < squarePoints.length; i++) {
			if (!Point.contains(points, squarePoints[i])) {
				return false;
			}
		}

		return square;
	}
}
