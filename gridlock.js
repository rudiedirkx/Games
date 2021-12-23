"use strict";

class GridlockBar {

	constructor(from, name) {
		this.from = from;
		this.name = name;
		this.size = null;
		this.axis = '';
		this.color = '';

		this.cells = [from];
	}

	getTo() {
		const D = this.getD();
		return this.from.add(D.multiply((this.size.x || this.size.y) - 1));
	}

	getD(dir) {
		const D = new Coords2D(0, 0);
		D[this.axis] = dir < 0 ? -1 : 1;
		return D;
	}

	addCell(C) {
		this.cells.push(C);
	}

	validateCells() {
		if (this.cells.length < 2) {
			throw new Error('Too few cells', this);
		}

		var dir;
		if (this.cells[0].x == this.cells[1].x) {
			dir = Coords2D.dir4Coords[2];
			this.axis = 'y';
		}
		else if (this.cells[0].y == this.cells[1].y) {
			dir = Coords2D.dir4Coords[1];
			this.axis = 'x';
		}
		else {
			throw new Error('Not on one axis');
		}

		for ( let i = 1; i < this.cells.length; i++ ) {
			if (!this.cells[i].equal(this.cells[i-1].add(dir))) {
				throw new Error('Not inline');
			}
		}

		this.size = dir.multiply(this.cells.length);
		// this.cells = null;
	}

}

class Gridlock extends CanvasGame {

	static LEVELS = [];

	static OFFSET = 20;
	static SQUARE = 60;
	static MARGIN = 3;

	static COLOR_TARGET = 'lightblue';

	reset() {
		super.reset();

		this.setMoves(0);

		this.levelNum = 0;
		this.width = 0;
		this.height = 0;
		this.bars = [];
		this.exit = null;

		this.dragging = null;
	}

	setLevelNum(n) {
		this.levelNum = n;

		$('#level-num').textContent = `${(n + 1)} / ${Gridlock.LEVELS.length}`;
		$('#prev').disabled = n <= 0;
		$('#next').disabled = n >= Gridlock.LEVELS.length-1;
	}

	loadLevel(n) {
		if (!Gridlock.LEVELS[n]) return;

		this.reset();
		this.setLevelNum(n);

		const level = Gridlock.LEVELS[n];

		this.width = Math.max(...level.map.map(row => row.length));
		this.height = level.map.length;
		this.exit = Coords2D.fromArray(level.exit || [6, 2]);
		try {
			this.bars = this.makeBarsFromMap(level.map);
		}
		catch (ex) {
			setTimeout(() => alert('LEVEL ERROR: ' + ex), 60);
		}

		this.canvas.width = Gridlock.OFFSET + this.width * (Gridlock.SQUARE + Gridlock.MARGIN) - Gridlock.MARGIN + Gridlock.OFFSET;
		this.canvas.height = Gridlock.OFFSET + this.height * (Gridlock.SQUARE + Gridlock.MARGIN) - Gridlock.MARGIN + Gridlock.OFFSET;

		this.changed = true;
	}

	makeBarsFromMap(level) {
		const width = Math.max(...level.map(row => row.length));
		const height = level.length;

		const barsMap = {};
		for ( let y = 0; y < height; y++ ) {
			for ( let x = 0; x < width; x++ ) {
				const b = level[y][x];
				if (b != ' ') {
					const C = new Coords2D(x, y);
					if (barsMap[b]) {
						barsMap[b].addCell(C);
					}
					else {
						barsMap[b] = new GridlockBar(C, b);
						if (b === 'z') {
							barsMap[b].color = Gridlock.COLOR_TARGET;
						}
						else {
							barsMap[b].color = Gridlock.makeRandomBarColor();
						}
					}
				}
			}
		}

		const bars = Object.values(barsMap);
		bars.forEach(bar => bar.validateCells());

		return bars;
	}

	static makeRandomColor() {
		return '#' + ('00000' + parseInt(Math.random() * 16777216).toString(16)).slice(-6);
	}

	static makeRandomBarColor() {
		const c = ('0' + (50 + parseInt(Math.random() * 116)).toString(16)).slice(-2);
		// const c = (10 + parseInt(Math.random() * 216)).toString(16);
		return '#' + c + c + c;
	}

	drawStructure() {
		this.drawGrid();
		// this.drawBorder();
		this.drawExit();
	}

	drawContent() {
		this.drawBars();

		// this.prepareCircle(new Coords2D(150, 150), 80).fill('red').stroke('yellow', 5);
		// this.prepareRectangle(new Coords2D(150, 150), new Coords2D(250, 250)).fill('green').stroke('purple', 5);
	}

	drawGrid() {
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				const from = this.scale(new Coords2D(x, y));
				const to = from.add(new Coords2D(Gridlock.SQUARE, Gridlock.SQUARE));
				this.drawRectangle(from, to, {color: '#fff', fill: true});
			}
		}
	}

	drawExit() {
		const from = this.scale(new Coords2D(this.exit.x, this.exit.y));
		const to = from.add(new Coords2D(Gridlock.SQUARE, Gridlock.SQUARE));

		if (this.exit.x === -1) {
			to.x += Gridlock.MARGIN;
		}
		else if (this.exit.x === this.width) {
			from.x -= Gridlock.MARGIN;
		}
		else if (this.exit.y === -1) {
			to.y += Gridlock.MARGIN;
		}
		else if (this.exit.y === this.width) {
			from.y -= Gridlock.MARGIN;
		}

		this.drawRectangle(from, to, {color: Gridlock.COLOR_TARGET, fill: true});
	}

	drawBorder() {
		for ( var y = Gridlock.OFFSET - 1, my = this.canvas.height - Gridlock.OFFSET + 1; y < my; y++ ) {
			this.drawBorderPixel('x', new Coords2D(Gridlock.OFFSET - 2, y));
			this.drawBorderPixel('x', new Coords2D(this.canvas.width - Gridlock.OFFSET + 2, y));
		}

		for ( var x = Gridlock.OFFSET - 1, mx = this.canvas.width - Gridlock.OFFSET + 1; x < mx; x++ ) {
			this.drawBorderPixel('y', new Coords2D(x, Gridlock.OFFSET - 2));
			this.drawBorderPixel('y', new Coords2D(x, this.canvas.height - Gridlock.OFFSET + 2));
		}

		if (this.exit.x == this.width) {
			const y1 = this.scale(this.exit.y) - 2;
			const y2 = this.scale(this.exit.y + 1) - Gridlock.MARGIN + 2;
			for ( var x = 0; x < Gridlock.OFFSET; x++ ) {
				this.drawBorderPixel('y', new Coords2D(this.canvas.width - Gridlock.OFFSET + x, y1));
				this.drawBorderPixel('y', new Coords2D(this.canvas.width - Gridlock.OFFSET + x, y2));
			}
		}
	}

	drawBorderPixel(axis, C) {
		C[axis] += parseInt(Math.random() * 3) - 1;
		this.drawDot(C, {radius: 1});
	}

	drawBars() {
		this.bars.forEach(bar => {
			if (this.dragging && this.dragging.bar === bar) {
				this.drawDraggingBar(bar);
			}
			else {
				this.drawBar(bar);
			}
		});
	}

	drawBar(bar) {
		const from = this.scale(bar.from);
		const to = this.scale(bar.from.add(new Coords2D(bar.size.x || 1, bar.size.y || 1))).subtract(new Coords2D(Gridlock.MARGIN, Gridlock.MARGIN));
		const color = bar.color;
		this.prepareRoundedRectangle(from, to, 3).fill(color);
	}

	drawDraggingBar(bar) {
		const from = this.scale(bar.from);
		const to = this.scale(bar.from.add(new Coords2D(bar.size.x || 1, bar.size.y || 1))).subtract(new Coords2D(Gridlock.MARGIN, Gridlock.MARGIN));

		const open = this.getOpenSpaces(bar, this.dragging.diff);
		const maxMove = (Gridlock.SQUARE + Gridlock.MARGIN) * open + Gridlock.MARGIN;
		const move = Math.min(maxMove, Math.abs(this.dragging.diff)) * (this.dragging.diff < 0 ? -1 : 1);

		const axis = this.dragging.bar.axis;
		from[axis] += move;
		to[axis] += move;

		const color = bar.color;
		this.prepareRoundedRectangle(from, to, 3).stroke('#bbb', 2).fill(color);
	}

	getOpenSpaces(bar, dir) {
		const D = bar.getD(dir);

		var loc = dir < 0 ? bar.from : bar.getTo();
		for ( let d = 1; d < 10; d++ ) {
			loc = loc.add(D);
			if (!this.inside(loc) || this.getBar(loc)) {
				return d - 1;
			}
		}
	}

	inside(C) {
		return C.x >= 0 && C.y >= 0 && C.x < this.width && C.y < this.height;
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Gridlock.OFFSET + source * (Gridlock.SQUARE + Gridlock.MARGIN);
	}

	unscale( source ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return C;
		}

		return Math.round((source - Gridlock.OFFSET - Gridlock.SQUARE/2) / (Gridlock.MARGIN + Gridlock.SQUARE));
	}

	getTarget() {
		return this.bars.find(bar => bar.name == 'z');
	}

	haveWon() {
		const target = this.getTarget();

		const prev = target.from.add(target.getD(-1));
		if (prev.equal(this.exit)) return true;

		const next = target.getTo().add(target.getD(+1));
		if (next.equal(this.exit)) return true;

		return false;
	}

	getBar(C) {
		return this.bars.find(bar => this.barIsAt(bar, C));
	}

	barIsAt(bar, C) {
		return true &&
			bar.from.x <= C.x && bar.from.x + (bar.size.x || 1) > C.x &&
			bar.from.y <= C.y && bar.from.y + (bar.size.y || 1) > C.y;
	}

	moveBar(bar, move) {
		bar.from[bar.axis] += move;
		this.setMoves(this.m_iMoves+1);

		this.startWinCheck();
	}

	listenControls() {
		this.listenDrag();
		this.listenActions();

		$('#restart').on('click', e => {
			e.preventDefault();
			this.loadLevel(this.levelNum);
		});
	}

	listenActions() {
		$('#prev').on('click', (e) => {
			this.loadLevel(this.levelNum - 1);
		});
		$('#next').on('click', (e) => {
			this.loadLevel(this.levelNum + 1);
		});
	}

	listenDrag() {
		var start;

		this.canvas.on(['mousedown', 'touchstart'], (e) => {
			e.preventDefault();

			const C = this.unscale(e.subjectXY);
			const bar = this.getBar(C);
			if (bar) {
				start = e.subjectXY;
				this.dragging = {bar, diff: 0};
				this.changed = true;
			}
		});
		this.canvas.on(['mousemove', 'touchmove'], (e) => {
			if (this.dragging) {
				const bar = this.dragging.bar;
				const diff = e.subjectXY.subtract(start)[bar.axis];
				this.dragging.diff = diff;
				this.changed = true;
			}
		});
		document.on(['mouseup', 'touchend'], (e) => {
			if (this.dragging) {
				const move = this.dragging.diff / (Gridlock.MARGIN + Gridlock.SQUARE);
				const max = this.getOpenSpaces(this.dragging.bar, this.dragging.diff);
				const full = Math.min(max, Math.abs(Math.round(move))) * (move < 0 ? -1 : 1);
				if (full != 0 /*&& Math.abs(full - move) < 0.45*/) {
					this.moveBar(this.dragging.bar, full);
				}
			}

			this.changed = true;
			this.dragging = null;
			start = null;
		});
	}

	createGame() {
	}

}

class GridlockEditor extends GridGameEditor {

	cellTypes() {
		return {
			"z": 'Target block',
			"a": 'Block A',
			"b": 'Block B',
			"c": 'Block C',
			"d": 'Block D',
			"e": 'Block E',
			"f": 'Block F',
			"g": 'Block G',
			"h": 'Block H',
			"i": 'Block I',
			"j": 'Block J',
			"k": 'Block K',
			"l": 'Block L',
			"m": 'Block M',
			"n": 'Block N',
		};
	}

	defaultCellType() {
		return 'z';
	}

	createCellTypeCell( type ) {
		return '<td data-block="' + type + '"></td>';
	}

	exportLevel() {
		const map = [];

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				row += cell.data('block') || ' ';
			});
			map.push(row);
		});

		const level = {map};
		return level;
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

	setBlockType( cell, type ) {
		const cur = cell.data('block');
		if (cur === type) {
			cell.data('block', null);
			cell.setText('');
		}
		else {
			cell.data('block', type);
			cell.setText(type);
		}
	}

	setType_z( cell ) {
		this.setBlockType(cell, 'z');
	}

	setType_a( cell ) {
		this.setBlockType(cell, 'a');
	}

	setType_b( cell ) {
		this.setBlockType(cell, 'b');
	}

	setType_c( cell ) {
		this.setBlockType(cell, 'c');
	}

	setType_d( cell ) {
		this.setBlockType(cell, 'd');
	}

	setType_e( cell ) {
		this.setBlockType(cell, 'e');
	}

	setType_f( cell ) {
		this.setBlockType(cell, 'f');
	}

	setType_g( cell ) {
		this.setBlockType(cell, 'g');
	}

	setType_h( cell ) {
		this.setBlockType(cell, 'h');
	}

	setType_i( cell ) {
		this.setBlockType(cell, 'i');
	}

	setType_j( cell ) {
		this.setBlockType(cell, 'j');
	}

	setType_k( cell ) {
		this.setBlockType(cell, 'k');
	}

	setType_l( cell ) {
		this.setBlockType(cell, 'l');
	}

	setType_m( cell ) {
		this.setBlockType(cell, 'm');
	}

	setType_n( cell ) {
		this.setBlockType(cell, 'n');
	}

}
