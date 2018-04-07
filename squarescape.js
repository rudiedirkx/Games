class Squarescape extends LeveledGridGame {

	createField( cell, type, rv, x, y ) {
		if ( type != 'x' ) {
			cell.addClass('available');
		}

		if ( type == 'p' ) {
			cell.addClass('pause');
		}
		else if ( type == 'd' ) {
			cell.addClass('danger');
		}
		else if ( type == 'o' ) {
			cell.addClass('collect');
		}
		else if ( type == 't' ) {
			cell.addClass('end');
		}
	}

	createdMap( rv ) {
		var start = Coords2D.fromArray(rv.start);
		this.getCell(start).addClass('player');
	}

	haveWon() {
		return this.m_objGrid.querySelectorAll('.collect:not(.end)').length == 0;
	}

	haveLost() {
		return (
			this.m_objGrid.querySelector('.player.danger') ||
			this.m_objGrid.querySelector('.player.end')
		);
	}

	getPlayer() {
		return this.m_objGrid.getElement('.player');
	}

	move( offset ) {
		const path = this.findPath(this.getPlayer(), offset);
		if ( path.length > 0 ) {
			this.drawPath(path);
			this.setMoves(this.m_iMoves + 1);
		}
	}

	findPath( from, offset ) {
		const path = [];
		var current = from;
		while ( current = this.getCell(this.getCoord(current).add(offset)) ) {
			if ( current.hasClass('pause') || current.hasClass('danger') ) {
				path.push(current);
				return path;
			}
			else if ( !current.hasClass('available') ) {
				return path;
			}
			else {
				path.push(current);
			}
		}

		return path;
	}

	drawPath( path ) {
		var drawStep = () => {
			const step = path.shift();
			this.movePlayer(step);
			this.winOrLose();
			path.length > 0 && setTimeout(drawStep, 50);
		};
		drawStep();
	}

	movePlayer( to ) {
		const player = this.getPlayer();
		player.removeClass('player');
		to.addClass('player');

		if ( player.hasClass('collect') ) {
			player.removeClass('collect');
			to.addClass('collect');
		}
	}

	listenControls() {
		this.listenGlobalDirection();
	}

	handleGlobalDirection( direction ) {
		if ( this.m_bGameOver ) return this.restartLevel();

		this.move(this.dir4Coords[this.dir4Names.indexOf(direction[0])]);
	}

}

class SquarescapeEditor extends GridGameEditor {

	cellTypes() {
		return {
			available: 'Available',
			start: 'Start',
			end: 'End',
			pause: 'Pause',
			danger: 'Danger',
			collect: 'Collect',
		};
	}

	defaultCellType() {
		return 'available';
	}

	createdMapCell( cell ) {
		cell.addClass('available');
	}

	setType_available( cell ) {
		cell.toggleClass('available');
	}

	setType_start( cell ) {
		cell.addClass('available');
		if ( cell.hasClass('start') ) {
			cell.removeClass('start');
		}
		else {
			this.m_objGrid.getElements('.start').removeClass('start');
			cell.removeClass('end');
			cell.addClass('start');
		}
	}

	setType_end( cell ) {
		cell.addClass('available');
		if ( cell.hasClass('end') ) {
			cell.removeClass('end');
		}
		else {
			this.m_objGrid.getElements('.end').removeClass('end');
			cell.removeClass('start');
			cell.addClass('end');
		}
	}

	setType_pause( cell ) {
		cell.toggleClass('pause');
		cell.addClass('available');
		cell.removeClass('collect');
		cell.removeClass('danger');
	}

	setType_danger( cell ) {
		cell.toggleClass('danger');
		cell.addClass('available');
		cell.removeClass('collect');
		cell.removeClass('pause');
	}

	setType_collect( cell ) {
		cell.toggleClass('collect');
		cell.addClass('available');
		cell.removeClass('pause');
		cell.removeClass('danger');
	}

	exportLevel() {
		var map = [];
		var start;

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('start') ) {
					row += ' ';
					start = this.getCoord(cell);
				}
				else if ( cell.hasClass('end') ) {
					row += 't';
				}
				else if ( cell.hasClass('pause') ) {
					row += 'p';
				}
				else if ( cell.hasClass('danger') ) {
					row += 'd';
				}
				else if ( cell.hasClass('collect') ) {
					row += 'o';
				}
				else {
					row += cell.hasClass('available') ? ' ' : 'x';
				}
			});
			map.push(row);
		});

		const level = {map, start};
		this.validateLevel(level);
		return level;
	}

	validateLevel( level ) {
		if ( !level.start ) {
			throw 'Need 1 start';
		}

		if ( this.countMapCells(level.map, 't') != 1 ) {
			throw 'Need 1 end';
		}

		if ( this.countMapCells(level.map, 'o') == 0 ) {
			throw 'Need collectibles';
		}
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push("\t\t'start' => [" + level.start.join() + "],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

}
