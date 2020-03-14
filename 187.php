<?php
// BLACKBOX (JS)

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Blackbox</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="<?= html_asset('blackbox.css') ?>" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('blackbox.js') ?>"></script>
</head>

<body style="padding: 8px">

<table id="blackbox" style="margin: 0; width: auto; height: auto"></table>

<p>
	<span id="stats"></span>
</p>
<p>
	Size: <select id="board-size"></select>
	Atoms: <select id="board-atoms"></select>
	<button id="newgame">New game</button>
	<button id="reveal">Show atoms</button>
</p>

<script>
objGame = new Blackbox($('#blackbox'));
objGame.createMap();
objGame.listenControls();
</script>
</body>

</html>
