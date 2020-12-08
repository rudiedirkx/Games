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

	createRandom(size) {
		size || (size = this.size);

		const RED_CHANCE = 0.3;
		const RED_MAX = 0.6;

		let attempts = 0;
		while (true) {
			this.createEmpty(size);
			attempts++;
			this.m_objGrid.getElements('td').forEach(td => td.className = Math.random() > RED_CHANCE ? 'active' : 'closed');
			var recount = false;
			const starts = this.m_objGrid.getElements('td.active');
			let neighbors = starts.map(td => {
				const neighbors = this.allActiveNeighbors(td).length;
				if (neighbors == 0) {
					recount = true;
					td.className = 'closed';
				}
				else {
					td.firstElementChild.setText(neighbors);
				}
				return neighbors;
			});
			if (recount) {
				neighbors = starts.map(td => this.allActiveNeighbors(td).length);
			}
console.log(neighbors);
			const closed = this.m_objGrid.getElements('.closed').length;
			if (Math.max(...neighbors) <= 9 && closed/size/size < RED_MAX) {
				break;
			}
		}
console.log(`in ${attempts} attempts`);

		this.m_objGrid.getElements('td').forEach(td => {
			if (Math.random() > 0.5) {
				td.firstElementChild.setText('');
			}
			else {
				if (td.hasClass('closed')) {
					td.removeClass('closed').data('closed', '1');
				}
				else {
					td.removeClass('active').data('required', parseInt(td.firstElementChild.getText()));
				}
			}
			td.className = '';
		});
	}

	createEmpty(size) {
		size || (size = this.size);
		const grid = Array.from(Array(size)).map(x => ' '.repeat(size));
		return this.createMap(grid);
	}

	listenControls() {
		this.listenImageDrop();

		this.listenCellClick();

		$('#new').on('click', e => this.createRandom());

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

	allActiveNeighbors(cell, flatten = true) {
		const dirs = this.dir4Coords.map(C => this.activeNeighborsToward(cell, C));
		return flatten ? dirs.flat(1) : dirs;
	}

	activeNeighborsToward(cell, dir) {
		var curr = cell;
		var next;

		const list = [];
		while (this.isActiveNeighbor(next = this.getNextCell(this.getCoord(curr), dir))) {
			list.push(next);
			curr = next;
		}

		return list;
	}

	isActiveNeighbor(cell) {
		return cell && (cell.data('required') || cell.hasClass('active'));
	}

	cheatOneRound() {
		this.m_bCheating = true;

		const solver = OhnoSolver.fromDom(this.m_objGrid);
		solver.findKnowns();
console.log(solver);
		solver.updatesActive.forEach(C => this.getCell(C).addClass('active'));
		solver.updatesClosed.forEach(C => this.getCell(C).addClass('closed'));
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
		this.updatesActive = [];
		this.updatesClosed = [];
	}

	makeRequireds() {
		return this.constructor.makeCoords(this.grid).filter(C => !isNaN(parseInt(this.grid[C.y][C.x])));
	}

	allPotentialNeighbors(C) {
		return Coords2D.dir4Coords.map(D => this.potentialNeighborsToward(C, D, 'isPotentialNeighbor'));
	}

	allActiveNeighbors(C) {
		return Coords2D.dir4Coords.map(D => this.potentialNeighborsToward(C, D, 'isActiveNeighbor'));
	}

	potentialNeighborsToward(C, D, matcher = 'isPotentialNeighbor') {
		var curr = C;
		var next;

		const list = [];
		while (this[matcher](next = curr.add(D))) {
			list.push(next);
			curr = next;
		}

		return list;
	}

	isPotentialNeighbor(C) {
		return this.grid[C.y] && (typeof this.grid[C.y][C.x] == 'number' || this.grid[C.y][C.x] === 'o' || this.grid[C.y][C.x] === null);
	}

	isActiveNeighbor(C) {
		return this.grid[C.y] && (typeof this.grid[C.y][C.x] == 'number' || this.grid[C.y][C.x] === 'o');
	}

	setActive(C) {
		const curr = this.grid[C.y][C.x];
		if (curr === null) {
			console.log('SET ACTIVE', C);
			this.grid[C.y][C.x] = 'o';
			this.updatesActive.push(C);
		}
	}

	setClosed(C) {
		const curr = this.grid[C.y] && this.grid[C.y][C.x];
		if (curr === null) {
			console.log('SET CLOSED', C);
			this.grid[C.y][C.x] = 'x';
			this.updatesClosed.push(C);
		}
	}

	updateFromSpacesAll(C, neighbors) {
		neighbors.flat(1).forEach(C2 => this.setActive(C2));
	}

	updateFromSpacesLeft(C, neighbors, left) {
		neighbors.forEach(L => {
			L.slice(0, -left).forEach(C2 => this.setActive(C2));
		});
	}

	updateFromEnough(C, neighbors) {
		neighbors.forEach((L, d) => {
			const D = Coords2D.dir4Coords[d];
			console.log(Coords2D.dir4Names[d], D);
			const next = (L.length ? L[L.length-1] : C).add(D);
			this.setClosed(next);
		});
	}

	findKnownsFromSpacesStarting(C) {
		const required = this.grid[C.y][C.x];
		const neighbors = this.allPotentialNeighbors(C);
		const lengths = neighbors.map(L => L.length);
		const total = neighbors.flat(1).length;
		if (total == required) {
// console.log(C, 'fill all', lengths);
			return this.updateFromSpacesAll(C, neighbors);
		}

		const left = total - required;
		if (left > 0 && left < Math.max(...neighbors.map(L => L.length))) {
// console.log(C, 'fill -' + left, lengths);
			return this.updateFromSpacesLeft(C, neighbors, left);
		}

// console.log(C, 'too many options', lengths);
	}

	findKnownsFromEnoughStarting(C) {
		const required = this.grid[C.y][C.x];
		const neighbors = this.allActiveNeighbors(C);
		const lengths = neighbors.map(L => L.length);
		const total = neighbors.flat(1).length;
		if (total == required) {
console.log(C, 'enough', lengths);
			return this.updateFromEnough(C, neighbors);
		}
	}

	findKnownsFromSpaces() {
		this.requireds.forEach(C => this.findKnownsFromSpacesStarting(C));
	}

	findKnownsFromEnough() {
		this.requireds.forEach(C => this.findKnownsFromEnoughStarting(C));
	}

	findKnowns() {
		this.findKnownsFromSpaces();
		this.findKnownsFromEnough();
	}

}
