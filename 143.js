
"use strict";

var Coord = THREE.Vector3;

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
	var self = this;

	this.$container = $(container);
	this.m_szPlayerColor = f_szYouColor;
	this.m_szOpponentColor = Abalone.oppositeColor(this.m_szPlayerColor);
	this.m_szTurnColor = f_szTurnColor;

	// Create [id => DOM element] index
	this.index = {};
	this.createIndex();

	// Hilite difference
	this.hiliteDifference();

	// Attach DOM handlers
	var self = this,
		match = '.ball.' + this.m_szPlayerColor;
	this.$container.on('click', function(e) {
		e.preventDefault();
	});
	this.$container.on('click', match, function(e) {
		self.clickedOn(e.target.id.substr(5));
	});
	this.$container.on('click', 'a.direction', function(e) {
		e.preventDefault();

		var C = this.dataset.dir.split(',');
		C = new Coord(~~C[0], ~~C[1], ~~C[2]);
		self.move(C);
	});

	// Start polling
	self.tickStatus();

	document.body.classList.add(self.m_szPlayerColor == self.m_szTurnColor ? 'my-turn' : 'their-turn');
	document.body.classList.add('ready');
}

Abalone.INACTIVE_AFTER = 20;
Abalone.selectMaxBalls = 4;
Abalone.oppositeColor = function( color ) {
	return 'white' == color ? 'black' : 'white';
};

Abalone.prototype = {


	/**
	 * Clicked on an Abalone IMG (why and the consequences are yet unknown)
	 */
	createIndex: function() {
		var self = this;

		var $balls = this.$container.find('.ball').each(function(i, el) {
			self.index[ el.id.substr(5) ] = el;
		});

		return this.index;

	}, // createIndex


	/**
	 *
	 */
	hiliteDifference: function() {
		var old = JSON.parse(sessionStorage.abaloneState || '{}');
console.debug('old', old);
		var current = this.getCurrentState();
console.debug('current', current);

		for ( var coord in old ) {
			if ( old[coord] != current[coord] ) {
				this.index[coord].classList.add('changed');
			}
		}

		this.saveCurrentState(current);
	},


	/**
	 *
	 */
	getCurrentState: function() {
		var state = {};
		for ( var coord in this.index ) {
			state[coord] = this.owner(this.index[coord]) || '';
		}

		return state;
	},


	/**
	 *
	 */
	saveCurrentState: function( state ) {
		sessionStorage.abaloneState = JSON.stringify(state || this.getCurrentState());
	},


	/**
	 *
	 */
	owner: function( ball ) {
		return ball.classList.contains('black') ? 'black' : ball.classList.contains('white') ? 'white' : null;
	},


	/**
	 * Clicked on an Abalone IMG (why and the consequences are yet unknown)
	 */
	clickedOn: function( id ) {
		var ball = this.index[id];

		// Is my turn?
		if ( this.m_szPlayerColor == this.m_szTurnColor ) {
console.debug('click: my turn');
			// Is my ball?
			if ( this.owner(ball) == this.m_szPlayerColor ) {
console.debug('click: my ball');
				return this.toggleSelectBall(ball);
			}

			// Move selected balls
			var selecteds = this.selecteds();
			if ( selecteds.length ) {
console.debug('click: move');
				return this.moveSelectedTo(id);
			}
		}

	}, // clickedOn


	/**
	 *
	 */
	selecteds: function() {
		return this.$container.find('.ball.selected').get();
	},


	/**
	 *
	 */
	tickStatus: function() {
		var self = this;

		if (!document.hidden) {
			this.updateStatus();
		}

		setTimeout(function() {
			self.tickStatus();
		}, 800);
	},


	/**
	 * Waiting for turn or other player
	 */
	updateStatus: function() {
		var self = this;

		return $.get('?status', function(rv) {
			if ( rv.status.turn != self.m_szTurnColor ) {
				location.reload();
				return;
			}

			var $tr = $('tr.other');
			$tr[0].cells[1].title = `Online: ${rv.status.opponentOnline} sec ago`;
			$tr.toggleClass('inactive', rv.status.opponentOnline > Abalone.INACTIVE_AFTER);
		});

	}, // updateStatus


	/**
	 *
	 */
	nextInDirection: function( balls, direction ) {
		var coords = balls.map(function(ball) {
			return ball.underscore();
		});

		for (var i=0; i<balls.length; i++) {
			var ball = balls[i];
			var next = ball.clone().addSelf(direction);
			if (coords.indexOf(next.underscore()) == -1) {
				return next;
			}
		}
	},


	/**
	 *
	 */
	offsets: function( balls, direction ) {
		return balls.map(function(ball) {
			return ball.clone().addSelf(direction);
		});
	},


	/**
	 * Move selected balls into a direction (Coord)
	 */
	move: function( direction ) {
		var self = this;

console.debug('direction', direction);

		var balls = this.selectedCoords();
		if ( !balls.length ) {
console.warn('No balls selected');
			return false;
		}
console.debug('balls:', balls);

		// @todo Do all validation only server side

		var info = this.coordsAreAligned();
		if ( !info ) {
console.warn('Invalid balls selection');
			return false;
		}
console.debug('info:', info);

		// Find direction type
		var inline = balls.length == 1 || direction.underscore() == info.direction.underscore() || direction.clone().negate().underscore() == info.direction.underscore();
console.debug('inline', inline);

		// Find target location(s)
		var targets;
		if ( inline ) {
			// Find 1 extension following axis
			targets = [this.nextInDirection(balls, direction)];
		}
		else {
			// Find all adjacents following axis
			targets = balls.map(function(ball) {
				return ball.clone().addSelf(direction);
			});
		}
console.debug('targets', targets);

		// All targets must exist
		var wrong = targets.some(function(coord) {
			if ( !self.index[coord.underscore()] ) {
				return true;
			}
		});
		if ( wrong ) {
console.warn('Not all targets exist.');
			return;
		}

		// Target can't be our own
		if ( inline ) {
			var ball = this.index[ targets[0].underscore() ];
			if ( this.owner(ball) == this.m_szPlayerColor ) {
console.warn("Can't push own ball.");
				return;
			}
		}

		// All targets must be empty
		if ( !inline ) {
			var wrong = targets.some(function(coord) {
				var ball = self.index[ coord.underscore() ];
				if ( self.owner(ball) ) {
					return true;
				}
			});
			if ( wrong ) {
console.warn("Can't push sideways onto other balls.");
				return;
			}
		}

		// Can only push fewer other balls
		var pushing = [];
		if ( inline ) {
			var nexts = this.nexts(balls, direction);
console.debug('nexts', nexts);

			var owners = nexts.map(function(coord) {
				return self.owner(self.index[ coord.underscore() ]);
			});
console.debug('owners', owners);

			owners.some(function(owner, i) {
				if (!owner) {
					return true;
				}

				pushing.push(nexts[i]);
			});
console.debug('pushing', pushing);
			if ( pushing.length > balls.length-1 ) {
console.warn("Can only push " + (balls.length-1) + " balls, not " + pushing.length + ".");
				return;
			}

			var pushingOwners = pushing.map(function(coord) {
				return self.owner(self.index[ coord.underscore() ]);
			});
console.debug('pushingOwners', pushingOwners);
			if ( pushingOwners.indexOf(this.m_szPlayerColor) != -1 ) {
console.warn("Can't push own balls.");
				return;
			}
		}

		// Find pusher/pushee target location(s)
		var pusherTargets = this.offsets(balls, direction);
// console.debug('pusheeTargets', pusheeTargets);
		var pusheeTargets = this.offsets(pushing, direction);
// console.debug('pusheeTargets', pusheeTargets);

		// Replace pushees
		this.clearBalls(pushing);
		this.placeBalls(pusheeTargets, this.m_szOpponentColor);

		// Replace pushers
		this.clearBalls(balls);
		this.placeBalls(pusherTargets, this.m_szPlayerColor);

		// Collect all changed locations and send it to the server
		// @todo Send the actual move to the server and let IT decide what to change
		var changed = pushing.concat(pusheeTargets).concat(balls).concat(pusherTargets);
		this.sendChange(changed);

	}, // move


	/**
	 *
	 */
	clearBalls: function( coords ) {
		for (var i=0; i<coords.length; i++) {
			var ball = this.index[ coords[i].underscore() ];
			ball.className = 'ball';
		}
	},


	/**
	 *
	 */
	placeBalls: function( coords, color ) {
		for (var i=0; i<coords.length; i++) {
			var ball = this.index[ coords[i].underscore() ];
			if ( ball ) {
				ball.className = 'ball ' + color;
			}
		}
	},


	/**
	 *
	 */
	sendChange: function( coords ) {
		var self = this;

		var changes = {};
		for (var i=0; i<coords.length; i++) {
			var coord = coords[i];
			var ball = this.index[ coord.underscore() ];
			if ( ball ) {
				changes[ coord.underscore() ] = this.owner(ball);
			}
		}
console.debug('changes:', changes);

		this.saveCurrentState();

		// Prep & send data
		var data = {changes: changes};
		$.post('?send_changes=1', data, function(rv) {
			location.reload();
		});

	}, // sendChange


	/**
	 * Selected balls in Coord format
	 */
	selectedCoords: function() {
		var self = this;

		var balls = this.selecteds();
		return balls.map(function(ball) {
			return self.ballToCoord(ball);
		});

	}, // selectedCoords


	/**
	 *
	 */
	ballToCoord: function( ball ) {
		var C = ball.id.substr(5).split('_');
		return new Coord(parseInt(C[0]), parseInt(C[1]), parseInt(C[2]))
	},


	/**
	 * Check if coords are aligned
	 */
	coordsAreAligned: function() {
		var self = this, balls, C, axis, values, i, diffs, direction, M;

		balls = this.selectedCoords();
// console.debug('balls:', balls);

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
console.debug('found common axis: ' + ax);
			}
		});

		if ( !axis ) {
console.debug('found no common axis');
			return false;
		}

		// Find reversable direction (for forecasting the next ball)
		direction = new Coord;
		$.each(values, function(ax, values) {
			direction[ax] = balls[0][ax] > balls[1][ax] ? 1 : -1;
		});
// console.debug('reversable direction:', direction);

		// The rest must have that axis as well
		for ( var i=2, L=balls.length; i<L; i++ ) {
			if ( balls[i][axis] != balls[0][axis] ) {
// console.debug('ball # ' + i + ' has different axis ' + axis + ' value: ' + balls[i][axis]);
				return false;
			}

			values.x && values.x.push(balls[i].x);
			values.y && values.y.push(balls[i].y);
			values.z && values.z.push(balls[i].z);
		}
// console.debug('ball coord values:', values);

		// Find distance between ends
		diffs = [];
		$.each(values, function(ax, values) {
			diffs.push(Math.max.apply(Math, values) - Math.min.apply(Math, values));
		});
// console.debug('diffs:', diffs);

		// Shortest distance
		M = balls.length-1;
		if ( diffs != M + ',' + M ) {
			return false;
		}

		// Aaaaight!

		var potentials = this.potentialNexts(balls, direction);
// console.debug('potentials:', potentials);

		return {
			axis: axis,
			direction: direction,
			potentials: potentials
		};

	}, // coordsAreAligned


	/**
	 * All the next holes into one direction (Coord)
	 */
	nexts: function( balls, direction ) {
		var self = this, next, nexts;

		nexts = [];
		next = balls;

		while ( true ) {
			next = this.potentialNexts(next, direction, true);
			if ( next && next[0] && this.index[ next[0].underscore() ] ) {
				nexts.push(next[0]);
			}
			else {
console.debug('next/fail:', next[0], '"' + next[0].underscore() + '"');
				break;
			}
		}

		return nexts;

	}, // nexts


	/**
	 * The two potential next coordinates (or one if oneWay)
	 */
	potentialNexts: function( balls, direction, oneWay ) {
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
	toggleSelectBall: function( ball ) {
		if ( !ball.classList.contains('selected') ) {
			return this.selectBall(ball);
		}

		return this.unselectBall(ball);

	}, // toggleSelectBall


	/**
	 * Add coords to list and color ball on board to hilite
	 */
	selectBall: function( ball ) {
		var selecteds = this.selecteds();
		if ( Abalone.selectMaxBalls <= selecteds.length ) {
			return;
		}

		ball.classList.add('selected');

		var caa = this.coordsAreAligned();
		if ( !caa ) {
			ball.classList.remove('selected');
console.debug("Won't select because out of line.");
			return;
		}

	}, // selectBall


	/**
	 * Remove coords from list and color ball on board to normal
	 */
	unselectBall: function( ball ) {
		ball.classList.remove('selected');

	}, // unselectBall


	/**
	 * Dev helper
	 */
	debug: function( msg ) {
		if ( window.console && window.console.debug ) {
			window.console.debug.apply(window.console, arguments);
		}

	} // debug


}; // var Abalone
