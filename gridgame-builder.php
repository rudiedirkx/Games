<?php

require 'inc.functions.php';

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
	tab-size: 4;
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
			<table class="inside">
				<? foreach ($types as $type => $label): ?>
					<tr data-type="<?= $type ?>" class="<?= $type == 'wall' ? 'active' : '' ?>">
						<td class="<?= $type ?>"></td>
						<td><?= $label ?></td>
					</tr>
				<? endforeach ?>
			</table>
		</td>
	</tr>
</table>

<p><button id="btn-export">Export</button></p>

<p><textarea id="export-code" rows="15" cols="30"></textarea></p>

<script>
var objGame = new <?= $jsClass ?>Editor();
objGame.createMap(10, 10);
objGame.listenControls();

$('#btn-export').on('click', function(e) {
	e.preventDefault();

	var level;
	try {
		level = objGame.exportLevel();
	}
	catch ( ex ) {
		return alert(ex);
	}

	var code = objGame.formatLevelCode(level);

	$('#export-code').value = code.join('\n');
});
</script>
</body>

</html>

