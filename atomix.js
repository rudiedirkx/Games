class Atomix extends LeveledGridGame {

	reset() {
		super.reset();

		this.m_arrTarget = null;
	}

	createField( cell, type, rv, x, y ) {
		if ( 'x' == type ) {
			this.makeWall(cell);
		}
		else {
			cell.setHTML('<span></span>');
		}
	}

	createdMap( rv ) {
		r.each(rv.atoms, (atom) => {
			var C = Coords2D.fromArray(atom);
			this.getCell(C).data('atom', atom[2]);
		});

		this.m_arrTarget = rv.target;

		var header = '';
		header += '<p>' + rv.molecule + ' (' + rv.formula + ')</p>';
		header += '<table class="inside">';
		r.each(rv.target, (row) => {
			header += '<tr>';
			r.each(row, (atom) => {
				header += '<td data-atom="' + atom + '"><span></span></td>';
			});
			header += '</tr>';
		})
		header += '</table>';
		header += '<br>';

		$('#level-header').setHTML(header);
	}

	haveWon() {
		var first = this.m_objGrid.getElement('[data-atom]');
		var haveFirst = first.data('atom');
		var mustFirstLine = this.m_arrTarget[0];
		var mustFirstLineTrim = mustFirstLine.trimLeft();
		var mustFirst = mustFirstLineTrim[0];

		if ( haveFirst != mustFirst ) {
			return false;
		}

		var offsetC = new Coords2D(mustFirstLineTrim.length - mustFirstLine.length, 0);
		var startC = this.getCoord(first).add(offsetC);
		var startCell = this.getCell(startC);

		var won = !this.m_arrTarget.some((row, offsetY) => {
			return row.split('').some((atom, offsetX) => {
				var offset = new Coords2D(offsetX, offsetY);
				var cell = this.getCell(startC.add(offset));
				if ( atom != ' ' && atom !== cell.data('atom') ) {
					return true;
				}
			});
		});
		return won;
	}

	win() {
		super.win();

		this.unselect();
	}

	undoLastMove() {
		if ( super.undoLastMove() ) {
			console.log('undone');
		}
	}

	listenControls() {
		this.listenGlobalDirection();
		this.listenCellClick();
	}

	unselect() {
		this.m_objGrid.getElements('.selected').removeClass('selected');
	}

	handleCellClick( cell ) {
		if ( cell.data('atom') ) {
			this.unselect();
			cell.addClass('selected');
		}
	}

	getSelectedCell() {
		return this.m_objGrid.getElement('.selected');
	}

	passableCell( cell ) {
		if ( !cell.hasClass('wall') && !cell.data('atom') ) {
			return cell;
		}
	}

	getEndCell( start, deltaC ) {
		var cell = start;
		var end;
		while ( cell = this.passableCell(this.getCell(this.getCoord(cell).add(deltaC))) ) {
			end = cell;
		}
		return end;
	}

	handleGlobalDirection( direction ) {
		var selectedCell = this.getSelectedCell();
		if ( !selectedCell ) return;

		var deltaC = this.dirCoords[ this.dirNames.indexOf(direction[0]) ];

		var endCell = this.getEndCell(selectedCell, deltaC);
		if ( !endCell ) return;

		this.unselect();

		this.saveUndoState();

		endCell.data('atom', selectedCell.data('atom')).addClass('selected');
		selectedCell.data('atom', null);

		this.setMoves(this.m_iMoves + 1);

		this.haveWon() && this.win();
	}

}
