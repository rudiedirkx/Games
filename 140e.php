<?php
// THE BOX | EDITOR

require 'inc.functions.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>THE BOX - EDITOR</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<link rel="stylesheet" href="<?= html_asset('gridgame.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset("thebox.js") ?>"></script>
<style>
[data-type].active {
	background: lime;
}
textarea {
	tab-size: 4;
}
</style>
</head>

<body class="thebox">
<table class="outside">
	<tr>
		<td class="inside">
			<table class="inside" id="grid"></table>
		</td>
		<td>
			<table class="inside">
				<tr data-type="wall" class="active">
					<td class="wall wall1"></td>
					<td>Wall</td>
				</tr>
				<tr data-type="target">
					<td class="target"></td>
					<td>Target</td>
				</tr>
				<tr data-type="box">
					<td class="box"></td>
					<td>Box</td>
				</tr>
				<tr data-type="pusher">
					<td class="pusher"></td>
					<td>Pusher</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<p><button id="btn-export">Export</button></p>

<p><textarea id="export-code" rows="15" cols="30"></textarea></p>

<script>
var objGame = new TheBoxEditor();
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

	var code = [];
	code.push('\t[');
	code.push("\t\t'map' => [");
	r.each(level.map, row => code.push("\t\t\t'" + row + "',"));
	code.push("\t\t],");
	code.push("\t\t'pusher' => [" + level.pusher.join(', ') + "],");
	code.push("\t\t'boxes' => [");
	r.each(level.boxes, box => code.push("\t\t\t[" + box.join(', ') + "],"));
	code.push("\t\t],");
	code.push('\t],');
	code.push('');
	code.push('');

	$('#export-code').value = code.join('\n');
});
</script>
</body>

</html>
