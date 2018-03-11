r.extend(Coords2D, {
	direction: function() {
		if ( Math.abs(this.y) > Math.abs(this.x) ) {
			return this.y > 0 ? 'down' : 'up';
		}
		return this.x > 0 ? 'right' : 'left';
	},
	distance: function( target ) {
		return Math.sqrt(Math.pow(Math.abs(this.x - target.x), 2) + Math.pow(Math.abs(this.y - target.y), 2));
	},
});

class GridGame {

	constructor( f_iLevel ) {
		this.m_objGrid = $('#grid');

		this.reset();

		if ( f_iLevel ) {
			this.loadLevel(f_iLevel);
		}
	}

	reset() {
		this.m_bGameOver = false;
		this.m_arrLastMove = null;

		this.setLevel(0);
		this.setMoves(0);
	}

	setLevel( f_iLevel ) {
		this.m_iLevel = parseInt(f_iLevel) || 0;
		$('#stats-level').setText(this.m_iLevel);
	}

	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		$('#stats-moves').setText(this.m_iMoves);
	}

	haveWon() {
		return false;
	}

	win() {
		this.m_bGameOver = true;
		setTimeout(function() {
			alert('You win!');
		}, 100);
	}

	undoLastMove() {
	}

	loadLevel( f_level ) {
		r.get('?load_map=' + f_level).on('done', (e, rv) => {
			if ( rv.error || !rv.map ) {
				var error = rv.error ? rv.error : rv;
				alert('Map load error\n\n' + error);
				return;
			}

			document.location = '#' + f_level;

			this.reset();
			this.setLevel(rv['level']);

			this.m_objGrid.empty();

			r.each(rv.map, (row, y) => {
				var nr = this.m_objGrid.insertRow(this.m_objGrid.rows.length);
				r.each(row, (type, x) => {
					var cell = nr.insertCell(nr.cells.length);

					this.createField(cell, type, rv, x, y);
				});
			});

			this.createdMap(rv);
		});
	}

	createField( cell, type, rv, x, y ) {
		cell.setText('?');
	}

	createdMap(rv) {

	}

	listenAjax() {
		window.on('xhrStart', function(e) {
			$('#loading').css('visibility', 'visible');
		});
		window.on('xhrDone', function(e) {
			if ( r.xhr.busy == 0 ) {
				$('#loading').css('visibility', 'hidden');
			}
		});
	}

	listenControls() {
	}

	listenGlobalDirection() {
		document.on('keydown', (e) => {
			if ( e.code.match(/^Arrow/) && !e.alt && !e.ctrl ) {
				e.preventDefault();
				var dir = e.code.substr(5).toLowerCase();
				this.handleGlobalDirection(dir);
			}
		});

		var movingStart, movingEnd;
		document.on('touchstart', (e) => {
			e.preventDefault();
			movingStart = e.pageXY;
		});
		document.on('touchmove', (e) => {
			e.preventDefault();
			if ( movingStart ) {
				movingEnd = e.pageXY;
			}
		});
		document.on('touchend', (e) => {
			if ( movingStart && movingEnd ) {
				var distance = movingStart.distance(movingEnd);
				if ( distance > 10 ) {
					var moved = movingEnd.subtract(movingStart);
					var dir = moved.direction();
					this.handleGlobalDirection(dir);
				}
			}
			movingStart = movingEnd = null;
		});
	}

	listenCellClick() {
		this.m_objGrid.on('click', '#grid td', (e) => {
			this.handleCellClick(e.subject);
		});
	}

	handleGlobalDirection( direction ) {
	}

	handleCellClick( cell ) {
	}

}
