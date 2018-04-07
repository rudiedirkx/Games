<?php
// FLOOD

$colors = ['red', 'green', 'blue', 'yellow'];

// header('Content-type: text/plain');

$width = 10;
$height = 10;
$map = [];

for ($y=0; $y < $height; $y++) {
	for ($x=0; $x < $width; $x++) {
		$map[$y][$x] = array_rand($colors);
	}
}

?>
<!doctype html>
<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>FLOOD</title>
	<style>
	td {
		padding: 0;
	}
	td > a {
		display: block;
		width: 30px;
		height: 30px;
	}
	</style>
</head>

<body>
<?php

echo '<table>';
foreach ($map as $y => $row) {
	echo '<tr>';
	foreach ($row as $x => $color) {
		echo '<td data-color="' . $colors[$color] . '" bgcolor="' . $colors[$color] . '"><a href="#"></a></td>';
	}
	echo '</tr>';
}
echo '</table>';

?>
<p>Turns: <code id="turns">0</code> | <a id="restart" href="#">restart</a></p>

<script>
var cell1 = document.querySelector('td');
var turns = 0;

document.querySelector('table').addEventListener('click', function(e) {
	if (e.target.nodeName == 'A') {
		e.preventDefault();
		var cell = e.target.parentNode;
		turns++;

		document.querySelector('#turns').textContent = turns;

		// alert(cell.bgColor);
		var color = cell.bgColor;

		// Find all adjacent `color` cells
		var collection = [];
		addAdjacents(collection, cell1, color);
		// alert(collection.length);
		for (var i=0; i<collection.length; i++) {
			var ncell = collection[i];
			ncell.bgColor = color;
		}

		if (collection.length == <?= $width * $height ?>) {
			setTimeout(function() {
				alert('You win!, in ' + turns + ' turns.');
				setTimeout(function() {
					location.reload();
				}, 600);
			}, 50);
		}
	}
});

document.querySelector('#restart').addEventListener('click', function(e) {
	e.preventDefault();

	[].forEach.call(document.querySelectorAll('td'), function(cell) {
		cell.bgColor = cell.dataset.color;
	});

	document.querySelector('#turns').textContent = String(turns = 0);
});

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
			if (adj.bgColor == cell.bgColor || adj.bgColor == color) {
				if (collection.indexOf(adj) == -1) {
					addAdjacents(collection, adj, color);
				}
			}
		}
	}
}
</script>
