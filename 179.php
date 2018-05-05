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
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('pythagorea.js') ?>"></script>
<script src="<?= html_asset('pythagorea_levels.js') ?>"></script>
<style>
canvas {
	background-color: #eee;
	touch-action: none;
}
#level-num {
	display: inline-block;
	width: 1.2em;
	text-align: center;
}
button:disabled {
	opacity: 0.75;
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
	<strong id="level-num"></strong>
	<button id="next">&gt;&gt;</button>
</p>

<script>
var objGame = new Pythagorea(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();
objGame.loadLevel(0);
</script>
