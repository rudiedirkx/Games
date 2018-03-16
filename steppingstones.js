class SteppingStones extends LeveledGridGame {

	reset() {
		super.reset();

		this.setStones(0);
	}

	setMoves() {
	}

	setStones( f_iStones ) {
		var iStones = f_iStones == null ? this.m_objGrid.getElements('.stone').length : f_iStones;
		$('#stats-stones').setText(iStones);
	}

	getJumper() {
		var jumper = this.m_objGrid.getElement('.jumper');
		return this.getCoord(jumper);
	}

	createField( cell, type, rv, x, y ) {
		if ( 'o' == type ) {
			cell.setHTML('<span></span>');
			cell.addClass('available');
		}
		else if ( 's' == type ) {
			cell.setHTML('<span></span>');
			cell.addClass('available');
			cell.addClass('stone');
		}
	}

	createdMap( rv ) {
		var stoneCells = this.m_objGrid.getElements('.stone');
		stoneCells.sort(() => Math.random() > 0.5 ? 1 : -1);
		stoneCells[0].addClass('jumper');

		this.setStones();
	}

	haveWon() {
		return this.m_objGrid.getElements('.stone').length == 1;
	}

	listenControls() {
		this.listenGlobalDirection();
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( cell.hasClass('stone') ) {
			this.m_objGrid.getElements('.jumper').removeClass('jumper');
			cell.addClass('jumper');
		}
	}

	handleGlobalDirection( direction ) {
		var jumper = this.getJumper();

		var overFieldC = this.coordsByDir(jumper, direction);
		var overField = this.getCell(overFieldC);

		var toFieldC = this.coordsByDir( overFieldC, direction );
		var toField = this.getCell(toFieldC);

		if ( overField.is('.available.stone') && toField.is('.available:not(.stone)') ) {
			this.saveUndoState();

			var nowField = this.getCell(jumper);

			nowField.removeClass('jumper').removeClass('stone');
			overField.removeClass('stone');
			toField.addClass('jumper').addClass('stone');

			this.setStones();

			this.haveWon() && this.win();
		}
	}

	coordsByDir( start, direction ) {
		var dx = 0, dy = 0;
		switch ( direction ) {
			case 'up':
				dy = -1;
				break;
			case 'down':
				dy = 1;
				break;
			case 'left':
				dx = -1;
				break;
			case 'right':
				dx = 1;
				break;
		}

		return start.add(new Coords2D(dx, dy));
	}

}

class SteppingStonesEditor extends GridGameEditor {

	cellTypes() {
		return {
			available: 'Grid',
			stone: 'Stone',
		};
	}

	defaultCellType() {
		return 'available';
	}

	createCellTypeCell( type ) {
		return '<td class="available ' + type + '"><span></span></td>';
	}

	createdMapCell( cell ) {
		cell.setHTML('<span></span>');
	}

	exportLevel() {
		var map = [];

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('stone') ) {
					row += 's';
				}
				else if ( cell.hasClass('available') ) {
					row += 'o';
				}
				else {
					row += ' ';
				}
			});
			map.push(row);
		});

		var level = {map};
		this.validateLevel(level);
		return level;
	}

	validateLevel( level ) {
		var map = level.map.join('');
		var stones = map.length - map.replace(/s/g, '').length;

		if ( stones == 0 ) {
			throw 'Need stones.';
		}
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

	setType_available( cell ) {
		if ( cell.hasClass('available') ) {
			cell.removeClass('available');
			cell.removeClass('stone');
		}
		else {
			cell.addClass('available');
		}
	}

	setType_stone( cell ) {
		if ( cell.hasClass('stone') ) {
			cell.removeClass('stone');
		}
		else {
			cell.addClass('available');
			cell.addClass('stone');
		}
	}

}
