class TicTacToe extends GridGame {
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
		this.m_objGrid.getElements('[data-chosen]').data('chosen', null);
	}

	choose( cell ) {
		cell.data('chosen', this.m_iTurn);

		this.setTurn(!this.m_iTurn);

		this.winOrLose();
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		console.log(cell);
		if ( !cell.data('chosen') ) {
			this.choose(cell);
		}
	}
}
