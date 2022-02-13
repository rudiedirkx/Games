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
	/*gap: 20px;*/
	font-family: sans-serif;
}
canvas {
	display: block;
	max-width: 100%;
	max-height: 100vh;
}
.meta {
	margin-top: 20px;
	margin-left: 20px;
	max-width: 80vh;
}
.key-stats {
	display: flex;
	gap: 20px;
	align-items: flex-start;
}
.key-stats p:first-child {
	margin-top: 0;
}
.key-stats p:last-child {
	margin-bottom: 0;
}
#status {
	font-style: italic;
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
	<div class="key-stats">
		<canvas id="key"></canvas>
		<div>
			<p id="status">Eeeeehhh</p>
			<p>
				Time: <span id="stats-time"></span><br>
				Moves: <span id="stats-moves"></span><br>
			</p>
		</div>
	</div>
	<p>
		Targets: <span id="targets"></span>
		<select id="treasurestrategy">
			<option value="InOrder">In Order</option>
			<option value="AnyOrder">Any Order</option>
		</select>
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
