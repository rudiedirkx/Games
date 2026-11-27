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
		this.realLevel = false;

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
		const right = this.scale(this.width);
		const bottom = this.scale(this.height);

		for (let i = 0; i <= this.height; i++) {
			const y = this.scale(i);
			this.drawGridLine(new Coords2D(Slither.WHITESPACE, y), new Coords2D(Slither.OFFSET, y));
			this.drawGridLine(new Coords2D(right, y), new Coords2D(this.canvas.width - Slither.WHITESPACE, y));
		}

		for (let i = 0; i <= this.width; i++) {
			const x = this.scale(i);
			this.drawGridLine(new Coords2D(x, Slither.WHITESPACE), new Coords2D(x, Slither.OFFSET));
			this.drawGridLine(new Coords2D(x, bottom), new Coords2D(x, this.canvas.height - Slither.WHITESPACE));
		}

		this.connectors.forEach(conn => {
			if (!this.disableds.includes(conn)) {
				this.drawGridLine(this.scale(conn.from), this.scale(conn.to));
			}
		});
	}

	drawGridLine(from, to) {
		this.drawLine(from, to, {color: '#fff', width: 1});
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

	levelOrder() {
		const ids = [];
		for (const difc of Object.keys(Slither.LEVELS)) {
			const levels = Slither.LEVELS[difc];
			for (let n = 0; n < levels.length; n++) {
				ids.push(difc + '-' + n);
			}
		}
		return ids;
	}

	parseCompleted() {
		const groups = {};
		const raw = localStorage.getItem('slitherCompleted') || '';
		for (const part of raw.split(',')) {
			const dash = part.indexOf('-');
			if (dash < 0) {
				continue;
			}
			const difc = part.slice(0, dash);
			const digits = part.slice(dash + 1);
			const set = new Set();
			for (let i = 0; i + 2 <= digits.length; i += 2) {
				set.add(parseInt(digits.slice(i, i + 2), 10));
			}
			groups[difc] = set;
		}
		return groups;
	}

	writeCompleted(groups) {
		const parts = [];
		for (const difc of Object.keys(Slither.LEVELS)) {
			const set = groups[difc];
			if (!set || set.size === 0) {
				continue;
			}
			const nums = [...set].sort((a, b) => a - b);
			let digits = '';
			for (const n of nums) {
				digits += String(n).padStart(2, '0');
			}
			parts.push(difc + '-' + digits);
		}
		localStorage.setItem('slitherCompleted', parts.join(','));
	}

	markCompleted() {
		const parts = this.levelNum.split('-');
		const difc = parts[0];
		const n = parseInt(parts[1], 10);
		if (isNaN(n)) {
			return;
		}

		const groups = this.parseCompleted();
		if (!groups[difc]) {
			groups[difc] = new Set();
		}
		groups[difc].add(n);
		this.writeCompleted(groups);
	}

	resumeLevel() {
		const order = this.levelOrder();
		const groups = this.parseCompleted();
		const lastIndex = order.length - 1;

		const done = [];
		for (const id of order) {
			const parts = id.split('-');
			const difc = parts[0];
			const n = parseInt(parts[1], 10);
			const set = groups[difc];
			done.push(set ? set.has(n) : false);
		}

		let highest = -1;
		for (let i = 0; i < done.length; i++) {
			if (done[i]) {
				highest = i;
			}
		}

		if (highest < 0) {
			return order[0];
		}
		if (highest < lastIndex) {
			return order[highest + 1];
		}

		let runStart = highest;
		while (runStart > 0 && done[runStart - 1]) {
			runStart--;
		}

		let prevEnd = -1;
		for (let i = 0; i < runStart; i++) {
			if (done[i]) {
				prevEnd = i;
			}
		}
		if (prevEnd < 0) {
			return order[0];
		}
		return order[prevEnd + 1];
	}

	loadLevel(lvl, trySaved = true) {
		const map = this.getLevel(lvl);
		if (!map) return false;

		this.loadMap(map);
		this.setLevelNum(lvl);
		this.realLevel = true;

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

		$('#create-size').value = this.width;

		this.connectors = this.createConnectors();
		this.conditions = this.extractConditions(map);

		this.canvas.width = Slither.OFFSET * 2 + Slither.SQUARE * this.width;
		this.canvas.height = Slither.OFFSET * 2 + Slither.SQUARE * this.height;
	}

	createLevel(size) {
		// const size = 5 + parseInt(Math.random() * 8);
		// const builder = this.generateWithBuilder2(size);
		const builder = this.generateWithBuilder(size);

		const connectors = builder.makeConnections();
		const full = this.clueMap(connectors, size, size);
		const map = SlitherSolver.dig(full, size, size);

		const lvl = this.levelNum;
		this.loadMap(map);
		this.levelNum = lvl;
		this.m_bGameOver = false;
		this.changed = true;
	}

	generateWithBuilder(size) {
		let attempts = 0;
		let builder;
		while (attempts < 1000) {
			attempts++;
			builder = new SlitherBuilder(size, size);
			builder.randomStart();
			while (!builder.done) {
				builder.walk();
			}
			if (builder.success()) {
				break;
			}
		}
console.log('size', size, 'attempts', attempts);
		return builder;
	}

	generateWithBuilder2(size) {
		const builder = new SlitherBuilder2(size, size);
		const ok = builder.build();
console.log('size', size, 'backtracks', builder.backtracks, 'restarts', builder.restarts);
		return builder;
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

	clueMap(connectors, width, height) {
		const present = new Set();
		connectors.forEach(conn => {
			const key = conn.x + ',' + conn.y + ',' + conn.orient;
			present.add(key);
		});

		const rows = [];
		for (let y = 0; y < height; y++) {
			let row = '';
			for (let x = 0; x < width; x++) {
				const edges = [
					x + ',' + y + ',hor',
					x + ',' + y + ',ver',
					x + ',' + (y + 1) + ',hor',
					(x + 1) + ',' + y + ',ver',
				];
				let n = 0;
				for (const key of edges) {
					if (present.has(key)) {
						n++;
					}
				}
				row += String(n);
			}
			rows.push(row);
		}

		return rows;
	}

	conditionsToMap() {
		const grid = [];
		for (let y = 0; y < this.height; y++) {
			grid.push(new Array(this.width).fill(' '));
		}
		for (const cond of this.conditions) {
			grid[cond.y][cond.x] = String(cond.number);
		}
		return grid.map(row => row.join(''));
	}

	connKey(conn) {
		return conn.orient + ':' + conn.x + ',' + conn.y;
	}

	cheat() {
		const map = this.conditionsToMap();
		const clues = SlitherSolver.cluesFromMap(map, this.width, this.height);
		const solver = new SlitherSolver(clues, this.width, this.height);
		const res = solver.countSolutions(1);
console.log('cheat: count', res.count, res.aborted ? '(aborted)' : '');
		if (!res.first) {
			return false;
		}

		const want = new Set();
		for (const e of solver.onEdges(res.first)) {
			const orient = e.type === 'h' ? 'hor' : 'ver';
			want.add(orient + ':' + e.x + ',' + e.y);
		}
		const have = new Set();
		for (const conn of this.enableds) {
			have.add(this.connKey(conn));
		}

		for (const conn of this.connectors) {
			const key = this.connKey(conn);
			const shouldBeOn = want.has(key);
			const currentlyOn = have.has(key);
			if (shouldBeOn === currentlyOn) {
				continue;
			}
			if (shouldBeOn && this.disableds.includes(conn)) {
				this.toggleDisabled(conn);
			}
			this.toggleEnabled(conn);
			return true;
		}

		return false;
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

		if (this.realLevel) {
			localStorage.setItem('slitherAutoSave', this.levelNum + '-' + this.exportBoard());
		}

		this.changed = true;
	}

	toggleEnabled(conn) {
		this.toggleXabled(conn, 'enableds', 'disableds');
		this.startWinCheck(200).then(() => {
			if (this.m_bGameOver) {
				this.changed = true;
				localStorage.removeItem('slitherAutoSave');
				if (this.realLevel) {
					this.markCompleted();
				}
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
			console.time('createLevel');
			this.createLevel(parseInt($('#create-size').value));
			console.timeEnd('createLevel');
		});

		$('#cheat').on('click', e => {
			this.cheat();
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
	static minPoints(width, height) {
		return 0.9 * width * height - Math.max(width, height);
	}

	constructor(width, height) {
		this.width = width;
		this.height = height;
		this.minPoints = SlitherBuilder.minPoints(this.width, this.height);

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
			if (this.stepsBackward > this.minPoints * 2) {
				this.done = true;
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

class SlitherBuilder2 {
	constructor(width, height) {
		this.width = width;
		this.height = height;
		this.cols = width + 1;
		this.rows = height + 1;
		this.minPoints = SlitherBuilder.minPoints(this.width, this.height);

		this.start = null;
		this.path = [];
		this.visited = [];
		this.forward = 0;
		this.backtracks = 0;
		this.restarts = 0;
	}

	build(maxBacktracks) {
		const dotCount = this.cols * this.rows;

		if (maxBacktracks == null) {
			maxBacktracks = 1000 * dotCount;
		}

		this.maxBacktracks = maxBacktracks;

		let restarts = 0;
		while (restarts <= 50) {
			this.resetState();

			const sx = Math.floor(Math.random() * this.cols);
			const sy = Math.floor(Math.random() * this.rows);
			this.start = new Coords2D(sx, sy);
			this.visited[sx][sy] = true;
			this.path.push(this.start);

			if (this.extend(this.start)) {
				this.restarts = restarts;
				return true;
			}

			restarts++;
		}

		this.restarts = restarts;
		return false;
	}

	resetState() {
		this.visited = [];
		for (let x = 0; x < this.cols; x++) {
			const col = new Array(this.rows).fill(false);
			this.visited.push(col);
		}
		this.path = [];
		this.forward = 0;
		this.backtracks = 0;
	}

	shuffled(list) {
		const out = list.slice();
		for (let i = out.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			const t = out[i];
			out[i] = out[j];
			out[j] = t;
		}
		return out;
	}

	inside(C) {
		if (C.x < 0 || C.x >= this.cols) {
			return false;
		}
		if (C.y < 0 || C.y >= this.rows) {
			return false;
		}
		return true;
	}

	extend(cur) {
		if (this.backtracks > this.maxBacktracks) {
			return false;
		}

		const dirs = this.shuffled(Coords2D.dir4Coords);
		for (const d of dirs) {
			const nb = cur.add(d);

			if (!this.inside(nb)) {
				continue;
			}

			if (this.start.equal(nb)) {
				if (this.success()) {
					return true;
				}
				continue;
			}

			if (this.visited[nb.x][nb.y]) {
				continue;
			}

			this.visited[nb.x][nb.y] = true;
			this.path.push(nb);
			this.forward++;

			if (this.extend(nb)) {
				return true;
			}

			this.path.pop();
			this.visited[nb.x][nb.y] = false;
			this.backtracks++;
		}

		return false;
	}

	success() {
		if (this.path.length < this.minPoints) {
			return false;
		}

		const xs = this.path.map(C => C.x);
		const ys = this.path.map(C => C.y);
		if (Math.min(...xs) > 0 || Math.max(...xs) < this.width) {
			return false;
		}
		if (Math.min(...ys) > 0 || Math.max(...ys) < this.height) {
			return false;
		}

		return true;
	}

	makeConnections() {
		const conns = [];
		for (let i = 1; i < this.path.length; i++) {
			const from = this.path[i - 1];
			const to = this.path[i];
			conns.push(SlitherConnector.fromPoints(from, to));
		}

		const first = this.path[0];
		const last = this.path[this.path.length - 1];
		conns.push(SlitherConnector.fromPoints(last, first));

		return conns;
	}

	createEmptyMap() {
		return Array(this.height).fill(0).map(() => ' '.repeat(this.width));
	}
}

class SlitherSolver {
	constructor(clues, width, height) {
		this.width = width;
		this.height = height;
		this.clues = clues;
		this.model = SlitherSolver.buildModel(width, height);
	}

	static buildModel(width, height) {
		const edges = [];
		const edgeIndex = {};

		function vertexId(x, y) {
			return y * (width + 1) + x;
		}
		function cellId(x, y) {
			return y * width + x;
		}
		function addEdge(type, x, y, v1, v2, cells) {
			const id = edges.length;
			const key = type + ':' + x + ',' + y;
			edgeIndex[key] = id;
			edges.push({ id, key, type, x, y, v1, v2, cells });
		}

		for (let y = 0; y <= height; y++) {
			for (let x = 0; x < width; x++) {
				const cells = [];
				if (y - 1 >= 0) {
					cells.push(cellId(x, y - 1));
				}
				if (y <= height - 1) {
					cells.push(cellId(x, y));
				}
				addEdge('h', x, y, vertexId(x, y), vertexId(x + 1, y), cells);
			}
		}
		for (let y = 0; y < height; y++) {
			for (let x = 0; x <= width; x++) {
				const cells = [];
				if (x - 1 >= 0) {
					cells.push(cellId(x - 1, y));
				}
				if (x <= width - 1) {
					cells.push(cellId(x, y));
				}
				addEdge('v', x, y, vertexId(x, y), vertexId(x, y + 1), cells);
			}
		}

		return {
			width,
			height,
			edges,
			edgeIndex,
			vertexCount: (width + 1) * (height + 1),
			cellCount: width * height,
		};
	}

	static cluesFromMap(map, width, height) {
		const clues = new Array(width * height).fill(null);
		for (let y = 0; y < height; y++) {
			for (let x = 0; x < width; x++) {
				const row = map[y] || '';
				const ch = row[x];
				if (ch == null) {
					continue;
				}
				const trimmed = ch.trim();
				if (trimmed === '') {
					continue;
				}
				const n = parseInt(trimmed, 10);
				if (!isNaN(n)) {
					clues[y * width + x] = n;
				}
			}
		}
		return clues;
	}

	static shuffle(a) {
		for (let i = a.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			const t = a[i];
			a[i] = a[j];
			a[j] = t;
		}
		return a;
	}

	static dig(fullClueRows, width, height) {
		const grid = fullClueRows.map(row => row.split(''));
		const cells = [];
		for (let y = 0; y < height; y++) {
			for (let x = 0; x < width; x++) {
				cells.push([x, y]);
			}
		}
		SlitherSolver.shuffle(cells);

		for (const cell of cells) {
			const x = cell[0];
			const y = cell[1];
			if (grid[y][x] === ' ') {
				continue;
			}

			const saved = grid[y][x];
			grid[y][x] = ' ';

			const map = grid.map(row => row.join(''));
			const clues = SlitherSolver.cluesFromMap(map, width, height);
			const solver = new SlitherSolver(clues, width, height);
			const res = solver.countSolutions(2);

			if (res.aborted || res.count !== 1) {
				grid[y][x] = saved;
			}
		}

		return grid.map(row => row.join(''));
	}

	addToOrder(order, seen, key) {
		const id = this.model.edgeIndex[key];
		if (id == null) {
			return;
		}
		if (seen[id]) {
			return;
		}
		seen[id] = true;
		order.push(id);
	}

	edgeOrder() {
		const seen = new Array(this.model.edges.length).fill(false);
		const order = [];
		for (let y = 0; y < this.height; y++) {
			for (let x = 0; x < this.width; x++) {
				this.addToOrder(order, seen, 'h:' + x + ',' + y);
				this.addToOrder(order, seen, 'h:' + x + ',' + (y + 1));
				this.addToOrder(order, seen, 'v:' + x + ',' + y);
				this.addToOrder(order, seen, 'v:' + (x + 1) + ',' + y);
			}
		}
		for (let i = 0; i < this.model.edges.length; i++) {
			if (!seen[i]) {
				seen[i] = true;
				order.push(i);
			}
		}
		return order;
	}

	countSolutions(limit = 2, nodeBudget = 2000000) {
		const model = this.model;
		this.limit = limit;
		this.nodeBudget = nodeBudget;
		this.nodes = 0;
		this.aborted = false;

		this.vOn = new Array(model.vertexCount).fill(0);
		this.vUnd = new Array(model.vertexCount).fill(0);
		this.cOn = new Array(model.cellCount).fill(0);
		this.cUnd = new Array(model.cellCount).fill(0);

		for (const e of model.edges) {
			this.vUnd[e.v1]++;
			this.vUnd[e.v2]++;
			for (const c of e.cells) {
				this.cUnd[c]++;
			}
		}

		this.state = new Array(model.edges.length).fill(0);
		this.order = this.edgeOrder();
		this.result = { count: 0, first: null };

		this.decide(0);

		return { count: this.result.count, first: this.result.first, aborted: this.aborted };
	}

	decide(pos) {
		if (this.aborted) {
			return;
		}
		if (this.result.count >= this.limit) {
			return;
		}
		this.nodes++;
		if (this.nodes > this.nodeBudget) {
			this.aborted = true;
			return;
		}

		if (pos === this.order.length) {
			if (this.isSingleLoop()) {
				this.result.count++;
				if (!this.result.first) {
					this.result.first = this.state.slice();
				}
			}
			return;
		}

		const e = this.model.edges[this.order[pos]];
		for (const on of [false, true]) {
			const ok = this.applyEdge(e, on);
			if (ok) {
				this.state[e.id] = on ? 1 : 0;
				this.decide(pos + 1);
			}
			this.undoEdge(e, on);
			if (this.aborted) {
				return;
			}
			if (this.result.count >= this.limit) {
				return;
			}
		}
	}

	applyEdge(e, on) {
		this.vUnd[e.v1]--;
		this.vUnd[e.v2]--;
		for (const c of e.cells) {
			this.cUnd[c]--;
		}
		if (on) {
			this.vOn[e.v1]++;
			this.vOn[e.v2]++;
			for (const c of e.cells) {
				this.cOn[c]++;
			}
		}
		return this.viable(e);
	}

	undoEdge(e, on) {
		this.vUnd[e.v1]++;
		this.vUnd[e.v2]++;
		for (const c of e.cells) {
			this.cUnd[c]++;
		}
		if (on) {
			this.vOn[e.v1]--;
			this.vOn[e.v2]--;
			for (const c of e.cells) {
				this.cOn[c]--;
			}
		}
	}

	viable(e) {
		for (const v of [e.v1, e.v2]) {
			if (this.vOn[v] > 2) {
				return false;
			}
			if (this.vOn[v] === 1 && this.vUnd[v] === 0) {
				return false;
			}
		}
		for (const c of e.cells) {
			const clue = this.clues[c];
			if (clue == null) {
				continue;
			}
			if (this.cOn[c] > clue) {
				return false;
			}
			if (this.cOn[c] + this.cUnd[c] < clue) {
				return false;
			}
		}
		return true;
	}

	pushAdj(map, vertex, edge) {
		if (!map.has(vertex)) {
			map.set(vertex, []);
		}
		map.get(vertex).push(edge);
	}

	isSingleLoop() {
		const onEdges = [];
		for (const e of this.model.edges) {
			if (this.state[e.id] === 1) {
				onEdges.push(e);
			}
		}
		if (onEdges.length === 0) {
			return false;
		}

		const vAdj = new Map();
		for (const e of onEdges) {
			this.pushAdj(vAdj, e.v1, e);
			this.pushAdj(vAdj, e.v2, e);
		}
		for (const list of vAdj.values()) {
			if (list.length !== 2) {
				return false;
			}
		}

		const startEdge = onEdges[0];
		let comeEdge = startEdge;
		let curVertex = startEdge.v2;
		let visited = 1;
		while (curVertex !== startEdge.v1) {
			const list = vAdj.get(curVertex);
			let nextEdge = list[0];
			if (nextEdge === comeEdge) {
				nextEdge = list[1];
			}
			comeEdge = nextEdge;
			if (nextEdge.v1 === curVertex) {
				curVertex = nextEdge.v2;
			}
			else {
				curVertex = nextEdge.v1;
			}
			visited++;
			if (visited > onEdges.length) {
				return false;
			}
		}
		return visited === onEdges.length;
	}

	onEdges(state) {
		const edges = [];
		for (const e of this.model.edges) {
			if (state[e.id] === 1) {
				edges.push(e);
			}
		}
		return edges;
	}
}
