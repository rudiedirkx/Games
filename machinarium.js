class Machinarium extends LeveledGridGame {

	reset() {
		super.reset();

		this.m_arrSnakes = [];
		this.m_objDraggingCell = null;
	}

	setMoves() {
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
