<?php

require __DIR__ . '/inc.bootstrap.php';
require "$levels.php";

if ( isset($_REQUEST['load_map']) ) {
	$iLevel = $_REQUEST['load_map'];
	if ( !isset($g_arrLevels[$iLevel]) ) {
		exit(json_encode(array(
			'error' => "Level $iLevel doesn't exist.",
		)));
	}

	$arrLevel = $g_arrLevels[$iLevel];

	exit(json_encode(['level' => $iLevel, 'levels' => count($g_arrLevels)] + $arrLevel));
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title><?= $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('gridgame.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset("$javascript.js") ?>"></script>
</head>

<body class="<?= $bodyClass ?>">
<img id="loading" alt="loading" src="images/loading.gif" />

<? if (isset($explanation)): ?>
	<p><?= do_html($explanation) ?></p>
<? endif ?>

<table class="outside">
	<tr>
		<td colspan="2" class="level">
			LEVEL <span id="stats-level">0</span> / <span id="stats-levels">0</span>
		</td>
	</tr>
	<tr>
		<td class="inside">
			<table class="inside" id="grid"></table>
		</td>
		<td>
			<div id="level-header"></div>
			<div id="level-nav">
				<a href="#" onclick="return objGame.prevLevel(), false">&lt;&lt;</a>
				&nbsp;
				<a href="#" onclick="return objGame.nextLevel(), false">&gt;&gt;</a><br />
				<br />
			</div>
			<a href="#" onclick="return objGame.restartLevel(), false">restart</a><br />
			<div id="undo-div">
				<br />
				<a href="#" onclick="return objGame.undoLastMove(), false">undo</a>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2" id="stats"></td>
	</tr>
</table>

<? if (file_exists($editorFile = ((int) basename($_SERVER['SCRIPT_NAME']) . 'B.php'))): ?>
	<p><a href="<?= $editorFile ?>">Create your own level</a></p>
<? endif ?>

<script>
var objGame = new <?= $jsClass ?>($('#grid'));
<? if ( isset($_POST['import']) ): ?>
	objGame.loadCustomMap(<?= json_encode(json_decode($_POST['import'])) ?>);
<? else: ?>
	objGame.loadLevel(<?= key($g_arrLevels) ?>);
<? endif ?>
objGame.listenAjax();
objGame.listenControls();
</script>
</body>

</html>
