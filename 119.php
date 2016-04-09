<?php
// PICROSS

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
	exit(json_encode($arrMap));
}

$level = getLevel();
$map = prepareLevel($level);

?>
<!doctype html>
<html>

<head>
<title>PICROSS</title>
<style>
body {
	font-family: sans-serif;
	background-color: yellow;
}
table {
	border-collapse: collapse;
}
td,
th {
	border: solid 1px #999;
	cursor: pointer;
	padding: 4px;
	line-height: 1.4;
}
thead a.disabled {
	visibility: hidden;
}
tbody td {
	background: #bbb;
	padding: 0;
	width: calc(1.4em + 8px);
}
tbody td[data-state="active"] {
	background-color: black;
}
tbody td[data-state="inactive"] {
	background-color: white;
}
tbody a {
	display: block;
	padding: 4px 0;
	text-decoration: none;
}
th.hor {
	text-align: left;
}
th.hor span + span {
	margin-left: .25em;
}
th.ver {
	text-align: center;
	vertical-align: top;
}
th.ver span {
	display: block;
}
</style>
</head>

<body>
	<table id="picross">
		<thead>
			<tr>
				<th colspan="40">
					<a class="<?= !isset($g_arrMaps[$level-1]) ? 'disabled' : '' ?>" href="?level=<?= $level-1 ?>">&lt;&lt;</a> &nbsp;
					Level <?= $level ?> &nbsp;
					<a class="<?= !isset($g_arrMaps[$level+1]) ? 'disabled' : '' ?>" href="?level=<?= $level+1 ?>">&gt;&gt;</a>
				</th>
			</tr>
		</thead>
		<tbody id="picross_tb">
			<? foreach ($map['map'] as $y => $line): ?>
				<tr>
					<? for ($x=0; $x < strlen($line); $x++): ?>
						<td><a href="#">&nbsp;</a></td>
					<? endfor ?>
					<th class="hor"><span><?= implode('</span> <span>', $map['hor'][$y]) ?></span></th>
				</tr>
			<? endforeach ?>
			<tr>
				<? for ($x=0; $x < strlen($map['map'][0]); $x++): ?>
					<th class="ver"><span><?= implode('</span> <span>', $map['ver'][$x]) ?></span></th>
				<? endfor ?>
				<th></th>
			</tr>
		</tbody>
	</table>

	<script>
	var states = ['', 'active', 'inactive'];
	document.querySelector('tbody').addEventListener('click', function(e) {
		if (e.target.nodeName == 'A') {
			e.preventDefault();

			var cell = e.target.parentNode;
			var state = cell.dataset.stateIndex || 0;
			state = (state + 1) % 3;
			cell.dataset.stateIndex = state;
			cell.dataset.state = states[state];
		}
	});
	</script>

</body>

</html>
<?php

function getLevel() {
	global $g_arrMaps;
	return isset($_GET['level'], $g_arrMaps[ (int) $_GET['level'] ]) ? (int) $_GET['level'] : 1;
}

function prepareLevel($level) {
	global $g_arrMaps;

	$map = $g_arrMaps[$level];

	$hor = prepareAxis($map, true);
	$ver = prepareAxis($map, false);

	return compact('map', 'hor', 'ver');
}

function prepareAxis($map, $hor) {
	$d1max = $hor ? count($map) : strlen($map[0]);
	$d2max = $hor ? strlen($map[0]) : count($map);

	$streak = false;
	$numbers = [];
	for ( $d1=0; $d1 < $d1max; $d1++ ) {
		if ($d1 > 0) {
			$streak = false;
			$numbers[] = '';
		}

		for ( $d2=0; $d2 < $d2max; $d2++ ) {
			$y = $hor ? $d1 : $d2;
			$x = $hor ? $d2 : $d1;

			if ( $map[$y][$x] == 'x' ) {
				if ( !$streak ) {
					$streak = true;
					$numbers[] = 0;
				}
				$numbers[ count($numbers)-1 ]++;
			}
			else {
				$streak = false;
			}
		}
	}

	$lines = [[]];
	foreach ( $numbers as $number ) {
		if ( !$number ) {
			$lines[] = [];
		}
		else {
			$lines[ count($lines)-1 ][] = $number;
		}
	}

	return $lines;
}
