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

		this.winner = null;
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

		this.startWinCheck();
	}

	getPlayerLabel(player) {
		return parseInt(player) ? 'o' : 'x';
	}

	haveLost() {
		return this.m_objGrid.getElements('td:not([data-chosen])').length == 0;
	}

	getLoseText() {
		return 'NOBODY WINS :-(';
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
			this.winner = this.getCell(winner[0]).data('chosen');
			winner.forEach(C => this.getCell(C).addClass('winner'));
			return true;
		}
	}

	getWinText() {
		return `${this.getPlayerLabel(this.winner)} WINS!`;
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
		return this.m_arrWinLines = coords;
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
