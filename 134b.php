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
<? include 'tpl.onerror.php' ?>
<style>
table {
	border-collapse: collapse;
}
td {
	width: 50px;
	height: 50px;
	border: solid 2px #aaa;
	padding: 0;
	line-height: 50px;
	text-align: center;
	font-size: 40px;
}
tr:first-child > td {
	border-top: 0;
}
tr > td:last-child {
	border-right: 0;
}
tr:last-child > td {
	border-bottom: 0;
}
tr > td:first-child {
	border-left: 0;
}
td.winner {
	background-color: lightgreen;
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
