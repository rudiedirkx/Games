<?php
// LABYRINTH
// https://www.youtube.com/watch?v=6ECL-bH_GAw

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Labyrinth</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
* {
	user-select: none;
}
html, body {
	margin: 0;
	padding: 0;
}
body {
	display: flex;
	flex-wrap: wrap;
	gap: 20px;
}
canvas {
	display: block;
	max-width: 100vw;
	max-height: 100vh;
}
.target {
	display: inline-block;
	border: solid 1px black;
	padding: 1px 3px;
	background-color: #fff;
}
.target.found {
	background-color: #afa;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('labyrinth.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<div class="meta">
	<div><canvas id="key"></canvas></div>
	<p>
		Targets: <span id="targets"></span>
		<select id="treasurestrategy">
			<option value="InOrder">In Order</option>
			<option value="AnyOrder">Any Order</option>
		</select>
	</p>
	<p>
		Time: <span id="stats-time"></span><br>
		Moves: <span id="stats-moves"></span><br>
	</p>
	<p><button id="create">Create random</button></p>
</div>

<script>
objGame = new Labyrinth($('canvas'), $('#key'));
objGame.startGame('InOrder');
objGame.listenControls();
objGame.startPainting();
</script>
</body>

</html>
