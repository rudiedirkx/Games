<?php
// MONDRIAN PUZZLE

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('mondrian.js') ?>"></script>
<title>MONDRIAN PUZZLE</title>
<style>
html, body {
	margin: 0;
	padding: 0;
}
body {
	margin: 5px;
}
canvas {
	outline: solid 1px black;
	max-width: calc(100vw - 10px);
	max-height: calc(100vh - 10px);
	max-width: 100%;
	touch-action: none;
}
.complete {
	font-weight: bold;
	color: green;
}
</style>
</head>

<body>
<canvas></canvas>

<p>
	Click &amp; drag rectangles to fill the board.
	Size: <select id="size"><?= do_html_options(array_combine(range(4, 9), range(4, 9))) ?></select>
	<button id="reset">Reset</button>
	<button id="undo">Undo</button>
</p>

<p>Score: <code id="score">?</code> (the lower the better)</p>

<script>
objGame = new Mondrian(document.querySelector('canvas'));
objGame.listenControls();
objGame.startGame();
objGame.startPainting();
</script>
</body>

</html>
