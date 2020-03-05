<?php

require __DIR__ . '/inc.bootstrap.php';

$gameName = (int) basename($_SERVER['PHP_SELF']);

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title><?= $title ?> - EDITOR</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('gridgame.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset("$javascript.js") ?>"></script>
<style>
[data-type].active td + td {
	background: lime !important;
}
textarea {
	width: 100%;
	tab-size: 4;
	font-family: monospace;
}
</style>
</head>

<body class="builder <?= $bodyClass ?>">
<table class="outside">
	<tr>
		<td class="inside">
			<table class="inside" id="grid"></table>
		</td>
		<td>
			<div id="level-header"></div>
			<table class="inside" id="building-blocks"></table>
			<div id="level-sizes">
				<p>
					<button data-resize="-u">t&minus;</button>
					<button data-resize="+u">t+</button>
				</p>
				<p>
					<button data-resize="-r">r&minus;</button>
					<button data-resize="+r">r+</button>
				</p>
				<p>
					<button data-resize="-d">b&minus;</button>
					<button data-resize="+d">b+</button>
				</p>
				<p>
					<button data-resize="-l">l&minus;</button>
					<button data-resize="+l">l+</button>
				</p>
			</div>
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
	<p><textarea name="import" id="export-code" rows="10"></textarea></p>
</form>

<script>
var gameName = '<?= $gameName ?>';
var storageName = 'editor_' + gameName;
var gameNameTypeMap = <?= json_encode(@$nameTypeMap ?: new stdClass) ?>;

var objGame = new <?= $jsClass ?>Editor($('#grid'));
objGame.createEditor();
objGame.listenControls();

setTimeout(function() {
	objGame.restore(storageName);
});

function exportLevel() {
	return new Promise((resolve) => {
		try {
			var level = objGame.exportLevel();
			console.log('level 1', level);
			resolve(level);
		}
		catch ( ex ) {
			alert(ex);
			throw ex;
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
		console.log('level 2', level);
		var $code = $('#export-code');
		$code.value = JSON.stringify(level, Coords2D.jsonReplacer());
		if ( level.game && gameNameTypeMap[level.game] ) {
			$code.form.action = gameNameTypeMap[level.game] + '.php';
		}
		$code.form.submit();
	})
});

$('#btn-export').on('click', function(e) {
	e.preventDefault();

	exportLevel().then((level) => {
		var code = objGame.formatAsPHP(level);
		var codeTA = $('#export-code');
		codeTA.value = code.join('\n');
		codeTA.select();
	});
});
</script>
</body>

</html>

