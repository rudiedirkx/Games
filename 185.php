<?php
// TETRIS SOLVER

// https://steamcommunity.com/sharedfiles/filedetails/?id=354590899

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Tetris solver</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!-- <? include 'tpl.onerror.php' ?> -->
<style>
table {
	border-collapse: collapse;
	display: inline-block;
	margin-right: .5em;
}
td {
	width: 30px;
	height: 30px;
	border: solid 1px #fff;
}
</style>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
</head>

<body>

<div id="output"></div>

<script>
const COLORS = ['red', 'green', 'blue', 'yellow', 'fuchsia', 'lime', 'orange', 'purple', 'black', 'grey'];

class TetrisSolver {
	constructor(width, height, objects) {
		this.width = width;
		this.height = height;
		this.objects = objects;
	}

	makeGrid() {
		return TetrisSolver.makeGridOf(this.width, this.height);
	}

	static makeGridOf(width, height) {
		const grid = [];
		for ( let y = 0; y < height; y++ ) {
			grid.push([]);
			for ( let x = 0; x < width; x++ ) {
				grid[y].push(0);
			}
		}

		return grid;
	}

	tryRandoms(n) {
		// return this.tryRandom(n);

		return new Promise(resolve => {
			for ( let i = 0; i < n; i++ ) {
				let attempt = this.tryRandom(n, i + 1);
				if ( attempt ) {
					return resolve([i + 1, attempt]);
				}
			}

			resolve([n, null]);
		});
	}

	tryRandom(todo, iter = 1) {
		// if ( todo == 0 ) {
		// 	return [iter, null];
		// }

		// return new Promise(resolve => {
		// 	// requestIdleCallback(() => {
		// 		const shapes = this.objects.map(object => {
		// 			return object.variations[parseInt(Math.random() * object.variations.length)];
		// 		});
		// 		shapes.sort(() => Math.random() > 0.5 ? -1 : 1);
		// 		const attempt = this.tryShapes(shapes);
		// 		resolve(attempt ? [iter, attempt] : this.tryRandom(todo - 1, iter + 1));
		// 	// });
		// });

		const shapes = this.objects.map(object => {
			return object.variations[parseInt(Math.random() * object.variations.length)];
		});
		shapes.sort(() => Math.random() > 0.5 ? -1 : 1);
		return this.tryShapes(shapes);
	}

	tryShapes(shapes) {
		const grid = this.makeGrid();
		const placements = [];
		for ( let i = 0; i < shapes.length; i++ ) {
			let placedAt = this.placeShape(grid, shapes[i]);
			if ( placedAt ) {
				placements.push([shapes[i], placedAt]);
			}
			else {
				return;
			}
		}

		return placements;
	}

	placeShape(grid, shape) {
// console.log('placing', this.printShape(shape), 'on', this.printGrid(grid));
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				let offset = new Coords2D(x, y);
				if ( this.placeShapeAt(grid, shape, offset) ) {
// console.log('placed at', offset, 'on', this.printGrid(grid));
					return offset;
				}
			}
		}

		return false;
	}

	placeShapeAt(grid, shape, offset) {
		const coords = shape.coords.map(C => C.add(offset));
		const free = coords.reduce((free, C) => {
			return free + parseInt(this.coordFree(grid, C));
		}, 0);
// console.log(free, 'free at', offset);

		if ( free != shape.coords.length ) return false;

		coords.forEach(C => {
			grid[C.y][C.x] = 1;
		});

		return true;
	}

	coordFree(grid, C) {
		return grid[C.y] && grid[C.y][C.x] === 0 ? 1 : 0;
	}

	printShape(shape) {
		return shape.toGrid();
	}

	printGrid(grid) {
		return JSON.parse(JSON.stringify(grid));
	}
}

class TetrisObject {
	constructor(name, coords) {
		this.name = name;
		this.coords = coords;
		this.width = Math.max(...coords.map(C => C.x)) + 1;
		this.height = Math.max(...coords.map(C => C.y)) + 1;
	}

	makeVariations() {
		var quarters = 0;
		var next;

		this.variations = [this];

		for ( let quarters = 1; quarters <= 3; quarters++ ) {
			let next = new this.constructor(`${this.name} ${quarters + 1}`, this.rotate(quarters));
			if ( !next.includedIn(this.variations) ) {
				this.variations.push(next);
			}
		}

		return this;
	}

	rotate(quarters) {
		return this.constructor.correctPosition(this.coords.map(C => C.rotate(quarters * Math.PI / 2).round()));
	}

	includedIn(objects) {
		return objects.some(object => object.equal(this));
	}

	equal(object) {
		if ( object.coords.length != this.coords.length ) return false;

		var same = 0;
		for ( let a of this.coords ) {
			for ( let b of object.coords ) {
				if ( a.equal(b) ) {
					same++;
					break;
				}
			}
		}

		return same == this.coords.length;
	}

	toGrid() {
		const grid = TetrisSolver.makeGridOf(4, 4);
		this.coords.forEach(C => grid[C.y][C.x] = 1);
		return grid;
	}

	static correctPosition(coords) {
		const x = 0 - Math.min(...coords.map(C => C.x));
		const y = 0 - Math.min(...coords.map(C => C.y));
		const offset = new Coords2D(x, y);
		return coords.map(C => C.add(offset));
	}
}

console.time('shapes');
const C00 = new Coords2D(0, 0);
const C10 = new Coords2D(1, 0);
const C20 = new Coords2D(2, 0);
const C30 = new Coords2D(3, 0);
const C01 = new Coords2D(0, 1);
const C11 = new Coords2D(1, 1);
const C21 = new Coords2D(2, 1);

const TETRIS_LINE = new TetrisObject('line', [C00, C10, C20, C30]).makeVariations();
const TETRIS_THREEWAY = new TetrisObject('3way', [C01, C11, C10, C21]).makeVariations();
const TETRIS_STEPR = new TetrisObject('step R', [C01, C11, C10, C20]).makeVariations();
const TETRIS_STEPL = new TetrisObject('step L', [C00, C10, C11, C21]).makeVariations();
const TETRIS_KNIGHTR = new TetrisObject('knight R', [C01, C11, C21, C20]).makeVariations();
const TETRIS_KNIGHTL = new TetrisObject('knight L', [C00, C01, C11, C21]).makeVariations();
const TETRIS_SQUARE = new TetrisObject('square', [C00, C10, C01, C11]).makeVariations();
console.timeEnd('shapes');

function solve(width, height, objects) {
	return new Promise(resolve => {
		const solver = new TetrisSolver(width, height, objects);
		console.log(solver);
		console.time('solve');
		const shapes = solver.tryRandoms(99999).then(([iterations, placements]) => {
			console.timeEnd('solve');
			console.log('solution in', iterations, 'iterations:', placements);
			if ( placements) {
				console.log('shapes', placements.map(([shape, C]) => shape.toGrid()));

				const grid = TetrisSolver.makeGridOf(solver.width, solver.height);
				placements.forEach(([shape, offset], i) => {
					// const color = '#' + Math.floor(Math.random() * 16777215).toString(16);
					const color = COLORS[i];
					shape.coords.forEach(C => {
						C = C.add(offset);
						grid[C.y][C.x] = color;
					});
				});
				console.log(grid);

				const html = '<table>' + grid.map(row => {
					return '<tr>' + row.map(color => `<td bgcolor="${color}"></td>`).join('') + '</tr>';
				}).join('') + '</table>';
				$('#output').innerHTML += html;
			}
			else {
				$('#output').innerHTML += ' failed ';
			}

			setTimeout(() => resolve(placements), 50);
		});
	});
}

requestAnimationFrame(function() {
	solve(3, 4, [
		TETRIS_KNIGHTL,
		TETRIS_KNIGHTL,
		TETRIS_STEPL,
	]).then(() => solve(4, 4, [
		TETRIS_LINE,
		TETRIS_KNIGHTL,
		TETRIS_KNIGHTR,
		TETRIS_STEPL,
	])).then(() => solve(6, 4, [
		TETRIS_KNIGHTL,
		TETRIS_KNIGHTL,
		TETRIS_KNIGHTR,
		TETRIS_THREEWAY,
		TETRIS_THREEWAY,
		TETRIS_STEPL,
	])).then(() => solve(8, 5, [
		TETRIS_STEPR,
		TETRIS_STEPL,
		TETRIS_KNIGHTR,
		TETRIS_LINE,
		TETRIS_SQUARE,
		TETRIS_THREEWAY,
		TETRIS_KNIGHTL,
		TETRIS_THREEWAY,
		TETRIS_LINE,
		TETRIS_SQUARE,
	]));
});
</script>

</body>

</html>
