<?php
// 0h h1

require __DIR__ . '/inc.bootstrap.php';

$size = $_GET['size'] ?? 6;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>0h h1</title>
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
#newgame:not(.loading) > img {
	display: none;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('ohhi.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<p>
	<button id="restart">Restart</button>
	<button id="newgame">New game <img src="images/loading.gif" style="height: 1em"></button>
	<span id="sizes"></span>
	<button id="build">Build</button>
</p>
<p>
	<button id="cheat">Cheat</button>
	<button id="export">Export to URL</button>
</p>

<script>
objGame = new Ohhi($('canvas'));
objGame.createFromExport(location.hash.substr(1)) || objGame.createMap(<?= $size ?>);
objGame.listenControls();
objGame.startPainting();
</script>
</body>

</html>
