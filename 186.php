<?php
// 0h h1

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>0h h1</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
table {
	border-spacing: 3px;
	border: solid 1px #999;
}
td {
	border: 0;
	padding: 0;
	width: 30px;
	height: 30px;
	background-color: #eee;
	text-align: center;
	vertical-align: middle;
}
td[data-color="on"] {
	background-color: green;
	color: lightseagreen;
}
td[data-color="off"] {
	background-color: gold;
	color: orange;
}
</style>
<!-- <script>window.onerror = function(e) { alert(e); };</script> -->
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('ohhi.js') ?>"></script>
</head>

<body>

<table class="inside" id="grid"></table>

<p>
	<button id="restart">Restart</button>
	<button id="newgame">New game</button>
	<span id="sizes"></span>
</p>

<script>
objGame = new Ohhi($('#grid'));
objGame.createMap(<?= $_GET['size'] ?? '6' ?>);
objGame.listenControls();
</script>
</body>

</html>
