class Ohhi extends GridGame {

	constructor( gridElement ) {
		super(gridElement);
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
					const line = grid.map(cells => cells[col]);
					if ( !this.isValidLineDistribution(line) ) {
						// console.log('restart col/grid at', col);
						row = -1;
						col = 0;
						break;
					}
				}

				if ( row == size - 1 && col == size - 1 ) {
					const rows = grid.map(cells => cells.join(''));
					if ( rows.unique().length != rows.length ) {
						// console.log('restart grid (rows)');
						row = -1;
						col = 0;
						break;
					}

					const cols = grid.map((x, col) => grid.map(row => row[col]).join(''));
					if ( cols.unique().length != cols.length ) {
						// console.log('restart grid (cols)');
						row = -1;
						col = 0;
						break;
					}
				}
			}

			if ( row != -1 ) {
				const line = grid[row];
				if ( !this.isValidLineDistribution(line) ) {
					// console.log('restart row at', row);
					this.restartRow(grid[row]);
					row--;
				}
			}
		}

		console.timeEnd('createMap');

		// this.debugGrid(grid);

		// @todo Hide 60 % of values

		const playableGrid = grid.map(cells => cells.map(value => Math.random() > 0.6 ? value : null))
		this.m_objGrid.setHTML(this.createMapHtml(playableGrid));
	}

	debugGrid(grid) {
		console.log(grid.map(row => row.map(val => val === null ? '_' : Number(val)).join(' ')).join("\n"));
	}

	restartRow(cells) {
		cells.fill(null);
	}

	restartGrid(grid) {
		grid.forEach(cells => cells.fill(null));
	}

	isValidLineDistribution(line) {
		return line.map(n => Number(n)).join('').replace(/0/g, '').length == line.length / 2;
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

	createMapHtml( grid ) {
		const size = grid.length;

		var html = '';
		html += '<table>';
		for ( let row = 0; row < size; row++ ) {
			html += '<tr>';
			for ( let col = 0; col < size; col++ ) {
				const n = grid[row][col];
				const attr = n === null ? '' : ` data-initial data-color="${n ? 'on' : 'off'}"`;
				html += `<td${attr}></td>`;
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
			this.createMap(size);
		});
	}

	createStats() {
	}

	setTime( time ) {
	}

	setMoves( moves ) {
	}

}
