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
			this.winOrLose();
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

class Machinarium2 extends LeveledGridGame {

	reset() {
		super.reset();

		this.m_arrSnakes = [];
		this.m_objDraggingCell = null;
	}

	createGame() {
		super.createGame();

		$('#undo-div').hide();
	}

	createField( cell, type, rv, x, y ) {
		if ( 'x' != type ) {
			cell.addClass('available');

			if ( type.match(/\d/) ) {
				cell.data('target', type);
			}
		}
	}

	createdMap( rv ) {
		this.m_arrSnakes = rv.snakes.map((coords) => coords.map(Coords2D.fromArray));
		this.drawSnakes();
	}

	haveWon() {
		return this.m_objGrid.getElements('[data-target]').filter((cell) => {
			return cell.data('target') !== cell.data('snake');
		}).length == 0;
	}

	drawSnakes() {
		this.m_objGrid.getElements('[data-snake]').data('snake', null).removeClass('end');
		r.each(this.m_arrSnakes, (coords, snake) => {
			r.each(coords, (C) => {
				this.getCell(C).data('snake', snake);
			});
			this.getCell(coords[0]).addClass('end');
			this.getCell(coords[coords.length-1]).addClass('end');
		});
	}

	areAdjacent( cell1, cell2 ) {
		var C = this.getCoord(cell1);
		return this.dir4Coords.some((offset) => this.getCell(offset.add(C)) == cell2);
	}

	moveSnake( fromCell, toCell ) {
		var fromCellC = this.getCoord(fromCell);
		var toCellC = this.getCoord(toCell);

		var snake = parseInt(fromCell.data('snake'));
		var coords = this.m_arrSnakes[snake];

		if ( coords[0].join() == fromCellC.join() ) {
			coords.pop();
			coords.unshift(toCellC);
		}
		else {
			coords.shift();
			coords.push(toCellC);
		}

		this.setMoves(this.m_iMoves + 1);

		this.drawSnakes();
	}

	listenControls() {
		this.listenDrag();
	}

	listenDrag() {
		// Start dragging
		this.m_objGrid.on('mousedown', 'td', (e) => {
			if ( e.subject.hasClass('end') ) {
				this.handleDragStart(e.subject);
			}
		});

		document.on('touchstart', 'td', (e) => {
			e.preventDefault();
			if ( e.subject.hasClass('end') ) {
				this.handleDragStart(e.subject);
			}
		});

		// Drag
		this.m_objGrid.on('mouseover', 'td', (e) => {
			if ( this.m_objDraggingCell ) {
				this.handleDragMove(e.subject);
			}
		});

		var lastEl;
		document.on('touchmove', 'td', (e) => {
			e.preventDefault();
			var subject = document.elementFromPoint(e.pageX, e.pageY);
			if ( this.m_objDraggingCell && lastEl != subject ) {
				lastEl = subject;
				this.handleDragMove(subject);
			}
		});

		// Stop dragging
		document.on('mouseup', (e) => {
			if ( this.m_objDraggingCell ) {
				this.handleDragEnd();
			}
		});

		this.m_objGrid.on('mouseleave', (e) => {
			if ( this.m_objDraggingCell ) {
				this.handleDragEnd();
			}
		});

		document.on('touchend', (e) => {
			lastEl = null;
			if ( this.m_objDraggingCell ) {
				this.handleDragEnd();
			}
		});
	}

	handleDragStart( cell ) {
		this.m_objDraggingCell = cell;
	}

	handleDragMove( cell ) {
		if ( cell.hasClass('available') && !cell.data('snake') ) {
			if ( this.areAdjacent(this.m_objDraggingCell, cell) ) {
				this.moveSnake(this.m_objDraggingCell, cell);
				this.m_objDraggingCell = cell;
			}
		}
	}

	handleDragEnd() {
		this.m_objDraggingCell = null;

		this.winOrLose();
	}

}
