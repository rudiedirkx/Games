<?php
// Hitomezashi Stitch

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
	border: solid 1px black;
}
</style>
</head>

<body>
<table></table>

<script>
const tbl = document.querySelector('table');

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
console.log(w, h);
	return [w, h];
}

function applyGrid() {
	const [w, h] = getGrid(89, innerWidth, innerHeight);
	let html = '';
	for ( let y = 0; y < h; y++ ) {
		html += '<tr>';
		for ( let x = 0; x < w; x++ ) {
			html += '<td></td>';
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
