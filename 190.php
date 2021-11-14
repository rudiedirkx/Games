<?php
// Bridges

require __DIR__ . '/inc.bootstrap.php';

$grids = [
	[
		'1 2  3',
		' 1 2',
		'3 2  3',
		' 2 4',
		'     3',
		'5  5',
		' 1   3',
		'2',
		' 2 4 2',
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
	[
		'  2 3  3',
		'4  4 2',
		' 2  3  4',
		'4 2   2',
		' 1 3 3',
		'4   2 1',
		' 1 4 4 4',
		'    1 3',
		'2 3    3',
		'   3  5',
		'       2',
		'1 5 3 3',
	],
	[
		'2 2 3 2',
		' 1 3 4 2',
		'',
		'4 4  4',
		'       3',
		'2  2 4',
		'    2  3',
		'4 3  1',
		' 3  6  2',
		'2    1',
		'  1 3',
		' 3 3 4 1',
	],
	[
		'2 3 4 3  3',
		' 3   3  3',
		'3  2  3  3',
		' 3   2  3',
		'    2 4  1',
		'3 4  2  3',
		'         1',
		'1 5 5 4 4',
		'         3',
		'     3  5',
		'4 4      3',
		'      3 5',
		' 4  6    3',
		'2    1  1',
		' 2  2 3  4',
	],
	[
		' 2  5 5  3',
		'     2  1',
		'3 4 4    4',
		'   2  3 4',
		'    3    3',
		'   3 3  5',
		'3   3  2 3',
		'   3 3  4',
		'  3   2  3',
		'4   3  2',
		'  1   4 4',
		'5  3 3   3',
		'        3',
		'3 3 2 1  2',
		' 2 3 3  2',
	],
	[
		' 4 3 4 3 3',
		'  3 3',
		'1     1 3',
		' 3   5 2 1',
		'2   1 1 4',
		'  4  4 1',
		' 1       1',
		'3 6 3 3 2',
		'',
		'2 6  3 4 3',
		'',
		'  4 3  1',
		'3    2   3',
		'  1',
		'2   2  1 3',
	],
	[
		' 1 1  3  2 4',
		'1       3 1',
		' 4  2       2',
		'3 2   5 6  3',
		' 4 3 3 1  1 5',
		'  1     5  1',
		'4',
		' 3   3  6   4',
		'          1',
		'3 3 3   4   3',
		'           1',
		' 1 4  2     3',
		'3 2 2   5 3',
		' 1 3        2',
		'3   6 4 4  4',
		'            3',
		'1 3  3 3 3 3',
		' 2  3 3 4 3 3',
	],
	[
		'4 4  2',
		'',
		'1 1  2  1',
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
body {
	background-color: #eee;
	margin: 0;
	padding: 0;
	font-family: sans-serif;
}
canvas {
	background-color: #eee;
	max-width: 100vw;
	max-height: 100vh;
}
p {
	margin-left: 1em;
}

body.editing,
body.editing canvas {
	background-color: pink;
}
body.editing #edit {
	font-weight: bold;
}
body.editing #cheat,
body:not(.editing) #clear {
	display: none;
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
	<button id="edit">Edit</button>
	<button id="clear">Clear</button>
	<button id="restart">Restart</button>
	<? foreach ($grids as $i => $grid): ?>
		| <a href="?demo=<?= $i ?>"><?= $i ?></a>
	<? endforeach ?>
</p>

<script>
objGame = new Bridges($('canvas'));
objGame.clickCheat = <?= json_encode(is_local()) ?>;
objGame.startPainting();
objGame.listenControls();
setTimeout(function() {
	objGame.createMap(<?= json_encode($grids[$_GET['demo'] ?? 0] ?? $grids[0]) ?>);
});
</script>
</body>

</html>
