<?php
// LASER

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/178_levels.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>LASER</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('laser.js') ?>"></script>
<style>
body {
	font-family: sans-serif;
}
canvas {

	touch-action: none;
	user-select: none;

	background: #aaa none;
	background: repeating-linear-gradient(
		130deg,
		#6c6c6c,
		#6c6c6c 10px,
		#777 10px,
		#777 20px
	);

	image-rendering: optimizeSpeed;
	image-rendering: -webkit-optimize-contrast;
	image-rendering: pixelated;

	max-width: 100%;
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
</head>

<body class="laser">

<canvas></canvas>

<p>
	<button id="prev">&lt;&lt;</button>
	<strong id="level-num"></strong>
	<button id="next">&gt;&gt;</button>
</p>

<script>
var objGame = new Laser(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();
<? if ( isset($_POST['import']) ): ?>
	Laser.levels = <?= json_encode([json_decode($_POST['import'])]) ?>;
<? else: ?>
	Laser.levels = <?= json_encode($g_arrLevels) ?>;
<? endif ?>
objGame.loadLevel(0);
</script>
