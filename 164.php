<?php

$maps = array(1 =>
	array(
		array(3, 5),
	),
	array(
		array(0, 5),
		array(5, 5),
	),
	array(
		array(0, 0),
		array(5, 5),
	),
	array(
		array(0, 0),
		array(1, 0),
		array(4, 5),
		array(5, 5),
	),
);

$g_w = $g_h = 6;

?>
<!doctype html>
<html>

<head>
<title>Machinarium I</title>
<style>
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
.maps a:active,
.maps a:focus {
	color: red;
}
.grid {
	width: 300px;
	margin: 0 auto;
}
.cell {
	display: block;
	float: left;
	width: 48px;
	height: 48px;
	background: #ddd;
	border: solid 1px #000;
	border-color: #eee #ccc #ccc #eee;
	border-radius: 3px;
	cursor: pointer;
}
.cell.had {
	background: yellow;
}
.cell.not {
	background: red;
}
.cell.start {
	background: green;
}
.cell.neighbour:after {
	content: "";
	display: block;
	width: 100%;
	height: 100%;
	background: url(http://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Right_arrow.svg/434px-Right_arrow.svg.png) center center no-repeat;
	background-size: cover;
}
.cell.left:after {
	-webkit-transform: rotate(180deg);
	-moz-transform: rotate(180deg);
	-o-transform: rotate(180deg);
}
.cell.down:after {
	-webkit-transform: rotate(90deg);
	-moz-transform: rotate(90deg);
	-o-transform: rotate(90deg);
}
.cell.up:after {
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

<ul class=maps id=maps></ul>

<div class="grid" id="grid">
<?php
for ( $y=0; $y<$g_h; $y++ ) {
	echo '<div class="row">';
	for ( $x=0; $x<$g_w; $x++ ) {
		echo '<a data-x="'.$x.'" data-y="'.$y.'" class="cell"></a>';
	}
	echo '</div>';
}
?>
</div>

<!-- script src="http://code.jquery.com/jquery-latest.js"></script -->
<script src="https://raw.github.com/rudiedirkx/simpledom/master/simpledom.js"></script>
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
		var x = ~~this.dataset.x, y = ~~this.dataset.y;
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
		var x = ~~this.dataset.x, y = ~~this.dataset.y, d = this.dirs[dir];
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
	}
});
NodeList.prototype.each = Array.prototype.forEach;
NodeList.prototype.addClass = Array.prototype.addClass;
NodeList.prototype.removeClass = Array.prototype.removeClass;
NodeList.prototype.removeClasses = Array.prototype.removeClasses;
NodeList.prototype.clearClasses = Array.prototype.clearClasses;

// config
var maps = <?=json_encode($maps)?>, mapsel = document.getElementById('maps');
for ( var m in maps ) {
	simple.last(mapsel, simple('li', [simple('a', {"data-map": m, "href": '#'}, {"click": function(e) {
		e.preventDefault();
		loadMap(this.dataset.map);
	}}, ''+m)]));
}

function loadMap(m) {
	var map = maps[m];
	lastMap = m;

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
	lastMap = 1,
	started = false,
	hasReset = false,
	lastClick,
	lastHilite;

// process
//window.onload = function(e) {
	// load map
	loadMap(lastMap);

	// attach listeners
	grid.on('click', 'cell', function(e) {
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
//}
</script>
</body>

</body>
