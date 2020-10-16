<?php
// MAMONO

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mamono sweeper</title>
<style>
* {
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}
html, body {
	height: 100%;
	width: 100%;
	overflow: hidden;
}
html {
	background: black;
	font-family: sans-serif;
	font-size: 14px;
	color: white;
	user-select: none;

	--stats-height: 30px;
}

#stats {
	height: var(--stats-height);
	line-height: var(--stats-height);
	background-color: black;
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

#ms {
	height: calc(100% - var(--stats-height));
	width: 100vw;
	overflow: auto;
}
#ms .padding {
	padding: 50px;
	width: fit-content;
}
table {
	border-spacing: 0;
	font-size: inherit;
	border: solid 0 black;
	border-width: 0 5px 5px 3px;
	width: calc(var(--w) * 23px);
	height: calc(var(--h) * 23px);
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
td[data-monster] { background: none center center no-repeat; }
td[data-monster="1"] { background-image: url(/images/mamono/slime.png); }
td[data-monster="2"] { background-image: url(/images/mamono/goblin.png); }
td[data-monster="3"] { background-image: url(/images/mamono/lizard.png); }
td[data-monster="4"] { background-image: url(/images/mamono/golem.png); }
td[data-monster="5"] { background-image: url(/images/mamono/dragon.png); }
td[data-monster="6"] { background-image: url(/images/mamono/demon.png); }
td[data-monster="7"] { background-image: url(/images/mamono/ninja.png); }
td[data-monster="8"] { background-image: url(/images/mamono/dragon_zombie.png); }
td[data-monster="9"] { background-image: url(/images/mamono/satan.png); }

td.closed {
	background: none green;
}
td[data-monster].show-adjacents {
	background: none black;
	color: red;
}
td.closed span,
td[data-monster]:not(.show-adjacents) span {
	visibility: hidden;
}

span.adjacents,
span.empty {
	pointer-events: none;
	display: block;
	width: 22px;
	height: 22px;
	line-height: 22px;
}

img.preload { visibility: hidden; position: absolute; }
</style>
<? include 'tpl.onerror.php' ?>
</head>

<body xonload="init()">

<div id="stats">
	<strong>HP</strong> <span id="hp">?</span>
	<strong>LV</strong> <span id="lv">?</span>
	<strong>EX</strong> <span id="ex">?</span>
	<strong>NX</strong> <span id="nx">?</span>
</div>

<div id="ms">
	<div class="padding">
		<table></table>
	</div>
</div>

<script type="disabled">
var w = 50;
var h = 25;
var numMonsters = [52, 46, 40, 36, 30, 24, 18, 13, 1];
var nextLevels = [10, 90, 202, 400, 1072];
var _adj = [[-1,-1], [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0]];

var $tbl, $stats, $hp, $lv, $nx, grid, monsters, happenId;

var hp = 30;
var level = 0;
var exp = 0;
var nextLevel = nextLevels[0];

function init() {
	// $stats = document.querySelector('#stats');
	$hp = document.querySelector('#hp');
	$lv = document.querySelector('#lv');
	$nx = document.querySelector('#nx');
	$ex = document.querySelector('#ex');
	$tbl = document.querySelector('#ms table');

	monsters = _monsters(numMonsters);
	_build(monsters);
	level = 1;
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
console.log(td);
// 	if ( level == 0 ) {
// 		const y = td.parentNode.rowIndex;
// 		const x = td.cellIndex;

// // debugger;
// 		const i = y * h + x;
// 		while (!monsters || monsters[i] != -1) {
// 			monsters = _monsters(numMonsters);
// 		}
// 		_build(monsters);

// 		level = 1;
// 		_update();
// 	}

	// console.log('clickClosed');
	td.classList.remove('closed');
	td.dataset.title && (td.title = td.dataset.title);

// debugger;
	if ( td.monster ) {
		var pexp = Math.pow(2, td.monster-1);
		exp += pexp;
		nextLevel -= pexp;

		// Level up!
		if ( nextLevel <= 0 ) {
			happening();

			level++;
			nextLevel = nextLevels[level] + nextLevel;
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

		// var diff = td.monster - level;
		// // var hit = Math.factorial(diff) * 2;
		// var hit = Math.pow(2, diff);
		// hp -= hit;
		// _update();
		// if ( hp <= 0 ) {
			setTimeout(function() {
				alert('You dead!');
				// location.reload();
			}, 100);
		// }
	}

	// Open neighbours
	if ( td.adjacents == 0 ) {
		openAdjacents(td);
	}

	_update();
}

function openAdjacents(td) {
	var x = td.cellIndex;
	var y = td.parentNode.rowIndex;
// debugger;
	_adj.forEach(function(d) {
		var mx = x+d[0];
		var my = y+d[1];
		if ( grid[my] && grid[my][mx] ) {
			var nb = grid[my][mx];
			if ( nb.classList.contains('closed') ) {
				clickClosed(nb);
			}
		}
	});
}

function _monsters(source) {
	var indivs = [];
	source.forEach(function(num, lvl) {
		for ( var i=0; i<num; i++ ) {
			indivs.push(lvl + 1);
		}
	});
	var R = w*h - indivs.length;
	for ( var i=0; i<R; i++ ) {
		indivs.push(0);
	}
	indivs.sort(function() {
		return 0.5 - Math.random();
	});
	return indivs;
}

function _build(monsters) {
console.log(monsters);
	$tbl.innerHTML = '';
	grid = [];

	var tr, td;
	var line, mi = 0;
	for ( var y=0; y<h; y++ ) {
		tr = $tbl.insertRow();
		grid.push(line = []);

		for ( var x=0; x<w; x++ ) {
			td = tr.insertCell();
			line.push(td);

			var m = monsters[mi] || 0;

			td.monster = m;
			td.adjacents = 0;
			td.dataset.title = td.monster ? 'Monster ' + td.monster : '';

			if ( td.monster ) {
				td.className = 'closed monster monster-' + td.monster;
				td.innerHTML = '<span class="adjacents">0</span>';
			}
			else {
				td.className = 'closed';
				td.innerHTML = '<span class="empty"></span>';
			}

			mi++;
		}
	}

// debugger;
	grid.forEach(function(line, y) {
		line.forEach(function(td, x) {
			if ( td.monster ) {
				_adj.forEach(function(d) {
					var mx = x+d[0];
					var my = y+d[1];
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
}

Math.factorial = function(n) {
	var f = 1;
	for ( var i=1; i<=n; i++ ) {
		f *= i;
	}
	return f;
};
</script>

<img class="preload" src="/images/mamono/slime.png" />
<img class="preload" src="/images/mamono/goblin.png" />
<img class="preload" src="/images/mamono/lizard.png" />
<img class="preload" src="/images/mamono/golem.png" />
<img class="preload" src="/images/mamono/dragon.png" />
<img class="preload" src="/images/mamono/demon.png" />
<img class="preload" src="/images/mamono/ninja.png" />
<img class="preload" src="/images/mamono/dragon_zombie.png" />
<img class="preload" src="/images/mamono/satan.png" />

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('mamono.js') ?>"></script>
<script>
objGame = new Mamono($('#ms table'));
objGame.createMap('normal');
objGame.listenControls();
</script>
</body>

</html>

