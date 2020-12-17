class Bridge {

	constructor(from, to) {
		if (from.x > to.x || (from.x == to.x && from.y > to.y)) {
			[from, to] = [to, from];
		}

		this.from = from;
		this.to = to;
		this.strength = 1;
	}

	equal(bridge) {
		return this.from.equal(bridge.from) && this.to.equal(bridge.to);
	}

}

class Bridges extends CanvasGame {

	reset() {
		super.reset();

		this.grid = [];
		this.width = 0;
		this.height = 0;

		this.OFFSET = 30;
		this.SQUARE = 50;
		this.CIRCLE = 22;
		this.TEXT = 40;
		this.STRUCTURE = '#999';

		this.dragging = null;
		this.bridging = null;
		this.bridges = [];
	}

	createMap(grid) {
		this.grid = grid;
		this.height = grid.length;
		this.width = Math.max(...grid.map(L => L.length));

		this.canvas.width = this.OFFSET + (this.width-1) * this.SQUARE + this.OFFSET;
		this.canvas.height = this.OFFSET + (this.height-1) * this.SQUARE + this.OFFSET;

		this.changed = true;
	}

	drawStructure() {
		this.drawGrid();
	}

	drawContent() {
		this.drawBridges();
		this.drawRequirements();
		this.drawBridging();
	}

	drawGrid() {
		for ( let y = 0; y < this.height; y++ ) {
			this.drawLine(
				new Coords2D(this.OFFSET, this.OFFSET + y * this.SQUARE),
				new Coords2D(this.OFFSET + (this.width-1) * this.SQUARE, this.OFFSET + y * this.SQUARE),
				{width: 1, color: this.STRUCTURE}
			);
		}

		for ( let x = 0; x < this.width; x++ ) {
			this.drawLine(
				new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET),
				new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + (this.height-1) * this.SQUARE),
				{width: 1, color: this.STRUCTURE}
			);
		}
	}

	drawRequirements() {
		this.ctx.textAlign = 'center';
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				const n = parseInt(this.grid[y][x]);
				if (!isNaN(n)) {
					this.drawDot(
						new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + y * this.SQUARE),
						{radius: this.CIRCLE+2, color: '#eee'}
					);
					this.drawCircle(
						new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + y * this.SQUARE),
						this.CIRCLE
					);
					this.drawText(
						new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + y * this.SQUARE + this.TEXT/3),
						n,
						{size: this.TEXT + 'px'}
					);
				}
			}
		}
	}

	drawBridging() {
		if (this.bridging) {
			this.drawDot(this.scale(this.bridging.from), {radius: 5, color: 'red'});
			this.drawDot(this.scale(this.bridging.to), {radius: 5, color: 'red'});
		}
	}

	drawBridges() {
		this.bridges.forEach(B => {
			this.drawLine(this.scale(B.from), this.scale(B.to), {width: 5, color: 'green'});
		});
	}

	getCrossing(C) {
		const crossing = new Coords2D(
			Math.round((C.x - this.OFFSET) / this.SQUARE),
			Math.round((C.y - this.OFFSET) / this.SQUARE)
		);
		return crossing;
	}

	getRequirement(C) {
		const n = parseInt(this.grid[C.y][C.x]);
		return n && !isNaN(n) ? n : null;
	}

	attemptBridge(from, to) {
		this.bridges.push(new Bridge(from, to));
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return this.OFFSET + source * this.SQUARE;
	}

	listenControls() {
		this.listenDrag();
	}

	listenDrag() {
		this.dragging = null;
		var location;

		this.canvas.on(['mousedown', 'touchstart'], (e) => {
			e.preventDefault();

			location = e.subjectXY;
			const crossing = this.getCrossing(location);
			if (crossing && this.getRequirement(crossing)) {
				this.dragging = crossing;
				this.changed = true;
			}
		});
		this.canvas.on(['mousemove', 'touchmove'], (e) => {
			if (this.dragging) {
				location = e.subjectXY;
				const crossing = this.getCrossing(location);
				if (crossing && !crossing.equal(this.dragging)) {
					this.bridging = new Bridge(this.dragging, crossing);
				}
				this.changed = true;
			}
		});
		document.on(['mouseup', 'touchend'], (e) => {
			if (this.dragging) {
				const crossing = this.getCrossing(location);
				if (crossing && this.getRequirement(crossing)) {
					this.attemptBridge(this.dragging, crossing);
				}
				this.changed = true;
			}

			this.dragging = null;
			this.bridging = null;
		});
	}

	setTime() {}

	setMoves() {}

}
