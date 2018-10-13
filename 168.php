<?php
// FLOOD

require __DIR__ . '/inc.bootstrap.php';

$colors = ['red', 'green', 'blue', 'yellow'];
$size = 10;

?>
<!doctype html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>FLOOD</title>
	<style>
	td {
		padding: 0;
		width: 30px;
		height: 30px;
		background-color: var(--color, #eee);
	}
	</style>
</head>

<body>

<table><?= str_repeat('<tr>' . str_repeat('<td></td>', $size) . '</tr>', $size) ?></table>

<p>
	Turns: <code id="turns">0</code> |
	<a id="restart" href="#">restart</a> |
	<a id="start" href="#">new game</a>
</p>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
var COLORS = <?= json_encode($colors) ?>;

var cell1 = $('td');
var turns = 0;
var start = Date.now();

$('table').on('click', 'td', function(e) {
	var cell = this;

	turns++;
	$('#turns').textContent = turns;

	var color = getColor(cell);

	var collection = [];
	addAdjacents(collection, cell1, color);
	collection.forEach(cell => setColor(cell, color));

	if (collection.length == <?= $size * $size ?>) {
		setTimeout(function() {
			alert('You win!, in ' + turns + ' turns.');
			setTimeout(function() {
				startGame();
			}, 600);
		}, 50);

		Game.saveScore({
			time: Math.round((Date.now() - start) / 1000),
			moves: turns,
			level: <?= $size ?>,
		});
	}
});

$('#restart').addEventListener('click', function(e) {
	e.preventDefault();

	$$('td').forEach(cell => setColor(cell, cell.dataset.color));

	$('#turns').textContent = String(turns = 0);
});

$('#start').addEventListener('click', function(e) {
	e.preventDefault();

	startGame();
});

function startGame() {
	$$('td').forEach(cell => {
		cell.dataset.color = setColor(cell, COLORS[parseInt(Math.random() * COLORS.length)]);
	});
	$('#turns').textContent = String(turns = 0);
}

function getColor(cell) {
	return cell.style.getPropertyValue('--color');
}

function setColor(cell, color) {
	return cell.style.setProperty('--color', color), color;
}

function addAdjacents(collection, cell, color) {
	var x = cell.cellIndex;
	var y = cell.parentNode.sectionRowIndex;
	var grid = cell.parentNode.parentNode;

	collection.push(cell);

	var dirs = [[0, -1], [1, 0], [0, 1], [-1, 0]];
	for (var i=0; i<dirs.length; i++) {
		var dir = dirs[i];
		if (grid.rows[y + dir[1]] && grid.rows[y + dir[1]].cells[x + dir[0]]) {
			var adj = grid.rows[y + dir[1]].cells[x + dir[0]];
			if (getColor(adj) == getColor(cell) || getColor(adj) == color) {
				if (collection.indexOf(adj) == -1) {
					addAdjacents(collection, adj, color);
				}
			}
		}
	}
}

setTimeout(startGame);
</script>
