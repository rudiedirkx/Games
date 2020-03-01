"use strict";

class Ohhi extends GridGame {

	constructor( gridElement ) {
		super(gridElement);

		this.checker = 0;
	}

	handleCellClick( cell ) {
		if ( cell.dataset.initial != null ) return;

		const curColor = cell.dataset.color;
		if (curColor === 'on') {
			cell.dataset.color = 'off';
		}
		else if (curColor === 'off') {
			delete cell.dataset.color;
		}
		else {
			cell.dataset.color = 'on';
		}

		clearTimeout(this.checker);
		this.checker = setTimeout(() => this.checkValid(), 500);
	}

	checkValid() {
		const unset = this.m_objGrid.getElements('td:not([data-color])');
		if ( unset.length > 0 ) {
			return;
		}

		const grid = this.m_objGrid.getElements('tr').map(tr => {
			return tr.getElements('td').map(td => td.dataset.color === 'on');
		});
		if ( !this.isValidGrid(grid) ) {
			return;
		}

		alert('You win!');
	}

	createFromExport(chars) {
		const size = Math.sqrt(chars.length);
		if (!chars.match(/[_0123]+/) || Math.ceil(size) != Math.floor(size)) {
			return false;
		}

		const grid = [];
		for ( let i = 0; i < chars.length; i++ ) {
			const C = chars[i];
			if (!grid[0] || grid[grid.length-1].length == size) {
				grid.push([]);
			}
			grid[grid.length-1].push(C === '_' ? null : (parseInt(C) & 1) == 1);
		}

		this.m_objGrid.setHTML(this.createMapHtml(grid, false));
		const cells = this.m_objGrid.getElements('td');
		for ( let i = 0; i < chars.length; i++ ) {
			const val = parseInt(chars[i]);
			if (!isNaN(val) && (val & 2) == 2) {
				cells[i].dataset.initial = '';
			}
		}

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

	createMap( size ) {
		const grid = (new Array(size)).fill(0).map(row => (new Array(size)).fill(null));

		console.time('createMap');

		for ( let row = 0; row < size; row++ ) {
			for ( let col = 0; col < size; col++ ) {
				const p2row = this.getPrevTwoInRow(grid, col, row);
				const p2col = this.getPrevTwoInCol(grid, col, row);

				if ( p2row == '11' ) {
					if ( p2col == '00' ) {
						// console.log('restart grid at', col, row);
						this.restartGrid(grid);
						row = -1;
						col = 0;
						break;
					}
					else {
						grid[row][col] = false;
					}
				}
				else if ( p2row == '00' ) {
					if ( p2col == '11' ) {
						// console.log('restart grid at', col, row);
						this.restartGrid(grid);
						row = -1;
						col = 0;
						break;
					}
					else {
						grid[row][col] = true;
					}
				}
				else {
					if ( p2col == '11' ) {
						grid[row][col] = false;
					}
					else if ( p2col == '00' ) {
						grid[row][col] = true;
					}
					else {
						grid[row][col] = this.makeOneRandom();
					}
				}

				if ( row == size - 1 ) {
					// const line = grid.map(cells => cells[col]);
					const line = this.getLineCol(grid, col);
					if ( !this.isValidLineDistribution(line) ) {
						// console.log('restart col/grid at', col);
						row = -1;
						col = 0;
						break;
					}
				}

				if ( row == size - 1 && col == size - 1 ) {
					// const rows = grid.map(cells => cells.join(''));
					const rows = this.getLineRows(grid);
					if ( rows.unique().length != rows.length ) {
						// console.log('restart grid (rows)');
						row = -1;
						col = 0;
						break;
					}

					// const cols = grid.map((x, col) => grid.map(row => row[col]).join(''));
					const cols = this.getLineCols(grid);
					if ( cols.unique().length != cols.length ) {
						// console.log('restart grid (cols)');
						row = -1;
						col = 0;
						break;
					}
				}
			}

			if ( row != -1 ) {
				// const line = grid[row];
				const line = this.getLineRow(grid, row);
				if ( !this.isValidLineDistribution(line) ) {
					// console.log('restart row at', row);
					this.restartRow(grid[row]);
					row--;
				}
			}
		}

		console.timeEnd('createMap');

		// this.m_objGrid.setHTML(this.createMapHtml(grid));

		console.time('make playable');
		var playableGrid;
		while (!this.isPlayable(playableGrid = this.hideCellsPlayable(grid))) {
			// redo
		}
		console.timeEnd('make playable');

		this.m_objGrid.setHTML(this.createMapHtml(playableGrid));
	}

	debugGrid(grid) {
		console.log(grid.map(row => row.map(val => val === null ? '_' : Number(val)).join(' ')).join("\n"));
	}

	isPlayable(grid) {
		const solver = new OhhiSolver(grid);
		while (true) {
			const founds = Array.from(solver.findMustBes());
			for (let found of founds) {
				solver.grid[found.y][found.x] = Number(found.color);
			}

			if (!founds.length) {
				return solver.isPlayable();
			}
		}

		return true;
	}

	hideCellsPlayable(grid) {
		return grid.map(cells => cells.map(value => Math.random() < 0.3 ? value : null));
	}

	restartRow(cells) {
		cells.fill(null);
	}

	restartGrid(grid) {
		grid.forEach(cells => cells.fill(null));
	}

	isValidGrid(grid) {
		for ( let i = 0; i < grid.length; i++ ) {
			if ( !this.isValidLineDistribution(this.getLineRow(grid, i)) ) {
				return false;
			}

			if ( !this.isValidLineDistribution(this.getLineCol(grid, i)) ) {
				return false;
			}
		}

		const rows = this.getLineRows(grid);
		if ( rows.unique().length != rows.length ) {
			return false;
		}

		const cols = this.getLineCols(grid);
		if ( cols.unique().length != cols.length ) {
			return false;
		}

		return true;
	}

	isValidLineDistribution(line) {
		// return line.map(n => Number(n)).join('').replace(/0/g, '').length == line.length / 2;
		if ( line.replace(/0/g, '').length != line.length / 2 ) {
			return false;
		}

		if ( line.match(/(000|111)/) ) {
			return false;
		}

		return true;
	}

	getLineRow(grid, y) {
		return grid[y].map(val => Number(val)).join('');
	}

	getLineRows(grid) {
		return grid.map((x, i) => this.getLineRow(grid, i));
	}

	getLineCol(grid, x) {
		return grid.map(cells => cells[x]).map(val => Number(val)).join('');
	}

	getLineCols(grid) {
		return grid.map((x, i) => this.getLineCol(grid, i));
	}

	getOneAsNum(grid, x, y) {
		const val = (grid[y] || [])[x];
		return val == null ? null : Number(val);
	}

	getOneAsPrevString(grid, x, y) {
		const num = this.getOneAsNum(grid, x, y);
		return num === null ? '' : String(num);
	}

	getPrevTwoInRow(grid, x, y) {
		const p1 = this.getOneAsPrevString(grid, x-1, y);
		const p2 = this.getOneAsPrevString(grid, x-2, y);
		return p1 + p2;
	}

	getPrevTwoInCol(grid, x, y) {
		const p1 = this.getOneAsPrevString(grid, x, y-1);
		const p2 = this.getOneAsPrevString(grid, x, y-2);
		return p1 + p2;
	}

	makeOneRandom() {
		return Math.random() > 0.5;
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
		this.listenCellClick();

		$('#restart').on('click', e => {
			this.m_objGrid.getElements('td[data-color]:not([data-initial])').attr('data-color', null);
		});

		$('#newgame').on('click', e => {
			const size = this.m_objGrid.getElements('tr').length;
			requestIdleCallback(() => this.createMap(size));
		});

		$('#sizes').on('click', 'a[data-size]', e => {
			e.preventDefault();

			const size = Number(e.target.data('size'));
			requestIdleCallback(() => this.createMap(size));
		});

		$('#cheat').on('click', e => {
			this.cheatOneRound();
		});

		$('#export').on('click', e => {
			location.hash = this.exportCurrent();
		});
	}

	exportCurrent() {
		const cells = this.m_objGrid.getElements('td').map(td => td.dataset);
		const chars = cells.map(D => D.color == null ? '_' : Number(D.color == 'on') + (D.initial == null ? 0 : 2));
		return chars.join('');
	}

	cheatOneRound() {
		const solver = OhhiSolver.fromDom(this.m_objGrid);
		for (let found of solver.findMustBes()) {
			this.m_objGrid.rows[found.y].cells[found.x].dataset.color = found.color ? 'on' : 'off';
		}

		setTimeout(() => this.checkValid(), 50);
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
		this.grid = grid.map(cells => cells.map(val => val == null ? null : Number(val)));

		this.threeStarts = this.constructor.makeCoords(this.grid);
	}

	getUnknowns() {
		return this.threeStarts.map(C => this.grid[C.y][C.x]).filter(val => val == null).length;
	}

	isPlayable() {
		return this.getUnknowns() == 0;
	}

	findMustBe() {
		for (let end of this.findMustBes()) {
			return end;
		}
	}

	*findMustBes() {
		const seen = [];
		const haveSeen = (x, y) => {
			const C = `${x}-${y}`;
			if (!seen.includes(C)) {
				seen.push(C);
				return false;
			}
			return true;
		};

		for ( let C of this.threeStarts ) {
			const s = this.grid[C.y][C.x];

			if (C.x <= this.size - 3) {
				const r1 = this.grid[C.y][C.x+1];
				const r2 = this.grid[C.y][C.x+2];
				if (r1 != null && s == null && r1 == r2) {
					if (!haveSeen(C.x, C.y)) {
						yield this.coordWithColor(C.x, C.y, Number(!r1));
					}
				}
				if (s != null && s == r2 && r1 == null) {
					if (!haveSeen(C.x+1, C.y)) {
						yield this.coordWithColor(C.x+1, C.y, Number(!s));
					}
				}
				if (r1 != null && s == r1 && r2 == null) {
					if (!haveSeen(C.x+2, C.y)) {
						yield this.coordWithColor(C.x+2, C.y, Number(!r1));
					}
				}
			}

			if (C.y <= this.size - 3) {
				const b1 = this.grid[C.y+1][C.x];
				const b2 = this.grid[C.y+2][C.x];
				if (b1 != null && s == null && b1 == b2) {
					if (!haveSeen(C.x, C.y)) {
						yield this.coordWithColor(C.x, C.y, Number(!b1));
					}
				}
				if (s != null && s == b2 && b1 == null) {
					if (!haveSeen(C.x, C.y+1)) {
						yield this.coordWithColor(C.x, C.y+1, Number(!s));
					}
				}
				if (b1 != null && s == b1 && b2 == null) {
					if (!haveSeen(C.x, C.y+2)) {
						yield this.coordWithColor(C.x, C.y+2, Number(!b1));
					}
				}
			}
		}

		const rows = this.getLineRows();
		const cols = this.getLineCols();

		for ( let y = 0; y < this.size; y++ ) {
			const line = rows[y];
			const no0 = line.replace(/[0_]/g, '').length;
			const no1 = line.replace(/[1_]/g, '').length;
			if (no0 == this.size / 2 && no1 < no0) {
				const founds = Array.from(line).map((v, i) => v == '_' ? this.coordWithColor(i, y, 0) : null).filter(v => v != null);
				yield* founds;
			}
			if (no1 == this.size / 2 && no0 < no1) {
				const founds = Array.from(line).map((v, i) => v == '_' ? this.coordWithColor(i, y, 1) : null).filter(v => v != null);
				yield* founds;
			}
		}

		for ( let x = 0; x < this.size; x++ ) {
			const line = cols[x];
			const no0 = line.replace(/[0_]/g, '').length;
			const no1 = line.replace(/[1_]/g, '').length;
			if (no0 == this.size / 2 && no1 < no0) {
				const founds = Array.from(line).map((v, i) => v == '_' ? this.coordWithColor(x, i, 0) : null).filter(v => v != null);
				yield* founds;
			}
			if (no1 == this.size / 2 && no0 < no1) {
				const founds = Array.from(line).map((v, i) => v == '_' ? this.coordWithColor(x, i, 1) : null).filter(v => v != null);
				yield* founds;
			}
		}

		if (seen.length) {
			// Repeat previous tactics first
			return;
		}

		for ( let y = 0; y < rows.length; y++ ) {
			const line = rows[y];
			if (line.replace(/[01]/g, '').length == 2) {
				const re = new RegExp('^' + line.replace(/_/g, '([01])') + '$');
				for ( let y2 = 0; y2 < rows.length; y2++ ) {
					const match = rows[y2].match(re);
					if (match) {
						const x1 = line.indexOf('_');
						const x2 = line.indexOf('_', x1+1);
						yield this.coordWithColor(x1, y, Number(!Number(match[1])));
						yield this.coordWithColor(x2, y, Number(!Number(match[2])));
						break;
					}
				}
			}
		}

		for ( let x = 0; x < cols.length; x++ ) {
			const line = cols[x];
			if (line.replace(/[01]/g, '').length == 2) {
				const re = new RegExp('^' + line.replace(/_/g, '([01])') + '$');
				for ( let x2 = 0; x2 < cols.length; x2++ ) {
					const match = cols[x2].match(re);
					if (match) {
						const y1 = line.indexOf('_');
						const y2 = line.indexOf('_', y1+1);
						yield this.coordWithColor(x, y1, Number(!Number(match[1])));
						yield this.coordWithColor(x, y2, Number(!Number(match[2])));
						break;
					}
				}
			}
		}
	}

	coordWithColor(x, y, color) {
		const C = new Coords2D(x, y);
		C.color = color;
		return C;
	}

	getLineRow(y) {
		return this.grid[y].map(val => val == null ? '_' : val).join('');
	}

	getLineCol(x) {
		return this.grid.map(cells => cells[x]).map(val => val == null ? '_' : val).join('');
	}

	getLineRows() {
		return this.grid.map((x, i) => this.getLineRow(i));
	}

	getLineCols() {
		return this.grid.map((x, i) => this.getLineCol(i));
	}

}
