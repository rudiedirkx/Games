class Flood extends GridGame {

	reset() {
		super.reset();

		this.colors = 4;

		this.size = 0;
		this.cell1 = null;
		this.checker = 0;
	}

	createMap( size ) {
		this.reset();

		if (this.size != size) {
			this.resetGrid(size);
		}

		this.m_objGrid.getElements('td').forEach(cell => {
			cell.dataset.color = cell.dataset.original = parseInt(Math.random() * this.colors);
		});

		this.setMoves(0);
		this.startTime();
	}

	resetGrid(size) {
		this.size = size;

		this.m_objGrid.setHTML(`<tr>${`<td></td>`.repeat(this.size)}</tr>`.repeat(this.size));
		this.cell1 = this.m_objGrid.getElement('td');
	}

	_random() {
		return 1 + parseInt(Math.random() * (this.level + 1));
	}

	extendNeighbours(collection, cell, color) {
		const x = cell.cellIndex;
		const y = cell.parentNode.sectionRowIndex;
		const grid = cell.parentNode.parentNode;

		collection.push(cell);

		const dirs = [[0, -1], [1, 0], [0, 1], [-1, 0]];
		for (var i=0; i<dirs.length; i++) {
			var dir = dirs[i];
			if (grid.rows[y + dir[1]] && grid.rows[y + dir[1]].cells[x + dir[0]]) {
				var adj = grid.rows[y + dir[1]].cells[x + dir[0]];
				if (adj.dataset.color == cell.dataset.color || adj.dataset.color == color) {
					if (collection.indexOf(adj) == -1) {
						this.extendNeighbours(collection, adj, color);
					}
				}
			}
		}
	}

	handleCellClick(source) {
		if (this.m_bGameOver) {
			return this.createMap(this.size);
		}

		const color = source.dataset.color;
		if (this.cell1.dataset.color == color) return;

		this.setMoves(this.m_iMoves+1);

		const neighbours = [];
		this.extendNeighbours(neighbours, this.cell1, color);

		neighbours.forEach(cell => cell.dataset.color = color);

		clearTimeout(this.checker);
		this.checker = setTimeout(() => this.winOrLose(), 100);
	}

	haveWon() {
		return this.m_objGrid.getElements('td').map(el => el.dataset.color).unique().length == 1;
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.size,
		};
	}

	listenControls() {
		this.listenCellClick();

		$('#restart').on('click', e => {
			this.m_objGrid.getElements('td').forEach(cell => {
				cell.dataset.color = cell.dataset.original;
			});
		});

		$('#newgame').on('click', e => this.createMap(this.size));
	}

	getStatsDelimiter() {
		return ' | ';
	}

}
