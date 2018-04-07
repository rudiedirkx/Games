<?php
// TIC TAC TOE - JS

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>TIC TAC TOE - JS</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<style>
table {
	border-collapse: collapse;
}
td {
	width: 30px;
	height: 30px;
	border: solid 1px #aaa;
	padding: 0;
	line-height: 30px;
	text-align: center;
}
td[data-chosen="0"]:after,
#turn[data-chosen="0"]:after {
	content: "x";
}
td[data-chosen="1"]:after,
#turn[data-chosen="1"]:after {
	content: "o";
}
</style>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset("tictactoe.js") ?>"></script>
</head>

<body class="<?= $bodyClass ?>">

<table>
	<tbody id="grid">
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>

<p>Turn: <span id="turn"></span></p>

<script>
var objGame = new TicTacToe($('#grid'));
objGame.startGame();
objGame.listenControls();
</script>
