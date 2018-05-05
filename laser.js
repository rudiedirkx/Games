Coords2D.prototype.multiply = function( factor ) {
	return new this.constructor(this.x * factor, this.y * factor);
};

class Laser extends CanvasGame {

	createGame() {

	}

	reset() {
		super.reset();


	}

	loadLevel( n ) {
		this.reset();

		this.levelNum = n;
		this.level = Laser.levels[n];

		this.canvas.width = 30 * 2 + this.level.map[0].length * 40;
		this.canvas.height = 30 * 2 + this.level.map.length * 40;

		this.changed = true;
	}

	color( type, half = false ) {
		const ins = half ? '8' : 'f';
		return '#' + [type & 1 ? ins : '0', type & 2 ? ins : '0', type & 4 ? ins : '0'].join('');
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return 30 + source * 40;
	}

	drawStructure() {
		if ( !this.level ) return;

		// grid
		const W = this.level.map[0].length;
		const H = this.level.map.length;
		this.ctx.imageSmoothingEnabled = false;
		for ( let x = 0; x < W; x++ ) {
			for ( let y = 0; y < H; y++ ) {
				let target = parseInt(this.level.map[y][x].trim() || 0);
				this.drawSquare(new Coords2D(x, y), target);
			}
		}
		this.ctx.imageSmoothingEnabled = true;

		// lasers
		this.level.lasers.forEach((C) => {
			let moreDir = Coords2D.dir4Coords[ Coords2D.dir4Names.indexOf(C[2]) ].multiply(10);
			let oppositeDir = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(C[2]) + 2) % 4 ];
			let cell = Coords2D.fromArray(C).add(oppositeDir);
			let style = {radius: 4, color: this.color(C[3])};
			this.drawDot(this.scale(cell.add(new Coords2D(.5, .5))).add(moreDir), style);
		});
	}

	drawSquare( coord, target = 0 ) {
		const tl = this.scale(coord).add(new Coords2D(3, 3));
		this.ctx.strokeStyle = this.color(target);
		this.ctx.lineWidth = 3;
		this.ctx.strokeRect(tl.x, tl.y, 34, 34);
	}

	setTime() {
	}

}
