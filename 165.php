<?php

$maps = array(1 =>
	array(
		'map' => array(
			' ttt',
			' xx ',
			'    ',
			'    ',
		),
		'target' => 1,
		'snakes' => array(
			array(array(0,0), array(0,1), array(0,2), array(1,2), array(2,2), array(3,2)),
			array(array(1,3), array(2,3), array(3,3)),
		),
	),
	array(
		'map' => array(
			'xx    ',
			'   xx ',
			' x xx ',
			'      ',
			'x x xx',
			'   ttt',
		),
		'target' => 1,
		'snakes' => array(
			array(array(2,0), array(3,0), array(4,0), array(5,0), array(5,1), array(5,2), array(5,3), array(4,3), array(3,3), array(3,4), array(3,5), array(4,5)),
			array(array(0,1), array(1,1), array(2,1)),
			array(array(0,3), array(1,3), array(1,4), array(1,5), array(0,5)),
		),
	),
	array(
		'map' => array(
			'ttt   ',
			'xxxx x',
			'     x',
			'xx x x',
			'      ',
			' xxxx ',
		),
		'target' => 2,
		'snakes' => array(
			array(array(4,0), array(4,1), array(4,2), array(4,3), array(4,4)),
			array(array(0, 2), array(1, 2), array(2, 2), array(3,2)),
			array(array(1,4), array(2,4), array(3,4)),
			array(array(0,4), array(0,5)),
		),
	),
);

$g_w = $g_h = 6;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width" />
<title>Machinarium II</title>
<script>CELL_SIZE = 80</script>
<style>
html, body {
	margin: 0;
	padding: 0;
	background: #aaa;
}
.maps {
	height: 1.5em;
	line-height: 1.5em;
	font-size: 1.5em;
	text-align: center;
	padding: 0;
}
.maps:before {
	content: "Load map: ";
}
.maps li {
	display: inline-block;
}
.maps li:not(:first-child):before {
	content: ", ";
}
.maps a {
	text-decoration: none;
	color: green;
}
.maps a:target {
	font-weight: bold;
}
.maps a:active,
.maps a:focus {
	color: red;
}
.grid {
	margin: 0 auto;
	-webkit-tap-highlight-color: rgba(0,0,0,0);
}
.cell {
	display: block;
	float: left;
	width: 80px;
	height: 80px;
	box-sizing: border-box;
	box-sizing: -webkit-border-box;
	-webkit-box-sizing: border-box;
}
.cell.path {
	background-color: #eee;
	border: solid 1px #000;
	border-color: #eee #ccc #ccc #eee;
	border-radius: 3px;
}
.cell.no-path {
	background-color: #aaa;
	border-color: #aaa;
}
.cell.end {
	cursor: pointer;
}
.cell.s:after {
	content: "";
	display: block;
	width: 70px;
	height: 70px;
	margin: 5% 0 0 5%;
	border-radius: 50%;
	opacity: 0.8;
}
.cell.s.t:after {
	margin: -5px 0 0 -5px;
}
.cell.t {
	border: solid 10px red;
}
.cell.s0:after {
	background-color: black;
}
.cell.t0 {
	border-color: black;
}
.cell.s1:after {
	background-color: orange;
}
.cell.t1 {
	border-color: orange;
}
.cell.s2:after {
	background-color: green;
}
.cell.t2 {
	border-color: green;
}
.cell.s3:after {
	background-color: lightblue;
}
.cell.t3 {
	border-color: lightblue;
}

.grid:after {
	content: "";
	display: block;
	clear: both;
	height: 0;
	visibility: 0;
}
</style>
</head>

<body>

<div class="grid" id="grid"></div>

<ul class=maps id=maps>
	<?foreach( $maps AS $i => $map ):?>
		<li><a class="goto" data-map="<?=$i?>" id="m<?=$i?>" href="#m<?=$i?>"><?=$i?></a></li>
	<?endforeach?>
</ul>

<!-- script src="http://code.jquery.com/jquery-latest.js"></script -->
<script src="simpledom.js"></script>
<script src="classlist.js"></script>
<script>
function extend(C, m) {
	for ( var x in m ) {
		C.prototype[x] = m[x];
	}
}
function each(list, cb) {
	//var list = this;
	if ( 'string' ==  typeof list || list instanceof Array ) {
		for ( var i=0, L=list.length; i<L; i++ ) {
			cb(list[i], i, list);
		}
	}
	else {
		for ( var i in list ) {
			if ( list.hasOwnProperty(i) ) {
				cb(list[i], i, list);
			}
		}
	}
}
extend(Array, {
	split: function(size) {
		var arr = [], L = Math.ceil(this.length / size);
		for ( var i=0; i<L; i++ ) {
			arr.push(this.slice(i*size, i*size+size));
		}
		return arr;
	},
	contains: function(el) {
		return -1 != this.indexOf(el);
	},
	addClass: function(c) {
		this.each(function(el) {
			el.classList.add(c);
		});
	},
	addClasses: function(c) {
		for ( var i=0, L=c.length; i<L; i++ ) {
			this.addClass(c[i]);
		}
	},
	removeClass: function(c) {
		this.each(function(el) {
			el.classList.remove(c);
		});
	},
	removeClasses: function(c) {
		for ( var i=0, L=c.length; i<L; i++ ) {
			this.removeClass(c[i]);
		}
	},
	clearClasses: function(only) {
		this.each(function(el) {
			el.className = only;
		});
	}
});
Array.prototype.each = Array.prototype.forEach;
extend(HTMLElement, {
	dirs: {
		up: [0, -1],
		right: [1, 0],
		down: [0, 1],
		left: [-1, 0]
	},
	neighbours: function() {
		var x = ~~this.data('x'), y = ~~this.data('y');
		var dirs = this.dirs, el, els = {};
		for ( var d in dirs ) {
			var nx = x+dirs[d][0], ny = y+dirs[d][1];
			if ( el = document.querySelector('.cell[data-x="' + nx + '"][data-y="' + ny + '"]') ) {
				if ( el.available() ) {
					els[d] = el;
				}
			}
		}
		return els;
	},
	on: function(t, c, f) {
		this.addEventListener(t, function(e) {
			if ( !c || e.target.classList.contains(c) ) {
				f.call(e.target, e);
			}
		}, false);
		return this;
	},
	next: function(dir) {
		var x = ~~this.data('x'), y = ~~this.data('y'), d = this.dirs[dir];
		var nx = x+d[0], ny = y+d[1];
		return grid.querySelector('.cell[data-x="' + nx + '"][data-y="' + ny + '"]');
	},
	all: function(q) {
		return this.querySelectorAll(q);
	},
	showNeighbours: function() {
		var nbs = [], els = this.neighbours();
		for ( var dir in els ) {
			var el = els[dir];
			el.classList.add('neighbour');
			el.classList.add(dir);
			el.godir = dir;
			nbs.push(el);
		}
		return nbs.length;
	},
	available: function() {
		return !this.classList.contains('had') && !this.classList.contains('not');
	},
	data: function(name) {
		return this.getAttribute('data-' + name);
	}
});
NodeList.prototype.each = Array.prototype.forEach;
NodeList.prototype.addClass = Array.prototype.addClass;
NodeList.prototype.removeClass = Array.prototype.removeClass;
NodeList.prototype.removeClasses = Array.prototype.removeClasses;
NodeList.prototype.clearClasses = Array.prototype.clearClasses;

function Snake(i) {
	this.i = i;
	this.cells = [];
	this.numCells = 0;
}
extend(Snake, {
	add: function(cell) {
		// add cell to end of snake
		this.cells.push(cell);
		this.numCells++;
	},
	first: function() {
		// return first cell
		return this.cells[0];
	},
	last: function() {
		// return last cell
		return this.cells[this.numCells-1];
	},
	drag: function(dragCell, targetCell) {
		// full drag logic incl DOM changes
		var direction = 'first' == this.position(dragCell) ? 'backward' : 'forward';
console.log('drag', direction);
		return this[direction](targetCell);
	},
	forward: function(newCell) {
		// remove first cell
		var oldCell = this.cells.shift();
		// add new cell after last
		this.cells.push(newCell);
		newCell.s = this.i;

		// reset classes
		this.resetClasses(oldCell);
	},
	backward: function(newCell) {
		// remove last cell
		var oldCell = this.cells.pop();
		// add new cell before first
		this.cells.unshift(newCell);
		newCell.s = this.i;

		// reset classes
		this.resetClasses(oldCell);
	},
	resetClasses: function(oldCell) {
		// reset ends
		this.cells.removeClasses(['end']);
		[this.first(), this.last()].addClasses(['end', 's', 's' + this.i]);
		// reset old cell
		[oldCell].removeClasses(['end', 's', 's' + this.i]);
		delete oldCell.s;
	},
	position: function(cell) {
		// return "start", "end" or false for cell position in snake
		if ( cell == this.first() ) {
			return 'first';
		}

		else if ( cell == this.last() ) {
			return 'last';
		}

		return false;
	},
	targets: function(targets) {
		var yes = true,
			snake = this.i;
		each(targets, function(target) {
console.log(target, target.s, snake);
			if ( target.s !== snake ) {
				yes = false;
			}
		});
		return yes;
	}
});

// config
var maps = <?=json_encode($maps)?>, mapsel = document.getElementById('maps');

function loadMap(m) {
	var map = maps[m];
	lastMap = m;
	location.hash = 'm' + m;

	// reset game
	cmap = [];
	snakes = [];
	targets = [];

	// clear grid
	grid.innerHTML = '';
	grid.style.width = (CELL_SIZE*map.map[0].length) + 'px';

	// rebuild grid
	each(map.map, function(row, y) {
		cmap.push([]);
		each(row, function(cell, x) {
			var c = document.createElement('a'),
				classes = ['cell'];

			classes.push('x' == cell ? 'no-path' : 'path');
			if ( 't' == cell ) {
				classes.push('t t' + map.target);
				targets.push(c);
			}

			c.setAttribute('data-x', x);
			c.setAttribute('data-y', y);
			c.setAttribute('class', classes.join(' '));

			c.x = x;
			c.y = y;

			cmap[y].push(c);
			grid.appendChild(c);
		});
	});

	// cache & hilite snakes
	each(map.snakes, function(coords, i) {
		snakes.push(new Snake(i));
		each(coords, function(c, j) {
			var el = cmap[c[1]][c[0]],
				end = 0 == j || coords.length-1 == j;

			el.className += ' s s' + i;
			end && (el.className += ' end');

			el.s = i;
			snakes[i].add(el);
		});
	});
}

// game
var grid = document.getElementById('grid'),
	cmap,
	lastMap = (location.hash.match(/^\#m(\d+)$/) || [0,1])[1],
	snakes,
	targets,
	dragCell;

// process
window.onload = function(e) {
	//grid.style.width = grid.style.height = Math.min(window.innerHeight, window.innerWidth, 400) + 'px';

	// load map
	loadMap(lastMap);

	// attach listeners

	// drag start
	var evType = 'ontouchstart' in document.documentElement ? 'touchstart' : 'mousedown';
	grid.on(evType, 'cell', function(e) {
		e.preventDefault();

		var cell = this,
			s = cell.s,
			end = cell.classList.contains('end') && false !== snakes[s].position(cell);

		if ( end ) {
			dragCell = cell;
//console.log('start drag', cell);
		}
	});

	// drag
	document.body.on('mousemove', 'cell', function(e) {
		if ( !dragCell ) {
			return;
		}

		var cell = this,
			open = cell.classList.contains('path') && !cell.classList.contains('s'),
			s = dragCell.s;
//console.log('drag', cell, s, open);

		if ( open ) {
			// move snake
			snakes[s].drag(dragCell, cell);

			if ( snakes[maps[lastMap].target].targets(targets) ) {
				dragCell = null;
				setTimeout("alert('You done it! Excellent.')", 60);
				return;
			}

			// prepare next move
			dragCell = cell;
		}
		else if ( dragCell != cell ) {
			dragCell = null;
		}
	});

	// drag end
	var evType = 'ontouchend' in document.documentElement ? 'touchend' : 'mouseup';
	document.body.on(evType, 0, function(e) {
console.log('drag end', e.target);
		dragCell = null;
	});
	grid.on('mouseleave', 0, function(e) {
console.log('drag end', e.target);
		//dragCell = null;
	});

	// maps links
	mapsel.on('click', 'goto', function(e) {
		e.preventDefault();
		loadMap(this.data('map'));
	});
}
</script>
</body>

</body>
