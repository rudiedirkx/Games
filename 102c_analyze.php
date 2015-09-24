<?php

$g_arrMaps = require 'inc.102.maps.php';

$arrMap = isset($_GET['map'], $g_arrMaps[$_GET['map']]) ? $g_arrMaps[$_GET['map']] : reset($g_arrMaps);
if ( isset($_POST['field']) && is_array($_POST['field']) ) {
	$arrMap = $_POST['field'];
	foreach ( $arrMap AS $k => $m ) {
		$arrMap[$k] = array_map('intval', $m);
	}
}

$g_arrSides = array(count($arrMap), strlen($arrMap[0]));

?>
<!DOCTYPE html>
<html>

<head>
<title>MS 2c - Test - Board Analysis</title>
<style>
* {
	margin			: 0;
	padding			: 0;
}
p {
	margin			: 5px 0;
}
#field {
	margin: 10px;
}
</style>
<link rel="stylesheet" href="102.css" />
<script src="js/rjs-custom.js"></script>
<script src="102c.js"></script>
</head>

<body>

<select style="margin: 10px 10px 0 10px" onchange="this.value&&(document.location='?map='+this.value)"><?php

for ( $i=0; $i<count($g_arrMaps); $i++ ) {
	echo '<option value="' . $i . '"' . ( $arrMap == $g_arrMaps[$i] ? ' selected' : '' ) . '>Map ' . (1+$i) . ' (' . count($g_arrMaps[$i][0]) . 'x' . count($g_arrMaps[$i]) . ')</option>';
}

?></select>

<table id="field" style="border:solid 1px #777;"><tr><td>
	<table style="border:solid 10px #bbb;"><tr><td>
		<table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td>
			<table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;">
				<tbody id="ms_tbody">
					<?php
					foreach ( $arrMap as $y => $row ) {
						echo '<tr>';
						foreach ( str_split($row) as $x => $tile ) {
							echo '<td class="o' . trim($tile) . '"></td>';
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
</script>

</body>

</html>
