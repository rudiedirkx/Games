class Blackbox extends GridGame {

	reset() {
		super.reset();

		this.beams = [];
	}

	resetMap() {
		this.m_objGrid.removeClass('show-atoms');
		this.m_objGrid.getElements('td[data-side]').attr('style', null);
		this.m_objGrid.getElements('td.grid').removeClass('atom').removeClass('hilite').removeClass('impossible').removeClass('beam');
	}

	createMap() {
		this.reset();
		this.resetMap();

		const coords = this.makeAllCoords();
		coords.sort(() => Math.random() > 0.5 ? 1 : -1);
		coords.slice(0, Blackbox.ATOMS).forEach(C => this.m_objGrid.rows[C.y].cells[C.x].addClass('atom'));

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
		this.drawBeams();

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
			level: Blackbox.SIZE << 8 | Blackbox.ATOMS,
			score: this.getNumericalScore(),
		};
	}

	addBeam(move) {
		const beam = new BlackboxBeam(move);
		this.beams.push(beam);
		return beam;
	}

	drawBeams() {
		this.beams.forEach(beam => this.drawBeam(beam));
	}

	drawBeam(beam) {
		if (this.beamAbsorbedOrReflected(beam)) return;
		if (this.beamCorners(beam) < Blackbox.DRAW_CORNERS) return;

		// @todo Use classes lt, lr, lb, ll instead of beam

		for (let move of beam.path) {
			if (move.loc.hasClass('grid')) {
				move.loc.addClass('beam');
			}
		}
	}

	beamAbsorbedOrReflected(beam) {
		const last = beam.path.last();
		return last === false || last === true;
	}

	beamCorners(beam) {
		let corners = 0;
		let lastLoc;
		for (let move of beam.path) {
			if (move) {
				if (move.loc == lastLoc) {
					corners++;
				}

				lastLoc = move.loc;
			}
		}

		return corners;
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

	finishMove(start, end, beam) {
		start.style.backgroundColor = end.style.backgroundColor = this.randomColor();

		// this.drawBeam(beam);
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
			(Blackbox.SIDES.indexOf(cell.data('side')) + 2) % 4
		);
		const beam = this.addBeam(cur);
		for ( let i = 0; i < 200; i++ ) {
			cur = this.getNextMove(cur);
			beam.add(cur);

			if (cur === false) {
				this.finishMoveAbsorbed(cell);
				break;
			}
			if (cur === true || cur.loc === cell) {
				this.finishMoveSource(cell);
				break;
			}
			if (cur.loc.data('side')) {
				this.finishMove(cell, cur.loc, beam);
				break;
			}
		}
	}

	handleInsideClick(cell) {
		cell.removeClass('impossible').toggleClass('hilite');
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
		this.listenContextClick();
		this.listenTouchDrag();

		$('#newgame').on('click', e => {
			this.createMap();
		});

		$('#reveal').on('click', e => {
			this.m_bGameOver = this.m_bCheating = true;
			this.stopTime();
			this.m_objGrid.addClass('show-atoms');
		});

		$('#board-size').on('change', e => {
			Blackbox.SIZE = parseInt(e.subject.value);
			this.createBoard();
			this.createMap();
		});
		$('#board-atoms').on('change', e => {
			Blackbox.ATOMS = parseInt(e.subject.value);
			this.createBoard();
			this.createMap();
		});
	}

	listenContextClick() {
		this.m_objGrid.on('contextmenu', '#' + this.m_objGrid.idOrRnd() + ' td', (e) => {
			e.preventDefault();

			e.subject.removeClass('hilite').toggleClass('impossible');
		});
	}

	listenTouchDrag() {
		var toggle = null;
		var last = null;
		this.m_objGrid.on('touchstart', e => {
			// e.preventDefault();
			const td = e.target.closest('td');
			if (td) {
				toggle = !td.hasClass('impossible');
			}
		});
		this.m_objGrid.on('touchmove', e => {
			if (toggle == null) return;

			const td2 = document.elementFromPoint(e.pageX, e.pageY);
			if (last != td2) {
				td2.toggleClass('impossible', toggle);
			}

			last = td2;
		});
		document.on('touchend', e => {
			toggle = last = null;
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

		this.createConfigDropdowns();
		this.createBoard();
	}

	createConfigDropdowns() {
		$('#board-size').setHTML('<option>6<option>7<option>8<option>9<option>10');
		$('#board-atoms').setHTML('<option>4<option>5<option>6<option>7<option>8');
	}

	createBoard() {
		const corner = '<td class="corner"></td>';
		const top = '<td data-side="top"></td>';
		const left = '<td data-side="left"></td>';
		const right = '<td data-side="right"></td>';
		const bottom = '<td data-side="bottom"></td>';
		const inside = '<td class="grid"></td>';
		const html = '' +
			`<tr>${corner}${top.repeat(Blackbox.SIZE)}${corner}</tr>\n` +
			`<tr>${left}${inside.repeat(Blackbox.SIZE)}${right}</tr>\n`.repeat(Blackbox.SIZE) +
			`<tr>${corner}${bottom.repeat(Blackbox.SIZE)}${corner}</tr>\n`;
		$('table').setHTML(html);

		$('#board-size').value = Blackbox.SIZE;
		$('#board-atoms').value = Blackbox.ATOMS;
	}

	getStatsDelimiter() {
		return ' | ';
	}

}

Blackbox.SIZE = 8;
Blackbox.ATOMS = 5;
Blackbox.SIDES = ['top', 'right', 'bottom', 'left'];
Blackbox.DRAW_CORNERS = 2;

class BlackboxBeam {

	constructor(start) {
		this.path = [start];
	}

	add(move) {
		this.path.push(move);
	}

}

class BlackboxMove {

	constructor(loc, dir) {
		this.loc = loc;
		this.dir = dir;
	}

}
