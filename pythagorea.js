"use strict";

/**
 * @property {int} x
 * @property {int} y
 * @property {int} explicit
 * @property {Edge[]} edges
 */
class Vertex extends Coords2D {
	constructor( x, y, explicit = Pythagorea.EXPLICIT ) {
		super(x, y);
		this.explicit = explicit;
	}

	comparable() {
		return new Vertex(Math.round(this.x * 50), Math.round(this.y * 50));
	}

	equal( coord ) {
		const R = (v) => Math.round(v * 50);
		return R(coord.x) == R(this.x) && R(coord.y) == R(this.y);
	}

	static fromEdges( line1, line2, explicit = Pythagorea.EXPLICIT ) {
		const x1 = line1.from.x;
		const y1 = line1.from.y;
		const a1 = line1.to.x - line1.from.x;
		var b1 = line1.to.y - line1.from.y;
		const x2 = line2.from.x;
		const y2 = line2.from.y;
		const a2 = line2.to.x - line2.from.x;
		const b2 = line2.to.y - line2.from.y;

		if ( a2 * b1 == a1 * b2 ) {
			return null;
		}

		// @todo Fix ridiculous division by zero and rounding
		if ( b1 == 0 ) {
			b1 = 0.000000000001;
		}

		const y = (b1 * ( (a2*y2) + b2*(x1-x2) ) - a1*b2*y1) / ( b1 * a2 - a1 * b2 ) || 0;

		const x = b1 == 0 ? a1 : a1 / b1 * (y-y1) + x1;

		const V = new Vertex(x, y, explicit);
		V.edges = [line1, line2];
		return V;
	}
}

/**
 * @property {Vertex} from
 * @property {Vertex} to
 * @property {int} explicit
 */
class Edge {
	constructor( from, to, explicit = Pythagorea.EXPLICIT ) {
		this.from = from;
		this.to = to;
		this.explicit = explicit;
	}

	equal( other ) {
		return (other.from.equal(this.from) && other.to.equal(this.to)) || (other.from.equal(this.to) && other.to.equal(this.from));
	}

	intersect( other ) {
		return Vertex.fromEdges(this, other);
	}

	get length() {
		return Math.sqrt(Math.pow(this.from.x - this.to.x, 2) + Math.pow(this.from.y - this.to.y, 2));
	}

	get gradient() {
		if ( this.from.x == this.to.x ) return Infinity;
		return (this.from.y - this.to.y) / (this.to.x - this.from.x);
	}
}

class Pythagorea extends CanvasGame {
	createGame() {
		this.lineProps = {
			"structure": ['#ccc', 1],
			"explicit": ['#666', 2],
			"initial": ['#000', 3],
			"winner": ['orange', 4],
			"extended": ['#aaa', 1],
			"dragging": ['#f00', 2],
		};

		this.dotProps = {
			"explicit": ['blue', 3],
			"initial": ['#000', 3],
			"winner": ['orange', 4],
			"dragging": ['#f00', 3],
		};

		setTimeout(() => {
			this.canvas.width = this.canvas.height = (this._size + 1) * this._scale;
			this.changed = true;
		});
	}

	reset() {
		super.reset();

		this._size = 6;
		this._scale = 50;

		const S = -1;
		const E = this._size + 1;
		this._sides = [
			new Edge(new Coords2D(S, S), new Coords2D(E, S)),
			new Edge(new Coords2D(E, S), new Coords2D(E, E)),
			new Edge(new Coords2D(E, E), new Coords2D(S, E)),
			new Edge(new Coords2D(S, E), new Coords2D(S, S)),
		];

		this.vertices = this.createStructureVertices();
		this.edges = this.createStructureEdges();

		this.undoState = [];
	}

	loadLevel( n ) {
		this.reset();

		this.levelNum = n == null || isNaN(n) || !Pythagorea.levels[n] ? 0 : n;
		this.level = Pythagorea.levels[this.levelNum];
		this.level.init(this);

		document.querySelector('#level-num').textContent = `${this.levelNum + 1} / ${Pythagorea.levels.length}`;
		document.querySelector('#level-desc').textContent = this.level._desc;
		document.querySelector('#prev').disabled = this.levelNum <= 0;
		document.querySelector('#next').disabled = this.levelNum >= Pythagorea.levels.length-1;

		this.changed = true;
	}

	haveWon() {
		return this.level && this.level.check(this);
	}

	win() {
		super.win();

		this.level && this.level.win(this);
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.levelNum + 1,
			moves: this.undoState.length,
			score: Math.round(100 * this.edges.reduce(function(length, E) {
				return length + (E.explicit & Pythagorea.INITIAL ? 0 : E.length);
			}, 0)) / 100,
		};
	}

	saveUndoState() {
		this.undoState.push([this.vertices.length, this.edges.length]);
		this.printUndoState();
	}

	undoUndoState() {
		this.undoState.pop();
		this.printUndoState();
	}

	printUndoState() {
		$('#undo-num').setText(this.undoState.length);
	}

	undo() {
		if ( !this.undoState.length ) return;
		if ( this.m_bGameOver ) return this.loadLevel(this.levelNum);

		const [vertices, edges] = this.undoState.pop();
		this.vertices.length = vertices;
		this.edges.length = edges;

		this.printUndoState();
		this.changed = true;
	}

	createStructureVertices() {
		this.strucVertices = [];

		for (var x = 0; x <= this._size; x++) {
			for (var y = 0; y <= this._size; y++) {
				this.strucVertices.push(new Vertex(x, y, 0));
			}
		}

		return this.strucVertices.slice();
	}

	createStructureEdges() {
		this.strucEdges = [];

		for (var x = 0; x <= this._size; x++) {
			this.strucEdges.push(new Edge(new Vertex(x, 0), new Vertex(x, this._size), 0));
		}

		for (var y = 0; y <= this._size; y++) {
			this.strucEdges.push(new Edge(new Vertex(0, y), new Vertex(this._size, y), 0));
		}

		return this.strucEdges.slice();
	}

	hasVertex( coord ) {
		return this.vertices.some((V) => (V.explicit || !coord.explicit) && V.equal(coord));
	}

	hasEdge( line ) {
		return this.edges.some((E) => E.explicit && E.equal(line));
	}

	addVertex( coord ) {
		if ( !this.hasVertex(coord) || coord.explicit == Pythagorea.WINNER ) {
			this.vertices.push(coord);
			this.changed = true;
			return true;
		}
		return false;
	}

	addEdge( line ) {
		if ( !this.hasEdge(line) || line.explicit == Pythagorea.WINNER ) {
			this.addVertex(new Vertex(line.from.x, line.from.y, line.explicit));
			this.addVertex(new Vertex(line.to.x, line.to.y, line.explicit));

			// Find intersections with all other edges
			const alongStruc = this.alongStructure(line);
			const intersections = alongStruc ? [] : this.findIntersectionVertices(line);

			this.edges.push(line);
			this.changed = true;

			intersections.forEach((V) => this.addVertex(V));
		}
	}

	findIntersectionVertices( line ) {
		const intersections = [];

		for ( let E of this.edges ) {
			let P = E.intersect(line);
			if ( P && this.withinBounds(P) ) {
				P.explicit = 0;
				intersections.push(P);
			}
		}

		return intersections;
	}

	alongStructure( line ) {
		return this.alongStructureAxis(line, 'x') || this.alongStructureAxis(line, 'y');
	}

	alongStructureAxis( line, axis ) {
		if ( line.from[axis] == line.to[axis] ) {
			if ( Math.floor(line.from[axis]) == Math.ceil(line.from[axis]) ) {
				return true;
			}
		}

		return false;
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return this._scale/2 + source * this._scale;
	}

	explicitToType( explicit ) {
		return explicit == Pythagorea.WINNER ? 'winner' : (explicit & Pythagorea.INITIAL ? 'initial' : 'explicit');
	}

	drawStructure() {
		// console.time('drawStructure');

		for (var x = 0; x <= this._size; x++) {
			this.drawLine(
				new Coords2D(this.scale(x), this._scale / 2),
				new Coords2D(this.scale(x), this.canvas.height - this._scale / 2),
				'structure'
			);
		}

		for (var y = 0; y <= this._size; y++) {
			this.drawLine(
				new Coords2D(this._scale / 2, this.scale(y)),
				new Coords2D(this.canvas.width - this._scale / 2, this.scale(y)),
				'structure'
			);
		}

		this.ctx.closePath();
		this.ctx.stroke();

		// console.timeEnd('drawStructure');
	}

	drawEdges() {
		// console.time('drawEdges');

		this.edges.forEach(E => {
			if (E.explicit & Pythagorea.EXPLICIT && !this.alongStructure(E)) {
				this.drawEdgeExtensions(E);
			}
		});
		this.edges.forEach(E => {
			if (E.explicit) {
				this.drawEdge(E, this.explicitToType(E.explicit));
			}
		});

		// console.timeEnd('drawEdges');
	}

	drawVertices() {
		// console.time('drawVertices');

		this.vertices.forEach((V) => V.explicit && this.drawVertex(V, this.explicitToType(V.explicit)));

		// console.timeEnd('drawVertices');
	}

	drawVertex( coord, type = 'explicit' ) {
		this.drawDot(this.scale(coord), type);
	}

	drawEdge( line, type = 'explicit' ) {
		this.drawLine(
			this.scale(line.from),
			this.scale(line.to),
			type
		);
	}

	drawEdgeExtensions( line ) {
		const intersections = this._sides.map((side) => side.intersect(line));

		const unique = [];
		const have = [];
		intersections.forEach((coord) => {
			const key = coord ? coord.comparable().join() : null;
			if ( coord && !have.includes(key) ) {
				have.push(key);
				unique.push(coord);
			}
		});

		const bounded = unique.filter((coord) => this.onBound(coord));

		this.drawLine(this.scale(bounded[0]), this.scale(bounded[1]), 'extended');
	}

	drawDragging() {
		if ( !this.draggingEdge || this.hasEdge(this.draggingEdge) ) return;
		// console.time('drawVertices');

		this.drawEdge(this.draggingEdge, 'dragging');
		this.drawVertex(this.draggingEdge.from, 'dragging');
		this.drawVertex(this.draggingEdge.to, 'dragging');

		// console.timeEnd('drawVertices');
	}

	drawDot( coord, type ) {
		const [color, radius] = this.dotProps[type];

		super.drawDot(coord, {radius, color});
	}

	drawLine( from, to, type ) {
		const [color, width] = this.lineProps[type];

		super.drawLine(from, to, {color, width});
	}

	drawContent() {
		this.drawEdges();
		this.drawVertices();
		this.drawDragging();
	}

	listenControls() {
		this.listenClick();
		this.listenDrag();
		this.listenActions();
	}

	listenActions() {
		document.querySelector('#undo').on('click', (e) => {
			this.undo();
		});
		document.querySelector('#prev').on('click', (e) => {
			this.loadLevel(this.levelNum - 1);
		});
		document.querySelector('#next').on('click', (e) => {
			this.loadLevel(this.levelNum + 1);
		});
	}

	listenDrag() {
		this.dragging = 0;
		this.draggingFrom = null;

		this.canvas.on(['mousedown', 'touchstart'], (e) => {
			if ( this.m_bGameOver ) return;

			this.dragging = 1;
			this.draggingFrom = this.findClosestVertex(e.subjectXY);
		});
		this.canvas.on(['mousemove', 'touchmove'], (e) => {
			if ( this.m_bGameOver ) return;

			if ( this.dragging >= 1 ) {
				this.dragging = 2;
				this.handleDragMove(e.subjectXY);
			}
		});
		document.on(['mouseup', 'touchend'], (e) => {
			if ( this.m_bGameOver ) return;

			setTimeout(() => {
				if ( this.dragging == 2 && this.draggingEdge && !this.hasEdge(this.draggingEdge) ) {
					this.handleDragEnd();
				}
				if ( this.dragging || this.draggingEdge ) {
					this.dragging = 0;
					this.draggingEdge = null;
					this.changed = true;
				}
			});
		});
	}

	handleDragMove( coord ) {
		const V = this.findClosestVertex(coord);
		if ( V.equal(this.draggingFrom) ) return;

		this.changed = true;
		this.draggingEdge = new Edge(this.draggingFrom, V);
	}

	handleDragEnd() {
		this.startTime();

		this.saveUndoState();
		this.addEdge(this.draggingEdge);
		this.startWinCheck();
	}

	handleClick( coord ) {
		if ( this.m_bGameOver ) return;

		this.startTime();

		const V = this.findClosestVertex(coord);

		if ( this.withinBounds(V) ) {
			this.saveUndoState();
			if (!this.addVertex(new Vertex(V.x, V.y))) {
				this.undoUndoState();
			}
			this.startWinCheck();
		}
	}

	withinBounds( coord, S, E ) {
		const R = (v) => Math.round(v * 50);
		S = R(S == null ? 0 : S);
		E = R(E == null ? this._size : E);
		return R(coord.x) >= S && R(coord.x) <= E && R(coord.y) >= S && R(coord.y) <= E;
	}

	onBound( coord ) {
		return this.withinBounds(coord, -1, this._size + 1);
	}

	findClosestVertex( coord ) {
		var distance = -1;
		var vertex = null;

		for ( let V of this.vertices ) {
			let dist = this.scale(V).distance(coord);
			if ( distance == -1 || dist < distance ) {
				distance = dist;
				vertex = V;
			}
		}

		return vertex;
	}

	setTime() {
	}
}

Pythagorea.levels = [];

Pythagorea.IMPLICIT = 0;
Pythagorea.EXPLICIT = 1;
Pythagorea.INITIAL = 2;
Pythagorea.WINNER = 4;

class PythagoreaLevel {
	constructor( desc, init, check, win ) {
		this._desc = desc;
		this._init = init;
		this._check = check;
		this._win = win;
	}

	init( game ) {
		this._init.call(this, game);
	}

	check( game ) {
		return this._check.call(this, game);
	}

	win( game ) {
		return this._win.call(this, game);
	}

	edge( from, to, explicit = null ) {
		return new Edge(from, to, explicit == null ? Pythagorea.INITIAL : explicit);
	}

	vertex( x, y ) {
		return new Vertex(x, y, Pythagorea.INITIAL);
	}

	flatten( lists ) {
		return [].concat.call([], ...lists);
	}

	allVerticesExist( game, vertices ) {
		return !vertices.some((V) => !game.hasVertex(V));
	}

	allEdgesExist( game, edges ) {
		return !edges.some((E) => !game.hasEdge(E));
	}

	drawVertices( game, vertices ) {
		vertices.forEach((V) => {
			V.explicit = Pythagorea.WINNER;
			game.addVertex(V);
		});
		game.changed = true;
	}

	drawEdges( game, edges ) {
		edges.forEach((E) => {
			E.from.explicit = Pythagorea.WINNER;
			E.to.explicit = Pythagorea.WINNER;
			E.explicit = Pythagorea.WINNER;
			game.addEdge(E);
		});
		game.changed = true;
	}

	createVerticesEdges( vertices ) {
		const edges = [];
		for (var i = 0; i < vertices.length; i++) {
			edges.push(new Edge(vertices[i-1] || vertices.last(), vertices[i], Pythagorea.WINNER));
		}
		return edges;
	}

	drawVerticesEdges( game, vertices ) {
		vertices.forEach((Vs) => {
			this.createVerticesEdges(Vs).forEach((E) => game.addEdge(E));
		});
		game.changed = true;
	}
}
