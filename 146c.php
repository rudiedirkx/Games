<?php
// SLITHER 3

require __DIR__ . '/inc.bootstrap.php';

$g_arrLevels = require '146_levels.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('slither.js') ?>"></script>
<title>SLITHER 3</title>
<style>
html, body {
	margin: 0;
	padding: 0;
}
body {
	margin: 5px;
}
canvas {
	background-color: #bde4a3;
	outline: solid 1px black;
	max-width: calc(100vw - 10px);
	max-height: calc(100vh - 10px);
	max-width: 100%;
	touch-action: none;
}
</style>
</head>

<body>
<canvas></canvas>

<p>
	<button id="restart">Restart</button>
	Level: <select id="level"></select>
	<button data-level-nav="-1">&lt;</button>
	<button data-level-nav="+1">&gt;</button>
</p>

<p>Click between dots to connect a slither and make every cell have the right number of connectors. White numbers = good.</p>

<script>
<? if (isset($_POST['import'])): ?>
	Slither.LEVELS = [<?= json_encode(json_decode($_POST['import'])) ?>];
<? else: ?>
	Slither.LEVELS = <?= json_encode($g_arrLevels) ?>;
<? endif ?>
objGame = new Slither($('canvas'));
objGame.listenControls();
objGame.loadFromSaved() || objGame.loadLevel(<?= json_encode($_GET['level'] ?? 'easy-0') ?>);
objGame.startPainting();
</script>
</body>

</html>
