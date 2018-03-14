class Pixelus extends LeveledGridGame {

	getStones() {
		return this.m_objGrid.getElements('.target').length - this.m_objGrid.getElements('.stone').length;
	}

	setStones() {
		$('#stats-stones').setText(this.getStones());
	}

	undoLastMove() {
		if ( super.undoLastMove() ) {
			this.setStones();
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
		this.setStones();
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( !cell.hasClass('wall') ) {
			if ( this.m_bGameOver ) return;

			if ( !cell.hasClass('stone') ) {
				this.slingStone(cell);
			}
			else {
				this.removeStone(cell);
			}
		}
	}

	slingStone( target ) {
		if ( this.getStones() > 0 ) {
			if ( this.isReachableField(target, true) ) {
				this.saveUndoState();

				target.addClass('stone');

				this.setMoves(this.m_iMoves + 1);
				this.setStones();

				this.haveWon() && this.win();
			}
		}
	}

	removeStone( field ) {
		if ( this.isReachableField(field, false) ) {
			this.saveUndoState();

			field.removeClass('stone');

			this.setMoves(this.m_iMoves + 1);
			this.setStones();

			this.haveWon() && this.win();
		}
	}

	isReachableField( field, withBufferStop ) {
		for ( var d=0; d<4; d++ ) {
			var deltaC = this.dir4Coords[d];
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
		var deltaC = this.dir4Coords[direction];
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

	cellTypes() {
		return {
			wall: 'Wall',
			target: 'Target',
		};
	}

	exportLevel() {
		var map = [];

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('wall') ) {
					row += 'x';
				}
				else if ( cell.hasClass('target') ) {
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
		var targets = map.length - map.replace(/o/g, '').length;

		if ( targets == 0 ) {
			throw 'Need targets.';
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
