<?php

$g_arrMaps = require 'inc.102.maps.php';

$iMap = isset($_GET['map'], $g_arrMaps[$_GET['map']]) ? $_GET['map'] : 0;
$arrMap = $g_arrMaps[$iMap];

$g_arrSides = array(count($arrMap), strlen($arrMap[0]));

?>
<!DOCTYPE html>
<html>

<head>
	<title>MS 2c - Test - Board Analysis</title>
	<link rel="stylesheet" href="102.css" />
	<script src="js/rjs-custom.js"></script>
	<script src="102.js"></script>
	<script src="102c.js"></script>
</head>

<body>

<p>
	<select onchange="this.value&&(document.location='?map='+this.value)"><?= _mapsOptions($g_arrMaps, $iMap) ?></select>
	<? if ($iMap): ?>
		<a href="102d_create.php?map=<?= $iMap ?>">&gt; create</a>
	<? endif ?>
</p>

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
							echo '<td class="' . $class . '"></td>';
						}
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
		</td></tr></table>
	</td></tr></table>
</td></tr></table>

<div style="margin: 10px; margin-top: 0">
	<p>
		<input type="button" value="SaveAllMines()" onclick="solver.mf_SaveAllMines()" />
		<input type="button" value="MarkSavedMines()" onclick="solver.mf_MarkSavedMines()" />
		<input type="button" value="MarkNonoMines()" onclick="solver.mf_MarkNonoMines()" />
	</p>
	<p>
		<input type="button" value="SaveAndMarkAll()" onclick="solver.mf_SaveAndMarkAll()" />
	</p>
</div>

<script>
(['dicht', 0, 1, 2, 3, 4, 5, 6, 7, 8]).forEach(function(img) {
	(new Image).src = 'images/' + (typeof img == 'number' ? 'open_' + img : img) + '.gif';
});

solver = new MinesweeperSolver($('ms_tbody'));

$('ms_tbody').on('contextmenu', 'td', function(e) {
	e.preventDefault();
	Minesweeper.prototype.toggleFlag.call(Minesweeper.prototype, this);
});
</script>

</body>

</html>
