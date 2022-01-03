"use strict";

class SlitherRoundedCorner extends Coords2D {
	static SIZE = 0.2;

	constructor(C, conn1, conn2) {
		super(C.x, C.y);
		if (conn1.orient == 'ver') {
			this.ver = conn1;
			this.hor = conn2;
		}
		else {
			this.ver = conn2;
			this.hor = conn1;
		}
	}

	get center() {
		const dx = this.ver.x == this.hor.x ? +SlitherRoundedCorner.SIZE : -SlitherRoundedCorner.SIZE;
		const dy = this.ver.y == this.hor.y ? +SlitherRoundedCorner.SIZE : -SlitherRoundedCorner.SIZE;
		return new Coords2D(this.ver.x + dx, this.hor.y + dy);
	}

	get startAngle() {
		if (this.ver.x == this.hor.x) {
			return this.ver.y == this.hor.y ? 0.5 : 0.25;
		}

		return this.ver.y == this.hor.y ? 0.75 : 0.0;
	}
}

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

	get fromTrimmed() {
		return this.orient == 'hor' ? new Coords2D(this.x + SlitherRoundedCorner.SIZE, this.y) : new Coords2D(this.x, this.y + SlitherRoundedCorner.SIZE);
	}

	get toTrimmed() {
		return this.orient == 'hor' ? new Coords2D(this.x + 1 - SlitherRoundedCorner.SIZE, this.y) : new Coords2D(this.x, this.y + 1 - SlitherRoundedCorner.SIZE);
	}

	get enabledBits() {
		return this.orient == 'hor' ? SlitherConnector.ENABLED_HOR : SlitherConnector.ENABLED_VER;
	}

	get disabledBits() {
		return this.orient == 'hor' ? SlitherConnector.DISABLED_HOR : SlitherConnector.DISABLED_VER;
	}

	static fromPoints(a, b) {
		if (a.x == b.x) {
			return new SlitherConnector(a.x, Math.min(a.y, b.y), 'ver');
		}
		return new SlitherConnector(Math.min(a.x, b.x), a.y, 'hor');
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

		// this.paintingTiming = true;

		this.levelNum = 0;
		this.width = 0;
		this.height = 0;
		this.conditions = [];
		this.connectors = [];
		this.enableds = [];
		this.disableds = [];

		// Drawing cache
		this.roundedCorners = [];
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Slither.OFFSET + source * Slither.SQUARE;
	}

	unscale(source) {
		if (source instanceof Coords2D) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			return new Coords2D(this.unscale(source.x), this.unscale(source.y));
		}

		return (source - Slither.OFFSET) / Slither.SQUARE;
	}

	getConnectionColor() {
		if (this.builder) {
			return this.builder.path.length >= this.builder.minPoints ? 'green' : 'red';
		}

		return this.m_bGameOver ? '#555' : '#888';
	}

	drawContent() {
		this.roundedCorners = [];

		this.drawGrid();
		this.drawDisableds();
		this.drawEnableds();
		this.drawRoundedCorners();
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
		this.enableds.forEach(conn => this.drawConnectionStraight(conn));
	}

	drawConnectionStraight(conn) {
		const color = this.getConnectionColor();
		let to = conn.to;
		const toConnections = this.getConnections(to, conn);
		if (toConnections.length == 1 && toConnections[0].orient != conn.orient) {
			this.roundedCorners.push(new SlitherRoundedCorner(to, conn, toConnections[0]));
			to = conn.toTrimmed;
		}

		let from = conn.from;
		const fromConnections = this.getConnections(from, conn);
		if (fromConnections.length == 1 && fromConnections[0].orient != conn.orient) {
			this.roundedCorners.push(new SlitherRoundedCorner(from, conn, fromConnections[0]));
			from = conn.fromTrimmed;
		}

		this.drawLine(this.scale(from), this.scale(to), {color, width: 8});
	}

	drawRoundedCorners() {
		const color = this.getConnectionColor();

		this.ctx.strokeStyle = color;
		this.ctx.lineWidth = 8;

		this.roundedCorners.forEach(corner => {
			const C = this.scale(corner.center);
			const A = corner.startAngle * 2 * Math.PI;
			this.ctx.beginPath();
			this.ctx.arc(C.x, C.y, SlitherRoundedCorner.SIZE * Slither.SQUARE, A, A + Math.PI/2);
			this.ctx.stroke();

			// this.drawCircle(C, SlitherRoundedCorner.SIZE * Slither.SQUARE, {width: 8, color});
		});
	}

	drawDisableds() {
		this.disableds.forEach(conn => this.drawConnection(conn, '#bde4a3'));
	}

	drawConnection(conn, color) {
		this.drawLine(this.scale(conn.from), this.scale(conn.to), {color, width: 8});
	}

	drawDots() {
		const color = this.getConnectionColor();
		for (var y = 0; y <= this.height; y++) {
			for (var x = 0; x <= this.width; x++) {
				const C = new Coords2D(x, y);
				if (!this.roundedCorners.some(corner => corner.equal(C))) {
					this.drawDot(this.scale(C), {color, radius: 4});
				}
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

	getConnections(C, notConn) {
		return this.enableds.filter(conn => {
			return conn != notConn && conn.touchesPoint(C);
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
		for ( let i = 0; i < 200; i++ ) {
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
		const touchings = this.getConnections(fromPoint, notConn);
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

	quickSave() {
		localStorage.setItem('slitherQuickSave', this.exportBoard());
	}

	quickLoad() {
		const saved = localStorage.getItem('slitherQuickSave');
		if (saved) {
			this.loadLevel(this.levelNum, false);
			this.importBoard(saved);
		}
	}

	getAutoSaved() {
		const saved = localStorage.getItem('slitherAutoSave');
		if (!saved) return null;

		const [difc, n, board] = saved.split('-');
		return [difc + '-' + n, board];
	}

	loadFromSaved() {
		const saved = this.getAutoSaved();
		if (!saved) return false;

		const [lvl, board] = saved;
		return this.loadLevel(lvl);
	}

	loadLevel(lvl, trySaved = true) {
		const map = this.getLevel(lvl);
		if (!map) return false;

		this.loadMap(map);
		this.setLevelNum(lvl);

		if (trySaved) {
			const saved = this.getAutoSaved();
			if (saved && saved[0] == this.levelNum) {
				this.importBoard(saved[1]);
			}
		}

		this.changed = true;

		return true;
	}

	loadMap(map) {
		this.reset();

		this.width = Math.max(...map.map(row => row.length));
		this.height = map.length;

		this.connectors = this.createConnectors();
		this.conditions = this.extractConditions(map);

		this.canvas.width = Slither.OFFSET * 2 + Slither.SQUARE * this.width;
		this.canvas.height = Slither.OFFSET * 2 + Slither.SQUARE * this.height;
	}

	createLevel() {
		const size = 5 + parseInt(Math.random() * 8);
		let attempts = 0;
		var builder;
		while (attempts < 100) {
			attempts++;
			builder = new SlitherBuilder(size, size);
			builder.randomStart();
			while (!builder.done) {
				builder.walk();
			}
// console.log(builder);
			if (builder.success()) {
				break;
			}
		}
console.log(size, attempts);

		const lvl = this.levelNum;
		this.loadMap(builder.createEmptyMap());
		this.levelNum = lvl;
		this.m_bGameOver = true;
		this.enableds = builder.makeConnections();
		this.paint();
		this.drawDot(this.scale(builder.start), {color: 'red'});
	}

	createLevelStep() {
		this.m_bGameOver = true;
		if (!this.builder) {
			this.builder = new SlitherBuilder(5, 5);
			this.builder.randomStart();
		}
		else {
			this.builder.walk();
			if (this.builder.done) {
				setTimeout(() => alert(this.builder.success() ? 'SUCCESS' : 'failed'), 100);
			}
		}
console.log(this.builder);

		this.loadMap(this.builder.createEmptyMap());
		this.enableds = this.builder.makeConnections();
		this.paint();
		this.drawDot(this.scale(this.builder.start), {color: 'red'});
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

		localStorage.setItem('slitherAutoSave', this.levelNum + '-' + this.exportBoard());

		this.changed = true;
	}

	toggleEnabled(conn) {
		this.toggleXabled(conn, 'enableds', 'disableds');
		this.startWinCheck(200).then(() => {
			if (this.m_bGameOver) {
				this.changed = true;
				localStorage.removeItem('slitherAutoSave');
			}
		});
	}

	toggleDisabled(conn) {
		this.toggleXabled(conn, 'disableds', 'enableds');
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
		if (this.m_bGameOver) return;

		this.startTime();

		const conn = this.findConnector(C);
		if (conn) {
			this.toggleEnabled(conn);
		}
	}

	handleContextClick(C) {
		if (this.m_bGameOver) return;

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
			const options = $$('#level option');
			const curIndex = options.findIndex(opt => opt.value == this.levelNum);
			if (options[curIndex + D]) {
				this.loadLevel(options[curIndex + D].value);
			}
		});

		$('#save').on('click', e => {
			this.quickSave();
		});
		$('#load').on('click', e => {
			this.quickLoad();
		});

		$('#restart').on('click', e => {
			this.loadLevel(this.levelNum, false);
		});

		$('#create').on('click', e => {
			this.createLevel();
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

class SlitherBuilder {
	constructor(width, height) {
		this.width = width;
		this.height = height;
		this.minPoints = 0.8 * this.width * this.height - Math.max(this.width, this.height);

		this.start = null;
		this.path = [];
		this.pos = null;
		this.options = [];
		this.visited = [];
		this.done = false;
		this.stepsForward = 0;
		this.stepsBackward = 0;
	}

	randomStart() {
		this.moveForward(this.start = new Coords2D(
			Math.floor(Math.random() * (this.width + 1)),
			Math.floor(Math.random() * (this.height + 1))
		));
	}

	moveForward(C) {
		this.stepsForward++;

		this.path.push(C);
		this.moved();
		this.done = (this.stepsForward > 1 && this.start.equal(C)) || this.visited.some(V => V.equal(C));
		if (!this.done && this.path.length > 1) {
			this.visited.push(C);
		}
	}

	moveBackward() {
		this.stepsBackward++;

		this.path.pop();
		this.done = this.path.length == 0;
		if (!this.done) this.moved();
	}

	moved() {
		const C = this.path[this.path.length-1];
		this.pos = C;
		this.options = Coords2D.dir4Coords.map(D => C.add(D)).filter(neig => {
			if (!this.inside(neig)) return false;
			if (this.path.length >= this.minPoints && this.start.equal(neig)) return true;
			if (!this.path.some(P => P.equal(neig))) return true;
			return false;
		});
	}

	inside(C) {
		return C.x >= 0 && C.x <= this.width && C.y >= 0 && C.y <= this.height;
	}

	walk() {
		if (this.options.length) {
			this.moveForward(this.options[parseInt(Math.random() * this.options.length)])
		}
		else {
			while (this.options.length < 2 && !this.done) {
				this.moveBackward();
			}
			if (!this.done) {
				this.options = this.options.filter(O => !this.visited.some(V => V.equal(O)));
				// this.visited = [];
			}
		}
	}

	success() {
		if (this.path.length < this.minPoints) return false;
		if (!this.start.equal(this.path[this.path.length-1])) return false;

		const xs = this.path.map(C => C.x);
		const ys = this.path.map(C => C.y);
		if (Math.min(...xs) > 0 || Math.max(...xs) < this.width) return false;
		if (Math.min(...ys) > 0 || Math.max(...ys) < this.height) return false;

		return true;
	}

	makeConnections() {
		const conns = [];
		for ( let i = 1; i < this.path.length; i++ ) {
			const from = this.path[i-1];
			const to = this.path[i];
			conns.push(SlitherConnector.fromPoints(from, to));
		}
		return conns;
	}

	createEmptyMap() {
		return Array(this.height).fill(0).map(() => ' '.repeat(this.width));
	}
}
