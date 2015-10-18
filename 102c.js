
function MinesweeperSolver(table, sweeper) {
	this.m_table = table;
	this.m_arrBoard = this.mf_GetBoard(table);
	this.m_arrSides = [ this.m_arrBoard.length, this.m_arrBoard[0].length ];

	this.m_arrKnownMines = {};
	this.m_arrDefiniteNoNoMines = {};

	this.m_objMinesweeper = sweeper;
	this.m_arrClickableNoNoMines = [];

console.log('solver', this);

}

MinesweeperSolver.autoClickDelay = 50;

MinesweeperSolver.prototype = {
	// m_arrImgs : {
		// '-1' : 'images/dicht.gif',
		// '0' : 'images/open_0.gif',
		// '1' : 'images/open_1.gif',
		// '2' : 'images/open_2.gif',
		// '3' : 'images/open_3.gif',
		// '4' : 'images/open_4.gif',
		// '5' : 'images/open_5.gif',
		// '6' : 'images/open_6.gif',
		// '7' : 'images/open_7.gif',
		// '8' : 'images/open_8.gif',
		// 'm' : 'images/open_m.gif',
		// 'x' : 'images/open_x.gif',
		// 'f' : 'images/flag.gif',
		// 'w' : 'images/open_w.gif',
		// '?' : 'images/qmark.gif'
	// },

	mf_GetBoard: function() {
		return [].map.call(this.m_table.rows, function(row) {
			return [].map.call(row.cells, function(cell) {
				var cn = cell.className;
				cell.classList.remove('solver');
				cell.classList.remove('f');
				cell.classList.remove('ow');
				var c = cell.className,
					n = c.substr(1);
				cell.className = cn;
				return n ? +n : -1;
			});
		});
	},

	mf_SaveAndMarkAndClickAll: function(success, error) {
		var self = this;
		this.mf_SaveAndMarkAndClick(function(change) {
console.log('DONE 2', change);
			if ( change ) {
				// Reset instance and replay
				// self.m_arrBoard = self.mf_GetBoard(self.m_table);
				// self.mf_SaveAndMarkAndClickAll();

				// Create new instance and play
				var solver = new self.constructor(self.m_table, self.m_objMinesweeper);
				solver.mf_SaveAndMarkAndClickAll(success);
			}
			else {
console.log('DONE 3');
				if ( error ) {
					error.call(self);
				}
				else {
					if ( success ) {
						success.call(self);
					}
				}
			}
		});
	},

	mf_SaveAndMarkAndClick: function(done) {
		this.mf_SaveAndMarkAll();
		this.mf_ClickAllNoNoMines(done);
	},

	mf_ClickAllNoNoMines: function(done) {
		var tiles = this.m_table.querySelectorAll('.ow');
		this.m_arrClickableNoNoMines = [].slice.call(tiles);

console.log('START auto clicking', this.m_arrClickableNoNoMines);
		this.mf_ClickNextNoNoMine(true, done);
	},

	mf_ClickNextNoNoMine: function(first, done) {
		var tile = this.m_arrClickableNoNoMines.pop();
		if ( tile ) {
			var self = this;
			if ( tile.classList.contains('ow') ) {
				setTimeout(function() {
// console.debug('OPEN', tile.cellIndex, tile.parentNode.sectionRowIndex);
					self.m_objMinesweeper.openField(tile, function() {
						self.mf_ClickNextNoNoMine(false, done);
					});
				}, first ? 0 : self.constructor.autoClickDelay);
			}
			else {
// console.debug('SKIP "' + tile.className + '"');
				self.mf_ClickNextNoNoMine(false, done);
			}
		}
		else {
console.log('DONE auto clicking');
// console.debug('----------------');
			if ( done ) {
				done.call(this, !first);
			}
		}
	},

	mf_SaveAndMarkAll: function() {
		this.mf_SaveAllMines();
		this.mf_MarkSavedMines();
		this.mf_MarkNonoMines();
	},

	mf_SaveAllMines: function() {
// console.log('mf_SaveAllMines');
		iKnownMines = sizeof(this.m_arrKnownMines);
		this.mf_SaveAllMinesThisRound();
		if ( sizeof(this.m_arrKnownMines) > iKnownMines ) {
			this.mf_EliminateFields();
			return this.mf_SaveAllMines();
		}
	},

	mf_SaveAllMinesThisRound: function() {
// console.log('mf_SaveAllMinesThisRound');
		for ( var y=0; y<this.m_arrBoard.length; y++ ) {
			for ( var x=0; x<this.m_arrBoard[y].length; x++ ) {
				if ( typeof this.m_arrBoard[y][x] == 'number' && this.m_arrBoard[y][x] > 0 ) {
					this.mf_AnalyseOneField(x, y);
				}
			}
		}
	},

	mf_AnalyseOneField: function(x, y) {
// console.log('mf_AnalyseOneField');
		szTile = this.mf_GetTile(x, y);
		arrSurrounders = this.mf_GetSurroundingTiles(x, y, false);
		iClosedTiles = this.mf_CountClosedTiles(arrSurrounders);

		if ( szTile == iClosedTiles ) {
			arrSurrounders.each(function(coord) {
				var nx = coord[0],
					ny = coord[1],
					id = nx + '_' + ny,
					t = this.mf_GetTile(nx, ny);

				if ( -1 == t && this.m_arrDefiniteNoNoMines[id] == null ) {
					this.m_arrKnownMines[id] = coord;
				}
			}, this);
		}

		return true;
	},

	mf_EliminateFields: function() {
// console.log('mf_EliminateFields');
		for ( var y=0; y<this.m_arrBoard.length; y++ ) {
			for ( var x=0; x<this.m_arrBoard[y].length; x++ ) {
				id = 'tile_' + x + '_' + y;
				if ( typeof this.m_arrBoard[y][x] == 'number' && this.m_arrBoard[y][x] > 0 ) {
					this.mf_EliminateFieldsAround(x, y);
				}
			}
		}
		return true;
	},

	mf_EliminateFieldsAround: function(x, y) {
// console.log('mf_EliminateFieldsAround');
		szTile = this.mf_GetTile(x, y);
		arrSurrounders = this.mf_GetSurroundingTiles(x, y, false);

		iMinesInSurrounders = 0;
		arrSurrounders.each(function(coord) {
			var nx = coord[0],
				ny = coord[1],
				id = nx + '_' + ny;

			if ( this.m_arrKnownMines[id] ) {
				iMinesInSurrounders++;
			}
		}, this);
// console.log(iMinesInSurrounders, iMinesInSurrounders == szTile);

		if ( szTile == iMinesInSurrounders ) {
			arrSurrounders.each(function(coord) {
				var nx = coord[0],
					ny = coord[1],
					id = nx + '_' + ny,
					t = this.mf_GetTile(nx, ny);

				if ( t == -1 && !this.m_arrKnownMines[id] ) {
					this.m_arrDefiniteNoNoMines[id] = coord;
				}
			}, this);
		}
	},

	mf_MarkSavedMines: function() {
// console.log('mf_MarkSavedMines');
		for ( id in this.m_arrKnownMines ) {
			var coord = this.m_arrKnownMines[id],
				x = coord[0],
				y = coord[1];
			this.m_table.rows[y].cells[x].className += ' solver f';
		}
	},

	mf_MarkNonoMines: function() {
// console.log('mf_MarkNonoMines');
		for ( id in this.m_arrDefiniteNoNoMines ) {
			var coord = this.m_arrDefiniteNoNoMines[id],
				x = coord[0],
				y = coord[1];
			this.m_table.rows[y].cells[x].className += ' solver ow';
		}
	},

	mf_CountClosedTiles: function( f_arrCoords ) {
// console.log('mf_CountClosedTiles');
		iClosedTiles = 0;
		f_arrCoords.each(function(coord) {
			var x = coord[0],
				y = coord[1],
				id = x + '_' + y;
			if ( -1 == this.mf_GetTile(x, y) && this.m_arrDefiniteNoNoMines[id] == null ) {
				iClosedTiles++;
			}
		}, this);

		return iClosedTiles;
	},

	mf_GetCoords: function( f_objTile ) {
// console.log('mf_GetCoords');
		c = f_objTile.id.split("_");
		return [ c[1], c[2] ];
	},

	mf_GetSurroundingTiles: function(x, y, returnTiles) {
// console.log('mf_GetSurroundingTiles');
		var arrSurrounders = [];
		for ( var dy=-1; dy<=1; dy++ ) {
			for ( var dx=-1; dx<=1; dx++ ) {
				if ( dx || dy ) {
					var nx = x + dx,
						ny = y + dy,
						t = this.mf_GetTile(nx, ny);
					if ( t !== false ) {
						arrSurrounders.push( returnTiles ? t : [nx, ny] );
					}
				}
			}
		}

		return arrSurrounders;
	},

	mf_GetTile: function(x, y) {
// console.log('mf_GetTile');
		if ( this.m_arrBoard[y] && x in this.m_arrBoard[y] ) {
			return this.m_arrBoard[y][x];
		}

		return false;
	}

};
MinesweeperSolver.prototype.constructor = MinesweeperSolver;

function sizeof(source) {
	return source instanceof Array ? source.length : Object.keys(source).length;
}
