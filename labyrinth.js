"use strict";

// - Pick cards
// - Show cards
// - Path finding
//   - Animation?
// - Use strategy (InOrder or AnyOrder) to fulfil cards
// - Keep time & moves

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
		this.exits = [1, 2];
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
		this.exits = [1, 3];
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
		this.exits = [1, 2, 3];
	}
}

class LabyrinthTileShapeArrow {
	constructor() {
		this.points = [
			new Coords2D(0.1, 0.5),
			new Coords2D(0.9, 0.5),
			new Coords2D(0.5, 0.9),
		];
	}
}

class LabyrinthTile {
	constructor(shape, treasure = 0, rotation = 0, dynamicIndex = null) {
		this.shape = shape;
		this.rotation = rotation;
		this.treasure = treasure;
		this.dynamicIndex = dynamicIndex;
		this.wobble = this.fixed ? new Coords2D(0, 0) : null;
		this.loc = null;
	}

	get fixed() {
		return this.dynamicIndex == null;
	}

	getExits() {
		return this.shape.exits.map(dir => (dir + this.rotation) % 4);
	}

	getTreasureLabel() {
		return String(this.treasure || '');
	}
}

class LabyrinthSlideVertical {
	constructor(from, direction) {
		this.from = from;
		this.direction = direction;
	}

	inline(tile) {
		return tile.loc && tile.loc.x == this.from.x;
	}

	move(tile, part) {
		tile.loc.y += this.direction / part;
	}

	get head() {
		const delta = (new Coords2D(0, this.direction)).multiply(Labyrinth.SIZE);
		return this.from.add(delta);
	}
}

class LabyrinthSlideHorizontal {
	constructor(from, direction) {
		this.from = from;
		this.direction = direction;
	}

	inline(tile) {
		return tile.loc && tile.loc.y == this.from.y;
	}

	move(tile, part) {
		tile.loc.x += this.direction / part;
	}

	get head() {
		const delta = (new Coords2D(this.direction, 0)).multiply(Labyrinth.SIZE);
		return this.from.add(delta);
	}
}

class LabyrinthPlayer {
	constructor(tile, color, offset) {
		this.tile = tile;
		this.color = color;
		this.offset = offset;
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
	static WOBBLE = 3;

	static FIX_WALL_COLOR = 'saddlebrown';
	static DYN_WALL_COLOR = 'peru';
	static ARROW_COLOR = 'gold';
	static PLAYER_COLORS = ['red', 'green', 'blue', 'yellow'];

	constructor(canvas, keyCanvas) {
		super(canvas);

		this.keyGame = keyCanvas ? new Labyrinth(keyCanvas) : null;

		this.shapeStraight = new LabyrinthTileShapeStraight();
		this.shapeCorner = new LabyrinthTileShapeCorner();
		this.shapeIntersect = new LabyrinthTileShapeIntersect();

		Labyrinth.fixedTiles = this.makeFixedTiles();
		Labyrinth.dynamicTiles = this.makeDyanmicTiles();
	}

	reset() {
		super.reset();

		this.tiles = [];
		this.keyTile = null;
		this.player = null;

		this.paintingTiming = true;
	}

	drawContent() {
		this.drawArrows();
		this.drawTiles();
		this.drawPlayer();
		this.drawKeyTile();
	}

	drawTiles() {
		this.tiles.forEach(tile => {
			if (tile.loc) {
				const topleft = this.scale(tile.loc).add(tile.wobble);
				this.drawTile(topleft, tile);
			}
		});
	}

	drawArrows() {
		const shape = new LabyrinthTileShapeArrow();
		this.ctx.fillStyle = Labyrinth.ARROW_COLOR;

		for ( let x = 1; x < Labyrinth.SIZE; x += 2 ) {
			this.drawShape(this.scale(new Coords2D(x, -1)), shape, 0);
			this.ctx.fill();

			this.drawShape(this.scale(new Coords2D(x, Labyrinth.SIZE)), shape, 2);
			this.ctx.fill();
		}

		for ( let y = 1; y < Labyrinth.SIZE; y += 2 ) {
			this.drawShape(this.scale(new Coords2D(-1, y)), shape, 3);
			this.ctx.fill();

			this.drawShape(this.scale(new Coords2D(Labyrinth.SIZE, y)), shape, 1);
			this.ctx.fill();
		}
	}

	drawPlayer() {
		const RADIUS = 15;
		const C = this.player.tile.loc.add(new Coords2D(0.5, 0.5));
		const P = this.scale(C).add(this.player.offset.multiply(RADIUS));
		this.drawDot(P, {radius: RADIUS, color: this.player.color});
	}

	drawKeyTile() {
		const MARGIN = 2;
		this.keyGame.canvas.width = this.keyGame.canvas.height = MARGIN + Labyrinth.SQUARE + MARGIN;
		if (this.keyTile) {
			this.keyGame.drawTile(new Coords2D(MARGIN, MARGIN), this.keyTile);
		}
	}

	drawTile(topleft, tile) {
		const rect = () => this.prepareRoundedRectangle(
			topleft,
			topleft.add(new Coords2D(Labyrinth.SQUARE, Labyrinth.SQUARE)),
			Labyrinth.BORDER_RADIUS
		);
		rect().fill(tile.fixed ? Labyrinth.FIX_WALL_COLOR : Labyrinth.DYN_WALL_COLOR);

		this.drawShape(topleft, tile.shape, tile.rotation);
		this.ctx.fillStyle = '#fff';
		this.ctx.fill();

		rect().stroke('#888', 1);

		this.ctx.textAlign = 'center';
		this.ctx.textBaseline = 'middle';
		this.drawText(topleft.add(new Coords2D(Labyrinth.SQUARE / 2, Labyrinth.SQUARE / 2)), tile.getTreasureLabel(), {size: 20});
	}

	drawShape(topleft, shape, rotation) {
		const points = shape.points.map(P => P.add(new Coords2D(-0.5, -0.5)).rotate(rotation / 4 * 2 * Math.PI).add(new Coords2D(0.5, 0.5)));
		this.ctx.beginPath();
		this.ctx.moveTo(topleft.x + points[0].x * Labyrinth.SQUARE, topleft.y + points[0].y * Labyrinth.SQUARE);
		for ( let i = 1; i < points.length; i++ ) {
			this.ctx.lineTo(topleft.x + points[i].x * Labyrinth.SQUARE, topleft.y + points[i].y * Labyrinth.SQUARE);
		}
		this.ctx.closePath();
	}

	makeTileWobble() {
		if (!Labyrinth.WOBBLE) return new Coords2D(0, 0);
		return this.makeWobble(Labyrinth.WOBBLE);
	}

	makeWobble(lean) {
		const T = 2 * lean + 1;
		return new Coords2D(this.randInt(T) - lean, this.randInt(T) - lean);
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

	getTile(C) {
		return this.tiles.find(tile => tile.loc && tile.loc.equal(C));
	}

	exportTiles() {
		return this.serializeTiles([].concat(...this.createIndex()));
	}

	importTiles(chars) {
		const dynamicTiles = this.unserializeTiles(chars).reverse();
		const dynamicIndexes = dynamicTiles.map(tile => tile.dynamicIndex);
		const keyTile = Labyrinth.dynamicTiles.find(tile => !dynamicIndexes.includes(tile.dynamicIndex));
		this.startGameWith(dynamicTiles, keyTile);
	}

	startGame() {
		const dynamicTiles = this.randomizeTiles([...Labyrinth.dynamicTiles]);
		const keyTile = dynamicTiles[0];
		this.startGameWith(dynamicTiles, keyTile);
	}

	startGameWith(dynamicTiles, keyTile) {
		this.reset();

		this.gridTiles(dynamicTiles);
		this.tiles.push(this.keyTile = keyTile);

		this.player = this.getRandomPlayer();

		this.canvas.width = this.canvas.height = Labyrinth.OFFSET + Labyrinth.SIZE * (Labyrinth.SQUARE + Labyrinth.MARGIN) - Labyrinth.MARGIN + Labyrinth.OFFSET;
		this.changed = true;
	}

	gridTiles(dynamicTiles) {
		for ( let y = 0; y < Labyrinth.SIZE; y++ ) {
			for ( let x = 0; x < Labyrinth.SIZE; x++ ) {
				const tile = Labyrinth.fixedTiles[`${x}_${y}`] || dynamicTiles.pop();
				tile.loc = new Coords2D(x, y);
				this.tiles.push(tile);
			}
		}
	}

	randomizeTiles(tiles) {
		tiles.forEach(tile => {
			tile.rotation = this.randInt(4);
			tile.wobble = this.makeTileWobble();
		});
		return tiles.sort(() => Math.random() > 0.5 ? -1 : 1);
	}

	getCornerOffsets(min = 0) {
		return [
			new Coords2D(1, min),
			new Coords2D(1, 1),
			new Coords2D(min, 1),
			new Coords2D(min, min),
		];
	}

	getRandomPlayer() {
		const i = this.randInt(4);
		const starts = this.getCornerOffsets(0);
		const offsets = this.getCornerOffsets(-1);
		const pos = starts[i].multiply(Labyrinth.SIZE - 1);
		return new LabyrinthPlayer(
			this.getTile(pos),
			Labyrinth.PLAYER_COLORS[i],
			offsets[i]
		);
	}

	makeFixedTiles() {
		const tiles = {
			"0_0": new LabyrinthTile(this.shapeCorner, 0, 0),
			"2_0": new LabyrinthTile(this.shapeIntersect, 1, 0),
			"4_0": new LabyrinthTile(this.shapeIntersect, 2, 0),
			"6_0": new LabyrinthTile(this.shapeCorner, 0, 1),

			"0_2": new LabyrinthTile(this.shapeIntersect, 3, 3),
			"2_2": new LabyrinthTile(this.shapeIntersect, 4, 3),
			"4_2": new LabyrinthTile(this.shapeIntersect, 5, 0),
			"6_2": new LabyrinthTile(this.shapeIntersect, 6, 1),

			"0_4": new LabyrinthTile(this.shapeIntersect, 7, 3),
			"2_4": new LabyrinthTile(this.shapeIntersect, 8, 2),
			"4_4": new LabyrinthTile(this.shapeIntersect, 9, 1),
			"6_4": new LabyrinthTile(this.shapeIntersect, 10, 1),

			"0_6": new LabyrinthTile(this.shapeCorner, 0, 3),
			"2_6": new LabyrinthTile(this.shapeIntersect, 11, 2),
			"4_6": new LabyrinthTile(this.shapeIntersect, 12, 2),
			"6_6": new LabyrinthTile(this.shapeCorner, 0, 2),
		};
		return tiles;
	}

	makeDyanmicTiles() {
		const tiles = [];

		for ( let i = 0; i < 12; i++ ) {
			tiles.push(new LabyrinthTile(this.shapeStraight, 0, 0, tiles.length));
		}

		for ( let i = 0; i < 16; i++ ) {
			const treasure = i < Labyrinth.DYN_TREAS_CORNER ? Labyrinth.FIX_TREAS_INTERSECT + i + 1 : 0;
			tiles.push(new LabyrinthTile(this.shapeCorner, treasure, 0, tiles.length));
		}

		for ( let i = 0; i < 6; i++ ) {
			const treasure = i < Labyrinth.DYN_TREAS_INTERSECT ? Labyrinth.FIX_TREAS_INTERSECT + Labyrinth.DYN_TREAS_CORNER + i + 1 : 0;
			tiles.push(new LabyrinthTile(this.shapeIntersect, treasure, 0, tiles.length));
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

	serializeTiles(tiles) {
		return tiles.filter(tile => !tile.fixed).map(tile => `${Game.b64(tile.dynamicIndex)}${tile.rotation}`).join('');
	}

	unserializeTiles(chars) {
		const tiles = [];
		for ( let i = 0; i < chars.length; i += 2 ) {
			const ti = Game.unb64(chars[i]);
			const tr = parseInt(chars[i + 1]);
			const tile = Labyrinth.dynamicTiles[ti];
			tile.rotation = tr;
			tiles.push(tile);
		}
		return tiles;
	}

	createIndex() {
		const index = [];
		for ( let y = 0; y < Labyrinth.SIZE; y++ ) {
			index.push([]);
		}
		this.tiles.forEach(tile => {
			if (tile.loc) index[tile.loc.y][tile.loc.x] = tile;
		});
		return index;
	}

	isEnterableEdge(C) {
		if (C.x < Labyrinth.SIZE && C.x % 2 == 1) {
			if (C.y == -1) {
				return new LabyrinthSlideVertical(C, +1);
			}
			if (C.y == Labyrinth.SIZE) {
				return new LabyrinthSlideVertical(C, -1);
			}
		}

		if (C.y < Labyrinth.SIZE && C.y % 2 == 1) {
			if (C.x == -1) {
				return new LabyrinthSlideHorizontal(C, +1);
			}
			if (C.x == Labyrinth.SIZE) {
				return new LabyrinthSlideHorizontal(C, -1);
			}
		}
	}

	handleClick(C) {
		if (!this.keyTile) return;

		C = this.unscale(C);
		const slide = this.isEnterableEdge(C);
		if (slide) {
			return this.handleSlideClick(C, slide);
		}

		if (C.x >= 0 && C.x < Labyrinth.SIZE && C.y >= 0 && C.y < Labyrinth.SIZE) {
			return this.handleMoveClick(C);
		}
	}

	handleMoveClick(C) {
		console.log('find path to', C);

this.player.tile = this.getTile(C);
this.changed = true;

		const index = this.createIndex();
		console.log(index);
	}

	handleSlideClick(C, slide) {
		const oldKeyTile = this.keyTile;
		oldKeyTile.loc = C;
		this.keyTile = null;

		const head = slide.head;
		const newKeyTile = this.getTile(head);

		const line = this.tiles.filter(tile => slide.inline(tile));
		const PARTS = 20;
		let iters = 0;
		setTimeout(() => {
			const timer = setInterval(() => {
				line.forEach(tile => slide.move(tile, PARTS));
				if (++iters >= PARTS) {
					line.forEach(tile => tile.loc = tile.loc.round());
					clearInterval(timer);
					newKeyTile.loc = null;
					this.keyTile = newKeyTile;
					this.keyTile.wobble = this.makeTileWobble();
					if (this.player.tile == newKeyTile) {
						this.player.tile = oldKeyTile;
					}
				}
				this.changed = true;
			}, 20);
		}, 200);
		this.changed = true;
	}

	handleKeyTileClick() {
		if (this.keyTile) {
			this.keyTile.rotation = (this.keyTile.rotation + 1) % 4;
			this.changed = true;
		}
	}

	listenControls() {
		this.listenClick();

		$('#create').on('click', e => {
			this.startGame();
		});

		$('#key').on('click', e => {
			this.handleKeyTileClick();
		});
	}

}
