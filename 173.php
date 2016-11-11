<?php
// MAMONO

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mamono sweeper</title>
<style>
html, body {
	margin: 0;
	padding: 0;
	width: 100%;
	height: 100%;
}
html {
	background: black;
	font-family: sans-serif;
	font-size: 14px;
	line-height: 1.2;
	color: white;
}

body {
	padding: .3em;
	padding-top: 2.1em;
	padding-top: calc(.3em + 2 * .3em + 1.2em);
	min-width: -webkit-fit-content;
	min-width: -moz-fit-content;
	min-width: fit-content;
}

#stats {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	min-width: 100%;
	padding: .3em;
	background-color: black;
	box-shadow: 0 0 10px white;
}
.happening #stats {
	font-weight: bold;
	color: black;
	background-color: white;
}
#stats strong::after {
	content: ":";
}
#stats strong:not(:first-child) {
	margin-left: 10px;
}

table {
	border-spacing: 0;
	border: 0;
	font-size: inherit;
	border: solid 1px lightgreen;
	border-width: 0 1px 1px 0;
}
td {
	border: solid 1px black;
	border-top-color: lightgreen;
	border-left-color: lightgreen;
	width: 22px;
	height: 22px;
	background: black;
	color: white;
	font-weight: bold;
	text-align: center;
	padding: 0;
	line-height: 1;
	cursor: pointer;
}
td.monster { background: none center center no-repeat; }
td.monster-1 { background-image: url(/images/mamono/slime.png); }
td.monster-2 { background-image: url(/images/mamono/goblin.png); }
td.monster-3 { background-image: url(/images/mamono/lizard.png); }
td.monster-4 { background-image: url(/images/mamono/golem.png); }
td.monster-5 { background-image: url(/images/mamono/dragon.png); }
td.monster-6 { background-image: url(/images/mamono/demon.png); }
td.monster-7 { background-image: url(/images/mamono/ninja.png); }
td.monster-8 { background-image: url(/images/mamono/dragon_zombie.png); }
td.monster-9 { background-image: url(/images/mamono/satan.png); }

td.closed {
	background: none green;
}
td.monster.show-adjacents {
	background: none black;
	color: red;
}

td.closed span,
td.monster:not(.show-adjacents) span {
	visibility: hidden;
}

span.adjacents {
	pointer-events: none;
	display: block;
	width: 22px;
	height: 22px;
	line-height: 22px;
}

img.preload { visibility: hidden; position: absolute; }
</style>
</head>

<body onload="init()">

<div id="stats">
	<strong>HP</strong> <span id="hp">?</span>
	<strong>LV</strong> <span id="lv">?</span>
	<strong>EX</strong> <span id="ex">?</span>
	<strong>NX</strong> <span id="nx">?</span>
</div>

<table id="ms"></table>

<script>
var w = 50, h = 25, nextLevels = [10, 80, 112, 198], _adj = [[-1,-1], [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0]];

var $tbl, $stats, $hp, $lv, $nx, grid, monsters, happenId;

var hp = 20, level = 1, exp = 0, nextLevel = nextLevels[0];

function init() {
	// $stats = document.querySelector('#stats');
	$hp = document.querySelector('#hp');
	$lv = document.querySelector('#lv');
	$nx = document.querySelector('#nx');
	$ex = document.querySelector('#ex');
	$tbl = document.querySelector('#ms');

	// Max exp = 52 + 92 + 160 + 304 + 480 + 768 + 1152 + 1664 + 256
	monsters = _monsters([52, 46, 40, 36, 30, 24, 18, 13, 1]);
	grid = _build(monsters);

	_update();

	$tbl.onclick = function(e) {
		var td = e.target;
		if ( td.nodeName == 'TD' ) {
			if ( td.classList.contains('closed') ) {
				clickClosed(td);
			}
			else if ( td.classList.contains('monster') ) {
				toggleMonster(td);
			}
		}
	};

	$tbl.onmousedown = function(e) {
		e.preventDefault();
	};
}

function _update() {
	$hp.textContent = hp;
	$lv.textContent = level;
	$ex.textContent = exp;
	$nx.textContent = nextLevel;
}

function happening() {
	// Set
	document.body.classList.add('happening');

	// Unset
	happenId && clearTimeout(happenId);
	happenId = setTimeout(function() {
		document.body.classList.remove('happening');
	}, 1500);
}

function toggleMonster(td) {
	// console.log('toggleMonster');
	td.classList.toggle('show-adjacents');
}

function clickClosed(td) {
	// console.log('clickClosed');
	td.classList.remove('closed');
	td.dataset.title && (td.title = td.dataset.title);

	if ( td.monster ) {
		var pexp = Math.pow(2, td.monster-1);
		exp += pexp;
		nextLevel -= pexp;

		// Level up!
		if ( nextLevel <= 0 ) {
			happening();

			// How much for the next level?
			// 1 = 10
			// 2 = 80
			// 3 = 112
			// 4 = 198

			level++;
			nextLevel += /*nextLevels[level-1] ? nextLevels[level-1] :*/ 10 * Math.ceil(Math.pow(1.8, level));
		}
	}

	// Auch
	if ( td.monster > level ) {
		happening();

		// How does `hit` work in the actual Mamono?
		// 1 -> 2 = 2
		// 1 -> 3 = 6
		// 1 -> 4 = 12
		// 1 -> 5 >= 20
		// 2 -> 3 = 3
		// 2 -> 4 = 4
		// 2 -> 5 = 10
		// 2 -> 6 = 12
		// 2 -> 7 >= 20
		// 3 -> 4 = 4

		var diff = td.monster - level;
		// var hit = Math.factorial(diff) * 2;
		var hit = Math.pow(2, diff);
		hp -= hit;
		_update();
		if ( hp <= 0 ) {
			setTimeout(function() {
				alert('You dead!');
				location.reload();
			}, 100);
		}
	}

	// Open neighbours
	if ( td.adjacents == 0 ) {
		openAdjacents(td);
	}

	_update();
}

function openAdjacents(td) {
	var x = td.cellIndex, y = td.parentNode.sectionRowIndex;
	_adj.forEach(function(d) {
		var mx = x+d[0], my = y+d[1];
		if ( grid[my] && grid[my][mx] ) {
			var td = grid[my][mx];
			if ( td.classList.contains('closed') ) {
				clickClosed(td);
			}
		}
	});
}

function _monsters(source) {
	var indivs = [];
	source.forEach(function(num, lvl) {
		for ( var i=0; i<num; i++ ) {
			indivs.push(lvl);
		}
	});
	var R = w*h - indivs.length;
	for ( var i=0; i<R; i++ ) {
		indivs.push(-1);
	}
	indivs.sort(function() {
		return 0.5 - Math.random();
	});
	return indivs;
}

function _build(monsters) {
	var tr, td;
	var grid = [], line, mi = 0;
	for ( var y=0; y<h; y++ ) {
		tr = $tbl.insertRow($tbl.rows.length);
		line = [];
		for ( var x=0; x<w; x++ ) {
			var m = monsters[mi];

			td = tr.insertCell(tr.cells.length);
			line.push(td);
			td.monster = m+1;
			td.className = 'closed';
			td.adjacents = 0;
			td.innerHTML = '';
			td.dataset.title = td.monster ? 'Monster ' + td.monster : '';

			if ( td.monster ) {
				td.className += ' monster monster-' + td.monster;
				td.innerHTML = '<span class="adjacents">0</span>';
			}

			mi++;
		}
		grid.push(line);
	}

	grid.forEach(function(line, y) {
		line.forEach(function(td, x) {
			if ( td.monster ) {
				_adj.forEach(function(d) {
					var mx = x+d[0], my = y+d[1];
					try {
						grid[my][mx].adjacents += td.monster;
						// if ( grid[my][mx] && !grid[my][mx].monster ) {
							grid[my][mx].innerHTML = '<span class="adjacents">' + grid[my][mx].adjacents + '</span>';
						// }
					}
					catch (ex) {}
				});
			}
		})
	})

	return grid;
}

Math.factorial = function(n) {
	var f = 1;
	for ( var i=1; i<=n; i++ ) {
		f *= i;
	}
	return f;
};
</script>

<img class="preload" src="slime.png" />
<img class="preload" src="goblin.png" />
<img class="preload" src="lizard.png" />
<img class="preload" src="golem.png" />
<img class="preload" src="dragon.png" />
<img class="preload" src="demon.png" />
<img class="preload" src="ninja.png" />
<img class="preload" src="dragon_zombie.png" />
<img class="preload" src="satan.png" />

</body>

</html>

