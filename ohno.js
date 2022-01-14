"use strict";

const OhnoMixin = {

	isActive(v) {
		return v === Ohno.ACTIVE_STRUCTURE || v === Ohno.ACTIVE_USER || this.isNumber(v);
	},

	isClosed(v) {
		return v === Ohno.CLOSED_STRUCTURE || v === Ohno.CLOSED_USER;
	},

	isNumber(v) {
		return v != null && v > 0 && v < 100;
	},

	allActiveNeighbors(C) {
		return Coords2D.dir4Coords.map(D => this.neighborsToward(C, D, 'isActiveNeighbor'));
	},

	allPotentialNeighbors(C) {
		return Coords2D.dir4Coords.map(D => this.neighborsToward(C, D, 'isPotentialNeighbor'));
	},

	neighborsToward(C, D, matcher = 'isActiveNeighbor') {
		var curr = C;
		var next;

		const list = [];
		while (this.grid.insideC(next = curr.add(D)) && this[matcher](next)) {
			list.push(next);
			curr = next;
		}

		return list;
	},

	isActiveNeighbor(C) {
		return this.isActive(this.grid.getC(C));
	},

	isPotentialNeighbor(C) {
		const v = this.grid.getC(C);
		return v === 0 || this.isActive(v);
	},

};

class Ohno extends CanvasGame {

	static CLOSED_STRUCTURE = 101;
	static CLOSED_USER = 102;
	static ACTIVE_STRUCTURE = 111;
	static ACTIVE_USER = 112;

	static OFFSET = 20;
	static CIRCLE = 40;
	static MARGIN = 10;

	constructor(...args) {
		super(...args);

		this.WHITE = '';
		this.BLACK = '';
		this.GRAY = 'g';
		this.RED = 'r';
		this.BLUE = 'b';

		this.OUTSIDE = [100, 10, 100, 10];
		this.MIN_GROUPS = 11;
	}

	reset() {
		super.reset();

		this.size = 7;
		this.grid = [];
	}

	drawStructure() {
	}

	drawContent() {
		this.drawGrid();
	}

	drawGrid() {
		this.ctx.textAlign = 'center';

		for ( let y = 0; y < this.size; y++ ) {
			for ( let x = 0; x < this.size; x++ ) {
				const v = this.grid.get(x, y);

				const centerC = this.scale(new Coords2D(x, y));
				const color = this.isActive(v) ? '#86c5da' : (this.isClosed(v) ? 'red' : '#cccc');
				this.drawDot(centerC, {radius: Ohno.CIRCLE/2, color});

				if (this.isNumber(v)) {
					const textC = centerC.add(new Coords2D(0, Ohno.CIRCLE*0.8/3));
					this.drawText(textC, v, {color: 'white', size: (Ohno.CIRCLE*0.8) + 'px'});
				}
				else if (v === Ohno.CLOSED_STRUCTURE) {
					const from = centerC.add(new Coords2D(
						(Ohno.CIRCLE/2 - 1) * Math.sin(Math.PI*3/4),
						(Ohno.CIRCLE/2 - 1) * Math.cos(Math.PI*3/4)
					));
					const to = centerC.add(new Coords2D(
						(Ohno.CIRCLE/2 - 1) * Math.sin(Math.PI/-4),
						(Ohno.CIRCLE/2 - 1) * Math.cos(Math.PI/-4)
					));
					this.drawLine(from, to, {width: 3, color: '#a00'});
				}
			}
		}
	}

	scale(source) {
		if (source instanceof Coords2D) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Ohno.OFFSET + source * (Ohno.CIRCLE + Ohno.MARGIN) + Ohno.CIRCLE/2;
	}

	unscale(source) {
		if (source instanceof Coords2D) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return this.scale(C).distance(source) < Ohno.CIRCLE/2 - 3 ? C : null;
		}

		return Math.round((source - Ohno.OFFSET - Ohno.CIRCLE/2) / (Ohno.MARGIN + Ohno.CIRCLE));
	}

	getState(C) {
		return C && this.grid.getC(C);
	}

	loadGrid(grid) {
		this.size = grid.width;
		this.grid = grid;

		this.canvas.width = this.canvas.height = Ohno.OFFSET + Ohno.CIRCLE * this.size + Ohno.MARGIN * (this.size - 1) + Ohno.OFFSET;
		this.changed = true;
	}

	importMap(size, source) {
		const grid = new GameGrid(size, size);
		for ( let y = 0; y < size; y++ ) {
			for ( let x = 0; x < size; x++ ) {
				const t = source[y*size+x];
				if (t == 'x') {
					grid.set(x, y, Ohno.CLOSED_STRUCTURE);
				}
				else if (!isNaN(parseInt(t))) {
					grid.set(x, y, parseInt(t));
				}
			}
		}

		this.loadGrid(grid);
		return grid;
	}

	createRandom(size) {
		size || (size = this.size);

		const RED_CHANCE = 0.3;
		const RED_MAX = 0.6;

		let attempts = 0;
		const grid = new GameGrid(size, size);
		while (attempts < 1) {
			attempts++;

			for ( let i = 0; i < grid.length; i++ ) {
				const v = Math.random() > RED_CHANCE ? Ohno.ACTIVE_STRUCTURE : Ohno.CLOSED_STRUCTURE;
				grid.setIndex(i, v);
			}
// console.log(grid);

// 			var recount = false;
// 			const starts = this.m_objGrid.getElements('td.active');
// 			let neighbors = starts.map(td => {
// 				const neighbors = this.allActiveNeighbors(td).length;
// 				if (neighbors == 0) {
// 					recount = true;
// 					td.className = 'closed';
// 				}
// 				else {
// 					td.firstElementChild.setText(neighbors);
// 				}
// 				return neighbors;
// 			});
// console.log(neighbors);
// 			if (recount) {
// 				neighbors = starts.map(td => this.allActiveNeighbors(td).length);
// 			}
// // console.log(neighbors);
// 			const closed = this.m_objGrid.getElements('.closed').length;
// 			if (Math.max(...neighbors) <= 9 && closed/size/size < RED_MAX) {
// 				break;
// 			}
		}
console.log(`in ${attempts} attempts`);

		this.loadGrid(grid);
	}

	createEmpty(size) {
		return this.importMap(size || this.size, '');
	}

	listenControls() {
		this.listenImageDrop();

		this.listenClick();
		this.canvas.on('mousedown', (e) => {
			e.preventDefault();
		});

		$('#create').on('click', e => this.createRandom());

		$('#cheat').on('click', e => this.cheatOneRound());
	}

	handleClick(coord) {
		const C = this.unscale(coord);
		const v = this.getState(C);
		if (v == null) return;

		if (this.isNumber(v)) {
			if (this.clickCheat) {
				this.cheatOneRoundFromStart(C);
			}
			return;
		}

		if (v === 0) {
			this.grid.setC(C, Ohno.ACTIVE_USER);
		}
		else if (v === Ohno.ACTIVE_USER) {
			this.grid.setC(C, Ohno.CLOSED_USER);
		}
		else if (v === Ohno.CLOSED_USER) {
			this.grid.setC(C, 0);
		}
		else {
			return;
		}

		this.changed = true;

		this.startWinCheck();
	}

	haveWon() {
		const starts = [];
		for ( let y = 0; y < this.size; y++ ) {
			for ( let x = 0; x < this.size; x++ ) {
				const v = this.grid.get(x, y);
				if (v === 0) return false;

				if (this.isNumber(v)) starts.push(new Coords2D(x, y));
			}
		}

		return starts.every(C => this.allActiveNeighbors(C).flat(1).length == this.grid.getC(C));
	}

	cheatOneRoundFromStart(C) {
		this.m_bCheating = true;

		const solver = OhnoSolver.fromGrid(this.grid);
		solver.findKnownsFromSpacesStarting(C);
		solver.findKnownsFromEnoughStarting(C);
		solver.findKnownsFromTooFarStarting(C);
		this.cheatFromSolver(solver);
	}

	cheatOneRound() {
		this.m_bCheating = true;

		const solver = OhnoSolver.fromGrid(this.grid);
		solver.findKnowns();
		this.cheatFromSolver(solver);
	}

	cheatFromSolver(solver) {
console.log(solver);
		solver.updatesActive.forEach(C => this.grid.setC(C, Ohno.ACTIVE_USER));
		solver.updatesClosed.forEach(C => this.grid.setC(C, Ohno.CLOSED_USER));
		this.changed = true;
	}

	listenImageDrop() {
		// ['drag', 'dragend', 'dragenter', 'dragleave', 'dragover', 'dragstart', 'drop'].forEach(t => {
		// 	document.on(t, e => {
		// 		e.preventDefault();
		// 		console.log(e.type);
		// 	});
		// });

		$('input[type="file"]').on('change', e => {
			const file = e.target.files[0];
			this.fileToBoard(file);
		});

		document.on('dragover', e => {
			e.preventDefault();
		});
		document.on('drop', e => {
			e.preventDefault();
			const file = e.data.files[0];
			this.fileToBoard(file);
		});
	}

	async fileToBoard(file) {
		console.time('fileToPixels');
		const imageData = await this.fileToPixels(file);
		console.timeEnd('fileToPixels');
console.log(imageData);

		console.time('pixelsToGrid');
		const grid = this.pixelsToGrid(imageData);
		console.timeEnd('pixelsToGrid');
console.log(grid);
		if (!grid) return;

		this.loadGrid(grid);
	}

	fileToPixels(file) {
		return new Promise(resolve => {
			const img = document.createElement('img');
			img.src = URL.createObjectURL(file);
			img.onload = e => {
				const width = img.width;
				const height = img.height;
				const canvas = document.createElement('canvas');
				canvas.width = width;
				canvas.height = height;

				const ctx = canvas.getContext('2d');
				ctx.drawImage(img, 0, 0);
				const data = ctx.getImageData(
					this.OUTSIDE[3],
					this.OUTSIDE[0],
					width - this.OUTSIDE[1] - this.OUTSIDE[3],
					height - this.OUTSIDE[2] - this.OUTSIDE[0]
				);
				resolve(data);
			};
		});
	}

	pixelsToGrid(imageData) {
// console.log(imageData);

		var x, centersVer, offCenterVer;
		var y, centersHor, offCenterHor;

		for ( x = 0; x <= imageData.width; x += 10 ) {
			const groups = this.groupColors(this.getRowColors(imageData, x));
			if (groups.length >= this.MIN_GROUPS) {
				centersVer = this.getGroupCenters(groups, this.OUTSIDE[0]);
				const distance = this.getAvgCellDistance(centersVer);
// console.log(x, groups, centersVer, distance);
				break;
			}
		}

		for ( y = 0; y <= imageData.height; y += 10 ) {
			const groups = this.groupColors(this.getColumnColors(imageData, y));
			if (groups.length >= this.MIN_GROUPS) {
				centersHor = this.getGroupCenters(groups, this.OUTSIDE[3]);
				const distance = this.getAvgCellDistance(centersHor);
// console.log(y, groups, centersHor, distance);
				break;
			}
		}

		if (centersHor.length != centersVer.length) {
			return alert(`Can't find cells: width = ${centersHor.length}, height = ${centersVer.length}`);
		}

		offCenterVer = parseInt((centersVer[0] - y) / 2);
		offCenterHor = parseInt((centersHor[0] - x) / 2);

		// Use https://github.com/antimatter15/ocrad.js for numbers?

		const grid = [];
		for ( let y = 0; y < centersVer.length; y++ ) {
			const row = new Uint8Array(centersHor.length);
			grid.push(row);
			for ( let x = 0; x < centersHor.length; x++ ) {
				const color = this.getNormalColor(this.getPixelColor(imageData, centersHor[x] - offCenterHor, centersVer[y] - offCenterVer));
				row[x] = color == 'r' ? Ohno.CLOSED_STRUCTURE : (color == 'b' ? Ohno.ACTIVE_STRUCTURE : 0);
			}
		}

		return grid;
	}

	getAvgCellDistance(centers) {
		var total = 0;
		var amount = 0;
		for ( let i = 1; i < centers.length; i++ ) {
			total += centers[i] - centers[i-1];
			amount++;
		}
		return Math.round(total / amount);
	}

	getGroupCenters(groups, offset) {
		const centers = [];
		for ( let i = 0; i < groups.length; i++ ) {
			const [color, size] = groups[i];
			if (color) {
				centers.push(offset + parseInt(size/2));
			}

			offset += size;
		}
		return centers;
	}

	groupColors(colors) {
		const groups = [];
		var last = null;
		for ( let i = 0; i < colors.length; i++ ) {
			if (last != colors[i]) {
				groups.push([colors[i], 0]);
				last = colors[i];
			}
			groups[groups.length-1][1]++;
		}
		return groups;
	}

	getRowColors(imageData, x) {
		const normalColors = [];
		for ( let y = this.OUTSIDE[0]; y < imageData.height - this.OUTSIDE[2]; y++ ) {
			normalColors.push(this.getNormalColor(this.getPixelColor(imageData, x, y)));
		}
		return normalColors;
	}

	getColumnColors(imageData, y) {
		const normalColors = [];
		for ( let x = this.OUTSIDE[3]; x < imageData.width - this.OUTSIDE[1]; x++ ) {
			normalColors.push(this.getNormalColor(this.getPixelColor(imageData, x, y)));
		}
		return normalColors;
	}

	getPixelColor(imageData, x, y) {
		const pi = y*imageData.width + x;
		const data = imageData.data.slice(pi*4, pi*4+3);
		return data;
	}

	getNormalColor(rgb) {
		if (this.isWhite(rgb)) return this.WHITE;
		if (this.isBlack(rgb)) return this.BLACK;
		if (this.isGray(rgb)) return this.GRAY;
		if (this.isRed(rgb)) return this.RED;
		if (this.isBlue(rgb)) return this.BLUE;
		return '';
	}

	isWhite(rgb) {
		return this.isAlmost(rgb, [255,255,255]);
	}

	isBlack(rgb) {
		return this.isAlmost(rgb, [0,0,0]);
	}

	isGray(rgb) {
		return this.isAlmost(rgb, [238,238,238]);
	}

	isRed(rgb) {
		return this.isAlmost(rgb, [255,56,75]) || this.isAlmost(rgb, [203,45,60]);
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

	static fromGrid(grid) {
		return new OhnoSolver(grid.copy());
	}

	constructor(grid) {
		this.size = grid.width;
		this.grid = grid;

		this.requireds = this.makeRequireds();
		this.updatesActive = [];
		this.updatesClosed = [];
	}

	makeRequireds() {
		const coords = [];
		for ( let y = 0; y < this.size; y++ ) {
			for ( let x = 0; x < this.size; x++ ) {
				if (this.isNumber(this.grid.get(x, y))) {
					coords.push(new Coords2D(x, y));
				}
			}
		}
		return coords;
	}

	setActive(C) {
		if (this.grid.getC(C) === 0) {
			this.grid.setC(C, 'o');
			this.updatesActive.push(C);
		}
	}

	setClosed(C) {
		if (this.grid.getC(C) === 0) {
			this.grid.setC(C, 'x');
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
			const next = (L.length ? L[L.length-1] : C).add(D);
			this.grid.insideC(next) && this.setClosed(next);
		});
	}

	findKnownsFromSpacesStarting(C) {
		const required = this.grid.getC(C);
		const neighbors = this.allPotentialNeighbors(C);
		const lengths = neighbors.map(L => L.length);
		const total = neighbors.flat(1).length;
		if (total == required) {
			return this.updateFromSpacesAll(C, neighbors);
		}

		const left = total - required;
		if (left > 0 && left < Math.max(...neighbors.map(L => L.length))) {
			return this.updateFromSpacesLeft(C, neighbors, left);
		}

	}

	findKnownsFromEnoughStarting(C) {
		const required = this.grid.getC(C);
		const neighbors = this.allActiveNeighbors(C);
		const lengths = neighbors.map(L => L.length);
		const total = neighbors.flat(1).length;
		if (total == required) {
			return this.updateFromEnough(C, neighbors);
		}
	}

	findKnownsFromTooFarStarting(C) {
		const required = this.grid.getC(C);
		const neighbors = this.allActiveNeighbors(C);
		neighbors.forEach((L, d) => {
			const D = Coords2D.dir4Coords[d];
			const next = (L.length ? L[L.length-1] : C).add(D);
			const val = this.grid.get(next.x, next.y);
			if (val === 0) {
				this.grid.set(next.x, next.y, Ohno.ACTIVE_USER);
				const neighbors2 = this.allActiveNeighbors(C);
				const total2 = neighbors2.flat(1).length;
				this.grid.set(next.x, next.y, 0);
				if (total2 > required) {
					this.setClosed(next);
				}
			}
		});
	}

	findKnownsFromSpaces() {
		this.requireds.forEach(C => this.findKnownsFromSpacesStarting(C));
	}

	findKnownsFromEnough() {
		this.requireds.forEach(C => this.findKnownsFromEnoughStarting(C));
	}

	findKnownsFromTooFar() {
		this.requireds.forEach(C => this.findKnownsFromTooFarStarting(C));
	}

	findKnowns() {
		this.findKnownsFromSpaces();
		this.findKnownsFromEnough();
		this.findKnownsFromTooFar();
	}

}

Object.assign(Ohno.prototype, OhnoMixin);
Object.assign(OhnoSolver.prototype, OhnoMixin);
