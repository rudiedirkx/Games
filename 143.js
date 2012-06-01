
function Coord(f_x, f_y) {
	this.x = f_x;
	this.y = f_y;
}
Coord.prototype = {
	add : function(c) {
		this.x += c.x;
		this.y += c.y;
		return this;
	},
	img : function() {
		return $('ball_' + this.x + '_' + this.y);
	}
}


function Abalone( f_szYouColor, f_szTurnColor ) {
	this.m_szPlayerColor	= f_szYouColor;
	this.m_szOpponentColor	= 'white' == this.m_szPlayerColor ? 'black' : 'white';
	this.m_szTurnColor		= f_szTurnColor;
	this.m_arrSelectedBalls	= [];
}

Abalone.prototype = {

	/**
	 * Clicked on an Abalone IMG (why and the consequences are yet unknown)
	 */
	clickedOn : function( ball ) {
		if ( this.m_szPlayerColor === this.m_szTurnColor ) {
			if ( ball.owner === this.m_szPlayerColor ) {
				return this.toggleSelectBall(ball);
			}

			if ( 0 < this.m_arrSelectedBalls.length ) {
				return this.moveSelectedTo(ball);
			}
		}

	}, // END clickedOn


	/**
	 * A player selects a ball with this function (his own color ofcourse), by clicking on the board
	 */
	fetchMap : function() {
		var self = this;
		new Ajax('?fetch_map=1', {
			method: 'get',
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
				// Clear map
				$('abalone_div').select('.ball').each(function(ball) {
					var x = ball.id.split('_');
					ball.coords = new Coord(~~x[1], ~~x[2]);
					ball.owner = null;
					ball.selected = false;
					ball.className = 'ball';
				});
				// Populate map
				$A(rv).each(function(details) {
					ball = $('ball_' + details[0] + '_' + details[1]);
					ball.owner = details[2];
					ball.className = 'ball ' + details[2];
				});
			}
		}).request();
		return false;

	}, // END fetchMap


	/**
	 * S e n d   o r d e r
	 */
	sendOrder : function( f_changes ) {
		var d = [];
		$A(f_changes).each(function(c) {
			d.push(c.join(','));
		});
		var data = 'changes[]=' + d.join('&changes[]=');
		var self = this;
		new Ajax('?', {
			data : data,
			onComplete : function(t) {
				return document.location.reload();
				self.fetchMap();
			}
		}).request();
		return false;

	}, // END sendOrder


	/**
	 * N e x t   f i e l d
	 */
	getNextField : function( f_coord, f_dir ) {
		return this.getCoordInfo(f_dir).add(f_coord).img();

	}, // END getNextField


	/**
	 * Move the selected balls to another position (clicked on a ball or an empty field)
	 */
	moveSelectedTo : function( f_objBall ) {
		// Min 1 ball selected and max 3
		if ( 0 == this.m_arrSelectedBalls.length || 3 < this.m_arrSelectedBalls.length ) {
			return false;
		}
		// Check if all selected balls + target are in one line
		var c = [];
		$A(this.m_arrSelectedBalls).each(function(b) {
			c.push(b.coords);
		});
		c.push(f_objBall.coords);
		var caa = this.coordsAreAligned(c)
		if ( !caa ) {
this.debug('Selected balls & target ball are not in one line!');
			return false;
		}

		// Player wants to push the opponent away
		var toPosition = f_objBall;
		if ( this.m_szOpponentColor === toPosition.owner ) {
this.debug('Wanna push opponent...');
			if ( 1 == this.m_arrSelectedBalls.length ) {
				return false;
			}
			var to2Position = this.getNextField(toPosition.coords, caa);
this.debug(to2Position);
			if ( 2 <= this.m_arrSelectedBalls.length && ( !to2Position || !to2Position.owner ) ) {
				var c = [];
				// Pushing 1 opponent ball
				if ( to2Position ) {
					// Opponent one forward
					to2Position.owner = this.m_szOpponentColor;
					to2Position.src = 'images/143_' + this.m_szOpponentColor + '.gif';
					c.push([ to2Position.coords.x, to2Position.coords.y, this.m_szOpponentColor ]);
				}
				// Player one forward
				toPosition.owner = this.m_szPlayerColor;
				toPosition.src = 'images/143_' + this.m_szPlayerColor + '.gif';
					c.push([ toPosition.coords.x, toPosition.coords.y, this.m_szPlayerColor ]);
				// 'Remove' last position
				var firstBall = this.m_arrSelectedBalls[0];
				this.unselectAllBalls();
				firstBall.owner = null;
				firstBall.src = 'images/143_empty.gif';
				c.push([ firstBall.coords.x, firstBall.coords.y, '' ]);
				this.sendOrder(c);
				return false;
			}
			var to3Position = this.getNextField(to2Position.coords, caa);
this.debug(to3Position);
			if ( 3 <= this.m_arrSelectedBalls.length && to2Position.owner == this.m_szOpponentColor && ( !to3Position || !to3Position.owner ) ) {
				var c = [];
				// Pushing 2 opponent balls
				if ( to3Position ) {
					// Opponent one forward
					to3Position.owner = this.m_szOpponentColor;
					to3Position.src = 'images/143_' + this.m_szOpponentColor + '.gif';
					c.push([ to3Position.coords.x, to3Position.coords.y, this.m_szOpponentColor ]);
				}
				// Player one forward
				toPosition.owner = this.m_szPlayerColor;
				toPosition.src = 'images/143_' + this.m_szPlayerColor + '.gif';
				c.push([ toPosition.coords.x, toPosition.coords.y, this.m_szPlayerColor ]);
				// 'Remove' last position
				var firstBall = this.m_arrSelectedBalls[0];
				this.unselectAllBalls();
				firstBall.owner = null;
				firstBall.src = 'images/143_empty.gif';
				c.push([ firstBall.coords.x, firstBall.coords.y, '' ]);
				this.sendOrder(c);
				return false;
			}
		}
		// Player pushes toward empty
		else {
			var c = [];
			// Move first ball to the empty position
			var firstBall = this.m_arrSelectedBalls[0];
			this.unselectAllBalls();
			firstBall.owner = null;
			firstBall.src = 'images/143_empty.gif';
			c.push([ firstBall.coords.x, firstBall.coords.y, '' ]);
			toPosition.owner = this.m_szPlayerColor;
			toPosition.src = 'images/143_' + this.m_szPlayerColor + '.gif';
			c.push([ toPosition.coords.x, toPosition.coords.y, this.m_szPlayerColor ]);
			this.sendOrder(c);
		}
		return false;

	}, // END moveSelectedTo


	/**
	 * Check if coords are aligned
	 */
	coordsAreAligned : function( balls ) {
console.log(balls);
		if ( 1 >= balls.length ) {
			return true;
		}

		var _x = balls[0].x, _y = balls[0].y, _d = false;
		for ( var i=1; i<balls.length; i++ ) {
			var c = balls[i], x = c.x, y = c.y;
			var _1, _2, _3, _4, _5, _6;
			_1 = 1*( (x+1 == _x) && (y+1 == _y) );	// bottom left
			_2 = 1*( (x-1 == _x) && (y-1 == _y) );	// top right
			_3 = 1*( (x == _x) && (y+1 == _y) );	// bottom right
			_4 = 1*( (x == _x) && (y-1 == _y) );	// top left
			_5 = 1*( (x+1 == _x) && (y == _y) );	// left
			_6 = 1*( (x-1 == _x) && (y == _y) );	// right
//this.debug([_1,_2,_3,_4,_5,_6]);
			if ( 0 < _d ) {
				var _t = false;
				switch ( _d ) {
					case 1: _t = _1; break;
					case 2: _t = _2; break;
					case 3: _t = _3; break;
					case 4: _t = _4; break;
					case 5: _t = _5; break;
					case 6: _t = _6; break;
				}
				if (0 >= _t) {
					return false;
				}
			}
			else {
				if ( 0>=_1 && 0>=_2 && 0>=_3 && 0>=_4 && 0>=_5 && 0>=_6 ) {
					return false;
				}
				if ( 0<_1 ) {
					_d = 1;
				}
				else if ( 0<_2 ) {
					_d = 2;
				}
				else if ( 0<_3 ) {
					_d = 3;
				}
				else if ( 0<_4 ) {
					_d = 4;
				}
				else if ( 0<_5 ) {
					_d = 5;
				}
				else if ( 0<_6 ) {
					_d = 6;
				}
			}
			_x = x;
			_y = y;
		}
		return _d;

	}, // END coordsAreAligned


	/**
	 * A player selects a ball with this function (his own color ofcourse), by clicking on the board
	 */
	toggleSelectBall : function( ball ) {
		var s = this.search(ball, this.m_arrSelectedBalls);
console.log(s);
		if ( false === s ) {
			return this.selectBall(ball);
		}

		return this.unselectBall(s);

	}, // END toggleSelectBall


	/**
	 * Add coords to list and color ball on board to hilite
	 */
	selectBall : function( ball ) {
		if ( 3 <= this.m_arrSelectedBalls.length ) {
			return false;
		}

		this.m_arrSelectedBalls.push(ball);

		var caa = this.coordsAreAligned(this.m_arrSelectedBalls);
		if ( !caa ) {
			this.m_arrSelectedBalls.pop();
			this.debug('Won\'t select because already out of line!');
			return false;
		}

		ball.addClass('selected');

		return false;

	}, // END selectBall


	/**
	 * Remove coords from list and color ball on board to normal
	 */
	unselectBall : function( f_index ) {
		var ball = this.m_arrSelectedBalls[f_index];
		ball.src = '/images/143_' + this.m_szPlayerColor + '.gif';
		this.m_arrSelectedBalls.splice(f_index, 1);
		return false;

	}, // END unselectBall


	/**
	 * Unselects all balls, maybe not a necessary function
	 */
	unselectAllBalls : function() {
		while ( 0 < this.m_arrSelectedBalls.length ) {
			this.unselectBall(0);
		}
		return false;

	}, // END unselectAllBalls


	/**
	 * Get coord delta by direction name
	 */
	getCoordInfo : function( f_dir ) {
		var d = [0, 0];
		switch ( f_dir ) {
			case 'topright':
			case 2:
				d = [1,1];
			break;
			case 'bottomleft':
			case 1:
				d = [-1,-1];
			break;
			case 'topleft':
			case 4:
				d = [0,1];
			break;
			case 'bottomright':
			case 3:
				d = [0,-1];
			break;
			case 'right':
			case 6:
				d = [1,0];
			break;
			case 'left':
			case 5:
				d = [-1,0];
			break;
		}
		return new Coord(d[0], d[1]);

	}, // END getCoordInfo


	/**
	 * The opposite direction of the argument
	 */
	getOppositeDirection : function( f_dir ) {
		switch ( f_dir ) {
			case 2:
				return 1;
			case 1:
				return 2;
			case 4:
				return 3;
			case 3:
				return 4;
			case 6:
				return 5;
			case 5:
				return 6;
		}
		return 0;

	}, // END getOppositeDirection


	/**
	 * Search in a list of coords, for one coord. Returns the index if found, or FALSE if not found
	 */
	search : function( f_needle, f_haystack ) {
		for ( var i=0; i<f_haystack.length; i++ ) {
			if ( f_haystack[i] === f_needle ) {
				return i;
			}
		}
		return false;
/*		if ( "object" != typeof f_haystack || !f_haystack.length ) return false;
		needle = f_needle.join('|');
		for ( i=0; i<f_haystack.length; i++ ) {
			if ( f_haystack[i].join('|') == needle ) return i;
		}
		return false;*/

	}, // END search


	debug : function( f_msg ) {
		if ( window.console && window.console.debug ) {
			window.console.debug(f_msg);
		}

	}, // END debug


}; // var Abalone
