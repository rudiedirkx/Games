"use strict";

class SlitherConnector extends Coords2D {
	static ENABLED_HOR = 1;
	static ENABLED_VER = 2;
	static DISABLED_HOR = 4;
	static DISABLED_VER = 8;

	constructor(x, y, orient) {
		super(x, y);
		this.orient = orient;
	}

	touchesPoint(C) {
		return this.from.equal(C) || this.to.equal(C);
	}

	get center() {
		return this.orient == 'hor' ? new Coords2D(this.x + 0.5, this.y) : new Coords2D(this.x, this.y + 0.5);
	}

	get from() {
		return new Coords2D(this.x, this.y);
	}

	get to() {
		return this.orient == 'hor' ? new Coords2D(this.x + 1, this.y) : new Coords2D(this.x, this.y + 1);
	}

	get enabledBits() {
		return this.orient == 'hor' ? SlitherConnector.ENABLED_HOR : SlitherConnector.ENABLED_VER;
	}

	get disabledBits() {
		return this.orient == 'hor' ? SlitherConnector.DISABLED_HOR : SlitherConnector.DISABLED_VER;
	}
}

class SlitherCondition extends Coords2D {
	constructor(x, y, number) {
		super(x, y);
		this.number = number;
	}
}

class Slither extends CanvasGame {

	static LEVELS = [];

	static OFFSET = 20;
	static WHITESPACE = 3;
	static SQUARE = 60;

	reset() {
		super.reset();

		this.levelNum = 0;
		this.width = 0;
		this.height = 0;
		this.conditions = [];
		this.connectors = [];
		this.enableds = [];
		this.disableds = [];
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Slither.OFFSET + source * Slither.SQUARE;
	}

	unscale(source, round = true) {
		if (source instanceof Coords2D) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x, round), this.unscale(source.y, round));
			return this.inside(C) ? C : null;
		}

		const c = (source - Slither.OFFSET) / Slither.SQUARE;
		return round ? Math.round(c) : c;
	}

	inside(coord) {
		return true;
		// return coord.x >= 0 && coord.x < this.width && coord.y >= 0 && coord.y < this.height;
	}

	drawContent() {
		this.drawGrid();
		this.drawDisableds();
		this.drawEnableds();
		this.drawDots();
		this.drawNumbers();

		// this.drawConnectorCenters();
	}

	drawConnectorCenters() {
		this.connectors.forEach(C => this.drawDot(this.scale(C.center)));
	}

	drawGrid() {
		for (var y = Slither.OFFSET; y < this.canvas.height; y += Slither.SQUARE) {
			this.drawLine(new Coords2D(Slither.WHITESPACE, y), new Coords2D(this.canvas.width - Slither.WHITESPACE, y), {color: '#fff', width: 1});
		}

		for (var x = Slither.OFFSET; x < this.canvas.width; x += Slither.SQUARE) {
			this.drawLine(new Coords2D(x, Slither.WHITESPACE), new Coords2D(x, this.canvas.height - Slither.WHITESPACE), {color: '#fff', width: 1});
		}
	}

	drawEnableds() {
		// @todo Rounded corners, animated after toggle on
		this.enableds.forEach(conn => this.drawConnection(conn, '#888'));
	}

	drawDisableds() {
		this.disableds.forEach(conn => this.drawConnection(conn, '#bde4a3'));
	}

	drawConnection(conn, color) {
		this.drawLine(this.scale(conn.from), this.scale(conn.to), {color, width: 8});
	}

	drawDots() {
		for (var y = 0; y <= this.height; y++) {
			for (var x = 0; x <= this.width; x++) {
				this.drawDot(this.scale(new Coords2D(x, y)), {color: '#888', radius: 4});
			}
		}
	}

	drawNumbers() {
		this.ctx.textAlign = 'center';
		this.ctx.textBaseline = 'middle';
		this.conditions.forEach(cond => {
			const C = this.scale(cond.add(new Coords2D(0.5, 0.5)));
			const color = this.countConnections(cond) == cond.number ? 'white' : 'black';
			this.drawText(C, cond.number, {size: 35, color});
		});
	}

	countConnections(C) {
		let num = 0;
		for (let conn of this.enableds) {
			if (conn.equal(C)) {
				num++;
			}
			else if (conn.x == C.x && conn.y == C.y + 1 && conn.orient == 'hor') {
				num++;
			}
			else if (conn.y == C.y && conn.x == C.x + 1 && conn.orient == 'ver') {
				num++;
			}
		}

		return num;
	}

	findSlither() {
		const start = this.enableds[0];
		let conn = start;
		let point = conn.from;
		const path = [conn];
		for ( let i = 0; i < 33; i++ ) {
			const nextConn = this.findSlitherNext(point, conn);
			if (nextConn === start) {
				return path;
			}

			if (!nextConn) {
				return null;
			}

			point = point.equal(nextConn.from) ? nextConn.to : nextConn.from;
			conn = nextConn;
			path.push(nextConn);
		}

		return path;
	}

	findSlitherNext(fromPoint, notConn) {
		const touchings = this.enableds.filter(conn => {
			return conn != notConn && conn.touchesPoint(fromPoint);
		});
		if (touchings.length == 1) {
			return touchings[0];
		}
	}

	haveWon() {
		if (!this.conditions.every(cond => this.countConnections(cond) == cond.number)) {
			return false;
		}

		const slither = this.findSlither();
		return slither && slither.length == this.enableds.length;
	}

	getLevelInt() {
		const [difc, n] = this.levelNum.split('-');
		const difcMult = Object.keys(Slither.LEVELS).indexOf(difc) * 200;
		return difcMult + parseInt(n) + 1;
	}

	getScore() {
		return {
			time: this.getTime(),
			moves: this.enableds.length,
			level: this.getLevelInt(),
		};
	}

	getSaved() {
		const saved = localStorage.getItem('slitherBoard');
		if (!saved) return null;

		const [difc, n, board] = saved.split('-');
		return [difc + '-' + n, board];
	}

	loadFromSaved() {
		const saved = this.getSaved();
		if (!saved) return false;

		const [lvl, board] = saved;
		return this.loadLevel(lvl);
	}

	loadLevel(lvl, trySaved = true) {
		const level = this.getLevel(lvl);
		if (!level) return false;

		this.reset();
		this.setLevelNum(lvl);

		const map = level.board;
		this.width = Math.max(...map.map(row => row.length));
		this.height = map.length;

		this.connectors = this.createConnectors();
		this.conditions = this.extractConditions(map);

		this.canvas.width = Slither.OFFSET * 2 + Slither.SQUARE * this.width;
		this.canvas.height = Slither.OFFSET * 2 + Slither.SQUARE * this.height;

		if (trySaved) {
			const saved = this.getSaved();
			if (saved && saved[0] == this.levelNum) {
				this.importBoard(saved[1]);
			}
		}

		this.changed = true;

		return true;
	}

	getLevel(lvl) {
		const [difc, n] = lvl.split('-');
		return Slither.LEVELS[difc] && Slither.LEVELS[difc][n];
	}

	getDistance(C1, C2) {
		return Math.sqrt(Math.pow(C1.x - C2.x, 2) + Math.pow(C1.y - C2.y, 2));
	}

	findConnector(C) {
		const U = this.unscale(C, false);
		let conn = this.connectors[0];
		let minDistance = this.getDistance(U, conn.center);
		for ( let i = 1; i < this.connectors.length; i++ ) {
			const distance = this.getDistance(U, this.connectors[i].center);
			if (distance < minDistance) {
				minDistance = distance;
				conn = this.connectors[i];
			}
		}

		return minDistance < 0.3 ? conn : null;
	}

	createConnectors() {
		const conns = [];
		for (var y = 0; y <= this.height; y++) {
			for (var x = 0; x <= this.width; x++) {
				if (x < this.width) {
					conns.push(new SlitherConnector(x, y, 'hor'));
				}
				if (y < this.height) {
					conns.push(new SlitherConnector(x, y, 'ver'));
				}
			}
		}

		return conns;
	}

	extractConditions(map) {
		const conditions = [];
		for (var y = 0; y < this.height; y++) {
			for (var x = 0; x < this.width; x++) {
				const n = (map[y] && map[y][x] || '').trim();
				if (n && !isNaN(parseInt(n))) {
					conditions.push(new SlitherCondition(x, y, parseInt(n)));
				}
			}
		}

		return conditions;
	}

	setLevelNum(lvl) {
		this.levelNum = lvl;
		$('#level').value = lvl;
	}

	toggleXabled(conn, inList, checkList) {
		if (this[checkList].includes(conn)) return;

		const i = this[inList].indexOf(conn);
		if (i != -1) {
			this[inList].splice(i, 1);
		}
		else {
			this[inList].push(conn);
		}

		localStorage.setItem('slitherBoard', this.levelNum + '-' + this.exportBoard());

		this.startWinCheck();

		this.changed = true;
	}

	toggleEnabled(conn) {
		return this.toggleXabled(conn, 'enableds', 'disableds');
	}

	toggleDisabled(conn) {
		return this.toggleXabled(conn, 'disableds', 'enableds');
	}

	exportBoard() {
		const board = (new Array((this.width + 1) * (this.height + 1) - 1)).fill(0);

		this.enableds.forEach(conn => {
			const i = (this.width + 1) * conn.y + conn.x;
			board[i] += conn.enabledBits;
		});
		this.disableds.forEach(conn => {
			const i = (this.width + 1) * conn.y + conn.x;
			board[i] += conn.disabledBits;
		});

		return this.serializeBoard(board);
	}

	serializeBoard(board) {
		return board.map(int => String.fromCharCode(int + 65)).join('');
	}

	unserializeBoard(board) {
		return board.split('').map(x => x.charCodeAt(0) - 65);
	}

	importBoard(board) {
		board = this.unserializeBoard(board);

		const connsMap = {};
		this.connectors.forEach(conn => {
			const i = (this.width + 1) * conn.y + conn.x;
			connsMap[`${i}-${conn.orient}`] = conn;
		});

		board.forEach((bits, i) => {
			const hor = `${i}-hor`;
			const ver = `${i}-ver`;

			if (bits & SlitherConnector.ENABLED_HOR) {
				if (connsMap[hor]) this.enableds.push(connsMap[hor]);
			}
			else if (bits & SlitherConnector.DISABLED_HOR) {
				if (connsMap[hor]) this.disableds.push(connsMap[hor]);
			}

			if (bits & SlitherConnector.ENABLED_VER) {
				if (connsMap[ver]) this.enableds.push(connsMap[ver]);
			}
			else if (bits & SlitherConnector.DISABLED_VER) {
				if (connsMap[ver]) this.disableds.push(connsMap[ver]);
			}
		});
	}

	handleClick(C) {
		this.startTime();

		const conn = this.findConnector(C);
		if (conn) {
			this.toggleEnabled(conn);
		}
	}

	handleContextClick(C) {
		this.startTime();

		const conn = this.findConnector(C);
		if (conn) {
			this.toggleDisabled(conn);
		}
	}

	listenControls() {
		this.listenClick();
		this.listenContextClick();

		$('#level').on('change', e => {
			this.loadLevel(e.target.value);
		});
		$$('[data-level-nav]').on('click', e => {
			const D = parseInt(e.target.data('level-nav'));
			this.loadLevel(this.levelNum.replace(/-(\d+)/, m => '-' + (parseInt(m[1]) + D)));
		});

		$('#restart').on('click', e => {
			e.preventDefault();
			this.loadLevel(this.levelNum, false);
		});
	}

	listenContextClick() {
		this.canvas.on('contextmenu', e => {
			e.preventDefault();
			this.handleContextClick(e.subjectXY);
		});
	}

	createGame() {
		const html = Object.keys(Slither.LEVELS).map(difc => {
			const T = Slither.LEVELS[difc].length;
			return `<optgroup label="${difc}">` + Slither.LEVELS[difc].map((x, n) => {
				return `<option value="${difc}-${n}">${difc} ${n+1} / ${T}</option>`;
			}).join('') + '</optgroup>';
		}).join('');
		$('#level').setHTML(html);
	}

}
