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

class Machinarium2Editor extends GridGameEditor {

	cellTypes() {
		return {
			available: 'Available',
			target_0: 'Target A',
			snake_0: 'Snake A',
			target_1: 'Target B',
			snake_1: 'Snake B',
			target_2: 'Target C',
			snake_2: 'Snake C',
			target_3: 'Target D',
			snake_3: 'Snake D',
		};
	}

	defaultCellType() {
		return 'available';
	}

	createCellTypeCell( type ) {
		var m = type.match(/(target|snake)_(\d+)/);
		if ( !m ) {
			return super.createCellTypeCell(type);
		}

		return '<td class="available" data-' + m[1] + '="' + m[2] + '"></td>';
	}

	createdMapCell( cell ) {
		cell.addClass('available');
	}

	setTargetSnake( type, cell, n ) {
		if ( cell.data(type) == n ) {
			cell.data(type, null);
		}
		else {
			cell.data(type, n);
		}
	}

	setType_available( cell ) {
		if ( cell.hasClass('available') ) {
			cell.removeClass('available');
			cell.data('target', null);
			cell.data('snake', null);
		}
		else {
			cell.addClass('available');
		}
	}

	setType_target_0( cell ) {
		return this.setTargetSnake('target', cell, 0);
	}

	setType_snake_0( cell ) {
		return this.setTargetSnake('snake', cell, 0);
	}

	setType_target_1( cell ) {
		return this.setTargetSnake('target', cell, 1);
	}

	setType_snake_1( cell ) {
		return this.setTargetSnake('snake', cell, 1);
	}

	setType_target_2( cell ) {
		return this.setTargetSnake('target', cell, 2);
	}

	setType_snake_2( cell ) {
		return this.setTargetSnake('snake', cell, 2);
	}

	setType_target_3( cell ) {
		return this.setTargetSnake('target', cell, 3);
	}

	setType_snake_3( cell ) {
		return this.setTargetSnake('snake', cell, 3);
	}

	exportLevel() {
		var map = [];
		var snakes = [];

		// @todo Find ambiguous snake paths, like F1Racer

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('available') ) {
					var target = cell.data('target');
					var snake = cell.data('snake');

					row += target ? target : ' ';

					if ( snake ) {
						snakes[snake] || (snakes[snake] = []);
						snakes[snake].push(this.getCoord(cell));
					}
				}
				else {
					row += 'x';
				}
			});
			map.push(row);
		});

		return {map, snakes};
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push("\t\t'snakes' => [");
		r.each(level.snakes, snake => code.push("\t\t\t" + JSON.stringify(snake, Coords2D.jsonReplacer()) + ","));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

}
