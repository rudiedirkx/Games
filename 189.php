<?php
// 0h n0

require __DIR__ . '/inc.bootstrap.php';

$grid = [
	' 6      7',
	'   6 67x ',
	' 3  3   8',
	'1 97 5   ',
	'    x    ',
	'  9    5 ',
	' 5       ',
	'     3   ',
	'549 7x3x ',
];
$grid = [
	'2   ',
	'1  2',
	' 33 ',
	'x1  ',
];
$grid = [
	'2 3  ',
	'4    ',
	'  55x',
	'xx  1',
	'  2  ',
];
$grid = [
	'     7 x',
	' x  7 7 ',
	'   33   ',
	'4       ',
	' 885 x 5',
	'    xx 5',
	'3    4 7',
	'4    3  ',
];

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>0h n0</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
img.loader {
	margin-left: .5em;
	height: 1.4em;
}

table {
	border-spacing: 4px;
	user-select: none;
}
td {
	border: 0;
	padding: 0;
	text-align: center;
	vertical-align: middle;
}
td span {
	display: block;
	width: 30px;
	height: 30px;
	line-height: 30px;
	background-color: #eee;
	border-radius: 15px;
}
td[data-closed] span {
	background-color: #b10000;
}
td.closed span {
	background-color: red;
}
td[data-required] span,
td.active span {
	background-color: #86c5da;
	color: white;
	font-weight: bold;
}
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('ohno.js') ?>"></script>
</head>

<body>

<table class="inside" id="grid"></table>

<p>
	<button id="cheat">Cheat</button>
</p>

<script>
objGame = new Ohno($('#grid'));
objGame.createEmpty();
objGame.listenControls();
setTimeout(function() {
	objGame.createMap(<?= json_encode($grid) ?>);
}, 300);
</script>
</body>

</html>
