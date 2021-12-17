<?php
// INDEX concept

require __DIR__ . '/inc.bootstrap.php';

$tiles = require __DIR__ . '/inc.index.php';
// var_dump(count($tiles));
$tiles = array_values(array_unique(array_column($tiles, 0)));
// var_dump(count($tiles));
// print_r($tiles);
$thumbs = get_thumbs_positions();
// print_r($thumbs);

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>INDEX</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
* {
	margin: 0;
	padding: 0;
}
table {
	width: 100vw;
	height: 100vh;
	border-collapse: collapse;
}
td {
	border: solid 0px black;
}
</style>
</head>

<body>
<table></table>

<script>
const tbl = document.querySelector('table');
var layout = '';

function randomColor() {
	return '#' + ('000000' + (Math.random()*0xFFFFFF<<0).toString(16)).slice(-6);
}

function getGrid(tiles, width, height) {
	const ratio = width / height;
	const y0 = Math.sqrt(tiles / ratio);
// console.log(y0);

	const y1 = Math.floor(y0);
	const tiles1 = Math.ceil(tiles / y1) * y1;
// console.log(tiles1);
	const y2 = Math.ceil(y0);
	const tiles2 = Math.ceil(tiles / y2) * y2;
// console.log(tiles2);

	const h = tiles1 < tiles2 ? y1 : y2;
	const w = tiles1 < tiles2 ? tiles1 / y1 : tiles2 / y2;
	return [w, h];
}

function applyGrid() {
	const [w, h] = getGrid(<?= count($tiles) ?>, innerWidth, innerHeight);
	const newLayout = `${w} x ${h} = ${w*h}`;
	if (layout == newLayout) return;
	layout = newLayout;
console.log(layout);

	let html = '';
	for ( let y = 0; y < h; y++ ) {
		html += '<tr>';
		for ( let x = 0; x < w; x++ ) {
			html += `<td bgcolor="${randomColor()}"></td>`;
		}
		html += '</tr>';
	}
	tbl.innerHTML = html;
}

window.addEventListener('resize', e => applyGrid());
applyGrid();
</script>
</body>

</html>
