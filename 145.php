<?php
// LINX

require __DIR__ . '/inc.bootstrap.php';
$g_arrLevels = require __DIR__ . '/145_levels.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Linx</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
body {
	margin: 0;
	padding: 0;
	font-family: sans-serif;
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
button:disabled {
	opacity: 0.75;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('linx.js') ?>"></script>
</head>

<body>

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
	Linx.LEVELS = [<?= json_encode(json_decode($_POST['import'])) ?>];
<? else: ?>
	Linx.LEVELS = <?= json_encode($g_arrLevels) ?>;
<? endif ?>
objGame = new Linx($('canvas'));
objGame.listenControls();
objGame.loadLevel(<?= (int) ($_GET['level'] ?? 0) ?>);
objGame.startPainting();
</script>
</body>

</html>
