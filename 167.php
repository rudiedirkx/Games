<?php
// FILLING

require __DIR__ . '/inc.bootstrap.php';

$size = $_GET['size'] ?? 7;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Filling</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
table {
	border-collapse: collapse;
	user-select: none;
	margin-bottom: 1em;
}
td {
	border: solid 1px #999;
	width: 36px;
	height: 36px;
	padding: 0;
	vertical-align: middle;
	text-align: center;
	font-weight: bold;
}
</style>
<style id="colors"></style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('filling.js') ?>"></script>
</head>

<body>

<table class="inside" id="grid"></table>

<p>
	<button id="restart">Restart</button>
	<button id="newgame">New game</button>
	<button id="export">Export to URL</button>
</p>

<script>
objGame = new Filling($('#grid'));
objGame.createFromExport(location.hash.substr(1)) || objGame.createMap(<?= $size ?>);
objGame.listenControls();
</script>

</body>

</html>
