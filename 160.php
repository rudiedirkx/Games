<?php
// Pixelus

define('S_NAME', 'pxl');

$g_arrLevels = array(
	1 => array(
		'map' => array(
			'xxx xx',
			'o    x',
			'x    x',
			'x    x',
			'x    o',
			'xoxxx ',
		),
		'stones' => 3,
	),
	2 => array(
		'map' => array(
			'         xx',
			'        xxx',
			'     xxx x ',
			'   ox     x',
			'  o       x',
			' o        x',
			'o     xx  x',
			'x      x  x',
			'x         x',
			'x         x',
			'x         o',
			' oox     x ',
			'  ox    x  ',
			'   xxxxx   ',
		),
		'stones' => 8,
	),
	3 => array(
		'map' => array(
			'        xx    ',
			'       x  x   ',
			'  xx   x   x  ',
			' x  o  x    x ',
			'o    x x    x ',
			'x     x    x  ',
			' x        x   ',
			'  oo       xx ',
			'    o        o',
			'   x         o',
			'  x    xx   x ',
			'  x    x xxx  ',
			'   x   x x    ',
			'    xxx  x    ',
			'         x    ',
			'         xx   ',
		),
		'stones' => 7,
	),
	4 => array(
		'map' => array(
			'    xxxo    ',
			'   x    o   ',
			'  x      o  ',
			' x      x o ',
			' x     x  x ',
			'x      x   x',
			'x       x  x',
			'x     xx x x',
			'x    x    xx',
			'x    x     x',
			' x        x ',
			'  x      x  ',
			'   x    x   ',
			'    xxxx    ',
		),
		'stones' => 4,
	),
	5 => array(
		'map' => array(
			'           oooo     ',
			'        ooo   o     ',
			'   x  oo     o      ',
			'   x x      o       ',
			'   x x      o       ',
			'   x x     x  xx    ',
			'xx x xx    xxxx     ',
			' xxxxxxxx  x        ',
			'  xxxxxxxxx         ',
			'  xx                ',
			'  x                 '
		),
		'stones' => 13,
	),
	6 => array(
		'map' => array(
			'          xxo       ',
			' ox      x  o       ',
			'x  xxxxxx   o       ',
			'x          x        ',
			' o         x        ',
			'  x        x        ',
			'  o   o     o       ',
			' o           x      ',
			' x           x      ',
			' x          xx      ',
			'  oxx       xx      ',
			'     xxxxxxxx       ',
		),
		'stones' => 10,
	),
	7 => array(
		'map' => array(
			'             x  ',
			'            x x ',
			'           x   x',
			'  xxxxxxxxx  xx ',
			' x          x   ',
			'x x         x   ',
			'x  x  xxxxx x   ',
			'x  xxx    xxx   ',
			'  oo  o  oo  o  ',
			'  o    o o    o ',
			' o      o      x',
		),
		'stones' => 12,
	),
	8 => array(
		'map' => array(
			'     xxxxx    ',
			'   xx     ox  ',
			'  o         o ',
			' x          x ',
			'x          x x',
			'x        x   x',
			'x       xx   x',
			'x            x',
			'x   x        x',
			'x       x   x ',
			' x         x  ',
			'  o      oo   ',
			'   oxxxxx     ',
		),
		'stones' => 7,
	),
	9 => array(
		'map' => array(
			'      xx       ',
			'  xx  x x  oo  ',
			'   xoxx  xx o  ',
			'    o      o   ',
			'xxxx  xxx x    ',
			' xx  xx x  xxx ',
			'   x  xxx     x',
			'   o        xx ',
			'  x oxx xx  x  ',
			'  x o x x x x  ',
			'   x  x x x x  ',
			'      x x  xx  ',
			'       x       ',
		),
		'stones' => 8,
	),
	10 => array(
		'map' => array(
			'      x       ',
			'oxxxxxxxxxxox ',
			'              ',
			'       xxxxoxx',
			'              ',
			'              ',
			'              ',
			'        xx o  ',
			' xxxxxxxxxx x ',
			'         oo x ',
			'o       x   x ',
			' xxxxxxx    x ',
			'  xxxxxxxxxo  ',
		),
		'stones' => 8,
	),
	11 => array(
		'map' => array(
			'           o    ',
			'          o     ',
			'         o      ',
			'  o     o     o ',
			' x   x x   x o  ',
			' x  x x  x x x o',
			' x x x  x x x x ',
			' x x x x x  x x ',
			'x  x xx x  x xx ',
			'x x x x x  x x  ',
			'x x x x x  x x  '
		),
		'stones' => 8,
	),
	12 => array(
		'map' => array(
			'     xxxx      ',
			'   xx    x     ',
			'  xxx    xxx   ',
			' xxxxx  x   xxx',
			'xxxx    x      ',
			'   xx  x   xxxx',
			'    xxx   x    ',
			'    xx   x     ',
			'     o   o     ',
			'     o    o    ',
			'     oo    o   ',
			'      oo    xxx',
			'       oo      ',
			'        oo     ',
			'          o    ',
		),
		'stones' => 14,
	),
	13 => array(
		'map' => array(
			'  xxx    ooo   ',
			'   x x  x x    ',
			'    xx  xo  xx ',
			'xxx        xxx ',
			'xxx         x  ',
			'  xx           ',
			'    o    o  xx ',
			'            xx ',
			'            xx ',
			'            xx ',
			'           ox  ',
			'       x   ox  ',
			'      ox   oxxx',
			'            x  ',
			'            x  ',
		),
		'stones' => 10,
	),
);

/** START GAME **/
if ( isset($_REQUEST['get_map']) ) {
	$level = (int)$_REQUEST['get_map'];

	if ( !isset($g_arrLevels[$level]) ) {
		exit('Invalid level ['.$level.']');
	}

	$arrLevel = $g_arrLevels[$level];

	reset_game($level);

	exit(json_encode(array(
		'level'		=> $_SESSION[S_NAME]['level'],
		'map'		=> $_SESSION[S_NAME]['map'],
		'stones'	=> $_SESSION[S_NAME]['stones'],
	)));
}

?>
<!doctype html>
<html lang="en">

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, maximum-scale=1.0, minimum-scale=1.0" />
	<meta charset="utf-8" />
	<title>Pixelus</title>
	<link rel="stylesheet" href="games.css" />
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
	<td></td>
</tr>
<tr>
	<td class="pad" style="padding-top:0;">
	<table id="map-table">
		<tbody id="map-container"></tbody>
		<tfoot>
			<tr><th class="pad" colspan="30">Stones left: <span id="stats-stones">0</span><br />Moves: <span id="stats-moves">0</span></th></tr>
		</tfoot>
	</table></td>
	<td valign="top" align="left" class="pad">
		<a href="#" id="btn-load-level">load level #</a><br />
		<br />
		<a href="#" id="btn-prev-level">&lt;&lt;</a> &nbsp; <a href="#" id="btn-next-level">&gt;&gt;</a><br />
		<br />
		<a href="#" id="btn-restart-level">restart</a><br />
		<br />
		<a href="?action=reset">reset</a><br />
		<br />
	</td>
</tr>
<tr>
	<th colspan="2" class="pad" id="stack_message">no stack messages</th>
</tr>
</table>

<script src="/js/mootools_1_11.js"></script>
<script src="games.js"></script>
<script src="160.js"></script>
<script>
Ajax.setGlobalHandlers({
	onStart : function() {
		$('loading').style.visibility = 'visible';
	},
	onComplete: function() {
		if( !Ajax.busy ) {
			$('loading').style.visibility = 'hidden';
		}
	}
});

//var objPixelus = new Pixelus(1);
game = new Pixelus
</script>
</body>

</html>
<?php

function reset_game( $f_iLevel = 0 ) {
	global $g_arrLevels;
	$arrLevel = $g_arrLevels[$f_iLevel];

	$_SESSION[S_NAME]['play']		= true;
	$_SESSION[S_NAME]['level']		= $f_iLevel;
	$_SESSION[S_NAME]['map']		= $arrLevel['map'];
	$_SESSION[S_NAME]['stones']		= $arrLevel['stones'];

}
