<?php
// BLOCK 3x

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Block 3x</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!-- <script>window.onerror = function(e) { alert(e); };</script> -->
<style>
table {
	border-collapse: collapse;
	display: inline-block;
}
td {
	width: 30px;
	height: 30px;
	border: solid 1px #ccc;
	vertical-align: middle;
	text-align: center;
	--color: #eee;
	--color-light: #eee;
	background-color: var(--color);
	color: transparent;
}
td[data-color="0"] {
	/*color: var(--color);*/
}
td[data-color="1"] {
	--color: #f00;
	--color-light: #f006;
	/*color: #fff;*/
}
td[data-color="2"] {
	--color: #080;
	--color-light: #0806;
	/*color: #fff;*/
}
td.hilite {
	border: solid 2px #000;
	background-color: var(--color-light);
}
td.hilite:not([data-color="0"]) {
	/*color: #000;*/
}

input[type="number"] {
	-moz-appearance: textfield;
	width: 2em;
	text-align: center;
}
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
	-webkit-appearance: none;
	appearance: none;
	margin: 0;
}
</style>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
</head>

<body>

<div id="input"></div>
<hr />
<div id="output"></div>

<script>
RUN_INCOMPLETE = 0;
RUN_COMPLETE = 1;
RUN_WIN = 2;

COLORS = 2;

class Block3xSolver {
	constructor(grid) {
		this.grid = grid;
		this.H = this.grid.length;
		this.W = this.grid[0].length;
		this.switches = this.makeSwitches();
	}

	makeSwitches() {
		const switches = [];
		for ( let y = 0; y < this.H; y++ ) {
			for ( let x = 0; x < this.W; x++ ) {
				const from = new Coords2D(x, y);
				const to1 = new Coords2D(x+1, y);
				const to2 = new Coords2D(x, y+1);

				this.inBounds(to1) && switches.push(new Block3xSwitch(from, to1));
				this.inBounds(to2) && switches.push(new Block3xSwitch(from, to2));
			}
		}

		return switches;
	}

	filterSwitches(grid) {
		console.time('filterSwitches');

		const switches = this.switches.filter(move => this.switchIsUseful(grid || this.grid, move));

		console.timeEnd('filterSwitches');
		return switches;
	}

	inBounds(coord) {
		return coord.x < this.W && coord.y < this.H;
	}

	switchIsUseful(grid, move) {
		const from = this.getColor(grid, move.from);
		const to = this.getColor(grid, move.to);
		return from != to;
	}

	getColor(grid, coord) {
		return grid[coord.y][coord.x];
	}

	setColor(grid, coord, color) {
		grid[coord.y][coord.x] = color;
	}

	runAll(moves = 3) {
		const switches = this.switches;

		console.time('runAll');

		const moveNames = [];
		var code = ['let runs = 0;'];
		for ( let m = 1; m <= moves; m++ ) {
			code.push(`for ( let i${m} = 0; i${m} < switches.length; i${m}++ ) {`);
			code.push(`	const move${m} = switches[i${m}];`);
			moveNames.push(`move${m}`);
		}
		code.push(`const run = [${moveNames.join(', ')}];`);
		code.push(`const ran = solver.runOne(run);`);
		code.push(`if ( ran != RUN_INCOMPLETE ) {`);
		code.push(`	runs++;`);
		code.push(`	if ( ran == RUN_WIN ) {`);
		code.push(`		return [runs, run];`);
		code.push(`	}`);
		code.push(`}`);
		for ( let m = 1; m <= moves; m++ ) {
			code.push('}');
		}
		code.push(`return [runs, null];`);

		const fn = new Function('solver', 'switches', code.join('\n'));

		const [runs, run] = fn(this, switches);

		console.timeEnd('runAll');
		console.log('Runs', runs);
		return run;
	}

	copyGrid() {
		return JSON.parse(JSON.stringify(this.grid));
	}

	runOne(switches) {
		const grid = this.copyGrid();

		for ( let move of switches ) {
			if ( !this.switchIsUseful(grid, move) ) {
				return RUN_INCOMPLETE;
			}

			this.doSwitch(grid, move);
			this.eliminateLines(grid);
		}

		const left = this.countBlocks(grid);
		return left == 0 ? RUN_WIN : RUN_COMPLETE;
	}

	countBlocks(grid) {
		return grid.reduce((num1, row) => {
			return num1 + row.reduce((num2, cell) => {
				return num2 + (cell == 0 ? 0 : 1);
			}, 0);
		}, 0);
	}

	eliminateLines(grid) {
		const lines = [];

		let saveLine = (y, start, size) => {
			const line = [];
			for ( let i = 0; i < size; i++ ) {
				line.push(new Coords2D(start + i, y));
			}
			lines.push(line);
		};

		for ( let y = 0; y < this.H; y++ ) {
			let lastColor = grid[y][0];
			let lastStart = 0;
			let lastSize = 1;

			let checkAndSaveLine = () => lastColor != 0 && lastSize >= 3 && saveLine(y, lastStart, lastSize);

			for ( let x = 1; x < this.W; x++ ) {
				let newColor = grid[y][x];
				if ( newColor == lastColor ) {
					lastSize++;
				}
				else {
					checkAndSaveLine();

					lastColor = newColor;
					lastStart = x;
					lastSize = 1;
				}
			}

			checkAndSaveLine();
		}

		saveLine = (x, start, size) => {
			const line = [];
			for ( let i = 0; i < size; i++ ) {
				line.push(new Coords2D(x, start + i));
			}
			lines.push(line);
		};

		for ( let x = 0; x < this.W; x++ ) {
			let lastColor = grid[0][x];
			let lastStart = 0;
			let lastSize = 1;

			let checkAndSaveLine = () => lastColor != 0 && lastSize >= 3 && saveLine(x, lastStart, lastSize);

			for ( let y = 1; y < this.H; y++ ) {
				let newColor = grid[y][x];
				if ( newColor == lastColor ) {
					lastSize++;
				}
				else {
					checkAndSaveLine();

					lastColor = newColor;
					lastStart = y;
					lastSize = 1;
				}
			}

			checkAndSaveLine();
		}

		lines.forEach(line => line.forEach(coord => grid[coord.y][coord.x] = 0));
	}

	doSwitch(grid, move) {
		const from = this.getColor(grid, move.from);
		const to = this.getColor(grid, move.to);

		this.setColor(grid, move.from, to);
		this.setColor(grid, move.to, from);
	}
}

class Block3xSwitch {
	constructor(from, to) {
		this.from = from;
		this.to = to;
	}

	// equal(move) {
	// 	return (this.from.equal(move.from) && this.to.equal(move.to)) ||
	// 		(this.from.equal(move.to) && this.to.equal(move.from));
	// }
}

class Block3xUI {
	constructor(defaultGrid) {
		this.input = $('#input');
		this.output = $('#output');

		this.makeInput(defaultGrid);
	}

	makeGrid(size) {
		const grid = [];
		for ( let y = 0; y < size; y++ ) {
			const row = [];
			for ( let x = 0; x < size; x++ ) {
				row.push(0);
			}
			grid.push(row);
		}

		return grid;
	}

	makeInput(grid) {
		grid || (grid = this.makeGrid(6));

		var html = this.makeHtml(grid);

		html += ' Moves: <input type="number" value="3" min="1" /> <button>Solve</button>';

		this.input.setHTML(html);

		this.input.on('click', 'td', e => {
			const cell = e.subject;
			this.toggleColor(cell);
		});

		this.input.on('click', 'button', e => {
			const grid = this.extractGrid();
			const moves = parseInt(this.input.getElement('input').value);
			this.solve(grid, moves);
		});
	}

	toggleColor(cell) {
		var color = (cell.dataset.color + 1) % (COLORS + 1);
		cell.dataset.color = color;
	}

	makeHtml(grid, hiliteMove) {
		return '<table>' + grid.map((row, y) => {
			return '<tr>' + row.map((cell, x) => {
				const coord = new Coords2D(x, y);
				const hilite = hiliteMove && (coord.equal(hiliteMove.from) || coord.equal(hiliteMove.to));
				return `<td data-color="${cell}" class="${hilite ? 'hilite' : ''}"></td>`;
			}).join('') + '</tr>';
		}).join('') + '</table>';
	}

	extractGrid() {
		return this.input.getElements('tr').map(tr => {
			return tr.getElements('td').map(td => parseInt(td.dataset.color));
		});
	}

	solve(grid, moves) {
		const solver = new Block3xSolver(grid);
		this.output.setHTML(this.makeHtml(solver.grid));

		const run = solver.runAll(moves);
		console.log('result', run);

		if ( !run ) return;

		const htmls = [this.makeHtml(grid, run[0])];

		run.forEach((move, i) => {
			solver.doSwitch(grid, move);
			solver.eliminateLines(grid);

			htmls.push(this.makeHtml(grid, run[i+1]));
		});
		this.output.setHTML(htmls.join(' '));
	}
}


requestAnimationFrame(function() {
	const ui = new Block3xUI([
		[0, 0, 0, 0, 0],
		[0, 1, 2, 1, 0],
		[0, 2, 1, 2, 0],
		[0, 2, 2, 0, 0],
		[0, 0, 0, 0, 0],
	]);
});
</script>

</body>

</html>
