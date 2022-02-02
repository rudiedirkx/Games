"use strict";

class LabyrinthTileShapeCorner {
	constructor() {
		this.points = [
			new Coords2D(0.3, 0.3),
			new Coords2D(1.0, 0.3),
			new Coords2D(1.0, 0.7),
			new Coords2D(0.7, 0.7),
			new Coords2D(0.7, 1.0),
			new Coords2D(0.3, 1.0),
		];
	}

	getDebugLabel() {
		return 'COR';
	}
}

class LabyrinthTileShapeStraight {
	constructor() {
		this.points = [
			new Coords2D(0.0, 0.3),
			new Coords2D(1.0, 0.3),
			new Coords2D(1.0, 0.7),
			new Coords2D(0.0, 0.7),
		];
	}

	getDebugLabel() {
		return 'STR';
	}
}

class LabyrinthTileShapeIntersect {
	constructor() {
		this.points = [
			new Coords2D(0.0, 0.3),
			new Coords2D(1.0, 0.3),
			new Coords2D(1.0, 0.7),
			new Coords2D(0.7, 0.7),
			new Coords2D(0.7, 1.0),
			new Coords2D(0.3, 1.0),
			new Coords2D(0.3, 0.7),
			new Coords2D(0.0, 0.7),
		];
	}

	getDebugLabel() {
		return 'INT';
	}
}

class LabyrinthTile {
	constructor(shape, treasure = 0, rotation = 0, fixed = false) {
		this.shape = shape;
		this.rotation = rotation;
		this.treasure = treasure;
		this.fixed = fixed;
	}

	getTreasureLabel() {
		return String(this.treasure || '');
	}

	getDebugLabel() {
		return (this.shape.getDebugLabel() + ' ' + (this.treasure || '')).trim();
	}
}

class Labyrinth extends CanvasGame {

	static SIZE = 7;

	static FIX_TREAS_INTERSECT = 12;
	static DYN_TREAS_CORNER = 6;
	static DYN_TREAS_INTERSECT = 6;

	static OFFSET = 60;
	static SQUARE = 80;
	static MARGIN = 4;
	static BORDER_RADIUS = 8;
	static WOBBLE = 2;

	static FIX_WALL_COLOR = '#bbb';
	static DYN_WALL_COLOR = '#ddd';

	constructor(canvas, keyCanvas) {
		super(canvas);

		this.keyGame = keyCanvas ? new Labyrinth(keyCanvas) : null;

		this.shapeStraight = new LabyrinthTileShapeStraight();
		this.shapeCorner = new LabyrinthTileShapeCorner();
		this.shapeIntersect = new LabyrinthTileShapeIntersect();

		this.fixedTiles = this.makeFixedTiles();
	}

	reset() {
		super.reset();

		this.dynamicTiles = [];
		this.tiles = [];
		this.keyTile = null;

		this.paintingTiming = true;
	}

	drawContent() {
		this.drawTiles();
		this.drawKeyTile();
	}

	drawTiles() {
		for ( let y = 0; y < Labyrinth.SIZE; y++ ) {
			for ( let x = 0; x < Labyrinth.SIZE; x++ ) {
				const C = new Coords2D(x, y);
				const tile = this.tiles[C.y][C.x];
				const topleft = this.scale(C).add(this.makeTileWobble());
				this.drawTile(topleft, tile);
			}
		}
	}

	drawKeyTile() {
		const MARGIN = 2;
		this.keyGame.canvas.width = this.keyGame.canvas.height = MARGIN + Labyrinth.SQUARE + MARGIN;
		this.keyGame.drawTile(new Coords2D(MARGIN, MARGIN), this.keyTile);
	}

	drawTile(topleft, tile) {
		const rect = this.prepareRoundedRectangle(
			topleft,
			topleft.add(new Coords2D(Labyrinth.SQUARE, Labyrinth.SQUARE)),
			Labyrinth.BORDER_RADIUS
		);
		rect.fill(tile.fixed ? Labyrinth.FIX_WALL_COLOR : Labyrinth.DYN_WALL_COLOR);
		rect.stroke('#888', 1);

		const points = tile.shape.points.map(P => P.add(new Coords2D(-0.5, -0.5)).rotate(tile.rotation / 4 * 2 * Math.PI).add(new Coords2D(0.5, 0.5)));
		if (points.length) {
			this.ctx.fillStyle = '#fff';

			this.ctx.beginPath();
			this.ctx.moveTo(topleft.x + points[0].x * Labyrinth.SQUARE, topleft.y + points[0].y * Labyrinth.SQUARE);
			for ( let i = 1; i < points.length; i++ ) {
				this.ctx.lineTo(topleft.x + points[i].x * Labyrinth.SQUARE, topleft.y + points[i].y * Labyrinth.SQUARE);
			}
			this.ctx.closePath();
			this.ctx.fill();
		}

		this.ctx.textAlign = 'center';
		this.ctx.textBaseline = 'middle';
		this.drawText(topleft.add(new Coords2D(Labyrinth.SQUARE / 2, Labyrinth.SQUARE / 2)), tile.getTreasureLabel(), {size: 20});
	}

	makeTileWobble() {
		if (!Labyrinth.WOBBLE) return new Coords2D(0, 0);
		const T = 2 * Labyrinth.WOBBLE + 1;
		return new Coords2D(this.randInt(T) - Labyrinth.WOBBLE, this.randInt(T) - Labyrinth.WOBBLE);
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Labyrinth.OFFSET + source * (Labyrinth.SQUARE + Labyrinth.MARGIN);
	}

	unscale( source ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return C;
		}

		return Math.round((source - Labyrinth.OFFSET - Labyrinth.SQUARE/2) / (Labyrinth.MARGIN + Labyrinth.SQUARE));
	}

	randInt(tail) {
		return parseInt(Math.random() * tail);
	}

	startGame() {
		this.reset();

		this.dynamicTiles = this.randomizeTiles(this.makeDyanmicTiles());
		this.tiles = this.gridTiles();
		this.keyTile = this.dynamicTiles.pop();

		this.canvas.width = this.canvas.height = Labyrinth.OFFSET + Labyrinth.SIZE * (Labyrinth.SQUARE + Labyrinth.MARGIN) - Labyrinth.MARGIN + Labyrinth.OFFSET;
		this.changed = true;
	}

	gridTiles() {
		const tiles = [];
		for ( let y = 0; y < Labyrinth.SIZE; y++ ) {
			const row = [];
			for ( let x = 0; x < Labyrinth.SIZE; x++ ) {
				row.push(this.fixedTiles[`${x}_${y}`] || this.dynamicTiles.pop());
			}
			tiles.push(row);
		}
		return tiles;
	}

	randomizeTiles(tiles) {
		tiles.forEach(tile => tile.rotation = this.randInt(4));
		return tiles.sort(() => Math.random() > 0.5 ? -1 : 1);
	}

	makeFixedTiles() {
		const tiles = {
			"0_0": new LabyrinthTile(this.shapeCorner, 0, 0, true),
			"2_0": new LabyrinthTile(this.shapeIntersect, 1, 0, true),
			"4_0": new LabyrinthTile(this.shapeIntersect, 2, 0, true),
			"6_0": new LabyrinthTile(this.shapeCorner, 0, 1, true),

			"0_2": new LabyrinthTile(this.shapeIntersect, 3, 3, true),
			"2_2": new LabyrinthTile(this.shapeIntersect, 4, 3, true),
			"4_2": new LabyrinthTile(this.shapeIntersect, 5, 0, true),
			"6_2": new LabyrinthTile(this.shapeIntersect, 6, 1, true),

			"0_4": new LabyrinthTile(this.shapeIntersect, 7, 3, true),
			"2_4": new LabyrinthTile(this.shapeIntersect, 8, 2, true),
			"4_4": new LabyrinthTile(this.shapeIntersect, 9, 1, true),
			"6_4": new LabyrinthTile(this.shapeIntersect, 10, 1, true),

			"0_6": new LabyrinthTile(this.shapeCorner, 0, 3, true),
			"2_6": new LabyrinthTile(this.shapeIntersect, 11, 2, true),
			"4_6": new LabyrinthTile(this.shapeIntersect, 12, 2, true),
			"6_6": new LabyrinthTile(this.shapeCorner, 0, 2, true),
		};
		return tiles;
	}

	makeDyanmicTiles() {
		const tiles = [];

		for ( let i = 0; i < 12; i++ ) {
			tiles.push(new LabyrinthTile(this.shapeStraight, 0));
		}

		for ( let i = 0; i < 16; i++ ) {
			const treasure = i < Labyrinth.DYN_TREAS_CORNER ? Labyrinth.FIX_TREAS_INTERSECT + i + 1 : 0;
			tiles.push(new LabyrinthTile(this.shapeCorner, treasure));
		}

		for ( let i = 0; i < 6; i++ ) {
			const treasure = i < Labyrinth.DYN_TREAS_INTERSECT ? Labyrinth.FIX_TREAS_INTERSECT + Labyrinth.DYN_TREAS_CORNER + i + 1 : 0;
			tiles.push(new LabyrinthTile(this.shapeIntersect, treasure));
		}

		return tiles;

		// Dynamic:
		// 12x straight
		// 16x corner
		// 6x intersect

		// Fixed:
		// 4x corner
		// 12x intersect
	}

	listenControls() {
		$('#create').on('click', e => {
			this.startGame();
		});
	}

}
