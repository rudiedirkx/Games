"use strict";

class KeerOpKeer2 extends KeerOpKeer {

	static SPECIAL_FACES = ['bomb', 'bomb', 'stars', 'heart', 'die', 'joker'];
	static SPECIAL_SYMBOLS = {bomb: '💣', stars: '⭐', heart: '❤️', die: '⚅', joker: '🃏'};
	static SPECIAL_TITLES = {
		bomb: 'Bomb - cross any 4 fields',
		stars: 'Stars - cross any 2 star fields',
		heart: 'Heart - mark a heart (no selection)',
		die: 'Bonus die - mark a bonus die, worth 2 points (no selection)',
		joker: 'Joker - cross 1 free field',
	};

	static BOMB_CELLS = 4;
	static STAR_CELLS = 2;
	static JOKER_CELLS = 1;

	static ROW_SCORES = [];
	static HEART_SCORES = [];
	static GOLDEN_SCORE = 2;

	printBoard(board) {
		document.body.css('--color', board.color);
		document.body.css('--text', board.text || '#fff');
		$('meta[name="theme-color"]').prop('content', board.color);

		const html = [];
		board.map.forEach((line, y) => {
			html.push('<tr>');
			[...line.replace(/\s+/g, '')].forEach((cell, x) => {
				const classes = [];
				if (this.isStar(cell)) classes.push('star');
				if (x == KeerOpKeer.CENTER) classes.push('center');
				html.push(`<td data-color="${cell.toLowerCase()}" class="${classes.join(' ')}"></td>`);
			});
			const score = KeerOpKeer2.ROW_SCORES[y] ?? '';
			html.push(`<td class="full-row" data-row="${y}" data-score="${score}">${score}</td>`);
			html.push('</tr>');
		});
		this.m_objGrid.setHTML(html.join(''));
	}

	roll(button) {
		return new Promise(resolve => {
			button.disabled = true;
			let rolls = 12;
			const rollIter = () => {
				const colors = [];
				for ( let i = 0; i < this.DICE; i++ ) {
					colors.push(KeerOpKeer.COLORS[this.randInt(KeerOpKeer.COLORS.length)] || '?');
				}

				const numbers = [];
				for ( let i = 0; i < this.DICE; i++ ) {
					numbers.push(this.randInt(5));
				}

				const special = KeerOpKeer2.SPECIAL_FACES[this.randInt(KeerOpKeer2.SPECIAL_FACES.length - 1)];

				this.printDice({colors, numbers, special});

				if (--rolls) {
					setTimeout(rollIter, 60);
				}
				else {
					this.selectUniques();
					button.disabled = true;
					setTimeout(() => button.disabled = false, 1000);
					resolve({colors, numbers, special});
				}
			};
			rollIter();
		});
	}

	printDice({colors, numbers, special, disabled}) {
		super.printDice({colors, numbers, disabled});

		if (special) {
			const el = document.el('span', {class: 'special', 'data-special': special});
			el.setText(KeerOpKeer2.SPECIAL_SYMBOLS[special]);
			$('#dice').append(el);
		}

		this.turnSpecial = null;
		this.printSpecialInfo();
	}

	listenDice() {
		super.listenDice();
		$('#dice').on('click', '[data-special]', e => {
			this.selectSpecial(e.subject);
		});
	}

	selectSpecial(el) {
		if ( el.hasClass('disabled') ) return;
		if ( !$('#next-turn') ) return this.hiliteCantSelect();

		if ( el.hasClass('selected') ) {
			el.removeClass('selected');
			this.turnSpecial = null;
			this.printSpecialInfo();
			this.resetChoosing();
			return;
		}

		$$('#dice > .selected').removeClass('selected');
		this.turnColor = null;
		this.turnNumber = null;
		this.turnSpecial = el.dataset.special;
		el.addClass('selected');
		this.printSpecialInfo();
		this.resetChoosing();
	}

	selectColor(el) {
		this.clearSpecial();
		super.selectColor(el);
	}

	selectNumber(el) {
		this.clearSpecial();
		super.selectNumber(el);
	}

	clearSpecial() {
		this.turnSpecial = null;
		const el = $('#dice > .special.selected');
		if (el) el.removeClass('selected');
		this.printSpecialInfo();
	}

	printSpecialInfo() {
		const el = $('#special-info');
		if (el) el.setText(this.turnSpecial ? KeerOpKeer2.SPECIAL_TITLES[this.turnSpecial] : '');
	}

	handleCellClick(cell) {
		if ( this.m_bGameOver ) return;
		if ( cell.hasClass('full-row') ) return;
		if ( this.turnSpecial ) return this.handleSpecialCellClick(cell);
		return super.handleCellClick(cell);
	}

	handleSpecialCellClick(cell) {
		const face = this.turnSpecial;
		if ( face == 'heart' || face == 'die' ) return;
		if ( cell.hasClass('chosen') ) return;

		if ( !cell.hasClass('choosing') ) {
			const chosen = this.getChoosing();
			if ( face == 'bomb' ) {
				if ( chosen.length >= KeerOpKeer2.BOMB_CELLS ) return;
			}
			else if ( face == 'stars' ) {
				if ( !cell.hasClass('star') ) return;
				if ( chosen.length >= KeerOpKeer2.STAR_CELLS ) return;
			}
			else if ( face == 'joker' ) {
				if ( chosen.length >= KeerOpKeer2.JOKER_CELLS ) return;
				if ( chosen.length && chosen[0].dataset.color != cell.dataset.color ) return;
				if ( !this.gridClickAllowedCoord(this.getCoord(cell)) ) return;
			}
		}

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
		this.evalNextReady();
	}

	currentTurnIsComplete() {
		if ( this.turnSpecial ) {
			const face = this.turnSpecial;
			if ( face == 'heart' || face == 'die' ) return true;

			const chosen = this.getChoosing();
			if ( chosen.length == 0 ) return true;

			if ( face == 'bomb' ) return chosen.length == KeerOpKeer2.BOMB_CELLS;
			if ( face == 'stars' ) return chosen.length == KeerOpKeer2.STAR_CELLS && chosen.every(c => c.hasClass('star'));
			if ( face == 'joker' ) {
				if ( chosen.length != KeerOpKeer2.JOKER_CELLS ) return false;
				return chosen.some(c => this.gridClickAllowedCoord(this.getCoord(c), false));
			}
			return true;
		}

		return super.currentTurnIsComplete();
	}

	evalNextReady() {
		const el = $('#next-turn');
		if (el) el.disabled = !this.currentTurnIsComplete();

		const active = this.getChoosing().length > 0 || this.turnSpecial == 'heart' || this.turnSpecial == 'die';
		document.body.toggleClass('with-choosing', active);
	}

	maybeConfirmWithoutSelection() {
		if ( this.turnSpecial == 'heart' || this.turnSpecial == 'die' ) return true;
		return super.maybeConfirmWithoutSelection();
	}

	evalFulls() {
		const columns = this.evalFullColumns();
		const colors = this.evalFullColors();
		const rows = this.evalFullRows();
		return {columns, colors, rows};
	}

	evalFullRows() {
		const rows = [];
		this.m_objGrid.getElements('tr').forEach((tr, y) => {
			if ( tr.getElements('td:not(.chosen):not(.full-row)').length == 0 ) {
				rows.push(y);
				const el = $(`.full-row[data-row="${y}"]`);
				if (el) el.addClass('self');
			}
		});
		return rows;
	}

	addHeart() {
		if ( this.hearts < KeerOpKeer2.HEART_SCORES.length ) {
			this.hearts++;
			this.printHearts();
		}
	}

	printHearts() {
		$$('#hearts > .heart').forEach((el, i) => el.toggleClass('marked', i < this.hearts));
	}

	addGolden() {
		if ( this.goldenDice < $$('#golden > .golden').length ) {
			this.goldenDice++;
			this.printGolden();
		}
	}

	printGolden() {
		$$('#golden > .golden').forEach((el, i) => el.toggleClass('marked', i < this.goldenDice));
	}

	getNumericScore() {
		const base = super.getNumericScore();
		const rows = $$('.full-row.self').reduce((T, c) => T + parseInt(c.dataset.score || 0), 0);
		const hearts = KeerOpKeer2.HEART_SCORES.slice(0, this.hearts).reduce((a, b) => a + b, 0);
		const golden = this.goldenDice * KeerOpKeer2.GOLDEN_SCORE;
		return base + rows + hearts + golden;
	}

}

class SoloKeerOpKeer2 extends KeerOpKeer2 {

	reset() {
		this.DICE = 2;
		this.TURNS = 30;

		super.reset();

		this.board = null;
		this.hearts = 0;
		this.goldenDice = 0;

		this.usedJokers = 0;
		this.turnColor = null;
		this.turnNumber = null;
		this.turnSpecial = null;
	}

	statTypes() {
		return {
			...super.statTypes(),
			moves: 'Round',
			jokers: 'Jokers',
			score: 'Score',
		};
	}

	setMoves(f_iMoves) {
		this.m_iMoves = f_iMoves;
		if ( this.m_iMoves > 0 ) {
			this.startTime();
		}

		$('#stats-moves').setText(`${this.m_iMoves} / ${this.TURNS}`);
	}

	startGame(boardName) {
		this.reset();
		this.printGameState();
		$('#dice').setHTML('');
		this.printSpecialInfo();

		this.board = boardName;
		const board = KeerOpKeer.BOARDS[boardName];
		this.printBoard(board);

		$$('.full-color, .full-column, .full-row').removeClass('self');
		this.printJokers();
		this.printHearts();
		this.printGolden();
		this.printScore();
	}

	startRandomDifferentGame() {
		const boards = Object.keys(KeerOpKeer.BOARDS).filter(board => board != this.board);
		const board = boards[parseInt(Math.random() * boards.length)];
		this.startGame(board);
	}

	getBoardIndex() {
		return Object.keys(KeerOpKeer.BOARDS).indexOf(this.board);
	}

	getScore() {
		return {
			...super.getScore(),
			score: this.getNumericScore(),
			level: this.getBoardIndex(),
		};
	}

	handleEndTurn() {
		if ( this.m_bGameOver ) return;

		if ( this.m_iMoves ) {
			if (!this.maybeConfirmWithoutSelection()) return;

			this.finishTurn();
		}

		if ( this.m_bGameOver ) {
			this.printGameState();
			return;
		}

		this.setMoves(this.m_iMoves + 1);
		this.printGameState();

		this.roll($('#next-turn'));
	}

	finishTurn() {
		document.body.removeClass('with-choosing');

		const face = this.turnSpecial;
		const choosing = this.lockInChoosing();

		if ( face == 'heart' ) this.addHeart();
		else if ( face == 'die' ) this.addGolden();
		else if ( !face && choosing.length ) {
			if ( this.turnColor === '?' ) this.useJoker();
			if ( this.turnNumber === 0 ) this.useJoker();
		}

		this.evalFulls();

		this.turnColor = null;
		this.turnNumber = null;
		this.turnSpecial = null;

		$$('#dice .selected').removeClass('selected');
		this.printSpecialInfo();

		this.printScore();
		if ( this.m_iMoves == this.TURNS ) {
			this.endGame();
		}
	}

	endGame() {
		this.m_bGameOver = true;
		this.stopTime();
		$('#dice').setHTML('');
		this.printGameState();

		KeerOpKeer.saveScore(this.getScore());
	}

	printGameState() {
		$('body').data('state', this.getGameState());
	}

	getGameState() {
		if (this.m_bGameOver) {
			return 'done';
		}
		else if (this.m_iMoves == 0) {
			return null;
		}
		else if (this.m_iMoves == this.TURNS) {
			return 'last';
		}
		else {
			return 'turn';
		}
	}

	listenControls() {
		this.listenCellClick();
		this.listenDice();

		$('#next-turn').on('click', e => {
			if ( this.m_bGameOver ) {
				this.startRandomDifferentGame();
			}
			else if ( this.currentTurnIsComplete() ) {
				this.handleEndTurn();
			}
		});

		$$('a[data-board]').on('click', e => {
			e.preventDefault();
			this.startGame(e.subject.dataset.board);
		});
	}

}
