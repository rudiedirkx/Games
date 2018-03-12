<?php
// MASTERMIND

require 'inc.functions.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Mastermind</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('mastermind.js') ?>"></script>
<style>
* {
	box-sizing: border-box;
	font-family: sans-serif;
	font-size: 16px;
}
html {
	background-color: #00ddff;
}
body {
	max-width: 400px;
	padding: 20px;
	margin: 0 auto;
}
table {
	border-spacing: 15px;
	background-color: #772200;
	width: 100%;
}
table td {
	width: 25px;
	height: 25px;
	padding: 0;
	text-align: center;
	vertical-align: middle;
	border: solid 1px #774400;
}
.unknown-colors td {
	background-color: #774400;
	color: white;
}
.unknown-colors span {
	position: relative;
}
.unknown-colors td.open span {
	color: transparent;
}
.unknown-colors th + td span:before {
	content: "";
	display: block;
	position: absolute;
	width: 152px;
	height: 2px;
	background: white;
	top: 28px;
	left: -12px;
}
tbody tr:not(.done):not(.active) {
	display: none;
}
tbody tr.done .choose {
	display: none;
}
tbody tr.active td {
	cursor: pointer;
}
tbody th.desc {
	width: 50%;
	text-align: left;
	padding-left: 1em;
}
tbody .score span.position {
	color: black;
}
tbody .score span.color {
	color: white;
}
td.submit {
	text-align: center;
	border: 0;
}
td.submit button {
	padding: 2px 40px;
}
body.gameover td.submit .check,
body:not(.gameover) td.submit .restart {
	display: none;
}

#color-selection {
	position: fixed;
	top: 30px;
	left: 50%;
	transform: translateX(-50%);
	margin: 0;
	padding: 30px 15px;
	background-color: #00ddff;
	outline: solid rgba(0, 0, 0, 0.7) 9999px;
	white-space: nowrap;
}
body:not(.selecting-color) #color-selection {
	display: none;
}
#color-selection li {
	display: inline-block;
	width: 25px;
	height: 25px;
	cursor: pointer;
	border: solid 2px transparent;
}
#color-selection li.selected {
	border-color: lime;
}
#color-selection li + li {
	margin-left: 15px;
}
</style>
</head>

<body class="mastermind">

<table id="table">
	<thead>
		<tr class="unknown-colors">
			<th></th>
			<td>
				<span>?</span>
			</td>
			<td>
				<span>?</span>
			</td>
			<td>
				<span>?</span>
			</td>
			<td>
				<span>?</span>
			</td>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<? for ($i=1; $i <= 10; $i++): ?>
			<tr class="<?= $i == 1 ? 'active' : '' ?>">
				<th class="iteration"><?= $i ?>.</th>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<th class="desc">
					<span class="choose">&lt; choose</span>
					<span class="score"></span>
				</th>
			</tr>
		<? endfor ?>
	</tbody>
	<tfoot>
		<tr>
			<td class="submit" colspan="6">
				<button>
					<span class="check">CHECK</span>
					<span class="restart">RESTART</span>
				</button>
			</td>
		</tr>
	</tfoot>
</table>

<ul id="color-selection"></ul>

<script>
objGame = new Mastermind();
objGame.startGame();
objGame.listenControls();
</script>
</body>

</html>
