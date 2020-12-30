<?php
// GRIDLOCK

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/188_levels.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Gridlock</title>
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
<script src="<?= html_asset('gridlock.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<p>
	<button id="restart">Restart</button>
	<span id="level-links"></span>
</p>

<script>
<? if (isset($_POST['import'])): ?>
	Gridlock.LEVELS = [<?= json_encode(json_decode($_POST['import'])) ?>];
<? else: ?>
	Gridlock.LEVELS = <?= json_encode($g_arrLevels) ?>;
<? endif ?>
objGame = new Gridlock($('canvas'));
objGame.listenControls();
objGame.loadLevel(<?= json_encode($_GET['level'] ?? 0) ?>);
objGame.startPainting();
</script>
</body>

</html>
