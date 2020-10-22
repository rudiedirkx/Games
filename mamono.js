class Mamono extends GridGame {

	static SIZES = {
		easy: {
			size: [16, 16],
			hp: 10,
			monsters: [10, 8, 6, 4, 2],
			levelUps: [0, 7, 20, 50, 82],
		},
		normal: {
			size: [22, 22],
			hp: 10,
			monsters: [33, 27, 22, 13, 6],
			levelUps: [0, 10, 50, 167, 271],
		},
		extreme: {
			size: [22, 22],
			hp: 10,
			monsters: [25, 25, 25, 25, 25],
			levelUps: [0, 10, 50, 175, 375],
		},
		blind: {
			size: [22, 22],
			level: 0,
			hp: 0,
			monsters: [33, 27, 22, 13, 6],
			levelUps: [],
		},
		huge: {
			size: [36, 36],
			hp: 30,
			monsters: [52, 46, 40, 36, 30, 24, 18, 13, 1],
			levelUps: [0, 10, 90, 202, 400, 1072, 1840, 2992, 4656],
		},
	};

	getSpecs() {
		return this.constructor.SIZES[this.size];
	}

	reset() {
		super.reset();

		this.mapInited = false;
		this.level = 1;
		this.exp = 0;
	}

	setMoves() {
	}

	statTypes() {
		return {
			time: 'Time',
			lvl: 'Lvl',
			exp: 'Exp',
			nxt: 'Nxt',
		};
	}

	getStatsDelimiter() {
		return ' ';
	}

	showStats() {
		$('#stats-lvl').setText(this.level);
		$('#stats-exp').setText(this.exp);

		const specs = this.getSpecs();
		$('#stats-nxt').setText(specs.levelUps[this.level]);
	}

	createSizeSelect(selected) {
		const el = $('#select-size');
		el.empty();
		el.setHTML(Object.keys(this.constructor.SIZES).map(size => {
			const sel = selected == size ? ' selected' : '';
			return `<option${sel}>${size}`;
		}).join(''));
	}

	createMap(size) {
		this.size = size;

		this.createSizeSelect(size);

		this.reset();

		const specs = this.getSpecs();
		specs.level != null && (this.level = specs.level);

		this.showStats();

		this.m_objGrid.empty();
		this.m_objGrid.style.setProperty('--w', specs.size[0]);
		this.m_objGrid.style.setProperty('--h', specs.size[1]);

		for (var y = 0; y < specs.size[1]; y++) {
			var nr = this.m_objGrid.insertRow(this.m_objGrid.rows.length);
			for (var x = 0; x < specs.size[0]; x++) {
				var cell = nr.insertCell(nr.cells.length);
				cell.className = 'closed';
			}
		}
	}

	initMap(firstCell) {
		const specs = this.getSpecs(this.size);
		const monsters = this.makeMonsters(specs.monsters);
		this.fillMonsters(monsters, specs.size[0] * specs.size[1]);

		const C = this.getCoord(firstCell);
		var grid;
		while (!grid || grid[C.y][C.x] != 0 || this.getAdjacentCount(C, grid) > 1) {
			grid = this.makeGrid(monsters, specs.size);
		}

		this.grid = grid;
		this.fillMap();
	}

	fillMap() {
		const monsters = [].concat(...this.grid);
		const cells = this.m_objGrid.getElements('td');
		cells.forEach((cell, i) => {
			const m = monsters[i];
			if (m) {
				cell.data('monster', m);
			}
		});
	}

	makeGrid(monsters, size) {
		monsters.sort(() => Math.random() > 0.5 ? -1 : 1);

		const grid = [];
		var row;
		for (let y = 0; y < size[1]; y++) {
			grid.push(row = []);
			for (let x = 0; x < size[0]; x++) {
				row.push(monsters[y * size[1] + x]);
			}
		}

		return grid;
	}

	makeMonsters(numbers) {
		const monsters = [];
		numbers.forEach(function(num, m) {
			for (let i = 0; i < num; i++) {
				monsters.push(m+1);
			}
		});

		return monsters;
	}

	fillMonsters(monsters, total) {
		while (monsters.length < total) {
			monsters.push(0);
		}

		return monsters;
	}

	listenControls() {
		this.listenCellClick();
		this.listenSizeSelect();
	}

	listenSizeSelect() {
		$('#select-size').on('change', e => {
			this.createMap(e.target.value);
		});
	}

	handleCellClick(cell) {
		if (this.m_bGameOver) {
			return this.createMap(this.size);
		}

		if (!this.mapInited) {
			this.startTime();
			this.mapInited = true;
			this.initMap(cell);
		}

		if (cell.hasClass('closed')) {
			this.openCell(cell);
		}
		else if (cell.data('monster')) {
			this.toggleMonster(cell);
		}

		this.showStats();
		this.winOrLose();
	}

	haveWon() {
		return this.m_objGrid.getElements('.closed').length == 0;
	}

	getMonster(C, grid = null) {
		grid || (grid = this.grid);
		return grid[C.y] && grid[C.y][C.x] || 0;
	}

	getAdjacentCount(C, grid = null) {
		return this.dir8Coords.reduce((adj, N) => {
			return adj + this.getMonster(N.add(C), grid);
		}, 0);
	}

	isLevelUp(exp) {
		const specs = this.getSpecs();
		const need = specs.levelUps[this.level];
		return this.exp < need && this.exp + exp >= need;
	}

	openCell(cell) {
		if (!cell.hasClass('closed')) return;

		cell.removeClass('closed');
		const C = this.getCoord(cell);
		const adj = this.getAdjacentCount(C);

		if (cell.data('monster')) {
			cell.innerHTML = '<span>' + (adj || '0') + '</span>';

			const m = parseInt(cell.data('monster'));
			if (m > this.level) {
				return this.lose();
			}

			const exp = Math.pow(2, m-1);
			const up = this.isLevelUp(exp);
			this.exp += exp;
			if (up) {
				this.level++;
				this.happening();
			}

			adj == 0 && this.openAdjacentCells(C);
			return;
		}

		cell.innerHTML = '<span>' + (adj || '') + '</span>';
		adj == 0 && this.openAdjacentCells(C);
	}

	happening() {
		document.body.addClass('happening');
		setTimeout(() => document.body.removeClass('happening'), 5000);
	}

	openAdjacentCells(C) {
		this.dir8Coords.forEach(N => {
			const cell = this.getCell(N.add(C));
			cell && this.openCell(cell);
		});
	}

	toggleMonster(cell) {
		cell.toggleClass('show-adjacents');
	}

}
