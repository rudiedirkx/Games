"use strict";

class Squarescape extends CanvasGame {

	static LEVELS = [];

	static OFFSET = 20;
	static SQUARE = 40;
	static MARGIN = 3;

	reset() {
		super.reset();

		this.setMoves(0);

		this.levelNum = 0;
		this.width = 0;
		this.height = 0;
		this.map = null;
		this.player = null;
		this.end = null;
		this.collectibles = [];

		// this.dragging = null;
		// this.paintingTiming = true;
	}

	setLevelNum(n) {
		this.levelNum = n;

		$('#level-num').textContent = `${(n + 1)} / ${Squarescape.LEVELS.length}`;
		$('#prev').disabled = n <= 0;
		$('#next').disabled = n >= Squarescape.LEVELS.length-1;
	}

	restart() {
		return this.loadLevel(this.levelNum);
	}

	loadLevel(n) {
		if (isNaN(n = parseInt(n)) || !Squarescape.LEVELS[n]) return;

		this.reset();
		this.setLevelNum(n);

		const level = Squarescape.LEVELS[n];
console.log(n, level);

		this.width = Math.max(...level.map.map(row => row.length));
		this.height = level.map.length;

		this.parseMap(level.map);
		this.player = Coords2D.fromArray(level.start);
		this.playerDirection = this.getInitialPlayerDirection();

		this.canvas.width = Squarescape.OFFSET + this.width * (Squarescape.SQUARE + Squarescape.MARGIN) - Squarescape.MARGIN + Squarescape.OFFSET;
		this.canvas.height = Squarescape.OFFSET + this.height * (Squarescape.SQUARE + Squarescape.MARGIN) - Squarescape.MARGIN + Squarescape.OFFSET;

		this.changed = true;
	}

	scale( source ) {
		if ( source instanceof Coords2D ) {
			return new Coords2D(this.scale(source.x), this.scale(source.y));
		}

		return Squarescape.OFFSET + source * (Squarescape.SQUARE + Squarescape.MARGIN);
	}

	unscale( source ) {
		if ( source instanceof Coords2D ) {
			source = source.multiply(this.canvas.width / this.canvas.offsetWidth);
			const C = new Coords2D(this.unscale(source.x), this.unscale(source.y));
			return C;
		}

		return Math.round((source - Squarescape.OFFSET - Squarescape.SQUARE/2) / (Squarescape.MARGIN + Squarescape.SQUARE));
	}

	drawStructure() {
		this.drawMap();
	}

	drawContent() {
		this.drawCollectibles();
		this.drawPlayerTriangle();
	}

	drawMap() {
		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				const t = this.map[y][x];
				if (t !== 'x') {
					const C = new Coords2D(x, y);
					const from = this.scale(C);
					const to = from.add(new Coords2D(Squarescape.SQUARE, Squarescape.SQUARE));
					const color = this.end.equal(C) ? 'lime' : (t === 'd' ? 'red' : (t === 'p' ? 'yellow' : 'white'));
					this.drawRectangle(from, to, {color, fill: true});
				}
			}
		}
	}

	drawCollectibles() {
		this.collectibles.forEach(C => {
			const center = this.scale(C).add(new Coords2D(Squarescape.SQUARE / 2, Squarescape.SQUARE / 2));
			this.drawDot(center, {radius: Squarescape.SQUARE / 3, color: 'gold'});
		});
	}

	drawPlayerDot() {
		const C = this.scale(this.player).add(new Coords2D(Squarescape.SQUARE / 2, Squarescape.SQUARE / 2));
		this.drawDot(C, {radius: Squarescape.SQUARE / 3});
	}

	drawPlayerTriangle() {
		const C = this.scale(this.player);
		const size = 6;
		const triangles = [
			[new Coords2D(0.5, 1/size), new Coords2D(1 - 1/size, 1 - 1/size), new Coords2D(1/size, 1 - 1/size)],
			[new Coords2D(1 - 1/size, 0.5), new Coords2D(1/size, 1 - 1/size), new Coords2D(1/size, 1/size)],
			[new Coords2D(0.5, 1 - 1/size), new Coords2D(1 - 1/size, 1/size), new Coords2D(1/size, 1/size)],
			[new Coords2D(1/size, 0.5), new Coords2D(1 - 1/size, 1 - 1/size), new Coords2D(1 - 1/size, 1/size)],
		];
		const triangle = triangles[this.playerDirection].map(P => C.add(P.multiply(Squarescape.SQUARE)));
		this.drawPolygon(triangle);
	}

	drawPolygon(points) {
		this.ctx.fillStyle = 'black';

		this.ctx.beginPath();
		this.ctx.moveTo(points[0].x, points[0].y);
		for (const point of points) {
			this.ctx.lineTo(point.x, point.y);
		}
		this.ctx.closePath();
		this.ctx.fill();
	}

	isNavigable(t) {
		return t != null && t !== 'x';
	}

	cellIsNavigable(C) {
		const t = this.map[C.y] && this.map[C.y][C.x];
		return this.isNavigable(t);
	}

	getInitialPlayerDirection() {
		return Coords2D.dir4Coords.findIndex(D => {
			return this.cellIsNavigable(this.player.add(D));
		});
	}

	parseMap(map) {
		this.map = map.slice(0);

		for ( let y = 0; y < this.height; y++ ) {
			for ( let x = 0; x < this.width; x++ ) {
				const t = this.map[y][x];
				if (t === 'o') {
					this.collectibles.push(new Coords2D(x, y));
				}
				else if (t === 't') {
					this.end = new Coords2D(x, y);
				}
			}
			this.map[y] = this.map[y].replace(/[ot]/g, ' ');
		}
	}

	haveWon() {
		return this.collectibles.length == 0 && this.end.equal(this.player);
	}

	haveLost() {
		const t = this.map[this.player.y][this.player.x];
		return t === 'd' || (this.end.equal(this.player) && this.collectibles.length > 0);
	}

	move( D ) {
		this.startTime();

		const path = this.findPath(this.player, D);
		if ( path.length > 0 ) {
			this.playerDirection = Coords2D.dir4Coords.findIndex(C => C.equal(D));
			this.drawPath(path);
			this.setMoves(this.m_iMoves + 1);
		}
	}

	findPath( from, D ) {
		const path = [];
		var current = from;
		while ( current = current.add(D) ) {
			const t = this.map[current.y] && this.map[current.y][current.x];
			if ( t === 'p' || t === 'd' ) {
				path.push(current);
				return path;
			}
			else if ( !this.isNavigable(t) ) {
				return path;
			}
			else {
				path.push(current);
			}
		}

		throw new Error('eh?');
	}

	drawPath( path ) {
		this.drawingPath = true;
		var drawStep = () => {
			const step = path.shift();
			this.movePlayer(step);
			if (path.length > 0) {
				setTimeout(drawStep, 50);
			}
			else {
				this.drawingPath = false;
				this.startWinCheck(100);
			}
		};
		drawStep();
	}

	movePlayer( to ) {
		this.player = to;

		this.collectibles = this.collectibles.filter(C => !C.equal(to));

		this.changed = true;
	}

	handleGlobalDirection(dir) {
		if (this.drawingPath) return;
		if (this.m_bGameOver) return this.restart();

		this.move(Coords2D.dir4Coords[Coords2D.dir4Names.indexOf(dir[0])]);
	}

	listenControls() {
		this.listenGlobalDirection();
		this.listenActions();

		$('#restart').on('click', e => this.restart());
	}

	listenActions() {
		$('#prev').on('click', (e) => {
			this.loadLevel(this.levelNum - 1);
		});
		$('#next').on('click', (e) => {
			this.loadLevel(this.levelNum + 1);
		});
	}

	createGame() {
	}

}

class SquarescapeEditor extends GridGameEditor {

	cellTypes() {
		return {
			available: 'Available',
			start: 'Start',
			end: 'End',
			pause: 'Pause',
			danger: 'Danger',
			collect: 'Collect',
		};
	}

	defaultCellType() {
		return 'available';
	}

	createdMapCell( cell ) {
		cell.addClass('available');
	}

	setType_available( cell ) {
		cell.toggleClass('available');
	}

	setType_start( cell ) {
		cell.addClass('available');
		if ( cell.hasClass('start') ) {
			cell.removeClass('start');
		}
		else {
			this.m_objGrid.getElements('.start').removeClass('start');
			cell.removeClass('end');
			cell.addClass('start');
		}
	}

	setType_end( cell ) {
		cell.addClass('available');
		if ( cell.hasClass('end') ) {
			cell.removeClass('end');
		}
		else {
			this.m_objGrid.getElements('.end').removeClass('end');
			cell.removeClass('start');
			cell.addClass('end');
		}
	}

	setType_pause( cell ) {
		cell.toggleClass('pause');
		cell.addClass('available');
		cell.removeClass('collect');
		cell.removeClass('danger');
	}

	setType_danger( cell ) {
		cell.toggleClass('danger');
		cell.addClass('available');
		cell.removeClass('collect');
		cell.removeClass('pause');
	}

	setType_collect( cell ) {
		cell.toggleClass('collect');
		cell.addClass('available');
		cell.removeClass('pause');
		cell.removeClass('danger');
	}

	exportLevel() {
		var map = [];
		var start = null;

		r.each(this.m_objGrid.rows, (tr, y) => {
			var row = '';
			r.each(tr.cells, (cell, y) => {
				if ( cell.hasClass('start') ) {
					row += ' ';
					start = this.getCoord(cell);
				}
				else if ( cell.hasClass('end') ) {
					row += 't';
				}
				else if ( cell.hasClass('pause') ) {
					row += 'p';
				}
				else if ( cell.hasClass('danger') ) {
					row += 'd';
				}
				else if ( cell.hasClass('collect') ) {
					row += 'o';
				}
				else {
					row += cell.hasClass('available') ? ' ' : 'x';
				}
			});
			map.push(row);
		});

		const level = {map, start};
		this.validateLevel(level);
		return level;
	}

	validateLevel( level ) {
		if ( !level.start ) {
			throw 'Need 1 start';
		}

		if ( this.countMapCells(level.map, 't') != 1 ) {
			throw 'Need 1 end';
		}

		if ( this.countMapCells(level.map, 'o') == 0 ) {
			throw 'Need collectibles';
		}
	}

	formatAsPHP( level ) {
		var code = [];
		code.push('\t[');
		code.push("\t\t'map' => [");
		r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
		code.push("\t\t],");
		code.push("\t\t'start' => [" + level.start.join() + "],");
		code.push('\t],');
		code.push('');
		code.push('');
		return code;
	}

}
