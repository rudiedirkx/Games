<?php
// RECTANGLES

require __DIR__ . '/inc.bootstrap.php';

$size = $_GET['size'] ?? 7;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Rectangles</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
table {
	/*border-spacing: 1px;*/
	border-collapse: collapse;
	user-select: none;
}
td {
	width: 36px;
	height: 36px;
	padding: 0;
	border: solid 1px #aaa;
	vertical-align: middle;
	text-align: center;
}
td:not(:empty) {
	background-color: #eee;
}
</style>
<style id="colors"></style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('rectangles.js') ?>"></script>
</head>

<body>

<table class="inside" id="grid"></table>

<p>
	<button id="restart">Restart</button>
	<button id="newgame">New game</button>
	<button id="edit">Edit</button>
	<button id="export">Export to URL</button>
</p>

<script>
objGame = new Rectangles($('#grid'));
objGame.createFromExport(location.hash.substr(1)) || objGame.createMap(<?= $size ?>);
objGame.listenControls();

setTimeout(() => console.log(RectanglesSolver.fromDom($('table'))), 100);
</script>
</body>

</html>
