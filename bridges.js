class Bridge {

	constructor(from, to, strength = 1) {
		if (from.x > to.x || (from.x == to.x && from.y > to.y)) {
			[from, to] = [to, from];
		}

		this.from = from;
		this.to = to;
		this.strength = strength;
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
		this.height = grid.length;
		this.width = Math.max(...grid.map(L => L.length));
		this.grid = grid.map(L => {
			L = L.split('').map(v => parseInt(v) || 0);
			while (L.length < this.width) L.push(0);
			return L;
		});

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
				const n = this.grid[y][x];
				if (n) {
					const conns = this.getConnections(new Coords2D(x, y));
					const color = conns == n ? 'green' : (conns > n ? 'red' : null);
					this.drawDot(
						new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + y * this.SQUARE),
						{radius: this.CIRCLE+2, color: '#eee'}
					);
					this.drawCircle(
						new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + y * this.SQUARE),
						this.CIRCLE,
						{width: 4, color: color || '#777'}
					);
					this.drawText(
						new Coords2D(this.OFFSET + x * this.SQUARE, this.OFFSET + y * this.SQUARE + this.TEXT/3),
						n,
						{size: this.TEXT + 'px', color: color || '#000'}
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
			if (B.strength == 1) {
				this.drawLine(this.scale(B.from), this.scale(B.to), {width: 5, color: 'green'});
			}
			else {
				this.drawLine(this.scale(B.from), this.scale(B.to), {width: 12, color: 'green'});
				this.drawLine(this.scale(B.from), this.scale(B.to), {width: 4, color: '#eee'});
			}
		});
	}

	haveWon() {
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				if (this.grid[y][x] && this.grid[y][x] != this.getConnections(new Coords2D(x, y))) {
					return false;
				}
			}
		}

		return true;
	}

	getCrossing(C) {
		const crossing = new Coords2D(
			Math.round((C.x - this.OFFSET) / this.SQUARE),
			Math.round((C.y - this.OFFSET) / this.SQUARE)
		);
		return crossing;
	}

	getRequirement(C) {
		return this.grid[C.y][C.x];
	}

	getConnections(C) {
		return this.bridges.reduce((T, B) => B.from.equal(C) || B.to.equal(C) ? T + B.strength : T, 0);
	}

	attemptBridge(from, to) {
		// Only straight
		var d;
		if (from.x == to.x) d = new Coords2D(0, 1);
		else if (from.y == to.y) d = new Coords2D(1, 0);
		else return;

		const bridge = new Bridge(from, to);

		// Not across a requirement
		let curr = bridge.from.add(d);
		while (!curr.equal(bridge.to)) {
			if (this.getRequirement(curr)) {
				return;
			}
			curr = curr.add(d);
		}

		// @todo Not across another bridge

		this.addBridge(bridge);

		this.startWinCheck();
	}

	addBridge(bridge) {
		const existsIndex = this.bridges.findIndex(B => B.equal(bridge));
console.log('addBridge', existsIndex);
		if (existsIndex == -1) {
			this.bridges.push(bridge);
		}
		else if (this.bridges[existsIndex].strength == 1) {
			this.bridges[existsIndex].strength++;
		}
		else {
			this.bridges.splice(existsIndex, 1);
		}
	}

	replaceBridge(bridge) {
		const existsIndex = this.bridges.findIndex(B => B.equal(bridge));
console.log('replaceBridge', existsIndex);
		if (existsIndex == -1) {
			this.bridges.push(bridge);
		}
		else {
			this.bridges[existsIndex] = bridge;
		}
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return this.OFFSET + source * this.SQUARE;
	}

	listenControls() {
		this.listenDrag();
		// this.listenClick();

		$('#cheat').on('click', e => this.cheatOneRound());
	}

	listenDrag() {
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
				if (crossing) {
					if (crossing.equal(this.dragging)) {
						this.handleClick(location);
					}
					else if (this.getRequirement(crossing)) {
						this.attemptBridge(this.dragging, crossing);
					}
				}
				this.changed = true;
			}

			this.dragging = null;
			this.bridging = null;
		});
	}

	handleClick(C) {
		const crossing = this.getCrossing(C);
		if (crossing && this.getRequirement(crossing)) {
			this.cheatOneRoundFromStart(crossing);
		}
	}

	cheatOneRoundFromStart(C) {
		this.m_bCheating = true;

		const solver = BridgesSolver.fromState(this.grid, this.bridges);
		solver.findKnownsFromDirectionsStarting(C);
		this.cheatFromSolver(solver);
	}

	cheatOneRound() {
		this.m_bCheating = true;

		const solver = BridgesSolver.fromState(this.grid, this.bridges);
		solver.findKnowns();
		this.cheatFromSolver(solver);
	}

	cheatFromSolver(solver) {
console.log(solver);
		solver.updates.forEach(B => this.replaceBridge(B));

		this.changed = true;
	}

	setTime() {}

	setMoves() {}

}

class BridgesSolver {

	static fromState(grid, bridges) {
		return new this(grid, bridges);
	}

	constructor(grid, bridges) {
		this.width = grid[0].length;
		this.height = grid.length;
		this.grid = grid;
		this.bridges = bridges;

		this.requireds = this.makeRequireds();
		this.updates = [];
	}

	makeRequireds() {
		const coords = [];
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				if (this.grid[y][x]) {
					coords.push(new Coords2D(x, y));
				}
			}
		}
		return coords;
	}

	requireBridge(bridge) {
		this.updates.push(bridge);

		const existsIndex = this.bridges.findIndex(B => B.equal(bridge));
		if (existsIndex == -1) {
			this.bridges.push(bridge);
		}
		else {
			this.bridges[existsIndex] = bridge;
		}
	}

	isBridgeFor(C, T) {
		return this.bridges.some(B => {
			if (!B.from.equal(T) && !B.to.equal(T)) {
				if (B.from.x == C.x && B.from.y < C.y && B.to.y > C.y) return true;
				if (B.from.y == C.y && B.from.x < C.x && B.to.x > C.x) return true;
			}
		});
	}

	getTargetFromToward(C, D) {
		let curr = C.add(D);
		while (this.grid[curr.y] && this.grid[curr.y][curr.x] != null) {
			if (this.isBridgeFor(curr, C)) {
				return null;
			}
			else if (this.grid[curr.y][curr.x]) {
				return curr;
			}
			curr = curr.add(D);
		}
	}

	getTargetsFrom(C) {
		const targets = [];
		Coords2D.dir4Coords.forEach(D => {
			const T = this.getTargetFromToward(C, D);
			if (T) {
				targets.push(T);
			}
		});
		return targets;
	}

	findKnownsFromDirectionsStarting(C) {
		let required = this.grid[C.y][C.x];
		const targets = this.getTargetsFrom(C);
console.log(targets);
		const targetRequireds = targets.map(C => this.grid[C.y][C.x]).sort().map(n => Math.min(2, n));
		const ones = targetRequireds.filter(n => n == 1).length;
		const twos = targetRequireds.filter(n => n == 2).length;

		if (twos * 2 + ones == required) {
			console.log('all');
			targets.forEach(T => {
				this.requireBridge(new Bridge(C, T, this.grid[T.y][T.x] > 1 ? 2 : 1));
			});
			return;
		}

		if (Math.ceil((required - ones) / 2) == twos) {
			console.log('twos');
			targets.forEach(T => {
				if (this.grid[T.y][T.x] >= 2) {
					this.requireBridge(new Bridge(C, T));
				}
			});
			return;
		}

// 		let needTargets = 0;
// 		while (required > 0) {
// 			required -= targetRequireds.pop();
// 			needTargets++;
// 		}
// console.log(`${needTargets} / ${targets.length}`);

// 		if (needTargets == targets.length) {
// 			targets.forEach(T => this.requireBridge(new Bridge(C, T)));
// 		}
	}

	findKnownsFromDirections() {
		this.requireds.forEach(C => this.findKnownsFromDirectionsStarting(C));
	}

	findKnowns() {
		this.findKnownsFromDirections();
		// FromEnding (if C can only go 1 direction)
		// FromOnlyUndone (C can only go in directions of undone T)
		// FromReached (T can only be reached from 1 undone C)
	}

}
