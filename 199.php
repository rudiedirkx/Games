<?php
// TICKET TO RIDE
// https://www.youtube.com/watch?v=EU6JBeIe390

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Ticket To Ride</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#333" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('tickettoride.js') ?>"></script>
<style>
canvas {
	background-color: #eee;
}
</style>
</head>

<body class="solo">

<canvas></canvas>

<script>
var objGame = new TicketToRide($('canvas'));
objGame.startGame();
objGame.listenControls();
objGame.startPainting();
</script>
