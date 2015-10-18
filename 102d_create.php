<?php

$g_arrMaps = require 'inc.102.maps.php';

$g_arrSides = array(16, 30);

$iMap = isset($_GET['map'], $g_arrMaps[$_GET['map']]) ? $_GET['map'] : -1;
$arrMap = null;
if ( isset($g_arrMaps[$iMap]) ) {
	$empty = str_repeat(' ', strlen($g_arrMaps[$iMap][0])+2);
	$arrMap = array_map(function($line) {
		return ' ' . $line . ' ';
	}, array_merge(array($empty), $g_arrMaps[$iMap], array($empty)));

	$g_arrSides[0] = max($g_arrSides[0], count($arrMap));
	$g_arrSides[1] = max($g_arrSides[1], strlen($arrMap[0]));
}

?>
<!DOCTYPE html>
<html>

<head>
<title>Create Minesweeper Field</title>
<link rel="stylesheet" href="102.css" />
<script>
var g_arrImgs = ['dicht', 0, 1, 2, 3, 4, 5, 6, 7, 8];
g_arrImgs.forEach(function(img, i) {
	(new Image).src = g_arrImgs[i] = 'images/' + (typeof img == 'number' ? 'open_' + img : img) + '.gif';
});

function reTileP( f_obj ) {
	var iTile = Number(f_obj.dataset.tile);
	var iNext = iTile + 1;
	if ( 8 < iNext ) {
		iNext = -1;
	}
	f_obj.dataset.tile = iNext;
	f_obj.src = g_arrImgs[iNext+1];
	return false;
}

function reTileM( f_obj ) {
	var iTile = Number(f_obj.dataset.tile);
	var iNext = iTile - 1;
	if ( -1 > iNext ) {
		iNext = 8;
	}
	f_obj.dataset.tile = iNext;
	f_obj.src = g_arrImgs[iNext+1];
	return false;
}

function createPhpArray() {
	var trs = document.querySelectorAll('#ms_tbody tr');
	var szPhpArray = "\tarray(\n";
	for ( var i=0; i<trs.length; i++ ) {
		szPhpArray += "\t\t'";
		var imgs = trs[i].querySelectorAll('img');
		for ( var j=0; j<imgs.length; j++ ) {
			var tile = Number(imgs[j].dataset.tile);
			szPhpArray += tile == -1 ? ' ' : String(tile);
		}
		szPhpArray += "',\n";
	}
	szPhpArray += "\t),\n";
	document.querySelector('#php_array').value = szPhpArray;
	document.querySelector('#php_array').select();
}
</script>
</head>

<body>

<p>
	<select onchange="this.value&&(document.location='?map='+this.value)"><?= _mapsOptions($g_arrMaps, $iMap) ?></select>
	<? if ($iMap): ?>
		<a href="102c_analyze.php?map=<?= $iMap ?>">&gt; analyze</a>
	<? endif ?>
</p>

<table id="field" style="border:solid 1px #777;"><tr><td>
	<table style="border:solid 10px #bbb;"><tr><td>
		<table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td>
			<table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;">
				<tbody id="ms_tbody">
					<?php
					$tiles = array_merge(array('dicht'), range(0, 8));
					for ( $i=0; $i<$g_arrSides[0]; $i++ ) {
						echo '<tr>' . "\n";
						for ( $j=0; $j<$g_arrSides[1]; $j++ ) {
							$tileIndex = $arrMap && isset($arrMap[$i][$j]) && $arrMap[$i][$j] != ' ' ? $arrMap[$i][$j] : -1;
							$tileImage = $tileIndex > -1 ? 'open_' . $tiles[$tileIndex+1] : 'dicht';
							echo '<td><img title="[' . (1 + $j) . ', ' . (1 + $i) . ']" src="images/' . $tileImage . '.gif" border="0" data-tile="' . $tileIndex . '" onclick="return reTileP(this)" oncontextmenu="return reTileM(this)" /></td>' . "\n";
						}
						echo '</tr>' . "\n";
					}
					?>
				</tbody>
			</table>
		</td></tr></table>
	</td></tr></table>
</td></tr></table>

<p><input type="button" value="create php array" onclick="createPhpArray();" /></p>

<textarea rows="19" cols="50" id="php_array"></textarea>

</body>

</html>
