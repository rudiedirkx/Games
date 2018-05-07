Coords2D.prototype.multiply = function( factor ) {
	return new this.constructor(this.x * factor, this.y * factor);
};

class Mirror {
}

class ReflectMirror extends Mirror {
	constructor( bendLeftDirs ) {
		super();
		this.bendLeftDirs = bendLeftDirs;
	}

	makeOuts( inDirIndex ) {
		const delta = this.bendLeftDirs.includes(inDirIndex) ? -1 : 1;
		return [(inDirIndex + delta + 4) % 4];
	}
}

class SplitMirror extends Mirror {
	constructor( inDirIndex ) {
		super();
		this.inDirIndex = inDirIndex;
	}

	makeOuts( inDirIndex ) {
		if ( inDirIndex != this.inDirIndex ) return [];

		return [
			(inDirIndex + 1) % 4,
			(inDirIndex - 1) % 4,
		];
	}
}

class Lasing {
	constructor( loc, dir, type ) {
		this.loc = loc;
		this.dir = dir;
		this.type = type;
		this.start = false;
	}

	add( coord ) {
		return new Lasing(this.loc.add(coord), this.dir, this.type);
	}

	newDir( dirName ) {
		this.dir = dirName;
		return this;
	}
}

class Laser extends CanvasGame {

	constructor( canvas ) {
		super(canvas);

		this.mirrorTypes = [
			new ReflectMirror([0, 2]),
			new ReflectMirror([1, 3]),
			// new SplitMirror(0),
			// new SplitMirror(1),
			// new SplitMirror(2),
			// new SplitMirror(3),
		];
	}

	loadLevel( n ) {
		this.reset();

		this.levelNum = n;
		this.level = Laser.levels[n];

		document.querySelector('#level-num').textContent = n + 1;
		document.querySelector('#prev').disabled = n <= 0;
		document.querySelector('#next').disabled = n >= Laser.levels.length-1;

		this.canvas.width = 30 * 2 + this.level.map[0].length * 40;
		this.canvas.height = 30 * 2 + this.level.map.length * 40;

		this.lasers = [];
		this.lights = [];
		this.mirrors = this.createMirrors();

		this.changed = true;
	}

	haveWon() {
		this.recalculate();

		for ( let y = 0; y < this.level.map.length; y++ ) {
			for ( let x = 0; x < this.level.map[y].length; x++ ) {
				let target = parseInt(this.level.map[y][x].trim());
				if ( !isNaN(target) && this.lights[y][x] !== target ) {
					return false;
				}
			}
		}

		return true;
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

	penetrable( coord ) {
		return this.inside(coord) && this.level.map[coord.y][coord.x] != 'x';
	}

	laserStart( lasing ) {
		const oppositeDir = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(lasing.dir) + 2) % 4 ];
		const start = lasing.add(oppositeDir);
		start.start = true;
		return start;
	}

	createLights() {
		return this.createEmptyBoard(0);
	}

	createMirrors() {
		return this.createEmptyBoard(-1);
	}

	createEmptyBoard( value ) {
		const W = this.level.map[0].length;
		const H = this.level.map.length;

		const lights = [];
		for ( let y = 0; y < H; y++ ) {
			let row = [];
			for ( let x = 0; x < W; x++ ) {
				row.push(value);
			}
			lights.push(row);
		}
		return lights;
	}

	drawContent() {
		if ( !this.level ) return;

		this.drawLasers();
		this.drawMirrors();
		this.drawBlocks();
	}

	getMirror( coord ) {
		if ( this.mirrors[coord.y] && this.mirrors[coord.y][coord.x] != null ) {
			const mirror = this.mirrors[coord.y][coord.x];
			if ( mirror != -1 ) {
				return this.mirrorTypes[mirror];
			}
		}
	}

	_trajectLaser( lasing ) {
		if ( lasing.start || this.penetrable(lasing.loc) ) {
			const dirIndex = Coords2D.dir4Names.indexOf(lasing.dir);

			const mirror = this.getMirror(lasing.loc);
			if ( mirror ) {
				const dirIndexes = mirror.makeOuts(dirIndex);
				return dirIndexes.map((dirIndex) => lasing.add(Coords2D.dir4Coords[dirIndex]).newDir(Coords2D.dir4Names[dirIndex]));
			}

			const next = lasing.add(Coords2D.dir4Coords[dirIndex]);
			return [next];
		}
	}

	_trajectLasers( lasers ) {
		const output = [];
		for ( let i = 0; i < lasers.length; i++ ) {
			var lasing = [lasers[i]];
			const type = lasing[0].type;
			const path = [lasing[0].loc];
			while ( lasing = this._trajectLaser(lasing[0]) ) {
				path.push(lasing[0].loc);

				if ( lasing.length > 1 ) {
					lasers.push(lasing[1]);
				}
			}

			output.push({type, path});
		}

		return output;
	}

	trajectLasers() {
		var input = this.level.lasers.map((C) => this.laserStart(new Lasing(Coords2D.fromArray(C), C[2], C[3])));
		return this._trajectLasers(input);
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

	recalculate() {
		this.lasers = this.trajectLasers();
		this.lights = this.trajectLights(this.lasers);
	}

	drawLasers() {
		this.recalculate();

		this.lights.forEach((row, y) => {
			row.forEach((color, x) => {
				if ( color > 0 ) {
					this.drawLight(new Coords2D(x, y), color);
				}
			});
		});

		this.lasers.forEach((laser) => {
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

	drawBlocks() {
		const W = this.level.map[0].length;
		const H = this.level.map.length;
		for ( let y = 0; y < H; y++ ) {
			for ( let x = 0; x < W; x++ ) {
				let target = this.level.map[y][x].trim();
				if ( target == 'x' ) {
					let loc = new Coords2D(x, y);
					this.drawBlock(loc);
				}
			}
		}
	}

	drawStructure() {
		if ( !this.level ) return;

		// grid
		const W = this.level.map[0].length;
		const H = this.level.map.length;
		for ( let y = 0; y < H; y++ ) {
			for ( let x = 0; x < W; x++ ) {
				let target = this.level.map[y][x].trim();
				let loc = new Coords2D(x, y);
				this.drawSquare(loc, parseInt(target || 0));
			}
		}

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
		const tl = this.scale(coord).add(new Coords2D(9, 9));
		this.ctx.fillStyle = '#000';
		this.ctx.fillRect(tl.x + 0.5, tl.y + 0.5, 22, 22);
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

	listenControls() {
		this.listenActions();
		this.listenClick();
	}

	listenActions() {
		document.querySelector('#prev').on('click', (e) => {
			this.loadLevel(this.levelNum - 1);
		});
		document.querySelector('#next').on('click', (e) => {
			this.loadLevel(this.levelNum + 1);
		});
	}

	handleClick( coord ) {
		const square = this.getSquare(coord);
		var mirror = this.mirrors[square.y][square.x];
		mirror++;
		if ( mirror >= this.mirrorTypes.length ) {
			mirror = -1;
		}
		this.mirrors[square.y][square.x] = mirror;
		this.changed = true;

		this.winOrLose();
	}

	getSquare( coord ) {
		const x = Math.floor((coord.x - 30) / 40);
		const y = Math.floor((coord.y - 30) / 40);
		const square = new Coords2D(x, y);
		if ( this.inside(square) ) {
			return square;
		}
	}

	setTime() {
	}

}
