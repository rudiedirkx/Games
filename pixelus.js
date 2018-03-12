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
			this.setMoves(this.m_iMoves - 1);
			this.m_arrLastMove = null;
			return true;
		}
	}

	createField( cell, type, rv, x, y ) {
		if ( 'x' == type ) {
			this.makeWall(cell);
		}
		else {
			cell.setHTML('<span></span>');
			if ( 'o' == type ) {
				cell.addClass('target');
			}
		}
	}

	createdMap( rv ) {
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
			var deltaC = this.dirCoords[d];
			var nf = this.getNeighborField(field, deltaC);
			if ( !withBufferStop || (nf && this.isSolid(nf)) ) {
				if ( this.pathIsFree(field, (d+2)%4) ) {
					return true;
				}
			}
		}

		return false;
	}

	pathIsFree( startField, direction ) {
		var deltaC = this.dirCoords[direction];
		var neighbor = startField;
		while ( neighbor = this.getNeighborField(neighbor, deltaC) ) {
			if ( this.isSolid(neighbor) ) {
				return false;
			}
		}

		return true;
	}

	isSolid( field ) {
		return field.is('.wall, .stone');
	}

	getNeighborField( field, deltaC ) {
		return this.getCell(this.getCoord(field).add(deltaC));
	}

	haveWon() {
		return this.m_objGrid.getElements('.target:not(.stone), .stone:not(.target)').length == 0;
	}

}

class PixelusEditor extends GridGameEditor {

	exportLevel() {
		var map = [];
		var stones = 0;

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('wall') ) {
					row += 'x';
				}
				else if ( cell.hasClass('target') ) {
					row += 'o';
					stones++;
				}
				else {
					row += ' ';
				}
			});
			map.push(row);
		});

		var level = {map, stones};
		this.validateLevel(level);
		return level;
	}

	validateLevel( level ) {
		var map = level.map.join('');
		var targets = map.length - map.replace(/o/g, '').length;

		if ( targets == 0 ) {
			throw 'Need targets.';
		}
	}

	formatLevelCode( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push("\t\t'stones' => " + level.stones + ",");
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

}
