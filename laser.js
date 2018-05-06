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

	inside( coord ) {
		return coord.x >= 0 && coord.x < this.level.map[0].length && coord.y >= 0 && coord.y < this.level.map.length;
	}

	laserStart( C ) {
		let oppositeDir = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(C[2]) + 2) % 4 ];
		return Coords2D.fromArray(C).add(oppositeDir);
	}

	createLights() {
		const W = this.level.map[0].length;
		const H = this.level.map.length;

		const lights = [];
		for ( let y = 0; y < H; y++ ) {
			let row = [];
			for ( let x = 0; x < W; x++ ) {
				row.push(0);
			}
			lights.push(row);
		}
		return lights;
	}

	drawContent() {
		if ( !this.level ) return;

		this.drawLasers();
		this.drawMirrors();
	}

	drawLasers() {
		const lights = this.createLights();

		this.level.lasers.forEach((C) => {
			let type = C[3];
			let dir = Coords2D.dir4Names.indexOf(C[2]);
			let startLoc = this.laserStart(C);
			let loc = startLoc;
			while ( loc == startLoc || this.inside(loc) ) {
				let nextLoc = loc.add(Coords2D.dir4Coords[dir]);
				this.drawLaser(loc, nextLoc, type);

				if ( loc != startLoc ) {
					lights[loc.y][loc.x] |= type;
				}

				loc = nextLoc;
			}
		});

		lights.forEach((row, y) => {
			row.forEach((color, x) => {
				if ( color > 0 ) {
					this.drawLight(new Coords2D(x, y), color);
				}
			});
		});

		console.log(lights);
	}

	drawLaser( from, to, type ) {
console.log('laser', from, to);
		const half = new Coords2D(.5, .5);
		const scale = (C) => this.scale(C.add(half)).add(new Coords2D(.5, .5));
		const style = {color: this.color(type), width: 3};
		this.drawLine(scale(from), scale(to), style);
	}

	drawMirrors() {
	}

	drawStructure() {
		if ( !this.level ) return;

		// grid
		const W = this.level.map[0].length;
		const H = this.level.map.length;
		this.ctx.imageSmoothingEnabled = false;
		for ( let y = 0; y < H; y++ ) {
			for ( let x = 0; x < W; x++ ) {
				let target = parseInt(this.level.map[y][x].trim() || 0);
				this.drawSquare(new Coords2D(x, y), target);
			}
		}
		this.ctx.imageSmoothingEnabled = true;

		// laser starts
		this.level.lasers.forEach((C) => {
			let moreDir = Coords2D.dir4Coords[ Coords2D.dir4Names.indexOf(C[2]) ].multiply(4);
			let oppositeDir = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(C[2]) + 2) % 4 ];
			let cell = Coords2D.fromArray(C).add(oppositeDir);
			let style = {radius: 4, color: this.color(C[3])};
			this.drawDot(this.scale(cell.add(new Coords2D(.5, .5))).add(moreDir), style);
		});
	}

	drawSquare( coord, type ) {
		const tl = this.scale(coord).add(new Coords2D(3, 3));
		this.ctx.strokeStyle = this.color(type);
		this.ctx.lineWidth = 3;
		this.ctx.strokeRect(tl.x + 0.5, tl.y + 0.5, 34, 34);
	}

	drawLight( coord, type ) {
		const tl = this.scale(coord).add(new Coords2D(5, 5));
		this.ctx.fillStyle = this.color(type) + '7';
		this.ctx.fillRect(tl.x + 0.5, tl.y + 0.5, 30, 30);
	}

	setTime() {
	}

}
