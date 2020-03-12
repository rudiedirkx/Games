<?php
// MARBLES

require __DIR__ . '/inc.bootstrap.php';

?>
<!DOCTYPE>
<html>

<head>
<meta charset="utf-8" />
<title>Marbles</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
#frame {
	display: flex;
	flex-flow: row nowrap;
	justify-content: flex-start;
	max-width: 480px;
}
#frame .column {
	flex: 0 1 60px;
}
#frame div.block {
	outline: solid 1px #555;
	height: 0;
	padding-top: 100%;
	cursor: pointer;
}
div[data-t="1"] { background-color: red; }
div[data-t="2"] { background-color: green; }
div[data-t="3"] { background-color: blue; }
div[data-t="4"] { background-color: money; }
</style>
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('marbles.js') ?>"></script>
</head>

<body>
<div id="frame"></div>

<p>
	<button id="newgame">New game</button>
</p>

<script>
objGame = new Marbles($('#frame'));
objGame.createMap(<?= $_GET['level'] ?? 1 ?>);
objGame.listenControls();
</script>
</body>

</html>
