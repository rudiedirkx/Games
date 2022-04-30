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

<div id="pieces"></div>

<script>
var objGame = new SoloProjectL($('#grid'));
objGame.startGame();
objGame.listenControls();
</script>
