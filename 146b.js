
// Config
var cellSize = 50;
var outsidePadding = 25;
var lineWidth = 9;
var gridColor = '#cccccc';
var gridXColor = '#aaaaaa';
var gridHiliteColor = '#aaaaaa';
var textColor = '#000000';
var textCorrectColor = '#ffffff';

// Context
var html = document.documentElement;
var elCanvas = $('canvas');
var ctx = elCanvas.getContext('2d');
var _w = elCanvas.width;
var _h = elCanvas.height;
var evType = 'ontouchstart' in html ? 'touchstart' : 'click';


r.extend(Coords2D, {
	distanceTo: function(C) {
		return Math.sqrt( Math.pow(this.x - C.x, 2) + Math.pow(this.y - C.y, 2) );
	},
	multiply: function(f) {
		return new Coords2D(
			Math.round(this.x * f),
			Math.round(this.y * f)
		);
	},
	log: function() {
		console.log(this.x, this.y);
	}
});


r.extend(Array, {
	intersect: function(arr) {
		var matches = [];
		this.each(function(v1) {
			arr.each(function(v2) {
				if ( v1 == v2 ) {
					matches.push(v1);
				}
			});
		});
		return matches;
	}
});


function Connector(x, y, dir) {
	this.dir = dir;
	this.x = x;
	this.y = y;
}
Connector.fromString = function(str) {
	var pts = str.split('-');
	return new Connector(~~pts[1], ~~pts[2], pts[0]);
};
r.extend(Connector, {
	bit: function(lvl) {
		return this.bitX() + this.bitY() * (lvl.width + 1);
	},
	bitX: function() {
		return this.x;
	},
	bitY: function() {
		return this.y * 2 + Number(this.dir == 'ver');
	},
	touches: function(con) {
		var coords1 = this.getEnds().invoke('join');
		var coords2 = con.getEnds().invoke('join');
		return coords1.intersect(coords2).length == 1;
	},
	toString: function() {
		return this.dir + '-' + this.x + '-' + this.y;
	},
	getNeighborStrings: function() {
		var ends = this.getEnds();
		var me = this.toString();

		var cons1 = ends[0].getConnectors();
		cons1 = cons1.invoke('toString');
		var meIndex = cons1.indexOf(me);
		delete cons1[meIndex];

		var cons2 = ends[1].getConnectors();
		cons2 = cons2.invoke('toString');
		var meIndex = cons2.indexOf(me);
		delete cons2[meIndex];

		var cons = cons1.concat(cons2);
		return cons;
	},
	findNextIn: function(cons) {
		var neighbors = this.getNeighborStrings();
		var nexts = cons.intersect(neighbors);
		if ( nexts.length ) {
			var next = nexts[0];
			var nextIndex = cons.indexOf(next);
			delete cons[nextIndex];
			return Connector.fromString(next);
		}
	},
	getEnds: function() {
		if ( this.dir == 'hor' ) {
			return [
				new End(this.x, this.y),
				new End(this.x + 1, this.y)
			];
		}
		return [
			new End(this.x, this.y),
			new End(this.x, this.y + 1)
		];
	},
	valid: function() {
		var xp = this.dir == 'ver' ? 1 : 0;
		var yp = this.dir == 'hor' ? 1 : 0;
		return this.x >= 0 && this.y >= 0 && this.x < lvl.width + xp && this.y < lvl.height + yp;
	},
	getCenterPosition: function() {
		var plus = cellSize/2,
			dir = this.dir == 'hor' ? 'x' : 'y',
			pos = new Coords2D(outsidePadding + this.x * cellSize, outsidePadding + this.y * cellSize);
		pos[dir] += plus;
		pos.source = this;
		return pos;
	},
	hilite: function(color) {
		return hiliteConnector(this, color || 'red');
	}
}, Coords2D.prototype);


function End(x, y) {
	this.x = x;
	this.y = y;
}
r.extend(End, {
	getConnectors: function() {
		var cons = [];
		r.each([[0, -1, 'ver'], [-1, 0, 'hor'], [0, 0, 'ver'], [0, 0, 'hor']], function(vector) {
			var con = new Connector(this.x + vector[0], this.y + vector[1], vector[2]);
			con.valid(lvl) && cons.push(con);
		}, this);
		return cons;
	},
	getPosition: function() {
		return new Coords2D(outsidePadding + this.x * cellSize, outsidePadding + this.y * cellSize);
	}
}, Coords2D.prototype);


function Cell(x, y) {
	this.x = x;
	this.y = y;
}
r.extend(Cell, {
	getConnectors: function() {
		var cons = [];
		r.each([[0, 0, 'ver'], [0, 0, 'hor'], [1, 0, 'ver'], [0, 1, 'hor']], function(vector) {
			var con = new Connector(this.x + vector[0], this.y + vector[1], vector[2]);
			con.valid(lvl) && cons.push(con);
		}, this);
		return cons;
	},
	getHilitedConnecters: function(hilited) {
		return this.getConnectors().filter(function(con) {
			return hilited.contains(String(con));
		});
	}
}, Coords2D.prototype);


function getClosestConnector(C) {
	var closestConnector = lvl.connectors[0],
		minDistance = closestConnector.getCenterPosition().distanceTo(C);
	for ( var i=1, L=lvl.connectors.length; i<L; i++ ) {
		var distance = lvl.connectors[i].getCenterPosition().distanceTo(C);
		if ( distance < minDistance ) {
			minDistance = distance;
			closestConnector = lvl.connectors[i];
		}
	}
	return closestConnector;
}

function getAllConnectors() {
	var connectors = [];
	for ( var y=0; y<=lvl.height; y++ ) {
		for ( var x=0; x<=lvl.width; x++ ) {
			if ( x < lvl.width ) {
				connectors.push(new Connector(x, y, 'hor'));
			}
			if ( y < lvl.height ) {
				connectors.push(new Connector(x, y, 'ver'));
			}
		}
	}
	return connectors;
}

function getAllEnds() {
	var ends = [];
	for ( var y=0; y<=lvl.height; y++ ) {
		for ( var x=0; x<=lvl.width; x++ ) {
			ends.push(new End(x, y));
		}
	}
	return ends;
}

function getNeighborCells(connector) {
	var cells = [],
		hor = connector.x,
		ver = connector.y,
		dx = connector.dir == 'ver' ? 1 : 0,
		dy = connector.dir == 'hor' ? 1 : 0;
	if ( hor - dx >= 0 && ver - dy >= 0 ) {
		cells.push(new Coords2D(hor - dx, ver - dy));
	}
	if ( hor < lvl.width && ver < lvl.height ) {
		cells.push(new Coords2D(hor, ver));
	}
	return cells;
}

function hiliteConnector(connector, withState) {
	var dir = connector.dir,
		hor = connector.x,
		ver = connector.y,
		method = dir == 'ver' ? getVerticalConnectorCoords : getHorizontalConnectorCoords,
		cs = method(hor, ver);

	var ckey = dir + '-' + hor + '-' + ver,
		eIndex = connectors.indexOf(ckey),
		exists = eIndex != -1,
		color = typeof withState == 'string' ? withState : (withState && exists ? gridColor : gridHiliteColor);

	drawLine(cs[0], cs[1], color, true);
	if ( withState == true ) {
		exists ? connectors.splice(eIndex, 1) : connectors.push(ckey);
	}

	return !exists;
}

function getConnector(c) {
	var o = Math.floor(lineWidth / 2);

	var hor, ver;
	for ( var x=0; x<=lvl.width; x++ ) {
		var center = outsidePadding + x * cellSize;
		if ( c.x >= center-o && c.x <= center+o ) {
			hor = x;
			break;
		}
	}
	for ( var y=0; y<=lvl.height; y++ ) {
		var center = outsidePadding + y * cellSize;
		if ( c.y >= center-o && c.y <= center+o ) {
			ver = y;
			break;
		}
	}

	// Matched vertical connector
	if ( hor != null && ver == null ) {
		ver = Math.floor(c.y / (_h / lvl.height));
		return ['ver', hor, ver];
	}

	// Matched horizontal connector
	else if ( ver != null && hor == null ) {
		hor = Math.floor(c.x / (_w / lvl.width));
		return ['hor', hor, ver];
	}
}

function getHorizontalConnectorCoords(x, y) {
	var o = 0; // Math.floor(lineWidth / 2);
	var y = outsidePadding + y * cellSize - o,
		x = outsidePadding + x * cellSize + o;
	return [
		new Coords2D(x, y),
		new Coords2D(x + cellSize, y)
	];
}

function getVerticalConnectorCoords(x, y) {
	var o = 0; // Math.floor(lineWidth / 2);
	var y = outsidePadding + y * cellSize + o,
		x = outsidePadding + x * cellSize - o;
	return [
		new Coords2D(x, y),
		new Coords2D(x, y + cellSize)
	];
}

function drawNumber(c, number, loc) {
	var ckey = loc.join('-'),
		correct = (conditions[ckey] == null && number == 0) || conditions[ckey] == number;
	ctx.font = '30px sans-serif';
	ctx.fillStyle = correct ? textCorrectColor : textColor;
	ctx.fillText(number, c.x + 11, c.y + 32);
}

function getCellCoords(x, y) {
	var o = Math.floor(lineWidth / 2);
	return new Coords2D(
		outsidePadding + x * cellSize + o,
		outsidePadding + y * cellSize + o
	);
}

function hiliteConnectors() {
	r.each(connectors, function(key) {
		var c = key.split('-'),
			con = new Connector(c[1], c[2], c[0]);
		hiliteConnector(con, false);
	});
}

function drawGrid(lvl) {
	for ( var x=0; x<=lvl.width; x++ ) {
		// Vertical lines
		var xc = outsidePadding + x * cellSize;
		drawLine({y: 0, x: xc}, {y: _h, x: xc});
	}
	for ( var y=0; y<=lvl.height; y++ ) {
		// Horizontal lines
		var yc = outsidePadding + y * cellSize;
		drawLine({x: 0, y: yc}, {x: _w, y: yc});

		for ( var x=0; x<=lvl.width; x++ ) {
			var xc = outsidePadding + x * cellSize;
			drawDot({x: xc, y: yc}, gridXColor);
		}
	}
}

function drawDot(c, color) {
	var o = Math.floor(lineWidth / 2);
	ctx.fillStyle = color || gridXColor;
	ctx.fillRect(c.x - o, c.y - o, lineWidth, lineWidth);
}

function drawLine(p1, p2, color, dots) {
	ctx.lineWidth = lineWidth;
	ctx.strokeStyle = color || gridColor;
	ctx.beginPath();
	ctx.moveTo(p1.x, p1.y);
	ctx.lineTo(p2.x, p2.y);
	ctx.stroke();

	if ( dots ) {
		drawDot(p1);
		drawDot(p2);
	}
}

function initGrid(lvl) {
	_w = outsidePadding * 2 + cellSize * lvl.width;
	_h = outsidePadding * 2 + cellSize * lvl.height;
	elCanvas.width = _w;
	elCanvas.height = _h;
}
