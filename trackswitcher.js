"use strict";

class Track {
	constructor(name, x1, y1, x2, y2, stoppable = true) {
		this.name = name;
		this.from = new Coords2D(x1, y1);
		this.to = new Coords2D(x2, y2);
		if (x1 > x2 /*|| (x1 == x2 && y1 > y2)*/) {
			[this.from, this.to] = [this.to, this.from];
		}
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

	drawTrack(game) {
		const color = TrackSwitcher.TRACK_COLOR;
		const width = TrackSwitcher.TRACK_WIDTH;
		const from = game.scale(this.from);
		const to = game.scale(this.to);
		game.drawLine(from, to, {color, width});
		game.drawLine(from, to, {color: TrackSwitcher.BGCOLOR, width: 3});
	}

	drawWagon(game) {
		const color = TrackSwitcher.WAGON_COLOR;
		const width = TrackSwitcher.WAGON_WIDTH;
		game.drawLine(game.scale(this.from), game.scale(this.to), {color, width});
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
		game.ctx.arc(C.x, C.y, this.radius * TrackSwitcher.SQUARE_H, this.startAngle, this.endAngle);
		game.ctx.stroke();
	}
}

class Train {
	constructor(start) {
		this.tracks = [start];
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
		this.tracks.push(track);
	}

	undo() {
		this.tracks.pop();
	}
}

class Problem {
	constructor(moves, froms, tos) {
		this.moves = moves;
		this.froms = froms;
		this.tos = tos;
	}
}

class Car {
	constructor(location) {
		this.location = location;
	}
}

class Engine extends Car {
	constructor(location, direction) {
		super(location);
		this.direction = direction;
	}
}

class TrackSwitcher extends CanvasGame {

	static OFFSET = 0;
	static SQUARE_W = 40;
	static SQUARE_H = 30;
	static MARGIN = 0;
	static STOP_RADIUS = 8;
	static STOP_COLOR = '#aaa';
	static BGCOLOR = '#eee';
	static TRACK_WIDTH = 8;
	static TRACK_COLOR = '#aaa';
	static WAGON_WIDTH = 12;
	static WAGON_COLOR = 'red';

	static WIDTH = 16;
	static HEIGHT = 10;
	static TRACKS = [
		new Track('1-1', 0, 1, 2, 1), new Track('1-2', 2, 1, 4, 1), new Track('1-3', 4, 1, 6, 1), new Track('1-4', 6, 1, 8, 1),
		new Track('1-5', 8, 1, 10, 1), new Track('1-6', 10, 1, 12, 1), new Track('1-7', 12, 1, 14, 1), new Track('1-8', 14, 1, 16, 1),
		new Track('2', 8, 1, 6, 3),
		new Track('3-1', 6, 3, 8, 3), new Track('3-2', 8, 3, 10, 3),
		new Track('4-1', 6, 3, 4, 5), new Track('4-2', 10, 3, 12, 5),
		new Track('ct', 8, 3, 9, 5, false), new Track('cb', 9, 5, 8, 7, false),
		new Track('5-1', 0, 5, 2, 5), new Track('5-2', 2, 5, 4, 5), new Track('5-3', 12, 5, 14, 5), new Track('5-4', 14, 5, 16, 5),
		new Track('6-1', 6, 7, 4, 5), new Track('6-2', 10, 7, 12, 5),
		new Track('7-1', 6, 7, 8, 7), new Track('7-2', 8, 7, 10, 7),
		new Track('lb-1', 6, 7, 4, 8), new Track('lb-2', 4, 8, 3, 10),
		new Track('rb-1', 8, 7, 10, 8.5), new Track('rb-2', 10, 8.5, 12, 8.5), new Track('rb-3', 12, 8.5, 14, 10),
	];
	static UNBENDABLES = ['lb-1:6-1', '6-1:4-1', '3-1:2', '1-4:2', 'rb-1:7-2', 'rb-1:cb', 'cb:7-2', '4-2:6-2', '3-2:ct'];
	static PROBLEMS = [
		new Problem(3, // 1
			[new Engine(1, '1-8', -1), new Car(2, '5-1')],
			[new Engine(1, '5-4', 1), new Car(2, '5-3')],
		),
		new Problem(3, // 2
			[new Engine(1, '5-4', -1), new Car(2, '5-3')],
			[new Engine(1, '5-4', 1), new Car(2, '5-3')],
		),
		new Problem(3, // 3
			[new Engine(1, '1-7', -1), new Car(2, '1-8'), new Car(3, '5-4')],
			[new Engine(1, 'lb-2', 1), new Car(2, 'lb-1'), new Car(3, '7-1')],
		),
		new Problem(5, // 4
			[new Engine(1, '3-1', 1), new Car(2, '5-1'), new Car(3, '7-1'), new Car(4, 'rb-3')],
			[new Engine(1, '1-8', 1), new Car(2, '1-6'), new Car(3, 'lb-2'), new Car(4, '1-7')],
		),
		new Problem(3, // 5
			[new Engine(1, '5-1', -1), new Engine(1, '5-4', 1), new Car(2, '3-1')],
			[new Engine(1, 'rb-1', -1), new Engine(1, 'rb-3', -1), new Car(2, 'rb-2')],
		),
	];

	reset() {
		super.reset();

		this.$showNames = $('#show-names');

		this.dragging = 0;
		// this.draggingFrom = null;
		this.draggingTrain = null;
	}

	drawStructure() {
		this.drawFill(TrackSwitcher.BGCOLOR);
		this.drawStops();
		this.drawInterTracks();
		this.drawTracks();
		if (this.$showNames.checked) {
			this.drawTrackNames();
		}
	}

	drawContent() {
		this.drawDragging();
	}

	drawDragging() {
		if (this.draggingTrain) {
			this.draggingTrain.tracks.forEach(track => this.drawWagon(track));
		}
	}

	drawStops() {
		TrackSwitcher.TRACKS.map(T => {
			const C = T.center;
			if (T.stoppable) {
				this.drawCircle(this.scale(C), TrackSwitcher.STOP_RADIUS, {color: TrackSwitcher.STOP_COLOR});
			}
			// else {
			// 	this.drawLine(this.scale(C).add(new Coords2D(-7, -7)), this.scale(C).add(new Coords2D(7, 7)));
			// 	this.drawLine(this.scale(C).add(new Coords2D(-7, 7)), this.scale(C).add(new Coords2D(7, -7)));
			// }
		});
	}

	drawInterTracks() {
		const coords = [];
		TrackSwitcher.TRACKS.forEach(T => {
			coords.find(C => C.equal(T.to)) || coords.push(T.to);
			coords.find(C => C.equal(T.from)) || coords.push(T.from);
		});

		coords.forEach(C => this.drawDot(this.scale(C), {
			radius: TrackSwitcher.TRACK_WIDTH/2,
			color: TrackSwitcher.TRACK_COLOR,
		}));
	}

	drawTracks() {
		TrackSwitcher.TRACKS.map(track => this.drawTrack(track));
	}

	drawTrack(track) {
		track.drawTrack(this);
	}

	drawTrackNames() {
		TrackSwitcher.TRACKS.map(track => this.drawTrackName(track));
	}

	drawTrackName(track) {
		this.ctx.textAlign = 'left';
		this.ctx.textBaseline = 'bottom';
		this.drawText(this.scale(track.center), track.name, {color: 'green'});
	}

	drawWagon(track) {
		track.drawWagon(this);
	}

	startGame() {
		this.canvas.width = TrackSwitcher.OFFSET + TrackSwitcher.WIDTH * (TrackSwitcher.SQUARE_W + TrackSwitcher.MARGIN) - TrackSwitcher.MARGIN + TrackSwitcher.OFFSET;
		this.canvas.height = TrackSwitcher.OFFSET + TrackSwitcher.HEIGHT * (TrackSwitcher.SQUARE_H + TrackSwitcher.MARGIN) - TrackSwitcher.MARGIN + TrackSwitcher.OFFSET;
	}

	scale( source, square ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x, TrackSwitcher.SQUARE_W), this.scale(source.y, TrackSwitcher.SQUARE_H));
		}

		return TrackSwitcher.OFFSET + source * (square + TrackSwitcher.MARGIN);
	}

	// unscale( source, square ) {
	// 	if ( source instanceof Coords2D ) {
	// 		source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
	// 		const C = new Coords2D(this.unscale(source.x, TrackSwitcher.SQUARE_W), this.unscale(source.y, TrackSwitcher.SQUARE_H));
	// 		return C;
	// 	}

	// 	return Math.round((source - TrackSwitcher.OFFSET - square/2) / (TrackSwitcher.MARGIN + square));
	// }

	findClosestTrack(C) {
		C = C.multiply(this.canvas.width / this.canvas.offsetWidth);
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

	handleClick(C) {
		const T = this.findClosestTrack(C);
		if (!T) return;
		this.drawLine(this.scale(T.from), this.scale(T.to), {
			color: 'red',
			width: TrackSwitcher.TRACK_WIDTH * 1.5,
		});
	}

	handleDragMove(C) {
		const track = this.findClosestTrack(C);
		if (!track) return;

		if (this.draggingTrain && this.draggingTrain.beforeLast === track) {
			this.draggingTrain.undo();
			this.changed = true;
			return;
		}

		if (!this.draggingTrain) {
			this.draggingTrain = new Train(track);
			this.changed = true;
			return;
		}
		else if (!this.draggingTrain.includes(track)) {
			if (this.draggingTrain.connectsTo(track)) {
				if (this.canBendTo(this.draggingTrain.last, track)) {
					this.draggingTrain.add(track);
					this.changed = true;
					return;
				}
			}
		}
	}

	handleDragEnd() {
		console.log(this.draggingTrain);
		setTimeout(() => {
			this.draggingTrain = null;
			this.changed = true;
		}, 1000);

// this.unbendables || (this.unbendables = []);
// const i1 = /*TrackSwitcher.TRACKS.indexOf(*/this.draggingTrain.tracks[0].name/*)*/;
// const i2 = /*TrackSwitcher.TRACKS.indexOf(*/this.draggingTrain.tracks[1].name/*)*/;
// this.unbendables.push(`${i1}:${i2}`);
// console.log(this.unbendables.join(', '));
	}

	listenDrag() {
		this.canvas.on(['mousedown', 'touchstart'], e => {
			e.preventDefault();
			if ( this.m_bGameOver ) return;

			this.dragging = 1;
			// this.draggingFrom = this.findClosestTrack(e.subjectXY);
		});
		this.canvas.on(['mousemove', 'touchmove'], e => {
			if ( this.m_bGameOver ) return;

			if ( this.dragging >= 1 ) {
				this.dragging = 2;
				this.handleDragMove(e.subjectXY);
			}
		});
		document.on(['mouseup', 'touchend'], e => {
			if ( this.m_bGameOver ) return;

			setTimeout(() => {
				if ( this.dragging == 2 ) {
					this.handleDragEnd();
					this.changed = true;
				}

				this.dragging = 0;
				// this.draggingFrom = null;
			});
		});
	}

	listenControls() {
		// this.listenClick();
		this.listenDrag();

		this.$showNames.on('change', e => {
			this.changed = true;
		});
	}

}
