<?php

$g_arrMaps = require 'inc.102.maps.php';

$g_arrSides = array(16, 30);

?>
<html>

<head>
<title>Create Minesweeper Field</title>
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
	var trs = document.querySelectorAll('#m_tbl tr');
	var szPhpArray = "array(\n";
	for ( var i=0; i<trs.length; i++ ) {
		szPhpArray += "\t'";
		var imgs = trs[i].querySelectorAll('img');
		for ( var j=0; j<imgs.length; j++ ) {
			var tile = Number(imgs[j].dataset.tile);
			szPhpArray += tile == -1 ? ' ' : String(tile);
		}
		szPhpArray += "',\n";
	}
	szPhpArray += "),\n";
	document.querySelector('#php_array').innerHTML = szPhpArray;
}
</script>
</head>

<body>

<table id="m_tbl" border="0" cellpadding="0" cellspacing="0"><?php

for ( $i=0; $i<$g_arrSides[0]; $i++ ) {
	echo '<tr>';
	for ( $j=0; $j<$g_arrSides[1]; $j++ ) {
		echo '<td><img title="[' . (1 + $j) . ', ' . (1 + $i) . ']" src="images/dicht.gif" border="0" data-tile="-1" onclick="return reTileP(this)" oncontextmenu="return reTileM(this)" /></td>';
	}
	echo '</tr>';
}

?></table>

<p><input type="button" value="create php array" onclick="createPhpArray();" /></p>

<pre id="php_array"></pre>

</body>

</html>
