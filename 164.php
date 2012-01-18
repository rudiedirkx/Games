<?php

$maps = array(1 =>
	array(),
	array(
		array(2, 4),
	),
	array(
		array(4, 4),
		array(3, 1),
	),
	array(
		array(2, 2),
		array(4, 2),
	),
	array(
		array(1, 1),
		array(3, 1),
		array(1, 3),
		array(2, 3),
		array(4, 3),
		array(4, 4),
	),
	array(
		array(4, 0),
		array(3, 2),
		array(0, 3),
		array(2, 4),
	),
	array(
		array(2, 0),
		array(3, 0),
		array(4, 0),
		array(0, 4),
		array(1, 4),
	),
);

$g_w = $g_h = 5;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width" />
<title>Machinarium I</title>
<style>
html, body {
	margin: 0;
	padding: 0;
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
	width: 300px;
	height: 300px;
	margin: 0 auto;
	-webkit-tap-highlight-color: rgba(0,0,0,0);
}
.cell {
	display: block;
	float: left;
	width: 20%;
	height: 20%;
	box-sizing: border-box;
	box-sizing: -webkit-border-box;
	-webkit-box-sizing: border-box;
	background-color: #ddd;
	border: solid 1px #000;
	border-color: #eee #ccc #ccc #eee;
	border-radius: 3px;
	cursor: pointer;
}
.cell.had {
	background-color: yellow;
}
.cell.not {
	background-color: red;
}
.cell.start {
	background-color: green;
}
.cell.end {
	background-color: blue;
}
.cell.neighbour {
	background-image: url(images/right.png);
	background-position: center center;
	background-repeat: no-repeat;
	background-size: cover;
	-webkit-background-size: cover;
	border-color: #ddd;
}
.neighbour.left {
	-webkit-transform: rotate(180deg);
	-moz-transform: rotate(180deg);
	-o-transform: rotate(180deg);
}
.neighbour.down {
	-webkit-transform: rotate(90deg);
	-moz-transform: rotate(90deg);
	-o-transform: rotate(90deg);
}
.neighbour.up {
	-webkit-transform: rotate(-90deg);
	-moz-transform: rotate(-90deg);
	-o-transform: rotate(-90deg);
}
.row:after {
	content: "";
	display: block;
	clear: both;
	height: 0;
	visibility: 0;
}
</style>
</head>

<body>

<div class="grid" id="grid">
<?php
for ( $y=0; $y<$g_h; $y++ ) {
	//echo '<div class="row">';
	for ( $x=0; $x<$g_w; $x++ ) {
		echo '<a data-x="'.$x.'" data-y="'.$y.'" class="cell"></a>';
	}
	//echo '</div>';
}
?>
</div>

<ul class=maps id=maps></ul>

<!-- script src="http://code.jquery.com/jquery-latest.js"></script -->
<script src="simpledom.js"></script>
<script src="classlist.js"></script>
<script>
function extend(C, m) {
	for ( var x in m ) {
		C.prototype[x] = m[x];
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
			if ( e.target.classList.contains(c) ) {
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

// config
var maps = <?=json_encode($maps)?>, mapsel = document.getElementById('maps');

function loadMap(m) {
	var map = maps[m];
	lastMap = m;
	location.hash = 'm' + m;

	// reset grid
	grid.all('.cell').clearClasses('cell');

	// reset game
	started = false;
	lastClick = lastHilite = undefined;

	// paint grid
	for ( var i=0, L=map.length; i<L; i++ ) {
		var not = map[i],
			x = not[0],
			y = not[1],
			el = grid.querySelector('.cell[data-x="' + x + '"][data-y="' + y + '"]');
		el.classList.add('not');
	}
}

// game
var grid = document.getElementById('grid'),
	lastMap = (location.hash.match(/^\#m(\d+)$/) || [0,1])[1],
	started = false,
	hasReset = false,
	lastClick,
	lastHilite;

// process
window.onload = function(e) {
	grid.style.width = grid.style.height = Math.min(window.innerHeight, window.innerWidth, 400) + 'px';

	// load map
	loadMap(lastMap);

	// attach listeners
	var evType = 'ontouchstart' in document.documentElement ? 'touchstart' : 'mousedown';
	grid.on(evType, 'cell', function(e) {
		e.preventDefault();

		if ( !started ) {
			// this = start
			this.classList.add('had');
			this.classList.add('start');
			started = true;

			// this = clicked = hilited
			lastClicked = lastHilite = this;

			// show neighbours
			this.showNeighbours();
		}

		else if ( this.classList.contains('start') ) {
			// notify once
			if ( !hasReset ) {
				hasReset = true;
				//alert('Resetting...');
			}

			// reset
			loadMap(lastMap);
		}

		else if ( this.classList.contains('neighbour') ) {
			// this = lastClicked
			lastClicked = this;

			// hide neighbours
			grid.all('.neighbour').removeClasses(['neighbour', 'left', 'right', 'up', 'down']);

			// keep going that direction
			var dir = this.godir, lel, el = this;
			while ( 1 ) {
				// hilite
				el.classList.add('had');

				// next
				lel = el
				el = el.next(dir);
				if ( !el || !el.available() ) {
					// last -- show neighbours
					if ( !lel.showNeighbours() ) {
						lel.classList.add('end');
						var win = !grid.all('.cell:not(.had):not(.not)').length;
						if ( win ) {
							alert("YOU WIN!\n\nGo for next, you rule!");
						}
						else {
							alert("FAIL!\n\nYou can try again, but you suck...");
							loadMap(lastMap);
						}
					}
					break;
				}
			}
		}
	});

	// maps links
	for ( var m in maps ) {
		simple.last(mapsel, simple('li', [simple('a', {"id": 'm'+m, "data-map": m, "href": '#'}, {"click": function(e) {
			e.preventDefault();
			loadMap(this.data('map'));
		}}, ''+m)]));
	}
}
</script>
</body>

</body>
