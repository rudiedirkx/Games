
class FoundPattern {
	constructor(coords, dir) {
		this.coords = coords;
		this.dir = dir;
	}
}

class MinesweeperSolver {

	static autoClickDelay = 0;
	static DEBUG = 2;

	constructor(table, sweeper) {
		this.m_table = table;
		this.m_objMinesweeper = sweeper;

		this.resetProps();
	}

	resetProps() {
		this.m_arrBoard = this.mf_GetBoard(this.m_table);
		this.m_arrSides = [ this.m_arrBoard.length, this.m_arrBoard[0].length ];
		this.m_arrKnowns = {};
		this.m_arrClickableNoNoMines = [];
		this.mf_ResetTrace();
	}

	_count(obj) {
		return Object.keys(obj).length;
	}

	_group(obj) {
		var groups = [0, 0];
		for ( var k in obj ) {
			var v = obj[k];
			groups[v]++;
		}

		return groups;
	}

	mf_Trace(name, log) {
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
	}

	mf_ResetTrace() {
		this.m_bTrace = this.constructor.DEBUG > 0;
		this.m_bLogTrace = this.constructor.DEBUG > 1;
		this.m_bLog = this.constructor.DEBUG > 1;
		this.m_arrTrace = {};
	}

	mf_Log() {
		this.m_bLog && console.debug.apply(console, arguments);
	}

	mf_Reset() {
		this.resetProps();
	}

	mf_GetBoard() {
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
	}

	mf_SaveAndMarkAndClickAll(done) {
this.mf_Trace('mf_SaveAndMarkAndClickAll');
		var self = this;
		this.mf_SaveAndMarkAndClick(function(change) {
console.log('DONE 2, ' + (change ? 'with changes' : 'no change'));
			if ( change ) {
				// Reset instance and replay
				self.mf_Reset();
				self.mf_SaveAndMarkAndClickAll(done.bind(this, true));
			}
			else {
console.log('DONE 3');
				done && done.call(this, change);
			}
		});
	}

	mf_SaveAndMarkAndClick(done) {
this.mf_Trace('mf_SaveAndMarkAndClick');
		this.mf_SaveAndMarkAll();
		this.mf_ClickAllNoNoMines(done);
	}

	mf_ClickAllNoNoMines(done) {
this.mf_Trace('mf_SaveAndMarkAndClick');
		var tiles = this.m_table.querySelectorAll('.n');
		this.m_arrClickableNoNoMines = [].slice.call(tiles);

		if (this.m_arrClickableNoNoMines.length) {
console.log('START auto clicking', this.m_arrClickableNoNoMines.length, this.m_arrClickableNoNoMines);
			this.mf_ClickNextNoNoMine(true, done);
		}
		else {
			done && done.call(this, false);
		}
	}

	mf_ClickNextNoNoMine(first, done) {
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
			done && done.call(this, true);
		}
	}

	mf_SaveThisRoundAndMarkAll(done) {
this.mf_Trace('mf_SaveThisRoundAndMarkAll');
		this.mf_SaveMinesThisRound(done);

		this.mf_MarkSavedMines();
		this.mf_MarkNonoMines();

		// this.m_bTrace && console.log(this.m_arrTrace);
	}

	mf_SaveAndMarkAll() {
this.mf_Trace('mf_SaveAndMarkAll');
		this.mf_SaveAllMines();

		this.mf_MarkSavedMines();
		this.mf_MarkNonoMines();

		// this.m_bTrace && console.log(this.m_arrTrace);
	}

	mf_SaveAllMines() {
this.mf_Trace('mf_SaveAllMines');
		// Find all known mines and see if we found any new
		if (this.mf_SaveMinesThisRound()) {
			// Yes, new mines, so so another round
			this.mf_SaveAllMines();
		}
	}

	mf_FilterKnowns(mine) {
		var ids = [];
		for ( var id in this.m_arrKnowns ) {
			if ( this.m_arrKnowns[id] == mine ) {
				ids.push(id);
			}
		}
		return ids;
	}

	/**
	 * Cycle through open fields to save known mines
	 */
	mf_SaveMinesThisRound(done) {
this.mf_Trace('mf_SaveMinesThisRound');
		// Known mines before analyzing
		var knowns = this._group(this.m_arrKnowns);
		var iOldMines = knowns[1];
		var iOldNonos = knowns[0];

		// Analyze all open fields
		for ( var y=0; y<this.m_arrBoard.length; y++ ) {
			for ( var x=0; x<this.m_arrBoard[y].length; x++ ) {
				if ( typeof this.m_arrBoard[y][x] == 'number' && this.m_arrBoard[y][x] > 0 ) {
					this.mf_AnalyseOneField(x, y);
				}
			}
		}

		// Found some mines!
		var iFoundMines = this.mf_FilterKnowns(1).length - iOldMines;

		if ( iFoundMines == 0 ) {
			// Advanced 1: patterns
			var patterns;

			// pattern 1: 1 2 1
			patterns = this.findPatterns([1, 2, 1]);
			for (var i = 0; i < patterns.length; i++) {
				const P = patterns[i];
				const closeds = this.getClosedIfFree(P);
				if (closeds) {
					this.m_arrKnowns[closeds[0].join('_')] = 1;
					this.m_arrKnowns[closeds[1].join('_')] = 0;
					this.m_arrKnowns[closeds[2].join('_')] = 1;
				}
			}

			// pattern 1: 1 2 2 1
			patterns = this.findPatterns([1, 2, 2, 1]);
			for (var i = 0; i < patterns.length; i++) {
				const P = patterns[i];
				const closeds = this.getClosedIfFree(P);
				if (closeds) {
					this.m_arrKnowns[closeds[0].join('_')] = 0;
					this.m_arrKnowns[closeds[1].join('_')] = 1;
					this.m_arrKnowns[closeds[2].join('_')] = 1;
					this.m_arrKnowns[closeds[3].join('_')] = 0;
				}
			}

			iFoundMines = this.mf_FilterKnowns(1).length - iOldMines;
		}

		if ( iFoundMines ) {
			this.mf_EliminateFields();
			var iFoundNonos = this.mf_FilterKnowns(0).length - iOldNonos;

			console.log('Found', iFoundMines, 'new mines, and', iFoundNonos, 'new nonos');
			done && done.call(this, true);
			return true;
		}

		console.log('Found nothing new');
		done && done.call(this, false);
	}

	findPatterns(pattern) {
this.mf_Trace('findPatterns(' + pattern.join(', ') + ')');
		const isPalindrome = pattern.join('_') == pattern.reverse().join('_');
		const searchDirs = /*isPalindrome ? 2 :*/ 4;

		const patterns = [];
		for ( var y = 0; y < this.m_arrBoard.length; y++ ) {
			for ( var x = 0; x < this.m_arrBoard[y].length; x++ ) {
				if ( this.m_arrBoard[y][x] == pattern[0] ) {
					const C = new Coords2D(x, y);
					for (var d = 0; d < searchDirs; d++) {
						const found = this.findPattern(pattern, C, d);
						if (found) {
							patterns.push(found);
						}
					}
				}
			}
		}

		return patterns;
	}

	findPattern(pattern, from, dirIndex) {
		const dirCoord = Coords2D.dir4Coords[dirIndex];
		var C = from;
		const coords = [C];
		for (var i = 1; i < pattern.length; i++) {
			C = C.add(dirCoord);
			const tile = this.mf_GetTile(C.x, C.y);
			if (tile !== pattern[i]) {
				return null;
			}
			coords.push(C);
		}

		return new FoundPattern(coords, dirIndex);
	}

	getClosedIfFree(P) {
		const addLeft = Coords2D.dir4Coords[(P.dir - 1 + 4) % 4];
		const leftCoords = P.coords.map(C => C.add(addLeft));
		const leftTiles = leftCoords.map(C => this.mf_GetTile(C.x, C.y));
		if (!leftTiles.every(tile => tile > -1)) {
			return false;
		}

		const addRight = Coords2D.dir4Coords[(P.dir + 1 + 4) % 4];
		const rightCoords = P.coords.map(C => C.add(addRight));
		const rightTiles = rightCoords.map(C => this.mf_GetTile(C.x, C.y));
		if (!rightTiles.every(tile => tile == -1)) {
			return false;
		}

		return rightCoords;
	}

	/**
	 * Save known mines
	 */
	mf_AnalyseOneField(x, y) {
this.mf_Trace('mf_AnalyseOneField(' + x + ', ' + y + ')');
		var iTile = this.mf_GetTile(x, y);
		var arrSurrounders = this.mf_GetSurroundingTiles(x, y, false);
		var arrClosedTiles = this.mf_GetPotentialMines(arrSurrounders);

		if ( iTile == arrClosedTiles.length ) {
this.mf_Log('(' + x + ', ' + y + ') is solved');
			arrClosedTiles.each(function(coord) {
				var id = coord.join('_');
				this.m_arrKnowns[id] = 1;
			}, this);
			return true;
		}
	}

	/**
	 * Cycle through open fields to save nono's
	 */
	mf_EliminateFields() {
this.mf_Trace('mf_EliminateFields');
		for ( var y=0; y<this.m_arrBoard.length; y++ ) {
			for ( var x=0; x<this.m_arrBoard[y].length; x++ ) {
				var id = 'tile_' + x + '_' + y;
				if ( typeof this.m_arrBoard[y][x] == 'number' && this.m_arrBoard[y][x] > 0 ) {
					this.mf_EliminateFieldsAround(x, y);
				}
			}
		}
	}

	/**
	 * Save nono's around an open field
	 */
	mf_EliminateFieldsAround(x, y) {
this.mf_Trace('mf_EliminateFieldsAround(' + x + ', ' + y + ')');
		var iTile = this.mf_GetTile(x, y);
		var arrSurrounders = this.mf_GetSurroundingTiles(x, y, false);
		var arrMines = this.mf_GetKnownMines(arrSurrounders);

		if ( iTile == arrMines.length ) {
			arrSurrounders.each(function(coord) {
				var id = coord.join('_');
				var iSTile = this.mf_GetTile(id);

				if ( iSTile == -1 && this.m_arrKnowns[id] == null ) {
					this.m_arrKnowns[id] = 0;
				}
			}, this);
		}
	}

	mf_MarkSavedMines() {
this.mf_Trace('mf_MarkSavedMines');
		this.m_table.getElements('.f').removeClass('f');

		this.mf_FilterKnowns(1).each(function(id) {
			var coord = id.split('_');
			var x = coord[0];
			var y = coord[1];
			this.m_table.rows[y].cells[x].addClass('f').data('ms-solved', '');
		}, this);

		if ( this.m_objMinesweeper ) {
			this.m_objMinesweeper.updateFlagCounter();
		}
	}

	mf_MarkNonoMines() {
this.mf_Trace('mf_MarkNonoMines');
		this.m_table.getElements('.n').removeClass('n');

		this.mf_FilterKnowns(0).each(function(id) {
			var coord = id.split('_');
			var x = coord[0];
			var y = coord[1];
			this.m_table.rows[y].cells[x].addClass('n').data('ms-solved', '');
		}, this);
	}

	mf_GetPotentialMines( f_arrCoords ) {
this.mf_Trace('mf_GetPotentialMines(~' + f_arrCoords.length + ')');
		var arrTiles = [];
		f_arrCoords.each(function(coord) {
			var id = coord.join('_');
			var iTile = this.mf_GetTile(id);

			if ( iTile == -1 && this.m_arrKnowns[id] != 0 ) {
				arrTiles.push(coord);
			}
		}, this);

		return arrTiles;
	}

	mf_GetKnownMines( f_arrCoords ) {
this.mf_Trace('mf_GetKnownMines(~' + f_arrCoords.length + ')');
		var arrTiles = [];
		f_arrCoords.each(function(coord) {
			var id = coord.join('_');
			var iTile = this.mf_GetTile(id);

			if ( iTile == -1 && this.m_arrKnowns[id] == 1 ) {
				arrTiles.push(coord);
			}
		}, this);

		return arrTiles;
	}

	mf_GetSurroundingTiles(x, y, returnTiles) {
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
	}

	mf_GetTile(x, y) {
this.mf_Trace('mf_GetTile(' + x + ', ' + y + ')', false);
		if ( typeof x == 'string' && y == null ) {
			var coord = x.split('_');
			x = Number(coord[0]);
			y = Number(coord[1]);
		}

		if ( this.m_arrBoard[y] && this.m_arrBoard[y][x] != null ) {
			return this.m_arrBoard[y][x];
		}

		return false;
	}

}
