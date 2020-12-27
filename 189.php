<?php
// 0h n0

require __DIR__ . '/inc.bootstrap.php';

$grids = [
	[
		' 6      7',
		'   6 67x ',
		' 3  3   8',
		'1 97 5   ',
		'    x    ',
		'  9    5 ',
		' 5       ',
		'     3   ',
		'549 7x3x ',
	],
	[
		'2   ',
		'1  2',
		' 33 ',
		'x1  ',
	],
	[
		'2 3  ',
		'4    ',
		'  55x',
		'xx  1',
		'  2  ',
	],
	[
		'     7 x',
		' x  7 7 ',
		'   33   ',
		'4       ',
		' 885 x 5',
		'    xx 5',
		'3    4 7',
		'4    3  ',
	],
	[
		'x5353xx ',
		'  x x2 5',
		'4535  24',
		'1xxx22x3',
		' x  3 x ',
		' 3 xxx x',
		' 3x   54',
		' 533 34 ',
	],
];
$selected = isset($grids[$_GET['demo'] ?? 0]) ? ($_GET['demo'] ?? 0) : 0;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>0h n0</title>
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
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('ohno.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<p>
	<button id="cheat">Cheat</button>
	<button id="new">New game</button>
	<? foreach ($grids as $i => $grid): ?>
		| <a href="?demo=<?= $i ?>"><?= $i ?></a>
	<? endforeach ?>
</p>
<p>
	Analyze: <input type="file" />
</p>

<script>
objGame = new Ohno($('canvas'));
objGame.clickCheat = <?= json_encode(is_local()) ?>;
objGame.listenControls();
objGame.importMap(<?= strlen($grids[$selected][0]) ?>, <?= json_encode(str_replace(' ', '_', implode('', $grids[$selected]))) ?>);
objGame.startPainting();
</script>
</body>

</html>
