<?php
// Keer op keer
// https://www.spelmagazijn.be/media/catalog/product/g/r/groene_scoreblok_b.jpg
// https://media.s-bol.com/N07PKk4pRn9p/550x567.jpg

require __DIR__ . '/inc.bootstrap.php';

$columns = [
	range('A', 'O'),
	[5, 3, 3, 3, 2, 2, 2, 1, 2, 2, 2, 3, 3, 3, 5],
	[3, 2, 2, 2, 1, 1, 1, 0, 1, 1, 1, 2, 2, 2, 3],
];

// Gray
$maps = [
	'gray' => [
		'color' => '#444',
		'map' => [
			'gggyyyyGbbbOyyy',
			'ogYgYyoopBboogg',
			'BgpgggGpppyyogg',
			'bppgoObbggyyoPb',
			'poooopbbooopppp',
			'pBbPpppyYoPbbbO',
			'yybbbbpyyyggGoo',
		],
	],
];

$mapMap = $maps['gray']['map'];
$mapColor = $maps['gray']['color'];
$mapCenter = ceil(count($columns[0]) / 2) - 1;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Keer Op Keer</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('keeropkeer.js') ?>"></script>
<style>
:root {
	--center-border: solid 3px #c00;
}
body {
	background-color: <?= do_html($mapColor) ?>;
	color: white;
	font-family: sans-serif;
}

.board,
.meta {
	float: left;
}
.board {
	margin-right: 20px;
}

table {
	/*border-spacing: 0px;*/
	border-collapse: collapse;
}
td {
	width: 30px;
	height: 30px;
	padding: 0;
	border: solid 1px <?= do_html($mapColor) ?>;
	text-align: center;
}
tbody td {
	color: black;
}
tbody .center {
	border-left: var(--center-border);
	border-right: var(--center-border);
}
thead .center,
tbody tr:last-child .center {
	border-bottom: var(--center-border);
}

.star,
.choosing,
.chosen,
.self {
	position: relative;
}
.star:before,
.choosing:after,
.chosen:after {
	display: block;
	position: absolute;
	line-height: 1;
	text-align: center;
}
.star:before {
	content: "\2606";
	top: 1px;
	left: 1px;
	width: 28px;
	height: 28px;
	font-size: 28px;
}
.choosing:after,
.chosen:after {
	content: "\274C";
	top: 7px;
	left: 7px;
	width: 16px;
	height: 16px;
	font-size: 16px;
}
.chosen:after {
	font-weight: bold;
}
.self:after {
	content: "";
	position: absolute;
	top: 2px;
	left: 2px;
	width: 22px;
	height: 22px;
	border-radius: 30px;
	border: solid 2px currentColor;
}

.dice-cont {
	display: flex;
}
.dice-cont button {
	height: 36px;
	line-height: 36px;
	padding: 0 6px;
}
#dice {
	display: contents;
}
#dice > * {
	width: 30px;
	height: 30px;
	border: solid 4px black;
	font-weight: bold;
	text-align: center;
	font-size: 24px;
	line-height: 30px;
	margin-right: 5px;
	cursor: pointer;
}
#dice > .number {
	background-color: white;
	color: black;
}
#dice > .color + .number {
	margin-left: 10px;
}
#dice > .selected {
	border-color: green;
}

#next-turn {
	margin-right: 20px;
}

[data-color="?"],
#dice > [data-number="?"] {
	background-color: black;
	color: white;
}
[data-color="g"] {
	background-color: lightgreen;
}
[data-color="y"] {
	background-color: yellow;
}
[data-color="b"] {
	background-color: lightblue;
}
[data-color="p"] {
	background-color: pink;
}
[data-color="o"] {
	background-color: orange;
}
</style>
</head>

<body>

<table class="board">
	<thead>
		<tr>
			<? foreach ($columns[0] as $i => $cell): ?>
				<td data-col="<?= $i ?>" class="<?= $mapCenter == $i ? 'center' : '' ?>"><?= $cell ?></td>
			<? endforeach ?>
		</tr>
	</thead>
	<tbody id="grid">
		<? foreach ($mapMap as $row): ?>
			<tr>
				<? foreach (str_split($row) as $i => $cell): ?>
					<td data-color="<?= strtolower($cell) ?>" class="<?= strtoupper($cell) == $cell ? 'star' : '' ?> <?= $mapCenter == $i ? 'center' : '' ?>"></td>
				<? endforeach ?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<? foreach (array_slice($columns, 1, 1) as $row): ?>
			<tr>
				<? foreach ($row as $i => $cell): ?>
					<td class="full-column" data-col="<?= $i ?>" data-score="<?= $cell ?>"><?= $cell ?></td>
				<? endforeach ?>
			</tr>
		<? endforeach ?>
	</tfoot>
</table>

<div class="meta">
	<p class="dice-cont">
		<button id="next-turn">Next round</button>
		<span id="dice"></span>
	</p>

	<p id="stats"></p>

	<table>
		<tr>
			<? foreach (['g', 'y', 'b', 'p', 'o'] as $color): ?>
				<td class="full-color" data-color="<?= $color ?>">5</td>
			<? endforeach ?>
		</tr>
	</table>
</div>

<script>
KeerOpKeer.CENTER = <?= $mapCenter ?>;
var objGame = new SoloKeerOpKeer($('#grid'));
objGame.listenControls();
</script>
