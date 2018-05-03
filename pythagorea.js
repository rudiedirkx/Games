class Vertex extends Coords2D {
	constructor( x, y, explicit = true ) {
		super(x, y);
		this.explicit = explicit;
	}

	equal( coord ) {
		const R = (v) => Math.round(v * 50);
		return R(coord.x) == R(this.x) && R(coord.y) == R(this.y);
	}

	static fromEdges( line1, line2, explicit = true ) {
		var x1 = line1.from.x;
		var y1 = line1.from.y;
		var a1 = line1.to.x - line1.from.x;
		var b1 = line1.to.y - line1.from.y;
		var x2 = line2.from.x;
		var y2 = line2.from.y;
		var a2 = line2.to.x - line2.from.x;
		var b2 = line2.to.y - line2.from.y;

		if ( a2 * b1 == a1 * b2 ) {
			return null;
		}

		// @todo Fix ridiculous division by zero and rounding
		if ( b1 == 0 ) {
			b1 = 0.000000000001;
		}

		var y = (b1 * ( (a2*y2) + b2*(x1-x2) ) - a1*b2*y1) / ( b1 * a2 - a1 * b2 ) || 0;

		var x = b1 == 0 ? a1 : a1 / b1 * (y-y1) + x1;

		const V = new Vertex(x, y, explicit);
		V.edges = [line1, line2];
		return V;
	}
}

class Edge {
	constructor( from, to, explicit = true ) {
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

	get gradient() {
		if ( this.from.x == this.to.x ) return Infinity;
		return (this.from.y - this.to.y) / (this.to.x - this.from.x);
	}
}

class Pythagorea extends Game {
	constructor( canvas ) {
		super();

		this._size = 5;
		this._scale = 50;

		const S = -1;
		const E = this._size + 1;
		this._sides = [
			new Edge(new Coords2D(S, S), new Coords2D(E, S)),
			new Edge(new Coords2D(E, S), new Coords2D(E, E)),
			new Edge(new Coords2D(E, E), new Coords2D(S, E)),
			new Edge(new Coords2D(S, E), new Coords2D(S, S)),
		];

		this.lineProps = {
			"structure": ['#ccc', 1],
			"explicit": ['#000', 2],
			"extended": ['#aaa', 1],
			"dragging": ['#f00', 2],
		};

		this.dotProps = {
			"explicit": ['#000', 3],
			"dragging": ['#f00', 3],
		};

		this.canvas = canvas;
		this.ctx = canvas.getContext('2d');
	}

	reset() {
		super.reset();

		this.changed = false;
		this.vertices = this.createStructureVertices();
		this.edges = this.createStructureEdges();

		this.undoState = [];
	}

	saveUndoState() {
		this.undoState.push([this.vertices.length, this.edges.length]);
	}

	undo() {
		if ( !this.undoState.length ) return;

		const [vertices, edges] = this.undoState.pop();
		this.vertices.length = vertices;
		this.edges.length = edges;

		this.changed = true;
	}

	createStructureVertices() {
		this.strucVertices = [];

		for (var x = 0; x <= this._size; x++) {
			for (var y = 0; y <= this._size; y++) {
				this.strucVertices.push(new Vertex(x, y, false));
			}
		}

		return this.strucVertices.slice();
	}

	createStructureEdges() {
		this.strucEdges = [];

		for (var x = 0; x <= this._size; x++) {
			this.strucEdges.push(new Edge(new Vertex(x, 0), new Vertex(x, this._size), false));
		}

		for (var y = 0; y <= this._size; y++) {
			this.strucEdges.push(new Edge(new Vertex(0, y), new Vertex(this._size, y), false));
		}

		return this.strucEdges.slice();
	}

	createGame() {
		setTimeout(() => {
			this.canvas.width = this.canvas.height = (this._size + 1) * (this._scale + 0);
			this.changed = true;
		});
	}

	hasVertex( coord ) {
		return this.vertices.some((V) => (V.explicit || !coord.explicit) && V.equal(coord));
	}

	hasEdge( line ) {
		return this.edges.some((E) => E.explicit && E.equal(line));
	}

	addVertex( coord ) {
		if ( !this.hasVertex(coord) ) {
			this.vertices.push(coord);
			this.changed = true;
		}
	}

	addEdge( line ) {
		if ( !this.hasEdge(line) ) {
			// Make structure nodes explicit
			const [from, to] = [line.from, line.to];

			this.addVertex(new Vertex(line.from.x, line.from.y));
			this.addVertex(new Vertex(line.to.x, line.to.y));

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
				P.explicit = false;
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

	drawStructure() {
		console.time('drawStructure');

		for (var x = 0; x <= this._size; x++) {
			this.drawLine(
				new Coords2D(this.scale(x), 0),
				new Coords2D(this.scale(x), this.canvas.height),
				'structure'
			);
		}

		for (var y = 0; y <= this._size; y++) {
			this.drawLine(
				new Coords2D(0, this.scale(y)),
				new Coords2D(this.canvas.width, this.scale(y)),
				'structure'
			);
		}

		this.ctx.closePath();
		this.ctx.stroke();

		console.timeEnd('drawStructure');
	}

	drawEdges() {
		console.time('drawEdges');

		this.edges.forEach((E) => E.explicit && this.drawEdgeExtensions(E));
		this.edges.forEach((E) => E.explicit && this.drawEdge(E));

		console.timeEnd('drawEdges');
	}

	drawVertices() {
		console.time('drawVertices');

		this.vertices.forEach((V) => V.explicit && this.drawVertex(V));

		console.timeEnd('drawVertices');
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
		const grad = line.gradient;

		const intersections = this._sides.map((side) => side.intersect(line));

		const unique = [];
		const have = [];
		intersections.forEach((coord) => {
			if ( !have.includes(coord.join()) ) {
				have.push(coord.join());
				unique.push(coord);
			}
		});

		const bounded = unique.filter((coord) => this.onBound(coord));

		this.drawLine(this.scale(bounded[0]), this.scale(bounded[1]), 'extended');
	}

	drawDragging() {
		if ( !this.draggingEdge || this.hasEdge(this.draggingEdge) ) return;

		this.drawEdge(this.draggingEdge, 'dragging');
		this.drawVertex(this.draggingEdge.from, 'dragging');
		this.drawVertex(this.draggingEdge.to, 'dragging');
	}

	drawDot( coord, type ) {
		const [color, radius] = this.dotProps[type];

		this.ctx.fillStyle = color;

		this.ctx.beginPath();
		this.ctx.arc(coord.x, coord.y, radius, 0, 2*Math.PI);
		this.ctx.closePath();
		this.ctx.fill();
	}

	drawLine( from, to, type ) {
		const [color, width] = this.lineProps[type];

		this.ctx.lineWidth = width;
		this.ctx.strokeStyle = color;

		this.ctx.beginPath();
		this.ctx.moveTo(from.x, from.y);
		this.ctx.lineTo(to.x, to.y);
		this.ctx.closePath();
		this.ctx.stroke();
	}

	drawContent() {
		this.drawEdges();
		this.drawVertices();
		this.drawDragging();
	}

	paint() {
		this.canvas.width = this.canvas.width;

		this.drawStructure();
		this.drawContent();
		this.changed = false;
	}

	startPainting() {
		const render = () => {
			this.changed && this.paint();
			requestAnimationFrame(render);
		};
		render();
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
	}

	listenClick() {
		this.canvas.on('click', (e) => {
			if ( this.dragging < 2 ) {
				this.handleClick(e.subjectXY);
			}
		});
	}

	listenDrag() {
		this.dragging = 0;
		this.draggingFrom = null;

		this.canvas.on(['mousedown', 'touchstart'], (e) => {
			this.dragging = 1;
			this.draggingFrom = this.findClosestVertex(e.subjectXY);
		});
		this.canvas.on(['mousemove', 'touchmove'], (e) => {
			if ( this.dragging >= 1 ) {
				this.dragging = 2;
				this.handleDragMove(e.subjectXY);
			}
		});
		document.on(['mouseup', 'touchend'], (e) => {
			setTimeout(() => {
				if ( this.dragging == 2 && this.draggingEdge && !this.hasEdge(this.draggingEdge) ) {
					this.saveUndoState();
					this.addEdge(this.draggingEdge);
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

	handleClick( coord ) {
		const V = this.findClosestVertex(coord);

		if ( this.withinBounds(V) ) {
			this.saveUndoState();
			this.addVertex(new Vertex(V.x, V.y));
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
