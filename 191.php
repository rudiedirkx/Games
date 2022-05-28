<?php
// Keer op keer SOLO

require __DIR__ . '/inc.bootstrap.php';
[$columns, $boards] = require '191_levels.php';

$board = isset($_GET['board'], $boards[$_GET['board']]) ? $_GET['board'] : array_rand($boards);
$mapMap = array_map(function($line) {
	return str_replace(' ', '', $line);
}, $boards[$board]['map']);
$mapCenter = ceil(count($columns[0]) / 2) - 1;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Keer Op Keer SOLO</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#333" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('keeropkeer.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('keeropkeer.js') ?>"></script>
</head>

<body class="layout solo">

<table class="board game">
	<thead>
		<tr>
			<? foreach ($columns[0] as $i => $cell): ?>
				<td data-col="<?= $i ?>" class="<?= $mapCenter == $i ? 'center' : '' ?>"><?= $cell ?></td>
			<? endforeach ?>
		</tr>
	</thead>
	<tbody id="grid"></tbody>
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
		<button id="next-turn">
			<span class="state start">Start game</span>
			<span class="state turn">
				<span class="choosing">End turn</span>
				<span class="not-choosing">SKIP turn</span>
			</span>
			<span class="state end">End game</span>
			<span class="state restart">New game</span>
		</button>
		<span id="dice"></span>
	</p>

	<p id="challenge"></p>

	<p id="stats"></p>

	<table class="colors game">
		<? foreach ([5] as $score): ?>
			<tr>
				<? foreach (['g', 'y', 'b', 'p', 'o'] as $color): ?>
					<td class="full-color" data-color="<?= $color ?>" data-score="<?= $score ?>"><?= $score ?></td>
				<? endforeach ?>
			</tr>
		<? endforeach ?>
	</table>

	<p>
		<?= implode(' | ', array_map(function($board) {
			return '<a data-board="' . do_html($board) . '" href="?board=' . do_html($board) . '">' . do_html($board) . '</a>';
		}, array_keys($boards))) ?>
	</p>
</div>

<script>
KeerOpKeer.CENTER = <?= json_encode($mapCenter) ?>;
KeerOpKeer.BOARDS = <?= json_encode($boards) ?>;
var objGame = new SoloKeerOpKeer($('#grid'));
objGame.startGame(<?= json_encode($board) ?>);
objGame.listenControls();
</script>
