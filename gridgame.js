r.extend(Element, {
	idOrRnd: function() {
		return this.id || this.attr('id', '_' + String(Math.random()).substr(2)).id;
	},
});

r.extend(Coords2D, {
	direction: function() {
		if ( Math.abs(this.y) > Math.abs(this.x) ) {
			return this.y > 0 ? 'down' : 'up';
		}
		return this.x > 0 ? 'right' : 'left';
	},
	distance: function(target) {
		return Math.sqrt(Math.pow(Math.abs(this.x - target.x), 2) + Math.pow(Math.abs(this.y - target.y), 2));
	},
	replace: function(a, b) {
		var x = this.x;
		var y = this.y;
		x == a && (x = b);
		y == a && (y = b);
		return new Coords2D(x, y);
	},
});

class Game {

	constructor() {
		this.reset();
	}

	reset() {
		this.m_bGameOver = false;
	}

	win() {
		this.m_bGameOver = true;
		setTimeout(function() {
			alert('You win!');
		}, 100);
	}

	lose() {
		this.m_bGameOver = true;
		setTimeout(function() {
			alert('You lose!');
		}, 100);
	}

	haveWon() {
		return false;
	}

	haveLost() {
		return false;
	}

	winOrLose() {
		if ( this.haveWon() ) {
			this.win();
		}
		else if ( this.haveLost() ) {
			this.lose();
		}
	}

}

class GridGame extends Game {

	constructor( gridElement ) {
		super();

		this.m_objGrid = gridElement;

		this.dirCoords = [
			new Coords2D(0, -1),
			new Coords2D(1, 0),
			new Coords2D(0, 1),
			new Coords2D(-1, 0),
		];

		this.dirNames = [
			'u',
			'r',
			'd',
			'l',
		];

		this.reset();
	}

	reset() {
		super.reset();
		this.setMoves(0);
	}

	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		$('#stats-moves').setText(this.m_iMoves);
	}

	makeWall( cell ) {
		cell.addClass('wall');
		cell.addClass('wall' + Math.ceil(2*Math.random()));
	}

	getCell( coord ) {
		return this.m_objGrid.rows[coord.y] && this.m_objGrid.rows[coord.y].cells[coord.x];
	}

	getCoord( cell ) {
		return new Coords2D(
			cell.cellIndex,
			cell.parentNode.sectionRowIndex
		);
	}

	listenAjax() {
		window.on('xhrStart', function(e) {
			$('#loading').css('visibility', 'visible');
		});
		window.on('xhrDone', function(e) {
			if ( r.xhr.busy == 0 ) {
				$('#loading').css('visibility', 'hidden');
			}
		});
	}

	listenControls() {
	}

	listenGlobalDirection() {
		document.on('keydown', (e) => {
			if ( e.code.match(/^Arrow/) && !e.alt && !e.ctrl ) {
				e.preventDefault();
				var dir = e.code.substr(5).toLowerCase();
				this.handleGlobalDirection(dir);
			}
		});

		var movingStart, movingEnd;
		document.on('touchstart', (e) => {
			e.preventDefault();
			movingStart = e.pageXY;
		});
		document.on('touchmove', (e) => {
			e.preventDefault();
			if ( movingStart ) {
				movingEnd = e.pageXY;
			}
		});
		document.on('touchend', (e) => {
			if ( movingStart && movingEnd ) {
				var distance = movingStart.distance(movingEnd);
				if ( distance > 10 ) {
					var moved = movingEnd.subtract(movingStart);
					var dir = moved.direction();
					this.handleGlobalDirection(dir);
				}
			}
			movingStart = movingEnd = null;
		});
	}

	listenCellClick() {
		this.m_objGrid.on('click', '#' + this.m_objGrid.idOrRnd() + ' td', (e) => {
			this.handleCellClick(e.subject);
		});
	}

	handleGlobalDirection( direction ) {
	}

	handleCellClick( cell ) {
	}

}

class LeveledGridGame extends GridGame {

	reset() {
		super.reset();

		this.m_arrCustomMap = null;
		this.m_arrLastMove = null;

		this.setLevel(0);
	}

	setLevel( f_level ) {
		var iLevel = parseInt(f_level);
		if ( isNaN(iLevel) ) {
			this.m_iLevel = f_level;
			$('#level-nav').hide();
		}
		else {
			this.m_iLevel = iLevel;
			$('#level-nav').show();
		}
		$('#stats-level').setText(this.m_iLevel);
	}

	saveUndoState() {
		this.m_arrLastMove = [this.m_iMoves, this.m_objGrid.innerHTML];
	}

	undoLastMove() {
		if ( this.m_arrLastMove ) {
			this.m_objGrid.setHTML(this.m_arrLastMove[1]);
			this.setMoves(this.m_arrLastMove[0]);
			this.m_arrLastMove = null;
			return true;
		}
	}

	restartLevel() {
		return this.m_arrCustomMap ? this.loadCustomMap(this.m_arrCustomMap) : this.loadLevel(objGame.m_iLevel);
	}

	prevLevel() {
		return this.loadLevel(this.m_iLevel-1);
	}

	nextLevel() {
		return this.loadLevel(this.m_iLevel+1);
	}

	loadLevel( f_level ) {
		r.get('?load_map=' + f_level).on('done', (e, rv) => {
			if ( rv.error || !rv.map ) {
				var error = rv.error ? rv.error : rv;
				alert('Map load error\n\n' + error);
				return;
			}

			this.loadMap(rv);
		});
	}

	loadCustomMap( rv ) {
		console.log('custom level', rv);

		this.loadMap(rv);
		this.m_arrCustomMap = rv;
	}

	loadMap( rv ) {
		this.reset();
		this.setLevel(rv.level || '?');

		this.m_objGrid.empty();

		r.each(rv.map, (row, y) => {
			var nr = this.m_objGrid.insertRow(this.m_objGrid.rows.length);
			r.each(row, (type, x) => {
				var cell = nr.insertCell(nr.cells.length);

				this.createField(cell, type, rv, x, y);
			});
		});

		this.createdMap(rv);
	}

	createField( cell, type, rv, x, y ) {
		cell.setText('?');
	}

	createdMap( rv ) {

	}

}

class GridGameEditor extends GridGame {

	reset() {
	}

	clear() {
		this.m_objGrid.getElements('td').prop('className', '');
	}

	createEditor() {
		this.createMap(12, 12);
		this.createCellTypes();
	}

	cellTypes() {
		return {};
	}

	createCellTypes() {
		var html = '';
		r.each(this.cellTypes(), (label, type) => {
			var activeClass = type == 'wall' ? 'active' : '';
			html += '<tr data-type="' + type + '" class="' + activeClass + '">';
			html += '	' + this.createCellTypeCell(type);
			html += '	<td style="text-align: left">' + label + '</td>';
			html += '</tr>';
		});
		$('#building-blocks').setHTML(html);
	}

	createCellTypeCell( type ) {
		return '<td class="' + type + '"></td>';
	}

	createMap( width, height ) {
		for (var y = 0; y < height; y++) {
			var nr = this.m_objGrid.insertRow(this.m_objGrid.rows.length);
			for (var x = 0; x < width; x++) {
				var cell = nr.insertCell(nr.cells.length);
				this.createdMapCell(cell);
			}
		}
	}

	createdMapCell( cell ) {
	}

	getType() {
		var tr = $('[data-type].active');
		if ( tr ) {
			return tr.data('type');
		}
	}

	listenControls() {
		this.listenCellClick();
		this.listenTypeClick();
	}

	listenTypeClick() {
		$$('[data-type]').on('click', (e) => {
			this.handleTypeClick(e.subject.data('type'));
		});
	}

	handleTypeClick( type ) {
		$$('[data-type].active').removeClass('active');
		$$('[data-type="' + type + '"]').addClass('active');
	}

	handleCellClick( cell ) {
		var type = this.getType();
		if ( !type ) {
			alert('Select a type first');
			return;
		}

		this['setType_' + type](cell);
	}

	setWall( cell ) {
		this.makeWall(cell);
	}

	unsetWall( cell ) {
		cell.removeClass('wall').removeClass('wall1').removeClass('wall2');
	}

	remember( name ) {
		localStorage.setItem(name, JSON.stringify({
			map: this.m_objGrid.getHTML(),
		}));
	}

	forget( name ) {
		localStorage.removeItem(name);
	}

	restore( name ) {
		var saved = localStorage.getItem(name);
		if ( saved ) {
			saved = JSON.parse(saved);
			if ( saved ) {
				this.restoreLevel(saved);
			}
		}
	}

	restoreLevel( level ) {
		this.m_objGrid.setHTML(level.map);
	}

	exportLevel( validate = true ) {
	}

	validateLevel( level ) {
	}

	formatAsPHP( level ) {
		// @todo JS(ON) to PHP?
	}

}
