<?php
// PICROSS 2

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>PICROSS</title>
<meta name="theme-color" content="yellow" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
:root {
	--cell-size: 28px;
}
html {
	background-color: yellow;
	font-family: sans-serif;
}
table {
	border-collapse: collapse;
}
th, td {
	border: solid 1px #999;
}
td {
	background-color: #bbb;
	padding: 0;
}
td > span {
	position: relative;
	display: block;
	width: var(--cell-size);
	height: var(--cell-size);
}
td.hilite {
	background-color: #aaa;
}
td[data-state="1"] {
	background-color: #000;
}
td[data-state="2"] {
	background-color: #fff;
}
th.hilite {
	background-color: rgba(0, 0, 0, 0.15);
}

td.last-toggle > span:after {
	content: "";
	display: block;
	position: absolute;
	left: 50%;
	top: 50%;
	margin-left: -2px;
	margin-top: -2px;
	width: 4px;
	height: 4px;
	background: red;
	z-index: 65;
	pointer-events: none;
}
td.last-toggle > span:after {
	background: green;
	border-radius: 10px;
}

th.meta.hor {
	padding: 0 4px;
	text-align: left;
	vertical-align: middle;
	white-space: nowrap;
}
th.meta.hor span {
	margin-right: 0.2em;
}
th.meta.ver {
	padding: 4px 0;
	text-align: center;
	vertical-align: top;
}
th.meta.ver span {
	display: block;
	margin-bottom: 0.2em;
}
@media (max-width: 500px) {
	:root {
		--cell-size: 24px;
	}
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('picross.js') ?>"></script>
</head>

<body>

<p>
	Size: <input id="size" type="number" min="6" max="15" style="width: 3em" />
	<button id="create">Create</button>
	<button id="cheat">Cheat</button>
	<button id="undo">Undo (<span id="undo-count">0</span>)</button>
	<button id="reset">Reset</button>
	<span id="stats-time"></span>
</p>

<table></table>

<script>
objGame = new Picross($('table'));
objGame.listenControls();
objGame.loadSavedGame() || objGame.startRandomGame();
</script>
</body>

</html>
