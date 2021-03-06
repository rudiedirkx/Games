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
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mamono sweeper</title>
<style>
* {
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}
html, body {
	height: 100%;
	width: 100%;
	overflow: hidden;
}
html {
	background: black;
	font-family: sans-serif;
	font-size: 14px;
	color: white;
	user-select: none;

	--stats-height: 30px;
}
body.happening {
	animation: happening 0.5s 2;
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
	font-weight: bold;
	font-family: monospace;
	text-transform: uppercase;
}
#stats-bar .stat-time .label {
	display: none;
}

#ms {
	height: calc(100% - var(--stats-height));
	width: 100vw;
	overflow: auto;
}
#ms .padding {
	padding: 50px;
	width: fit-content;
}
table {
	border-spacing: 0;
	font-size: inherit;
	border: solid 0 black;
	border-width: 0 5px 5px 3px;
	width: calc(var(--w) * 23px);
	height: calc(var(--h) * 23px);
}
td {
	border: solid 1px black;
	border-top-color: var(--border);
	border-left-color: var(--border);
	width: 22px;
	height: 22px;
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

span.adjacents,
span.empty {
	pointer-events: none;
	display: block;
	width: 22px;
	height: 22px;
	line-height: 22px;
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

<body>

<div id="stats-bar">
	<select id="select-size"></select>
	<span id="stats"></span>
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
objGame.createMap('normal');
objGame.listenControls();
</script>
</body>

</html>

