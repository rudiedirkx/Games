<?php
// PICROSS

session_start();

include_once('connect.php');
require_once('json_php'.(int)PHP_VERSION.'.php');

$g_arrMaps = array(
	1 => array(
		'xxxx',
		'x__x',
		'__xx',
		'_x__',
	),
	2 => array(
		'xx_x',
		'_xxx',
		'xxx_',
		'x_xx',
	),
	3 => array(
		'_xxxxx_',
		'_x_x_x_',
		'_xxxxx_',
		'___x___',
		'_xxxxx_',
		'_x_x_xx',
		'___x___',
		'_xxxxx_',
		'_x___x_',
		'xx___xx',
	),
	4 => array(
		'xxxxxxxxxxxxxx_',
		'x__xxxxxxxxx__x',
		'x__xxxxx__xx__x',
		'x__xxxxx__xx__x',
		'x__xxxxx__xx__x',
		'x__xxxxxxxxx__x',
		'x_____________x',
		'x_xxxxxxxxxxx_x',
		'x_x_________x_x',
		'x_x_xxxxxxx_x_x',
		'x_x_________x_x',
		'x_x_xxxxxxx_x_x',
		'x_x_________x_x',
		'x_x_________x_x',
		'xxxxxxxxxxxxxxx',
	),
	5 => array(
		'xxx_xxxxxxxxxxx',
		'xx___xxxxxxxxxx',
		'x___xxxxxxxxxxx',
		'___xxxxxxxxxxxx',
		'__xxxxxx___xxxx',
		'___xxxx_____xxx',
		'x___xx__xx__xxx',
		'xx_xx__xxx__xxx',
		'xxxx__xxx__xx_x',
		'xxxx__xx__xx___',
		'xxxxx____xx___x',
		'xxxxxx__xx___xx',
		'xxxxxxxxx___xxx',
		'xxxxxxxxxx___xx',
		'xxxxxxxxxxx___x',
	),
);

//$_GET['fetch_map'] = $_GET['level'] = 4;
if ( isset($_GET['fetch_map'], $_GET['level']) ) {
	if ( !isset($g_arrMaps[$_GET['level']]) ) {
		exit('Invalid level ['.$_GET['level'].']');
	}
	$arrMap = $g_arrMaps[$_GET['level']];
	$b = array('h' => array(), 'v' => array());
	foreach ( $arrMap AS $l => $szLine ) {
		$b['v'][$l] = implode(',', array_map('strlen', preg_split('/(_+)/', trim($szLine, '_'))));
	}
	for ( $x=0; $x<strlen($arrMap[0]); $x++ ) {
		$szRow = '';
		foreach ( $arrMap AS $l => $szLine ) {
			$szRow .= substr($szLine, $x, 1);
		}
		$b['h'][$x] = implode(',', array_map('strlen', preg_split('/(_+)/', trim($szRow, '_'))));
	}
//exit('<pre>'.print_r($b, true));
	$arrMap = array(
		'size' => array(strlen($arrMap[0]), count($arrMap)),
		'borders' => $b,
	);
	exit(json::encode($arrMap));
}

?>
<html>

<head>
<title>PICROSS</title>
<style type="text/css">
body, table, input {
	font-family			: verdana;
	font-size			: 12px;
	color				: #000;
	line-height			: 150%;
	cursor				: default;
}
table#picross {
	border-collapse		: collapse;
}
tbody#picross_tb td {
	border				: solid 1px #bbb;
	cursor				: pointer;
	width				: 28px;
	height				: 28px;
	padding				: 2px;
}
tbody#picross_tb td.hborder,
tbody#picross_tb td.vborder {
	background-color	: #eee;
	text-align			: center;
	vertical-align		: top;
}
tbody#picross_tb td.vborder {
	text-align			: left;
	vertical-align		: middle;
	padding				: 2px 4px;
}
</style>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<script type="text/javascript">
<!--//
var _bg = [ 'white', 'black', 'pink url(images/dot.gif) 50% 50% no-repeat' ];
var _lvl = 0;
function fetchMap(l) {
	new Ajax('?fetch_map=1&level=' + l, {
		onComplete : function(t) {
			try { var r = eval('('+t+')') }
			catch (ex) { alert(t); return; }
			console.debug(r);
			_lvl = l;
			$('stats_level').innerHTML = _lvl;
			// clear tbody
			while ( 0 < $('picross_tb').childNodes.length ) {
				$('picross_tb').removeChild($('picross_tb').firstChild);
			}
			// fill tbody
			for ( var y=0; y<r.size[1]; y++ ) {
				var tr = $('picross_tb').insertRow($('picross_tb').rows.length);
				for ( var x=0; x<r.size[0]; x++ ) {
					tr.insertCell(0);
				}
				var b = tr.insertCell(r.size[0]);
				b.className = 'vborder';
				b.innerHTML = r.borders.v[y].replace(/,/g, '&nbsp;');
			}
			var tr = $('picross_tb').insertRow(r.size[1]);
			for ( var x=0; x<r.size[0]; x++ ) {
				var b = tr.insertCell(tr.cells.length);
				b.className = 'hborder';
				b.innerHTML = r.borders.h[x].replace(/,/g, '<br \/>');
			}
			var b = tr.insertCell(tr.cells.length);
			b.className = 'hborder';
			b.style.backgroundColor = 'white';
		}
	}).request();
	return false;
}
//-->
</script>
</head>

<body>
<table id="picross"><thead><tr><th colspan="40">Level [<span id="stats_level">0</span>] | <a href="#" onclick="return fetchMap(_lvl-1);">prev</a> | <a href="#" onclick="return fetchMap(_lvl+1);">next</a></th></tr></thead><tbody id="picross_tb"><tr><td></td></tr></tbody></table>
<script type="text/javascript">
<!--//
$('picross_tb').onclick = function(e) {
	e = new Event(e);
	if ( 'TD' == e.target.nodeName && !e.target.className ) {
		e.target.style.background = '' == e.target.style.background ? 'black' : '';
	}
}
$('picross_tb').oncontextmenu = function(e) {
	e = new Event(e).stop();
	if ( 'TD' == e.target.nodeName && !e.target.className ) {
		e.target.style.background = '' != e.target.style.background ? '' : 'white url(images/dot.gif) center center no-repeat';
	}
	return false;
}
fetchMap(1);
//-->
</script>
</body>

</html>