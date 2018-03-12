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
		var jumper = Coords2D.fromArray(rv.jumper);
		this.getCell(jumper).addClass('stone').addClass('jumper');

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
