<?php
// MINESWEEPER

if ( isset($_GET['source']) ) {
	highlight_file(__FILE__);
	exit;
}

// Must have a MS session!
if ( !isset($_GET['session']) ) {
	$session = mt_rand();

	$qs = http_build_query($_GET);
	$location = $qs ? '?' . $qs . '&session=' . $session : '?session=' . $session;

	header('Location: ' . $location);
	exit;
}

define('SESSION', $_GET['session']);


session_start();

require_once('connect.php');
define('S_NAME', 'ms2');

$g_szDefaultField = "b";
$_FIELDS = array(
	"a" => array(
		"name"	=> "Beginner",
		"sides" => array(9, 9),
		"mines" => 10,
	),
	"b" => array(
		"name"	=> "Intermediate",
		"sides" => array(16, 16),
		"mines" => 40,
	),
	"c" => array(
		"name"	=> "Expert",
		"sides" => array(30, 16),
		"mines" => 99,
	),
);



// Start new game //
if ( isset($_POST['fetch_map'], $_POST['field']) ) {
	if ( !isset($_FIELDS[$_POST['field']]) ) {
		exit(json_encode(array('error' => 'Invalid field!')));
	}

	$arrLevel = $_FIELDS[$_POST['field']];
	$_SESSION[S_NAME]['sessions'][SESSION]['map'] = create_map($arrLevel['sides'][0], $arrLevel['sides'][1], $arrLevel['mines']);
	$_SESSION[S_NAME]['sessions'][SESSION]['starttime'] = null;
	$_SESSION[S_NAME]['sessions'][SESSION]['field'] = $_POST['field'];
	$_SESSION[S_NAME]['sessions'][SESSION]['mines'] = (int)$arrLevel['mines'];
	$arrMap = array(
		'field'	=> $_POST['field'],
		'size'	=> array(
			'x'		=> $arrLevel['sides'][0],
			'y'		=> $arrLevel['sides'][1],
		),
		'mines'	=> $arrLevel['mines'],
	);

	header('Content-type: text/json');
	exit(json_encode($arrMap));
}

// Click on field //
else if ( isset($_POST['click'], $_POST['x'], $_POST['y']) ) {
	$arrUpdates = array();

	$f_x = (int)$_POST['x'];
	$f_y = (int)$_POST['y'];

	if ( !isset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y][$f_x]) ) {
		header('Content-type: text/json');
		exit(json_encode(array('updates' => array(), 'msg' => '', 'gameover' => false)));
//		exit(json_encode(array('error' => 'Invalid coordinate!')));
	}

	if ( null === $_SESSION[S_NAME]['sessions'][SESSION]['starttime'] ) {
		$_SESSION[S_NAME]['sessions'][SESSION]['starttime'] = time();
	}
	$bGameOver = false;

	$f = $_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y][$f_x];
	if ( 'm' === $f ) {
		$bGameOver = true;
		foreach ( $_SESSION[S_NAME]['sessions'][SESSION]['map'] AS $y => $row ) {
			foreach ( $row AS $x => $c ) {
				if ( 'm' === $c ) {
					$arrUpdates[] = array($x, $y, 'm');
				}
			}
		}
		$arrUpdates[] = array($f_x, $f_y, 'x');
	}
	else if ( 0 === $f ) {
		$arrUpdates[] = array($f_x, $f_y, $f);
		// Find surrounders, surrounders, surrounders, etc
		click_on_surrounders($f_x, $f_y);
	}
	else {
		$arrUpdates[] = array($f_x, $f_y, $f);
	}
	unset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y][$f_x]);
	$iClosed = 0;
	foreach ( $_SESSION[S_NAME]['sessions'][SESSION]['map'] AS $r ) {
		$iClosed += count($r);
	}
	$szMsg = '';
	if ( $iClosed === $_SESSION[S_NAME]['sessions'][SESSION]['mines'] ) {
		$bGameOver = true;
		$arrLevel = $_FIELDS[$_SESSION[S_NAME]['sessions'][SESSION]['field']];
		$_SESSION[S_NAME]['name'] = isset($_SESSION[S_NAME]['name']) ? $_SESSION[S_NAME]['name'] : 'Anonymous';
		$playtime = time()-$_SESSION[S_NAME]['sessions'][SESSION]['starttime'];
		mysql_query("INSERT INTO minesweeper (name,size_x,size_y,mines,playtime,utc, ip, user_agent) VALUES ('".addslashes($_SESSION[S_NAME]['name'])."',".$arrLevel['sides'][0].",".$arrLevel['sides'][1].",".$arrLevel['mines'].",".$playtime.",".time().", '".addslashes($_SERVER['REMOTE_ADDR'])."', '".addslashes($_SERVER['HTTP_USER_AGENT'])."');");
		$m = floor($playtime / 60);
		$s = $playtime % 60;
		$szMsg = 'LEVEL "'.$arrLevel['name'].'" ACHIEVEMENT SAVED ('.( 0 < $m ? $m.'m ' : '' ).$s.'s)';
	}

	header('Content-type: text/json');
	exit(json_encode(array('updates' => $arrUpdates, 'msg' => $szMsg, 'gameover' => $bGameOver)));
}

// Change name //
else if ( isset($_POST['new_name']) ) {
	$_SESSION[S_NAME]['name'] = $_POST['new_name'];
	exit(htmlspecialchars($_SESSION[S_NAME]['name']));
}

// Leaderboard
else if ( isset($_GET['leaderboard']) ) {
	echo '<table width=100% border="1" cellpadding=5 cellspacing=0>';
	echo '<tr><th>Name</th><th>Board</th><th>Mines</th><th>Time</th><th>Date</th></tr>';
	$q= mysql_query("SELECT * FROM minesweeper ORDER BY mines DESC, playtime ASC, utc DESC");
	echo mysql_error();
	$bgs = array('#eeeeee', '#dddddd', '#eeeeee');
	$bg = 0;
	$lb = '';
	while ( $r = mysql_fetch_object($q) ) {
		if ( $lb && $lb != $r->mines ) {
			$bg++;
		}
		$lb = $r->mines;
		echo '<tr bgcolor="'.$bgs[$bg%count($bgs)].'">';
		echo '<td>'.$r->name.'</td>';
		echo '<td>'.$r->size_x.' * '.$r->size_y.'</td>';
		echo '<td>'.$r->mines.'</td>';
		$m = floor($r->playtime / 60);
		$s = $r->playtime % 60;
		echo '<td>'.( 0 < $m ? $m.'m ' : '' ).$s.'s</td>';
		echo '<td>'.date('Y-m-d H:i:s', $r->utc).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	exit;
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	exit('Invalid request');
}

?>
<html>

<head>
<title>MINESWEEPER</title>
<script src="/js/mootools_1_11.js"></script>
<script src="/102.js"></script>
<style>
* {
	margin			: 0;
	padding			: 0;
}
body,table {
	font-family		: verdana;
	font-size		: 11px;
	color			: black;
}
table#field, table#field table {
	border-collapse		: collapse;
}
table#field td {
	padding				: 0;
}
div#loading {
	position			: absolute;
	top					: 10px;
	right				: 10px;
	padding				: 5px;
	display				: none;
	background-color	: #f00;
	color				: #fff;
}
table tbody#ms_tbody td {
	width				: 16px;
	height				: 16px;
	padding				: 0;
	background-image	: url(images/dicht.gif);
}
table tbody#ms_tbody td.o0 {
	background-image	: url(images/open_0.gif);
}
table tbody#ms_tbody td.o1 {
	background-image	: url(images/open_1.gif);
}
table tbody#ms_tbody td.o2 {
	background-image	: url(images/open_2.gif);
}
table tbody#ms_tbody td.o3 {
	background-image	: url(images/open_3.gif);
}
table tbody#ms_tbody td.o4 {
	background-image	: url(images/open_4.gif);
}
table tbody#ms_tbody td.o5 {
	background-image	: url(images/open_5.gif);
}
table tbody#ms_tbody td.o6 {
	background-image	: url(images/open_6.gif);
}
table tbody#ms_tbody td.o7 {
	background-image	: url(images/open_7.gif);
}
table tbody#ms_tbody td.o8 {
	background-image	: url(images/open_8.gif);
}
table tbody#ms_tbody td.om {
	background-image	: url(images/open_m.gif);
}
table tbody#ms_tbody td.ox {
	background-image	: url(images/open_x.gif);
}
table tbody#ms_tbody td.ow {
	background-image	: url(images/open_w.gif);
}
table tbody#ms_tbody td.f {
	background-image	: url(images/flag.gif);
}
</style>
</head>

<body>

<div id="loading">
	<b>AJAX BUSY</b>
</div>

<table border="0" cellspacing="0" width="100%" height="100%">
<tr valign="middle">
	<td align="center"<?php if ( !empty($_GET['frame']) ) { ?> style="display:none;"<?php } ?>>
		<p><a href="#" onclick="window.open('?leaderboard', '', 'statusbar=0,width=650,height=500');return false;">Leaderboard</a></p>
		<br />
		<p><a href="#" onclick="return objMinesweeper.changeName();">Change Name</a></p>
		<br />
		<p><a href="#" onclick="window.open('?frame=1', '', 'statusbar=0');return false;">In frame</a></p>
	</td>
	<td align="center">
		<table id="field" style="border:solid 1px #777;"><tr><td><table style="border:solid 10px #bbb;"><tr><td><table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td><table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;"><tbody id="ms_tbody"><?php /*foreach( create_map(10, 10, 15) AS $row ) { echo '<tr>'; foreach ( $row AS $cell ) { echo '<td class="o'.$cell.'"></td>'; } echo '</tr>'; }*/ ?></tbody></table></td></tr></table></td></tr></table></td></tr></table>
		<br />
		<div><?php $arrFields = array(); foreach ( $_FIELDS AS $szField => $arrField ) { $arrFields[] = '<a href="#" onclick="return objMinesweeper.fetchMap(\''.$szField.'\');">'.$arrField['name'].'</a>'; } echo implode(' | ', $arrFields); ?></div>
	</td>
	<td align="center"<?php if ( !empty($_GET['frame']) ) { ?> style="display:none;"<?php } ?>>
		Mines: <b id="mines_to_find"></b> / <b><span id="mine_percentage"></span> %</b><br />
		<br />
		Your name: <b id="your_name">?</b><br />
		<br />
		Flags left: <b id="flags_left"></b><br />
	</td>
</tr>
</table>

<script>
Ajax.setGlobalHandlers({
	onStart : function() {
		$('loading').style.display = "block";
	},
	onComplete : function() {
		if ( 0 == Ajax.busy ) {
			$('loading').style.display = "none";
		}
	}
});

var objMinesweeper = new Minesweeper('<?= @$_SESSION[S_NAME]['sessions'][SESSION]['field'] ?: $g_szDefaultField ?>', '<?= $_GET['session'] ?>');
objMinesweeper.<?php echo empty($_SESSION[S_NAME]['name']) ? 'changeName()' : "setName('".addslashes($_SESSION[S_NAME]['name'])."')"; ?>;

$('ms_tbody').addEvents({
	contextmenu : function(e) {
		e = new Event(e).stop();
		if ( 'TD' === e.target.nodeName ) {
			objMinesweeper.toggleFlag(e.target);
		}
	},
	click : function(e) {
		e = new Event(e).stop();
		if ( 'TD' === e.target.nodeName ) {
			objMinesweeper.openField(e.target);
		}
	}
});
</script>
</body>

</html>
<?php

function create_map($f_x, $f_y, $f_m) {
	$arrMap = array_fill(0, $f_y, array_fill(0, $f_x, 0));
	$iMines = 0;
	while ( $iMines < $f_m ) {
		$x = rand(0, $f_x-1);
		$y = rand(0, $f_y-1);
		if ( 'm' !== $arrMap[$y][$x] ) {
			$arrMap[$y][$x] = 'm';
			surrounders_plus_one($arrMap, $x, $y);
			$iMines++;
		}
	}
	return $arrMap;
}

function surrounders_plus_one(&$f_map, $f_x, $f_y) {
	$_d = array(
		array(0, -1),
		array(1, -1),
		array(1, 0),
		array(1, 1),
		array(0, 1),
		array(-1, 1),
		array(-1, 0),
		array(-1, -1),
	);
	foreach ( $_d AS $d ) {
		if ( isset($f_map[$f_y+$d[0]][$f_x+$d[1]]) && 'm' !== $f_map[$f_y+$d[0]][$f_x+$d[1]] ) {
			$f_map[$f_y+$d[0]][$f_x+$d[1]]++;
		}
	}
}

function click_on_surrounders($f_x, $f_y) {
	global $arrUpdates;
	foreach ( array(array(0,-1),array(1,-1),array(1,0),array(1,1),array(0,1),array(-1,1),array(-1,0),array(-1,-1)) AS $d ) {
		if ( isset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]]) ) {
			$f = $_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]];
			$arrUpdates[] = array($f_x+$d[1], $f_y+$d[0], $f);
			unset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]]);
			if ( 0 === $f ) {
				click_on_surrounders($f_x+$d[1], $f_y+$d[0]);
			}
		}
	}
}

?>