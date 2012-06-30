
Coord = THREE.Vector3;

Coord.prototype.each = function(cb) {
	this.x && cb('x', this.x);
	this.y && cb('y', this.y);
	this.z && cb('z', this.z);

	return this;
};

Coord.prototype.underscore = function(cb) {
	return this.x + '_' + this.y + '_' + this.z;
};

Coord.prototype.reverse = function(cb) {
	var C = this.clone();

	C.x *= -1;
	C.y *= -1;
	C.z *= -1;

	return C;
};

function Abalone( container, f_szYouColor, f_szTurnColor, fetch ) {
	this.$container = $(container);
	this.m_szPlayerColor = f_szYouColor;
	this.m_szOpponentColor = Abalone.oppositeColor(this.m_szPlayerColor);
	this.m_szTurnColor = f_szTurnColor;
	this.m_arrSelectedBalls = [];

	// Create [id => DOM element] index
	this.index = {};
	this.createIndex();

	// Fetch balls
	fetch && this.fetchMap();

	// Attach DOM handlers
	var self = this,
		match = '.ball.' + this.m_szPlayerColor;
	this.$container.on('click', match, function(e) {
		self.clickedOn(e.target.id.substr(5));
	});
	this.$container.on('click', 'a.direction', function(e) {
		e.preventDefault();

		C = this.dataset.dir.split(',');
		C = new Coord(~~C[0], ~~C[1], ~~C[2]);
		self.debug('move:', self.move(C));
	});
}

Abalone.selectMaxBalls = 4;
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
			self.index[ el.id.substr(5) ] = el;
		});

		return this.index;

	}, // END createIndex


	/**
	 * Clicked on an Abalone IMG (why and the consequences are yet unknown)
	 */
	clickedOn : function( id ) {
		var ball = this.index[id];

		// Is my turn?
		if ( this.m_szPlayerColor == this.m_szTurnColor ) {
			// Is my ball?
			if ( ball.owner == this.m_szPlayerColor ) {
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
					.removeClass(Abalone.oppositeColor(b[3]))
					.addClass(b[3]);
				$ball[0].owner = b[3];
			});
		}).error(function(xhr, error) {
			alert('Fetch failed. Response:' + "\n\n" + xhr.responseText);
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
	 * Move selected balls into a direction (Coord)
	 */
	move : function( direction ) {
		var self = this, info, u, u1, u2, balls, Cnexts, i, M, C, pushables, P, Clast, B, Blast;

		balls = this.selectedBalls();
		if ( !balls.length ) {
this.debug('No balls selected');
			return false;
		}
this.debug('balls:', balls);

		info = this.coordsAreAligned();
		if ( !info ) {
this.debug('Invalid balls selection');
			return false;
		}
this.debug('info:', info);

		// Verify direction/axis
		if ( 1 < balls.length ) {
			u = direction.underscore();
			u1 = info.direction.underscore();
			u2 = info.direction.reverse().underscore();
			if ( u != u1 && u != u2 ) {
this.debug("You can't go there... Correct direction: " + u1 + ' or ' + u2);
				return false;
			}
		}

		Cnexts = this.nexts(balls, direction, true);
this.debug('next coords:', Cnexts);

		// RULES
		// 1. Can't push off-board
		// 2. Can push only [balls.length-1] other balls
		// 3. Can't push self (directly or indirectly)

		// 1. Can't push off-board
		// => Next coords must exist
		if ( !Cnexts.length ) {
this.debug('move/fail: no target');
			return false;
		}

		// 2. Can push only [balls.length-1] other balls
		// => One of the next X slots must be empty (off-board or unowned)
		for ( i=0, M=balls.length; i<M; i++ ) {
			C = Cnexts[i];
			if ( !C || !this.index[C.underscore()].owner ) {
this.debug('empty slot found:', C || 'off-board', 'pushables:', i);
				// That's a wrap!
				pushables = i;
				break;
			}
		}

		// No empty slot found?
		if ( undefined === pushables ) {
this.debug('move/fail: invalid target? not enough momentum? need somewhere to push to...');
			return false;
		}

		// 3. Can't push self (directly or indirectly)
		// All pushables must be others
		for ( i=0; i<pushables; i++ ) {
			C = Cnexts[i];
			P = this.index[C.underscore()];
			if ( !P || this.m_szPlayerColor == P.owner ) {
this.debug("move/fail: can't push self");
				return false;
			}
		}

		// Woohoo!
		// Now let's push...

		// Refetch balls in the right order
		balls = this.nexts([Cnexts[0]], direction.reverse()).slice(0, M);

		// Prepare movables + target slot (empty or off-board)
		movables = Cnexts.slice(0, pushables);
		$.each(balls, function(i, C) {
			movables.unshift(C);
		});
		movables.push(Cnexts[pushables]);
this.debug('movables:', movables);

		// Cycle through backwards and replace owners
		for ( i=movables.length; i>=0; i-- ) {
			C = movables[i];
			if ( Clast ) {
				B = this.index[C.underscore()];
				Blast = this.index[Clast.underscore()];
this.debug('move', B, B.owner, 'to', Blast, Blast.owner);
				$(Blast).removeClass('' + Blast.owner).addClass('' + B.owner);
				Blast.owner = B.owner;
			}
			Clast = C;
		}

		// Update straggler
		B = this.index[C.underscore()];
this.debug('left over:', B); 
		$(B).removeClass('' + B.owner);
		delete B.owner;

		// Remove selectedness
		this.m_arrSelectedBalls = [];
		this.$container.find('.selected').removeClass('selected');

		return true;

	}, // move


	/**
	 * Move the selected balls to another position (clicked on a ball or an empty field)
	 *
	moveSelectedTo : function( f_objBall ) {
		// Min 1 ball selected and max Abalone.selectMaxBalls
		if ( 0 == this.m_arrSelectedBalls.length || Abalone.selectMaxBalls < this.m_arrSelectedBalls.length ) {
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
			if ( Abalone.selectMaxBalls <= this.m_arrSelectedBalls.length && to2Position.owner == this.m_szOpponentColor && ( !to3Position || !to3Position.owner ) ) {
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
	 * Selected balls in Coord format
	 */
	selectedBalls : function() {
		return this.m_arrSelectedBalls.map(function(id) {
			C = id.split('_');
			return new Coord(~~C[0], ~~C[1], ~~C[2]);
		});

	}, // balls


	/**
	 * Check if coords are aligned
	 */
	coordsAreAligned : function() {
		var self = this, balls, C, axis, values, i, diffs, direction, M;

		balls = this.selectedBalls();
this.debug('balls:', balls);

		// First ball is always fine
		if ( 1 >= balls.length ) {
			return true;
		}

		// Find common axis/direction
		values = { x: [], y: [], z: [] };
		$.each(['x', 'y', 'z'], function(i, ax) {
			values[ax].push(balls[0][ax]);
			values[ax].push(balls[1][ax]);
			if ( balls[0][ax] == balls[1][ax] ) {
				axis = ax;
				delete values[ax];
self.debug('found common axis: ' + ax);
			}
		});

		if ( !axis ) {
self.debug('found no common axis');
			return false;
		}

		// Find reversable direction (for forecasting the next ball)
		direction = new Coord;
		$.each(values, function(ax, values) {
			direction[ax] = balls[0][ax] > balls[1][ax] ? 1 : -1;
		});
self.debug('reversable direction:', direction);

		// The rest must have that axis as well
		for ( i=2, L=balls.length; i<L; i++ ) {
			if ( balls[i][axis] != balls[0][axis] ) {
self.debug('ball # ' + i + ' has different axis ' + axis + ' value: ' + balls[i][axis]);
				return false;
			}

			values.x && values.x.push(balls[i].x);
			values.y && values.y.push(balls[i].y);
			values.z && values.z.push(balls[i].z);
		}
self.debug('ball coord values:', values);

		// Find distance between ends
		diffs = [];
		$.each(values, function(ax, values) {
			diffs.push(Math.max.apply(Math, values) - Math.min.apply(Math, values));
		});
self.debug('diffs:', diffs);

		// Shortest distance
		M = balls.length-1;
		if ( diffs != M + ',' + M ) {
			return false;
		}

		// Aaaaight!

		var potentials = this.potentialNexts(balls, direction);
self.debug('potentials:', potentials);

		return {
			axis: axis,
			direction: direction,
			potentials: potentials
		};

	}, // coordsAreAligned


	/**
	 * All the next holes into one direction (Coord)
	 */
	nexts : function( balls, direction ) {
		var self = this, next, nexts;

		nexts = [];
		next = balls;

		while ( true ) {
			next = this.potentialNexts(next, direction, true);
			if ( next && next[0] && this.index[ next[0].underscore() ] ) {
				nexts.push(next[0]);
			}
			else {
this.debug('next/fail:', next[0], '"' + next[0].underscore() + '"');
				break;
			}
		}

		return nexts;

	}, // nexts


	/**
	 * The two potential next coordinates (or one if oneWay)
	 */
	potentialNexts : function( balls, direction, oneWay ) {
		var self = this, potentials, Ca, Cb, C, coords;

		potentials = {};
		$.each(balls, function(i, C) {
			Ca = $.extend({}, C);
			Cb = $.extend({}, C);

			direction.each(function(ax, dir) {
				Ca[ax] += dir;
				Cb[ax] -= dir;
			});

			Ca = Ca.underscore();
			potentials[Ca] || (potentials[Ca] = 0);
			potentials[Ca]++;

			if ( !oneWay ) {
				Cb = Cb.underscore();
				potentials[Cb] || (potentials[Cb] = 0);
				potentials[Cb]++;
			}
		});

		$.each(balls, function(i, C) {
			C = C.underscore();
			delete potentials[C];
		});

		coords = [];
		$.each(potentials, function(id, n) {
			C = id.split('_');
			coords.push(new Coord(~~C[0], ~~C[1], ~~C[2]));
		});

		return coords;

	}, // potentialNexts


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
		if ( Abalone.selectMaxBalls <= this.m_arrSelectedBalls.length ) {
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


	debug : function( msg ) {
		if ( window.console && window.console.debug ) {
			window.console.debug.apply(window.console, arguments);
		}

	}, // debug


}; // var Abalone
