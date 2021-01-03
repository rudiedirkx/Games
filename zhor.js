"use strict";

class Zhor extends LeveledGridGame {

	statTypes() {
		return {
			time: 'Time',
		};
	}

	setMoves() {
	}

	createField( cell, type, rv, x, y ) {
		if ( 't' == type ) {
			cell.addClass('target');
		}
		else {
			const n = parseInt(type);
			if ( !isNaN(n) ) {
				cell.data('source', n);
			}
		}
	}

	createdMap( rv ) {
		/*if ( !$('#solve') ) {
			document.el('p', {id: 'solve'}).setHTML('<a href="#">Solve</a>').appendTo($('#level-header').parentNode);
			$('#solve > a').on('click', e => {
				e.preventDefault();

				ZhorSolver.solveFromPage();
			});
		}*/
	}

	listenControls() {
		this.listenCellClick();
		this.listenGlobalDirection();
	}

	haveWon() {
		return this.m_objGrid.getElement('.target.path');
	}

	getSelected() {
		return this.m_objGrid.getElement('.selected');
	}

	getPath( start, dir ) {
		const offset = Coords2D.dir4Coords[ Coords2D.dir4Names.indexOf(dir) ];

		const path = [];
		var current = start;
		while ( current = this.getCell(this.getCoord(current).add(offset)) ) {
			if ( !current.is('.path, [data-source]') ) {
				path.push(current);
			}
		}

		return path;
	}

	selectSource( cell ) {
		const selected = this.getSelected();
		selected && this.unselectSource(selected);

		cell.addClass('selected');

		const C = this.getCoord(cell);
		Coords2D.dir4Coords.forEach((D, d) => {
			const nb = this.getCell(C.add(D));
			nb && nb.data('selected-nb', Coords2D.dir4Names[d]);
		});
	}

	unselectSource( cell ) {
		cell || (cell = this.getSelected());

		cell && cell.removeClass('selected');
		$$('[data-selected-nb]').data('selected-nb', null);
	}

	move( selected, dir ) {
		this.unselectSource();

		const n = parseInt(selected.data('source'));
		const path = this.getPath(selected, dir).slice(0, n);

		(new Elements(path.concat(selected))).addClass('path');

		this.winOrLose();
	}

	handleCellClick( cell ) {
		this.startTime();

		if ( cell.data('source') && !cell.hasClass('path') ) {
			const selected = this.getSelected();
			if ( cell == selected ) {
				this.unselectSource(cell);
			}
			else {
				this.selectSource(cell);
			}
		}

		if ( cell.data('selected-nb') ) {
			const selected = this.getSelected();
			selected && this.move(selected, cell.data('selected-nb'));
		}
	}

	handleGlobalDirection( dir ) {
		const selected = this.getSelected();
		if ( !selected ) return;

		this.move(selected, dir[0]);
	}

}

class ZhorEditor extends GridGameEditor {

	createGame() {
		$('#building-blocks').on('wheel', (e) => {
			e.preventDefault();

			var delta = e.deltaY > 0 ? 1 : -1;
			var selected = this.getType();
			var newSelected;
			if ( isNaN(parseInt(selected)) ) {
				newSelected = 1;
			}
			else {
				newSelected = selected - -delta;
				newSelected < 1 && (newSelected = 6);
				newSelected > 6 && (newSelected = 1);
			}

			this.handleTypeClick(String(newSelected));
		});
	}

	cellTypes() {
		return {
			"target": 'Target',
			"1": 'Source',
			"2": 'Source',
			"3": 'Source',
			"4": 'Source',
			"5": 'Source',
			"6": 'Source',
		};
	}

	defaultCellType() {
		return 'target';
	}

	createCellTypeCell( type ) {
		if ( !isNaN(parseInt(type)) ) {
			return '<td data-source="' + type + '"></td>';
		}

		return '<td class="' + type + '"></td>';
	}

	exportLevel() {
		const map = [];

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('target') ) {
					row += 't';
				}
				else if ( cell.data('source') ) {
					row += cell.data('source');
				}
				else {
					row += ' ';
				}
			});
			map.push(row);
		});

		const level = {map};
		return level;
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

	setType_target( cell ) {
		if ( cell.hasClass('target') ) {
			cell.removeClass('target');
		}
		else {
			this.m_objGrid.getElements('.target').removeClass('target');
			cell.addClass('target');
		}
	}

	setSource( cell, n ) {
		if ( String(cell.data('source')) == String(n) ) {
			cell.data('source', null);
		}
		else {
			cell.data('source', n);
		}
	}

	setType_1( cell ) {
		this.setSource(cell, 1);
	}

	setType_2( cell ) {
		this.setSource(cell, 2);
	}

	setType_3( cell ) {
		this.setSource(cell, 3);
	}

	setType_4( cell ) {
		this.setSource(cell, 4);
	}

	setType_5( cell ) {
		this.setSource(cell, 5);
	}

	setType_6( cell ) {
		this.setSource(cell, 6);
	}

}

class ZhorSolver {

	static createGrid(table) {
		return table.getElements('tr').map(tr => {
			return tr.getElements('td').map(td => {
				return td.hasClass('target') ? -1 : (parseInt(td.dataset.source) || 0);
			});
		});
	}

	static solveFromPage() {
		const grid = this.createGrid($('#grid'));
		console.log('grid', grid);
		const solver = new this(grid);
		console.log(solver);
	}

	constructor(grid) {
		this.grid = grid;
		this.H = this.grid.length;
		this.W = this.grid[0].length;
		this.moves = this.makeMoves();
	}

	makeMoves() {
		// @todo Make every N move every way it has room to
		// - 7 Ns
		// - 4 directions each
		// - 7! = 5040 orders of 7
		// - moves = 4^7 * 5040 = 8.8M ?

		return [];
	}

}

/*(function() {
	const solver = new ZhorSolver([
		[0,0,0,0,0],
		[0,0,1,0,0],
		[0,1,0,-1,0],
		[0,0,0,0,0],
		[0,0,0,0,0],
	]);
	console.log(solver);
})();*/
