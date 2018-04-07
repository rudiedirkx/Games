class TicTacToe extends GridGame {
	constructor( grid ) {
		super(grid);

		this.m_arrWinLines = [];
	}

	createGame() {
	}

	setTime() {
	}

	setMoves() {
	}

	reset() {
		super.reset();

		this.setTurn(0);
	}

	setTurn( turn ) {
		this.m_iTurn = Number(turn);

		$('#turn').data('chosen', this.m_iTurn);
	}

	startGame() {
		this.reset();

		this.setTurn(Math.random() > 0.5);
		this.m_objGrid.getElements('td').data('chosen', null).removeClass('winner');
	}

	choose( cell ) {
		cell.data('chosen', this.m_iTurn);

		this.setTurn(!this.m_iTurn);

		this.winOrLose();
	}

	haveWon() {
		const lines = this.getWinLines();
		const winner = lines.find((coords) => {
			const chosens = coords.map((coord) => {
				return this.getCell(coord).data('chosen');
			}).join('');
			return chosens === '111' || chosens === '000';
		});
		if ( winner ) {
			new Elements(winner.map((coord) => this.getCell(coord))).addClass('winner');
			return true;
		}
	}

	getWinLines() {
		if ( this.m_arrWinLines.length ) {
			return this.m_arrWinLines;
		}

		const dirs = [
			new Coords2D(0, 1),
			new Coords2D(1, 0),
			new Coords2D(1, 1),
			new Coords2D(-1, 1),
		];
		const starts = this.m_objGrid.getElements([
			'#' + this.m_objGrid.idOrRnd() + ' > tr:first-child > td',
			'#' + this.m_objGrid.idOrRnd() + ' > tr > td:first-child'
		].join(', '));

		const lines = [].concat.apply([], starts.map((start) => {
			return dirs.map((dir) => {
				var current = start;
				const line = [current];
				while ( current = this.getCell(this.getCoord(current).add(dir)) ) {
					line.push(current);
				}
				return line;
			}).filter((line) => line.length == 3);
		}));
		const coords = lines.map((cells) => cells.map((cell) => this.getCoord(cell)));
		return coords;
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return this.startGame();

		if ( !cell.data('chosen') ) {
			this.choose(cell);
		}
	}
}
