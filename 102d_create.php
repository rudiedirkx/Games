<?php

require __DIR__ . '/inc.bootstrap.php';

$g_arrMaps = require 'inc.102.maps.php';

$g_arrSides = array(16, 30);

$iMap = isset($_GET['map'], $g_arrMaps[$_GET['map']]) ? $_GET['map'] : -1;
$arrMap = null;

if ( isset($_POST['map']) ) {
	$iMap = -1;
	$arrMap = $_POST['map'];

	$g_arrSides[0] = max(4, count($arrMap));
	$g_arrSides[1] = max(4, strlen($arrMap[0]));
}

if ( isset($g_arrMaps[$iMap]) ) {
	// As-is
	$arrMap = $g_arrMaps[$iMap];

	// Add 1 cell extra around the border
	// $empty = str_repeat(' ', strlen($g_arrMaps[$iMap][0])+2);
	// $arrMap = array_map(function($line) {
	// 	return ' ' . $line . ' ';
	// }, array_merge(array($empty), $g_arrMaps[$iMap], array($empty)));

	$g_arrSides[0] = max(4, count($arrMap));
	$g_arrSides[1] = max(4, strlen($arrMap[0]));
}

?>
<!DOCTYPE html>
<html>

<head>
<title>Create Minesweeper Field</title>
<link rel="stylesheet" href="102.css" />
<script src="/js/rjs-custom.js"></script>
<script>
var g_arrImgs = ['dicht', 0, 1, 2, 3, 4, 5, 6, 7, 8];
g_arrImgs.forEach(function(img, i) {
	(new Image).src = g_arrImgs[i] = 'images/' + (typeof img == 'number' ? 'open_' + img : img) + '.gif';
});

String.repeat = function(str, num) {
	var out = str;
	while (--num) out += str;
	return out;
};
</script>
<style>
.ms-container {
	display: inline-block;
	position: relative;
}
.more-less {
	position: absolute;
}
.more-less.top,
.more-less.bottom {
	left: 50%;
	transform: translateX(-50%);
}
.more-less.top {
	top: -10px;
}
.more-less.bottom {
	bottom: -10px;
}
.more-less.left,
.more-less.right {
	top: 50%;
	transform: translateY(-50%);
	max-width: 2em;
}
.more-less.left {
	text-align: right;
	left: -10px;
}
.more-less.right {
	right: -10px;
}
.more-less button {
	font-weight: bold;
	display: inline-block;
	width: 1.6em;
	height: 1.6em;
	padding: 0;
}
.more-less.top button,
.more-less.bottom button {
	float: left;
}
.more-less.left button,
.more-less.right button {
	display: block;
}
</style>
</head>

<body>

<p>
	<select onchange="document.location='?map='+this.value"><?= _mapsOptions($g_arrMaps, $iMap, '-- NEW -- ') ?></select>
	<? if ($iMap): ?>
		<a href="102c_analyze.php?map=<?= $iMap ?>">&gt; analyze</a>
	<? endif ?>
</p>

<div class="ms-container">
	<table id="field" style="border:solid 1px #777;"><tr><td>
		<table style="border:solid 10px #bbb;"><tr><td>
			<table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td>
				<table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;">
					<tbody id="ms_tbody">
						<!-- ADD: top -->
						<?php
						$tiles = array_merge(array('dicht'), range(0, 8));
						for ( $y=0; $y<$g_arrSides[0]; $y++ ) {
							echo '<tr>' . "\n";
							echo '<!-- ADD: left -->' . "\n";
							for ( $x=0; $x<$g_arrSides[1]; $x++ ) {
								$tileIndex = $arrMap && isset($arrMap[$y][$x]) && is_numeric($arrMap[$y][$x]) ? $arrMap[$y][$x] : -1;
								$tileClass = $tileIndex > -1 ? 'o' . $tileIndex : '';
								echo '<td data-tile="' . $tileIndex . '" class="' . $tileClass . '" title="[' . $x . ', ' . $y . ']"></td>';
							}
							echo '<!-- ADD: right -->' . "\n";
							echo '</tr>' . "\n";
						}
						?>
						<!-- ADD: bottom -->
					</tbody>
				</table>
			</td></tr></table>
		</td></tr></table>
	</td></tr></table>

	<? foreach (array('top', 'right', 'bottom', 'left') as $loc): ?>
		<div class="more-less <?= $loc ?>">
			<button data-loc="<?= $loc ?>" class="add" data-op="add">+</button>
			<button data-loc="<?= $loc ?>" class="remove" data-op="remove">-</button>
		</div>
	<? endforeach ?>
</div>

<form id="form-analyze" method="post" action="102c_analyze.php">
	<div id="form-analyze-input"></div>
	<p>
		<button id="cpa">create php array</button>
		<button data-form-action="102d_create.php" id="cache">cache</button>
		<button data-form-action="102c_analyze.php" id="analyze">analyze</button>
	</p>
</form>

<textarea tabindex="-1" rows="19" cols="50" id="export"></textarea>

<script src="102.js"></script>
<script>
var $tbody = $('#ms_tbody');

// CLICK AND RIGHT CLICK
function reTile(td, delta) {
	var tile = Number(td.data('tile')) + delta;
	tile = ((tile + 11) % 10) - 1;

	td.data('tile', tile);
	if (tile == -1) {
		td.className = '';
	}
	else {
		td.className = 'o' + tile;
	}
}
$tbody.on('click', 'td', function(e) {
	e.preventDefault();
	reTile(this, 1);
});
$tbody.on('contextmenu', 'td', function(e) {
	e.preventDefault();
	reTile(this, -1);
});

// EXPORT TO ANALYZE
$$('#analyze, #cache').on('click', function(e) {
	$form = $('#form-analyze');
	Minesweeper.prototype.export.call(Minesweeper.prototype, function(rows) {
		$('#form-analyze-input').setHTML(rows.map(function(row) {
			return '<input name="map[]" type="hidden" value="' + row + '" />';
		}).join(''));
	});
	$form.action = this.data('form-action');
	$form.submit();
});

// MORE & LESS BUTTONS
$$('.more-less button.add').on('click', function(e) {
	var td = '<td data-tile="-1"></td>';

	var loc = this.data('loc');
	var html = $tbody.getHTML();
	if (['top', 'bottom'].contains(loc)) {
		var more = '<tr>' + String.repeat(td, $tbody.rows[0].cells.length) + '</tr>';
	}
	else {
		var more = td;
	}

	var token = '<!-- ADD: ' + loc + ' -->';
	var replacement = ['top', 'left'].contains(loc) ? token + more : more + token;
	html = html.replace(new RegExp(token, 'g'), replacement);
	$tbody.setHTML(html);
});
$$('.more-less button.remove').on('click', function(e) {
	var loc = this.data('loc');

	var sel = {
		top: 'tr:first-child',
		bottom: 'tr:last-child',
		left: 'td:first-child',
		right: 'td:last-child',
	};
	var remove = $tbody.getElements(sel[loc]);
	remove.invoke('remove');
});

// CREATE PHP ARRAY
$('#cpa').on('click', function(e) {
	e.preventDefault();

	var trs = $tbody.children;
	var szPhpArray = "\tarray(\n";
	for ( var i=0; i<trs.length; i++ ) {
		szPhpArray += "\t\t'";
		var tds = trs[i].children;
		for ( var j=0; j<tds.length; j++ ) {
			var tile = Number(tds[j].dataset.tile);
			szPhpArray += tile == -1 ? ' ' : String(tile);
		}
		szPhpArray += "',\n";
	}
	szPhpArray += "\t),\n";
	$('#export').value = szPhpArray;
	$('#export').select();
});
</script>

</body>

</html>
