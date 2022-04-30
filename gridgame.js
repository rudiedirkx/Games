"use strict";

r.extend(Array, {
	chunk(length) {
		const out = [];
		for ( let i = 0; i < this.length; i += length ) {
			out.push(this.slice(i, i + length));
		}
		return out;
	},
});

r.extend(String, {
	chunk: Array.prototype.chunk,
});

this.Element && r.extend(Element, {
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

class Coords3D {
	constructor(x, y, z) {
		this.x = x || 0;
		this.y = y || 0;
		this.z = z || 0;
	}

	equal(C) {
		return this.x == C.x && this.y == C.y && this.z == C.z;
	}

	add(C) {
		return new Coords3D(this.x + C.x, this.y + C.y, this.z + C.z);
	}
}

class RgbColor {
	static DARK = 138;

	constructor(color) {
		this.color = color;
		[this.r, this.g, this.b] = RgbColor.parseColor(color) || RgbColor.throughTemp(color) || RgbColor.invalidColor(color);
	}

	lightness() {
		return ((this.r*299) + (this.g*587) + (this.b*114)) / 1000;
	}

	isDark() {
		return this.lightness() < RgbColor.DARK;
	}

	static isDark(color) {
		return (new this(color)).isDark();
	}

	static parseColor(color) {
		if (color[0] == '#') {
			if (color.length == 7 || color.length == 9) {
				return RgbColor.parse6(color.substr(1, 6));
			}
			if (color.length == 4 || color.length == 5) {
				return RgbColor.parse3(color.substr(1, 3));
			}
		}
		if (color.indexOf('rgb(') == 0 || color.indexOf('rgba(') == 0) {
			return RgbColor.parseTuple(color);
		}
	}

	static throughTemp(color) {
		const el = document.el('div').css('display', 'none').css('border-color', color).appendTo(document.body);
		const color2 = el.css('border-color');
		el.remove();
		return RgbColor.parseColor(color2);
	}

	invalidColor(color) {
		throw new Error(`Invalid color: ${color}`);
	}

	static parse6(color) {
		return [
			parseInt(color.substr(0, 2), 16),
			parseInt(color.substr(2, 2), 16),
			parseInt(color.substr(4, 2), 16),
		];
	}

	static parse3(color) {
		return RgbColor.parse6(`${color[0]}${color[0]}${color[1]}${color[1]}${color[2]}${color[2]}`);
	}

	static parseTuple(color) {
		const m = color.match(/rgba?\((\d+), *(\d+), *(\d+)/);
		return [parseInt(m[1]), parseInt(m[2]), parseInt(m[3])];
	}
}

class GameGrid {
	constructor(width, height) {
		this.width = parseInt(width);
		this.content = height instanceof Uint8Array ? height : new Uint8Array(width * height);
		this.length = this.content.length;
		this.height = this.length / this.width;
	}

	inside(x, y) {
		return x >= 0 && y >= 0 && x < this.width && y < this.height;
	}

	insideC(C) {
		return this.inside(C.x, C.y);
	}

	get(x, y) {
		return this.inside(x, y) ? this.getIndex(y * this.width + x) : null;
	}

	getC(C) {
		return this.inside(C.x, C.y) ? this.getIndex(C.y * this.width + C.x) : null;
	}

	getIndex(i) {
		return this.content[i];
	}

	getRow(y) {
		const content = new Uint8Array(this.width);
		for ( let i = 0; i < this.width; i++ ) {
			content[i] = this.get(i, y);
		}
		return content;
	}

	getCol(x) {
		const content = new Uint8Array(this.height);
		for ( let i = 0; i < this.height; i++ ) {
			content[i] = this.get(x, i);
		}
		return content;
	}

	set(x, y, value) {
		/*if (this.inside(x, y))*/ this.setIndex(y * this.width + x, value);
	}

	setC(C, value) {
		/*if (this.inside(C.x, C.y))*/ this.setIndex(C.y * this.width + C.x, value);
	}

	setIndex(i, value) {
		this.content[i] = value;
	}

	setRow(y, values) {
		const L = Math.min(values.length, this.width);
		for ( let i = 0; i < L; i++ ) {
			this.content[y * this.width + i] = values[i];
		}
	}

	setCol(x, values) {
		const L = Math.min(values.length, this.height);
		for ( let i = 0; i < L; i++ ) {
			this.content[x + i * this.width] = values[i];
		}
	}

	includes(value) {
		return this.content.includes(value);
	}

	serialize64() {
		return [...this.content].map(n => Game.b64(n)).join('');
	}

	copy() {
		return new this.constructor(this.width, this.content.slice(0));
	}

	static unserialize64(width, str) {
		const arr = [...str].map(n => Game.unb64(n));
		return new this(width, new Uint8Array(arr));
	}
}

class Game {

	static b64(int) {
		if (int < 0 || int >= 64) {
			throw new Error('Invalid b64 range');
		}
		const chars = this._b64();
		return chars[int];
	}

	static unb64(char) {
		const chars = this._b64();
		const int = chars.indexOf(char);
		if (int == -1) {
			throw new Error('Invalid b64 range');
		}
		return int;
	}

	static _b64() {
		if (!Game._b64chars) {
			const chars = [];
			for ( let i = 0; i < 26; i++ ) {
				chars.push(String.fromCharCode(65 + i));
			}
			for ( let i = 0; i < 10; i++ ) {
				chars.push(String(i));
			}
			for ( let i = 0; i < 26; i++ ) {
				chars.push(String.fromCharCode(97 + i));
			}
			chars.push('-');
			chars.push('_');
			Game._b64chars = chars.join('');
		}
		return Game._b64chars;
	}

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
		this.m_iTimer = null;
		this.m_iMoves = 0;

		this.setTime('0:00');
		this.setMoves(0);
	}

	statTypes() {
		return {
			time: 'Time',
			moves: 'Moves',
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
		if ( this.m_iTimer !== null ) return;

		this.stopTime();
		this.m_iStartTime = Date.now();
		this.m_iTimer = setInterval(() => this.setTime(this.formatTime(this.getTime())), 50);
	}

	getTime() {
		return this.m_iStartTime ? Math.ceil((Date.now() - this.m_iStartTime) / 1000) : 0;
	}

	stopTime() {
		clearInterval(this.m_iTimer);
		this.m_iTimer = null;
	}

	setTime( time ) {
		var el = $('#stats-time');
		if (el) el.setText(time);
	}

	formatTime( s ) {
		var m = Math.floor(s/60);
		s -= m*60;
		return m + ':' + ('0' + s).slice(-2);
	}

	randomColor() {
		return '#' + ('000000' + (Math.random()*0xFFFFFF<<0).toString(16)).slice(-6);
	}

	log(msg) {
		console.log(msg);

		var log = $('#log');
		if (!log) {
			document.body.append(log = document.el('div', {id: 'log'}));
		}

		if (msg instanceof Coords2D) {
			msg = JSON.stringify(msg);
		}
		log.prepend(document.el('pre').setText(msg));
	}

	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		if ( this.m_iMoves > 0 ) {
			this.startTime();
		}
		var el = $('#stats-moves');
		if (el) el.setText(this.m_iMoves);
	}

	getScore() {
		return {
			time: this.getTime(),
			moves: this.m_iMoves,
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

		setTimeout(() => alert(this.getLoseText()), this.ALERT_DELAY);
	}

	getLoseText() {
		return 'You LOSE :-(';
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

	startWinCheck(delay = 500) {
		return new Promise(resolve => {
			clearTimeout(this.checker);
			this.checker = setTimeout(() => {
				this.winOrLose();
				resolve();
			}, delay);
		});
	}

	isTouchable( element ) {
		return element.matches('canvas') || element.closest('.outside') || getComputedStyle(document.body).touchAction == 'none';
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

	handleGlobalDirection( direction ) {
	}

}

class Drawable {

	constructor( ctx, args = [] ) {
		this.ctx = ctx;
		this.args = args;

		this.fillMethod = 'fill';
		this.strokeMethod = 'stroke';
	}

	fill(color = '#000') {
		this.ctx.fillStyle = color;
		this.ctx[this.fillMethod](...this.args);
		return this;
	}

	stroke(color = '#000', width = 2) {
		this.ctx.strokeStyle = color;
		this.ctx.lineWidth = width;
		this.ctx[this.strokeMethod](...this.args);
		return this;
	}

}

class DrawableRectangle extends Drawable {

	constructor( ctx, args = [] ) {
		super(ctx, args);
		this.fillMethod = 'fillRect';
		this.strokeMethod = 'strokeRect';
	}

}

class CanvasGame extends Game {

	constructor( canvas ) {
		super();

		this.canvas = canvas;
		this.ctx = canvas.getContext('2d');

		this.dragging = null;
		this.draggingObject = null;

		this.changed = true;
	}

	createGame() {
		this.paintingTiming = false;
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

		this.paintingTiming && console.time('paint');
		this.drawStructure();
		this.drawContent();
		this.paintingTiming && console.timeEnd('paint');
		this.changed = false;
	}

	drawStructure() {
	}

	drawContent() {
	}

	drawOn( ctx, callback ) {
		const _ctx = this.ctx;
		this.ctx = ctx;
		callback();
		this.ctx = _ctx;
	}

	drawFill( color ) {
		this.ctx.fillStyle = color;
		this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
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

	prepareCircle( coord, radius ) {
		this.ctx.beginPath();
		this.ctx.arc(coord.x, coord.y, radius, 0, 2*Math.PI);
		this.ctx.closePath();

		return new Drawable(this.ctx);
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

	prepareRectangle( from, to ) {
		const [x, y] = [Math.min(from.x, to.x), Math.min(from.y, to.y)];
		const [w, h] = [Math.abs(from.x - to.x), Math.abs(from.y - to.y)];

		return new DrawableRectangle(this.ctx, [x, y, w, h]);
	}

	prepareRoundedRectangle( from, to, radius ) {
		const [x1, y1] = [Math.min(from.x, to.x), Math.min(from.y, to.y)];
		const [x2, y2] = [Math.max(from.x, to.x), Math.max(from.y, to.y)];

		this.ctx.beginPath();
		this.ctx.arc(x2 - radius, y1 + radius, radius, -Math.PI/2, 0);
		this.ctx.arc(x2 - radius, y2 - radius, radius, 0, Math.PI/2);
		this.ctx.arc(x1 + radius, y2 - radius, radius, Math.PI/2, Math.PI);
		this.ctx.arc(x1 + radius, y1 + radius, radius, Math.PI, -Math.PI/2);
		this.ctx.closePath();

		return new Drawable(this.ctx);
	}

	drawText( coord, text, {size = '20px', color = '#000', style = '', align = null} = {} ) {
		typeof size == 'number' && (size = size + 'px');
		this.ctx.font = `${style} ${size} sans-serif`;
		this.ctx.fillStyle = color;
		if ( align === 'middle' ) {
			this.ctx.textAlign = 'center';
			this.ctx.textBaseline = 'middle';
		}
		this.ctx.fillText(text, coord.x, coord.y);
	}

	fixEventCoordScale(C) {
		return C.multiply(this.canvas.width / this.canvas.offsetWidth);
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

	listenDragAndClick() {
		var touchstart = null;
		var touchpos = null;

		this.canvas.on(['mousedown', 'touchstart'], e => {
			e.preventDefault();
			touchstart = e.subjectXY;

			if (this.handleDragStart(this.fixEventCoordScale(e.subjectXY))) {
				this.dragging = 1;
			}
		});
		this.canvas.on(['mousemove', 'touchmove'], e => {
			touchpos = e.subjectXY;
			if ( this.m_bGameOver ) return;

			if ( this.dragging >= 1 ) {
				this.dragging = 2;
				this.handleDragMove(this.fixEventCoordScale(e.subjectXY));
			}
		});
		document.on(['mouseup', 'touchend'], e => {
			if (touchstart && (!touchpos || touchstart.distance(touchpos) < 10)) {
				this.dragging = 0;
				const C = this.fixEventCoordScale(touchpos || touchstart);
				this.handleDragEnd();
				this.handleClick(C);
				touchstart = null;
				touchpos = null;
				return;
			}

			if ( this.dragging ) {
				this.handleDragEnd();
			}

			this.dragging = 0;
		});
	}

	getObjectAt( coord ) {
	}

	handleClick( coord ) {
		const object = this.getObjectAt(coord);
		if ( object ) {
			this.handleClickObject(object);
		}
	}

	handleClickObject( object ) {
	}

	handleDragStart( coord ) {
		const object = this.getObjectAt(coord);
		if ( object ) {
			this.draggingObject = object;
			return this.handleDragStartObject(object);
		}
	}

	handleDragStartObject( object ) {
	}

	handleDragMove( coord ) {
		const object = this.getObjectAt(coord);
		if ( object && object != this.draggingObject ) {
			this.draggingObject = object;
			this.handleDragMoveObject(object);
		}
	}

	handleDragMoveObject( object ) {
	}

	handleDragEnd() {
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

	listenCellClick( grid ) {
		grid || (grid = this.m_objGrid);
		grid.on('click', '#' + grid.idOrRnd() + ' td', (e) => {
			this.handleCellClick(e.subject);
		});
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

		(this['setType_' + type] || this.setType_).call(this, cell, type);
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
		return [];
	}

}
