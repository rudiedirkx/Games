<?php

require 'inc.functions.php';

$gameName = (int) basename($_SERVER['PHP_SELF']);

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title><?= $title ?> - EDITOR</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<link rel="stylesheet" href="<?= html_asset('gridgame.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset("$javascript.js") ?>"></script>
<style>
[data-type].active {
	background: lime;
}
textarea {
	width: 100%;
	tab-size: 4;
	font-family: monospace;
}
</style>
</head>

<body class="<?= $bodyClass ?>">
<table class="outside">
	<tr>
		<td class="inside">
			<table class="inside" id="grid"></table>
		</td>
		<td>
			<div id="level-header"></div>
			<table class="inside" id="building-blocks"></table>
		</td>
	</tr>
</table>

<p>
	<button id="btn-remember">Remember</button>
	&nbsp;
	<button id="btn-forget">Forget</button>
	&nbsp;
	<button id="btn-clear">Clear</button>
	&nbsp;
	<button id="btn-play">Play</button>
	&nbsp;
	<button id="btn-export">Export</button>
</p>

<form method="post" action="<?= $gameName ?>.php">
	<p><textarea name="import" id="export-code" rows="15"></textarea></p>
</form>

<script>
var gameName = '<?= $gameName ?>';
var storageName = 'editor_' + gameName;

var objGame = new <?= $jsClass ?>Editor($('#grid'));
objGame.createEditor();
objGame.listenControls();

setTimeout(function() {
	objGame.restore(storageName);
});

function exportLevel() {
	return new Promise(resolve => {
		try {
			var level = objGame.exportLevel();
			console.log(level);
			resolve(level);
		}
		catch ( ex ) {
			alert(ex);
		}
	});
}

$('#btn-remember').on('click', function(e) {
	e.preventDefault();

	objGame.remember(storageName);
});

$('#btn-forget').on('click', function(e) {
	e.preventDefault();

	objGame.forget(storageName);
	location.reload();
});

$('#btn-clear').on('click', function(e) {
	e.preventDefault();

	objGame.clear();
});

$('#btn-play').on('click', function(e) {
	e.preventDefault();

	exportLevel().then((level) => {
		var $code = $('#export-code');
		$code.value = JSON.stringify(level, (k, v) => v instanceof Coords2D ? [v.x, v.y] : v);
		$code.form.submit();
	})
});

$('#btn-export').on('click', function(e) {
	e.preventDefault();

	exportLevel().then((level) => {
		var code = objGame.formatAsPHP(level);
		$('#export-code').value = code.join('\n');
	})
});
</script>
</body>

</html>

