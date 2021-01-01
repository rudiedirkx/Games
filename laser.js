"use strict";

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

	polyPoints() {
		if ( this.bendLeftDirs.includes(0) ) {
			return [
				new Coords2D(-1, -1),
				new Coords2D(1, 1),
			];
		}

		return [
			new Coords2D(1, -1),
			new Coords2D(-1, 1),
		];
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
			(inDirIndex - 1 + 4) % 4,
		];
	}

	polyPoints() {
		const shift = 0.3;
		const points = [
			new Coords2D(-1, -shift),
			new Coords2D(1, -shift),
			new Coords2D(0, 1 - shift),
		];
		if ( this.inDirIndex == 0 ) {
			return points;
		}
		return points.map((P) => P.rotate(Math.PI * 2 / 4 * this.inDirIndex));
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

	isStart() {
		this.start = true;
		return this;
	}
}

class Laser extends CanvasGame {

	static OFFSET = 40;
	static SQUARE = 60;

	constructor( canvas ) {
		super(canvas);

		this.mirrorTypes = [
			new ReflectMirror([0, 2]),
			new ReflectMirror([1, 3]),
			new SplitMirror(0),
			new SplitMirror(1),
			new SplitMirror(2),
			new SplitMirror(3),
		];
	}

	setLevelNum( n ) {
		this.levelNum = n;

		$('#level-num').textContent = `${(n + 1)} / ${Laser.levels.length}`;
		$('#prev').disabled = n <= 0;
		$('#next').disabled = n >= Laser.levels.length-1;
	}

	loadLevel( n ) {
		this.reset();

		this.setLevelNum(n);
		this.level = Laser.levels[n];

		this.canvas.width = Laser.OFFSET + this.level.map[0].length * Laser.SQUARE + Laser.OFFSET;
		this.canvas.height = Laser.OFFSET + this.level.map.length * Laser.SQUARE + Laser.OFFSET;

		this.lasers = [];
		this.lights = [];
		this.mirrors = this.createMirrors();
		this.recalculate();

		this.changed = true;
	}

	haveWon() {
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

	static color( type, half = false ) {
		const ins = half ? '8' : 'f';
		return '#' + [type & 1 ? ins : '0', type & 2 ? ins : '0', type & 4 ? ins : '0'].join('');
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Laser.OFFSET + source * Laser.SQUARE;
	}

	unscale( source ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return this.inside(C) ? C : null;
		}

		return Math.round((source - Laser.OFFSET - Laser.SQUARE/2) / (/*Laser.MARGIN +*/ Laser.SQUARE));
	}

	inside( coord ) {
		return coord.x >= 0 && coord.x < this.level.map[0].length && coord.y >= 0 && coord.y < this.level.map.length;
	}

	penetrable( coord ) {
		return this.inside(coord) && this.level.map[coord.y][coord.x] != 'x';
	}

	laserStart( lasing ) {
		const oppositeDir = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(lasing.dir) + 2) % 4 ];
		return lasing.add(oppositeDir).isStart();
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
		if ( !lasing.start && !this.penetrable(lasing.loc) ) return null;

		const dirIndex = Coords2D.dir4Names.indexOf(lasing.dir);

		const mirror = this.getMirror(lasing.loc);
		if ( mirror && !lasing.start ) {
			const dirIndexes = mirror.makeOuts(dirIndex);
			if ( dirIndexes.length ) {
				return dirIndexes.map((dirIndex) => lasing.add(Coords2D.dir4Coords[dirIndex]).newDir(Coords2D.dir4Names[dirIndex]));
			}

			return null;
		}

		const next = lasing.add(Coords2D.dir4Coords[dirIndex]);
		return [next];
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
					lasers.push(this._reverseOne(lasing[1]).isStart());
				}
			}

			output.push({type, path});
		}

		return output;
	}

	_reverseOne( lasing ) {
		const backStep = Coords2D.dir4Coords[ (Coords2D.dir4Names.indexOf(lasing.dir) + 2) % 4 ];
		return lasing.add(backStep);
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
		const style = {color: Laser.color(type), width: 3};
		this.drawLine(scale(from), scale(to), style);
	}

	drawMirrors() {
		for ( let y = 0; y < this.mirrors.length; y++ ) {
			for ( let x = 0; x < this.mirrors[y].length; x++ ) {
				let mirror = this.mirrors[y][x];
				if ( mirror >= 0 ) {
					let points = this.mirrorTypes[mirror].polyPoints();
					this.drawMirror(new Coords2D(x, y), points);
				}
			}
		}
	}

	drawMirror( square, points ) {
		const center = this.scale(square.add(new Coords2D(.5, .5)));

		const scale = (C) => {
			return C.multiply(15).add(center);
		};

		if ( points.length == 2 ) {
			const style = {color: '#fff'};
			this.drawLine(scale(points[0]), scale(points[1]), style);
			return;
		}

		this.ctx.fillStyle = '#fff';
		this.ctx.beginPath();
		points.map(scale).forEach((P, i) => this.ctx[i ? 'lineTo' : 'moveTo'](P.x, P.y));
		this.ctx.closePath();
		this.ctx.fill();
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

			let style = {radius: 4, color: Laser.color(C[3])};
			this.drawDot(pos, style);
		});
	}

	drawBlock( coord ) {
		const MARGIN = 9;
		const tl = this.scale(coord).add(new Coords2D(MARGIN, MARGIN));
		this.ctx.fillStyle = '#000';
		this.ctx.fillRect(tl.x + 0.5, tl.y + 0.5, Laser.SQUARE - MARGIN*2, Laser.SQUARE - MARGIN*2);
	}

	drawSquare( coord, type ) {
		const MARGIN = 3;
		const tl = this.scale(coord).add(new Coords2D(MARGIN, MARGIN));
		this.ctx.strokeStyle = Laser.color(type);
		this.ctx.lineWidth = 3;
		this.ctx.strokeRect(tl.x + 0.5, tl.y + 0.5, Laser.SQUARE - MARGIN*2, Laser.SQUARE - MARGIN*2);
	}

	drawLight( coord, type ) {
		const MARGIN = 5;
		const tl = this.scale(coord).add(new Coords2D(MARGIN, MARGIN));
		this.ctx.fillStyle = Laser.color(type) + '7';
		this.ctx.fillRect(tl.x + 0.5, tl.y + 0.5, Laser.SQUARE - MARGIN*2, Laser.SQUARE - MARGIN*2);
	}

	listenControls() {
		this.listenActions();
		this.listenClick();
		this.listenWheel();
	}

	listenActions() {
		$('#prev').on('click', (e) => {
			this.loadLevel(this.levelNum - 1);
		});
		$('#next').on('click', (e) => {
			this.loadLevel(this.levelNum + 1);
		});
	}

	listenWheel() {
		this.canvas.on('wheel', (e) => {
			this.handleWheel(e.subjectXY, e.deltaY < 0 ? -1 : 1);
		});
	}

	handleWheel( coord, dir ) {
		const square = this.unscale(coord);
		square && this.changeMirror(square, dir);
	}

	handleClick( coord ) {
		const square = this.unscale(coord);
		square && this.changeMirror(square, 1);
	}

	changeMirror( square, delta ) {
		const L = this.mirrorTypes.length + 1;
		var mirror = this.mirrors[square.y][square.x];
		mirror = ((mirror + 1 + delta + L) % L) - 1;
		this.mirrors[square.y][square.x] = mirror;
		this.changed = true;

		this.recalculate();

		this.winOrLose();
	}

	setTime() {
	}

}

class LaserEditor extends GridGameEditor {

	createEditor() {
		super.createEditor();
		$('#level-sizes').hide();
	}

	createMap() {
		super.createMap(7, 7);
	}

	createdMap() {
		this.m_objGrid.getElements('td').forEach((cell) => {
			if ( this.isEdge(cell) ) {
				cell.addClass('edge');
			}
		})
	}

	defaultCellType() {
		return '0';
	}

	cellTypes() {
		return {
			"0": 'None',
			"block": 'Block',
			"1": 'Red',
			"2": 'Green',
			"3": 'Red+Green',
			"4": 'Blue',
			"5": 'Red+Blue',
			"6": 'Green+Blue',
			"7": 'Red+Green+Blue',
		};
	}

	createCellTypeCell( type ) {
		const color = type == '0' ? 'translarent' : Laser.color(parseInt(type)) + '8';
		return '<td class="' + type + '" style="background-color: ' + color + '"></td>';
	}

	handleCellClick( cell ) {
		var type = this.getType();
		if ( this['setType_' + type] ) {
			return this['setType_' + type](cell);
		}

		type = parseInt(type);
		if ( isNaN(type) ) {
			return alert('Invalid cell type?');
		}

		if ( type == 0 ) {
			return this.setEmpty(cell);
		}

		if ( cell.hasClass('edge') ) {
			return this.setEdge(cell, type);
		}

		return this.setTarget(cell, type);
	}

	setType_block( cell ) {
		cell.toggleClass('block');
	}

	setEmpty( cell ) {
		delete cell.dataset.type;
		cell.style.backgroundColor = '';
	}

	setEdge( cell, type ) {
		if ( ![1, 2, 4].includes(type) ) {
			return alert('Lasers must be base colors (RGB).');
		}

		this.setTarget(cell, type);
	}

	setTarget( cell, type ) {
		cell.dataset.type = type;
		cell.style.backgroundColor = Laser.color(type) + '8';
	}

	isEdge( cell ) {
		const C = this.getCoord(cell);
		const W = this.m_objGrid.rows[0].cells.length;
		const H = this.m_objGrid.rows.length;
		return C.x == 0 || C.x == W - 1 || C.y == 0 || C.y == H - 1;
	}

	exportLevel() {
		const map = [];
		for ( let y = 1; y < 6; y++ ) {
			var row = '';
			for ( let x = 1; x < 6; x++ ) {
				let cell = this.getCell(new Coords2D(x, y));
				if ( cell.hasClass('block') ) {
					row += 'x';
				}
				else {
					let type = cell.data('type');
					row += isNaN(parseInt(type)) ? ' ' : type;
				}
			}
			map.push(row);
		}

		const lasers = this.m_objGrid.getElements('.edge[data-type]').map((cell) => {
			const type = parseInt(cell.data('type'));
			var C = this.getCoord(cell);
			var dir;
			if ( C.y == 0 ) {
				dir = 'd';
			}
			else if ( C.y == 6 ) {
				dir = 'u';
			}
			else if ( C.x == 0 ) {
				dir = 'r';
			}
			else if ( C.x == 6 ) {
				dir = 'l';
			}

			C = C.add(Coords2D.dir4Coords[Coords2D.dir4Names.indexOf(dir)]).add(new Coords2D(-1, -1));
			return C.toArray().concat([dir,  type]);
		});

		var level = {map, lasers};
		this.validateLevel(level);
		return level;
	}

	validateLevel( level ) {
		// @todo
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push("\t\t'lasers' => [");
		r.each(level.lasers, laser => code.push("\t\t\t" + JSON.stringify(laser) + ","));
		code.push("\t\t],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

}
