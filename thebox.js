class TheBoxMultiple extends LeveledGridGame {

	getPusher() {
		var pusher = this.m_objGrid.getElement('.pusher');
		return this.getCoord(pusher);
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
		var toField = this.getCell(toFieldC);
		if ( toField.hasClass('wall') ) {
			return;
		}
		var nextField = this.getCell(nextFieldC);

		// NEXT-FIELD must be empty
		if ( toField.hasClass('box') && ( nextField.hasClass('box') || nextField.hasClass('wall') ) ) {
			return;
		}
		var nowField = this.getCell(nowFieldC);

		this.saveUndoState();

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

	createField( cell, type, rv, x, y ) {
		if ( 'x' == type ) {
			this.makeWall(cell);
		}
		else if ( 't' == type ) {
			cell.addClass('target');
		}
	}

	createdMap( rv ) {
		var pusher = Coords2D.fromArray(rv.pusher);
		this.getCell(pusher).addClass('pusher');

		r.each(rv.boxes, (box) => {
			this.getCell(Coords2D.fromArray(box)).addClass('box');
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

class TheBoxEditor extends GridGameEditor {

	cellTypes() {
		return {
			wall: 'Wall',
			target: 'Target',
			box: 'Box',
			pusher: 'Pusher',
		};
	}

	exportLevel( validate = true ) {
		var map = [];
		var boxes = [];
		var pusher = null;

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				var C = this.getCoord(cell);
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
		validate && this.validateLevel(level);
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

	formatLevelCode( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push("\t\t'pusher' => [" + level.pusher.join(', ') + "],");
		code.push("\t\t'boxes' => [");
		r.each(level.boxes, box => code.push("\t\t\t[" + box.join(', ') + "],"));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
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
			cell.removeClass('box');
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
