<?php
// MAMONO

require __DIR__ . '/inc.bootstrap.php';

$monsters = [
	'slime',
	'goblin',
	'lizard',
	'golem',
	'dragon',
	'demon',
	'ninja',
	'dragon_zombie',
	'satan',
];

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="theme-color" content="#333" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mamono sweeper</title>
<style>
* {
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}
html {
	background: black;
	font-family: sans-serif;
	font-size: 14px;
	color: white;
	user-select: none;

	--stats-height: 30px;
	--square: 26px;
}
body {
	height: 100vh;
	width: 100vw;
	overflow: hidden;
	touch-action: pan-x pan-y;
}
body,
body[data-size="huge"] {
	--bg: green;
	--border: lightgreen;
}
body[data-size="easy"] {
	--bg: #959900;
	--border: #bbc200;
}
body[data-size="normal"] {
	--bg: #422900;
	--border: #7a4b00;
}
body[data-size="extreme"] {
	--bg: #2b00bb;
	--border: #4f1aff;
}
body[data-size="blind"] {
	--bg: #454545;
	--border: #6b6b6b;
}

#stats-bar {
	height: var(--stats-height);
	line-height: var(--stats-height);
	background-color: black;
	font-weight: bold;
	font-family: monospace;
	text-transform: uppercase;
	white-space: nowrap;
}
body.happening #stats-bar {
	animation: happening 0.5s 2;
}
#stats-bar .stat-time .label {
	display: none;
}

#ms {
	--ms-height: 100vh;
	height: calc(var(--ms-height) - var(--stats-height));
	width: 100vw;
	overflow: scroll;
	-webkit-overflow-scrolling: auto;
	overscroll-behavior: none;
}
body.mobile #ms {
	--ms-height: 90vh;
}
#ms .padding {
	border: solid 20px black;
	width: calc(40px + var(--w) * (1px + var(--square)));
	height: calc(40px + var(--h) * (1px + var(--square)));
}
table {
	border-spacing: 0;
	font-size: inherit;
	border: solid 0 var(--border);
	border-width: 0 1px 1px 0;
}
td {
	border: solid 1px black;
	border-top-color: var(--border);
	border-left-color: var(--border);
	width: var(--square);
	height: var(--square);
	background: black;
	color: white;
	font-weight: bold;
	text-align: center;
	padding: 0;
	line-height: 1;
}
td.closed,
td[data-monster]:not(.closed) {
	cursor: pointer;
}

td[data-monster] { background: none center center no-repeat; }
<? foreach ($monsters as $m => $img): ?>
	td[data-monster="<?= $m+1 ?>"] { background-image: url(/images/mamono/<?= $img ?>.png); }
<? endforeach ?>

td.closed {
	background: none var(--bg);
}
td[data-monster].show-adjacents {
	background: none black;
	color: red;
}
td.closed span,
td[data-monster]:not(.show-adjacents) span {
	visibility: hidden;
}

img.preload { visibility: hidden; position: absolute; }

@keyframes happening {
	0% {
		background: black;
		color: white;
	}
	50% {
		background: white;
		color: black;
	}
	100% {
		background: black;
		color: white;
	}
}
</style>
<? include 'tpl.onerror.php' ?>
</head>

<body class="<?= is_mobile() ? 'mobile' : '' ?>">

<div id="stats-bar">
	<select id="select-size"></select>
	<span id="stats"></span>
	<span id="monsters" hidden></span>
</div>

<div id="ms">
	<div class="padding">
		<table></table>
	</div>
</div>

<? foreach ($monsters as $m => $img): ?>
	<img class="preload" src="/images/mamono/<?= $img ?>.png" />
<? endforeach ?>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('mamono.js') ?>"></script>
<script>
objGame = new Mamono($('#ms table'));
objGame.loadAnySaved() || objGame.createMap('normal');
objGame.listenControls();
</script>
</body>

</html>

