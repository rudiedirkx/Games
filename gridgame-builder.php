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
			<table class="inside" id="blocks">
				<? foreach ($types as $type => $label):
					list($label, $attrs) = array_merge((array) $label,  [[]]);
					?>
					<tr data-type="<?= $type ?>" class="<?= $type == 'wall' ? 'active' : '' ?>">
						<td class="<?= $type ?>"<?= html_attributes($attrs) ?>><span></span></td>
						<td style="text-align: left"><?= $label ?></td>
					</tr>
				<? endforeach ?>
			</table>
		</td>
	</tr>
</table>

<p>
	<button id="btn-remember">Remember</button>
	&nbsp;
	<button id="btn-clear">Clear</button>
	&nbsp;
	<button id="btn-play">Play</button>
	&nbsp;
	<button id="btn-export">Export</button>
</p>

<form method="post" action="<?= $gameName ?>.php">
	<p><textarea name="import" id="export-code" rows="15" cols="30"></textarea></p>
</form>

<script>
var gameName = '<?= $gameName ?>';
var storageName = 'editor_' + gameName;

var objGame = new <?= $jsClass ?>Editor();
objGame.createMap(16, 16);
objGame.listenControls();

setTimeout(function() {
	var saved = localStorage.getItem(storageName);
	if ( saved ) {
		objGame.m_objGrid.setHTML(saved);
	}
});

function exportLevel() {
	return new Promise(resolve => {
		try {
			resolve(objGame.exportLevel());
		}
		catch ( ex ) {
			alert(ex);
		}
	});
}

$('#btn-remember').on('click', function(e) {
	e.preventDefault();

	localStorage.setItem(storageName, objGame.m_objGrid.getHTML());
});

$('#btn-clear').on('click', function(e) {
	e.preventDefault();

	objGame.m_objGrid.getElements('td').prop('className', '');
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
		var code = objGame.formatLevelCode(level);
		$('#export-code').value = code.join('\n');
	})
});
</script>
</body>

</html>

