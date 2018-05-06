Coords2D.prototype.multiply = function( factor ) {
	return new this.constructor(this.x * factor, this.y * factor);
};

class Laser extends CanvasGame {

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

	trajectLasers() {
		return this.level.lasers.map((C) => {
			const type = C[3];
			let dir = Coords2D.dir4Names.indexOf(C[2]);
			let loc = this.laserStart(C);
			const path = [loc];
			while ( path.length == 1 || this.inside(loc) ) {
				const nextLoc = loc.add(Coords2D.dir4Coords[dir]);
				path.push(nextLoc);

				loc = nextLoc;
			}
			return {type, path};
		});
	}

	trajectLights( lasers ) {
		const lights = this.createLights();
		lasers.forEach((laser) => {
			laser.path.forEach((C) => {
				if ( lights[C.y] && lights[C.y][C.x] != null ) {
					lights[C.y][C.x] |= laser.type;
				}
			});
		});
		return lights;
	}

	getLaserOffset( type ) {
		const typeOffset = type & 1 ? -3 : (type & 4 ? 3 : 0);
		return new Coords2D(typeOffset, typeOffset);
	}

	drawLasers() {
		const lasers = this.trajectLasers();
console.log(lasers);

		const lights = this.trajectLights(lasers);
console.log(lights);

		lights.forEach((row, y) => {
			row.forEach((color, x) => {
				if ( color > 0 ) {
					this.drawLight(new Coords2D(x, y), color);
				}
			});
		});

		lasers.forEach((laser) => {
			for ( let i = 1; i < laser.path.length; i++ ) {
				const from = laser.path[i-1];
				const to = laser.path[i];
				this.drawLaser(from, to, laser.type);
			}
		});
	}

	drawLaser( from, to, type ) {
		const typeOffset = this.getLaserOffset(type);
		const half = new Coords2D(.5, .5);
		const scale = (C) => this.scale(C.add(half)).add(new Coords2D(.5, .5)).add(typeOffset);
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
				let target = this.level.map[y][x].trim();
				let loc = new Coords2D(x, y);
				this.drawSquare(loc, parseInt(target || 0));

				if ( target == 'x' ) {
					this.drawBlock(loc);
				}
			}
		}
		this.ctx.imageSmoothingEnabled = true;

		// laser starts
		this.level.lasers.forEach((C) => {
			let moreDir = Coords2D.dir4Coords[ Coords2D.dir4Names.indexOf(C[2]) ].multiply(4);
			let oppositeDir = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(C[2]) + 2) % 4 ];
			let cell = Coords2D.fromArray(C).add(oppositeDir);

			let typeOffset = this.getLaserOffset(C[3]);
			let pos = this.scale(cell.add(new Coords2D(.5, .5))).add(moreDir).add(typeOffset);

			let style = {radius: 4, color: this.color(C[3])};
			this.drawDot(pos, style);
		});
	}

	drawBlock( coord ) {
		const tl = this.scale(coord).add(new Coords2D(8, 8));
		this.ctx.fillStyle = '#000';
		this.ctx.fillRect(tl.x + 0.5, tl.y + 0.5, 24, 24);
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
