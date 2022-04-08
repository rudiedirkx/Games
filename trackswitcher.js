"use strict";

class Shape {
	constructor(points, front, rear) {
		this.points = points;
		this.front = front || new Coords2D(1, 0);
		this.rear = rear || new Coords2D(-1, 0);
	}

	rotate(rad) {
		return new Shape(
			this.points.map(P => P.rotate(rad)),
			this.front.rotate(rad),
			this.rear.rotate(rad),
		);
	}
}

class EngineShape extends Shape {
	constructor(points) {
		super(points || [
			new Coords2D(-1, -0.4),
			new Coords2D(0.8, -0.4),
			new Coords2D(1.0, 0.0),
			new Coords2D(0.8, 0.4),
			new Coords2D(-1, 0.4),
		]);
	}
}

class WagonShape extends Shape {
	constructor(points) {
		super(points || [
			new Coords2D(-1, -0.4),
			new Coords2D(1, -0.4),
			new Coords2D(1, 0.4),
			new Coords2D(-1, 0.4),
		]);
	}
}

class Track {
	constructor(name, x1, y1, x2, y2, angle, stoppable = true) {
		this.name = name;
		this.from = new Coords2D(x1, y1);
		this.to = new Coords2D(x2, y2);
		if (x1 > x2 /*|| (x1 == x2 && y1 > y2)*/) {
			[this.from, this.to] = [this.to, this.from];
		}
		this.angle = angle;
		this.stoppable = stoppable;
	}

	get center() {
		return new Coords2D((this.from.x + this.to.x) / 2, (this.from.y + this.to.y) / 2);
	}

	get slope() {
		return Math.round((this.from.y - this.to.y) / (this.from.x - this.to.x) * 100) / 100;
	}

	touchesTrack(other) {
		return other.touchesCoord(this.from) || other.touchesCoord(this.to);
	}

	touchesCoord(C) {
		return C.equal(this.from) || C.equal(this.to);
	}

	drawRoute(game) {
		game.drawLine(game.scale(this.from), game.scale(this.to), {
			color: TrackSwitcher.ROUTE_COLOR,
			width: TrackSwitcher.ROUTE_WIDTH,
		});
	}

	drawTrackOuter(game) {
		const from = game.scale(this.from);
		const to = game.scale(this.to);
		game.drawLine(from, to, {color: TrackSwitcher.TRACK_COLOR, width: TrackSwitcher.TRACK_WIDTH});
	}

	drawTrackInner(game) {
		const from = game.scale(this.from);
		const to = game.scale(this.to);
		game.drawLine(from, to, {color: TrackSwitcher.BGCOLOR, width: TrackSwitcher.TRACK_INNER});
	}

	drawShape(game, model, color, reverse = false) {
		const center = game.scale(this.center);
		const angle = this.angle + (reverse ? 180 : 0);
		const shape = model.rotate(angle / 180 * Math.PI);
		const SF = 0.85;

		game.ctx.fillStyle = color;
		game.ctx.beginPath();
		shape.points.forEach((point, i) => {
			const C = center.add(point.multiply(TrackSwitcher.SQUARE * SF));
			game.ctx[i == 0 ? 'moveTo' : 'lineTo'](C.x, C.y);
		});
		game.ctx.closePath();
		game.ctx.fill();

		const front = center.add(shape.front.multiply(TrackSwitcher.SQUARE * SF));
		game.drawDot(front);
		const rear = center.add(shape.rear.multiply(TrackSwitcher.SQUARE * SF));
		game.drawDot(rear);
	}

	drawEngine(game, car) {
		const color = car ? TrackSwitcher.CAR_COLORS[car.id] : TrackSwitcher.WAGON_COLOR;
		return this.drawShape(game, TrackSwitcher.SHAPE_ENGINE, color, car ? car.direction == -1 : false);
	}

	drawWagon(game, car) {
		const color = car ? TrackSwitcher.CAR_COLORS[car.id] : TrackSwitcher.WAGON_COLOR;
		return this.drawShape(game, TrackSwitcher.SHAPE_WAGON, color);
	}
}

class CircularTrack extends Track {
	constructor(center, radius, startAngle, endAngle) {
		super();
		this.center = center;
		this.radius = radius;
		this.startAngle = startAngle;
		this.endAngle = endAngle;
	}

	drawTrack(game, color, width) {
		color || (color = TrackSwitcher.TRACK_COLOR); // game.randomColor();
		width || (width = TrackSwitcher.TRACK_WIDTH);
		const C = game.scale(this.center);

		game.ctx.strokeStyle = color;
		game.ctx.lineWidth = width;

		game.ctx.beginPath();
		game.ctx.arc(C.x, C.y, this.radius * TrackSwitcher.SQUARE, this.startAngle, this.endAngle);
		game.ctx.stroke();
	}
}

class TrackConn {
	constructor(from, to, x, y) {
		this.from = from;
		this.to = to;
		this.button = new Coords2D(x, y);
	}
}

class Route {
	constructor(game, start) {
		this.game = game;
		this.tracks = [start];
	}

	get length() {
		return this.tracks.length;
	}

	get first() {
		return this.tracks[0];
	}

	get last() {
		return this.tracks[this.tracks.length - 1];
	}

	get beforeLast() {
		return this.tracks[this.tracks.length - 2];
	}

	get head() {
		if (this.tracks.length < 2) return null;

		const last = this.last;
		const beforeLast = this.beforeLast;
		return last.from.equal(beforeLast.from) || last.from.equal(beforeLast.to) ? last.to : last.from;
	}

	connectsTo(track) {
		if (this.head) {
			return track.touchesCoord(this.head);
		}

		return track.touchesTrack(this.last);
	}

	includes(track) {
		return this.tracks.includes(track);
	}

	add(track) {
		const last = this.last;
		const car = this.game.getCar(last);
		this.tracks.push(track);
		this.moveCar(car, last);
	}

	undo() {
		const last = this.last;
		const car = this.game.getCar(last);
		this.tracks.pop();
		this.moveCar(car, last);
	}

	moveCar(car, before) {
		car.location = this.last.name;

		const bla = before.angle;
		const la = this.last.angle;
		if ((bla < 0 && la > 0) || (bla > 0 && la < 0)) {
			car.reverse();
		}
	}
}

class Problem {
	constructor(moves, froms, tos) {
		this.moves = moves;
		this.froms = froms;
		this.tos = tos;
	}
}

class Wagon {
	constructor(id, location) {
		this.id = id;
		this.location = location;
	}

	draw(game) {
		const track = game.getTrack(this.location);
		track.drawWagon(game, this);
	}

	reverse() {
	}

	equal(car) {
		return car.id == this.id && car.location == this.location;
	}

	clone() {
		return new Wagon(this.id, this.location);
	}
}

class Engine extends Wagon {
	constructor(id, location, direction) {
		super(id, location);
		this.direction = direction;
	}

	draw(game) {
		const track = game.getTrack(this.location);
		track.drawEngine(game, this);
	}

	reverse() {
		this.direction *= -1;
	}

	equal(car) {
		return super.equal(car) && car.direction == this.direction;
	}

	clone() {
		return new Engine(this.id, this.location, this.direction);
	}
}

class TrackSwitcher extends CanvasGame {

	static OFFSET = 1;
	static SQUARE = 40;
	static SQUARE = 40;
	static MARGIN = 0;
	static STOP_RADIUS = 12;
	static STOP_WIDTH = 3;
	static STOP_COLOR = '#aaa';
	static BGCOLOR = '#eee';
	static TRACK_WIDTH = 14;
	static TRACK_INNER = 6;
	static TRACK_COLOR = '#aaa';
	static ROUTE_COLOR = '#000';
	static ROUTE_WIDTH = 4;
	static CONNECTOR_RADIUS = 8;
	static WAGON_COLOR = 'red';
	static CAR_COLORS = ['red', 'green', 'blue', 'orange'];

	static WIDTH = 16;
	static HEIGHT = 10;
	static TRACKS = [
		new Track('1-1', 0, 1, 2, 1, 0), new Track('1-2', 2, 1, 4, 1, 0), new Track('1-3', 4, 1, 6, 1, 0), new Track('1-4', 6, 1, 8, 1, 0),
		new Track('1-5', 8, 1, 10, 1, 0), new Track('1-6', 10, 1, 12, 1, 0), new Track('1-7', 12, 1, 14, 1, 0), new Track('1-8', 14, 1, 16, 1, 0),
		new Track('2', 8, 1, 6, 3, -45),
		new Track('3-1', 6, 3, 8, 3, 0), new Track('3-2', 8, 3, 10, 3, 0),
		new Track('4-1', 6, 3, 4, 5, -45), new Track('4-2', 10, 3, 12, 5, 45),
		new Track('ct', 8, 3, 9, 5, 60, false), new Track('cb', 9, 5, 8, 7, -60, false),
		new Track('5-1', 0, 5, 2, 5, 0), new Track('5-2', 2, 5, 4, 5, 0), new Track('5-3', 12, 5, 14, 5, 0), new Track('5-4', 14, 5, 16, 5, 0),
		new Track('6-1', 6, 7, 4, 5, 45), new Track('6-2', 10, 7, 12, 5, -45),
		new Track('7-1', 6, 7, 8, 7, 0), new Track('7-2', 8, 7, 10, 7, 0),
		new Track('lb-1', 6, 7, 4, 8, -30), new Track('lb-2', 4, 8, 3, 10, -60),
		new Track('rb-1', 8, 7, 10, 8.5, 36), new Track('rb-2', 10, 8.5, 12, 8.5, 0), new Track('rb-3', 12, 8.5, 14, 10, 36),
	];
	// static TRACK_CONNECTORS = [
	// 	new TrackConn('1-1', '1-2', 2, 0.5), new TrackConn('1-2', '1-3', 4, 0.5), new TrackConn('1-3', '1-4', 6, 0.5), new TrackConn('1-4', '1-5', 8, 0.5),
	// 	new TrackConn('1-5', '1-6', 10, 0.5), new TrackConn('1-6', '1-7', 12, 0.5), new TrackConn('1-7', '1-8', 14, 0.5),
	// 	new TrackConn('1-5', '2', 8.3, 1.5),
	// 	new TrackConn('2', '4-1', 5.5, 2.7), new TrackConn('3-1', '4-1', 6.3, 3.5), new TrackConn('3-1', '3-2', 8, 2.5), new TrackConn('3-2', '4-2', 10.3, 2.5),
	// 	new TrackConn('5-1', '5-2', 2, 4.5), new TrackConn('4-1', '5-2', 3.8, 4.4), new TrackConn('5-2', '6-1', 3.8, 5.6),
	// 	new TrackConn('6-1', '7-1', 6.1, 6.3), new TrackConn('cb', '7-1', 7.7, 6.4), new TrackConn('6-2', '7-2', 9.8, 6.4),
	// 	new TrackConn('7-1', 'lb-1', 6.2, 7.5), new TrackConn('7-1', 'rb-1', 7.8, 7.5),
	// ];
	static UNBENDABLES = ['lb-1:6-1', '6-1:4-1', '3-1:2', '1-4:2', 'rb-1:7-2', 'rb-1:cb', 'cb:7-2', '4-2:6-2', '3-2:ct'];

	static SHAPE_ENGINE = new EngineShape();
	static SHAPE_WAGON = new WagonShape();

	static PROBLEMS = [
		new Problem(3, // 1
			[new Engine(0, '1-8', -1), new Wagon(1, '5-1')],
			[new Engine(0, '5-4', 1), new Wagon(1, '5-3')],
		),
		new Problem(3, // 2
			[new Engine(0, '5-4', -1), new Wagon(1, '5-3')],
			[new Engine(0, '5-4', 1), new Wagon(1, '5-3')],
		),
		new Problem(3, // 3
			[new Engine(0, '1-7', -1), new Wagon(1, '1-8'), new Wagon(2, '5-4')],
			[new Engine(0, 'lb-2', 1), new Wagon(1, 'lb-1'), new Wagon(2, '7-1')],
		),
		new Problem(5, // 4
			[new Engine(0, '3-1', 1), new Wagon(1, '5-1'), new Wagon(2, '7-1'), new Wagon(3, 'rb-3')],
			[new Engine(0, '1-8', 1), new Wagon(1, '1-6'), new Wagon(2, 'lb-2'), new Wagon(3, '1-7')],
		),
		new Problem(3, // 5
			[new Engine(0, '5-1', -1), new Engine(0, '5-4', 1), new Wagon(1, '3-1')],
			[new Engine(0, 'rb-1', -1), new Engine(0, 'rb-3', -1), new Wagon(1, 'rb-2')],
		),
	];

	createGame() {
		super.createGame();

		this.paintingTiming = true;

		this.$levels = $('#levels');
		this.$showNames = $('#show-names');
		this.$showSolution = $('#show-solution');

		this.createLevelSelect();
		this.interTracks = this.createInterTracks();
	}

	createLevelSelect() {
		const html = TrackSwitcher.PROBLEMS.map((P, n) => `<option value="${n}">${n+1} (${P.moves} mv)</option>`).join('');
		this.$levels.setHTML(html);
	}

	createInterTracks() {
		const coords = [];
		TrackSwitcher.TRACKS.forEach(T => {
			coords.find(C => C.equal(T.to)) || coords.push(T.to);
			coords.find(C => C.equal(T.from)) || coords.push(T.from);
		});
		return coords;
	}

	reset() {
		super.reset();

		this.level = 0;
		this.cars = [];

		this.dragging = 0;
		// this.draggingFrom = null;
		this.draggingRoute = null;

		this.connecteds = [];
	}

	drawStructure() {
		this.drawFill(TrackSwitcher.BGCOLOR);
		this.drawStops();
		this.drawTracks();
		// this.drawConnectors();
	}

	drawContent() {
		this.drawCars();
		this.drawDragging();

		if (this.$showNames.checked) {
			this.drawTrackNames();
		}
	}

	drawCars() {
		this.getCars().forEach(car => {
			car.draw(this);
		});
	}

	drawDragging() {
		if (this.draggingRoute) {
			this.draggingRoute.tracks.forEach(track => track.drawRoute(this));
		}
	}

	drawStops() {
		TrackSwitcher.TRACKS.map(T => {
			const C = T.center;
			if (T.stoppable) {
				this.drawCircle(this.scale(C), TrackSwitcher.STOP_RADIUS, {
					color: TrackSwitcher.STOP_COLOR,
					width: TrackSwitcher.STOP_WIDTH,
				});
			}
			// else {
			// 	this.drawLine(this.scale(C).add(new Coords2D(-7, -7)), this.scale(C).add(new Coords2D(7, 7)));
			// 	this.drawLine(this.scale(C).add(new Coords2D(-7, 7)), this.scale(C).add(new Coords2D(7, -7)));
			// }
		});
	}

	drawTracks() {
		this.interTracks.forEach(C => this.drawDot(this.scale(C), {
			radius: TrackSwitcher.TRACK_WIDTH/2,
			color: TrackSwitcher.TRACK_COLOR,
		}));

		TrackSwitcher.TRACKS.map(track => track.drawTrackOuter(this));
		TrackSwitcher.TRACKS.map(track => track.drawTrackInner(this));
	}

	// drawConnectors() {
	// 	TrackSwitcher.TRACK_CONNECTORS.forEach(inter => {
	// 		// if (!this.getCar(this.getTrack(inter.from)) || !this.getCar(this.getTrack(inter.to))) return;

	// 		const C = this.scale(inter.button);
	// 		const enabled = this.connecteds.includes(inter);
	// 		this.drawDot(C, {
	// 			radius: TrackSwitcher.CONNECTOR_RADIUS,
	// 			color: enabled ? '#0c0' : '#f00',
	// 		});
	// 		this.drawCircle(C, TrackSwitcher.CONNECTOR_RADIUS, {
	// 			color: '#000',
	// 			width: 2,
	// 		});
	// 	});
	// }

	drawTrackNames() {
		TrackSwitcher.TRACKS.map(track => this.drawTrackName(track));
	}

	drawTrackName(track) {
		this.ctx.textAlign = 'left';
		this.ctx.textBaseline = 'bottom';
		this.drawText(this.scale(track.center), track.name, {color: 'green'});
	}

	getTrack(location) {
		return TrackSwitcher.TRACKS.find(T => T.name == location);
	}

	getCars() {
		return this.$showSolution.checked ? TrackSwitcher.PROBLEMS[this.level].tos : this.cars;
	}

	getCar(track) {
		return this.getCars().find(car => car.location === track.name);
	}

	getEngine(track) {
		const car = this.getCar(track);
		return car instanceof Engine ? car : null;
	}

	getConnInter(conn) {
		return this.getInter(this.getTrack(conn.from), this.getTrack(conn.to));
	}

	getInter(track1, track2) {
		if (track1.from.equal(track2.from)) {
			return track1.from;
		}
		if (track1.to.equal(track2.to)) {
			return track1.to;
		}
		if (track1.from.equal(track2.to)) {
			return track1.from;
		}
		if (track1.to.equal(track2.from)) {
			return track1.to;
		}
	}

	startGame(level = 0) {
		this.reset();

		this.canvas.width = TrackSwitcher.OFFSET + TrackSwitcher.WIDTH * (TrackSwitcher.SQUARE + TrackSwitcher.MARGIN) - TrackSwitcher.MARGIN + TrackSwitcher.OFFSET;
		this.canvas.height = TrackSwitcher.OFFSET + TrackSwitcher.HEIGHT * (TrackSwitcher.SQUARE + TrackSwitcher.MARGIN) - TrackSwitcher.MARGIN + TrackSwitcher.OFFSET;

		this.$showSolution.checked = false;
		this.loadLevel(level);
		this.changed = true;
	}

	loadLevel(n) {
		this.level = n;
		this.$levels.value = n;

		this.cars = TrackSwitcher.PROBLEMS[this.level].froms.map(car => car.clone());
	}

	scale( source, square ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x, TrackSwitcher.SQUARE), this.scale(source.y, TrackSwitcher.SQUARE));
		}

		return TrackSwitcher.OFFSET + source * (square + TrackSwitcher.MARGIN);
	}

	// unscale( source, square ) {
	// 	if ( source instanceof Coords2D ) {
	// 		source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
	// 		const C = new Coords2D(this.unscale(source.x, TrackSwitcher.SQUARE), this.unscale(source.y, TrackSwitcher.SQUARE));
	// 		return C;
	// 	}

	// 	return Math.round((source - TrackSwitcher.OFFSET - square/2) / (TrackSwitcher.MARGIN + square));
	// }

	findClosestTrack(C) {
		const dists = TrackSwitcher.TRACKS.map(T => this.scale(T.center).distance(C));
		const sorted = dists
			.map((d, i) => ([d, i]))
			.sort((a, b) => a[0] - b[0])
			.filter(([d, i]) => {
				return d < 20;
			});
			// .filter(([d, i]) => TrackSwitcher.TRACKS[i].stoppable);
		return sorted.length ? TrackSwitcher.TRACKS[sorted[0][1]] : null;
	}

	canBendTo(a, b) {
		return !TrackSwitcher.UNBENDABLES.includes(`${a.name}:${b.name}`) && !TrackSwitcher.UNBENDABLES.includes(`${b.name}:${a.name}`);
	}

	// disableConnections(conns) {
	// 	this.connecteds = this.connecteds.filter(conn => !conns.includes(conn));
	// }

	// enableConnection(conn) {
	// 	this.connecteds.push(conn);

	// 	const inter = this.getConnInter(conn);
	// 	const others = TrackSwitcher.TRACK_CONNECTORS.filter(conn2 => {
	// 		const inter2 = this.getConnInter(conn2);
	// 		return conn2 != conn && inter2.equal(inter);
	// 	});
	// 	if (others.length) {
	// 		this.disableConnections(others);
	// 	}
	// }

	haveWon() {
		const tos = TrackSwitcher.PROBLEMS[this.level].tos;
		if (tos.length == this.cars.length) {
			return this.cars.every((car, i) => {
				return car.equal(tos[i]);
			});
		}
	}

	getScore() {
		return null;
	}

	handleClick(C) {
		if (this.m_bGameOver) return;

		// const R = TrackSwitcher.CONNECTOR_RADIUS * 1.5;
		// const conn = TrackSwitcher.TRACK_CONNECTORS.find(conn => this.scale(conn.button).distance(C) < R);
		// if (conn) {
		// 	if (this.connecteds.includes(conn)) {
		// 		this.disableConnections([conn]);
		// 	}
		// 	else {
		// 		this.enableConnection(conn);
		// 	}
		// 	this.changed = true;
		// }
	}

	handleDragStart(C) {
		if (this.m_bGameOver) return;

		this.startTime();

		const track = this.findClosestTrack(C);
		if (track) {
			const car = this.getCar(track);
			if (car) {
				this.draggingRoute = new Route(this, track);
				this.changed = true;
				return true;
			}
		}
	}

	handleDragMove(C) {
		if (this.m_bGameOver) return;

		const track = this.findClosestTrack(C);
		if (!track) return;

		if (this.draggingRoute.beforeLast === track) {
			this.draggingRoute.undo();
			this.changed = true;
			return;
		}

		if (this.draggingRoute.last != track) {
			if (this.draggingRoute.connectsTo(track)) {
				if (this.canBendTo(this.draggingRoute.last, track)) {
					this.draggingRoute.add(track);
					this.changed = true;
					return;
				}
			}
		}
	}

	handleDragEnd() {
		if (this.m_bGameOver) return;

		this.setMoves(this.m_iMoves + 1);

// console.log(this.draggingRoute);
		// setTimeout(() => {
			this.draggingRoute = null;
			this.changed = true;

			this.startWinCheck();
		// }, 1000);

// this.unbendables || (this.unbendables = []);
// const i1 = /*TrackSwitcher.TRACKS.indexOf(*/this.draggingRoute.tracks[0].name/*)*/;
// const i2 = /*TrackSwitcher.TRACKS.indexOf(*/this.draggingRoute.tracks[1].name/*)*/;
// this.unbendables.push(`${i1}:${i2}`);
// console.log(this.unbendables.join(', '));
	}

	listenControls() {
		this.listenDragAndClick();

		this.$levels.on('change', e => {
			this.startGame(parseInt(e.target.value));
		});

		$('#restart').on('click', e => {
			this.startGame(this.level);
		});

		this.$showNames.on('change', e => {
			this.changed = true;
		});

		this.$showSolution.on('change', e => {
			this.changed = true;
		});
	}

}
