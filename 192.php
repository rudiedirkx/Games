<?php
// Hitomezashi Stitch

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Hitomezashi Stitch</title>
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
<script src="<?= html_asset('hitomezashi.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<p>
	<button onclick="objGame.loadMapRandom()">Random</button>
	<button onclick="objGame.loadMapOneOff()">One off</button>
</p>

<script>
objGame = new Hitomezashi($('canvas'));
objGame.loadMapRandom();
</script>
</body>

</html>
