<?php
// Pixelus

require 'inc.functions.php';
require '160_levels.php';

define('S_NAME', 'pxl');

/** START GAME **/
if ( isset($_REQUEST['get_map']) ) {
	$level = (int)$_REQUEST['get_map'];

	if ( !isset($g_arrLevels[$level]) ) {
		exit('Invalid level ['.$level.']');
	}

	$arrLevel = $g_arrLevels[$level];

	exit(json_encode(array(
		'level'		=> $level,
		'map'		=> $arrLevel['map'],
		'stones'	=> $arrLevel['stones'],
	)));
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Pixelus</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<link rel="stylesheet" href="<?= html_asset('games.css') ?>" />
<style>
#loading { border:medium none; height:100px; left:50%; margin-left:-50px; margin-top:-50px; position:absolute; top:50%; visibility:hidden; width:100px; }
#map-container span { display: block; height:100%; width: 100%; border-radius: 50px; }
#map-container td.target { background-color:#87cefa; }
#map-table #map-container td.stone span { background-color: #8b4513; }
#map-table #map-container td.valid { background-color:green; }
#map-table #map-container td.invalid { background-color:red; }
#map-container.actionless td.stone { cursor: pointer; }
</style>
</head>

<body>
<img id="loading" alt="loading" src="images/loading.gif" />

<table border="1" cellpadding="15" cellspacing="0">
<tr>
	<th class="pad">LEVEL <span id="stats-level">0</span></th>
</tr>
<tr>
	<td style="padding: 3px">
		<table id="map-table">
			<tbody id="map-container"></tbody>
			<tfoot>
				<tr>
					<th class="pad" colspan="30">
						Stones left: <span id="stats-stones">0</span><br />
						Moves: <span id="stats-moves">0</span>
					</th>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" align="left" class="pad">
		<a href="#" id="btn-prev-level">&lt;&lt;</a>
		&nbsp;
		<a href="#" id="btn-next-level">&gt;&gt;</a>
		&nbsp;
		<a href="#" id="btn-restart-level">restart</a>
	</td>
</tr>
</table>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('games.js') ?>"></script>
<script src="<?= html_asset('160.js') ?>"></script>
<script>
game = new Pixelus()
</script>
</body>

</html>
