<?php
// PYTHAGOREA

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>PYTHAGOREA</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<link rel="stylesheet" href="<?= html_asset('pythagorea.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('pythagorea.js') ?>"></script>
<style>
canvas {
	background-color: #eee;
	touch-action: none;
}
</style>
</head>

<body class="pythagorea">

<p id="level-desc">?</p>

<canvas></canvas>

<p>
	<button id="undo">Undo</button>
	|
	<button id="prev">&lt;&lt;</button>
	<button id="next">&gt;&gt;</button>
</p>

<script>
var objGame = new Pythagorea(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();
objGame.loadLevel(0);
</script>
