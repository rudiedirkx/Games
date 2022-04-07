<?php
// TRACK SWITCHER
// https://www.youtube.com/watch?v=EU6JBeIe390

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Track Switcher / De Spoorwerf</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#333" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('trackswitcher.js') ?>"></script>
<style>
canvas {
	/*background-color: #eee;*/
	max-width: 100%;
}
</style>
</head>

<body class="solo">

<canvas></canvas>

<p>
	Level <select id="levels"></select>
	<button id="restart">Restart</button>
	&nbsp;
	<label><input type="checkbox" id="show-names" /> Show names?</label>
	&nbsp;
	<label><input type="checkbox" id="show-solution" /> Show solution?</label>
</p>

<script>
var objGame = new TrackSwitcher($('canvas'));
objGame.startGame(location.hash ? parseInt(location.hash.substr(1)) : 0);
objGame.listenControls();
objGame.startPainting();
</script>
