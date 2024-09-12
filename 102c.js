
class Pattern {
	static ANY_NUMBER = 9;

	constructor(pattern) {
		this.pattern = pattern;
	}

	/*public*/ find(solver) {
		return [];
	}

	/*public*/ mark(solver, found) {
	}

	/*protected*/ isClosedIfFree(solver, found) {
		found.closeds = solver.getClosedIfFree(found);
		return found.closeds != null;
	}

	/*protected*/ isMapEdge(solver, found) {
		const D = Coords2D.dir4Coords[found.dir];
		const edge = found.coords[0];
		const next = edge.subtract(D);
		const nextTile = solver.mf_GetTile(next.x, next.y);
		return nextTile === false;
	}

	/*protected*/ markKnowns(solver, found, knowns) {
		for (var i = 0; i < found.closeds.length; i++) {
			if (this.knowns[i] != null) {
				solver.m_arrKnowns[found.closeds[i].join('_')] = this.knowns[i];
			}
		}
	}
}

class ClosedIfFreePattern extends Pattern {
	constructor(pattern, knowns) {
		super(pattern);
		this.knowns = knowns;
	}

	find(solver) {
		const founds0 = solver.findPatterns(this.pattern);
		const founds = founds0.filter(found => {
			return this.isClosedIfFree(solver, found);
		});
		return founds;
	}

	mark(solver, found) {
		this.markKnowns(solver, found, this.knowns);
	}
}

class StartAndClosedIfFreePattern extends Pattern {
	constructor(pattern, knowns) {
		super(pattern);
		this.knowns = knowns;
	}

	find(solver) {
		const founds0 = solver.findPatterns(this.pattern);
		const founds = founds0.filter(found => {
			return this.isMapEdge(solver, found) && this.isClosedIfFree(solver, found);
		});
		return founds;
	}

	mark(solver, found) {
		this.markKnowns(solver, found, this.knowns);
	}
}

class ClosedOpposite232Pattern extends Pattern {
	constructor() {
		super([2, 3, 2]);
		this.knowns = [1, 0, 1];
	}

	find(solver) {
		const founds0 = solver.findPatterns(this.pattern);
		const founds = founds0.filter(found => {
			return this.is232Pattern(solver, found);
		});
		return founds;
	}

	is232Pattern(solver, found) {
		const sides = solver.getPatternSides(found);
		const rightTilesClosed = sides.rightTiles.every(tile => tile == MinesweeperSolver.CLOSED);
		if (rightTilesClosed) {
			found.closeds = sides.rightCoords;
			if (
				(sides.leftTiles[0] > 0 && sides.leftTiles[0] != MinesweeperSolver.CLOSED) &&
				sides.leftTiles[1] == MinesweeperSolver.CLOSED &&
				(sides.leftTiles[2] > 0 && sides.leftTiles[2] != MinesweeperSolver.CLOSED)
			) {
				return true;
			}
		}

		return false;
	}

	mark(solver, found) {
		this.markKnowns(solver, found, this.knowns);
	}
}

class FoundPattern {
	constructor(coords, dir) {
		this.coords = coords;
		this.dir = dir;
	}
}

class FoundPatternSides {
	constructor(leftCoords, leftTiles, rightCoords, rightTiles) {
		this.leftCoords = leftCoords;
		this.leftTiles = leftTiles;
		this.rightCoords = rightCoords;
		this.rightTiles = rightTiles;
	}
}

class MinesweeperSolver {

	static autoClickDelay = 0;
	static DEBUG = 0;

	static CLOSED = 9;

	constructor(table, sweeper) {

		this.m_table = table;
		this.m_objMinesweeper = sweeper;

		this.patterns = [
			new ClosedIfFreePattern([1, 2, 1], [1, 0, 1]),
			new ClosedIfFreePattern([1, 2, 2, 1], [0, 1, 1, 0]),
			new StartAndClosedIfFreePattern([1, 1, Pattern.ANY_NUMBER], [null, null, 0]),
			new ClosedOpposite232Pattern(),
		];

		this.resetProps();
	}

	resetProps() {
console.time('mf_GetBoard');
		this.m_arrBoard = this.mf_GetBoard(this.m_table);
console.timeEnd('mf_GetBoard');
		// this.m_arrSides = [this.m_arrBoard.height, this.m_arrBoard.width];
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

	mf_GetBoardKnowns() {
		const knowns = {};
		const cells = this.m_table.querySelectorAll('.n, .f');
		for ( const cell of cells ) {
			const id = cell.cellIndex + '_' + cell.parentNode.rowIndex;
			knowns[id] = cell.classList.contains('f') ? 1 : 0;
		}
		return knowns;
	}

	mf_GetBoard(table) {
		const grid = new GameGrid(table.rows[0].cells.length, table.rows.length);
		table.getElements('td').forEach((cell, i) => {
			grid.setIndex(i, this.mf_GetCellInt(cell, MinesweeperSolver.CLOSED));
		});
		return grid;
	}

	mf_GetCellInt(cell) {
		var cn = cell.className;

		cell.classList.remove('solver');
		cell.classList.remove('f');
		cell.classList.remove('ow');
		cell.classList.remove('n');
		var c = cell.className;
		var n = c.substr(1);

		cell.className = cn;

		return n ? +n : MinesweeperSolver.CLOSED;
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
		for ( var y=0; y<this.m_arrBoard.height; y++ ) {
			for ( var x=0; x<this.m_arrBoard.width; x++ ) {
				const val = this.m_arrBoard.get(x, y);
				if ( val != null && val > 0 && val != MinesweeperSolver.CLOSED ) {
					this.mf_AnalyseOneField(x, y);
				}
			}
		}

		// Found some certains!
		var iFoundMines = this.mf_FilterKnowns(1).length - iOldMines;
		var iFoundNonos = this.mf_FilterKnowns(0).length - iOldNonos;

		if ( iFoundMines == 0 && iFoundNonos == 0 ) {
			console.log('Found nothing with simple analysis, but advanced:');

			// Advanced 1: patterns
			for (let pattern of this.patterns) {
				const patterns = pattern.find(this);
				for (var i = 0; i < patterns.length; i++) {
					pattern.mark(this, patterns[i]);
				}
			}

			iFoundMines = this.mf_FilterKnowns(1).length - iOldMines;
			iFoundNonos = this.mf_FilterKnowns(0).length - iOldNonos;
		}

		if ( iFoundMines || iFoundNonos ) {
			this.mf_EliminateFields();

			console.log('Found', iFoundMines, 'new mines, and', iFoundNonos, 'new nonos');
			done && done.call(this, true);
			return true;
		}

		console.log('Found nothing new');
		done && done.call(this, false);
	}

	findPatterns(pattern) {
this.mf_Trace('findPatterns(' + pattern.join(', ') + ')');
		// const isPalindrome = pattern.join('_') == pattern.reverse().join('_');
		const searchDirs = /*isPalindrome ? 2 :*/ 4;

		const patterns = [];
		const h = this.m_arrBoard.height;
		const w = this.m_arrBoard.width;
		for ( var y = -1; y <= h; y++ ) {
			for ( var x = -1; x <= w; x++ ) {
				const tile = this.m_arrBoard.get(x, y);
				if ( this.patternTileMatches(pattern[0], tile) ) {
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
			if (!this.patternTileMatches(pattern[i], tile)) {
				return null;
			}
			coords.push(C);
		}

		return new FoundPattern(coords, dirIndex);
	}

	patternTileMatches(patternTile, foundTile) {
		return foundTile === patternTile || (patternTile === Pattern.ANY_NUMBER && foundTile > 0);
	}

	getClosedIfFree(found) {
		const sides = this.getPatternSides(found);

		const leftTilesOpen = sides.leftTiles.every(tile => tile >= 0 && tile != MinesweeperSolver.CLOSED);
		const rightTilesClosed = sides.rightTiles.every(tile => tile == MinesweeperSolver.CLOSED);
		if (leftTilesOpen && rightTilesClosed) {
			return sides.rightCoords;
		}

		const leftTilesClosed = sides.leftTiles.every(tile => tile == MinesweeperSolver.CLOSED);
		const rightTilesOpen = sides.rightTiles.every(tile => tile >= 0 && tile != MinesweeperSolver.CLOSED);
		if (leftTilesClosed && rightTilesOpen) {
			return sides.leftCoords;
		}

		return null;
	}

	getPatternSides(found) {
		const addLeft = Coords2D.dir4Coords[(found.dir - 1 + 4) % 4];
		const leftCoords = found.coords.map(C => C.add(addLeft));
		const leftTiles = leftCoords.map(C => this.mf_GetTile(C.x, C.y));

		const addRight = Coords2D.dir4Coords[(found.dir + 1 + 4) % 4];
		const rightCoords = found.coords.map(C => C.add(addRight));
		const rightTiles = rightCoords.map(C => this.mf_GetTile(C.x, C.y));

		return new FoundPatternSides(leftCoords, leftTiles, rightCoords, rightTiles);
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
		for ( var y=0; y<this.m_arrBoard.height; y++ ) {
			for ( var x=0; x<this.m_arrBoard.width; x++ ) {
				const val = this.m_arrBoard.get(x, y);
				if ( val != null && val > 0 && val != MinesweeperSolver.CLOSED ) {
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

				if ( iSTile == MinesweeperSolver.CLOSED && this.m_arrKnowns[id] == null ) {
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

			if ( iTile == MinesweeperSolver.CLOSED && this.m_arrKnowns[id] != 0 ) {
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

			if ( iTile == MinesweeperSolver.CLOSED && this.m_arrKnowns[id] == 1 ) {
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

		return this.m_arrBoard.get(x, y) ?? false;
	}

	static exportPhp(tbody) {
		var trs = tbody.children;
		var szPhpArray = "\tarray(\n";
		for ( var i=0; i<trs.length; i++ ) {
			szPhpArray += "\t\t'";
			var tds = trs[i].children;
			for ( var j=0; j<tds.length; j++ ) {
				var cls = tds[j].classList;
				if (cls.contains('f')) szPhpArray += 'f';
				else if (cls.contains('n')) szPhpArray += 'n';
				else if (cls.length) szPhpArray += String(cls).substr(1);
				else szPhpArray += ' ';
			}
			szPhpArray += "',\n";
		}
		szPhpArray += "\t),\n";
		return szPhpArray;
	}

}
