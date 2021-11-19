class KeerOpKeer extends GridGame {

	static CENTER = 7;
	static COLORS = ['g', 'y', 'b', 'p', 'o'];
	static JOKERS = 8;

}

class SoloKeerOpKeer extends KeerOpKeer {

	reset() {
		super.reset();

		this.DICE = 2;
		this.TURNS = 30;

		this.usedJokers = 0;
		this.turnColor = null;
		this.turnColors = [];
		this.turnNumber = null;
		this.turnNumbers = [];
		// this.turnChoices = [];
	}

	statTypes() {
		return {
			...super.statTypes(),
			moves: 'Round',
			jokers: 'Jokers',
			score: 'Score',
		};
	}

	createGame() {
		super.createGame();

		setTimeout(() => this.printJokers());
		setTimeout(() => this.printScore());
	}

	printJokers() {
		$('#stats-jokers').setText(`${KeerOpKeer.JOKERS - this.usedJokers} / ${KeerOpKeer.JOKERS}`);
	}

	printScore() {
		$('#stats-score').setText(this.getNumericScore());
	}

	useJoker() {
		this.usedJokers++;
		this.printJokers();
	}

	endGame() {
		this.m_bGameOver = true;
		this.stopTime();
		$('#dice').setHTML('');

		KeerOpKeer.saveScore(this.getScore());
	}

	getScore() {
		return {
			...super.getScore(),
			score: this.getNumericScore(),
		};
	}

	getNumericScore() {
		const cols = $$('[data-col][data-score].self').reduce((T, cell) => {
			return T + parseInt(cell.dataset.score);
		}, 0);
		const colors = KeerOpKeer.COLORS.filter(color => {
			return this.m_objGrid.getElements(`[data-color="${color}"]:not(.chosen)`).length == 0;
		}).length * 5;
		const jokers = KeerOpKeer.JOKERS - this.usedJokers;
		const stars = this.m_objGrid.getElements('[data-color].star:not(.chosen)').length;

		return cols + colors + jokers - stars;
	}

	currentTurnIsComplete() {
return true;
		if (this.m_iMoves == 0) return true;
	}

	finishTurn() {
		if ( this.turnColor == '?' ) this.useJoker();
		if ( this.turnNumber == '?' ) this.useJoker();

		this.m_objGrid.getElements('.choosing').removeClass('choosing').addClass('chosen');
		this.turnColor = this.turnNumber = null;
		this.turnColors.length = this.turnNumbers.length = 0;

		this.printScore();
		if ( this.m_iMoves == this.TURNS ) {
			this.endGame();
		}
	}

	nextTurn() {
		if ( this.m_bGameOver ) return;

		if ( this.m_iMoves ) this.finishTurn();
		if ( this.m_bGameOver ) return;

		this.setMoves(this.m_iMoves + 1);

		const html = [];

		for ( let i = 0; i < this.DICE; i++ ) {
			const c = KeerOpKeer.COLORS[this.randInt(KeerOpKeer.COLORS.length)] || '?';
			this.turnColors.push(c);
			html.push(`<span class="color" data-color="${c}">${c == '?' ? '?' : '&nbsp;'}</span>`);
		}

		for ( let i = 0; i < this.DICE; i++ ) {
			const n = this.randInt(5);
			this.turnNumbers.push(n);
			html.push(`<span class="number" data-number="${n == 0 ? '?' : n}">${n == 0 ? '?' : n}</span>`);
		}

		$('#dice').setHTML(html.join(' '));
	}

	randInt(max) {
		return parseInt(Math.random() * (max + 1));
	}

	gridClickAllowedCoord( C ) {
		if ( C.x == KeerOpKeer.CENTER ) return true;
		if ( this.getCell(C).hasClass('choosing') ) return true;

		const adj = Coords2D.dir4Coords.find(O => {
			const cell = this.getCell(O.add(C));
			return cell && cell.is('.choosing, .chosen');
		});
		return adj != null;
	}

	gridClickAllowedColor( cell ) {
		return this.turnColor == '?' || cell.data('color') == this.turnColor;
	}

	gridClickAllowedNumber( cell ) {
		if ( cell.hasClass('choosing') ) return true;

		const max = this.turnNumber == '?' ? 5 : this.turnNumber;
		return max > this.m_objGrid.getElements('.choosing').length;
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return;

		if ( !this.turnColor || !this.turnNumber ) return;
		if ( !this.gridClickAllowedCoord(this.getCoord(cell)) ) return;
		if ( !this.gridClickAllowedColor(cell) ) return;
		if ( !this.gridClickAllowedNumber(cell) ) return;
		if ( cell.hasClass('chosen') ) return;

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
	}

	handleColumnClick( index ) {
		const el = this.getTable().getElement(`tfoot tr:first-child [data-col="${index}"]`);
		el.toggleClass('self');
	}

	resetChoosing() {
		this.m_objGrid.getElements('.choosing').removeClass('choosing');
	}

	selectColor( el ) {
		if ( this.turnColor == el.dataset.color ) return;

		$$(`#dice > [data-color="${this.turnColor}"]`).removeClass('selected');
		this.turnColor = el.dataset.color;
		el.addClass('selected');
		this.resetChoosing();
	}

	selectNumber( el ) {
		if ( this.turnNumber == el.dataset.number ) return;

		$$(`#dice > [data-number="${this.turnNumber}"]`).removeClass('selected');
		this.turnNumber = el.dataset.number;
		el.addClass('selected');
		this.resetChoosing();
	}

	getTable() {
		return this.m_objGrid.closest('table');
	}

	listenControls() {
		this.listenCellClick();

		this.getTable().on('click', '[data-col]', e => {
			this.handleColumnClick(parseInt(e.subject.dataset.col));
		});

		$('#next-turn').on('click', e => {
			this.currentTurnIsComplete() && this.nextTurn();
		});

		$('#dice').on('click', '[data-color]', e => {
			this.selectColor(e.subject);
		});
		$('#dice').on('click', '[data-number]', e => {
			this.selectNumber(e.subject);
		});
	}

}
