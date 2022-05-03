<?php
// PROJECT L
// https://www.youtube.com/watch?v=cbKdA1lk0Cg

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Project L SOLO</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#333" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('projectl.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('projectl.js') ?>"></script>
</head>

<body>

<div id="grid">
	<div id="col-1-coins" class="col-header num-coins"><output>?</output></div>
	<div id="col-2-coins" class="col-header num-coins"><output>?</output></div>
	<div id="col-3-coins" class="col-header num-coins"><output>?</output></div>
	<div id="deck" class="num-targets">deck<br><output>?</output></div>
	<div id="targets"></div>
	<div id="oppo-targets" class="num-targets">oppo<br><output>?</output></div>
	<div id="oppo-coins" class="num-coins">oppo<br><output>?</output></div>
	<div id="player-targets" class="num-targets">you<br><output>?</output></div>
	<div id="hand"></div>
</div>

<div class="stones-wrapper">
	<button id="finish-round">
		Finish round (<span id="used-actions">?</span>/<span id="max-actions">?</span>)
	</button>
	<button id="start-master">
		<span class="start">Start master</span>
		<span class="end">End master</span>
	</button>
	<button id="take-stone">Take stone</button>
	<div id="stones"></div>
</div>

<script>
var objGame = new SoloProjectL($('#grid'), $('#stones'));
objGame.startGame();
objGame.listenControls();
</script>
