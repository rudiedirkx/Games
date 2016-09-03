
// CONSTRUCTOR //
function TheBox( f_iLevel ) {
	if ( f_iLevel ) this.LoadAndPrintMap(f_iLevel);

} // END TheBox()

// METHODS //
TheBox.prototype = {

	/**
	 * M o v e
	 */
	Move : function( f_dir ) {
		if ( this.m_bGameOver ) { return; }

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
//			alert("ERR(clicked on " + f_coords.join(":") + " while pusher is" + this._pusher.join(":") + ")");
			return;
		}
		var nowFieldC = [this._pusher[0], this._pusher[1]];
		var toFieldC = [this._pusher[0]+dx1, this._pusher[1]+dy1];
		var nextFieldC = [this._pusher[0]+dx2, this._pusher[1]+dy2];

		// TO-FIELD cannot be wall
		var toField = $('thebox_tbody').rows[toFieldC[1]].cells[toFieldC[0]];
		if ( toField.wall ) {
//			alert("TO-FIELD cannot be wall");
			return;
		}
		var nextField = $('thebox_tbody').rows[nextFieldC[1]].cells[nextFieldC[0]];

		// NEXT-FIELD must be empty
		if ( toField.box && ( nextField.box || nextField.wall ) ) {
//			alert("Can't push box with box or wall behind it");
			return;
		}
		var nowField = $('thebox_tbody').rows[nowFieldC[1]].cells[nowFieldC[0]];

		this.m_arrLastMove = [];
		this.m_arrLastMove.push(nowFieldC.join('_'));
		this.m_arrLastMove.push('p');
		this.m_arrLastMove.push(toFieldC.join('_'));
		this.m_arrLastMove.push( toField.box ? 'b' : false );
		this.m_arrLastMove.push(nextFieldC.join('_'));
		this.m_arrLastMove.push(false);

		if ( toField.box ) {
			toField.box = false;
			nextField.box = true;
			nextField.className = 'box';
		}

		this._pusher = toFieldC;
		nowField.className = '';
		toField.className = 'pusher';

		this.m_iMoves++;
		this.AddToStack(f_dir);
		$('stats_moves').innerHTML = this.m_iMoves;

		if ( 0 == this.CountBadBoxes() ) {
			this.m_bGameOver = true;
			var self = this;
			new Ajax('?', {
				data : 'action=move&level=' + this.m_iLevel + '&dir=' + this.m_arrStack.join(''),
				onComplete : function(t) {
					self.SaveMessage(t);
				}
			}).request();
		}

	}, // END Move()


	/**
	 * U n d o   l a s t   m o v e
	 */
	UndoLastMove : function() {
		if ( 6 != this.m_arrLastMove.length || 0 == this.CountBadBoxes() ) {
			return false;
		}
		for ( var i=0; i<6; i+=2 ) {
			var x = this.m_arrLastMove[i].split('_');
			var objField = $('thebox_tbody').rows[x[1].toInt()].cells[x[0].toInt()];
			switch ( this.m_arrLastMove[i+1] ) {
				case 'p':
					objField.box = false;
					this._pusher = [x[0].toInt(), x[1].toInt()];
					objField.className = 'pusher';
				break;
				case 'b':
					objField.box = true;
					objField.className = 'box';
				break;
				default:
					objField.box = false;
					objField.className = '';
				break;
			}
		}
		this.m_arrStack.pop();
		this.m_arrLastMove = [];
		this.m_iMoves--;
		$('stats_moves').innerHTML = this.m_iMoves;
		return false;

	}, // END UndoLastMove()


	/**
	 * A d d   t o   s t a c k
	 */
	AddToStack : function( f_dir ) {
		this.m_arrStack.push(f_dir.substr(0, 1));

	}, // END AddToStack()


	/**
	 * S a v e   m e s s a g e
	 */
	SaveMessage : function( f_msg ) {
		$('stack_message').innerHTML = f_msg;
		$('stack_message').style.backgroundColor = 'red';
		setTimeout("$('stack_message').style.backgroundColor = '';", 500);

	}, // END SaveMessage()


	/**
	 * L o a d   a n d   p r i n t   m a p
	 */
	LoadAndPrintMap : function( f_level ) {
		if ( 'undefined' == typeof f_level ) {
			return;
		}
		document.location = '#'+f_level;

		var self = this;
		new Ajax('?', {
			data : 'action=get_maps&level=' + f_level,
			onComplete : function(t) {
				try {
					var rv = eval( "(" + t + ")" );
				} catch (e) {
					alert('Response error: '+t);
					return;
				}

				if ( rv.error ) {
					alert(rv.error);
					return;
				}

				// save pusher
				self._pusher = rv['pusher'];

				// save level #
				$('stats_level').innerHTML	= rv['level'];
				$('stats_moves').innerHTML	= '0';
				$('stack_message').innerHTML= '-';
				self.m_iLevel = rv['level'];
				self.m_bGameOver = false;

				self.m_iMoves		= 0;
				self.m_arrLastMove	= [];

				self.m_arrStack		= [];

				// empty current map
				while ( 0 < $('thebox_tbody').childNodes.length ) {
					$('thebox_tbody').removeChild($('thebox_tbody').firstChild);
				}

				// save map
				$A(rv.map).each(function(row, y) {
					var nr = $('thebox_tbody').insertRow($('thebox_tbody').rows.length);
					for ( var x=0; x<row.length; x++ ) {
						var nc = nr.insertCell(nr.cells.length);
						nc.innerHTML = '';
						if ( 'x' == row.substr(x, 1) ) {
							nc.className = 'wall' + Math.ceil(2*Math.random());
							nc.wall = true;
						}
						else if ( 't' == row.substr(x, 1) ) {
							nc.className = 'target';
							nc.innerHTML = 'T';
							nc.target = true;
						}
						else {
							nc.className = '';
						}
					}
				});

				// show pusher
				$('thebox_tbody').rows[rv.pusher[1]].cells[rv.pusher[0]].className = 'pusher';

				// show boxes
				$A(rv.boxes).each(function(box) {
					$('thebox_tbody').rows[box[1]].cells[box[0]].box = true;
					$('thebox_tbody').rows[box[1]].cells[box[0]].className = 'box';
				});
			}
		}).request();

	}, // End LoadAndPrintMap()


	/**
	 * C o u n t   b a d   b o x e s
	 */
	CountBadBoxes : function() {
		var iBoxes = 0;
		$A($('thebox_tbody').rows).each(function(row) {
			$A(row.cells).each(function(cell) {
				if ( cell.box && !cell.target ) {
					iBoxes++;
				}
			})
		});
		return iBoxes;

	}, // END CountBadBoxes()

} // END Class TheBox
