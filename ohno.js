"use strict";

class Ohno extends GridGame {

	reset() {
		super.reset();

		this.size = 7;

		this.WHITE = 'w';
		this.GRAY = 'g';
		this.RED = 'r';
		this.BLUE = 'b';
	}

	createMap(grid) {
		this.size = grid.length;

		this.m_objGrid.empty();
		for ( let y = 0; y < this.size; y++ ) {
			const tr = this.m_objGrid.insertRow();
			for ( let x = 0; x < this.size; x++ ) {
				const t = grid[y][x];
				const td = tr.insertCell();
				const cell = document.createElement('span');
				td.append(cell);
				if (t == 'x') {
					td.data('closed', '1');
				}
				else if (!isNaN(parseInt(t))) {
					td.data('required', t);
					cell.setText(t);
				}
			}
		}
	}

	createEmpty(size) {
		size || (size = this.size);
		const grid = Array.from(Array(size)).map(x => ' '.repeat(size));
		return this.createMap(grid);
	}

	listenControls() {
		this.listenImageDrop();

		this.listenCellClick();

		$('#cheat').on('click', e => this.cheatOneRound());
	}

	handleCellClick(cell) {
		if (cell.data('closed') || cell.data('required')) return;

		if (cell.hasClass('active')) {
			cell.removeClass('active');
			cell.addClass('closed');
		}
		else if (cell.hasClass('closed')) {
			cell.removeClass('closed');
		}
		else {
			cell.addClass('active');
		}

		this.startWinCheck();
	}

	haveWon() {
		const cells = this.m_objGrid.getElements('td');
		const empty = cells.filter(cell => !cell.data('closed') && !cell.data('required') && !cell.matches('.active, .closed'));
		if (empty.length) return false;

		const starts = cells.filter(cell => cell.data('required'));
		return starts.every(cell => this.allActiveNeighbors(cell).length == parseInt(cell.data('required')));
	}

	allActiveNeighbors(cell) {
		return this.dir4Coords.map(C => this.activeNeighborsToward(cell, C)).flat(1);
	}

	activeNeighborsToward(cell, dir) {
		var curr = cell;
		var next;

		const list = [];
		while (this.isActiveNeight(next = this.getNextCell(this.getCoord(curr), dir))) {
			list.push(next);
			curr = next;
		}

		return list;
	}

	isActiveNeight(cell) {
		return cell && (cell.data('required') || cell.hasClass('active'));
	}

	cheatOneRound() {
		this.m_bCheating = true;

		const solver = OhnoSolver.fromDom(this.m_objGrid);
	}

	listenImageDrop() {
		// ['drag', 'dragend', 'dragenter', 'dragleave', 'dragover', 'dragstart', 'drop'].forEach(t => {
		// 	document.on(t, e => {
		// 		e.preventDefault();
		// 		console.log(e.type);
		// 	});
		// });

		document.on('dragover', e => {
			e.preventDefault();
		});
		document.on('drop', e => {
			e.preventDefault();
			const file = e.data.files[0];
// console.log(file);
			this.fileToPixels(file).then(pxGrid => this.pixelsToGrid(pxGrid));
		});
	}

	fileToPixels(file) {
		return new Promise(resolve => {
			const img = document.createElement('img');
			img.src = URL.createObjectURL(file);
document.body.append(img);
// console.log(img);
			img.onload = e => {
				const width = img.width;
				const height = img.height;
				const canvas = document.createElement('canvas');
				canvas.width = width;
				canvas.height = height;
// console.log(canvas);

				const ctx = canvas.getContext('2d');
				ctx.drawImage(img, 0, 0);
				const pixels = ctx.getImageData(0, 0, width, height).data;
// console.log(pixels);
				resolve({pixels, width, height});
			};
		});
	}

	pixelsToGrid(pxGrid) {
console.log(pxGrid);
		this.getRowColors(pxGrid, 40);

		// for ( let x = 10; x < pxGrid.width-9; x += 10 ) {
		// 	console.log(x);
		// }

		// Use https://github.com/antimatter15/ocrad.js for numbers?
	}

	getRowColors(pxGrid, x) {
		const normalColors = [];
		for ( let y = 0; y < pxGrid.height; y++ ) {
			normalColors.push(this.getNormalColor(this.getPixelColor(pxGrid, x, y)));
		}
		console.log(normalColors.join(' '));
	}

	getPixelColor(pxGrid, x, y) {
		const pi = y*pxGrid.width + x;
		const data = pxGrid.pixels.slice(pi*4, pi*4+3);
		return data;
		// return data[0] + ',' + data[1] + ',' + data[2];
	}

	getNormalColor(rgb) {
		if (this.isWhite(rgb)) return this.WHITE;
		if (this.isGray(rgb)) return this.GRAY;
		if (this.isRed(rgb)) return this.RED;
		if (this.isBlue(rgb)) return this.BLUE;
		return '';
	}

	isWhite(rgb) {
		return this.isAlmost(rgb, [255,255,255]);
	}

	isGray(rgb) {
		return this.isAlmost(rgb, [238,238,238]);
	}

	isRed(rgb) {
		return this.isAlmost(rgb, [255,56,75]);
	}

	isBlue(rgb) {
		return this.isAlmost(rgb, [28,193,225]);
	}

	isAlmost(rgb, check) {
		return true &&
			rgb[0] > check[0] - 10 &&rgb[0] < check[0] + 10 &&
			rgb[1] > check[1] - 10 &&rgb[1] < check[1] + 10 &&
			rgb[2] > check[2] - 10 &&rgb[2] < check[2] + 10;
	}



	createStats() {
	}

	setTime( time ) {
	}

	setMoves( moves ) {
	}

}

class OhnoSolver {

	static fromDom(table) {
		const map = {"on": 1, "off": 0};
		const grid = table.getElements('tr').map(tr => {
			return tr.getElements('td').map(td => {
				if (td.hasClass('closed') || td.data('closed')) {
					return 'x';
				}
				else if (td.data('required')) {
					return parseInt(td.data('required'));
				}
				else if (td.hasClass('active')) {
					return 'o';
				}
				return null;
			});
		});
		return new this(grid);
	}

	static makeCoords(grid) {
		return grid.map((cells, y) => cells.map((val, x) => new Coords2D(x, y))).flat(1);
	}

	constructor(grid) {
		this.size = grid.length;
		this.grid = grid;

		this.requireds = this.makeRequireds();
console.log(this);
	}

	makeRequireds() {
		return this.constructor.makeCoords(this.grid).filter(C => !isNaN(parseInt(this.grid[C.y][C.x])));
	}

}
