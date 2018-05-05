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
<script>window.onerror = function(e) { alert(e); };</script>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('laser.js') ?>"></script>
<style>
canvas {
	background: #aaa none;
	background: repeating-linear-gradient(
		120deg,
		#888,
		#888 10px,
		#999 10px,
		#999 20px
	);
	touch-action: none;

	image-rendering: optimizeSpeed;
	image-rendering: -webkit-optimize-contrast;
	image-rendering: pixelated;
}
</style>
</head>

<body class="laser">

<canvas></canvas>

<script>
Laser.levels = <?= json_encode($g_arrLevels) ?>;

var objGame = new Laser(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();
objGame.loadLevel(0);
</script>