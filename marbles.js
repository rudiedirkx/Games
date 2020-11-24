class Marbles extends GridGame {

	reset() {
		super.reset();

		this.width = 8;
		this.height = 6;
		this.level = 0;
	}

	createMap( level ) {
		this.reset();

		this.level = level;
		$('#map-level').value = level;

		this.startTime();

		const grid = this.makeRandomGrid();
		this.fillGrid(grid);
	}

	makeRandomGrid() {
		const grid = [];
		for ( let x = 0; x < this.width; x++ ) {
			grid.push([]);
			for ( let y = 0; y < this.height; y++ ) {
				grid[x].push(this._random());
			}
		}

		return grid;
	}

	_random() {
		return 1 + parseInt(Math.random() * (this.level + 1));
	}

	fillGrid(grid) {
		this.m_objGrid.empty();

		for ( let x = 0; x < grid.length; x++ ) {
			const col = document.el('div', {"class": 'column'}).inject(this.m_objGrid);
			for ( let y = 0; y < grid[x].length; y++ ) {
				col.append(document.el('div', {"class": 'block'}).data('t', grid[x][y]));
			}
		}

		this.m_objGrid.data('original', JSON.stringify(grid));
	}

	extendNeighbours(source, neighbours) {
		if (neighbours.includes(source)) return neighbours;
		neighbours.push(source);

		const type = source.data('t');
		const x = 1 + source.parentNode.elementIndex();
		const y = 1 + source.elementIndex();

		const neighbourBlocks = $$(([
			`.column:nth-child(${x}) > [data-t="${type}"]:nth-child(${y-1})`,
			`.column:nth-child(${x}) > [data-t="${type}"]:nth-child(${y+1})`,
			`.column:nth-child(${x-1}) > [data-t="${type}"]:nth-child(${y})`,
			`.column:nth-child(${x+1}) > [data-t="${type}"]:nth-child(${y})`,
		]).join(', '));

		neighbourBlocks.forEach(block => this.extendNeighbours(block, neighbours));
		return neighbours;
	}

	handleCellClick(source) {
		var neighbours = [];
		this.extendNeighbours(source, neighbours);

		if (neighbours.length < 2) return;

		neighbours.invoke('remove');
		$$('.column:empty').invoke('remove');

		this.startWinCheck();
	}

	haveWon() {
		return this.m_objGrid.childElementCount == 0;
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.level,
		};
	}

	listenControls() {
		this.listenCellClick();

		$('#restart').on('click', e => {
			const grid = JSON.parse(this.m_objGrid.data('original'));
			this.fillGrid(grid);
		});

		$('#newgame').on('click', e => this.createMap(this.level));

		$('#map-level').on('change', e => this.createMap(parseInt(e.subject.value)));
	}

	listenCellClick() {
		this.m_objGrid.on('click', '.block', e => this.handleCellClick(e.subject));
	}

	createStats() {
	}

	setTime( time ) {
	}

	setMoves( moves ) {
	}

}
