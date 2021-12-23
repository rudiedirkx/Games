class Square {
	constructor(from, to) {
		this.from = from;
		this.to = to;
	}

	fromTopLeft() {
		if (this.from.x <= this.to.x && this.from.y <= this.to.y) {
			return this;
		}

		const flipX = this.from.x > this.to.x;
		const flipY = this.from.y > this.to.y;
		const from = new Coords2D(flipX ? this.to.x : this.from.x, flipY ? this.to.y : this.from.y);
		const to = new Coords2D(flipX ? this.from.x : this.to.x, flipY ? this.from.y : this.to.y);

		return new Square(from, to);
	}

	area() {
		return (this.to.x - this.from.x + 1) * (this.to.y - this.from.y + 1);
	}

	shape() {
		return `${this.to.x - this.from.x + 1}x${this.to.y - this.from.y + 1}`;
	}

	center() {
		return new Coords2D((this.to.x + this.from.x + 1) / 2, (this.to.y + this.from.y + 1) / 2);
	}

	overlap(S) {
		return this.from.x < S.to.x + 1 && this.to.x + 1 > S.from.x && this.from.y < S.to.y + 1 && this.to.y + 1 > S.from.y;
	}
}

class Mondrian extends CanvasGame {

	static OFFSET = 20;
	static WHITESPACE = 10;
	static SQUARE = 80;
	static COLORS = ['#f00', '#0f0', '#00f', '#cc0', '#f0f', '#0ff'];

	reset() {
		super.reset();

		// this.paintingTiming = true;

		this.SIZE = 0;
		this.squares = [];
		// this.hovering = null;
		this.dragging = null;
		this.squaring = null;
		this.error = null;
	}

	startGame(size = 5) {
		this.reset();

		this.SIZE = size;
		$('#size').value = size;

		this.canvas.width = this.canvas.height = Mondrian.OFFSET * 2 + Mondrian.SQUARE * this.SIZE;
		this.changed = true;

		this.printScore();
	}

	getScore() {
		return {
			time: this.getTime(),
			moves: this.squares.length,
			score: this.getNumberScore(),
		};
	}

	getNumberScore() {
		if (this.squares.length < 2) return 0;

		const areas = this.squares.map(S => S.area());
		return Math.max(...areas) - Math.min(...areas);
	}

	printScore() {
		$('#score').setText(this.getNumberScore() || '?');
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Mondrian.OFFSET + source * Mondrian.SQUARE;
	}

	unscale(source) {
		if (source instanceof Coords2D) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return this.inside(C) ? C : null;
		}

		return Math.round((source - Mondrian.OFFSET - Mondrian.SQUARE/2) / Mondrian.SQUARE);
	}

	inside(coord) {
		return coord.x >= 0 && coord.x < this.SIZE && coord.y >= 0 && coord.y < this.SIZE;
	}

	getColor(index) {
		return Mondrian.COLORS[index % Mondrian.COLORS.length];
	}

	drawContent() {
		// this.drawHovering();
		this.drawSquares();
		this.drawGrid();
		this.drawSquareNumbers();
		this.drawError();
		this.drawSquaring();
	}

	// drawHovering() {
	// 	if (this.hovering) {
	// 		const C = this.hovering;
	// 		this.drawRectangle(this.scale(C), this.scale(C.add(new Coords2D(1, 1))), {color: '#eee', fill: true});
	// 	}
	// }

	drawSquares() {
		this.squares.forEach((S, i) => this.drawSquare(S, this.getColor(i) + '5', true));
	}

	drawError() {
		if (this.error) {
			this.drawSquare(this.error, '#000', true);
			this.drawSquareNumber(this.error.center(), 'x', '#fff');
		}
	}

	drawSquaring() {
		if (this.squaring) {
			this.drawSquare(this.squaring, '#000', false);
		}
	}

	drawSquare(square, color, fill) {
		this.drawRectangle(
			this.scale(square.from),
			this.scale(square.to.add(new Coords2D(1, 1))),
			{color, fill}
		);
	}

	drawSquareNumbers() {
		this.squares.forEach((S, i) => {
			this.drawSquareNumber(S.center(), S.area(), this.getColor(i));
		});
	}

	drawSquareNumber(C, text, color) {
		this.ctx.textAlign = 'center';
		this.ctx.textBaseline = 'middle';
		this.drawText(this.scale(C), text, {size: 60, color});
	}

	drawGrid() {
		for (var y = Mondrian.OFFSET; y < this.canvas.height; y += Mondrian.SQUARE) {
			this.drawLine(new Coords2D(Mondrian.WHITESPACE, y), new Coords2D(this.canvas.width - Mondrian.WHITESPACE, y), {color: '#ddd', width: 1});
		}

		for (var x = Mondrian.OFFSET; x < this.canvas.width; x += Mondrian.SQUARE) {
			this.drawLine(new Coords2D(x, Mondrian.WHITESPACE), new Coords2D(x, this.canvas.height - Mondrian.WHITESPACE), {color: '#ddd', width: 1});
		}
	}

	validNewSquare(square) {
		if (this.squares.map(S => S.shape()).includes(square.shape())) return false;
		if (this.squares.some(S => S.overlap(square))) return false;

		return true;
	}

	haveWon() {
		return this.squares.reduce((T, S) => T + S.area(), 0) == this.SIZE * this.SIZE;
	}

	handleDragging(C) {
		C = this.unscale(C);
		if (!C) return;

		if (!this.squaring) {
			this.squaring = new Square(C, C);
		}
		else {
			this.squaring = (new Square(this.dragging, C)).fromTopLeft();
		}

		this.changed = true;
	}

	handleDragEnd() {
		if (!this.squaring) return;

		if (this.validNewSquare(this.squaring)) {
			this.squares.push(this.squaring);
			this.printScore();
			this.startWinCheck();
		}
		else {
			this.error = this.squaring;
			setTimeout(() => {
				this.error = null;
				this.changed = true;
			}, 500);
		}

		this.squaring = null;
	}

	listenDrag() {
		this.canvas.on(['mousedown', 'touchstart'], e => {
			if (!e.rightClick) {
				this.dragging = this.unscale(e.subjectXY);
			}
		});
		this.canvas.on(['mousemove', 'touchmove'], e => {
			if (this.dragging) {
				this.handleDragging(e.subjectXY);
			}
			// else {
			// 	if (e.type == 'mousemove') {
			// 		this.hovering = this.unscale(e.subjectXY);
			// 		this.changed = true;
			// 	}
			// }
		});
		// this.canvas.on('mouseout', e => {
		// 	this.hovering = null;
		// });
		document.onmouseup = document.ontouchend = e => {
			// e.preventDefault();

			this.handleDragEnd();
			// this.hovering = null;
			this.dragging = null;
			this.changed = true;
		};
	}

	listenControls() {
		this.listenDrag();

		$('#size').on('change', e => {
			this.startGame(parseInt(e.target.value));
		});

		$('#reset').on('click', e => {
			this.startGame(this.SIZE);
		});

		$('#undo').on('click', e => {
			this.squares.pop();
			this.changed = true;
			this.printScore();
		});
	}

}
