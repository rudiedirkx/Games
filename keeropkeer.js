"use strict";

class KOKChallenge {
	won() {
		return false;
	}

	lost() {
		return false;
	}
}

class KOKChallengeColor extends KOKChallenge {
	constructor(color) {
		super();
		this.color = color;
	}

	get message() {
		const i = KeerOpKeer.COLORS.indexOf(this.color);
		return `Fill all of color ${KeerOpKeer.COLOR_NAMES[i]}`;
	}

	won(game) {
		return $(`.full-color.self[data-color="${this.color}"]`) != null;
	}
}

class KOKChallengeAnyColorWithoutColor extends KOKChallenge {
	constructor(color) {
		super();
		this.color = color;
	}

	get message() {
		const i = KeerOpKeer.COLORS.indexOf(this.color);
		return `Fill any color without using any ${KeerOpKeer.COLOR_NAMES[i]}`;
	}

	won(game) {
		return !this.lost(game) && $('.full-color.self') != null;
	}

	lost(game) {
		return game.m_objGrid.getElement(`.chosen[data-color="${this.color}"]`) != null;
	}
}

class KOKChallengeColumns extends KOKChallenge {
	constructor(columns) {
		super();
		this.columns = columns.sort((a, b) => a - b);
	}

	get names() {
		return $$(this.columns.map(i => `thead [data-col="${i}"]`).join(',')).map(el => el.getText());
	}

	get message() {
		return `Fill columns ${this.names.join(', ')}`;
	}

	won(game) {
		const cols = $$(this.columns.map(i => `.full-column[data-col="${i}"]`).join(','));
		const fulls = cols.filter(col => col.hasClass('self'));
		return fulls.length == this.columns.length;
	}
}

class KOKChallengeEnds extends KOKChallengeColumns {
	constructor() {
		super([0, 14]);
	}

	get message() {
		const nms = this.names;
		return `Fill the far left and far right columns (${nms[0]} & ${nms[1]})`;
	}
}

class KOKChallengeSide extends KOKChallengeColumns {
	constructor(side) {
		super(side ? [8, 9, 10, 11, 12, 13, 14] : [0, 1, 2, 3, 4, 5, 6]);
		this.side = side;
	}

	get message() {
		const nms = this.names;
		return `Fill the entire ${this.side ? 'right' : 'left'} of the center (${nms[0]} - ${nms[nms.length-1]})`;
	}
}

class KOKChallengeNoMistakes extends KOKChallenge {
	constructor(columns) {
		super();
		this.chosen = 0;
	}

	get message() {
		return `Make 0 mistakes/corrections in selection`;
	}

	lost(game) {
		const chosen = game.m_objGrid.getElements('.chosen, .choosing').length;
		if (chosen < this.chosen) return true;
		this.chosen = chosen;
	}
}

class KeerOpKeer extends GridGame {

	static BOARDS = [];

	static CENTER = 7;
	static COLORS = ['g', 'y', 'b', 'p', 'o'];
	static COLOR_NAMES = ['Green', 'Yellow', 'Blue', 'Pink', 'Orange'];
	static JOKERS = 8;

	gridClickAllowedCoord( C, choosing = true ) {
		if ( C.x == KeerOpKeer.CENTER ) return true;

		const sel = choosing ? '.chosen, .choosing' : '.chosen';
		const adj = Coords2D.dir4Coords.find(O => {
			const cell = this.getCell(O.add(C));
			return cell && cell.is(sel);
		});
		return adj != null;
	}

	gridClickAllowedColor( cell ) {
		const prev = this.m_objGrid.getElement('.choosing');
		if ( prev ) {
			return prev.dataset.color == cell.dataset.color;
		}

		return this.turnColor == '?' || cell.data('color') == this.turnColor;
	}

	gridClickAllowedNumber( cell ) {
		if ( cell.hasClass('choosing') ) return true;

		const max = this.turnNumber == 0 ? 5 : this.turnNumber;
		return max > this.m_objGrid.getElements('.choosing').length;
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return;

		if ( this.turnColor == null || this.turnNumber == null ) return;
		if ( !cell.hasClass('choosing') && !this.gridClickAllowedCoord(this.getCoord(cell)) ) return;
		if ( !this.gridClickAllowedColor(cell) ) return;
		if ( !this.gridClickAllowedNumber(cell) ) return;
		if ( cell.hasClass('chosen') ) return;

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
		// this.winOrLose();
		this.evalNextReady();
	}

	haveWon() {
		return this.challenge ? this.challenge.won(this) : false;
	}

	haveLost() {
		return this.challenge ? (this.m_bGameOver || this.challenge.lost(this)) : false;
	}

	evalNextReady() {
		const el = $('#next-turn');
		if (el) el.disabled = !this.currentTurnIsComplete();

		document.body.toggleClass('with-choosing', this.getChoosing().length > 0);
	}

	currentTurnIsComplete() {
		const choosing = this.m_objGrid.getElements('.choosing');

		// Verify number of choosing
		if ( choosing.length > 0 ) {
			const needJokers = Number(this.turnNumber == 0) + Number(this.turnColor == '?');
			if ( needJokers > KeerOpKeer.JOKERS - this.usedJokers ) {
				return false;
			}

			if ( this.turnNumber != 0 && choosing.length != this.turnNumber ) {
				return false;
			}

			// All choosing must be 1 group
			const group1 = this.expandChoosing(choosing[0]);
			if ( group1.length != choosing.length ) {
				return false;
			}

			// Must originate from allowed coord
			const alloweds = choosing.filter(cell => this.gridClickAllowedCoord(this.getCoord(cell), false));
			if ( !alloweds.length ) {
				return false;
			}
		}

		return true;
	}

	expandChoosing( start, all = [] ) {
		all.push(start);

		const C = this.getCoord(start);
		Coords2D.dir4Coords.forEach(O => {
			const adj = this.getCell(O.add(C));
			if ( adj && !all.includes(adj) && adj.hasClass('choosing') ) {
				this.expandChoosing(adj, all);
			}
		});

		return all;
	}

	getChoosing() {
		return this.m_objGrid.getElements('.choosing');
	}

	lockInChoosing() {
		return this.getChoosing().removeClass('choosing').addClass('chosen');
	}

	maybeConfirmWithoutSelection() {
		if (this.getChoosing().length == 0) {
			if (!confirm('Do you want to END YOUR TURN without choosing any fields?')) {
				return false;
			}
		}
		return true;
	}

	clearFulls() {
		$$('.full-column, .full-color').removeClass('other').removeClass('self');
	}

	evalFulls() {
		const columns = this.evalFullColumns();
		const colors = this.evalFullColors();
		return {columns, colors};
	}

	evalFullColumns(grid) {
		grid || (grid = this.m_objGrid);
		const table = grid.closest('table');

		const columns = [];
		for ( let n = 0, cols = KeerOpKeer.CENTER * 2; n <= cols; n++ ) {
			const cells = grid.getElements(`tr > :nth-child(${n+1}):not(.chosen)`);
			if ( cells.length == 0 ) {
				columns.push(n);
				const [self, other] = table.getElements(`.full-column:nth-child(${n+1})`);
				if (!self.hasClass('other')) self.addClass('self');
				else if (other) other.addClass('self');
			}
		}

		return columns;
	}

	evalFullColors() {
		const colors = [];
		KeerOpKeer.COLORS.forEach(color => {
			if ( this.m_objGrid.getElements(`[data-color="${color}"]:not(.chosen)`).length == 0 ) {
				colors.push(color);
				const [self, other] = $$(`.full-color[data-color="${color}"]`);
				if (!self.hasClass('other')) self.addClass('self');
				else if (other) other.addClass('self');
			}
		});

		return colors;
	}

	useJoker() {
		this.usedJokers++;
		this.printJokers();
	}

	printJokers() {
		$('#stats-jokers').setText(`${KeerOpKeer.JOKERS - this.usedJokers} / ${KeerOpKeer.JOKERS}`);
	}

	printScore() {
		$('#stats-score').setText(this.getNumericScore());
	}

	getNumericScore() {
		const table = this.m_objGrid.closest('table');
		const cols = table.getElements('.full-column.self').reduce((T, cell) => {
			return T + parseInt(cell.dataset.score);
		}, 0);
		const colors = $$('.full-color.self').reduce((T, cell) => {
			return T + parseInt(cell.dataset.score);
		}, 0);
		const jokers = KeerOpKeer.JOKERS - this.usedJokers;
		const stars = this.m_objGrid.getElements('[data-color].star:not(.chosen)').length * 2;

		return cols + colors + jokers - stars;
	}

	roll(button) {
		return new Promise(resolve => {
			button.disabled = true;
			let rolls = 12;
			const rollIter = () => {
				const colors = [];
				for ( let i = 0; i < this.DICE; i++ ) {
					const c = KeerOpKeer.COLORS[this.randInt(KeerOpKeer.COLORS.length)] || '?';
					colors.push(c);
				}

				const numbers = [];
				for ( let i = 0; i < this.DICE; i++ ) {
					const n = this.randInt(5);
					numbers.push(n);
				}

				this.printDice({colors, numbers});

				if (--rolls) {
					setTimeout(rollIter, 60);
				}
				else {
					this.selectUniques();
					button.disabled = true;
					setTimeout(() => button.disabled = false, 1000);
					resolve({colors, numbers});
				}
			};
			rollIter();
		});
	}

	printDice({colors, numbers, disabled}) {
		const dis = (type, i) => {
			return disabled && disabled[type] === i ? 'disabled' : '';
		};
		const html = [
			...colors.map((c, i) => `<span class="color ${dis('color', i)}" data-color="${c}"></span>`),
			...numbers.map((n, i) => `<span class="number ${dis('number', i)}" data-number="${n}"></span>`),
		];
		$('#dice').setHTML(html.join(' '));

		this.turnColor = this.turnNumber = null;
	}

	randInt(max) {
		return parseInt(Math.random() * (max + 1));
	}

	printBoard(board) {
		document.body.css('--color', board.color);
		document.body.css('--text', board.text || '#fff');
		$('meta[name="theme-color"]').prop('content', board.color);

		const html = [];
		board.map.forEach(line => {
			html.push('<tr>');
			[...line.replace(/\s+/g, '')].forEach((cell, x) => {
				const classes = [];
				if (this.isStar(cell)) classes.push('star');
				if (x == KeerOpKeer.CENTER) classes.push('center');
				html.push(`<td data-color="${cell.toLowerCase()}" class="${classes.join(' ')}"></td>`);
			});
			html.push('</tr>');
		});
		this.m_objGrid.setHTML(html.join(''));
	}

	isStar(cell) {
		return cell.toUpperCase() == cell;
	}

	hiliteCantSelect() {
	}

	resetChoosing() {
		this.m_objGrid.getElements('.choosing').removeClass('choosing');
		this.evalNextReady();
	}

	selectUniques() {
		const colors = $$('#dice > .color:not(.disabled)');
		if (colors.map(el => el.dataset.color).unique().length == 1) {
			this.selectColor(colors[0]);
		}
		const numbers = $$('#dice > .number:not(.disabled)');
		if (numbers.map(el => el.dataset.number).unique().length == 1) {
			this.selectNumber(numbers[0]);
		}
	}

	selectColor( el ) {
		if ( el.hasClass('selected') || el.hasClass('disabled') ) return;
		if ( el.dataset.color == '?' && this.usedJokers >= KeerOpKeer.JOKERS ) return;
		if ( !$('#next-turn') ) return this.hiliteCantSelect();

		$$(`#dice > [data-color="${this.turnColor}"]`).removeClass('selected');
		this.turnColor = el.dataset.color;
		el.addClass('selected');
		this.resetChoosing();
	}

	selectNumber( el ) {
		if ( el.hasClass('selected') || el.hasClass('disabled') ) return;
		if ( el.dataset.number == '0' && this.usedJokers >= KeerOpKeer.JOKERS ) return;
		if ( !$('#next-turn') ) return this.hiliteCantSelect();

		$$(`#dice > [data-number="${this.turnNumber}"]`).removeClass('selected');
		this.turnNumber = parseInt(el.dataset.number);
		el.addClass('selected');
		this.resetChoosing();
	}

	listenDice() {
		$('#dice').on('click', '[data-color]', e => {
			this.selectColor(e.subject);
		});
		$('#dice').on('click', '[data-number]', e => {
			this.selectNumber(e.subject);
		});
	}

}

class MultiKeerOpKeer extends KeerOpKeer {

	static STATUS_REQUEST_MS = 1100;

	reset() {
		super.reset();

		this.DICE = 3;
		this.usedJokers = 0;
		this.turnColor = null;
		this.turnNumber = null;

		this.lastConnection = null;
		this.lastStatus = null;
		this.ignoringStatusUpdate = false;
	}

	startGame(boardName, state, usedJokers, othersColumns, othersColors) {
		const board = KeerOpKeer.BOARDS[boardName];
		this.printBoard(board);

		const emptyBoard = $('#board').outerHTML;
		this.importOtherPlayersBoardState(emptyBoard);

		this.importBoardState(state);
		this.importFullColumns(othersColumns);
		this.importFullColors(othersColors);
		this.evalFulls();

		this.usedJokers = usedJokers;
		this.printJokers();
		this.printScore();

		this.startPollingStatus();
	}

	startPollingStatus() {
		var lastPoll = Date.now();
		const poll = () => {
			if (document.hidden) {
				setTimeout(poll, MultiKeerOpKeer.STATUS_REQUEST_MS);
			}
			else {
				const $status = $('#status');
				const hash = this.lastStatus ? $status.data('hash') : 'x';
				$.get(location.search + '&status=' + hash).on('done', (e, rsp) => {
					setTimeout(poll, MultiKeerOpKeer.STATUS_REQUEST_MS);
					lastPoll = Date.now();

					if (!rsp) {
						console.warn('Empty status response. No connection?');
						this.warnNoConnection();
						return;
					}
					this.resetNoConnection();

					if (!rsp.status) {
						console.warn('status rsp', rsp);
						return;
					}

					if (rsp.players) {
						this.updatePlayersFromStatus(rsp.players);
					}

					if (rsp.status !== hash) {
						if (!this.ignoringStatusUpdate) {
console.log('no reload, but update', rsp);
							this.updateFromStatus(rsp);
						}
						else {
if ($('#debug')) $('#debug').append("ignoring this status update\n");
							console.warn('ignoring this status update');
						}
					}
				});
			}
		};
		setTimeout(poll, MultiKeerOpKeer.STATUS_REQUEST_MS);
	}

	warnNoConnection() {
		const sec = this.lastConnection ? Math.round((Date.now() - this.lastConnection) / 1000) : 0;
		if (sec > 5) {
			$('#no-connection').show();
		}
	}

	resetNoConnection() {
		this.lastConnection = Date.now();
		$('#no-connection').hide();
	}

	ignoreStatusUpdate() {
		this.ignoringStatusUpdate = true;
		setTimeout(() => this.ignoringStatusUpdate = false, 100);
	}

	updatePlayersFromStatus(players) {
		players.forEach(plr => {
			const id = plr.id;
			const online = $(`#online-${id}`);
			if (online) online.setText(plr.online);
			const jokers = $(`#jokers-left-${id}`);
			if (jokers && plr.jokers_left != null) jokers.setText(plr.jokers_left);
			const score = $(`#score-${id}`);
			if (score && plr.score != null) score.setText(plr.score);
			const tr = $(`tr#plr-${id}`);
			if (tr) {
				if (plr.turn != null) tr.toggleClass('turn', plr.turn);
				if (plr.kickable != null) tr.toggleClass('kickable', plr.kickable);
				if (plr.kicked != null) tr.toggleClass('kicked', plr.kicked);
			}
			else {
				location.reload();
			}
			const table = $(`#board-${id} #grid`);
			if (table && plr.board) this.importBoardState(plr.board, table);
		});
	}

	updateFromStatus(status) {
		$('#status').data('hash', status.status);
		if (this.hasChanged(status, 'message')) {
			$('#status').setHTML(status.message);
		}

		if (status.round != null) {
			$('#stats-round').setText(status.round);
		}

		if (status.dice && status.dice.colors && status.dice.numbers) {
			if (this.hasChanged(status, 'dice')) {
				this.importDice(status.dice);
			}
		}
		else {
			$('#dice').setHTML('');
		}

		if (this.hasChanged(status, 'others_columns') || this.hasChanged(status, 'others_colors')) {
			this.clearFulls();
			this.importFullColumns(status.others_columns);
			this.importFullColors(status.others_colors);
			this.evalFulls();
		}

		if (status.full_colors) {
			r.each(status.full_colors, (num, color) => {
				const el = $(`#color-${color}-players`);
				if (el) el.setText(num);
			});
		}

		this.lastStatus = status;
	}

	hasChanged(status, key) {
		return status[key] && (!this.lastStatus || JSON.stringify(this.lastStatus[key]) != JSON.stringify(status[key]));
	}

	importDice(dice) {
		this.printDice(dice);
		if ($('#next-turn')) {
			this.selectUniques();
		}
	}

	importFullColumns(columns, container) {
		const cont = (container || this.m_objGrid).closest('table');
		columns.forEach(n => cont.getElement(`.full-column:nth-child(${n+1})`).addClass('other'));
	}

	importFullColors(colors) {
		colors.forEach(color => $(`.full-color[data-color="${color}"]`).addClass('other'));
	}

	hiliteCantSelect() {
		$('#status').addClass('hilite');
		setTimeout(() => $('#status').removeClass('hilite'), 1000);
	}

	importOtherPlayersBoardState(emptyBoard) {
		$$('.other-player-board').forEach(el => {
			el.setHTML(emptyBoard);
			el.getElement('tfoot').remove();
			const grid = el.getElement('#grid');
			// this.importBoardState(JSON.parse(el.dataset.board), grid);
			// this.importFullColumns(JSON.parse(el.dataset.columns), grid);
			// this.evalFullColumns(grid);
		});
	}

	importBoardState(state, container) {
		(container || this.m_objGrid).getElements('td').forEach((td, i) => {
			td.toggleClass('chosen', state[i] === 'x');
		});
	}

	exportBoardState() {
		return this.m_objGrid.getElements('td').map(td => td.is('.chosen, .choosing') ? 'x' : ' ').join('').trimRight();
	}

	handleRoll() {
		this.roll($('#roll')).then(dice => {
			$.post(location.search + '&roll=1', $.serialize(dice)).on('done', (e, rsp) => {
console.log('roll rsp', rsp);
				if (rsp.status) {
					this.ignoreStatusUpdate();
					this.updateFromStatus(rsp.status);
				}
				else location.reload();
			});
		});
	}

	handleEndTurn() {
		if (!this.maybeConfirmWithoutSelection()) return;

		$('#next-turn').disabled = true;

		const choosing = this.lockInChoosing().length;
		const fulls = this.evalFulls();

		const color = choosing ? this.turnColor : '';
		const number = choosing ? String(this.turnNumber) : '';

		if ( color === '?' ) this.useJoker();
		if ( number === '0' ) this.useJoker();

		this.printScore();

		const state = this.exportBoardState();
		const score = this.getNumericScore();

		const data = {state, score, color, number, fulls};

		this.turnColor = this.turnNumber = null;

		$.post(location.search + '&endturn=1', $.serialize(data)).on('done', (e, rsp) => {
console.log('end turn rsp', rsp);
			document.body.removeClass('with-choosing');
			if (rsp.status) {
				this.ignoreStatusUpdate();
				this.updateFromStatus(rsp.status);
				this.updatePlayersFromStatus(rsp.status.players);
			}
			else location.reload();
		});
	}

	handleKick(pid) {
		if (!confirm('Kick player forever?')) return;

		const data = {pid};
		$.post(location.search + '&kick=1', $.serialize(data)).on('done', (e, rsp) => {
console.log('kick rsp', rsp);
			location.reload();
		});
	}

	listenControls() {
		this.listenCellClick();
		this.listenDice();

		$('#status').on('click', '#roll', e => {
			if (e.subject.disabled === false) this.handleRoll();
		});
		$('#status').on('click', '#next-turn', e => {
			if (e.subject.disabled === false) this.handleEndTurn();
		});

		$$('[data-kick]').on('click', e => {
			const pid = e.target.data('kick');
			this.handleKick(pid);
		});
	}

	createStats() {
	}

}

class SoloKeerOpKeer extends KeerOpKeer {

	reset() {
		super.reset();

		this.board = null;
		this.DICE = 2;
		this.TURNS = 30;

		this.usedJokers = 0;
		this.turnColor = null;
		this.turnNumber = null;

		this.challenge = null;
	}

	statTypes() {
		return {
			...super.statTypes(),
			moves: 'Round',
			jokers: 'Jokers',
			score: 'Score',
		};
	}

	setMoves( f_iMoves ) {
		this.m_iMoves = f_iMoves;
		if ( this.m_iMoves > 0 ) {
			this.startTime();
		}

		$('#stats-moves').setText(`${this.m_iMoves} / ${this.TURNS}`);
	}

	startRandomDifferentGame() {
		const boards = Object.keys(KeerOpKeer.BOARDS).filter(board => board != this.board);
		const board = boards[parseInt(Math.random() * boards.length)];

		this.startGame(board);
	}

	startGame(boardName) {
		this.reset();
		this.printGameState();
		$('#dice').setHTML('');

		this.board = boardName;
		const board = KeerOpKeer.BOARDS[boardName];
		this.printBoard(board);

		$$('.full-color, .full-column').removeClass('self');
		this.printJokers();
		this.printScore();

		this.printChallenge();
	}

	makeRandomChallenge() {
		const types = [
			() => new KOKChallengeColor(KeerOpKeer.COLORS[this.randInt(KeerOpKeer.COLORS.length - 1)]),
			() => new KOKChallengeEnds(),
			() => new KOKChallengeSide(this.randInt(1)),
			() => {
				const cols = [];
				while (cols.length < 4) {
					const col = 1 + this.randInt(KeerOpKeer.CENTER * 2 - 2);
					cols.includes(col) || cols.push(col);
				}
				return new KOKChallengeColumns(cols);
			},
			() => new KOKChallengeAnyColorWithoutColor(KeerOpKeer.COLORS[this.randInt(KeerOpKeer.COLORS.length - 1)]),
			// () => new KOKChallengeNoMistakes(),
		];
		return types[this.randInt(types.length - 1)]();
	}

	printChallenge() {
		if (this.m_iMoves == 0 && !this.challenge) {
			$('#challenge').setHTML('CHALLENGE? <a href id="yes">YES</a>');
		}
		else if (this.challenge) {
			$('#challenge').setHTML(`CHALLENGE: ${this.challenge.message}`);
		}
		else {
			$('#challenge').setHTML('');
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

	getBoardIndex() {
		return Object.keys(KeerOpKeer.BOARDS).indexOf(this.board);
	}

	getScore() {
		if (this.challenge) return null;

		return {
			...super.getScore(),
			score: this.getNumericScore(),
			level: this.getBoardIndex(),
		};
	}

	finishTurn() {
		document.body.removeClass('with-choosing');

		const choosing = this.lockInChoosing();

		if ( this.turnColor === '?' && choosing.length ) this.useJoker();
		if ( this.turnNumber === 0 && choosing.length ) this.useJoker();

		this.evalFulls();

		this.turnColor = this.turnNumber = null;

		this.printScore();
		if ( this.m_iMoves == this.TURNS ) {
			this.endGame();
		}
	}

	handleEndTurn() {
		if ( this.m_bGameOver ) return;

		if ( this.m_iMoves ) {
			if (!this.maybeConfirmWithoutSelection()) return;

			this.finishTurn();
			this.winOrLose();
		}

		if ( this.m_bGameOver ) {
			this.printGameState();
			this.printChallenge();
			return;
		}

		this.setMoves(this.m_iMoves + 1);
		this.printGameState();
		this.printChallenge();

		this.roll($('#next-turn'));
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

		$('#challenge').on('click', '#yes', e => {
			e.preventDefault();
			this.challenge = this.makeRandomChallenge();
			this.printChallenge();
		});

		$$('a[data-board]').on('click', e => {
			e.preventDefault();
			this.startGame(e.subject.dataset.board);
		});
	}

}

class KeerOpKeerGroup {
	constructor(color, cells, stars = 0) {
		this.color = color;
		this.cells = cells;
		this.stars = stars;
	}

	getAvailables() {
		return this.cells.filter(cell => !cell.hasClass('chosen')).length;
	}

	// isFull() {
	// 	return this.getAvailables() == 0;
	// }

	hilite() {
		new Elements(this.cells).addClass('hilite');
	}

	unhilite() {
		new Elements(this.cells).removeClass('hilite');
	}
}

class KeerOpKeerCheater {
	static PERFECT = 3;
	static IMPERFECT = 2;
	static POSSIBLE = 1;
	static IMPOSSIBLE = 0;

	constructor(grid) {
		this.m_objGrid = grid;

		this.groups = this.makeGroups();
	}

	findGroups(colors, numbers) {
		const matches = [
			[], [], [], [],
		];

		this.groups.forEach(group => {
			if (this.groupIsAccessible(group)) {
				const match = this.matchGroup(group, colors, numbers);
				if (match) {
					matches[match].push(group);
				}
			}
		});

		const i = matches.findLastIndex(groups => groups.length);
// console.log('match type', i);
		return matches[i] || [];
	}

	matchGroup(group, colors, numbers) {
		const av = group.getAvailables();
		const sn = Math.max(1, Math.min(...numbers));

		const perfectColor = colors.includes(group.color);
		const imperfectColor = colors.includes('?');
		const imperfectNumber = av >= sn;
		const perfectNumber = av && numbers.includes(av) || (imperfectNumber && av == 6);

		if (perfectColor && perfectNumber) return KeerOpKeerCheater.PERFECT;
		if (imperfectColor && perfectNumber) return KeerOpKeerCheater.IMPERFECT;
		if (perfectColor && imperfectNumber) return KeerOpKeerCheater.IMPERFECT;
		if (imperfectColor && imperfectNumber) return KeerOpKeerCheater.POSSIBLE;
		return KeerOpKeerCheater.IMPOSSIBLE;
	}

	groupIsAccessible(group) {
		return group.cells.some(cell => {
			if (this.getCoord(cell).x == KeerOpKeer.CENTER) return true;
			const C = this.getCoord(cell);
			return Coords2D.dir4Coords.some(O => {
				const nb = this.getCell(C.add(O));
				return nb && nb.hasClass('chosen');
			});
		});
	}

	makeGroups() {
		let cells = this.m_objGrid.getElements('td'); // .map(el => this.getCoord(el));
		const total = cells.length;
		let found = 0;
		const groups = [];
		while (found < total) {
			const group = this.expandGroup(cells[0]);
			found += group.length;
			groups.push(new KeerOpKeerGroup(group[0].dataset.color, group));
			cells = cells.filter(cell => !group.includes(cell));
		}
		return groups;
	}

	expandGroup(start, group = []) {
		group.push(start);

		const C = this.getCoord(start);
		Coords2D.dir4Coords.forEach(O => {
			const nb = this.getCell(C.add(O));
			if (nb && nb.dataset.color == group[0].dataset.color && !group.includes(nb)) {
				this.expandGroup(nb, group);
			}
		});

		return group;
	}

	static getRolledColors() {
		const els = $$('#dice .color');
		const values = [];
		if (els.length != 2) return values;
		values.push(els[0].dataset.color);
		values.push(els[1].dataset.color);
		return values;
	}

	static getRolledNumbers() {
		const els = $$('#dice .number');
		const values = [];
		if (els.length != 2) return values;
		values.push(parseInt(els[0].dataset.number));
		values.push(parseInt(els[1].dataset.number));
		return values;
	}
}

KeerOpKeerCheater.prototype.getCell = GridGame.prototype.getCell;
KeerOpKeerCheater.prototype.getCoord = GridGame.prototype.getCoord;
