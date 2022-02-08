"use strict";

class PicrossGroups {
	static calcRegex = null;

	constructor(hor = [], ver = []) {
		this.hor = hor;
		this.ver = ver;
	}

	serialize() {
		const ser = line => line.map(ns => ns.map(n => Game.b64(n)).join('')).join('.');
		return ser(this.hor) + '-' + ser(this.ver);
	}

	equal(groups) {
		return this.serialize() === groups.serialize();
	}

	get width() {
		return this.ver.length;
	}

	get height() {
		return this.hor.length;
	}

	static calcGroups(line) {
		if (!PicrossGroups.calcRegex) {
			PicrossGroups.calcRegex = new RegExp(`[0${Picross.OFF}]+`, 'g');
		}

		const str = line.join('').replace(PicrossGroups.calcRegex, ' ').trim();
		return str.length ? str.split(' ').map(grp => grp.length) : [];
	}

	static fromGrid(grid) {
		const groups = new PicrossGroups();
		for ( let y = 0; y < grid.height; y++ ) {
			groups.hor[y] = this.calcGroups(grid.getRow(y));
		}
		for ( let x = 0; x < grid.height; x++ ) {
			groups.ver[x] = this.calcGroups(grid.getCol(x));
		}

		return groups;
	}

	static unserialize(str) {
		const unser = line => line.split('.').map(ns => [...ns].map(n => Game.unb64(n)));
		const [hor, ver] = str.split('-');
		const groups = new PicrossGroups();
		groups.hor = unser(hor);
		groups.ver = unser(ver);
		return groups;
	}
}

class Picross extends GridGame {

	static ON = 1;
	static OFF = 2;
	static ON_CHANCE = 0.5;
	static DEFAULT_SIZE = 12;

	reset() {
		super.reset();

		this.groups = null;
		// this.width = 0;
		// this.height = 0;

		this.undo = [];
		this.optionsCache = {};
	}

	buildEmptyGrid(width, height) {
		const html = [];
		for ( let y = 0; y < height; y++ ) {
			html.push('<tr>');
			for ( let x = 0; x < width; x++ ) {
				html.push(`<td><span></span></td>`);
			}
			html.push(`<th class="meta hor"></th>`);
			html.push('</tr>');
		}
		for ( let x = 0; x < width; x++ ) {
			html.push(`<th class="meta ver"></th>`);
		}
		html.push(`<th></th>`);
		this.m_objGrid.innerHTML = html.join('');
	}

	resetGrid(manual) {
		this.printGrid(new GameGrid(this.groups.width, this.groups.height));

		this.undo = [];
		$('#undo-count').setText(this.undo.length);

		if (manual) {
			this.setLastToggle();
			this.stopTime();
			this.startTime();
		}
	}

	printGrid(grid) {
		this.m_objGrid.getElements('td').forEach((td, i) => td.dataset.state = grid.getIndex(i) || '');
	}

	printGroups(groups) {
		this.m_objGrid.getElements('.meta.hor').forEach((cell, i) => {
			cell.setHTML(this.makeGroupsMarkup(groups.hor[i]));
		});
		this.m_objGrid.getElements('.meta.ver').forEach((cell, i) => {
			cell.setHTML(this.makeGroupsMarkup(groups.ver[i]));
		});
	}

	makeGroupsMarkup(groups) {
		return groups.map(n => `<span>${n}</span>`).join(' ');
	}

	revertGroupsMarkup(cell) {
		return cell.getElements('span').map(el => parseInt(el.textContent));
	}

	startGame(groups) {
		this.reset();

		$('#size').value = groups.width;

		this.buildEmptyGrid(groups.width, groups.height);

		this.groups = groups;
		this.resetGrid();
		this.printGroups(groups);
	}

	loadSavedGame(size) {
		if (localStorage.picrossGroups) {
			try {
				const groups = PicrossGroups.unserialize(localStorage.picrossGroups);
				this.startGame(groups);
				if (localStorage.picrossGrid) {
					const grid = GameGrid.unserialize64(groups.width, localStorage.picrossGrid);
					this.printGrid(grid);
				}
				return true;
			}
			catch (ex) {}
		}

		return false;
	}

	startRandomGame(size = null) {
		if (size == null) {
			size = parseInt(localStorage.picrossLastSize || Picross.DEFAULT_SIZE);
		}
		localStorage.picrossLastSize = size;

		this.buildEmptyGrid(size, size);

		$('#create').disabled = true;
		this.createRandomGame(size).then(groups => {
			$('#create').disabled = false;
			localStorage.picrossGroups = groups.serialize();
			localStorage.removeItem('picrossGrid');

			this.startGame(groups);
		});
	}

	createRandomGame(size) {
		const grid = new GameGrid(size, size);
		return new Promise(resolve => {
			let attempts = 0;
			const attempt = () => {
				attempts++;
				this.randomizeGrid(grid);
				const groups = PicrossGroups.fromGrid(grid);
				const diff = this.getDifficulty(groups);
				if (diff >= size - 3 && diff <= size + 3) {
console.log('attempts', attempts);
					resolve(groups);
				}
				else {
					this.printGrid(grid);
					setTimeout(attempt);
				}
			};
			attempt();
		});
	}

	getDifficulty(groups) {
console.time('getDifficulty');
		const solver = new PicrossSolver(groups);
		const solved = solver.solve();
console.timeEnd('getDifficulty');
		const overs = solver.leftOvers();
		return overs > 10 ? 0 : solver.passes + Math.max(0, overs - 6) / 2;
	}

	randomizeGrid(grid) {
		for ( let i = 0; i < grid.length; i++ ) {
			grid.setIndex(i, Math.random() < Picross.ON_CHANCE ? Picross.ON : Picross.OFF);
		}
	}

	exportGrid() {
		const grid = new GameGrid(this.groups.width, this.groups.height);
		this.m_objGrid.getElements('td').forEach((el, i) => {
			const state = el.dataset.state;
			if (state) grid.setIndex(i, parseInt(state));
		});
		return grid;
	}

	exportGroups() {
		const hor = this.m_objGrid.getElements('.meta.hor').map(el => this.revertGroupsMarkup(el));
		const ver = this.m_objGrid.getElements('.meta.ver').map(el => this.revertGroupsMarkup(el));
		return new PicrossGroups(hor, ver);
	}

	cheatOne() {
		this.m_bCheating = true;

		const groups = this.exportGroups();
		const grid = this.exportGrid();
		const solver = new PicrossSolver(groups, grid, this.optionsCache);
// console.time('solvePass');
		let pass = solver.solvePass();
// console.timeEnd('solvePass');
// console.log('pass', pass, solver);
		this.printGrid(solver.grid);
	}

	fillInEmpty() {
		this.m_objGrid.getElements('td').forEach(td => {
			if (!td.dataset.state) {
				td.dataset.state = Picross.OFF;
			}
		});
	}

	haveWon() {
		const grid = this.exportGrid();
		const groups = PicrossGroups.fromGrid(grid);
		return this.groups.equal(groups);
	}

	win() {
		this.fillInEmpty();

		localStorage.removeItem('picrossGroups');
		localStorage.removeItem('picrossGrid');

		super.win();
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.groups.width * this.groups.height,
		};
	}

	hiliteLinesFrom(C) {
		this.m_objGrid.getElements('.hilite').removeClass('hilite');
		this.m_objGrid.getElements(`tr:nth-child(${C.y+1}) > *, tr > :nth-child(${C.x+1})`).addClass('hilite');
	}

	setLastToggle(td) {
		this.m_objGrid.getElements('.last-toggle').removeClass('last-toggle');
		if (td) {
			td.addClass('last-toggle');
		}
	}

	toggleCell(td, offset) {
		const states = ['', String(Picross.ON), String(Picross.OFF)];
		const i = states.indexOf(td.dataset.state || '');
		td.dataset.state = states[(i + offset) % 3];

		this.setLastToggle(td);

		localStorage.picrossGrid = this.exportGrid().serialize64();
	}

	handleUndo() {
		const td = this.undo.pop();
		if (td) {
			this.toggleCell(td, 2);
			$('#undo-count').setText(this.undo.length);
		}
	}

	handleCellClick(td) {
		if (this.m_bGameOver) {
			return this.startRandomGame(this.groups.width);
		}

		this.startTime();

		this.toggleCell(td, 1);

		this.undo.push(td);
		$('#undo-count').setText(this.undo.length);

		this.startWinCheck();
	}

	listenControls() {
		this.listenCellClick();

		this.m_objGrid.on('mouseover', 'td', e => {
			this.hiliteLinesFrom(this.getCoord(e.subject));
		});

		$('#cheat').on('click', e => {
			this.cheatOne();
		});

		$('#undo').on('click', e => {
			this.handleUndo();
		});

		$('#reset').on('click', e => {
			this.resetGrid(true);
		});

		$('#create').on('click', e => {
			const size = parseInt($('#size').value);
			this.startRandomGame(size);
		});
	}

	createStats() {}

}

class PicrossSolver {

	constructor(groups, grid = null, optionsCache = {}) {
		this.groups = groups;
		this.grid = grid ? grid.copy() : new GameGrid(groups.ver.length, groups.hor.length);
		this.width = this.grid.width;
		this.height = this.grid.height;

		this.optionsCache = optionsCache;
		this.passes = 0;
	}

	leftOvers() {
		return this.grid.content.filter(v => v == 0).length;
	}

	solve() {
		while (this.solvePass()) {
			this.passes++;
		}

		return this.leftOvers();
	}

	solvePass() {
		let changed = false;
		// changed = Math.random() < 0.8;

		for ( let y = 0; y < this.height; y++ ) {
			const line = this.grid.getRow(y).join('');
			const hints = this.groups.hor[y];
			const possibles = this.possibleLines(line, hints);
			if (possibles.length) {
				const commons = this.commonCells(possibles);
				if (commons.join('') != line) {
					this.grid.setRow(y, commons);
					changed = true;
				}
			}
		}

		for ( let x = 0; x < this.width; x++ ) {
			const line = this.grid.getCol(x).join('');
			const hints = this.groups.ver[x];
			const possibles = this.possibleLines(line, hints);
			if (possibles.length) {
				const commons = this.commonCells(possibles);
				if (commons.join('') != line) {
					this.grid.setCol(x, commons);
					changed = true;
				}
			}
		}

		return changed;
	}

	commonCells(possibles) {
		const counts = new Uint8Array(possibles[0].length);
		for (let i=0; i<possibles.length; i++) {
			const option = possibles[i];
			for (let j=0; j<option.length; j++) {
				const cell = option[j];
				if (counts[j] == null) {
					counts[j] = 0;
				}
				if (cell == Picross.ON) {
					counts[j]++;
				}
			}
		}

		const commons = new Uint8Array(possibles[0].length);
		for (let i=0; i<counts.length; i++) {
			const count = counts[i];
			if (count == possibles.length) {
				commons[i] = Picross.ON;
			}
			else if (count == 0) {
				commons[i] = Picross.OFF;
			}
		}

		return commons;
	}

	possibleLines(currentLine, hints) {
		const options = this.options(currentLine.length, hints);
		if (options.length == 1) return [options[0]];

		const regex = new RegExp('^' + currentLine.replace(/0/g, '.') + '$');
		return options.filter(option => regex.test(option));
	}

	options(length, hints) {
		const key = `${length}-${hints.join(',')}`;
		if (this.optionsCache[key]) {
// console.debug('options', key, 'from cache');
			return this.optionsCache[key];
		}

		const groups = hints.length;
		const taken = hints.reduce(function(num, hint) {
			return num + hint;
		}, 0);
		const spacers = groups-1;
		const room = length - taken - spacers;

		if (room == 0) {
			let option = '';
			for (let i=0; i<hints.length; i++) {
				if (i > 0) {
					option += Picross.OFF;
				}
				for (let j=0; j<hints[i]; j++) {
					option += Picross.ON;
				}
			}
// console.debug('options', key, 'full');
			return this.optionsCache[key] = [option];
		}

		const flexibles = groups+1;
		let code = 'var spread = [];';
		for (let f=0; f<flexibles; f++) {
			code += 'for (var g' + f + '=0; g' + f + '<=' + room + '; g' + f + '++) ';
		}
		const vars = [];
		for (let f=0; f<flexibles; f++) {
			vars.push('g' + f);
		}
		code += 'if (' + vars.join(' + ') + ' == ' + room + ') ';
		code += 'spread.push([' + vars.join(', 1+') + '-1]); ';
		code += 'return spread;';

		const fn = new Function(code);
		const spread = fn();

		const options = [];
		for (var i=0; i<spread.length; i++) {
			var option = '';
			for (var j=0; j<hints.length; j++) {
				// Add inactives before
				for (var x=0; x<spread[i][j]; x++) {
					option += Picross.OFF;
				}

				// Add actives
				for (var x=0; x<hints[j]; x++) {
					option += Picross.ON;
				}
			}

			// Add last inactives after
			for (var x=0; x<spread[i][j]; x++) {
				option += Picross.OFF;
			}
			options.push(option);
		}

// console.debug('options', key, 'eval');
		return this.optionsCache[key] = options;
	}

}
