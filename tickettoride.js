"use strict";

class Track {
	draw(game) {
	}
}

class StraightTrack extends Track {
	constructor(from, to) {
		super();
		this.from = from;
		this.to = to;
	}

	draw(game) {
		const color = '#666'; // game.randomColor();
		game.drawLine(game.scale(this.from), game.scale(this.to), {color, width: 5});
	}
}

class DoubleStraightTrack extends Track {
	constructor(from, through, to) {
		super();
		this.from = from;
		this.through = through;
		this.to = to;
	}

	draw(game) {
		const color = '#666'; // game.randomColor();
		game.drawLine(game.scale(this.from), game.scale(this.through), {color, width: 5});
		game.drawLine(game.scale(this.through), game.scale(this.to), {color, width: 5});
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

	draw(game) {
		const color = '#666'; // game.randomColor();
		const C = game.scale(this.center);

		game.ctx.strokeStyle = color;
		game.ctx.lineWidth = 5;

		game.ctx.beginPath();
		game.ctx.arc(C.x, C.y, this.radius * TicketToRide.SQUARE_H, this.startAngle, this.endAngle);
		game.ctx.stroke();
	}
}

class TicketToRide extends CanvasGame {

	static OFFSET = 2;
	static SQUARE_W = 40;
	static SQUARE_H = 30;
	static MARGIN = 0;
	static STOP_RADIUS = 8;

	static WIDTH = 17;
	static HEIGHT = 10;
	static TRACKS = [
		new StraightTrack(new Coords2D(0, 1), new Coords2D(8, 1)),
		new StraightTrack(new Coords2D(8, 1), new Coords2D(17, 1)),
		new StraightTrack(new Coords2D(8, 1), new Coords2D(6, 3)),
		new StraightTrack(new Coords2D(6, 3), new Coords2D(4, 5)),
		new StraightTrack(new Coords2D(0, 5), new Coords2D(4, 5)),
		new StraightTrack(new Coords2D(6, 3), new Coords2D(8, 3)),
		new StraightTrack(new Coords2D(4, 5), new Coords2D(6, 7)),
		new StraightTrack(new Coords2D(6, 7), new Coords2D(8, 7)),
		new CircularTrack(new Coords2D(8, 5), 2, -Math.PI/2, Math.PI/2),
		new DoubleStraightTrack(new Coords2D(8, 3), new Coords2D(11, 3), new Coords2D(13, 5)),
		new DoubleStraightTrack(new Coords2D(8, 7), new Coords2D(11, 7), new Coords2D(13, 5)),
		new StraightTrack(new Coords2D(13, 5), new Coords2D(17, 5)),
		new DoubleStraightTrack(new Coords2D(6, 7), new Coords2D(4, 8), new Coords2D(3, 10)),
		new DoubleStraightTrack(new Coords2D(8, 7), new Coords2D(11, 9), new Coords2D(17, 9)),
	];
	static STOPS = [
		new Coords2D(1, 1), new Coords2D(3, 1), new Coords2D(5, 1), new Coords2D(7, 1),
		new Coords2D(9, 1), new Coords2D(11, 1), new Coords2D(13, 1), new Coords2D(15, 1),
		new Coords2D(7, 2),
		new Coords2D(7, 3), new Coords2D(10, 3),
		new Coords2D(5, 4), new Coords2D(12, 4),
		new Coords2D(1, 5), new Coords2D(3, 5), new Coords2D(14, 5), new Coords2D(16, 5),
		new Coords2D(5, 6), new Coords2D(12, 6),
		new Coords2D(7, 7), new Coords2D(10, 7),
		new Coords2D(5, 7.5), new Coords2D(3.5, 9),
		new Coords2D(9.5, 8), new Coords2D(12.5, 9), new Coords2D(15.5, 9),
	];

	drawStructure() {
		this.drawStops();
		this.drawTracks();
	}

	drawStops() {
		TicketToRide.STOPS.map(C => this.drawCircle(this.scale(C), TicketToRide.STOP_RADIUS));
	}

	drawTracks() {
		TicketToRide.TRACKS.map(track => this.drawTrack(track));
	}

	drawTrack(track) {
		track.draw(this);
	}

	startGame() {
		this.canvas.width = TicketToRide.OFFSET + TicketToRide.WIDTH * (TicketToRide.SQUARE_W + TicketToRide.MARGIN) - TicketToRide.MARGIN + TicketToRide.OFFSET;
		this.canvas.height = TicketToRide.OFFSET + TicketToRide.HEIGHT * (TicketToRide.SQUARE_H + TicketToRide.MARGIN) - TicketToRide.MARGIN + TicketToRide.OFFSET;
	}

	scale( source, square ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x, TicketToRide.SQUARE_W), this.scale(source.y, TicketToRide.SQUARE_H));
		}

		return TicketToRide.OFFSET + source * (square + TicketToRide.MARGIN);
	}

	unscale( source, square ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x, TicketToRide.SQUARE_W), this.unscale(source.y, TicketToRide.SQUARE_H));
			return C;
		}

		return Math.round((source - TicketToRide.OFFSET - square/2) / (TicketToRide.MARGIN + square));
	}

	findStop(C) {
		const dists = TicketToRide.STOPS.map(stop => this.scale(stop).distance(C));
		// console.log(dists);
		const sorted = dists.map((d, i) => ([d, i])).sort((a, b) => a[0] - b[0]);
		// console.log(sorted);
		return TicketToRide.STOPS[sorted[0][1]];
	}

	handleClick(C) {
		// console.log(C, this.unscale(C));
		const stop = this.findStop(C);
		this.drawCircle(this.scale(stop), TicketToRide.STOP_RADIUS, {color: 'red'})
	}

	listenControls() {
		this.listenClick();
	}

}
