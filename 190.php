<?php
// Bridges

require __DIR__ . '/inc.bootstrap.php';

$grids = [
	[
		' 1 2 1',
		'',
		'2  6 4',
		'',
		' 3 5',
		'3 2  3',
		'   1',
		'2 3  3',
		' 3 2',
	],
	[
		' 3 2 1',
		'  2    2',
		'1  2 4',
		' 2',
		'4 6  4 4',
		'   3  2',
		'3   3  5',
		'  2',
		'4  3 1 4',
		' 3  6 1',
		'1    2 3',
		' 1  3 1',
	],
	[
		'  1',
		'',
		'6 4',
		'',
		'  3',
		'     1  ',
		'',
		'   6 5 1',
		'',
		'     3',
	],
	[
		'1 4 4 3',
		'   1   1',
		'3 4   3',
		'    1  3',
		'3  3  4',
		'  4 3  3',
		'5  2  1',
		' 3  6  4',
		'2     2',
		'  2 4',
		' 3 2   1',
		'1 3 4 3',
	],
];

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Bridges</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
canvas {
	background-color: #eee;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('bridges.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<p>
	<button id="cheat">Cheat</button>
	<button id="new">New game</button>
</p>

<script>
objGame = new Bridges($('canvas'));
objGame.startPainting();
objGame.listenControls();
setTimeout(function() {
	objGame.createMap(<?= json_encode($grids[$_GET['demo'] ?? 0]) ?>);
});
</script>
</body>

</html>
