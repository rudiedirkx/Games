
function MinesweeperSolver(table, sweeper) {
	// DEBUG //
	// this.DEBUG = true;
	// DEBUG //

	this.m_table = table;
	this.m_arrBoard = this.mf_GetBoard(table);
	this.m_arrSides = [ this.m_arrBoard.length, this.m_arrBoard[0].length ];

	this.m_arrKnownMines = {};
	this.m_arrDefiniteNoNoMines = {};

	this.m_objMinesweeper = sweeper;
	this.m_arrClickableNoNoMines = [];

	this.mf_ResetTrace();
}

MinesweeperSolver.autoClickDelay = 50;

MinesweeperSolver.prototype = {
	_count: function(obj) {
		return Object.keys(obj).length;
	},

	mf_Trace: function(name, log) {
		if ( !this.m_bTrace ) return;

		// Save trace
		if ( !this.m_arrTrace[name] ) {
			this.m_arrTrace[name] = 0;
		}
		this.m_arrTrace[name]++;

		// Log call
		log == null && (log = this.m_bLogTrace);
		if ( log === true ) {
			console.debug(name);
		}
	},

	mf_ResetTrace: function() {
		this.m_bTrace = this.DEBUG;
		this.m_bLogTrace = this.DEBUG;
		this.m_arrTrace = {};
	},

	mf_Reset: function() {
		this.constructor.call(this, this.m_table, this.m_objMinesweeper);
	},

	mf_GetBoard: function() {
		return [].map.call(this.m_table.rows, function(row) {
			return [].map.call(row.cells, function(cell) {
				var cn = cell.className;

				cell.classList.remove('solver');
				cell.classList.remove('f');
				cell.classList.remove('ow');
				cell.classList.remove('n');
				var c = cell.className;
				var n = c.substr(1);

				cell.className = cn;

				return n ? +n : -1;
			});
		});
	},

	mf_SaveAndMarkAndClickAll: function(success, error) {
this.mf_Trace('mf_SaveAndMarkAndClickAll');
		var self = this;
		this.mf_SaveAndMarkAndClick(function(change) {
console.log('DONE 2, ' + (change ? 'with changes' : 'no change'));
			if ( change ) {
				// Reset instance and replay
				self.mf_Reset();
				self.mf_SaveAndMarkAndClickAll();
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
this.mf_Trace('mf_SaveAndMarkAndClick');
		this.mf_SaveAndMarkAll();
		this.mf_ClickAllNoNoMines(done);
	},

	mf_ClickAllNoNoMines: function(done) {
this.mf_Trace('mf_SaveAndMarkAndClick');
		var tiles = this.m_table.querySelectorAll('.n');
		this.m_arrClickableNoNoMines = [].slice.call(tiles);

		if (this.m_arrClickableNoNoMines.length) {
console.log('START auto clicking', this.m_arrClickableNoNoMines.length, this.m_arrClickableNoNoMines);
			this.mf_ClickNextNoNoMine(true, done);
		}
		else {
			done.call(this, false);
		}
	},

	mf_ClickNextNoNoMine: function(first, done) {
this.mf_Trace('mf_ClickNextNoNoMine');
		var tile = this.m_arrClickableNoNoMines.pop();
		if ( tile ) {
			var self = this;
			if ( tile.classList.contains('n') ) {
				setTimeout(function() {
					self.m_objMinesweeper.openField(tile, function() {
						self.mf_ClickNextNoNoMine(false, done);
					});
				}, first ? 0 : self.constructor.autoClickDelay);
			}
			else {
				self.mf_ClickNextNoNoMine(false, done);
			}
		}
		else {
			this.m_objMinesweeper.updateFlagCounter();
console.log('DONE auto clicking');
			if ( done ) {
				done.call(this, true);
			}
		}
	},

	mf_SaveThisRoundAndMarkAll: function() {
this.mf_Trace('mf_SaveThisRoundAndMarkAll');
		this.mf_SaveMinesThisRound();

		this.mf_MarkSavedMines();
		this.mf_MarkNonoMines();

		this.m_bTrace && console.log(this.m_arrTrace);
	},

	mf_SaveAndMarkAll: function() {
this.mf_Trace('mf_SaveAndMarkAll');
		this.mf_SaveAllMines();

		this.mf_MarkSavedMines();
		this.mf_MarkNonoMines();

		this.m_bTrace && console.log(this.m_arrTrace);
	},

	mf_SaveAllMines: function() {
this.mf_Trace('mf_SaveAllMines');
		// Find all known mines and see if we found any new
		if (this.mf_SaveMinesThisRound()) {
			// Yes, new mines, so so another round
			this.mf_SaveAllMines();
		}
	},

	/**
	 * Cycle through open fields to save known mines
	 */
	mf_SaveMinesThisRound: function() {
this.mf_Trace('mf_SaveMinesThisRound');
		// Known mines before analyzing
		var iKnownMines = this._count(this.m_arrKnownMines);
		var iKnownNonos = this._count(this.m_arrDefiniteNoNoMines);

		// Analyze all open fields
		for ( var y=0; y<this.m_arrBoard.length; y++ ) {
			for ( var x=0; x<this.m_arrBoard[y].length; x++ ) {
				if ( typeof this.m_arrBoard[y][x] == 'number' && this.m_arrBoard[y][x] > 0 ) {
					this.mf_AnalyseOneField(x, y);
				}
			}
		}

		// Found some mines!
		var iNewKnownMines = this._count(this.m_arrKnownMines) - iKnownMines;
		if ( iNewKnownMines ) {
			this.mf_EliminateFields();
			var iNewKnownNonos = this._count(this.m_arrDefiniteNoNoMines) - iKnownNonos;

			console.log('Found', iNewKnownMines, 'new mines, and', iNewKnownNonos, 'new nonos');
			return true;
		}

		console.log('Found nothing new');
	},

	/**
	 * Save known mines
	 */
	mf_AnalyseOneField: function(x, y) {
this.mf_Trace('mf_AnalyseOneField(' + x + ', ' + y + ')');
		var iTile = this.mf_GetTile(x, y);
		var arrSurrounders = this.mf_GetSurroundingTiles(x, y, false);
		var iClosedTiles = this.mf_CountClosedTiles(arrSurrounders);

		if ( iTile == iClosedTiles ) {
			arrSurrounders.each(function(coord) {
				var sx = coord[0],
					sy = coord[1],
					id = sx + '_' + sy,
					iSTile = this.mf_GetTile(sx, sy);

				if ( iSTile == -1 && this.m_arrDefiniteNoNoMines[id] == null ) {
					this.m_arrKnownMines[id] = coord;
				}
			}, this);
		}

		return true;
	},

	/**
	 * Cycle through open fields to save nono's
	 */
	mf_EliminateFields: function() {
this.mf_Trace('mf_EliminateFields');
		for ( var y=0; y<this.m_arrBoard.length; y++ ) {
			for ( var x=0; x<this.m_arrBoard[y].length; x++ ) {
				var id = 'tile_' + x + '_' + y;
				if ( typeof this.m_arrBoard[y][x] == 'number' && this.m_arrBoard[y][x] > 0 ) {
					this.mf_EliminateFieldsAround(x, y);
				}
			}
		}
	},

	/**
	 * Save nono's around an open field
	 */
	mf_EliminateFieldsAround: function(x, y) {
this.mf_Trace('mf_EliminateFieldsAround(' + x + ', ' + y + ')');
		var iTile = this.mf_GetTile(x, y);
		var arrSurrounders = this.mf_GetSurroundingTiles(x, y, false);

		var iMinesInSurrounders = 0;
		arrSurrounders.each(function(coord) {
			var sx = coord[0];
			var sy = coord[1];
			var id = sx + '_' + sy;

			if ( this.m_arrKnownMines[id] ) {
				iMinesInSurrounders++;
			}
		}, this);
// console.debug(iMinesInSurrounders, iMinesInSurrounders == iTile);

		if ( iTile == iMinesInSurrounders ) {
			arrSurrounders.each(function(coord) {
				var sx = coord[0];
				var sy = coord[1];
				var id = sx + '_' + sy;
				var iSTile = this.mf_GetTile(sx, sy);

				if ( iSTile == -1 && !this.m_arrKnownMines[id] ) {
					this.m_arrDefiniteNoNoMines[id] = coord;
				}
			}, this);
		}
	},

	mf_MarkSavedMines: function() {
this.mf_Trace('mf_MarkSavedMines');
		this.m_table.getElements('.f').removeClass('f');

		for ( var id in this.m_arrKnownMines ) {
			var coord = this.m_arrKnownMines[id];
			var x = coord[0];
			var y = coord[1];
			this.m_table.rows[y].cells[x].addClass('f').data('ms-solved', '');
		}

		if ( this.m_objMinesweeper ) {
			this.m_objMinesweeper.updateFlagCounter();
		}
	},

	mf_MarkNonoMines: function() {
this.mf_Trace('mf_MarkNonoMines');
		this.m_table.getElements('.n').removeClass('n');

		for ( var id in this.m_arrDefiniteNoNoMines ) {
			var coord = this.m_arrDefiniteNoNoMines[id];
			var x = coord[0];
			var y = coord[1];
			this.m_table.rows[y].cells[x].addClass('n').data('ms-solved', '');
		}
	},

	mf_CountClosedTiles: function( f_arrCoords ) {
this.mf_Trace('mf_CountClosedTiles(~' + f_arrCoords.length + ')');
		var iClosedTiles = 0;
		f_arrCoords.each(function(coord) {
			var x = coord[0];
			var y = coord[1];
			var id = x + '_' + y;
			if ( this.mf_GetTile(x, y) == -1 && this.m_arrDefiniteNoNoMines[id] == null ) {
				iClosedTiles++;
			}
		}, this);

		return iClosedTiles;
	},

	mf_GetSurroundingTiles: function(x, y, returnTiles) {
this.mf_Trace('mf_GetSurroundingTiles(' + x + ', ' + y + ')');
		var arrSurrounders = [];
		for ( var dy=-1; dy<=1; dy++ ) {
			for ( var dx=-1; dx<=1; dx++ ) {
				if ( dx || dy ) {
					var sx = x + dx;
					var sy = y + dy;
					var t = this.mf_GetTile(sx, sy);
					if ( t !== false ) {
						arrSurrounders.push( returnTiles ? t : [sx, sy] );
					}
				}
			}
		}

		return arrSurrounders;
	},

	mf_GetTile: function(x, y) {
this.mf_Trace('mf_GetTile(' + x + ', ' + y + ')', false);
		if ( this.m_arrBoard[y] && this.m_arrBoard[y][x] != null ) {
			return this.m_arrBoard[y][x];
		}

		return false;
	}

};
MinesweeperSolver.prototype.constructor = MinesweeperSolver;
