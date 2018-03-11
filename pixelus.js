class Pixelus extends LeveledGridGame {

	constructor() {
		super();
	}

	reset() {
		super.reset();

		this.setStones(0);
	}

	setStones( f_iStones ) {
		if ( f_iStones != null ) {
			this.m_iStones = f_iStones;
		}
		$('#stats-stones').setText(this.m_iStones);
	}

	undoLastMove() {
		if ( this.m_arrLastMove ) {
			this.m_objGrid.setHTML(this.m_arrLastMove[1]);
			this.setStones(this.m_arrLastMove[0]);
			this.m_arrLastMove = null;
			this.setMoves(this.m_iMoves - 1);
		}
	}

	createField( cell, type, rv, x, y ) {
		if ( 'x' == type ) {
			cell.addClass('wall');
			cell.addClass('wall' + Math.ceil(2*Math.random()));
		}
		else {
			cell.setHTML('<span></span>');
			if ( 'o' == type ) {
				cell.addClass('target');
			}
		}
	}

	createdMap(rv) {
		this.setStones(rv.stones);
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( !cell.hasClass('wall') ) {
			if ( !cell.hasClass('stone') ) {
				this.slingStone(cell);
			}
			else {
				this.removeStone(cell);
			}
		}
	}

	slingStone( target ) {
		if ( 0 < this.m_iStones ) {
			if ( this.isReachableField(target, true) ) {
				this.m_arrLastMove = [this.m_iStones, this.m_objGrid.innerHTML];

				target.addClass('stone');

				this.setStones(this.m_iStones - 1);
				this.setMoves(this.m_iMoves + 1);

				this.haveWon() && this.win();
			}
		}
	}

	removeStone( field ) {
		if ( this.isReachableField(field, false) ) {
			this.m_arrLastMove = [this.m_iStones, this.m_objGrid.innerHTML];

			field.removeClass('stone');

			this.setStones(this.m_iStones + 1);
			this.setMoves(this.m_iMoves + 1);

			this.haveWon() && this.win();
		}
	}

	isReachableField( field, withBufferStop ) {
		for ( var d=0; d<4; d++ ) {
			var cd = this.nesw[d];
			var nf = this.getNeighborField(field, cd);
			if ( !withBufferStop || (nf && this.isSolid(nf)) ) {
				if ( this.pathIsFree(field, (d+2)%4) ) {
					return true;
				}
			}
		}

		return false;
	}

	pathIsFree( startField, direction ) {
		var cd = this.nesw[direction];
		var neighbor = startField;
		while ( neighbor = this.getNeighborField(neighbor, cd) ) {
			if ( this.isSolid(neighbor) ) {
				return false;
			}
		}

		return true;
	}

	isSolid( field ) {
		return field.is('.wall, .stone');
	}

	getNeighborField( field, cd ) {
		var x = field.cellIndex;
		var y = field.parentNode.sectionRowIndex;
		return this.m_objGrid.rows[ y + cd[1] ] && this.m_objGrid.rows[ y + cd[1] ].cells[ x + cd[0] ];
	}

	haveWon() {
		return this.m_objGrid.getElements('.target:not(.stone), .stone:not(.target)').length == 0;
	}

}
