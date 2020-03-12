class Marbles extends GridGame {

	reset() {
		super.reset();

		this.width = 8;
		this.height = 6;
		this.level = 0;
		this.checker = 0;
	}

	createMap( level ) {
		this.reset();

		this.level = level;

		this.startTime();

		this.m_objGrid.empty();
		for ( let x = 0; x < this.width; x++ ) {
			const col = document.el('div', {"class": 'column'}).data('x', x).inject(this.m_objGrid);
			for ( let y = 0; y < this.height; y++ ) {
				col.append(document.el('div', {"class": 'block'}).data('y', y).data('t', this._random()));
			}
		}
	}

	_random() {
		return 1 + parseInt(Math.random() * (this.level + 1));
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

		clearTimeout(this.checker);
		this.checker = setTimeout(() => this.winOrLose(), 200);
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

		$('#newgame').on('click', e => this.createMap(this.level));
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
