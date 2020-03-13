class Blackbox extends GridGame {

	static SIZE = 8;
	static SIDES = ['top', 'right', 'bottom', 'left'];

	reset() {
		super.reset();

		this.atoms = 5;
		this.checker = 0;
	}

	resetMap() {
		this.m_objGrid.removeClass('show-atoms');
		this.m_objGrid.getElements('td[data-side]').attr('style', null);
		this.m_objGrid.getElements('td.grid').removeClass('atom').removeClass('hilite');
	}

	createMap() {
		this.reset();
		this.resetMap();

		const coords = this.makeAllCoords();
		coords.sort(() => Math.random() > 0.5 ? 1 : -1);
		coords.slice(0, this.atoms).forEach(C => this.m_objGrid.rows[C.y].cells[C.x].addClass('atom'));

		// this.m_objGrid.addClass('show-atoms');
	}

	makeAllCoords() {
		return this.m_objGrid.getElements('.grid').map(cell => this.getCoord(cell));
	}

	haveWon() {
		return this.m_objGrid.getElements('.atom:not(.hilite)').length == 0 && this.m_objGrid.getElements('.hilite:not(.atom)').length == 0;
	}

	win() {
		this.m_objGrid.addClass('show-atoms');

		super.win();
	}

	getWinText() {
		return 'You WIN :-)\n\nYour score: ' + this.getNumericalScore();
	}

	getNumericalScore() {
		return Math.max(0, 2000 - this.getTime() * 5 - this.m_iMoves * 30);
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.constructor.SIZE << 8 | this.atoms,
		};
	}

	randomColor() {
		return '#' + ('000000' + (Math.random()*0xFFFFFF<<0).toString(16)).slice(-6);
	}

	getNextMove(cur) {
		const next = this.getNext(cur.loc, cur.dir);
		const left = this.getLeft(next, cur.dir);
		const right = this.getRight(next, cur.dir);

		const atomAhead = next.hasClass('atom');

		if (atomAhead) {
			// Absorbed
			return false;
		}

		const atomLeft = left.hasClass('atom');
		const atomRight = right.hasClass('atom');

		if (atomLeft && atomRight) {
			// Back to source
			return true;
		}

		if (atomLeft) {
			// Turn right
			return new BlackboxMove(cur.loc, (cur.dir + 1) % 4);
		}

		if (atomRight) {
			// Turn left
			return new BlackboxMove(cur.loc, (cur.dir + 3) % 4);
		}

		// Straight ahead
		return new BlackboxMove(next, cur.dir);
	}

	getCoord(cell) {
		return new Coords2D(cell.cellIndex, cell.parentNode.rowIndex);
	}

	getNext(cell, dir) {
		const C = this.getCoord(cell);
		const d = this.dir4Coords[dir];
		const next = this.m_objGrid.rows[C.y+d.y].cells[C.x+d.x];
		return next;
	}

	getLeft(cell, dir) {
		return this.getNext(cell, (dir + 3) % 4);
	}

	getRight(cell, dir) {
		return this.getNext(cell, (dir + 1) % 4);
	}

	finishMove(start, end) {
		start.style.backgroundColor = end.style.backgroundColor = this.randomColor();
	}

	finishMoveAbsorbed(start) {
		start.style.backgroundColor = 'gray';
	}

	finishMoveSource(start) {
		start.style.backgroundColor = 'white';
	}

	handleSideClick(cell) {
		this.setMoves(this.m_iMoves + 1);

		let cur = new BlackboxMove(
			cell,
			(this.constructor.SIDES.indexOf(cell.data('side')) + 2) % 4
		);
		for ( let i = 0; i < 200; i++ ) {
			cur = this.getNextMove(cur);

			if (cur === false) {
				this.finishMoveAbsorbed(cell);
				break;
			}
			if (cur === true || cur.loc === cell) {
				this.finishMoveSource(cell);
				break;
			}
			if (cur.loc.data('side')) {
				this.finishMove(cell, cur.loc);
				break;
			}
		}
	}

	handleInsideClick(cell) {
		cell.toggleClass('hilite');
	}

	startWinCheck() {
		clearTimeout(this.checker);
		this.checker = setTimeout(() => this.winOrLose(), 500);
	}

	handleCellClick(cell) {
		if (this.m_bGameOver) {
			return this.createMap();
		}

		this.startTime();

		if (cell.data('side')) {
			this.handleSideClick(cell);
			this.startWinCheck();
		}
		else if (cell.hasClass('grid')) {
			this.handleInsideClick(cell);
			this.startWinCheck();
		}
	}

	listenControls() {
		this.listenCellClick();

		$('#newgame').on('click', e => {
			this.createMap();
		});

		$('#reveal').on('click', e => {
			this.m_bGameOver = this.m_bCheating = true;
			this.stopTime();
			this.m_objGrid.addClass('show-atoms');
		});
	}

	setTime(time) {
		super.setTime(time);

		$('#stats-score').setText(this.getNumericalScore());
	}

	statTypes() {
		return {
			...super.statTypes(),
			score: 'Score',
		};
	}

	createGame() {
		this.createStats();

		this.createBoard();
	}

	createBoard() {
		const corner = '<td class="corner"></td>';
		const top = '<td data-side="top"></td>';
		const left = '<td data-side="left"></td>';
		const right = '<td data-side="right"></td>';
		const bottom = '<td data-side="bottom"></td>';
		const inside = '<td class="grid"></td>';
		const html = '' +
			`<tr>${corner}${top.repeat(this.constructor.SIZE)}${corner}</tr>\n` +
			`<tr>${left}${inside.repeat(this.constructor.SIZE)}${right}</tr>\n`.repeat(this.constructor.SIZE) +
			`<tr>${corner}${bottom.repeat(this.constructor.SIZE)}${corner}</tr>\n`;

		$('table').setHTML(html);
	}

	getStatsDelimiter() {
		return ' | ';
	}

}

class BlackboxMove {

	constructor(loc, dir) {
		this.loc = loc;
		this.dir = dir;
	}

}
