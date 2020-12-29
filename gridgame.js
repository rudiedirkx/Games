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
	replace: function(a, b) {
		var x = this.x;
		var y = this.y;
		x == a && (x = b);
		y == a && (y = b);
		return new Coords2D(x, y);
	},
	multiply: function(factor) {
		return new this.constructor(this.x * factor, this.y * factor);
	}
});
Coords2D.jsonReplacer = function() {
	return (k, v) => v instanceof Coords2D ? v.toArray() : v;
};
Coords2D.dir4Coords = [
	new Coords2D( 0, -1),
	new Coords2D( 1,  0),
	new Coords2D( 0,  1),
	new Coords2D(-1,  0),
];
Coords2D.dir4Names = [
	'u',
	'r',
	'd',
	'l',
];
Coords2D.dir8Coords = [
	new Coords2D(-1, -1),
	new Coords2D( 0, -1),
	new Coords2D( 1, -1),
	new Coords2D( 1,  0),
	new Coords2D( 1,  1),
	new Coords2D( 0,  1),
	new Coords2D(-1,  1),
	new Coords2D(-1,  0),
];
Coords2D.dir8Names = [
	'nw',
	'n',
	'ne',
	'e',
	'se',
	's',
	'sw',
	'w',
];

class Game {

	constructor() {
		this.ALERT_DELAY = 50;

		this.createGame();

		this.reset();
	}

	reset() {
		this.m_bGameOver = false;
		this.m_bCheating = false;

		this.checker = 0;
		this.stopTime();
		this.m_iStartTime = 0;
		this.m_iTimer = 0;
		this.setTime('0:00');
	}

	statTypes() {
		return {
			time: 'Time',
		};
	}

	getStatsDelimiter() {
		return '<br>';
	}

	createStats() {
		var delim = this.getStatsDelimiter();
		var html = '';
		r.each(this.statTypes(), (label, key) => {
			html += '<span class="stat-' + key + '"><span class="label">' + label + ':</span> <span class="value" id="stats-' + key + '"></span></span>' + delim;
		});
		$('#stats').setHTML(html);
	}

	createGame() {
		this.createStats();
	}

	startTime() {
		if ( this.m_iStartTime != 0 ) return;

		this.stopTime();
		this.m_iStartTime = Date.now();
		this.m_iTimer = setInterval(() => this.setTime(this.formatTime(this.getTime())), 50);
	}

	getTime() {
		return Math.ceil((Date.now() - this.m_iStartTime) / 1000);
	}

	stopTime() {
		clearInterval(this.m_iTimer);
	}

	setTime( time ) {
		$('#stats-time').setText(time);
	}

	formatTime( s ) {
		var m = Math.floor(s/60);
		s -= m*60;
		return m + ':' + ('0' + s).slice(-2);
	}

	getScore() {
		return {
			time: this.getTime(),
		};
	}

	static saveScore( score ) {
		console.log('saving score', score);
		if ( !score ) return;

		r.post('score.php', r.serialize(score), {globalStart: false}).on('done', (e, rsp) => {
			console.log('saved score?', rsp);
		});
	}

	win() {
		this.stopTime();
		this.m_bGameOver = true;

		if ( !this.m_bCheating ) {
			this.constructor.saveScore(this.getScore());
		}

		setTimeout(() => alert(this.getWinText()), this.ALERT_DELAY);
	}

	getWinText() {
		return 'You WIN :-)';
	}

	lose() {
		this.stopTime();
		this.m_bGameOver = true;

		setTimeout(function() {
			alert('You LOSE :-(');
		}, this.ALERT_DELAY);
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

	startWinCheck() {
		clearTimeout(this.checker);
		this.checker = setTimeout(() => this.winOrLose(), 500);
	}

}

class CanvasGame extends Game {

	constructor( canvas ) {
		super();

		this.canvas = canvas;
		this.ctx = canvas.getContext('2d');

		this.changed = true;
	}

	createGame() {
	}

	startPainting() {
		const render = () => {
			this.changed && this.paint();
			requestAnimationFrame(render);
		};
		render();
	}

	paint() {
		this.canvas.width = 1 * this.canvas.width;

		this.drawStructure();
		this.drawContent();
		this.changed = false;
	}

	drawStructure() {
	}

	drawContent() {
	}

	drawDot( coord, {radius = 3, color = '#000'} = {} ) {
		this.drawCircle(coord, radius, {color, fill: true});
	}

	drawCircle( coord, radius, {width = 2, color = '#000', fill = false} = {} ) {
		fill ? this.ctx.fillStyle = color : this.ctx.strokeStyle = color;
		this.ctx.lineWidth = width;

		this.ctx.beginPath();
		this.ctx.arc(coord.x, coord.y, radius, 0, 2*Math.PI);
		this.ctx.closePath();
		fill ? this.ctx.fill() : this.ctx.stroke();
	}

	drawLine( from, to, {width = 2, color = '#000'} = {} ) {
		this.ctx.lineWidth = width;
		this.ctx.strokeStyle = color;

		this.ctx.beginPath();
		this.ctx.moveTo(from.x, from.y);
		this.ctx.lineTo(to.x, to.y);
		this.ctx.closePath();
		this.ctx.stroke();
	}

	drawRectangle( from, to, {width = 2, color = '#000', fill = false} = {} ) {
		const [x, y] = [Math.min(from.x, to.x), Math.min(from.y, to.y)];
		const [w, h] = [Math.abs(from.x - to.x), Math.abs(from.y - to.y)];

		fill ? this.ctx.fillStyle = color : this.ctx.strokeStyle = color;
		this.ctx.lineWidth = width;

		fill ? this.ctx.fillRect(x, y, w, h) : this.ctx.strokeRect(x, y, w, h);
	}

	drawText( coord, text, {size = '20px', color = '#000', style = ''} = {} ) {
		this.ctx.font = `${style} ${size} sans-serif`;
		this.ctx.fillStyle = color;
		this.ctx.fillText(text, coord.x, coord.y);
	}

	listenControls() {
		this.listenClick();
	}

	listenClick() {
		this.canvas.on('click', (e) => {
			if ( this.dragging == null || this.dragging < 2 ) {
				this.handleClick(e.subjectXY);
			}
		});
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
			if ( this.isTouchable(e.target) ) {
				e.preventDefault();
				movingStart = e.pageXY;
			}
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

	handleClick( coord ) {
	}

	handleCellClick( cell ) {
	}

}

class GridGame extends Game {

	constructor( gridElement ) {
		super();

		if ( !gridElement ) {
			throw new Error('GridGame needs a DOM element');
		}

		this.m_objGrid = gridElement;

		this.dir4Coords = Coords2D.dir4Coords;
		this.dir4Names = Coords2D.dir4Names;
		this.dir8Coords = Coords2D.dir8Coords;
		this.dir8Names = Coords2D.dir8Names;

		this.reset();
	}

	reset() {
		super.reset();
		this.setMoves(0);
	}

	statTypes() {
		return {
			...super.statTypes(),
			moves: 'Moves',
		};
	}

	getScore() {
		const score = super.getScore();
		if ( this.m_iMoves != null ) {
			score.moves = this.m_iMoves;
		}
		return score;
	}

	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		if ( this.m_iMoves > 0 ) {
			this.startTime();
		}
		$('#stats-moves').setText(this.m_iMoves);
	}

	makeWall( cell ) {
		cell.addClass('wall');
		cell.addClass('wall' + Math.ceil(2*Math.random()));
		return cell;
	}

	unsetWall( cell ) {
		cell.removeClass('wall').removeClass('wall1').removeClass('wall2');
		return cell;
	}

	getCell( coord ) {
		return this.m_objGrid.rows[coord.y] && this.m_objGrid.rows[coord.y].cells[coord.x];
	}

	getNextCell( coord, dir ) {
		return this.m_objGrid.rows[coord.y + dir.y] && this.m_objGrid.rows[coord.y + dir.y].cells[coord.x + dir.x];
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

	isTouchable( element ) {
		return element.closest('.outside') || getComputedStyle(document.body).touchAction == 'none';
	}

	listenCellClick( grid ) {
		grid || (grid = this.m_objGrid);
		grid.on('click', '#' + grid.idOrRnd() + ' td', (e) => {
			this.handleCellClick(e.subject);
		});
	}

	handleGlobalDirection( direction ) {
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

	setMaxLevel( f_level ) {
		$('#stats-levels').setText(f_level);
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.m_iLevel,
		};
	}

	saveUndoState() {
		this.m_arrLastMove = [this.m_iMoves, this.m_objGrid.innerHTML];
	}

	undoLastMove() {
		if ( !this.m_bGameOver && this.m_arrLastMove ) {
			this.m_objGrid.setHTML(this.m_arrLastMove[1]);
			this.setMoves(this.m_arrLastMove[0]);
			this.m_arrLastMove = null;
			return true;
		}
	}

	restartLevel() {
		return this.m_arrCustomMap ? this.loadCustomMap(this.m_arrCustomMap) : this.loadLevel(this.m_iLevel);
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
		this.setMaxLevel(rv.levels || '?');

		this.m_objGrid.empty();

		r.each(rv.map, (row, y) => {
			var tr = this.m_objGrid.insertRow();
			r.each(row, (type, x) => {
				var cell = tr.insertCell();

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

	createGame() {
	}

	clear() {
		this.m_objGrid.empty();
		this.createMap(12, 12);
	}

	createEditor() {
		this.createMap(12, 12);
		this.fixMapSize();
		this.createCellTypes();
	}

	fixMapSize() {
		const td = this.m_objGrid.closest('td');
		td.style.minWidth = parseInt(parseFloat(getComputedStyle(td).width) + 5) + 'px';
	}

	cellTypes() {
		return {};
	}

	defaultCellType() {
		return 'wall';
	}

	createCellTypes() {
		var activeCellType = this.defaultCellType();

		var html = '';
		r.each(this.cellTypes(), (label, type) => {
			var activeClass = type == activeCellType ? 'active' : '';
			html += '<tr data-type="' + type + '" class="' + activeClass + '">';
			html += '	' + this.createCellTypeCell(type);
			html += '	<td style="text-align: left">' + label + '</td>';
			html += '</tr>';
		});
		$('#building-blocks').setHTML(html);

		this.createdCellTypes();
	}

	createCellTypeCell( type ) {
		return '<td class="' + type + '"></td>';
	}

	createdCellTypes() {
	}

	createMap( width, height ) {
		for (var y = 0; y < height; y++) {
			var nr = this.m_objGrid.insertRow(this.m_objGrid.rows.length);
			for (var x = 0; x < width; x++) {
				var cell = nr.insertCell(nr.cells.length);
				this.createdMapCell(cell);
			}
		}
		this.createdMap();
	}

	createdMapCell( cell ) {
	}

	createdMap() {
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
		this.listenResizers();
	}

	listenResizers() {
		$$('#level-sizes [data-resize]').on('click', (e) => {
			const resize = e.subject.data('resize');
			const side = resize[1];
			const change = parseInt(resize.replace(/[urdl]/, '1'));
			this.handleResizeClick(side, change);
		});
	}

	handleResizeClick( side, change ) {
		if ( change > 0 ) {
			this.addMapCells(side);
		}
		else {
			this.removeMapCells(side);
		}
	}

	addMapCells( side ) {
		switch ( side ) {
			case 'u':
				return this.addMapRow(this.m_objGrid.insertRow(0));
			case 'r':
				return this.addMapColumn(undefined, this.m_objGrid.rows);
			case 'd':
				return this.addMapRow(this.m_objGrid.insertRow());
			case 'l':
				return this.addMapColumn(0, this.m_objGrid.rows);
		}
	}

	removeMapCells( side ) {
		switch ( side ) {
			case 'u':
				return this.removeMapElements(this.m_objGrid.getElements('tr:first-child'));
			case 'r':
				return this.removeMapElements(this.m_objGrid.getElements('td:last-child'));
			case 'd':
				return this.removeMapElements(this.m_objGrid.getElements('tr:last-child'));
			case 'l':
				return this.removeMapElements(this.m_objGrid.getElements('td:first-child'));
		}
	}

	removeMapElements( els ) {
		const collection = new Elements(els);
		collection.invoke('remove');
	}

	addMapRow( tr ) {
		const width = tr.parentNode.rows[1].cells.length;
		for (var i = 0; i < width; i++) {
			this.createdMapCell(tr.insertCell());
		}
	}

	addMapColumn( where, rows ) {
		for ( var tr of rows ) {
			this.createdMapCell(tr.insertCell(where));
		}
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

	countMapCells( map, cell ) {
		var allCells = map.join('');
		var replacedCells = allCells.replace(new RegExp(cell, 'g'), '');
		return allCells.length - replacedCells.length;
	}

	exportLevel() {
		return {};
	}

	validateLevel( level ) {
	}

	formatAsPHP( level ) {
		// @todo JS(ON) to PHP?
	}

}
