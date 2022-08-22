"use strict";

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

		this.editing = false;
		this.grid = [];
		this.width = 0;
		this.height = 0;

		this.OFFSET = 40;
		this.SQUARE = 50;
		this.CIRCLE = 22;
		this.TEXT = 40;
		this.STRUCTURE = '#999';
		this.BRIDGE = ['limegreen', 'orange', 'hotpink', 'cyan'];

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
		const doColors = this.BRIDGE.length > 1;
		const clusters = doColors ? this.mapClusters(this.constructor.getClusters(this.bridges)) : {};
		this.bridges.forEach(B => {
			const bridgeColor = doColors ? this.BRIDGE[ clusters[B.from.join()] % this.BRIDGE.length ] : this.BRIDGE[0];
			if (B.strength == 1) {
				this.drawLine(this.scale(B.from), this.scale(B.to), {width: 5, color: bridgeColor});
			}
			else {
				this.drawLine(this.scale(B.from), this.scale(B.to), {width: 12, color: bridgeColor});
				this.drawLine(this.scale(B.from), this.scale(B.to), {width: 4, color: '#eee'});
			}
		});
	}

	haveWon() {
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				if (this.grid[y][x] && !this.isComplete(new Coords2D(x, y))) {
					return false;
				}
			}
		}

		return this.constructor.getClusters(this.bridges).length == 1;
	}

	static getClusters(bridges) {
		var coords = bridges.reduce((L, B) => {
			return L.push(B.from.join(), B.to.join()), L;
		}, []).unique();

		const clusters = [];
		while (coords.length) {
			const start = coords.pop();
			const cluster = [];
			this.expandClusterFrom(bridges, cluster, start);
			coords = coords.filter(C => !cluster.includes(C));
			clusters.push(cluster);
		}

		return clusters;
	}

	static expandClusterFrom(bridges, cluster, C) {
		cluster.push(C);

		bridges.forEach(B => {
			const from = B.from.join();
			const to = B.to.join();
			if (from == C && !cluster.includes(to)) this.expandClusterFrom(bridges, cluster, to);
			if (to == C && !cluster.includes(from)) this.expandClusterFrom(bridges, cluster, from);
		});
	}

	mapClusters(clusters) {
		const map = {};
		clusters.forEach((L, i) => L.forEach(C => map[C] = i));
		return map;
	}

	getCrossing(C) {
		C = C.multiply(this.canvas.width / this.canvas.offsetWidth);
		const crossing = new Coords2D(
			Math.round((C.x - this.OFFSET) / this.SQUARE),
			Math.round((C.y - this.OFFSET) / this.SQUARE)
		);
		return crossing;
	}

	getRequirement(C) {
		return this.grid[C.y] && this.grid[C.y][C.x];
	}

	getConnections(C) {
		return this.bridges.reduce((T, B) => B.from.equal(C) || B.to.equal(C) ? T + B.strength : T, 0);
	}

	isComplete(C) {
		return this.getConnections(C) == this.grid[C.y][C.x];
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
		this.listenWheel();

		$('#cheat').on('click', e => this.cheatOneRound());

		$('#restart').on('click', e => {
			this.bridges.length = 0;
			this.changed = true;
		});

		$('#edit').on('click', e => {
			this.editing = !this.editing;
			document.body.toggleClass('editing', this.editing);
			this.bridges.length = 0;
			this.changed = true;
		});

		$('#clear').on('click', e => {
			this.grid = this.grid.map(L => L.fill(0));
			this.changed = true;
		});
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

	listenWheel() {
		this.canvas.on('wheel', e => {
			if (this.editing) {
				e.preventDefault();
				this.handleWheel(e.subjectXY, e.deltaY > 0 ? -1 : 1);
			}
		});
	}

	handleWheel(C, delta) {
		const crossing = this.getCrossing(C);
		if (!crossing || this.getRequirement(crossing) == null) return;

		this.grid[crossing.y][crossing.x] += delta;
		this.changed = true;
	}

	handleClick(C) {
		if (!this.clickCheat) return;

		const crossing = this.getCrossing(C);
		if (crossing && this.getRequirement(crossing)) {
			this.cheatOneRoundFromStart(crossing);
		}
	}

	cheatOneRoundFromStart(C) {
		this.m_bCheating = true;

		const solver = BridgesSolver.fromState(this.grid, this.bridges);
		solver.findKnownsFromSamesStarting(C);
		solver.findKnownsFromDirectionsStarting(C);
		solver.findKnownsFromOnlyUndoneStarting(C);
		solver.findKnownsFromClustersStarting(C);
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
		const existsIndex = this.bridges.findIndex(B => B.equal(bridge));
		if (existsIndex == -1) {
			this.bridges.push(bridge);
			this.updates.push(bridge);
		}
		else if (bridge.strength > this.bridges[existsIndex].strength) {
			this.bridges[existsIndex] = bridge;
			this.updates.push(bridge);
		}
	}

	addBridgeStrength(bridge) {
		const existsIndex = this.bridges.findIndex(B => B.equal(bridge));
		if (existsIndex == -1) {
			return this.requireBridge(bridge);
		}

		bridge.strength = 2;
		this.requireBridge(bridge);
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

	getConnectionsUsed(C) {
		const bridges = this.bridges.filter(B => B.from.equal(C) || B.to.equal(C));
		const used = bridges.reduce((total, B) => total + B.strength, 0);
		return used;
	}

	getBridgeStrength(from, to) {
		const bridge = new Bridge(from, to);
		const exists = this.bridges.find(B => B.equal(bridge));
		return exists ? exists.strength : 0;
	}

	collateRequiredAndAvailable(C) {
		const required = this.grid[C.y][C.x];
		const targets = this.getTargetsFrom(C);

		let used = 0;
		const available = [];
		targets.forEach(T => {
			if (this.getConnectionsUsed(T) == this.grid[T.y][T.x]) {
				used += this.getBridgeStrength(C, T);
			}
			else {
				available.push(T);
			}
		});

		return [required - used, available];
	}

	findKnownsFromSamesStarting(C) {
		const required = this.grid[C.y][C.x];
		if (required > 2) return;

		// @todo Fix logic bug for
		//     2
		// 3  (2)
		//     2
		// In demo=9

		const targets = this.getTargetsFrom(C).filter(C => this.grid[C.y][C.x] != required);
		if (targets.length == 1) {
			this.requireBridge(new Bridge(C, targets[0]));
		}
	}

	findKnownsFromDirectionsStarting(C) {
		const [required, targets] = this.collateRequiredAndAvailable(C);
		const targetRequireds = targets.map(C => this.grid[C.y][C.x]).sort().map(n => Math.min(2, n));
		const ones = targetRequireds.filter(n => n == 1).length;
		const twos = targetRequireds.filter(n => n == 2).length;

		if (twos * 2 + ones == required) {
			targets.forEach(T => {
				this.requireBridge(new Bridge(C, T, this.grid[T.y][T.x] > 1 ? 2 : 1));
			});
			return;
		}

		if (Math.ceil((required - ones) / 2) == twos) {
			targets.forEach(T => {
				if (this.grid[T.y][T.x] >= 2) {
					this.requireBridge(new Bridge(C, T));
				}
			});
			return;
		}
	}

	findKnownsFromOnlyUndoneStarting(C) {
		const required = this.grid[C.y][C.x];
		const bridges = this.bridges.filter(B => B.from.equal(C) || B.to.equal(C));
		const used = bridges.reduce((total, B) => total + B.strength, 0);
		if (required == used) return;

		const targets = this.getTargetsFrom(C);
		const targetsLeft = targets.map(T => {
			return Math.min(this.grid[T.y][T.x] - this.getConnectionsUsed(T), 2 - this.getBridgeStrength(T, C));
		});

		const targetsLeftTotal = targetsLeft.reduce((total, n) => total + n, 0);
		if (targetsLeftTotal == required - used) {
			targetsLeft.forEach((n, i) => {
				n > 0 && this.addBridgeStrength(new Bridge(C, targets[i], n));
			});
			return;
		}

		const targetsLeftDirs = targetsLeft.filter(n => n > 0);
		if (targetsLeftDirs.length == 1) {
			targetsLeft.forEach((n, i) => {
				n > 0 && this.addBridgeStrength(new Bridge(C, targets[i], required - used));
			});
			return;
		}

		const maxLeft = Math.max(...targetsLeft);
		if (maxLeft > targetsLeftTotal - maxLeft && required - used >= maxLeft) {
			this.requireBridge(new Bridge(C, targets[targetsLeft.indexOf(maxLeft)]));
		}
	}

	findKnownsFromClustersStarting(C) {
		if (this.grid[C.y][C.x] == this.getConnectionsUsed(C)) return;

		console.log('findKnownsFromClusters', C);
	}

	findKnownsFromSames() {
		this.requireds.forEach(C => this.findKnownsFromSamesStarting(C));
	}

	findKnownsFromDirections() {
		this.requireds.forEach(C => this.findKnownsFromDirectionsStarting(C));
	}

	findKnownsFromOnlyUndone() {
		this.requireds.forEach(C => this.findKnownsFromOnlyUndoneStarting(C));
	}

	findKnownsFromClusters() {
		if (!this.updates.length) {
			this.requireds.forEach(C => this.findKnownsFromClustersStarting(C));
		}
	}

	findKnowns() {
		this.findKnownsFromSames();
		this.findKnownsFromDirections();
		this.findKnownsFromOnlyUndone();
		this.findKnownsFromClusters();

		// ... To connect clusters (see demo=2 top left)
		// ... To make sure a separate cluster isn't 'completed' (see demo=1 top, demo=6)
	}

}
