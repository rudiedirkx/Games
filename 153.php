<?php
// F1 racer

define( 'S_NAME', 'f1r' );

$g_arrLevels = array(
	1 => array(
		array(0, 5),
		array(0, 4),
		array(0, 3),
		array(0, 2),
		array(1, 2),
		array(2, 1),
		array(3, 1),
		array(4, 0),
		array(5, 0),
		array(6, 0),
		array(7, 0),
		array(8, 0),
		array(9, 0),
		array(10, 1),
		array(11, 1),
		array(12, 2),
		array(13, 3),
		array(14, 4),
		array(14, 5),
		array(13, 6),
		array(12, 6),
		array(11, 6),
		array(10, 6),
		array(9, 6),
		array(8, 6),
		array(7, 6),
		array(6, 6),
		array(5, 6),
		array(4, 6),
		array(3, 6),
		array(3, 5),
		array(2, 4),
		array(2, 5),
		array(2, 6),
		array(2, 7),
		array(1, 7),
		array(0, 6),
		array(0, 5),
	),
	2 => array(
		array(9, 5),
		array(8, 5),
		array(7, 5),
		array(6, 5),
		array(5, 5),
		array(4, 5),
		array(3, 5),
		array(2, 5),
		array(1, 5),
		array(1, 4),
		array(0, 3),
		array(0, 2),
		array(1, 1),
		array(2, 1),
		array(3, 1),
		array(4, 1),
		array(4, 2),
		array(3, 3),
		array(4, 4),
		array(5, 4),
		array(6, 3),
		array(7, 2),
		array(7, 1),
		array(8, 1),
		array(9, 2),
		array(10, 3),
		array(11, 3),
		array(12, 2),
		array(12, 1),
		array(13, 1),
		array(14, 2),
		array(14, 3),
		array(14, 4),
		array(13, 5),
		array(12, 5),
		array(11, 5),
		array(10, 5),
		array(9, 5),
	),
);

if ( isset($_GET['fetch'], $_GET['level']) ) {
	if ( !isset($g_arrLevels[$_GET['level']]) ) {
		exit('Invalid level!');
	}
	$x = $y = 0;
	foreach ( $g_arrLevels[$_GET['level']] AS $c ) {
		if ( $c[0] > $x ) { $x = $c[0]; }
		if ( $c[1] > $y ) { $y = $c[1]; }
	}
	$arrLevel = array(
		'map'	=> $g_arrLevels[$_GET['level']],
		'size'	=> array($x+1, $y+1),
	);
	exit(json_encode($arrLevel));
}

?>
<html>

<head>
<title>F1 racer</title>
<style>
table.f1track {
	border-collapse		: collapse;
}
table.f1track td {
	border				: solid 1px #eee;
	padding				: 0;
	width				: 20px;
	height				: 20px;
	font-size			: 6px;
	text-align			: center;
}
table.f1track td img {
	display				: none;
	width				: 12px;
	height				: 12px;
	background-color	: red;
	cursor				: pointer;
}
table.f1track td.track {
	background-color	: #000;
}
table.f1track td.active img {
	display				: inline;
}
</style>
<script>
function $(id) {
	return document.getElementById(id);
};

var g_arrMap = [], g_iPosition = 0;
function loadMap(m) {
	var xhr = new XMLHttpRequest;
	xhr.open('get', '?fetch=1&level=' + m, true);
	xhr.onload = function(e) {
		var r = JSON.parse(this.responseText);

		g_arrMap = r.map;
		g_iPosition = 0;

		// Empty table
		var track = $('f1track');
		while ( track.firstChild ) {
			track.removeChild(track.firstChild);
		}

		// Fill table
		for ( var y=0; y<r.size[1]; y++ ) {
			var tr = $('f1track').insertRow($('f1track').rows.length);
			for ( var x=0; x<r.size[0]; x++ ) {
				var td = tr.insertCell(tr.cells.length);
				td.id = 'f_' + x + '_' + y + '';
				td.innerHTML = '<img src="/icons/blank.gif" />';
			}
		}

		// Hilite track
		for ( var i=0; i<r.map.length; i++ ) {
			var f = $('f_' + r.map[i][0] + '_' + r.map[i][1]);
			if ( f ) {
				f.classList.add('track');
			}
		}

		$('f_' + r.map[0][0] + '_' + r.map[0][1]).classList.add('active');
		$('f_' + r.map[r.map.length-1][0] + '_' + r.map[r.map.length-1][1]).style.backgroundColor = 'blue';
	};
	xhr.send();
	return false;
}
</script>
</head>

<body>
<table class="f1track" border="0">
<tbody id="f1track"></tbody>
<tfoot><tr><td colspan="30" align="center"><select onchange="if(this.value){loadMap(this.value);}" name="tmp"><option value="">--</option><?php foreach ( array_keys($g_arrLevels) AS $l ) { echo '<option value="'.$l.'">Level '.$l.'</option>'; } ?></select></td></tr></tfoot>
</table>

<script>
$('f1track').onclick = function(e) {
	e.preventDefault();

	var img = e.target;
	if ( 'IMG' == img.nodeName && img.parentNode.classList.contains('active') ) {
		var op = img.parentNode;
		// Change old
		op.classList.remove('active');
//		op.style.backgroundColor = '';
		// Change new
		if ( ++g_iPosition < g_arrMap.length ) {
			var c = g_arrMap[g_iPosition], np = $('f_' + c[0] + '_' + c[1]);
			np.classList.add('active');
//			np.style.backgroundColor = 'green';
		}
		else {
			alert('Finish!');
		}
	}
	return false;
}
loadMap(<?php echo key($g_arrLevels); ?>);
</script>
</body>

</html>
