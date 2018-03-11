class TheBoxMultiple extends GridGame {

	getPusher() {
		var pusher = this.m_objGrid.getElement('.pusher');
		return new Coords2D(pusher.cellIndex, pusher.parentNode.sectionRowIndex);
	}

	handleGlobalDirection( f_dir ) {
		if ( this.m_bGameOver ) return;

		var dx = 0, dy = 0;
		if ( 'left' == f_dir ) {
			dx = -1;
		}
		else if ( 'right' == f_dir ) {
			dx = 1;
		}
		else if ( 'up' == f_dir ) {
			dy = -1;
		}
		else if ( 'down' == f_dir ) {
			dy = 1;
		}
		else {
			return;
		}

		var pusher = this.getPusher();

		var nowFieldC = pusher;
		var toFieldC = new Coords2D(pusher.x + dx, pusher.y + dy);
		var nextFieldC = new Coords2D(pusher.x + dx*2, pusher.y + dy*2);

		// TO-FIELD cannot be wall
		var toField = this.m_objGrid.rows[toFieldC.y].cells[toFieldC.x];
		if ( toField.hasClass('wall') ) {
			return;
		}
		var nextField = this.m_objGrid.rows[nextFieldC.y].cells[nextFieldC.x];

		// NEXT-FIELD must be empty
		if ( toField.hasClass('box') && ( nextField.hasClass('box') || nextField.hasClass('wall') ) ) {
			return;
		}
		var nowField = this.m_objGrid.rows[nowFieldC.y].cells[nowFieldC.x];

		this.m_arrLastMove = [this.m_iMoves, this.m_objGrid.innerHTML];

		if ( toField.hasClass('box') ) {
			this.m_iMoves++;
			toField.removeClass('box');
			nextField.addClass('box');
		}

		nowField.removeClass('pusher');
		toField.addClass('pusher');

		this.m_iMoves++;
		this.setMoves();

		this.postMove(toField, nextField);

		this.haveWon() && this.win();
	}

	postMove(toField, nextField) {
	}

	haveWon() {
		return this.m_objGrid.getElements('.box:not(.target), .target:not(.box)').length == 0;
	}

	undoLastMove() {
		if ( this.m_arrLastMove ) {
			this.m_objGrid.innerHTML = this.m_arrLastMove[1];
			this.setMoves(this.m_arrLastMove[0]);
			this.m_arrLastMove = null;
		}
	}

	createField( cell, type, rv, x, y ) {
		if ( 'x' == type ) {
			cell.addClass('wall');
			cell.addClass('wall' + Math.ceil(2*Math.random()));
		}
		else if ( 't' == type ) {
			cell.addClass('target');
		}
	}

	createdMap(rv) {
		var pusher = Coords2D.fromArray(rv.pusher);
		this.m_objGrid.rows[pusher.y].cells[pusher.x].addClass('pusher');

		r.each(rv.boxes, ([x, y]) => {
			this.m_objGrid.rows[y].cells[x].addClass('box');
		});
	}

	listenControls() {
		this.listenGlobalDirection();
	}

}

class TheBoxSingle extends TheBoxMultiple {

	postMove(toField, nextField) {
		if ( nextField.is('.box.target') ) {
			setTimeout(function() {
				nextField.removeClass('box');
			}, 200);
		}
	}

	haveWon() {
		return this.m_objGrid.getElements('.box:not(.target)').length == 0;
	}

}

class TheBoxEditor extends TheBoxMultiple {

	reset() {
	}

	createMap( width, height ) {
		for (var y = 0; y < height; y++) {
			var nr = this.m_objGrid.insertRow(this.m_objGrid.rows.length);
			for (var x = 0; x < width; x++) {
				var cell = nr.insertCell(nr.cells.length);

			}
		}
	}

	getType() {
		var tr = $('[data-type].active');
		if ( tr ) {
			return tr.data('type');
		}
	}

	exportLevel() {
		var map = [];
		var boxes = [];
		var pusher = null;

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				var C = new Coords2D(cell.cellIndex, cell.parentNode.sectionRowIndex);
				if ( cell.hasClass('wall') ) {
					row += 'x';
				}
				else if ( cell.hasClass('target') ) {
					row += 't';
				}
				else {
					row += ' ';
				}

				if ( cell.hasClass('box') ) {
					boxes.push(C);
				}
				else if ( cell.hasClass('pusher') ) {
					pusher = C;
				}
			});
			map.push(row);
		});

		var level = {map, boxes, pusher};
		this.validateLevel(level);
		return level;
	}

	validateLevel( level ) {
		var boxes = level.boxes.length;
		var map = level.map.join('');
		var targets = map.length - map.replace(/t/g, '').length;

		if ( targets == 0 ) {
			throw 'Need targets.';
		}

		if ( boxes == 0 ) {
			throw 'Need boxes.';
		}

		if ( targets > 1 && targets != boxes ) {
			throw 'Number of targets must equal number of boxes, or be 1.';
		}

		if ( !level.pusher ) {
			throw 'Need pusher.';
		}
	}

	listenControls() {
		this.listenCellClick();
		this.listenTypeClick();
	}

	listenTypeClick() {
		$$('[data-type]').on('click', (e) => {
			this.handleTypeClick(e.subject.data('type'));
		});
	}

	handleTypeClick( type ) {
		$$('[data-type].active').removeClass('active');
		$$('[data-type="' + type + '"]').addClass('active');
	}

	handleCellClick( cell ) {
		var type = this.getType();
		if ( !type ) {
			alert('Select a type first');
			return;
		}

		this['setType_' + type](cell);
	}

	setWall( cell ) {
		cell.addClass('wall');
		cell.addClass('wall' + Math.ceil(2*Math.random()));
	}

	unsetWall( cell ) {
		cell.removeClass('wall').removeClass('wall1').removeClass('wall2');
	}

	setType_wall( cell ) {
		if ( cell.hasClass('wall') ) {
			this.unsetWall(cell);
		}
		else {
			cell.removeClass('target');
			cell.removeClass('box');
			cell.removeClass('pusher');
			this.setWall(cell);
		}
	}

	setType_target( cell ) {
		if ( cell.hasClass('target') ) {
			cell.removeClass('target');
		}
		else {
			this.unsetWall(cell);
			cell.addClass('target');
		}
	}

	setType_box( cell ) {
		if ( cell.hasClass('box') ) {
			cell.removeClass('box');
		}
		else {
			this.unsetWall(cell);
			cell.addClass('box');
		}
	}

	setType_pusher( cell ) {
		if ( cell.hasClass('pusher') ) {
			cell.removeClass('pusher');
		}
		else {
			this.m_objGrid.getElements('.pusher').removeClass('pusher');
			cell.addClass('pusher');
		}
	}

}
