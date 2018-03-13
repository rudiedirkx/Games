class BioshockFlood extends GridGame {
	constructor() {
		super();

		this.pipes = [
			this.createPipe('ud'),
			this.createPipe('lr'),
			this.createPipe('ur'),
			this.createPipe('ul'),
			this.createPipe('dr'),
			this.createPipe('dl'),
			this.createPipe('ur'),
			this.createPipe('ul'),
			this.createPipe('dr'),
			this.createPipe('dl'),
		];

		this.reset();
	}

	reset() {
		super.reset();

		this.m_iSize = 0;
	}

	getSelectedCell() {
		return this.m_objGrid.getElement('td.selected');
	}

	getStartCell() {
		return this.m_objGrid.getElement('th.io.start');
	}

	getCurrentCell() {
		return this.m_objGrid.getElement('.current');
	}

	getCellCoords( cell ) {
		return new Coords2D(
			cell.cellIndex,
			cell.parentNode.sectionRowIndex
		);
	}

	getCellDirection( cell ) {
		if ( cell.hasClass('start') ) {
			var C = this.getCellCoords(cell);
			if ( C.x == 0 ) {
				return 'r';
			}
			else if ( C.x == this.m_iSize + 1 ) {
				return 'l';
			}
			else if ( C.y == 0 ) {
				return 'd';
			}
			else if ( C.y == this.m_iSize + 1 ) {
				return 'u';
			}
		}
		else {
			var dirIn = cell.data('in');
			var pipe = this.pipes[ cell.data('pipe') ];
			return pipe.getOtherSide(dirIn);
		}
	}

	makeOppositeDir( dir ) {
		var index = this.dirNames.indexOf(dir);
		return this.dirNames[ (index+2)%4 ];
	}

	getNextCell() {
		var currentCell = this.getCurrentCell();
		var direction = this.getCellDirection(currentCell);
		var deltaC = this.dirCoords[ this.dirNames.indexOf(direction) ];
		var nextCellC = this.getCellCoords(currentCell).add(deltaC);
		var nextCell = this.m_objGrid.rows[nextCellC.y].cells[nextCellC.x];

		return nextCell;
	}

	start() {
		if ( this.m_bGameOver ) return this.createMap(this.m_iSize);

		var ticker = setInterval(() => {
			this.tick();
			if ( this.m_bGameOver ) {
				clearInterval(ticker);
			}
		}, 2500);
	}

	tick() {
		if ( this.m_bGameOver ) return this.createMap(this.m_iSize);

		var currentCell = this.getCurrentCell();
		var nextCell = this.getNextCell();
		var direction = this.getCellDirection(currentCell);

		if ( !this.flowsInto(currentCell, direction, nextCell) ) {
			this.unselect();
			return this.lose();
		}

		currentCell.removeClass('current');

		if ( nextCell.hasClass('end') ) {
			return this.win();
		}

		nextCell.addClass('current').addClass('locked');
		nextCell.data('in', this.makeOppositeDir(direction));
	}

	flowsInto( fromCell, dirOut, toCell ) {
		if ( toCell.hasClass('meta') ) {
			return toCell.hasClass('end');
		}

		var dirIn = this.makeOppositeDir(dirOut);
		var pipe = this.pipes[ toCell.data('pipe') ];
		return pipe.acceptsAt(dirIn);
	}

	createMap( size ) {
		this.reset();

		this.unselect();

		this.m_objGrid.empty();
		this.m_iSize = size;

		var x, y, tr, cell, meta, corner;
		for (y = -1; y <= size; y++) {
			tr = this.m_objGrid.insertRow();
			for (x = -1; x <= size; x++) {
				meta = y == -1 || y == size || x == -1 || x == size;
				corner = (y == -1 || y == size) && (x == -1 || x == size);
				cell = document.createElement(meta ? 'th' : 'td');
				tr.appendChild(cell);
				corner ? cell.addClass('corner') : meta ? cell.addClass('meta') : null;
			}
		}

		this.createStartEnd();
		this.createPipes();
	}

	createPipe( connectors ) {
		return new BioshockFloodPipe(connectors);
	}

	createPipes() {
		this.m_objGrid.getElements('td').each(cell => {
			var pipe = parseInt(Math.random() * this.pipes.length);
			cell.data('pipe', pipe);
			cell.className = this.pipes[pipe].getClass();
		});
	}

	createStartEnd() {
		var [start, end] = this.generateStartEnd();

		var startCell = this.m_objGrid.rows[start.y].cells[start.x];
		startCell.addClass('io').addClass('start').addClass('current').setText('STA RT');
		var endCell = this.m_objGrid.rows[end.y].cells[end.x];
		endCell.addClass('io').addClass('end').setText('END');
	}

	generateStartEnd() {
		var size = this.m_iSize;
		var start = parseInt(Math.random() * 4);
		var end = (start + 2) % 4;

		start = this.dirCoords[start];
		end = this.dirCoords[end];

		start = start.replace(1, size + 1).replace(0, parseInt(Math.random() * size + 1)).replace(-1, 0);
		end = end.replace(1, size + 1).replace(0, parseInt(Math.random() * size + 1)).replace(-1, 0);

		return [start, end];
	}

	switchSelected( otherCell ) {
		var selectedCell = this.getSelectedCell();

		if ( otherCell.hasClass('locked') || selectedCell.hasClass('locked') ) {
			return this.unselect();
		}

		var selectedType = selectedCell.data('pipe');
		var otherType = otherCell.data('pipe');

		otherCell.data('pipe', selectedType);
		otherCell.className = this.pipes[selectedType].getClass();
		selectedCell.data('pipe', otherType);
		selectedCell.className = this.pipes[otherType].getClass();

		this.unselect();

		this.setMoves(this.m_iMoves + 1);
	}

	select( cell ) {
		cell.addClass('selected');
		this.m_objGrid.addClass('with-selection');
	}

	unselect() {
		var cell = this.getSelectedCell();
		cell && cell.removeClass('selected');
		this.m_objGrid.removeClass('with-selection');
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return this.createMap(this.m_iSize);

		if ( cell.hasClass('locked') ) return;

		var selected = this.getSelectedCell();
		if ( selected ) {
			if ( cell == selected ) {
				this.unselect();
			}
			else {
				this.switchSelected(cell);
			}
		}
		else {
			this.select(cell);
		}
	}

}

class BioshockFloodPipe {
	constructor( connectors ) {
		this.connectors = [connectors[0], connectors[1]];
	}

	getClass() {
		return 'pipe-' + this.connectors.join('');
	}

	getOtherSide( direction ) {
		if ( this.connectors[0] == direction ) {
			return this.connectors[1];
		}
		else if ( this.connectors[1] == direction ) {
			return this.connectors[0];
		}
	}

	acceptsAt( direction ) {
		return this.connectors.includes(direction);
	}

}
