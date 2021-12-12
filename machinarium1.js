class Machinarium1 extends LeveledGridGame {

	reset() {
		super.reset();

		this.m_iAutoChooseTimer = 0;
	}

	statTypes() {
		var stats = super.statTypes();
		delete stats.moves;
		return stats;
	}

	createGame() {
		super.createGame();

		$('#undo-div').hide();
	}

	setMoves() {
	}

	createField( cell, type, rv, x, y ) {
		if ( 'x' != type ) {
			cell.addClass('available');
		}
	}

	haveWon() {
		return this.m_objGrid.getElements('.available:not(.taken)').length == 0;
	}

	haveLost() {
		return this.m_objGrid.getElements('.available:not(.taken)').length > 0;
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) {
			return this.restartLevel();
		}

		this.startTime();
		clearTimeout(this.m_iAutoChooseTimer);

		if ( cell.hasClass('start') ) {
			this.restartLevel();
		}
		else if ( cell.hasClass('direction') ) {
			var currentCell = this.getCurrent();
			var currentCellC = this.getCoord(currentCell);
			this.dir4Coords.some((offset) => {
				var dirCell = this.getCell(currentCellC.add(offset));
				if ( dirCell == cell ) {
					this.followDirection(currentCell, offset);
					return true;
				}
			});
		}
		else if ( cell.matches('.available:not(.start):not(.taken)') ) {
			cell.addClass('start');
			cell.addClass('taken');
			this.markCurrent(cell);
			this.hiliteDirections(cell);
		}
	}

	followDirection( cell, offset ) {
		var taken = cell;
		var lastTaken;
		while ( taken = this.isAvailable(this.getCell(this.getCoord(taken).add(offset))) ) {
			taken.addClass('taken');
			lastTaken = taken;
		}

		this.markCurrent(lastTaken);
		var directions = this.hiliteDirections(lastTaken);

		if ( directions.length == 0 ) {
			this.startWinCheck(100);
		}
		else if ( directions.length == 1 ) {
			this.m_iAutoChooseTimer = setTimeout(() => directions[0].click(), 300);
		}
	}

	getCurrent() {
		return this.m_objGrid.getElement('.current');
	}

	markCurrent( cell ) {
		this.m_objGrid.getElements('.current, .direction').removeClass('current').removeClass('direction');
		cell.addClass('current');
	}

	hiliteDirections( currentCell ) {
		var currentCellC = this.getCoord(currentCell);

		var directions = [];
		r.each(this.dir4Coords, (offset, i) => {
			var cell = this.getCell(currentCellC.add(offset));
			if ( this.isAvailable(cell) ) {
				cell.addClass('direction');
				cell.data('direction', this.dir4Names[i]);
				directions.push(cell);
			}
		});

		return directions;
	}

	isAvailable( cell ) {
		return cell && cell.hasClass('available') && !cell.hasClass('taken') && cell;
	}

}

class Machinarium1Editor extends GridGameEditor {

	cellTypes() {
		return {
			available: 'Available'
		};
	}

	defaultCellType() {
		return 'available';
	}

	createdMapCell( cell ) {
		cell.addClass('available');
	}

	setType_available( cell ) {
		cell.toggleClass('available');
	}

	exportLevel() {
		var map = [];

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				row += cell.hasClass('available') ? ' ' : 'x';
			});
			map.push(row);
		});

		return {map};
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

}
