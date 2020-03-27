<?php
// Tetravex

require __DIR__ . '/inc.bootstrap.php';

if ( isset($_GET['image']) ) {
	$sides = (string) $_GET['image'];
	if ( !preg_match('/^[0-9]{4}$/', $sides) ) {
		exit('Invalid tile!');
	}

	$g_arrColors = array(
		0 => '0,0,0', // black
		1 => '139,69,19', // brown
		2 => '255,0,0', // red
		3 => '255,140,0', // orange
		4 => '255,255,0', // yellow
		5 => '50,205,50', // lime
		6 => '70,130,180', // blue
		7 => '160,32,240', // purple
		8 => '190,190,190', // gray
		9 => '255,255,255', // white
	);

	$areDark = function($a, $b) use ($sides) {
		return (int) ($sides[$a] == $sides[$b] && in_array($sides[$a], [0, 1, 6]) && in_array($sides[$b], [0, 1, 6]));
	};
	$isDark = function($a) use ($sides) {
		return (int) in_array($sides[$a], [0, 1, 6]);
	};

	header('Content-type: image/svg+xml; charset=utf-8');

	?>
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
		<style>
		line { stroke-width: 2; stroke: black; }
		line.d1 { stroke: white; }
		text { font-size: 35px; fill: black; }
		text.d1 { fill: white; }
		</style>

		<!-- up -->
		<polygon points="0 0 50 50 100 0" fill="rgb(<?= $g_arrColors[ $sides[0] ] ?>)" />
		<text x="45" y="30" class="d<?= $isDark(0) ?>"><?= $sides[0] ?></text>
		<!-- right -->
		<polygon points="100 0 50 50 100 100" fill="rgb(<?= $g_arrColors[ $sides[1] ] ?>)" />
		<text x="75" y="60" class="d<?= $isDark(1) ?>"><?= $sides[1] ?></text>
		<!-- down -->
		<polygon points="100 100 50 50 0 100" fill="rgb(<?= $g_arrColors[ $sides[2] ] ?>)" />
		<text x="45" y="90" class="d<?= $isDark(2) ?>"><?= $sides[2] ?></text>
		<!-- left -->
		<polygon points="0 100 50 50 0 0" fill="rgb(<?= $g_arrColors[ $sides[3] ] ?>)" />
		<text x="5" y="60" class="d<?= $isDark(3) ?>"><?= $sides[3] ?></text>

		<line x1="100" y1="0" x2="50" y2="50" class="d<?= $areDark(0, 1) ?>" />
		<line x1="100" y1="100" x2="50" y2="50" class="d<?= $areDark(1, 2) ?>" />
		<line x1="0" y1="100" x2="50" y2="50" class="d<?= $areDark(2, 3) ?>" />
		<line x1="0" y1="0" x2="50" y2="50" class="d<?= $areDark(3, 0) ?>" />
	</svg>
	<?php

	exit;
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Tetravex</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('tetravex.js') ?>"></script>
<style>
:root {
	--inside-cell: 45px;
}
html,
body {
	touch-action: none;
}
#tetracont table {
	border-collapse	: collapse;
}
#tetracont table td {
	border			: solid 1px #ccc;
	padding			: 0;
}
#tetracont table img {
	width			: var(--inside-cell);
	height			: var(--inside-cell);
	cursor			: pointer;
	display			: block;
}
#tetracont table img[data-tile=""] {
	opacity			: 0;
}
#tetracont table .selected img {
	opacity			: 0.5;
	outline			: solid 1px black;
}
</style>
</head>

<body>
<table id="tetracont" cellpadding="4">
	<tr>
		<th>
			<select id="size-selector">
				<option value="">--</option>
				<? foreach (range(2, 6) AS $iSize): ?>
					<option value="<?= $iSize ?>">Size <?= $iSize ?> board</option>
				<? endforeach ?>
			</select>
		</th>
		<td></td>
		<th><span id="stats-time">-</span> / <span id="stats-moves"></span> moves</th>
	</tr>
	<tr>
		<td style="padding-right: 0"><table><tbody id="solution"></tbody></table></td>
		<td style="padding: 0; font-size: 20px; font-weight: bold">◀</td>
		<td style="padding-left: 0"><table><tbody id="available"></tbody></table></td>
	</tr>
</table>

<p>
	<button id="restart">Restart</button>
</p>

<script>
var objGame = new Tetravex($('#solution'), $('#available'));
objGame.startGame(3);
objGame.listenControls();
</script>

</body>

</html>
