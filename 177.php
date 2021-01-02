<?php
// SQUARESCAPE

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/177_levels.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Squarescape</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
body {
	background-color: #e7e7e7;
	margin: 0;
	padding: 0;
	font-family: sans-serif;
	touch-action: none;
	height: 100vh;
}
canvas {
	max-width: 100vw;
	max-height: 100vh;
}
p {
	margin-left: 1em;
}

#level-num {
	display: inline-block;
	width: 3.5em;
	text-align: right;
}

button {
	padding: 11px;
}
button:disabled {
	opacity: 0.75;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('squarescape.js') ?>"></script>
</head>

<body>

<p>Collect the gold coins. Escape to the green exit. Don't die on the red squares. Use the yellow brakes.</p>

<canvas></canvas>

<p>
	<button id="restart">Restart</button>
	|
	<button id="prev">&lt;&lt;</button>
	<strong id="level-num"></strong>
	<button id="next">&gt;&gt;</button>
	|
	<span id="stats-time"></span>
	|
	<span id="stats-moves"></span> moves
</p>

<script>
<? if (isset($_POST['import'])): ?>
	Squarescape.LEVELS = [<?= json_encode(json_decode($_POST['import'])) ?>];
<? else: ?>
	Squarescape.LEVELS = <?= json_encode($g_arrLevels) ?>;
<? endif ?>
objGame = new Squarescape($('canvas'));
objGame.listenControls();
objGame.loadLevel(<?= json_encode($_GET['level'] ?? 0) ?>);
objGame.startPainting();
</script>
</body>

</html>
