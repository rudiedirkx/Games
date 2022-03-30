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
		const color = game.randomColor();
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
		const color = game.randomColor();
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
		const color = game.randomColor();
		const C = game.scale(this.center);

		game.ctx.strokeStyle = color;
		game.ctx.lineWidth = 5;

		game.ctx.beginPath();
		game.ctx.arc(C.x, C.y, this.radius * TicketToRide.SQUARE, this.startAngle, this.endAngle);
		game.ctx.stroke();
	}
}

class CircularStraightTrack extends CircularTrack {
	constructor(center, radius, startAngle, endAngle, to) {
		super(center, radius, startAngle, endAngle);
		this.to = to;
	}

	draw(game) {
		const color = game.randomColor();
		const C = game.scale(this.center);

		game.ctx.strokeStyle = color;
		game.ctx.lineWidth = 5;

		game.ctx.beginPath();
		game.ctx.arc(C.x, C.y, this.radius * TicketToRide.SQUARE, this.startAngle, this.endAngle);
		game.ctx.stroke();
	}
}

class TicketToRide extends CanvasGame {

	static OFFSET = 2;
	static SQUARE = 40;
	static MARGIN = 0;

	static WIDTH = 16;
	static HEIGHT = 10;
	static BOARD = [
		new StraightTrack(new Coords2D(0, 1), new Coords2D(8, 1)),
		new StraightTrack(new Coords2D(8, 1), new Coords2D(16, 1)),
		new StraightTrack(new Coords2D(8, 1), new Coords2D(6, 3)),
		new StraightTrack(new Coords2D(6, 3), new Coords2D(4, 5)),
		new StraightTrack(new Coords2D(0, 5), new Coords2D(4, 5)),
		new StraightTrack(new Coords2D(6, 3), new Coords2D(8, 3)),
		new StraightTrack(new Coords2D(4, 5), new Coords2D(6, 7)),
		new StraightTrack(new Coords2D(6, 7), new Coords2D(8, 7)),
		new CircularTrack(new Coords2D(8, 5), 2, -Math.PI/2, Math.PI/2),
		new DoubleStraightTrack(new Coords2D(8, 3), new Coords2D(11, 3), new Coords2D(13, 5)),
		new DoubleStraightTrack(new Coords2D(8, 7), new Coords2D(11, 7), new Coords2D(13, 5)),
		new StraightTrack(new Coords2D(13, 5), new Coords2D(16, 5)),
		new CircularTrack(new Coords2D(6, 10), 3, Math.PI, 1.5 * Math.PI),
		new DoubleStraightTrack(new Coords2D(8, 7), new Coords2D(10, 9), new Coords2D(16, 9)),
	];

	drawStructure() {
		this.drawTracks();
	}

	drawTracks() {
		TicketToRide.BOARD.map(track => this.drawTrack(track));
	}

	drawTrack(track) {
		track.draw(this);
	}

	startGame() {
		this.canvas.width = TicketToRide.OFFSET + TicketToRide.WIDTH * (TicketToRide.SQUARE + TicketToRide.MARGIN) - TicketToRide.MARGIN + TicketToRide.OFFSET;
		this.canvas.height = TicketToRide.OFFSET + TicketToRide.HEIGHT * (TicketToRide.SQUARE + TicketToRide.MARGIN) - TicketToRide.MARGIN + TicketToRide.OFFSET;
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return TicketToRide.OFFSET + source * (TicketToRide.SQUARE + TicketToRide.MARGIN);
	}

	unscale( source ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return C;
		}

		return Math.round((source - TicketToRide.OFFSET - TicketToRide.SQUARE/2) / (TicketToRide.MARGIN + TicketToRide.SQUARE));
	}

}
