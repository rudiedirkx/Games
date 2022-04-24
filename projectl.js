class Shape {
	static ON = 'x';

	constructor(...grid) {
		this.grid = grid;
		this.width = Math.max(...this.grid.map(line => line.length));
		this.heigth = this.grid.length;
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
	constructor(color, shape, rotate = false) {
		this.color = color;
		this.shape = shape;
		if (rotate) {
			this.shape = this.shape.rotate();
		}
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

	createGame() {
		this.SHAPES = this.createShapes();
		this.STONES = this.createStones();
		this.TARGETS = this.createTargets();
	}

	startGame() {
		console.time('draw all shapes');
		const html1 = this.STONES.map((stone, i) => {
			return '<div>' + this.createShapeHtml(stone.shape, i, stone.color) + '</div>';
		}).join(' ');
		const html2 = this.TARGETS.map((target, i) => {
			return '<div>' + this.createShapeHtml(target.shape, target.score, target.stone.color) + '</div>';
		}).join(' ');
		const html3 = this.SHAPES.map((shape, i) => {
			return '<div>' + this.createShapeHtml(shape, i, '#fff') + '</div>';
		}).join(' ');
		document.body.setHTML(`${html1} ---- ${html2} ---- ${html3}`);
		console.timeEnd('draw all shapes');

		const rotate = str => {
// console.log('rotate str', str);
			const shape = Shape.unserialize(str);
console.log('shape', shape);
			console.log(shape.grid.join("\n"));
// debugger;
			const rotated = shape.rotate();
console.log('rotated', rotated);
			console.log(rotated.grid.join("\n"));
			return rotated;
		};
		document.body.on('click', '[data-shape]', e => {
			const rotated = rotate(e.subject.data('shape'));
			const title = e.subject.getElement('.shape').textContent;
			const color = e.subject.css('--color');
			e.subject.parentNode.setHTML(this.createShapeHtml(rotated, title, color));
		});
		rotate(this.SHAPES[14].serialize());

		// this.SHAPES.forEach((shape1, i1) => {
		// 	const i2 = this.SHAPES.findIndex((shape2, i2) => i1 != i2 && shape1.equal(shape2));
		// 	if (i2 != -1 && i2 > i1) {
		// 		console.log(i1, i2);
		// 	}
		// });
	}

	createShapeHtml(shape, title, color) {
		const si = this.SHAPES.indexOf(shape);

		var titled = false;
		const html = [`<table class="card" data-shape="${shape.serialize()}" style="--color: ${color}">`];
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
		html.push(`</table>`);
		return html.join('');
	}

	createStones() {
		return [
			new Stone('yellow', this.SHAPES[0]),
			new Stone('green', this.SHAPES[1], true),
			new Stone('blue', this.SHAPES[2]),
			new Stone('orange', this.SHAPES[3]), // area 3
			new Stone('fuchsia', this.SHAPES[4]),
			new Stone('red', this.SHAPES[5]),
			new Stone('orange', this.SHAPES[6], true), // area 4
			new Stone('lightblue', this.SHAPES[7], true),
			new Stone('purple', this.SHAPES[8], true),
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
