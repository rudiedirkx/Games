"use strict";

class Filling extends GridGame {

	createGame() {
		super.createGame();

		this.closeNeighbors = [
			new Coords2D(0, -1),
			new Coords2D(1, 0),
			new Coords2D(0, 1),
			new Coords2D(-1, 0),
		];

		this.allNeighbors = [
			...this.closeNeighbors,
			new Coords2D(-1, -1),
			new Coords2D(1, -1),
			new Coords2D(1, 1),
			new Coords2D(-1, 1),
		];
	}

	reset() {
		super.reset();

		this.size = 0;
		this.colors = [];
		this.draggingColor = null;
	}

	handleCellDragStart(start) {
		if (this.m_bGameOver) return setTimeout(() => this.createMap(this.size), 300), false;

		clearTimeout(this.checker);
		this.startTime();
		this.setMoves(this.m_iMoves + 1);

		this.draggingColor = this.registerNewColor();
		return true;
	}

	handleCellDragMove(start, end) {
		if (this.m_bGameOver) return;

		end.data('color', this.draggingColor);
	}

	handleCellDragEnd(start, end) {
		if (this.m_bGameOver) return;

		if (!end) {
			start.data('color', null);
			return;
		}

		this.startWinCheck();
	}

	registerNewColor() {
		this.colors.push('#' + ('000000' + (Math.random()*0xFFFFFF<<0).toString(16)).slice(-6));
		this.updateColorsStyle();
		return this.colors.length - 1;
	}

	updateColorsStyle() {
		$('#colors').setText(this.colors.map((color, i) => {
			const dark = (new RgbColor(color)).isDark() ? ' color: white;' : '';
			return `td[data-color="${i}"] { background-color: ${color};${dark} }`;
		}).join("\n"));
	}

	getScore() {
		return {
			...super.getScore(),
			level: this.size,
			moves: this.m_iMoves - this.m_objGrid.getElements('td').filter(td => td.textContent.trim() != '').length,
		};
	}

	haveWon() {
		if (this.m_objGrid.getElements('td:not([data-color])').length) return false;

		const extendGroup = function(group, cell, groupColor) {
			if (!cell || group.includes(cell)) return;
			groupColor || (groupColor = cell.data('color'));
			if (!groupColor || cell.data('color') !== groupColor) return;

			group.push(cell);
			// cell.textContent += 'x';

			const grid = cell.parentNode.parentNode;
			const x = cell.cellIndex;
			const y = cell.parentNode.rowIndex;

			extendGroup(group, grid.rows[y-1] && grid.rows[y-1].cells[x], groupColor);
			extendGroup(group, grid.rows[y+1] && grid.rows[y+1].cells[x], groupColor);
			extendGroup(group, grid.rows[y].cells[x-1], groupColor);
			extendGroup(group, grid.rows[y].cells[x+1], groupColor);
		}

		const starts = this.m_objGrid.getElements('td').filter(td => td.textContent != '');
		const groups = starts.map(cell => {
			const group = [];
			extendGroup(group, cell);
			return group;
		});

		const wrongs = groups.filter((group, i) => group.length != parseInt(starts[i].textContent));
		return wrongs.length == 0;
	}

	createFromExport(chars) {
		console.time('createFromExport');

		const size = Math.sqrt(chars.length);
		if (chars.length && size == parseInt(size)) {
			this.printGrid(this.createEmptyGrid(size));
			this.m_objGrid.getElements('td').forEach((el, i) => {
				el.textContent = chars[i] == '_' ? '' : (chars[i].charCodeAt(0) - 96);
			});
			console.timeEnd('createFromExport');
			return true;
		}
	}

	createEmptyGrid(size) {
		return (new Array(size)).fill(0).map(row => (new Array(size)).fill(-1));
	}

	async createMap(size) {
		this.reset();

		this.size = size;

		console.time('createMap');
		const s = Date.now();

		var grid;
		var attempts = 1;
		while (!(grid = this._create())) {
			if (Date.now() - s > 5000) {
				console.timeEnd('createMap');
				throw new Error('QUITTING AFTER TRYING FOR 5 SECONDS');
			}
			attempts++;
		}

		console.timeEnd('createMap');

		const playableGrid = this.hideCellsPlayable(grid);
		this.printGrid(playableGrid);

		return grid;
	}

	hideCellsPlayable(grid) {
		const coords = [];
		for ( let y = 0; y < grid.length; y++ ) {
			for ( let x = 0; x < grid[y].length; x++ ) {
				const group = grid[y][x];
				coords[group] || (coords[group] = []);
				coords[group].push(`${x}-${y}`);
			}
		}

		const shows = coords.map(coords => coords[parseInt(Math.random() * coords.length)]);

		return grid.map((cells, y) => cells.map((groupIndex, x) => {
			return shows.includes(`${x}-${y}`) ? coords[groupIndex].length : null;
		}));
	}

	_create() {
		const map = {
			grid: this.createEmptyGrid(this.size),
			groupSizes: [],
		};

		var next;
		var groupIndex = 0;
		while (next = this._next(map)) {
			if (!this._group(map, next, groupIndex++)) {
				return;
			}
		}

		if (!this._validate(map)) {
			return;
		}

// console.log(map);
		return map.grid;
	}

	_validate(map) {
		for ( let y = 0; y < map.grid.length; y++ ) {
			for ( let x = 0; x < map.grid[y].length; x++ ) {
				const group = map.grid[y][x];
				const groupSize = map.groupSizes[group];
				for (let dir of this.closeNeighbors) {
					if (map.grid[y+dir.y] && map.grid[y+dir.y][x+dir.x] != null) {
						const nGroup = map.grid[y+dir.y][x+dir.x];
						const nGroupSize = map.groupSizes[nGroup];
						if (nGroupSize == groupSize && nGroup != group) {
// this.debugGrid(map.grid);
// console.log(x, y, dir);
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	_restartGrid(map) {
		map.grid.forEach(row => row.fill(-1));
	}

	_group(map, start, groupIndex) {
		const size = this._rand(2, 9);

		var loc = start;
		var actualSize1 = 1;
		var actualSize2 = 0;

		for ( let i = 0; i < size; i++ ) {
			map.grid[loc.y][loc.x] = groupIndex;
			actualSize2++;

			this.closeNeighbors.sort(x => Math.random() > 0.5 ? 1 : 0);
			var found = false;
			for (let dir of this.closeNeighbors) {
				const nloc = loc.add(dir);
				if (map.grid[nloc.y] && map.grid[nloc.y][nloc.x] === -1) {
					found = true;
					actualSize1++;
					loc = nloc;
					break;
				}
			}

			if (!found) {
				break;
			}
		}

		map.groupSizes[groupIndex] = actualSize2;
// this.debugGrid(map.grid);

		if (actualSize1 == 1) {
			return false;
		}

		return true;
	}

	_next(map) {
		const empties = [];
		for ( let y = 0; y < map.grid.length; y++ ) {
			for ( let x = 0; x < map.grid[y].length; x++ ) {
				if (map.grid[y][x] == -1) {
					empties.push(new Coords2D(x, y));
				}
			}
		}

		if (empties.length) {
			return empties[parseInt(Math.random() * empties.length)];
		}
	}

	_rand(min, max) {
		return min + parseInt(Math.random() * (max+1-min));
	}

	debugGrid(grid) {
		console.log(grid.map(row => row.map(val => val == null || val == -1 ? '_' : String.fromCharCode(97+val)).join(' ')).join("\n"));
	}

	printGrid(grid) {
		this.m_objGrid.setHTML(this.createMapHtml(grid));
	}

	createMapHtml(grid) {
		var html = '';
		html += '<table>';
		for ( let y = 0; y < grid.length; y++ ) {
			html += '<tr>';
			for ( let x = 0; x < grid[y].length; x++ ) {
				const cell = grid[y][x];
				html += `<td>${cell != null && cell != -1 ? cell : ''}</td>`;
			}
			html += '</tr>';
		}
		html += '</table>';

		return html;
	}

	_groupColor(group) {
		this.colors || (this.colors = []);
		this.colors[group] || (this.colors[group] = '#' + ('000000' + (Math.random()*0xFFFFFF<<0).toString(16)).slice(-6));
		return this.colors[group];
	}

	listenCellDrag() {
		var draggingStart = null;
		var draggingEnd = null;

		this.m_objGrid.on(['mousedown', 'touchstart'], e => {
			if (e.rightClick) return;

			e.preventDefault();

			if (this.handleCellDragStart(draggingStart)) {
				draggingStart = e.target;
			}
		});
		this.m_objGrid.on(['mousemove', 'touchmove'], e => {
			if (!draggingStart) return;

			e.preventDefault();

			const el = document.elementFromPoint(e.pageX, e.pageY);
			if (el != draggingEnd) {
				draggingEnd = el;
				this.handleCellDragMove(draggingStart, draggingEnd);
			}
		});
		this.m_objGrid.on(['mouseup', 'touchend'], e => {
			if (!draggingStart) return;

			e.preventDefault();

			// draggingEnd || (draggingEnd = e.target);
			this.handleCellDragEnd(draggingStart, draggingEnd);
		});

		document.on(['mouseup', 'touchend'], function(e) {
			draggingStart = draggingEnd = null;
		});
	}

	listenControls() {
		this.listenCellDrag();

		$('#restart').on('click', e => {
			if (this.m_bGameOver) return this.createMap(this.size);

			this.colors.length = 0;
			this.draggingColor = null;
			this.m_objGrid.getElements('td').data('color', null);
		});

		$('#newgame').on('click', e => {
			const size = prompt('Size:', this.m_objGrid.getElements('tr').length);
			size && !isNaN(parseInt(size)) && requestIdleCallback(() => this.createMap(parseInt(size)));
		});

		$('#export').on('click', e => {
			location.hash = this.exportCurrent();
		});
	}

	exportCurrent() {
		return this.m_objGrid.getElements('td').map(td => {
			return td.textContent ? String.fromCharCode(96 + parseInt(td.textContent)) : '_';
		}).join('');
	}

	createStats() {
	}

}
