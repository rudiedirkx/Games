<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Puzzle Edges performance</title>

<style>
canvas {
	background: #eee;
	max-width: 100%;
}
</style>

<canvas></canvas>

<p>
	<select id="strategy">
		<option value="Random">Random</option>
		<option value="PerSide">Per Side</option>
		<option value="Closest">Closest</option>
		<option value="ClosestSameSide">Closest Same Side</option>
	</select>
	<button id="restart">Restart</button>
	<button id="newgame">New random</button>
	Distance: <span id="distance"></span>
	<button id="step">Step</button>
	<label><input type="checkbox" id="auto" checked /> Auto</label>
</p>

<script src="https://games.webblocks.nl/js/rjs-custom.js"></script>
<script src="https://games.webblocks.nl/gridgame.js"></script>
<script>
class PuzzlePiece {
	constructor(pos, side) {
		this.pos = pos;
		this.origPos = pos.copy();
		this.side = side;
	}
}

class PuzzleStrategyPerSide {
	nextPiece(pieces, pos, fromSide) {
		const sorted = [...pieces].sort((a, b) => a.side - b.side);
		return sorted[0];
	}
}

class PuzzleStrategyClosest {
	nextPiece(pieces, pos, fromSide) {
		const distances = pieces.map(piece => {
			return piece.pos.distance(pos);
		});
		const sorted = distances.map((d, i) => i).sort((a, b) => distances[a] - distances[b]);
		return pieces[sorted[0]];
	}
}

class PuzzleStrategyClosestSameSide {
	constructor(range = 8) {
		this.range = range;
	}
	nextPiece(pieces, pos, fromSide) {
		const distances = pieces.map(piece => {
			return piece.pos.distance(pos);
		});
		const sortedIndexes = distances.map((d, i) => i).sort((a, b) => distances[a] - distances[b]);
		const sortedPieces = sortedIndexes.map(i => pieces[i]);
		const same = sortedPieces.slice(0, this.range).find(piece => piece.side == fromSide);
		return same || sortedPieces[0];
	}
}

class PuzzleStrategyRandom {
	nextPiece(pieces, pos, fromSide) {
		return pieces[parseInt(Math.random() * pieces.length)];
	}
}

class PuzzleEdges extends CanvasGame {
	static SIZE = 400;
	static EDGE_MARGIN = 30;
	static PIECES = 80;
	static SIDES = [
		[0.5, 0.0, 'red'],
		[1.0, 0.5, 'green'],
		[0.5, 1.0, 'blue'],
		[0.0, 0.5, 'orange'],
	];
	static STRATEGIES = {
		"Random": PuzzleStrategyRandom,
		"Closest": PuzzleStrategyClosest,
		"PerSide": PuzzleStrategyPerSide,
		"ClosestSameSide": PuzzleStrategyClosestSameSide,
	};

	static SPEED = 20;

	reset() {
		super.reset();

		// this.paintingTiming = true;

		this.pieces = [];
		// this.movingPiece = null;
		this.strategy = null;
		this.moves = [];
		this.pos = null;

		this.timer && clearTimeout(this.timer);
		this.timer = null;

		this.$auto = $('#auto');
	}

	getEdgePos(n) {
		const [x, y] = PuzzleEdges.SIDES[n];
		return new Coords2D(PuzzleEdges.SIZE * x, PuzzleEdges.SIZE * y);
	}

	printDistance(distance) {
		$('#distance').setText(Math.round(distance / 1000) + 'k');
	}

	drawContent() {
		this.drawSides();
		this.drawMoves();
		this.drawPieces();
	}

	drawSides() {
		PuzzleEdges.SIDES.forEach(([x, y, color], i) => {
			this.drawCircle(
				this.getEdgePos(i),
				PuzzleEdges.EDGE_MARGIN,
				{color}
			);
		});
	}

	drawPieces() {
		this.pieces.forEach(P => {
			const color = /*this.movingPiece === P ? 'black' :*/ PuzzleEdges.SIDES[P.side][2];
			this.drawPiece(P, color);
		});
	}

	drawPiece(P, color) {
		this.drawDot(P.pos, {radius: 5, color});
	}

	drawMoves() {
		let distance = 0;
		for ( let i = 1; i < this.moves.length; i++ ) {
			const from = this.moves[i - 1];
			const to = this.moves[i];
			this.drawLine(from, to, {width: 1});
			distance += from.distance(to);
		}
		this.printDistance(distance);
	}

	nextPiece() {
		return this.strategy.nextPiece(this.pieces, this.pos, this.side);
	}

	startEdging(strategy) {
		$('#strategy').value = strategy.constructor.name.substr('PuzzleStrategy'.length);

		this.strategy = strategy;
		this.pos = new Coords2D(PuzzleEdges.SIZE / 2, PuzzleEdges.SIZE / 2);
		this.moves.push(this.pos);

		// setTimeout(() => this.edgeNext(), 200);
	}

	edgeNext() {
		const piece = this.nextPiece();
		if (!piece) return;
		this.moveTo(piece.pos, null);
		this.timer = setTimeout(() => {
			this.moveTo(this.getEdgePos(piece.side), piece.side);
			this.pieces = this.pieces.filter(P => P != piece);
			if (this.$auto.checked) {
				this.timer = setTimeout(() => this.edgeNext(), PuzzleEdges.SPEED);
			}
		}, PuzzleEdges.SPEED);
	}

	moveTo(pos, side) {
		this.pos = pos;
		this.side = side;
		this.moves.push(pos);
		this.changed = true;
	}

	serializePieces(pieces) {
		return pieces.map(P => {
			return [P.pos.x, P.pos.y, P.side];
		});
	}

	unserializePieces(pieces) {
		return pieces.map(P => {
			return new PuzzlePiece(new Coords2D(P[0], P[1]), P[2]);
		});
	}

	randInt(max) {
		return Math.floor(Math.random() * (max + 1));
	}

	randPos() {
		const x = PuzzleEdges.EDGE_MARGIN + this.randInt(PuzzleEdges.SIZE - 2 * PuzzleEdges.EDGE_MARGIN);
		const y = PuzzleEdges.EDGE_MARGIN + this.randInt(PuzzleEdges.SIZE - 2 * PuzzleEdges.EDGE_MARGIN);
		return new Coords2D(x, y);
	}

	restartStructure() {
		this.startStructure(this.unserializePieces(JSON.parse(this.structureBackup)));
	}

	startRandomStructure() {
		const perSide = Math.floor(PuzzleEdges.PIECES / 4);

		const pieces = [];
		for ( let i = 0; i < PuzzleEdges.PIECES; i++ ) {
			const side = Math.floor(i / perSide);
			pieces.push(new PuzzlePiece(this.randPos(), side));
		}

		this.startStructure(pieces);
	}

	startStructure(pieces) {
		this.reset();

		this.canvas.width = this.canvas.height = PuzzleEdges.SIZE;

		this.pieces = pieces;
		this.structureBackup = JSON.stringify(this.serializePieces(this.pieces));

		this.changed = true;
	}

	listenControls() {
		$('#strategy').on('change', e => {
			const strategy = PuzzleEdges.STRATEGIES[e.subject.value];
			this.restartStructure();
			this.startEdging(new strategy());
			if (this.$auto.checked) {
				this.edgeNext();
			}
		});
		$('#restart').on('click', e => {
			const strategy = this.strategy.constructor;
			this.restartStructure();
			this.startEdging(new strategy());
		});
		$('#newgame').on('click', e => {
			const strategy = this.strategy.constructor;
			this.startRandomStructure();
			this.startEdging(new strategy());
			if (this.$auto.checked) {
				this.edgeNext();
			}
		});
		$('#step').on('click', e => {
			this.edgeNext();
		});
	}
}
</script>
<script>
objGame = new PuzzleEdges($('canvas'));
objGame.listenControls();
objGame.startPainting();
objGame.startRandomStructure();
// objGame.startEdging(new PuzzleStrategyRandom());
// objGame.startEdging(new PuzzleStrategyClosest());
// objGame.startEdging(new PuzzleStrategyPerSide());
objGame.startEdging(new PuzzleStrategyClosestSameSide());
</script>
