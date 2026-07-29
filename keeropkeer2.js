"use strict";

class KeerOpKeer2 extends KeerOpKeer {

	static SPECIAL_FACES = ['heart', 'heart', 'bomb', 'stars', 'three', 'colorblock'];
	static SPECIAL_SYMBOLS = {
		heart: '❤️',
		bomb: '💣',
		stars: '⭐⭐',
		three: '<svg class="face" width="30" height="11" viewBox="0 0 40 14"><g fill="none" stroke="#333" stroke-width="2"><rect x="1" y="1" width="12" height="12"/><rect x="14" y="1" width="12" height="12"/><rect x="27" y="1" width="12" height="12"/></g></svg>',
		colorblock: '<svg class="face" width="21" height="30" viewBox="0 0 27 40"><g fill="none" stroke="#333" stroke-width="2"><rect x="1" y="1" width="12" height="12"/><rect x="14" y="1" width="12" height="12"/><rect x="1" y="14" width="12" height="12"/><rect x="14" y="14" width="12" height="12"/><rect x="1" y="27" width="12" height="12"/></g></svg>',
	};
	static SPECIAL_TITLES = {
		heart: 'Heart - circle a heart (raises your column bonus)',
		bomb: 'Bomb - cross up to 4 fields in a 2x2 block, any colour',
		stars: 'Two stars - cross up to 2 star fields',
		three: 'Three in a row - cross up to 3 fields in one row',
		colorblock: 'Colour block - cross one whole block of a single colour',
	};

	static ROW_REWARDS = [];
	static HEART_MAX = 5;
	static USES_MAX = 12;

	getCell(C) {
		return this.m_objGrid.getElement(`[data-x="${C.x}"][data-y="${C.y}"]`);
	}

	getCoord(cell) {
		return new Coords2D(parseInt(cell.dataset.x), parseInt(cell.dataset.y));
	}

	printBoard(board) {
		document.body.css('--color', board.color);
		document.body.css('--text', board.text || '#fff');
		$('meta[name="theme-color"]').prop('content', board.color);

		const gold = new Set((board.gold || []).map(g => g[0] + ',' + g[1]));
		const html = [];
		board.map.forEach((line, y) => {
			const cells = [...line.replace(/\s+/g, '')];
			const label = String.fromCharCode(80 + y);
			const reward = KeerOpKeer2.ROW_REWARDS[y] || '';

			html.push('<tr>');
			html.push(`<td class="row-score" data-row="${y}">5</td>`);
			html.push(`<td class="row-reward" data-reward="${reward}"></td>`);
			cells.forEach((ch, x) => {
				const classes = ['cell'];
				if (this.isStar(ch)) classes.push('star');
				if (gold.has(x + ',' + y)) classes.push('gold');
				if (x == KeerOpKeer.CENTER) classes.push('center');
				html.push(`<td class="${classes.join(' ')}" data-color="${ch.toLowerCase()}" data-x="${x}" data-y="${y}"></td>`);
			});
			html.push(`<td class="row-label">${label}</td>`);
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
			el.setHTML(KeerOpKeer2.SPECIAL_SYMBOLS[special]);
			if (this.availableUses() < 1) el.addClass('unavailable');
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
		if ( el.hasClass('unavailable') ) return;
		if ( !$('#next-turn') ) return this.hiliteCantSelect();
		if ( this.availableUses() < 1 ) return this.hiliteCantSelect();

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
		if (!el) return;
		if (this.placingBonusBomb) {
			el.setText(`Bonus bomb! Cross up to 4 fields in a 2x2 block (${this.bonusBombs} left).`);
			return;
		}
		el.setText(this.turnSpecial ? KeerOpKeer2.SPECIAL_TITLES[this.turnSpecial] : '');
	}

	handleCellClick(cell) {
		if ( this.m_bGameOver ) return;
		if ( !cell.hasClass('cell') ) return;
		if ( this.placingBonusBomb ) return this.handleBombClick(cell);
		if ( this.turnSpecial ) return this.handleSpecialCellClick(cell);
		return super.handleCellClick(cell);
	}

	handleSpecialCellClick(cell) {
		const face = this.turnSpecial;
		if ( face == 'heart' ) return;
		if ( face == 'bomb' ) return this.handleBombClick(cell);
		if ( face == 'stars' ) return this.handleStarsClick(cell);
		if ( face == 'three' ) return this.handleThreeClick(cell);
		if ( face == 'colorblock' ) return this.handleBlockClick(cell);
	}

	handleBombClick(cell) {
		if ( cell.hasClass('chosen') ) return;

		if ( !cell.hasClass('choosing') ) {
			const cells = [...this.getChoosing(), cell];
			if ( cells.length > 4 ) return;
			if ( !this.fitsIn2x2(cells) ) return;
		}

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
		this.evalNextReady();
	}

	handleStarsClick(cell) {
		if ( cell.hasClass('chosen') ) return;
		if ( !cell.hasClass('star') ) return;

		if ( !cell.hasClass('choosing') ) {
			if ( this.getChoosing().length >= 2 ) return;
			if ( !this.gridClickAllowedCoord(this.getCoord(cell)) ) return;
		}

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
		this.evalNextReady();
	}

	handleThreeClick(cell) {
		if ( cell.hasClass('chosen') ) return;

		if ( !cell.hasClass('choosing') ) {
			const chosen = this.getChoosing();
			if ( chosen.length >= 3 ) return;
			if ( chosen.length && parseInt(chosen[0].dataset.y) != parseInt(cell.dataset.y) ) return;
			if ( !this.gridClickAllowedCoord(this.getCoord(cell)) ) return;
		}

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
		this.evalNextReady();
	}

	handleBlockClick(cell) {
		const block = this.floodColor(cell);
		const touches = block.some(c => parseInt(c.dataset.x) == KeerOpKeer.CENTER || this.gridClickAllowedCoord(this.getCoord(c), false));

		this.resetChoosing();

		if ( !touches ) return this.hiliteCantSelect();

		block.forEach(c => {
			if ( !c.hasClass('chosen') ) c.addClass('choosing');
		});
		this.evalNextReady();
	}

	floodColor(start) {
		const color = start.dataset.color;
		const all = [start];
		const stack = [start];
		while ( stack.length ) {
			const c = stack.pop();
			const C = this.getCoord(c);
			Coords2D.dir4Coords.forEach(O => {
				const nb = this.getCell(O.add(C));
				if ( nb && nb.dataset.color == color && !all.includes(nb) ) {
					all.push(nb);
					stack.push(nb);
				}
			});
		}
		return all;
	}

	fitsIn2x2(cells) {
		const xs = cells.map(c => parseInt(c.dataset.x));
		const ys = cells.map(c => parseInt(c.dataset.y));
		const dx = Math.max(...xs) - Math.min(...xs);
		const dy = Math.max(...ys) - Math.min(...ys);
		return dx <= 1 && dy <= 1;
	}

	currentTurnIsComplete() {
		if ( this.placingBonusBomb ) {
			const chosen = this.getChoosing();
			return chosen.length >= 1 && chosen.length <= 4 && this.fitsIn2x2(chosen);
		}

		if ( this.turnSpecial ) {
			const face = this.turnSpecial;
			if ( face == 'heart' ) return true;

			const chosen = this.getChoosing();
			if ( chosen.length == 0 ) return false;

			if ( face == 'bomb' ) return chosen.length <= 4 && this.fitsIn2x2(chosen);
			if ( face == 'stars' ) return chosen.length <= 2 && chosen.every(c => c.hasClass('star'));
			if ( face == 'three' ) return chosen.length <= 3 && chosen.every(c => parseInt(c.dataset.y) == parseInt(chosen[0].dataset.y));
			if ( face == 'colorblock' ) return chosen.length >= 1;
			return true;
		}

		return super.currentTurnIsComplete();
	}

	evalNextReady() {
		const el = $('#next-turn');
		if (el) el.disabled = !this.currentTurnIsComplete();

		const active = this.getChoosing().length > 0 || this.turnSpecial == 'heart' || this.placingBonusBomb;
		document.body.toggleClass('with-choosing', active);
	}

	maybeConfirmWithoutSelection() {
		if ( this.turnSpecial == 'heart' ) return true;
		return super.maybeConfirmWithoutSelection();
	}

	availableUses() {
		return this.usesUnlocked - this.usesSpent;
	}

	unlockUses(n) {
		this.usesUnlocked = Math.min(this.usesUnlocked + n, KeerOpKeer2.USES_MAX);
		this.printUses();
	}

	circleHeart() {
		if ( this.hearts < KeerOpKeer2.HEART_MAX ) {
			this.hearts++;
			this.printHearts();
		}
	}

	detectUnlocks() {
		const golds = this.m_objGrid.getElements('td.gold.chosen:not(.counted)');
		golds.forEach(c => c.addClass('counted'));
		if ( golds.length ) this.unlockUses(golds.length);

		if ( !this.hCounted && this.columnFull(KeerOpKeer.CENTER) ) {
			this.hCounted = true;
			this.unlockUses(1);
		}
	}

	resolveCrossings() {
		this.detectUnlocks();
		this.resolveColors();
		this.resolveRows();
		this.resolveColumns();
	}

	columnFull(x) {
		const cells = this.m_objGrid.getElements(`[data-x="${x}"]`);
		return cells.length > 0 && cells.every(c => c.hasClass('chosen'));
	}

	rowFull(y) {
		const cells = this.m_objGrid.getElements(`[data-y="${y}"]`);
		return cells.length > 0 && cells.every(c => c.hasClass('chosen'));
	}

	resolveColumns() {
		for ( let x = 0; x <= KeerOpKeer.CENTER * 2; x++ ) {
			if ( this.completedCols.has(x) ) continue;
			if ( !this.columnFull(x) ) continue;

			this.completedCols.add(x);
			const score = $(`.full-column[data-col="${x}"]`);
			if (score) score.addClass('self');

			if ( this.hearts > 0 ) {
				this.heartBonus += this.hearts;
				const hb = $(`.col-heart[data-col="${x}"]`);
				if (hb) hb.setText(this.hearts);
			}
		}
	}

	resolveRows() {
		for ( let y = 0; y < 7; y++ ) {
			if ( this.completedRows.has(y) ) continue;
			if ( !this.rowFull(y) ) continue;

			this.completedRows.add(y);
			const score = $(`.row-score[data-row="${y}"]`);
			if (score) score.addClass('self');

			this.awardRowBonus(y);
		}
	}

	awardRowBonus(y) {
		const reward = KeerOpKeer2.ROW_REWARDS[y];
		if ( reward == 'die' ) this.unlockUses(1);
		else if ( reward == 'heart' ) this.circleHeart();
		else if ( reward == 'bomb' ) this.bonusBombs++;
	}

	resolveColors() {
		KeerOpKeer.COLORS.forEach(color => {
			if ( this.completedColors.has(color) ) return;

			const cells = this.m_objGrid.getElements(`[data-color="${color}"]`);
			if ( cells.length > 0 && cells.every(c => c.hasClass('chosen')) ) {
				this.completedColors.add(color);
				const score = $(`.full-color[data-color="${color}"]`);
				if (score) score.addClass('self');
			}
		});
	}

	getNumericScore() {
		let cols = 0;
		$$('.full-column.self').forEach(c => cols += parseInt(c.dataset.score));

		let colors = 0;
		$$('.full-color.self').forEach(c => colors += parseInt(c.dataset.score));

		const rows = this.completedRows.size * 5;
		const hearts = this.heartBonus;
		const jokers = KeerOpKeer.JOKERS - this.usedJokers;
		const uses = this.availableUses() * 2;
		const stars = this.m_objGrid.getElements('.star:not(.chosen)').length * 2;

		return cols + colors + rows + hearts + jokers + uses - stars;
	}

	printHearts() {
		$$('#hearts > .heart').forEach((el, i) => el.toggleClass('marked', i < this.hearts));
	}

	printUses() {
		$$('#uses > .use').forEach((el, i) => {
			el.toggleClass('unlocked', i < this.usesUnlocked);
			el.toggleClass('spent', i < this.usesSpent);
		});
	}

}

class SoloKeerOpKeer2 extends KeerOpKeer2 {

	reset() {
		this.DICE = 2;
		this.TURNS = 30;

		super.reset();

		this.board = null;
		this.hearts = 0;
		this.heartBonus = 0;
		this.usesUnlocked = 1;
		this.usesSpent = 0;
		this.usedJokers = 0;

		this.completedCols = new Set();
		this.completedRows = new Set();
		this.completedColors = new Set();
		this.hCounted = false;

		this.bonusBombs = 0;
		this.placingBonusBomb = false;

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

		$$('.full-color, .full-column, .row-score').removeClass('self');
		$$('.col-heart').setText('');
		this.printJokers();
		this.printHearts();
		this.printUses();
		this.printScore();
	}

	startRandomDifferentGame() {
		const boards = Object.keys(KeerOpKeer.BOARDS).filter(board => board != this.board);
		const board = boards.length ? boards[parseInt(Math.random() * boards.length)] : this.board;
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

		if ( this.placingBonusBomb ) return this.handleBonusBomb();

		if ( this.m_iMoves ) {
			if (!this.maybeConfirmWithoutSelection()) return;

			this.finishTurn();

			if ( this.bonusBombs > 0 ) return this.enterBonusBomb();
		}

		this.afterTurnComplete();
	}

	finishTurn() {
		document.body.removeClass('with-choosing');

		const face = this.turnSpecial;
		const choosing = this.lockInChoosing();

		if ( face ) {
			this.usesSpent++;
			if ( face == 'heart' ) this.circleHeart();
		}
		else if ( choosing.length ) {
			if ( this.turnColor === '?' ) this.useJoker();
			if ( this.turnNumber === 0 ) this.useJoker();
		}

		this.resolveCrossings();

		this.turnColor = null;
		this.turnNumber = null;
		this.turnSpecial = null;

		$$('#dice .selected').removeClass('selected');
		this.printSpecialInfo();
		this.printUses();
		this.printScore();
	}

	enterBonusBomb() {
		this.placingBonusBomb = true;
		this.promptBonusBomb();
	}

	promptBonusBomb() {
		this.resetChoosing();
		$('#dice').setHTML('');
		this.printSpecialInfo();
		this.evalNextReady();
	}

	handleBonusBomb() {
		if ( !this.currentTurnIsComplete() ) return;

		this.lockInChoosing();
		document.body.removeClass('with-choosing');

		this.resolveCrossings();
		this.bonusBombs--;

		this.printUses();
		this.printScore();

		if ( this.bonusBombs > 0 ) {
			this.promptBonusBomb();
			return;
		}

		this.placingBonusBomb = false;
		this.printSpecialInfo();
		this.afterTurnComplete();
	}

	afterTurnComplete() {
		if ( this.m_iMoves == this.TURNS ) {
			this.endGame();
			return;
		}

		this.setMoves(this.m_iMoves + 1);
		this.printGameState();
		this.roll($('#next-turn'));
	}

	endGame() {
		this.m_bGameOver = true;
		this.stopTime();
		$('#dice').setHTML('');
		this.printSpecialInfo();
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
