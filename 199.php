<?php
// TRACK SWITCHER
// https://www.youtube.com/watch?v=EU6JBeIe390

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

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
	background-color: #e7e7e7;
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
		(very best = <span id="best-moves">?</span>)
		<!-- &nbsp; -->
		<!-- <label><input type="checkbox" id="show-solution" /> Show solution</label> -->
	</p>
	<? if (is_local()): ?>
		<p>
			<label><input type="checkbox" id="show-names" /> Show names</label>
		</p>
	<? endif ?>
</div>

<div class="panel">
	<h2>Solution:</h2>
	<canvas></canvas>
</div>

<script>
TrackSwitcher.BEST_SCORES = <?= json_encode(get_best_moves($db, 199)) ?>;
TrackSwitcher.BGCOLOR = '#e7e7e7';
TrackSwitcher.BGCOLOR_DRAGGING = '#ddd';
var objGame = new TrackSwitcher(...$$('canvas'));
objGame.startGame(location.hash ? parseInt(location.hash.substr(1)) - 1 : 0);
objGame.listenControls();
objGame.startPainting();
</script>

<? include 'tpl.queries.php' ?>
