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
<script src="<?= html_asset('trackswitcher_levels.js') ?>"></script>
<style>
body {
	font-family: sans-serif;
	display: flex;
	flex-flow: row wrap;
	justify-content: space-between;
}
canvas {
	/*background-color: #eee;*/
	max-width: 100%;
}
button:disabled {
	opacity: 0.75;
}
</style>
</head>

<body class="solo">

<div class="panel">
	<!-- <h2>Reality:</h2> -->
	<canvas></canvas>

	<p>
		<button id="prev">&lt;&lt;</button>
		<select id="levels"></select>
		<button id="next">&gt;&gt;</button>
		<button id="restart">Restart</button>
		|
		<span id="stats-time"></span>
		|
		<span id="stats-moves"></span> moves
		<!-- &nbsp; -->
		<!-- <label><input type="checkbox" id="show-solution" /> Show solution</label> -->
	</p>
	<p>
		<label><input type="checkbox" id="show-names" /> Show names</label>
	</p>
</div>

<div class="panel">
	<h2>Solution:</h2>
	<canvas></canvas>
</div>

<script>
var objGame = new TrackSwitcher(...$$('canvas'));
objGame.startGame(location.hash ? parseInt(location.hash.substr(1)) : 0);
objGame.listenControls();
objGame.startPainting();
</script>
