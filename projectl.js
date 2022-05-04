"use strict";

class Shape {
	static ON = 'x';

	constructor(...grid) {
		this.width = Math.max(...grid.map(line => line.length));
		this.heigth = grid.length;
		this.grid = grid.map(line => `${line}       `.substr(0, this.width));
	}

	equal(other) {
		return this.grid.join('|') == other.grid.join('|');
	}

	rotate() {
		const size = Math.max(this.width, this.heigth);
		const moats = Moats.get(size);
// console.log(moats);

		let grid = new Array(size * size).fill(' ').chunk(size);
		moats.moats.forEach((coords, mi) => {
			// const shift = Math.ceil(Math.sqrt(coords.length)) - 1;
			const shift = size - 1 - mi * 2;
// console.log(mi, 'shift', shift);
			coords.forEach((from, fi) => {
				const orig = this.grid[from.y] && this.grid[from.y][from.x];
				if (orig === Shape.ON) {
					const ti = (fi + shift) % coords.length;
// console.log(mi, fi, ti);
					const to = coords[ti];
					grid[to.y][to.x] = Shape.ON;
				}
			});
		});

		if (this.width > this.heigth) {
			grid = grid.map(line => line.slice(this.width - this.heigth));
		}
		else if (this.heigth > this.width) {
			grid = grid.slice(0, this.width);
		}

		return new Shape(...grid.map(line => line.join('')));
	}

	flip() {
		const grid = new Array(this.width * this.heigth).fill(' ').chunk(this.width);
		for ( let y = 0; y < this.heigth; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				const oppo = this.grid[y][this.width - 1 - x];
				grid[y][x] = oppo == null ? ' ' : oppo;
			}
		}

		return new Shape(...grid.map(line => line.join('')));
	}

	getOffsetCoords(origin) {
		const coords = [];
		for ( let y = 0; y < this.heigth; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				if (this.grid[y][x] === Shape.ON) {
					coords.push((new Coords2D(x, y)).subtract(origin));
				}
			}
		}
		return coords;
	}

	serialize() {
		return this.grid.join('.');
	}

	static unserialize(grid) {
		return new this(...grid.split('.'));
	}
}

class Moats {
	static _cache = {};

	constructor(size) {
		this.size = size;
		this.moats = [];

		const center = Math.floor(this.size / 2);
		for ( let i = 0; i < center; i++ ) {
			this.moats.push(this.make(i));
		}
		if (this.size % 2) {
			this.moats.push([new Coords2D(center, center)]);
		}
	}

	make(offset) {
		const coords = [];
		for ( let i = 1 + offset; i < this.size - offset; i++ ) {
			coords.push(new Coords2D(i, offset));
		}
		for ( let i = 1 + offset; i < this.size - offset; i++ ) {
			coords.push(new Coords2D(this.size - 1 - offset, i));
		}
		for ( let i = this.size - 2 - offset; i >= offset; i-- ) {
			coords.push(new Coords2D(i, this.size - 1 - offset));
		}
		for ( let i = this.size - 2 - offset; i >= offset; i-- ) {
			coords.push(new Coords2D(offset, i));
		}
		return coords;
	}

	static get(size) {
		return this._cache[size] ? this._cache[size] : (this._cache[size] = new Moats(size));
	}
}

class Stone {
	constructor(color, shape, level = 1, rotate = false) {
		this.color = color.trim();
		this.level = level;
		this.shape = shape;
		if (rotate) {
			this.shape = this.shape.rotate();
		}
	}

	get origin() {
		for ( let x = 0; x < this.shape.width; x++ ) {
			if (this.shape.grid[0][x] === Shape.ON) {
				return new Coords2D(x, 0);
			}
		}
	}

	getOffsetCoords() {
		return this.shape.getOffsetCoords(this.origin);
	}
}

class Target {
	constructor(score, shape, stone) {
		this.score = score;
		this.shape = shape;
		this.stone = stone;
	}
}

class SoloProjectL extends Game {

	static EASY_MAX = 2;
	static EASY_TARGETS = 15;
	static HARD_TARGETS = 10;

	static MAX_IN_HAND = 4;
	static MAX_ACTIONS = 3;

	static START_STONES = [1, 1];
	static START_OPPO_COINS = 6;
	static START_COLUMN_COINS = [1, 2, 1];

	constructor($grid, $stones) {
		super();

		this.$grid = $grid;
		this.$stones = $stones;
	}

	reset() {
		super.reset();

		this.waiting = false;

		this.deck = [];
		this.grid = (new Array(9)).fill(null);

		this.setMoves(1);
		this.resetActions();

		this.oppoCoins = 0;
		this.columnCoins = [];
		this.oppoTargets = [];
		this.playerTargets = [];
	}

	createGame() {
		this.SHAPES = this.createShapes();
		this.STONES = this.createStones();
		this.TARGETS = this.createTargets();
	}

	startGame() {
		this.reset();

		this.$stones.setHTML(this.STONES.map((stone, i) => {
			return '<div class="grid-cell">' + this.createStoneHtml(stone, 1) + '</div>';
		}).join(' '));
		$('#max-actions').setText(SoloProjectL.MAX_ACTIONS);

		this.deck = this.createDeck();
		this.fillGrid();
		this.printGrid();

		this.oppoCoins = SoloProjectL.START_OPPO_COINS;
		this.columnCoins = SoloProjectL.START_COLUMN_COINS;

		this.printNums();
	}

	listenControls() {
		$('#finish-round').on('click', e => {
			if (!this.waiting && this.actions > 0) {
				this.finishRound();
			}
		});
		$('#start-master').on('click', e => {
		});
		$('#take-stone').on('click', e => {
			this.waiting || this.takeStone();
		});

		$('#stones').on('click', 'table.stone[data-shape]', e => {
			this.waiting || this.handleStoneLeftClick(e.subject);
		});
		$('#stones').on('contextmenu', 'table.stone[data-shape]', e => {
			e.preventDefault();
			this.waiting || this.handleStoneRightClick(e.subject);
		});

		const context = '.target > tbody > tr > td.shape';
		$('#hand').on('mouseover', context, e => {
			this.handleTargetFillableHoverOn(e.subject);
		});
		$('#hand').on('mouseout', context, e => {
			this.handleTargetFillableHoverOff(e.subject);
		});
		$('#hand').on('click', context, e => {
			this.waiting || this.handleTargetFillableClick(e.subject, e.subject.closest('table'));
		});

		$('#targets').on('click', '.target', e => {
			const fillable = e.target.closest(context);
			if (!fillable) {
				this.waiting || this.handleTargetClick(e.subject);
			}
		});

		const alertScores = scores => {
			alert(scores.join(' + ') + ' = ' + scores.reduce((T, s) => T + s, 0));
		};
		$('#oppo-targets').on('click', e => {
			const scores = this.oppoTargets.map(target => target.score);
			alertScores(scores);
		});
		$('#player-targets').on('click', e => {
			const scores = this.playerTargets.map(table => parseInt(table.data('score')));
			alertScores(scores);
		});
	}



	finishRound() {
		this.setMoves(this.m_iMoves + 1);
		this.unselectStone();
		this.resetActions();

		const emptyColumns = [...this.columnCoins.keys()].filter(i => this.columnCoins[i] == 0);
		if (emptyColumns.length == 0) {
			this.columnCoins = this.columnCoins.map(coins => coins - 1);
			this.printNums();
			return;
		}

		const targets = this.getTargets().filter((target, i) => target && emptyColumns.includes(i % 3));
		const target = targets.sort((a, b) => parseInt(b.data('score')) - parseInt(a.data('score')))[0];
		if (!target) return;

		const targetIndex = this.getTargetIndex(target);

		this.oppoTargets.push(this.grid[targetIndex]);
		this.grid[targetIndex] = null;
		this.printGrid();

		this.waiting = true;
		setTimeout(() => {
			const column = targetIndex % 3;
			this.columnCoins[column] += this.oppoCoins;
			this.oppoCoins = 0;

			for ( let col = 0; col < 3; col++ ) {
				if (col != column && this.columnCoins[col] > 0) {
					this.columnCoins[column]++;
					this.columnCoins[col]--;
				}
			}
			this.printNums();

			setTimeout(() => {
				this.fillGrid();
				this.printGrid();
				this.printNums();
				this.waiting = false;
			}, 500);
		}, 500);
	}

	takeStone() {
		const table = this.getStones()[0];
		table.data('available', parseInt(table.data('available')) + 1);

		this.useAction();
	}

	resetActions() {
		this.actions = -1;
		this.useAction();
	}

	useAction() {
		this.actions++;
		$('#used-actions').setText(this.actions);
	}

	targetIsFull(table) {
		return table.tBodies[0].querySelectorAll('.shape:not(.filled)').length == 0;
	}

	addToHand(i) {
		const target = this.grid[i];
		this.grid[i] = null;
		this.printGrid();

		this.useAction();

		const html = `<div class="grid-cell">${this.createTargetHtml(target)}</div>`;
		const div = document.createElement('div');
		div.setHTML(html);
		this.$grid.getElement('#hand').append(div.firstChild);

		const column = i % 3;

		this.waiting = true;
		setTimeout(() => {
			var wait = 0;
			if (this.columnCoins[column] > 0) {
				this.columnCoins[column]--;
				this.oppoCoins++;
				wait = 500;
				this.printNums();
			}

			setTimeout(() => {
				this.fillGrid();
				this.printGrid();
				this.printNums();
				this.waiting = false;
			}, wait);
		}, 500);
	}

	printNums() {
		$('#deck output').setText(this.deck.length);
		$('#deck').toggleClass('hards', this.deck.length <= 10);
		$('#oppo-targets output').setText(this.oppoTargets.length);
		$('#player-targets output').setText(this.playerTargets.length);

		$('#oppo-coins output').setText(this.oppoCoins);
		this.columnCoins.forEach((num, i) => $(`#col-${i+1}-coins output`).setText(num));
	}

	printGrid() {
		this.$grid.getElement('#targets').setHTML(this.grid.map(target => {
			return `<div class="grid-cell">${target ? this.createTargetHtml(target) : ''}</div>`;
		}).join(' '));
	}

	fillGrid() {
		if (!this.deck.length) return;

		this.grid.forEach((el, i) => {
			if (!el) {
				this.grid[i] = this.deck.pop();
			}
		});
	}

	createDeck() {
		this.TARGETS.sort(_ => Math.random() > 0.5 ? -1 : 1);
		const easys = this.TARGETS.filter(target => target.score <= SoloProjectL.EASY_MAX);
		const hards = this.TARGETS.filter(target => target.score > SoloProjectL.EASY_MAX);
		return [...hards.slice(0, SoloProjectL.HARD_TARGETS), ...easys.slice(0, SoloProjectL.EASY_TARGETS)];
	}

	rotateSerializedShape(str) {
		const shape = Shape.unserialize(str);
		return shape.rotate();
	}

	flipSerializedShape(str) {
		const shape = Shape.unserialize(str);
		return shape.flip();
	}

	replaceStone(table, shape) {
		const title = table.getElement('.shape').textContent;
		const color = table.css('--color');
		table.setHTML(this.createShapeRowsHtml(shape, title, color)).data('shape', shape.serialize());
	}

	getStoneTable() {
		return $('.stone.selected');
	}

	getStone() {
		const table = this.getStoneTable();
		if (table) {
			return new Stone(table.css('--color'), Shape.unserialize(table.data('shape')), parseInt(table.data('level')));
		}
	}

	getCoord(td) {
		return new Coords2D(td.cellIndex, td.parentNode.sectionRowIndex);
	}

	getCell(table, C) {
		if (table.nodeName != 'TBODY') {
			table = table.tBodies[0];
		}

		return table.rows[C.y] && table.rows[C.y].cells[C.x];
	}

	placeStone(stone, targetCells) {
		targetCells.forEach(td => {
			td.addClass('filled');
			td.css('--color', stone.color);
		});
	}

	getStones() {
		return $$('#stones .stone');
	}

	getTargets() {
		return this.$grid.getElements('#targets > *').map(el => el.firstElementChild);
	}

	getTargetIndex(table) {
		return this.getTargets().indexOf(table);
	}

	unselectStone() {
		const selected = this.getStoneTable();
		if (selected) selected.removeClass('selected');
	}



	handleStoneLeftClick(table) {
		if (parseInt(table.data('available')) == 0) return;
		if (this.actions >= SoloProjectL.MAX_ACTIONS) return;

		if (!table.hasClass('selected')) {
			$$('.stone.selected').removeClass('selected');
			table.addClass('selected');
			return;
		}

		this.replaceStone(table, this.rotateSerializedShape(table.data('shape')));
	}

	handleStoneRightClick(table) {
		if (!table.hasClass('selected')) return;

		const flipped = this.flipSerializedShape(table.data('shape'));
		this.replaceStone(table, flipped);
	}

	handleTargetFillableHoverOn(td) {
		$$('td.hover').removeClass('hover');

		const stone = this.getStoneTable();
		if (stone) {
			td.addClass('hover');
		}
	}

	handleTargetFillableHoverOff(td) {
		td.removeClass('hover');
	}

	handleTargetFillableClick(td, table) {
		if (td.hasClass('filled') || !this.getStoneTable()) return;
		if (this.actions >= SoloProjectL.MAX_ACTIONS) return;

		const C = this.getCoord(td);
// console.log(table, td, C);

		const stone = this.getStone();
		const stoneCoords = stone.getOffsetCoords();
// console.log(stone, stoneCoords);

		const stoneTable = this.getStoneTable();
		const stoneIndex = stoneTable.parentNode.elementIndex();

		const targetCoords = stoneCoords.map(S => S.add(C));
		const targetCells = targetCoords.map(C => this.getCell(table, C));
		const validTargetCells = targetCells.filter(td => td && td.hasClass('shape') && !td.hasClass('filled'));
// console.log(targetCoords, targetCells, validTargetCells);
		if (targetCoords.length == validTargetCells.length) {
			stoneTable.data('available', parseInt(stoneTable.data('available')) - 1);
			stoneTable.removeClass('selected');
			this.placeStone(stone, targetCells);

			const usedStones = table.data('used').split(',');
			usedStones[stoneIndex] = parseInt(usedStones[stoneIndex]) + 1;
// console.log(usedStones);
			table.data('used', usedStones.join(','));

			this.useAction();

			if (this.targetIsFull(table)) {
				this.waiting = true;
				setTimeout(() => {
					const usedStones = table.data('used').split(',');
					usedStones[table.data('stone')] = parseInt(usedStones[table.data('stone')]) + 1;
// console.log(usedStones);
					const stoneTables = $$('#stones .stone');
					usedStones.forEach((num, i) => {
						const add = parseInt(usedStones[i]);
						if (add) {
							const stoneTable = stoneTables[i];
							stoneTable.data('available', parseInt(stoneTable.data('available')) + add);
						}
					});

					this.playerTargets.push(table);
					table.parentNode.remove();
					this.printNums();
					this.waiting = false;
				}, 500);
			}
		}
	}

	handleTargetClick(table) {
		if (this.actions >= SoloProjectL.MAX_ACTIONS) return;
		if ($$('#hand > *').length >= SoloProjectL.MAX_IN_HAND) return;

		const i = this.getTargetIndex(table);
		this.addToHand(i);

		this.unselectStone();
	}



	createShapeRowsHtml(shape, title, color) {
		var titled = false;
		const html = [];
		for ( let y = 0; y < shape.heigth; y++ ) {
			html.push(`<tr>`);
			for ( let x = 0; x < shape.width; x++ ) {
				const on = shape.grid[y][x] === Shape.ON;
				html.push(`<td${on ? ' class="shape"' : ''}>${on && !titled ? title : ''}</td>`);
				if (on) {
					titled = true;
				}
			}
			html.push(`</tr>`);
		}
		return html.join('');
	}

	createStoneHtml(stone, context) {
		const i = this.STONES.indexOf(stone);
		const title = context === 1 ? '☼' : '';
		const data = context === 1 ? ` data-shape="${stone.shape.serialize()}" data-available="${SoloProjectL.START_STONES[i] || 0}"` : '';
		const html = [
			`<table class="shape stone" data-level="${stone.level}" style="--color: ${stone.color}; --text: ${RgbColor.isDark(stone.color) ? '#fff' : '#000'}"${data}>`,
			this.createShapeRowsHtml(stone.shape, title),
			`</table>`,
		];
		return html.join('');
	}

	createTargetHtml(target) {
		var titled = false;
		target.shape.width = Math.max(2, target.shape.width);
		const html = [
			`<table class="shape target" data-score="${target.score}" data-stone="${this.STONES.indexOf(target.stone)}" data-used="${this.STONES.map(_ => 0).join(',')}">`,
			`<thead>`,
			`<tr>`,
			`<td class="score" colspan="${target.shape.width}">`,
			`${target.score || '&nbsp;'}`,
			this.createStoneHtml(target.stone, 2),
			`</td>`,
			`</tr>`,
			`</thead>`,
			`<tbody>`,
			this.createShapeRowsHtml(target.shape, ''),
			`</tbody>`,
			`</table>`,
		];
		return html.join('');
	}



	createStones() {
		return [
			new Stone('yellow', this.SHAPES[0], 1),
			new Stone('green', this.SHAPES[1], 2, true),
			new Stone('blue', this.SHAPES[2], 3),
			new Stone('orange', this.SHAPES[3], 3), // area 3
			new Stone('fuchsia', this.SHAPES[4], 4),
			new Stone('red', this.SHAPES[5], 4),
			new Stone('darkred', this.SHAPES[6], 4, true), // area 4
			new Stone('lightblue', this.SHAPES[7], 4, true),
			new Stone('purple', this.SHAPES[8], 4, true),
		];
	}

	createTargets() {
		return [
			new Target(0, this.SHAPES[1], this.STONES[1]),
			new Target(0, this.SHAPES[7], this.STONES[7]),
			new Target(0, this.SHAPES[9], this.STONES[2]),
			new Target(0, this.SHAPES[4], this.STONES[4]),
			new Target(0, this.SHAPES[10], this.STONES[6]),
			new Target(0, this.SHAPES[8], this.STONES[8]),
			new Target(0, this.SHAPES[2], this.STONES[2]),
			new Target(0, this.SHAPES[5], this.STONES[5]),
			new Target(0, this.SHAPES[11], this.STONES[3]),
			new Target(0, this.SHAPES[3], this.STONES[3]),

			new Target(1, this.SHAPES[12], this.STONES[1]),
			new Target(1, this.SHAPES[13], this.STONES[1]),
			new Target(1, this.SHAPES[14], this.STONES[6]),
			new Target(1, this.SHAPES[15], this.STONES[2]),
			new Target(1, this.SHAPES[16], this.STONES[3]),
			new Target(1, this.SHAPES[17], this.STONES[2]),
			new Target(1, this.SHAPES[1], this.STONES[0]),
			new Target(1, this.SHAPES[18], this.STONES[3]),
			new Target(1, this.SHAPES[19], this.STONES[7]),
			new Target(1, this.SHAPES[11], this.STONES[1]),
			new Target(1, this.SHAPES[20], this.STONES[5]),
			new Target(1, this.SHAPES[21], this.STONES[8]),

			new Target(2, this.SHAPES[22], this.STONES[1]),
			new Target(2, this.SHAPES[23], this.STONES[4]),
			new Target(2, this.SHAPES[24], this.STONES[8]),
			new Target(2, this.SHAPES[25], this.STONES[3]),
			new Target(2, this.SHAPES[26], this.STONES[2]),
			new Target(2, this.SHAPES[27], this.STONES[7]),
			new Target(2, this.SHAPES[28], this.STONES[5]),
			new Target(2, this.SHAPES[29], this.STONES[6]),

			new Target(3, this.SHAPES[30], this.STONES[7]),
			new Target(3, this.SHAPES[31], this.STONES[2]),
			new Target(3, this.SHAPES[32], this.STONES[1]),
			new Target(3, this.SHAPES[33], this.STONES[5]),
			new Target(3, this.SHAPES[34], this.STONES[4]),
			new Target(3, this.SHAPES[35], this.STONES[8]),
			new Target(3, this.SHAPES[36], this.STONES[3]),
			new Target(3, this.SHAPES[37], this.STONES[6]),

			new Target(4, this.SHAPES[38], this.STONES[1]),
			new Target(4, this.SHAPES[39], this.STONES[2]),
			new Target(4, this.SHAPES[40], this.STONES[1]),
			new Target(4, this.SHAPES[41], this.STONES[3]),
			new Target(4, this.SHAPES[42], this.STONES[2]),
			new Target(4, this.SHAPES[43], this.STONES[3]),
			new Target(4, this.SHAPES[44], this.STONES[0]),

			new Target(5, this.SHAPES[45], this.STONES[0]),
			new Target(5, this.SHAPES[46], this.STONES[0]),
			new Target(5, this.SHAPES[47], this.STONES[0]),
			new Target(5, this.SHAPES[48], this.STONES[0]),
			new Target(5, this.SHAPES[49], this.STONES[0]),
		];
	}

	createShapes() {
		return [
			new Shape(	'x'),
			new Shape(	'x',
						'x'),
			new Shape(	'xxx'),
			new Shape(	'x',
						'xx'),
			new Shape(	' x',
						'xxx'),
			new Shape(	'xx',
						'xx'),
			new Shape(	' x',
						'xx',
						'x'),
			new Shape(	'x',
						'x',
						'xx'),
			new Shape(	'x',
						'x',
						'x',
						'x'),
			new Shape(	'xx',
						' x'),
			new Shape(	'xx',
						' xx'),
			new Shape(	'x',
						'x',
						'x'),
			new Shape(	' x',
						'xx'),
			new Shape(	'xxx',
						' x'),
			new Shape(	' xx',
						'xxxx'),
			new Shape(	'xxxx'),
			new Shape(	'xx',
						' xx',
						'  x'),
			new Shape(	'xx',
						'xxx'),
			new Shape(	'x x',
						'xxx'),
			new Shape(	'x',
						'xx',
						'xxx'),
			new Shape(	'x',
						'x',
						'x',
						'xxx'),
			new Shape(	'x',
						'x x',
						'xxx'),
			new Shape(	'xxx',
						' x',
						' x'),
			new Shape(	'xx',
						'xxxx',
						'  xx'),
			new Shape(	'x',
						'xxx',
						'xxx',
						' xx'),
			new Shape(	'x',
						'xx',
						'xxx',
						'x'),
			new Shape(	'xx',
						'xx',
						'xxx'),
			new Shape(	' x',
						'xxx',
						'xxx',
						' x'),
			new Shape(	'xxxx',
						' xxxx'),
			new Shape(	' x',
						' xx',
						'xxx',
						' xx'),
			new Shape(	'xx',
						'xx',
						'xxx',
						'xxx'),
			new Shape(	'x',
						'xx',
						'xxx',
						'xxx'),
			new Shape(	'xxx',
						'xxxxx'),
			new Shape(	' x',
						'xx',
						'xx',
						'xxx',
						'xxx'),
			new Shape(	' xx',
						'xxx',
						'xxx',
						'xxx'),
			new Shape(	'xx',
						'xxx',
						'xxx',
						'xx'),
			new Shape(	' xx',
						'xxx',
						'xxxx'),
			new Shape(	'x',
						'xx',
						'xxx',
						'xxxx'),
			new Shape(	'   x',
						'  xx',
						'xxxx',
						'xxxxx'),
			new Shape(	'   xx',
						'  xxx',
						' xxxx',
						'xxxxx'),
			new Shape(	' x',
						' xxx',
						' xxx',
						'xxxxx'),
			new Shape(	'  x',
						' xx',
						' xx',
						' xxx',
						'xxxxx'),
			new Shape(	'    x',
						'xxxxx',
						'xxxxx',
						'  xxx'),
			new Shape(	' xxx',
						'xxxxx',
						'xxxxx'),
			new Shape(	' x',
						' xx',
						'xxx',
						'xxx',
						'xxx'),
			new Shape(	'  x',
						'xxxxx',
						'xxxxx',
						'xxxxx'),
			new Shape(	' xxxx',
						' xxxx',
						'xxxx',
						'xxxx'),
			new Shape(	'  xx',
						'xxxx',
						' xxxx',
						' xxxx',
						' xx'),
			new Shape(	' xxx',
						'xxxxx',
						'xxxxx',
						' xxx'),
			new Shape(	'xxxx',
						' xxxx',
						' xxxx',
						'xxxx'),
		];
	}

}
