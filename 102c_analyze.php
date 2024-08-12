<?php

require __DIR__ . '/inc.bootstrap.php';

$g_arrMaps = require 'inc.102.maps.php';

$arrMapsWithoutKnowns = array_keys(array_filter($g_arrMaps, function(array $map) {
	$map = implode('', $map);
	return strlen($map) == strlen(str_replace(['f', 'n'], '', $map));
}));

$iMap = isset($_GET['map'], $g_arrMaps[$_GET['map']]) ? $_GET['map'] : 0;
$arrMap = $g_arrMaps[$iMap];

if ( isset($_POST['map']) ) {
	$iMap = -1;
	$arrMap = $_POST['map'];
}

$g_arrSides = array(count($arrMap), strlen($arrMap[0]));

?>
<!DOCTYPE html>
<html>

<head>
	<title>MS 2c - Test - Board Analysis</title>
	<link rel="stylesheet" href="<?= html_asset('102.css') ?>" />
	<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
	<script src="<?= html_asset('gridgame.js') ?>"></script>
	<script src="<?= html_asset('102.js') ?>"></script>
	<script src="<?= html_asset('102c.js') ?>"></script>
</head>

<body>

<form method="post" action="102d_create.php">
	<p>
		<select onchange="this.value&&(document.location='?map='+this.value)"><?= _mapsOptions($g_arrMaps, $iMap, '-- CUSTOM --') ?></select>
		|
		<? foreach ($arrMap as $row): ?>
			<input name="map[]" type="hidden" value="<?= do_html($row) ?>" />
		<? endforeach ?>
		<button>create</button>
	</p>
</form>

<table id="field" style="border:solid 1px #777;"><tr><td>
	<table style="border:solid 10px #bbb;"><tr><td>
		<table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td>
			<table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;">
				<tbody id="ms_tbody">
					<?php
					foreach ( $arrMap as $y => $row ) {
						echo '<tr>';
						foreach ( str_split($row) as $x => $tile ) {
							$class = '';
							if (strlen(trim($tile))) {
								$class = is_numeric($tile) ? 'o' . $tile : $tile;
							}
							echo '<td title="[' . $x . ', ' . $y . ']" class="' . $class . '"></td>';
						}
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
		</td></tr></table>
	</td></tr></table>
</td></tr></table>

<div>
	<p>
		<input type="button" value="SaveMinesThisRound()" onclick="solver.mf_SaveMinesThisRound()" />
		<input type="button" value="SaveMinesThisRound() + mark all" onclick="solver.mf_SaveThisRoundAndMarkAll(), solver.mf_ResetTrace()" />
	</p>
	<p>
		<input type="button" value="SaveAllMines()" onclick="solver.mf_SaveAllMines()" />
		<input type="button" value="SaveAllMines() + mark all" onclick="testShouldKnowns()" style="font-weight: bold" />
		<span id="should-knowns-ok" style="color: green" hidden>FOUND ALL</span>
		<span id="should-knowns-nok" style="color: red" hidden>MISSED SOME</span>
	</p>
	<p>
		<input type="button" value="MarkSavedMines()" onclick="solver.mf_MarkSavedMines()" />
		<input type="button" value="MarkNonoMines()" onclick="solver.mf_MarkNonoMines()" />
	</p>
</div>

<? if (count($arrMapsWithoutKnowns)): ?>
	<p>
		Maps without knowns:
		<?= implode(', ', array_map(fn($n) => sprintf('<a href="?map=%d">%d</a>', $n, $n+1), $arrMapsWithoutKnowns)) ?>
	</p>
<? endif ?>

<p><a href="102f_analyze_all.php">Analyze all</a></p>

<script>
(['dicht', 0, 1, 2, 3, 4, 5, 6, 7, 8]).forEach(function(img) {
	(new Image).src = 'images/' + (typeof img == 'number' ? 'open_' + img : img) + '.gif';
});

const solver = new MinesweeperSolver($('#ms_tbody'));
const before = solver.mf_GetBoardKnowns();
// console.log(before);

function testShouldKnowns() {
	solver.mf_SaveAndMarkAll();
	const after = solver.mf_GetBoardKnowns();
	// console.log(after);
	const missed = Object.keys(before).filter(key => after[key] == null);
	// console.log(missed);
	$('#should-knowns-' + (missed.length ? 'nok' : 'ok')).show();
}

$('#ms_tbody')
	.on('click', 'td', function(e) {
		if (!this.className || this.className == 'n') {
			this.toggleClass('n');
		}
	})
	.on('contextmenu', 'td', function(e) {
		e.preventDefault();

		if (!this.className || this.className == 'f') {
			this.toggleClass('f');
		}
	});
</script>

</body>

</html>
