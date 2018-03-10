
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

		$('#stack_message').setHTML('&nbsp;');
	}


	setLevel( f_iLevel ) {
		this.m_iLevel = f_iLevel;
		$('#stats_level').setText(f_iLevel);
	}


	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		$('#stats_moves').setText(this.m_iMoves);
	}


	getPusher() {
		var pusher = this.m_objGrid.getElement('.pusher');
		return [pusher.cellIndex, pusher.parentNode.sectionRowIndex];
	}


	move( f_dir ) {
		if ( this.m_bGameOver ) return;

		var dx1 = 0, dx2 = 0, dy1 = 0, dy2 = 0;
		if ( 'left' == f_dir ) {
			dx1 = -1;
			dx2 = -2;
		}
		else if ( 'right' == f_dir ) {
			dx1 = 1;
			dx2 = 2;
		}
		else if ( 'up' == f_dir ) {
			dy1 = -1;
			dy2 = -2;
		}
		else if ( 'down' == f_dir ) {
			dy1 = 1;
			dy2 = 2;
		}
		else {
			return;
		}

		var pusher = this.getPusher();

		var nowFieldC = [pusher[0], pusher[1]];
		var toFieldC = [pusher[0]+dx1, pusher[1]+dy1];
		var nextFieldC = [pusher[0]+dx2, pusher[1]+dy2];

		// TO-FIELD cannot be wall
		var toField = this.m_objGrid.rows[toFieldC[1]].cells[toFieldC[0]];
		if ( toField.hasClass('wall') ) {
			return;
		}
		var nextField = this.m_objGrid.rows[nextFieldC[1]].cells[nextFieldC[0]];

		// NEXT-FIELD must be empty
		if ( toField.hasClass('box') && ( nextField.hasClass('box') || nextField.hasClass('wall') ) ) {
			return;
		}
		var nowField = this.m_objGrid.rows[nowFieldC[1]].cells[nowFieldC[0]];

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

		return false;
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
		if ( 'undefined' == typeof f_level ) {
			return;
		}
		document.location = '#'+f_level;

		var self = this;
		r.get('?action=get_maps&level=' + f_level).on('done', function(e, rv) {
			if ( rv.error ) return;

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
						nc.innerHTML = 'T';
					}
					else {
						nc.className = '';
					}
				}
			});

			// show pusher
			self.m_objGrid.rows[rv.pusher[1]].cells[rv.pusher[0]].addClass('pusher');

			// show boxes
			r.each(rv.boxes, function(box) {
				self.m_objGrid.rows[box[1]].cells[box[0]].addClass('box');
			});
		});
	}


	countBadBoxes() {
		return $$('.box:not(.target)').length;
	}

}
