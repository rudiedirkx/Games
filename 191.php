<?php
// Keer op keer

require __DIR__ . '/inc.bootstrap.php';

$columns = [
	range('A', 'O'),
	[5, 3, 3, 3, 2, 2, 2, 1, 2, 2, 2, 3, 3, 3, 5],
	[3, 2, 2, 2, 1, 1, 1, 0, 1, 1, 1, 2, 2, 2, 3],
];

$boards = [
	'gray' => [
		'color' => '#444',
		'map' => [
			'gggyyyy G bbbOyyy',
			'ogYgYyo o pBboogg',
			'BgpgggG p ppyyogg',
			'bppgoOb b ggyyoPb',
			'poooopb b ooopppp',
			'pBbPppp y YoPbbbO',
			'yybbbbp y yyggGoo',
		],
	],
	'green' => [
		'color' => '#5fb55f',
		'map' => [
			'Ogbbppp g ggYyypp',
			'ggggpyg P ggpyypy',
			'bboGyyG b ppppooy',
			'boooogg b bbboPoo',
			'bPOpboo o BYoopYO',
			'ppppbbb y yyoBggg',
			'yyyyGBy y ooggbbb',
		],
	],
	'pink' => [
		'color' => '#a50d61',
		'map' => [
			'gGooOpp p Ybbbbbp',
			'pooygGb y yyGooOp',
			'BbbPggB y pppogoo',
			'bbpppgg O oPygggg',
			'bppbbbo b boyyyyB',
			'oyggboo g booYPpy',
			'yyYgyyy g ggopppy',
		],
	],
];

$board = isset($_GET['board'], $boards[$_GET['board']]) ? $_GET['board'] : array_rand($boards);
$mapMap = array_map(function($line) {
	return str_replace(' ', '', $line);
}, $boards[$board]['map']);
$mapColor = $boards[$board]['color'];
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
	--center-border: solid 3px #fff;
	--color: <?= do_html($mapColor) ?>;
	--size: 30px;
}
body {
	background-color: var(--color);
	color: white;
	font-family: sans-serif;
}
a {
	color: inherit;
}

.board,
.meta {
	float: left;
}
.meta {
	margin-left: 20px;
}

table {
	/*border-spacing: 0px;*/
	border-collapse: collapse;
}
td {
	width: var(--size);
	height: var(--size);
	padding: 0;
	border: solid 1px var(--color);
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
	width: calc(var(--size) - 2px);
	height: calc(var(--size) - 2px);
	font-size: calc(var(--size) - 2px);
}
.choosing:after,
.chosen:after {
	content: "\274C";
	top: 7px;
	left: 7px;
	width: calc(var(--size) - 14px);
	height: calc(var(--size) - 14px);
	font-size: calc(var(--size) - 14px);
}
.chosen:after {
	font-weight: bold;
}
.self:after {
	content: "";
	position: absolute;
	top: 2px;
	left: 2px;
	width: calc(var(--size) - 8px);
	height: calc(var(--size) - 8px);
	border-radius: 30px;
	border: solid 2px currentColor;
}

.dice-cont {
	display: flex;
	user-select: none;
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
#dice > .selected.valid {
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
	background-color: #6fe951;
}
[data-color="y"] {
	background-color: yellow;
}
[data-color="b"] {
	background-color: #97c3e9;
}
[data-color="p"] {
	background-color: #e49baf;
}
[data-color="o"] {
	background-color: orange;
}

@media (max-width: 480px) {
	:root {
		--size: 26px;
	}
}

@media (max-width: 430px) {
	:root {
		--size: 22px;
	}
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

	<p>
		Boards:
		<?= implode(' | ', array_map(function($board) {
			return '<a href="?board=' . do_html($board) . '">' . do_html($board) . '</a>';
		}, array_keys($boards))) ?>
	</p>
</div>

<script>
KeerOpKeer.CENTER = <?= $mapCenter ?>;
var objGame = new SoloKeerOpKeer($('#grid'));
objGame.listenControls();
</script>
