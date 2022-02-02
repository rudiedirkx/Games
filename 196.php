<?php
// LABYRINTH
// https://www.youtube.com/watch?v=6ECL-bH_GAw

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Labyrinth</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
html, body {
	margin: 0;
	padding: 8;
}
canvas {
	display: block;
	background-color: #eee;
	max-width: 100vw;
	max-height: 100vh;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('labyrinth.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<script>
objGame = new Labyrinth($('canvas'));
objGame.startGame();
objGame.listenControls();
objGame.startPainting();
</script>
</body>

</html>
