class F1Racer extends LeveledGridGame {

	reset() {
		super.reset();

		this.m_arrTrack = [];
		this.m_iPosition = 0;
	}

	setMoves() {
	}

	loadMap( rv ) {
		this.reset();
		this.setLevel(rv.level || '?');

		this.m_objGrid.empty();

		var width = 1 + Math.max.apply(null, rv.map.map(C => C[0]));
		var height = 1 + Math.max.apply(null, rv.map.map(C => C[1]));

		for (var y = 0; y < height; y++) {
			var tr = this.m_objGrid.insertRow();
			for (var x = 0; x < width; x++) {
				var cell = tr.insertCell();
				this.makeWall(cell);
			}
		}

		this.createdMap(rv);
	}

	createdMap( rv ) {
		$('#level-header').empty()
			.append(document.el('p').setText(rv.track));

		this.m_arrTrack = rv.map.map(Coords2D.fromArray);
		r.each(this.m_arrTrack, (C, i) => this.unsetWall(this.getCell(C)).addClass('asphalt'));
		this.m_arrTrack.push(this.m_arrTrack[0]);

		this.hilitePosition();
	}

	hilitePosition() {
		this.m_objGrid.getElements('.current').removeClass('current');

		if ( this.m_arrTrack[this.m_iPosition] ) {
			this.getCell(this.m_arrTrack[this.m_iPosition]).addClass('current');
		}
	}

	listenControls() {
		this.listenCellClick();
	}

	handleCellClick( cell ) {
		if ( cell.hasClass('current') ) {
			cell.addClass('done');

			this.m_iPosition++;
			this.hilitePosition();

			this.winOrLose();
		}
	}

	haveWon() {
		return !this.m_objGrid.getElement('.current');
	}

}

class F1RacerEditor extends GridGameEditor {

	constructor( gridElement ) {
		super(gridElement);

		this.dir8Coords = [
			this.dir4Coords[0],
			new Coords2D(1, -1),
			this.dir4Coords[1],
			new Coords2D(1, 1),
			this.dir4Coords[2],
			new Coords2D(-1, 1),
			this.dir4Coords[3],
			new Coords2D(-1, -1),
		];

		this.dir8Names = [
			'u',
			'ur',
			'r',
			'dr',
			'd',
			'dl',
			'l',
			'ul',
		];
	}

	reset() {
		super.reset();

		this.m_fnPathingResolver = null;
	}

	createEditor() {
		this.createMap(20, 10);
		this.createCellTypes();
	}

	cellTypes() {
		return {
			asphalt: 'Track',
			start: 'Start',
		};
	}

	defaultCellType() {
		return 'asphalt';
	}

	createCellTypeCell( type ) {
		if ( type == 'start' ) {
			return '<td class="asphalt current"></td>';
		}

		return super.createCellTypeCell(type);
	}

	handleCellClick( cell ) {
		var choosing = this.m_objGrid.getElements('.choose-path');
		if ( choosing.length == 0 ) {
			return super.handleCellClick(cell);
		}

		if ( choosing.includes(cell) ) {
console.log('Got manual resolution!', cell, this.m_fnPathingResolver);
			this.m_fnPathingResolver.call(null, cell);

			this.m_fnPathingResolver = null;
			this.m_objGrid.getElements('.choose-path').removeClass('choose-path');
		}
	}

	setType_asphalt( cell ) {
		cell.toggleClass('asphalt');
	}

	setType_start( cell ) {
		if ( cell.hasClass('current') ) {
			cell.removeClass('current');
		}
		else {
			this.m_objGrid.getElements('.current').removeClass('current');
			cell.addClass('asphalt').addClass('current');
		}
	}

	getStart() {
		return this.m_objGrid.getElement('.current');
	}

	getTrackNeighbors( cell, except ) {
		var cellC = this.getCoord(cell);
		var neighbors = this.dir8Coords.map((offset) => this.getCell(cellC.add(offset)));
		var tracks = neighbors.filter((cell) => cell && cell.hasClass('asphalt') && cell != except);
		return new Elements(tracks);
	}

	getNextTrackCell( track ) {
		var DELAY = 100;

		return new Promise((resolve) => {
			var current = track[track.length - 1];
			track.removeClass('choosing-path');
			current.addClass('choosing-path');
			var neighbors = this.getTrackNeighbors(current, track[track.length - 2]);
			if ( neighbors.length == 1 ) {
				current.addClass('done');
				return setTimeout(() => resolve(neighbors[0]), DELAY);
			}
			else if ( neighbors.length == 2 && current != this.getStart() ) {
				var a = this.getTrackNeighbors(neighbors[0], track[track.length-1]);
				var b = this.getTrackNeighbors(neighbors[1], track[track.length-1]);
				var a1 = a.length == 1;
				var b1 = b.length == 1;
				if ( a1 && !b1 ) {
					current.addClass('done');
					return setTimeout(() => resolve(neighbors[0]), DELAY);
				}
				else if ( b1 && !a1 ) {
					current.addClass('done');
					return setTimeout(() => resolve(neighbors[1]), DELAY);
				}
			}

			if ( neighbors.length > 0 ) {
				// Wait for manual resolution...
				current.addClass('done');
				neighbors.addClass('choose-path');
				this.m_fnPathingResolver = resolve;
				return console.log('Need manual resolution');
			}

			setTimeout(function() {
				alert("Track stopped in the mdidle of nowhere?");
			}, this.ALERT_DELAY);

			resolve(null);
		});
	}

	resolveTrack( track ) {
		var iter = (cell) => {
			if ( cell ) {
				if ( cell == this.getStart() ) {
					return track;
				}
				track.push(cell);
			}
			else if ( cell !== undefined ) {
				return track;
			}
			return this.getNextTrackCell(track).then(iter);
		};
		return iter();
	}

	exportLevel() {
		var start = this.getStart();
		return this.resolveTrack(new Elements([start])).then((track) => {
			this.m_objGrid
				.getElements('.done, .choose-path, .choosing-path')
				.removeClass('done').removeClass('choose-path').removeClass('choosing-path');
			return {
				track: 'Track name',
				map: track.map((cell) => this.getCoord(cell)),
			};
		});
	}

	validateLevel( level ) {
	}

	formatAsPHP( level ) {
		return [
			'\t[',
			"\t\t'track' => " + JSON.stringify(level.track) + ",",
			"\t\t'map' => " + JSON.stringify(level.map, Coords2D.jsonReplacer()) + ",",
			'\t],',
		];
	}

}
