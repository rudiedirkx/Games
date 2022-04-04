"use strict";

class Track {
	constructor(x1, y1, x2, y2, stoppable = true) {
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

	draw(game, color, width) {
		color || (color = TrackSwitcher.TRACK_COLOR); // game.randomColor();
		width || (width = TrackSwitcher.TRACK_WIDTH);
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

	draw(game, color, width) {
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

class TrackSwitcher extends CanvasGame {

	static OFFSET = 0;
	static SQUARE_W = 40;
	static SQUARE_H = 30;
	static MARGIN = 0;
	static STOP_RADIUS = 8;
	static TRACK_WIDTH = 8;
	static TRACK_COLOR = '#aaa';
	static WAGON_WIDTH = 12;
	static WAGON_COLOR = 'red';

	static WIDTH = 16;
	static HEIGHT = 10;
	static TRACKS = [
		new Track(0, 1, 2, 1), new Track(2, 1, 4, 1), new Track(4, 1, 6, 1), new Track(6, 1, 8, 1),
		new Track(8, 1, 10, 1), new Track(10, 1, 12, 1), new Track(12, 1, 14, 1), new Track(14, 1, 16, 1),
		new Track(8, 1, 6, 3),
		new Track(6, 3, 8, 3), new Track(8, 3, 10, 3),
		new Track(6, 3, 4, 5), new Track(10, 3, 12, 5),
		new Track(8, 3, 9, 5, false), new Track(9, 5, 8, 7, false),
		new Track(0, 5, 2, 5), new Track(2, 5, 4, 5), new Track(12, 5, 14, 5), new Track(14, 5, 16, 5),
		new Track(6, 7, 4, 5), new Track(10, 7, 12, 5),
		new Track(6, 7, 8, 7), new Track(8, 7, 10, 7),
		new Track(6, 7, 4, 8), new Track(4, 8, 3, 10),
		new Track(8, 7, 10, 8.5), new Track(10, 8.5, 12, 8.5), new Track(12, 8.5, 14, 10),
	];
	static UNBENDABLES = ['23-19', '19-11', '9-8', '3-8', '25-22', '25-14', '14-22', '12-20', '10-13'];

	reset() {
		super.reset();

		this.dragging = 0;
		// this.draggingFrom = null;
		this.draggingTrain = null;
	}

	drawStructure() {
		this.drawStops();
		this.drawInterTracks();
		this.drawTracks();
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
				this.drawCircle(this.scale(C), TrackSwitcher.STOP_RADIUS);
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
		track.draw(this);
	}

	drawWagon(track) {
		track.draw(this, TrackSwitcher.WAGON_COLOR, TrackSwitcher.WAGON_WIDTH);
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
		const i1 = TrackSwitcher.TRACKS.indexOf(a);
		const i2 = TrackSwitcher.TRACKS.indexOf(b);
		return !TrackSwitcher.UNBENDABLES.includes(`${i1}-${i2}`) && !TrackSwitcher.UNBENDABLES.includes(`${i2}-${i1}`);
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
// const i1 = TrackSwitcher.TRACKS.indexOf(this.draggingTrain.tracks[0]);
// const i2 = TrackSwitcher.TRACKS.indexOf(this.draggingTrain.tracks[1]);
// this.unbendables.push(`${i1}-${i2}`);
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
	}

}
