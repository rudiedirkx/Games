
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


function Abalone( container, f_szYouColor, f_szTurnColor, fetch ) {
	this.$container = $(container);
	this.m_szPlayerColor = f_szYouColor;
	this.m_szOpponentColor = Abalone.oppositeColor(this.m_szPlayerColor);
	this.m_szTurnColor = f_szTurnColor;
	this.m_arrSelectedBalls = [];

	this.index = {};
	this.createIndex();

	fetch && this.fetchMap();
}

Abalone.oppositeColor = function( color ) {
	return 'white' == color ? 'black' : 'white';
};

Abalone.prototype = {

	/**
	 * Clicked on an Abalone IMG (why and the consequences are yet unknown)
	 */
	createIndex : function() {
		var self = this;

		$balls = this.$container.find('.ball').each(function(i, el) {
			self.index[ el.id ] = el;
		});

		return this.index;

	}, // END createIndex


	/**
	 * Clicked on an Abalone IMG (why and the consequences are yet unknown)
	 */
	clickedOn : function( id ) {
		var ball = this.index[id],
			$ball = $(ball);

		// Is my turn?
		if ( this.m_szPlayerColor == this.m_szTurnColor ) {
			// Is my ball?
			if ( $ball.data('owner') == this.m_szPlayerColor ) {
				return this.toggleSelectBall(id);
			}

			// Move selected balls
			if ( 0 < this.m_arrSelectedBalls.length ) {
				return this.moveSelectedTo(id);
			}
		}

	}, // END clickedOn


	/**
	 * A player selects a ball with this function (his own color ofcourse), by clicking on the board
	 */
	fetchMap : function() {
		var self = this;
		return $.get('?fetch_map=1', function(rv) {
			// Populate map
			$.each(rv.balls, function(i, b) {
				var $ball = $('#ball_' + b[0] + '_' + b[1] + '_' + b[2]);
				$ball
					.data('owner', b[3])
					.removeClass(Abalone.oppositeColor(b[3]))
					.addClass(b[3]);
			});
		});

	}, // fetchMap


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

	}, // sendOrder


	/**
	 * N e x t   f i e l d
	 */
	getNextField : function( f_coord, f_dir ) {
		return this.getCoordInfo(f_dir).add(f_coord).img();

	}, // getNextField


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

	}, // moveSelectedTo


	/**
	 * Check if coords are aligned
	 */
	coordsAreAligned : function() {
		var balls = this.m_arrSelectedBalls.map(function(id) {
			var C = id.substr(5).split('_');
			return { x: ~~C[0], y: ~~C[1], z: ~~C[2] };
		});

		// First ball is always fine
		if ( 1 >= balls.length ) {
			return true;
		}

		// Find common axis/direction
		var direction,
			values = { x: [], y: [], z: [] };
		$.each(['x', 'y', 'z'], function(i, axis) {
			values[axis].push(balls[0][axis]);
			values[axis].push(balls[1][axis]);
			if ( balls[0][axis] == balls[1][axis] ) {
				direction = axis;
				delete values[axis];
			}
		});

		if ( !direction ) {
			return false;
		}

		// The rest must have that direction as well
		for ( var i=2, L=balls.length; i<L; i++ ) {
			if ( balls[i][direction] != balls[0][direction] ) {
				return false;
			}
			values.x && values.x.push(balls[i].x);
			values.y && values.y.push(balls[i].y);
			values.z && values.z.push(balls[i].z);
		}

		// Find distance between ends
		var diffs = [];
		$.each(values, function(axis, values) {
			diffs.push(Math.max.apply(Math, values) - Math.min.apply(Math, values));
		});

		// Shortest distance
		if ( Math.min.apply(Math, diffs) != balls.length-1 ) {
			return false;
		}

		// Aaaaight!

		return direction;

	}, // coordsAreAligned


	/**
	 * A player selects a ball with this function (his own color ofcourse), by clicking on the board
	 */
	toggleSelectBall : function( id ) {
		var i = $.inArray(id, this.m_arrSelectedBalls);

		if ( i < 0 ) {
			return this.selectBall(id);
		}

		return this.unselectBall(i);

	}, // toggleSelectBall


	/**
	 * Add coords to list and color ball on board to hilite
	 */
	selectBall : function( id ) {
		if ( 3 <= this.m_arrSelectedBalls.length ) {
			return false;
		}

		this.m_arrSelectedBalls.push(id);

		var caa = this.coordsAreAligned();
		if ( !caa ) {
			this.m_arrSelectedBalls.pop();

			this.debug('Won\'t select because already out of line!');

			return false;
		}

		var $ball = $(this.index[id]);
		$ball.addClass('selected');

		return false;

	}, // selectBall


	/**
	 * Remove coords from list and color ball on board to normal
	 */
	unselectBall : function( i ) {
		var id = this.m_arrSelectedBalls[i],
			$ball = $(this.index[id]);

		this.m_arrSelectedBalls.splice(i, 1);
		$ball.removeClass('selected');

		return false;

	}, // unselectBall


	/**
	 * Unselects all balls, maybe not a necessary function
	 */
	unselectAllBalls : function() {
		while ( 0 < this.m_arrSelectedBalls.length ) {
			this.unselectBall(0);
		}
		return false;

	}, // unselectAllBalls


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

	}, // getCoordInfo


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

	}, // getOppositeDirection


	debug : function( f_msg ) {
		if ( window.console && window.console.debug ) {
			window.console.debug(f_msg);
		}

	}, // debug


}; // var Abalone
