<?php
// PYTHAGOREA

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>PYTHAGOREA</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<link rel="stylesheet" href="<?= html_asset('pythagorea.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('pythagorea.js') ?>"></script>
<style>
canvas {
	background-color: #eee;
	touch-action: none;
}
</style>
</head>

<body class="pythagorea">

<canvas></canvas>

<p>
	<button id="undo">Undo</button>
</p>

<script>
var objGame = new Pythagorea(document.querySelector('canvas'));
objGame.startPainting();
objGame.listenControls();

objGame.addVertex(new Vertex(0, 0));
objGame.addVertex(new Vertex(2, 1));
objGame.addEdge(new Edge(...objGame.vertices.slice(-2)));
// objGame.addVertex(new Vertex(5, 0));
// objGame.addVertex(new Vertex(5, 1));
// objGame.addEdge(new Edge(...objGame.vertices.slice(-2)));
</script>
