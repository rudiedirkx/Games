"use strict";

class LinxPad extends Coords2D {
	constructor(x, y, type) {
		super(x, y);
		this.type = type;
	}
}

class LinxPath {
	constructor(start) {
		this.nodes = [start];
	}

	add(cell) {
		return this.nodes.push(cell);
	}

	equals(C) {
		return this.nodes.length == 1 && this.nodes[0].equal(C);
	}

	isNeighbor(C) {
		const end = this.nodes[this.nodes.length-1];
		return Coords2D.dir4Coords.some(O => {
			return end.add(O).equal(C);
		});
	}

	contains(C) {
		return this.nodes.some(N => N.equal(C));
	}

	containsAnywhere(C) {
		return this.contains(C);
	}

	containsInside(C) {
		return this.nodes.length > 2 && this.nodes.slice(1, this.nodes.length - 1).some(N => N.equal(C));
	}

	removeUntil(C) {
		for ( let i = this.nodes.length - 1; i >= 0; i-- ) {
			if (this.nodes[i].equal(C)) {
				return;
			}
			this.nodes.pop();
		}
	}

	connectsTo(C) {
		return this.startEquals(C) || this.endEquals(C);
	}

	startEquals(C) {
		return C.equal(this.nodes[0]);
	}

	endEquals(C) {
		return C.equal(this.nodes[this.nodes.length-1]);
	}

	get type() {
		return this.nodes[0].type;
	}
}

class Linx extends CanvasGame {

	static LEVELS = [];

	static OFFSET = 20;
	static WHITESPACE = 3;
	static SQUARE = 40;

	static COLORS = ['fuchsia', 'red', 'orange', 'white', 'black', '#0d0', 'blue'];
	static CELL = 20;
	static NA = 21;

	static DRAG_NO = 0;
	static DRAG_NEXT = 1;
	static DRAG_SAME = 2;
	static DRAG_BACKTRACK = 3;

	reset() {
		super.reset();

		this.levelNum = 0;
		// this.type = '';
		this.width = 0;
		this.height = 0;
		this.grid = [];
		this.pads = [];

		this.dragging = null;
		this.paths = [];

		this.winnable = false;

		// this.dragging = new LinxPath(new LinxPad(1, 1, parseInt(Linx.COLORS.length * Math.random())));
		// this.dragging.add(new Coords2D(2, 1));
		// this.dragging.add(new Coords2D(3, 1));
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Linx.OFFSET + Linx.SQUARE/2 + source * Linx.SQUARE;
	}

	unscale(source) {
		if (source instanceof Coords2D) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return this.inside(C) ? C : null;
		}

		return Math.round((source - Linx.OFFSET - Linx.SQUARE/2) / Linx.SQUARE);
	}

	inside(coord) {
		return coord.x >= 0 && coord.x < this.width && coord.y >= 0 && coord.y < this.height;
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.levelNum,
		};
	}

	setLevelNum(n) {
		this.levelNum = n;

		$('#level-num').textContent = `${(n + 1)} / ${Linx.LEVELS.length}`;
		$('#prev').disabled = n <= 0;
		$('#next').disabled = n >= Linx.LEVELS.length-1;
	}

	ensureLevelFormat(level) {
		if (!(level.map instanceof Array)) {
			level = {map: level};
		}
		return level;
	}

	extractGrid(map) {
		const grid = [];
		const pads = [];
		for ( let y = 0; y < this.height; y++ ) {
			const row = [];
			for ( let x = 0; x < this.width; x++ ) {
				const T = (map[y][x] || '').trim();
				if (T == 'x') {
					row.push(Linx.NA);
				}
				else if (T) {
					row.push(parseInt(T));
					pads.push(new LinxPad(x, y, parseInt(T)));
				}
				else {
					row.push(Linx.CELL);
				}
			}
			grid.push(row);
		}
		return [grid, pads];
	}

	loadLevel(n) {
		if (!Linx.LEVELS[n]) return;

		this.reset();
		this.setLevelNum(n);

		const level = this.ensureLevelFormat(Linx.LEVELS[n]);

		// this.type = level.type || '';
		this.width = Math.max(...level.map.map(row => row.length));
		this.height = level.map.length;
		[this.grid, this.pads] = this.extractGrid(level.map);

		this.canvas.width = Linx.OFFSET * 2 + Linx.SQUARE * this.width;
		this.canvas.height = Linx.OFFSET * 2 + Linx.SQUARE * this.height;

		this.changed = true;
	}

	drawGrid() {
		this.drawRectangle(
			new Coords2D(Linx.OFFSET, Linx.OFFSET),
			new Coords2D(this.canvas.width - Linx.OFFSET, this.canvas.height - Linx.OFFSET),
			{color: '#ddd', fill: true}
		);

		for (var y = Linx.OFFSET; y < this.canvas.height; y += Linx.SQUARE) {
			this.drawLine(new Coords2D(Linx.WHITESPACE, y), new Coords2D(this.canvas.width - Linx.WHITESPACE, y), {color: '#fff', width: 1});
		}

		for (var x = Linx.OFFSET; x < this.canvas.width; x += Linx.SQUARE) {
			this.drawLine(new Coords2D(x, Linx.WHITESPACE), new Coords2D(x, this.canvas.height - Linx.WHITESPACE), {color: '#fff', width: 1});
		}

		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				if (this.grid[y][x] === Linx.NA) {
					this.drawRectangle(
						this.scale(new Coords2D(x - 0.5, y - 0.5)),
						this.scale(new Coords2D(x + 0.5, y + 0.5)),
						{color: '#fff', fill: true}
					);
				}
			}
		}
	}

	drawPath(path) {
		for ( let i = 1; i < path.nodes.length; i++ ) {
			this.drawLine(
				this.scale(path.nodes[i-1]),
				this.scale(path.nodes[i-0]),
				{width: 4, color: Linx.COLORS[path.type]}
			);
		}
	}

	drawPaths() {
		this.paths.forEach(P => this.drawPath(P));
	}

	drawDragging() {
		if (!this.dragging) return;

		this.drawPath(this.dragging);
	}

	drawPads() {
		this.pads.forEach(P => this.drawDot(this.scale(P), {radius: 10, color: Linx.COLORS[P.type]}));
	}

	drawContent() {
		this.drawGrid();
		this.drawPaths();
		this.drawPads();
		this.drawDragging();
	}

	handleClick(C) {
		C = this.unscale(C);
		this.drawDot(this.scale(C));
	}

	getPad(C) {
		return C && this.pads.find(P => P.equal(C));
	}

	addPath(path) {
		this.paths.push(path);
		this.recalcMoves();
	}

	removePath(C) {
		this.paths = this.paths.filter(P => !P.connectsTo(C));
		this.recalcMoves();
	}

	recalcMoves() {
		this.setMoves(this.paths.reduce((T, P) => T + P.nodes.length - 1, 0));
	}

	haveWon() {
		return this.pads.every(pad => {
			return this.paths.some(path => path.connectsTo(pad));
		});
	}

	draggableTo(path, C) {
		if (!path.isNeighbor(C)) {
// console.log(1);
			return Linx.DRAG_NO;
		}

		const T = this.grid[C.y][C.x];
		if (T == Linx.NA) {
// console.log(2);
			return Linx.DRAG_NO;
		}

		if (path.contains(C)) {
			if (this.isSingular(path.type) && path.startEquals(C)) {
// console.log(3);
				return Linx.DRAG_SAME;
			}
// console.log(4);
			return Linx.DRAG_BACKTRACK;
		}

		if (this.paths.some(P => this.isMultiple(P.type) ? P.containsInside(C) : P.containsAnywhere(C))) {
// console.log(5);
			return Linx.DRAG_NO;
		}

		if (T != Linx.CELL) {
// console.log(6);
			return T == path.type ? Linx.DRAG_SAME : Linx.DRAG_NO;
		}

		return Linx.DRAG_NEXT;
	}

	countPads(type) {
		return this.pads.filter(P => P.type == type).length;
	}

	isMultiple(type) {
		const pads = this.countPads(type);
		return pads > 2 && (pads % 2) == 1;
	}

	isSingular(type) {
		return this.countPads(type) == 1;
	}

	handleDragStart(pad) {
		this.startTime();
		if (!this.isMultiple(pad.type)) {
			const path = this.paths.find(P => P.startEquals(pad) || P.endEquals(pad));
			if (path) {
				this.removePath(path.nodes[0]);
			}
		}

		this.dragging = new LinxPath(pad);
	}

	handleDragEndSame(C) {
		this.dragging.add(C);
		this.addPath(this.dragging);

		this.winnable = true;
	}

	handleDragEndNext(C) {
		this.dragging.add(C);
	}

	handleDragEndBacktrack(C) {
		this.dragging.removeUntil(C);
	}

	listenDrag() {
		this.canvas.on(['mousedown', 'touchstart'], (e) => {
			e.preventDefault();

			const C = this.unscale(e.subjectXY);
			const pad = this.getPad(C);
			if (pad) {
				this.handleDragStart(pad);
			}
		});
		this.canvas.on(['mousemove', 'touchmove'], (e) => {
			if (this.dragging) {
				const C = this.unscale(e.subjectXY);
				if (C) {
					if (this.dragging.endEquals(C)) {
						return;
					}

					const draggable = this.draggableTo(this.dragging, C);
					if (draggable == Linx.DRAG_SAME) {
						this.handleDragEndSame(C);
						this.dragging = null;
						this.changed = true;
						return;
					}
					else if (draggable == Linx.DRAG_BACKTRACK) {
						this.handleDragEndBacktrack(C);
						this.changed = true;
						return;
					}
					else if (draggable == Linx.DRAG_NEXT) {
						this.handleDragEndNext(C);
						this.changed = true;
						return;
					}
				}
				this.dragging = null;
				this.changed = true;
			}
		});
		this.canvas.on(['mouseup', 'touchend'], (e) => {
			const C = this.unscale(e.subjectXY);
			if (C && this.dragging && this.dragging.equals(C)) {
				this.removePath(C);
			}
		});
		document.on(['mouseup', 'touchend'], (e) => {
			this.changed = true;
			this.dragging = null;

			if (this.winnable) {
				this.winnable = false;
				this.startWinCheck();
			}
		});
	}

	listenControls() {
		// this.listenClick();
		this.listenDrag();

		$('#restart').on('click', (e) => {
			this.loadLevel(this.levelNum);
		});
		$('#prev').on('click', (e) => {
			this.loadLevel(this.levelNum - 1);
		});
		$('#next').on('click', (e) => {
			this.loadLevel(this.levelNum + 1);
		});
	}

}

class LinxEditor extends GridGameEditor {

	defaultCellType() {
		return '1';
	}

	cellTypes() {
		return {
			"x": 'Block',
			"1": '1',
			"2": '2',
			"3": '3',
			"4": '4',
			"5": '5',
			"6": '6',
		};
	}

	setType_x( cell ) {
		cell.toggleClass('block');
		cell.data('type', null);
	}

	setType_( cell, type ) {
		cell.removeClass('block');
		cell.data('type', cell.data('type') === type ? null : type);
	}

	exportLevel() {
		return {
			map: this.m_objGrid.getElements('tr').map(tr => {
				return tr.getElements('td').map(td => {
					return td.data('type') || (td.hasClass('block') ? 'x' : ' ');
				}).join('');
			}),
		};
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		return code;
	}

}
