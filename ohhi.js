"use strict";

class OhhiBuilder {

	static createEmpty(size) {
		return (new Array(size)).fill(0).map(row => (new Array(size)).fill(0));
	}

	constructor(size) {
		this.size = size;
		this.grid = OhhiBuilder.createEmpty(this.size);
		this.buildTries = 1;
		this.playableTries = 1;
	}

	restartRow(row) {
		this.buildTries++;
		this.grid[row].fill(0);
	}

	restartGrid() {
		this.buildTries++;
		this.grid.forEach(cells => cells.fill(0));
	}

	static isValidLineDistribution(line) {
		if ( line.replace(/1/g, '').length != line.length / 2 ) {
			return false;
		}

		if ( line.match(/(111|222)/) ) {
			return false;
		}

		return true;
	}

	getLineRow(y) {
		return this.grid[y].join('');
	}

	getLineRows() {
		return this.grid.map((x, i) => this.getLineRow(i));
	}

	getLineCol(x) {
		return this.grid.map(cells => cells[x]).join('');
	}

	getLineCols() {
		return this.grid.map((x, i) => this.getLineCol(i));
	}

	getOneAsNum(x, y) {
		return this.grid[y] && this.grid[y][x] || 0;
	}

	getOneAsPrevString(x, y) {
		const num = this.getOneAsNum(x, y);
		return num === 0 ? '' : String(num);
	}

	getPrevTwoInRow(x, y) {
		const p1 = this.getOneAsPrevString(x-1, y);
		const p2 = this.getOneAsPrevString(x-2, y);
		return p1 + p2;
	}

	getPrevTwoInCol(x, y) {
		const p1 = this.getOneAsPrevString(x, y-1);
		const p2 = this.getOneAsPrevString(x, y-2);
		return p1 + p2;
	}

	makeOneRandom() {
		if (Date.now() > this.timeout) {
			throw new Error('builder timeout');
		}

		return Math.random() > 0.5 ? 2 : 1;
	}

	build(timeout = 5000) {
		this.timeout = Date.now() + timeout;

		for ( let row = 0; row < this.size; row++ ) {
			for ( let col = 0; col < this.size; col++ ) {
				const p2row = this.getPrevTwoInRow(col, row);
				const p2col = this.getPrevTwoInCol(col, row);

				if ( p2row == '22' ) {
					if ( p2col == '11' ) {
// console.log('restart grid at', col, row);
						this.restartGrid();
						row = -1;
						col = 0;
						break;
					}
					else {
						this.grid[row][col] = 1;
					}
				}
				else if ( p2row == '11' ) {
					if ( p2col == '22' ) {
// console.log('restart grid at', col, row);
						this.restartGrid();
						row = -1;
						col = 0;
						break;
					}
					else {
						this.grid[row][col] = 2;
					}
				}
				else {
					if ( p2col == '22' ) {
						this.grid[row][col] = 1;
					}
					else if ( p2col == '11' ) {
						this.grid[row][col] = 2;
					}
					else {
						this.grid[row][col] = this.makeOneRandom();
					}
				}

				if ( row == this.size - 1 ) {
					const line = this.getLineCol(col);
					if ( !OhhiBuilder.isValidLineDistribution(line) ) {
// console.log('restart col/grid at', col);
						this.restartGrid();
						row = -1;
						col = 0;
						break;
					}
				}

				if ( row == this.size - 1 && col == this.size - 1 ) {
					const rows = this.getLineRows();
					if ( rows.unique().length != rows.length ) {
// console.log('restart grid (rows)');
						this.restartGrid();
						row = -1;
						col = 0;
						break;
					}

					const cols = this.getLineCols();
					if ( cols.unique().length != cols.length ) {
// console.log('restart grid (cols)');
						row = -1;
						col = 0;
						break;
					}
				}
			}

			if ( row != -1 ) {
				const line = this.getLineRow(row);
				if ( !OhhiBuilder.isValidLineDistribution(line) ) {
// console.log('restart row at', row, line);
					this.restartRow(row);
					row--;
				}
			}
		}

		return this.grid;
	}

	makePlayable(timeout = 5000) {
		const timeoutEnd = Date.now() + timeout;
		var playableGrid;
		while (!this.isPlayable(playableGrid = this.hideCellsPlayable())) {
			this.playableTries++;

			if (Date.now() > timeoutEnd) {
				throw new Error('playable timeout');
			}
		}

		return playableGrid;
	}

	hideCellsPlayable() {
		return this.grid.map(cells => cells.map(value => Math.random() < 0.3 ? value : 0));
	}

	isPlayable(grid) {
		const solver = new OhhiSolver(grid);
		for ( let i = 0; i < 40; i++ ) {
			solver.findKnowns();

			if (!solver.updates.length) {
				return solver.isPlayable();
			}
			solver.updates.length = 0;
		}

		return false;
	}

}

class Ohhi extends CanvasGame {

	static ON_USER = 1;
	static OFF_USER = 2;

	static OFFSET = 20;
	static SQUARE = 40;
	static MARGIN = 8;

	reset() {
		super.reset();

		this.grid = [];
		this.size = 0;
		this.lastChange = null;
	}

	drawStructure() {
	}

	drawContent() {
		this.drawGrid();
	}

	drawGrid() {
		for ( let y = 0; y < this.size; y++ ) {
			for ( let x = 0; x < this.size; x++ ) {
				const v = this.grid[y] && this.grid[y][x];
				const color = v == 2 ? 'red' : (v == 1 ? 'green' : '#ddd');

				if (this.lastChange && this.lastChange.x == x && this.lastChange.y == y) {
					this.ctx.fillStyle = 'black';
					this.ctx.fillRect(this.scale(x) - 2, this.scale(y) - 2, Ohhi.SQUARE + 4, Ohhi.SQUARE + 4);
				}

				this.ctx.fillStyle = color;
				this.ctx.fillRect(this.scale(x), this.scale(y), Ohhi.SQUARE, Ohhi.SQUARE);
			}
		}
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Ohhi.OFFSET + source * (Ohhi.SQUARE + Ohhi.MARGIN);
	}

	unscale( source ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return C;
		}

		return Math.round((source - Ohhi.OFFSET - Ohhi.SQUARE/2) / (Ohhi.MARGIN + Ohhi.SQUARE));
	}

	getState(C) {
		return C && this.grid[C.y] && this.grid[C.y][C.x];
	}

	handleClick( coord ) {
		const C = this.unscale(coord);
		const v = this.getState(C);
		if (v == null) return;

		this.startTime();

		if (v === 0) {
			this.grid[C.y][C.x] = Ohhi.ON_USER;
		}
		else if (v === Ohhi.ON_USER) {
			this.grid[C.y][C.x] = Ohhi.OFF_USER;
		}
		else if (v === Ohhi.OFF_USER) {
			this.grid[C.y][C.x] = 0;
		}

		this.changed = true;

		this.startWinCheck();
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.size,
		};
	}

	haveWon() {
		const solver = new OhhiSolver(this.grid);
		return solver.getUnknowns() == 0 && solver.isValidGrid();
	}

	createFromExport(chars) {
		const size = Math.sqrt(chars.length);
		if (size < 4 || chars.match(/[^012]/) || Math.ceil(size) != Math.floor(size)) {
			return false;
		}

		const grid = [];
		for ( let i = 0; i < chars.length; i++ ) {
			const C = chars[i];
			if (!grid[0] || grid[grid.length-1].length == size) {
				grid.push([]);
			}
			grid[grid.length-1].push(parseInt(C));
		}

		this.loadMap(grid);

		return true;
	}

	createGame() {
		this.createSizes();
	}

	createSizes() {
		const sizes = [4, 6, 8, 10];

		const parent = $('#sizes');
		parent.append('Size: ');
		sizes.forEach(size => {
			const el = document.el('a', {href: '#'}).data('size', size).setText(size);
			parent.append(el);
			parent.append(' ');
		});
	}

	createMap(size) {
		this.reset();

		this.size = size;

		console.time('createMap');
		const builder = new OhhiBuilder(size);
		builder.build(2000);
		console.timeEnd('createMap');
console.log(builder.buildTries, builder.grid);

		// this.printGrid(grid);

		console.time('make playable');
		const playableGrid = builder.makePlayable(2000);
		// const playableGrid = builder.hideCellsPlayable();
		console.timeEnd('make playable');
console.log(builder.playableTries, playableGrid);

		this.loadMap(playableGrid);
	}

	loadMap(grid) {
		this.reset();

		this.grid = grid;
		this.size = this.grid.length;
		this.gridBackup = JSON.stringify(this.grid);
		this.canvas.width = this.canvas.height = Ohhi.OFFSET + this.size * (Ohhi.SQUARE + Ohhi.MARGIN) - Ohhi.MARGIN + Ohhi.OFFSET;
		this.changed = true;
	}

	debugGrid(grid) {
		console.log(grid.map(row => row.map(val => val === null ? '_' : Number(val)).join(' ')).join("\n"));
	}

	createMapHtml(grid, initial = true) {
		const size = grid.length;

		initial = initial ? ' data-initial' : '';

		var html = '';
		html += '<table>';
		for ( let row = 0; row < size; row++ ) {
			html += '<tr>';
			for ( let col = 0; col < size; col++ ) {
				const n = grid[row][col];
				const attr = n === null ? '' : `${initial} data-color="${n ? 'on' : 'off'}"`;
				html += `<td${attr}><span>x</span></td>`;
			}
			html += '</tr>';
		}
		html += '</table>';

		return html;
	}

	listenControls() {
		this.listenClick();
		this.canvas.on('mousedown', (e) => {
			e.preventDefault();
		});

		$('#restart').on('click', e => {
			this.loadMap(JSON.parse(this.gridBackup));
		});

		$('#newgame').on('click', e => {
			const size = this.size;
			setTimeout(() => this.createMap(size), 60);
		});

		$('#sizes').on('click', 'a[data-size]', e => {
			e.preventDefault();

			const size = Number(e.target.data('size'));
			setTimeout(() => this.createMap(size), 60);
		});

		$('#build').on('click', e => {
			const size = prompt('What size?', '8');
			if (size && !isNaN(parseInt(size))) {
				this.printGrid(OhhiBuilder.createEmpty(parseInt(size)));
			}
		});

		$('#cheat').on('click', e => {
			this.cheatOneFind();
		});

		$('#export').on('click', e => {
			location.hash = this.exportCurrent();
		});
	}

	exportCurrent() {
		return this.grid.map(row => row.join('')).join('');
	}

	cheatOneFind() {
		this.m_bCheating = true;

		const solver = new OhhiSolver(this.grid);
		solver.findKnowns();
		if (solver.updates.length) {
			const found = solver.updates[0];
			this.grid[found.y][found.x] = found.color;
			console.log(found);
			this.lastChange = found;
		}

		this.changed = true;
	}

	cheatOneRound() {
		this.m_bCheating = true;

		const solver = new OhhiSolver(this.grid);
		solver.findKnowns();
		for (let found of solver.updates) {
			this.grid[found.y][found.x] = found.color;
		}

		this.changed = true;
	}

	createStats() {
	}

	setTime( time ) {
	}

	setMoves( moves ) {
	}

}

class OhhiSolver {

	static fromDom(table) {
		const map = {"on": 1, "off": 0};
		const grid = table.getElements('tr').map(tr => {
			return tr.getElements('td').map(td => map[td.dataset.color] == null ? null : map[td.dataset.color]);
		});
		return new this(grid);
	}

	static makeCoords(grid) {
		return grid.map((cells, y) => cells.map((val, x) => new Coords2D(x, y))).flat(1);
	}

	constructor(grid) {
		this.size = grid.length;
		this.grid = grid.map(cells => [...cells]);

		this.threeStarts = OhhiSolver.makeCoords(this.grid);

		this.seen = [];
		this.updates = [];
	}

	getUnknowns() {
		return this.threeStarts.map(C => this.grid[C.y][C.x]).filter(val => val == 0).length;
	}

	isPlayable() {
		return this.getUnknowns() == 0;
	}

	haveSeen(x, y) {
		const C = `${x}-${y}`;
		if (!this.seen.includes(C)) {
			this.seen.push(C);
			return false;
		}
		return true;
	}

	remember(found) {
		this.grid[found.y][found.x] = found.color;
		this.updates.push(found);
	}

	coordWithColor(x, y, color, reason) {
		const C = new Coords2D(x, y);
		C.color = color;
		C.reason = reason;
		return C;
	}

	other(color) {
		return color === 1 || color === '1' ? 2 : 1;
	}

	findKnownsFromAdjacentThrees() {
		for ( let C of this.threeStarts ) {
			const s = this.grid[C.y][C.x];

			if (C.x <= this.size - 3) {
				const r1 = this.grid[C.y][C.x+1];
				const r2 = this.grid[C.y][C.x+2];
				if (s == 0 && r1 != 0 && r2 == r1) {
					this.remember(this.coordWithColor(C.x, C.y, this.other(r2), 'before two hor sames'));
				}
				if (s != 0 && r1 == 0 && r2 == s) {
					this.remember(this.coordWithColor(C.x+1, C.y, this.other(r2), 'between two hor sames'));
				}
				if (s == r1 && r1 != 0 && r2 == 0) {
					this.remember(this.coordWithColor(C.x+2, C.y, this.other(s), 'after two hor sames'));
				}
			}

			if (C.y <= this.size - 3) {
				const b1 = this.grid[C.y+1][C.x];
				const b2 = this.grid[C.y+2][C.x];
				if (s == 0 && b1 != 0 && b2 == b1) {
					this.remember(this.coordWithColor(C.x, C.y, this.other(b2), 'before two ver sames'));
				}
				if (s != 0 && b1 == 0 && b2 == s) {
					this.remember(this.coordWithColor(C.x, C.y+1, this.other(b2), 'between two ver sames'));
				}
				if (s == b1 && b1 != 0 && b2 == 0) {
					this.remember(this.coordWithColor(C.x, C.y+2, this.other(s), 'after two ver sames'));
				}
			}
		}
	}

	findKnownsFromFulls() {
		const rows = this.getLineRows();
		const cols = this.getLineCols();

		const rememberFounds = (line, x, y, color, reason) => {
			return Array.from(line).forEach((v, i) => {
				if (v == '_') {
					this.remember(this.coordWithColor(x == null ? i : x, y == null ? i : y, color, reason));
				}
			});
		};

		for ( let y = 0; y < this.size; y++ ) {
			const line = rows[y];
			const ones = line.replace(/[2_]/g, '').length;
			const twos = line.replace(/[1_]/g, '').length;
			if (ones == this.size / 2 && twos < ones) {
				rememberFounds(line, null, y, 2, 'full of ones');
			}
			if (twos == this.size / 2 && ones < twos) {
				rememberFounds(line, null, y, 1, 'full of twos');
			}
		}

		for ( let x = 0; x < this.size; x++ ) {
			const line = cols[x];
			const ones = line.replace(/[2_]/g, '').length;
			const twos = line.replace(/[1_]/g, '').length;
			if (ones == this.size / 2 && twos < ones) {
				rememberFounds(line, x, null, 2, 'full of ones');
			}
			if (twos == this.size / 2 && ones < twos) {
				rememberFounds(line, x, null, 1, 'full of twos');
			}
		}
	}

	findKnownsFromUniqueLines() {
		const rows = this.getLineRows();
		const cols = this.getLineCols();

		for ( let y = 0; y < rows.length; y++ ) {
			const line = rows[y];
			if (line.replace(/[12]/g, '').length == 2) {
				const re = new RegExp('^' + line.replace(/_/g, '([12])') + '$');
				for ( let y2 = 0; y2 < rows.length; y2++ ) {
					const match = rows[y2].match(re);
					if (match) {
						const x1 = line.indexOf('_');
						const x2 = line.indexOf('_', x1+1);
						this.remember(this.coordWithColor(x1, y, this.other(match[1]), `hor line exists on y = ${y2}`));
						this.remember(this.coordWithColor(x2, y, this.other(match[2]), `hor line exists on y = ${y2}`));
						break;
					}
				}
			}
		}

		for ( let x = 0; x < cols.length; x++ ) {
			const line = cols[x];
			if (line.replace(/[12]/g, '').length == 2) {
				const re = new RegExp('^' + line.replace(/_/g, '([12])') + '$');
				for ( let x2 = 0; x2 < cols.length; x2++ ) {
					const match = cols[x2].match(re);
					if (match) {
						const y1 = line.indexOf('_');
						const y2 = line.indexOf('_', y1+1);
						this.remember(this.coordWithColor(x, y1, match[1] == '1' ? 2 : 1, `ver line exists on x = ${x2}`));
						this.remember(this.coordWithColor(x, y2, match[2] == '1' ? 2 : 1, `ver line exists on x = ${x2}`));
						break;
					}
				}
			}
		}
	}

	findKnownsFromThreeOpens() {
		return;

		const rows = this.getLineRows();
		const cols = this.getLineCols();

		for ( let y = 0; y < rows.length; y++ ) {
			const line = rows[y];
			if (line.replace(/[12]/g, '').length == 3 && line.match(/__/)) {
				console.log('three open in row', y, line);
				const m = line.match(/([12]?)__+([12]?)/);
				console.log(m);
			}
		}
	}

	findKnowns() {
		this.findKnownsFromAdjacentThrees();
		this.findKnownsFromFulls();
		this.findKnownsFromThreeOpens();
		this.findKnownsFromUniqueLines();
	}

	getLineRow(y) {
		return this.grid[y].map(val => val == 0 ? '_' : val).join('');
	}

	getLineCol(x) {
		return this.grid.map(cells => cells[x]).map(val => val == 0 ? '_' : val).join('');
	}

	getLineRows() {
		return this.grid.map((x, i) => this.getLineRow(i));
	}

	getLineCols() {
		return this.grid.map((x, i) => this.getLineCol(i));
	}

	isValidGrid() {
		for ( let i = 0; i < this.size; i++ ) {
			if ( !OhhiBuilder.isValidLineDistribution(this.getLineRow(i)) ) {
				return false;
			}

			if ( !OhhiBuilder.isValidLineDistribution(this.getLineCol(i)) ) {
				return false;
			}
		}

		const rows = this.getLineRows();
		if ( rows.unique().length != rows.length ) {
			return false;
		}

		const cols = this.getLineCols();
		if ( cols.unique().length != cols.length ) {
			return false;
		}

		return true;
	}

}
