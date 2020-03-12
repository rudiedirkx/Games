<?php
// FLOOD

require __DIR__ . '/inc.bootstrap.php';

$colors = ['red', 'green', 'blue', 'yellow'];

$size = $_GET['size'] ?? 10;

?>
<!DOCTYPE>
<html>

<head>
<meta charset="utf-8" />
<title>Flood</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
td {
	padding: 0;
	width: 30px;
	height: 30px;
	background-color: #eee;
}
<? foreach ($colors as $n => $color): ?>
	td[data-color="<?= $n ?>"] { background-color: <?= $color ?>; }
<? endforeach ?>
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('flood.js') ?>"></script>
</head>

<body>
<table></table>

<p>
	<span id="stats"></span>
	<button id="restart">Restart</button>
	<button id="newgame">New game</button>
</p>

<script>
objGame = new Flood($('table'));
objGame.createMap(<?= $size ?>);
objGame.listenControls();
</script>
</body>

</html>
