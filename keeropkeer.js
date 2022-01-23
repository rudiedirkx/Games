class KeerOpKeer extends GridGame {

	static BOARDS = [];

	static CENTER = 7;
	static COLORS = ['g', 'y', 'b', 'p', 'o'];
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

		const max = this.turnNumber == '?' ? 5 : this.turnNumber;
		return max > this.m_objGrid.getElements('.choosing').length;
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return;

		if ( !this.turnColor || !this.turnNumber ) return;
		if ( !cell.hasClass('choosing') && !this.gridClickAllowedCoord(this.getCoord(cell)) ) return;
		if ( !this.gridClickAllowedColor(cell) ) return;
		if ( !this.gridClickAllowedNumber(cell) ) return;
		if ( cell.hasClass('chosen') ) return;

		cell.toggleClass('choosing');
		cell.data('turn', cell.hasClass('choosing') ? this.m_iMoves : null);
		this.evalNextReady();
	}

	evalNextReady() {
		const el = $('#next-turn');
		if (el) el.disabled = !this.currentTurnIsComplete();
	}

	currentTurnIsComplete() {
		const choosing = this.m_objGrid.getElements('.choosing');

		// Verify number of choosing
		if ( choosing.length > 0 ) {
			const needJokers = Number(this.turnNumber == '?') + Number(this.turnColor == '?');
			if ( needJokers > this.JOKERS - this.useJoker ) {
				return false;
			}

			if ( this.turnNumber != '?' && choosing.length != this.turnNumber ) {
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

	lockInChoosing() {
		return this.m_objGrid.getElements('.choosing').removeClass('choosing').addClass('chosen');
	}

	evalFulls() {
		// Full columns
		const columns = [];
		for ( let n = 0, cols = KeerOpKeer.CENTER * 2; n <= cols; n++ ) {
			const cells = this.m_objGrid.getElements(`tr > :nth-child(${n+1}):not(.chosen)`);
			if ( cells.length == 0 ) {
				columns.push(n);
				const [self, other] = $$(`.full-column:nth-child(${n+1})`);
				if (!self.hasClass('other')) self.addClass('self');
				else if (other) other.addClass('self');
			}
		}

		// Full colors
		const colors = [];
		KeerOpKeer.COLORS.forEach(color => {
			if ( this.m_objGrid.getElements(`[data-color="${color}"]:not(.chosen)`).length == 0 ) {
				colors.push(color);
				const [self, other] = $$(`.full-color[data-color="${color}"]`);
				if (!self.hasClass('other')) self.addClass('self');
				else if (other) other.addClass('self');
			}
		});

		return {columns, colors};
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
		const cols = $$('.full-column.self').reduce((T, cell) => {
			return T + parseInt(cell.dataset.score);
		}, 0);
		const colors = $$('.full-color.self').reduce((T, cell) => {
			return T + parseInt(cell.dataset.score);
		}, 0);
		const jokers = KeerOpKeer.JOKERS - this.usedJokers;
		const stars = this.m_objGrid.getElements('[data-color].star:not(.chosen)').length;

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
					setTimeout(() => button.disabled = false, 1000);
					resolve({colors, numbers});
				}
			};
			rollIter();
		});
	}

	printDice({colors, numbers}) {
		const html = [
			...colors.map(c => `<span class="color" data-color="${c}">${c == '?' ? '?' : '&nbsp;'}</span>`),
			...numbers.map(n => `<span class="number" data-number="${n == 0 ? '?' : n}">${n == 0 ? '?' : n}</span>`),
		];
		$('#dice').setHTML(html.join(' '));
	}

	randInt(max) {
		return parseInt(Math.random() * (max + 1));
	}

	printBoard(board) {
		document.body.css('--color', board.color);

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

	resetChoosing() {
		this.m_objGrid.getElements('.choosing').removeClass('choosing');
		this.evalNextReady();
	}

	selectColor( el ) {
		if ( el.hasClass('selected') ) return;
		if ( el.dataset.color == '?' && this.usedJokers >= KeerOpKeer.JOKERS ) return;

		$$(`#dice > [data-color="${this.turnColor}"]`).removeClass('selected');
		this.turnColor = el.dataset.color;
		el.addClass('selected');
		this.resetChoosing();
	}

	selectNumber( el ) {
		if ( el.hasClass('selected') ) return;
		if ( el.dataset.number == '?' && this.usedJokers >= KeerOpKeer.JOKERS ) return;

		$$(`#dice > [data-number="${this.turnNumber}"]`).removeClass('selected');
		this.turnNumber = el.dataset.number == '?' ? '?' : parseInt(el.dataset.number);
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

	static STATUS_REQUEST_MS = 800;

	reset() {
		super.reset();

		this.DICE = 3;
		this.usedJokers = 0;
		this.turnColor = null;
		this.turnNumber = null;
	}

	startGame(boardName, state, usedJokers, othersColumns, othersColors) {
		const board = KeerOpKeer.BOARDS[boardName];
		this.printBoard(board);

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
		const poll = () => {
			if (!document.hidden) {
				$.get(location.search + '&status=1').on('done', (e, rsp) => {
console.log(rsp.status, rsp.debug);
					if (!rsp.status) {
						console.warn(rsp);
					}
					else if (rsp.status !== $('#status').data('hash')) {
						setTimeout(() => location.reload(), 100);
					}
				});
			}
		};
		setInterval(poll, MultiKeerOpKeer.STATUS_REQUEST_MS);
	}

	importFullColumns(columns) {
		columns.forEach(n => $(`.full-column:nth-child(${n+1})`).addClass('other'));
	}

	importFullColors(colors) {
		colors.forEach(color => $(`.full-color[data-color="${color}"]`).addClass('other'));
	}

	importBoardState(state) {
		this.m_objGrid.getElements('td').forEach((td, i) => {
			if (state[i] === 'x') {
				td.addClass('chosen');
			}
		});
	}

	exportBoardState() {
		return this.m_objGrid.getElements('td').map(td => td.is('.chosen, .choosing') ? 'x' : ' ').join('').trimRight();
	}

	handleRoll() {
		this.roll($('#roll')).then(dice => {
			$.post(location.search + '&roll=1', $.serialize(dice)).on('done', (e, rsp) => {
				console.log(rsp);
				if (rsp.reload) location.reload();
			});
		});
	}

	handleEndTurn() {
		const choosing = this.lockInChoosing().length;
		const fulls = this.evalFulls();

		const color = choosing ? this.turnColor : '';
		const number = choosing ? this.turnNumber : '';

		if ( color === '?' ) this.useJoker();
		if ( number === '?' ) this.useJoker();

		this.printScore();

		const state = this.exportBoardState();
		const score = this.getNumericScore();

		const data = {state, score, color, number, fulls};

		$.post(location.search + '&endturn=1', $.serialize(data)).on('done', (e, rsp) => {
			console.log(rsp);
			if (rsp.reload) location.reload();
		});
	}

	listenControls() {
		this.listenCellClick();
		this.listenDice();

		$$('#roll').on('click', e => {
			this.handleRoll();
		});
		$$('#next-turn').on('click', e => {
			this.handleEndTurn();
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

		this.board = boardName;
		const board = KeerOpKeer.BOARDS[boardName];
		this.printBoard(board);

		$$('.full-color, .full-column').removeClass('self');
		this.printJokers();
		this.printScore();
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
		return {
			...super.getScore(),
			score: this.getNumericScore(),
			level: this.getBoardIndex(),
		};
	}

	finishTurn() {
		const choosing = this.lockInChoosing();

		if ( this.turnColor == '?' && choosing.length ) this.useJoker();
		if ( this.turnNumber == '?' && choosing.length ) this.useJoker();

		this.evalFulls();

		this.turnColor = this.turnNumber = null;

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
		this.printGameState();

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
				this.nextTurn();
			}
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
		values.push(parseInt(els[0].dataset.number) || 0);
		values.push(parseInt(els[1].dataset.number) || 0);
		return values;
	}
}

KeerOpKeerCheater.prototype.getCell = GridGame.prototype.getCell;
KeerOpKeerCheater.prototype.getCoord = GridGame.prototype.getCoord;
