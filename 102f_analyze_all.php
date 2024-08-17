<?php

require __DIR__ . '/inc.bootstrap.php';

$g_arrMaps = require 'inc.102.maps.php';

?>
<!DOCTYPE html>
<html>

<head>
	<title>MS - All Boards Analysis</title>
	<link rel="stylesheet" href="<?= html_asset('102.css') ?>" />
	<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
	<script src="<?= html_asset('gridgame.js') ?>"></script>
	<script src="<?= html_asset('102.js') ?>"></script>
	<script src="<?= html_asset('102c.js') ?>"></script>
</head>

<body>

<? foreach ($g_arrMaps as $n => $arrMap): ?>
	<div class="ms-board-container" style="margin-bottom: 1em; display: flex; gap: 1em">
		<table id="field" style="margin:0; border:solid 1px #777;"><tr><td>
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
			<p><a href="102c_analyze.php?map=<?= $n ?>">Analyze # <?= $n + 1 ?></a></p>
			<p class="ms-board-message"></p>
			<p><button class="mark-found" type="button">Mark</button></p>
		</div>
	</div>
<? endforeach ?>

<script>
const boards = $$('#ms_tbody');
boards.forEach(el => {
	const cont = el.closest('.ms-board-container');

	const solver = new MinesweeperSolver(el);
	const before = solver.mf_GetBoardKnowns();
	solver.mf_SaveAllMines();
	const after = solver.m_arrKnowns; // solver.mf_GetBoardKnowns();
	const missed = Object.keys(before).filter(key => after[key] == null);
	cont.querySelector('.ms-board-message').setText('Missed ' + missed.length);
	cont.querySelector('.mark-found').onclick = e => solver.mf_SaveAndMarkAll();
	if (missed.length == 0) {
		cont.hide();
	}
});

function testShouldKnowns() {
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
