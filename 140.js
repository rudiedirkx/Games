r.extend(Coords2D, {
	direction: function() {
		if ( Math.abs(this.y) > Math.abs(this.x) ) {
			return this.y > 0 ? 'down' : 'up';
		}
		return this.x > 0 ? 'right' : 'left';
	},
	distance: function(target) {
		return Math.sqrt(Math.pow(Math.abs(this.x - target.x), 2) + Math.pow(Math.abs(this.y - target.y), 2));
	},
});

class TheBoxMultiple {
	constructor( f_iLevel ) {
		this.m_objGrid = $('#thebox_tbody');

		this.reset();

		if ( f_iLevel ) {
			this.loadAndPrintMap(f_iLevel);
		}
	}


	reset() {
		this.m_bGameOver	= false;
		this.m_arrLastMove	= [];
		this.m_arrStack		= [];

		this.setLevel(0);
		this.setMoves(0);

		$('#stack_message').setHTML('Push the Boxes to the Targets.');
	}


	setLevel( f_iLevel ) {
		this.m_iLevel = parseInt(f_iLevel) || 0;
		$('#stats_level').setText(this.m_iLevel);
	}


	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		$('#stats_moves').setText(this.m_iMoves);
	}


	getPusher() {
		var pusher = this.m_objGrid.getElement('.pusher');
		return new Coords2D(pusher.cellIndex, pusher.parentNode.sectionRowIndex);
	}


	move( f_dir ) {
		if ( this.m_bGameOver ) return;

		var dx = 0, dy = 0;
		if ( 'left' == f_dir ) {
			dx = -1;
		}
		else if ( 'right' == f_dir ) {
			dx = 1;
		}
		else if ( 'up' == f_dir ) {
			dy = -1;
		}
		else if ( 'down' == f_dir ) {
			dy = 1;
		}
		else {
			return;
		}

		var pusher = this.getPusher();

		var nowFieldC = pusher;
		var toFieldC = new Coords2D(pusher.x + dx, pusher.y + dy);
		var nextFieldC = new Coords2D(pusher.x + dx*2, pusher.y + dy*2);

		// TO-FIELD cannot be wall
		var toField = this.m_objGrid.rows[toFieldC.y].cells[toFieldC.x];
		if ( toField.hasClass('wall') ) {
			return;
		}
		var nextField = this.m_objGrid.rows[nextFieldC.y].cells[nextFieldC.x];

		// NEXT-FIELD must be empty
		if ( toField.hasClass('box') && ( nextField.hasClass('box') || nextField.hasClass('wall') ) ) {
			return;
		}
		var nowField = this.m_objGrid.rows[nowFieldC.y].cells[nowFieldC.x];

		this.m_arrLastMove = [this.m_iMoves, this.m_objGrid.innerHTML];

		if ( toField.hasClass('box') ) {
			toField.removeClass('box');
			nextField.addClass('box');
			this.m_iMoves++;
		}

		nowField.removeClass('pusher');
		toField.addClass('pusher');

		this.m_iMoves++;
		this.addToStack(f_dir);
		this.setMoves();

		if ( 0 == this.countBadBoxes() ) {
			this.m_bGameOver = true;
			var self = this;
			var data = 'action=move&level=' + this.m_iLevel + '&dir=' + this.m_arrStack.join('');
			r.post('?', data).on('done', function(e, t) {
				self.saveMessage(t);
			});
		}
	}


	undoLastMove() {
		if ( this.m_arrLastMove ) {
			this.m_objGrid.innerHTML = this.m_arrLastMove[1];
			this.m_arrStack.pop();
			this.setMoves(this.m_arrLastMove[0]);
			this.m_arrLastMove = null;
		}
	}


	addToStack( f_dir ) {
		this.m_arrStack.push(f_dir.substr(0, 1));
	}


	saveMessage( f_msg ) {
		$('#stack_message').innerHTML = f_msg;
		$('#stack_message').addClass('hilite');
		setTimeout("$('#stack_message').removeClass('hilite');", 500);
	}


	loadAndPrintMap( f_level ) {
		if ( f_level == null ) return;

		var self = this;
		r.get('?action=get_maps&level=' + f_level).on('done', function(e, rv) {
			if ( rv.error ) return;

			document.location = '#' + f_level;

			self.reset();
			self.setLevel(rv['level']);

			self.m_objGrid.empty();
			r.each(rv.map, function(row, y) {
				var nr = self.m_objGrid.insertRow(self.m_objGrid.rows.length);
				for ( var x=0; x<row.length; x++ ) {
					var nc = nr.insertCell(nr.cells.length);
					nc.innerHTML = '';
					if ( 'x' == row.substr(x, 1) ) {
						nc.addClass('wall');
						nc.addClass('wall' + Math.ceil(2*Math.random()));
					}
					else if ( 't' == row.substr(x, 1) ) {
						nc.addClass('target');
					}
					else {
						nc.className = '';
					}
				}
			});

			var pusher = Coords2D.fromArray(rv.pusher);

			// show pusher
			self.m_objGrid.rows[pusher.y].cells[pusher.x].addClass('pusher');

			// show boxes
			r.each(rv.boxes, function([x, y]) {
				self.m_objGrid.rows[y].cells[x].addClass('box');
			});
		});
	}


	countBadBoxes() {
		return $$('.box:not(.target)').length;
	}


	log( msg ) {

	}


	listenToAjax() {
		window.on('xhrStart', function(e) {
			$('#loading').css('visibility', 'visible');
		});
		window.on('xhrDone', function(e) {
			if ( r.xhr.busy == 0 ) {
				$('#loading').css('visibility', 'hidden');
			}
		});
	}


	listenForMovement() {
		document.on('keydown', (e) => {
			if ( e.code.match(/^Arrow/) ) {
				e.preventDefault();
				var dir = e.code.substr(5).toLowerCase();
				this.move(dir);
			}
		});

		var movingStart, movingEnd;
		document.on(['mousedown', 'touchstart'], 'table', (e) => {
			e.preventDefault();
			movingStart = e.pageXY;
		});
		document.on(['mousemove', 'touchmove'], (e) => {
			e.preventDefault();
			if ( movingStart ) {
				movingEnd = e.pageXY;
			}
		});
		document.on(['mouseup', 'touchend'], (e) => {
			if ( movingStart && movingEnd ) {
				var distance = movingStart.distance(movingEnd);
				if ( distance > 10 ) {
					var moved = movingEnd.subtract(movingStart);
					var dir = moved.direction();
					this.move(dir);
				}
			}
			movingStart = movingEnd = null;
		});
	}

}
