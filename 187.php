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

<body>

<table id="blackbox"></table>

<p style="text-align: center">
	<span id="stats"></span>
</p>
<p style="text-align: center">
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
