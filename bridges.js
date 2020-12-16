class Bridges extends CanvasGame {

	reset() {
		super.reset();

		this.grid = [];
		this.width = 0;
		this.height = 0;
	}

	createMap(grid) {
		this.grid = grid;
		this.height = grid.length;
		this.width = Math.max(...grid.map(L => L.length));

		this.changed = true;
	}

	drawStructure() {
		const OFFSET = 30;
		const SQUARE = 50;
		const CIRCLE = 15;
		const TEXT = 40;

		this.canvas.width = 100+ OFFSET + (this.width-1) * SQUARE + OFFSET;
		this.canvas.height = 100+ OFFSET + (this.height-1) * SQUARE + OFFSET;

		const numbers = [];

		for ( let y = 0; y < this.height; y++ ) {
			this.drawLine(
				new Coords2D(OFFSET, OFFSET + y * SQUARE),
				new Coords2D(OFFSET + (this.width-1) * SQUARE, OFFSET + y * SQUARE),
				{width: 1}
			);
		}

		for ( let x = 0; x < this.width; x++ ) {
			this.drawLine(
				new Coords2D(OFFSET + x * SQUARE, OFFSET),
				new Coords2D(OFFSET + x * SQUARE, OFFSET + (this.height-1) * SQUARE),
				{width: 1}
			);
		}

		this.ctx.textAlign = 'center';
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				const n = parseInt(this.grid[y][x]);
				if (!isNaN(n)) {
					this.drawText(
						new Coords2D(OFFSET + x * SQUARE, OFFSET + y * SQUARE + TEXT/3),
						n,
						{size: TEXT + 'px'}
					);
				}
			}
		}
	}

	drawContent() {
	}

	setTime() {}

	setMoves() {}

}
