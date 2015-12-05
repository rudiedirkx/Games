<?php
// MINESWEEPER

if ( isset($_GET['source']) ) {
	highlight_file(__FILE__);
	exit;
}

session_start();

if ( isset($_GET['dump']) ) {
	header('Content-type: text/plain; charset=utf-8');
	print_r($_SESSION);
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


// require_once('connect.php');
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
	if ( !isset($_FIELDS[ $_POST['field'] ]) ) {
		exit(json_encode(array('error' => 'Invalid field!')));
	}

	$arrLevel = $_FIELDS[ $_POST['field'] ];
	// $_SESSION[S_NAME]['sessions'][SESSION]['map'] = create_map($arrLevel['sides'][0], $arrLevel['sides'][1], $arrLevel['mines']);
	$_SESSION[S_NAME]['sessions'][SESSION]['map'] = array();
	$_SESSION[S_NAME]['sessions'][SESSION]['starttime'] = 0;
	$_SESSION[S_NAME]['sessions'][SESSION]['field'] = $_POST['field'];
	$_SESSION[S_NAME]['sessions'][SESSION]['mines'] = (int)$arrLevel['mines'];
	$arrMap = array(
		'field' => $_POST['field'],
		'size' => array(
			'x' => $arrLevel['sides'][0],
			'y' => $arrLevel['sides'][1],
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

	// Create map & start timer
	if ( !$_SESSION[S_NAME]['sessions'][SESSION]['starttime'] ) {
		$arrLevel = $_FIELDS[ $_SESSION[S_NAME]['sessions'][SESSION]['field'] ];
		$_SESSION[S_NAME]['sessions'][SESSION]['map'] = create_map(
			$arrLevel['sides'][0],
			$arrLevel['sides'][1],
			$arrLevel['mines'],
			$f_x,
			$f_y
		);

		$_SESSION[S_NAME]['sessions'][SESSION]['starttime'] = time();
	}

	// Check valid coordinate
	if ( !isset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y][$f_x]) ) {
		header('Content-type: text/json');
		exit(json_encode(array('updates' => array(), 'msg' => '', 'gameover' => false)));
	}

	$bGameOver = false;

	$f = $_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y][$f_x];

	// Hit MINE
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

	// SPREAD OUT area clicking
	else if ( 0 === $f ) {
		$arrUpdates[] = array($f_x, $f_y, $f);
		// Find surrounders, surrounders' surrounders, etc
		click_on_surrounders($arrUpdates, $f_x, $f_y);
	}

	// OPEN SINGLE cell
	else {
		$arrUpdates[] = array($f_x, $f_y, $f);
	}

	unset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y][$f_x]);
	$_SESSION[S_NAME]['sessions'][SESSION]['map'] = array_filter($_SESSION[S_NAME]['sessions'][SESSION]['map']);
	$iClosed = array_sum(array_map('count', $_SESSION[S_NAME]['sessions'][SESSION]['map']));

	$szMsg = '';
	if ( !$bGameOver && $iClosed === $_SESSION[S_NAME]['sessions'][SESSION]['mines'] ) {
		$bGameOver = true;

		$arrLevel = $_FIELDS[ $_SESSION[S_NAME]['sessions'][SESSION]['field'] ];
		isset($_SESSION[S_NAME]['name']) or $_SESSION[S_NAME]['name'] = 'Anonymous';
		$playtime = time()-$_SESSION[S_NAME]['sessions'][SESSION]['starttime'];

		// mysql_query("INSERT INTO minesweeper (name,size_x,size_y,mines,playtime,utc, ip, user_agent) VALUES ('".addslashes($_SESSION[S_NAME]['name'])."',".$arrLevel['sides'][0].",".$arrLevel['sides'][1].",".$arrLevel['mines'].",".$playtime.",".time().", '".addslashes($_SERVER['REMOTE_ADDR'])."', '".addslashes($_SERVER['HTTP_USER_AGENT'])."');");

		$m = floor($playtime / 60);
		$s = $playtime % 60;
		$szMsg = 'YOU FINISHED LEVEL "' . $arrLevel['name'] . '" IN ' . ( $m ? $m . 'm ' : '' ) . $s . "s.\n\nNo more highscore!";
	}

	header('Content-type: text/json');
	exit(json_encode(array(
		'updates' => $arrUpdates,
		'msg' => $szMsg,
		'gameover' => $bGameOver,
	)));
}

// Change name //
else if ( isset($_POST['new_name']) ) {
	$_SESSION[S_NAME]['name'] = $_POST['new_name'];
	exit(htmlspecialchars($_SESSION[S_NAME]['name']));
}

if ( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
	exit('Invalid request');
}

?>
<html>

<head>
<title>MINESWEEPER</title>
<script src="/js/rjs-custom.js"></script>
<script src="/102.js"></script>
<link rel="stylesheet" href="102.css" />
<style>
* {
	margin: 0;
	padding: 0;
}
body,table {
	font-family: Verdana;
	font-size: 11px;
	color: black;
}
div#loading {
	position: absolute;
	top: 10px;
	right: 10px;
	padding: 5px;
	display: none;
	background-color: #f00;
	color: #fff;
}
</style>
<script src="/102c.js"></script>
</head>

<body>

<div id="loading">
	<b>AJAX BUSY</b>
</div>

<table border="0" cellspacing="0" width="100%" height="100%">
<tr valign="middle">
	<td align="center"<?php if ( !empty($_GET['frame']) ) { ?> style="display:none;"<?php } ?>>
		<!--
			<p><a href="#" onclick="window.open('?leaderboard', '', 'statusbar=0,width=650,height=500');return false;">Leaderboard</a></p>
			<br />
		-->
		<p><a class="change-name" href="#" onclick="return objMinesweeper.changeName();">Change Name</a></p>
		<br />
		<p><a class="open-in-frame" href="#" onclick="window.open('?frame=1', '', 'statusbar=0'); return false">In frame</a></p>
		<br />
		<p><a class="cheat" href="#" onclick="getSolver().mf_SaveAndMarkAndClickAll(function(change) { console.warn('change', change); change || alert('I can only help those who help themselves!'); }); return false">Cheat!</a></p>
		<br />
		<p><a class="export" href="#" onclick="
			objMinesweeper.export(function(rows) {
				$('#form-export').setHTML(rows.map(function(row) {
					return '<input name=&quot;map[]&quot; type=&quot;hidden&quot; value=&quot;' + row + '&quot; />';
				}).join(''));
				$('#form-export').submit();
			});
			return false
		">Export</a></p>
		<form id="form-export" method="post" action="102d_create.php" target="_blank"></form>
	</td>
	<td align="center">
		<table id="field" style="border:solid 1px #777;"><tr><td class="wrap">
			<table style="border:solid 10px #bbb;"><tr><td class="wrap">
				<table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td class="wrap">
					<table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;"><tbody id="ms_tbody">
						<!-- tiles here -->
					</tbody></table>
				</td></tr></table>
			</td></tr></table>
		</td></tr></table>
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

<div style="display: none">
	<img src="images/flag.gif" />
	<img src="images/open_m.gif" />
	<img src="images/open_x.gif" />
	<img src="images/open_0.gif" />
	<img src="images/open_1.gif" />
	<img src="images/open_2.gif" />
	<img src="images/open_3.gif" />
	<img src="images/open_4.gif" />
</div>

<script>
window.on('xhrStart', function() {
	$('#loading').style.display = "block";
});
window.on('xhrDone', function() {
	if ( r.xhr.busy == 0 ) {
		$('#loading').style.display = "none";
	}
});

var objMinesweeper = new Minesweeper('<?= @$_SESSION[S_NAME]['sessions'][SESSION]['field'] ?: $g_szDefaultField ?>', '<?= $_GET['session'] ?>');
// objMinesweeper.<?php echo empty($_SESSION[S_NAME]['name']) ? 'changeName()' : "setName('".addslashes($_SESSION[S_NAME]['name'])."')"; ?>;

// SOLVER //
function getSolver() {
	MinesweeperSolver.DEBUG = 0;
	return new MinesweeperSolver($('#ms_tbody'), objMinesweeper);
}
// SOLVER //

$('#ms_tbody')
	.on('contextmenu', 'td', function(e) {
		e.preventDefault();
		objMinesweeper.toggleFlag(this);
		objMinesweeper.updateFlagCounter();
	})
	.on('click', '#ms_tbody td', function(e) {
		e.preventDefault();
		objMinesweeper.openField(this);
	});
</script>
</body>

</html>
<?php

function create_map( $f_width, $f_height, $f_m, $f_x = null, $f_y = null ) {
	$arrMap = array_fill(0, $f_height, array_fill(0, $f_width, 0));

	$iMines = 0;
	while ( $iMines < $f_m ) {
		$x = rand(0, $f_width-1);
		$y = rand(0, $f_height-1);
		if ( 'm' !== $arrMap[$y][$x] ) {
			$arrMap[$y][$x] = 'm';
			surrounders_plus_one($arrMap, $x, $y);
			$iMines++;
		}
	}

	// I clicked something and that must be a 0
	if ( $f_x !== null && $f_y !== null && isset($arrMap[$f_y][$f_x]) ) {
		$tile = $arrMap[$f_y][$f_x];
		if ( $tile !== 0 ) {
			return create_map($f_width, $f_height, $f_m, $f_x, $f_y);
		}
	}

	return $arrMap;
}

function surrounders() {
	return array(
		array(0, -1),
		array(1, -1),
		array(1, 0),
		array(1, 1),
		array(0, 1),
		array(-1, 1),
		array(-1, 0),
		array(-1, -1),
	);
}

function surrounders_plus_one(&$f_map, $f_x, $f_y) {
	foreach ( surrounders() AS $d ) {
		if ( isset($f_map[$f_y+$d[0]][$f_x+$d[1]]) && 'm' !== $f_map[$f_y+$d[0]][$f_x+$d[1]] ) {
			$f_map[$f_y+$d[0]][$f_x+$d[1]]++;
		}
	}
}

function click_on_surrounders(&$arrUpdates, $f_x, $f_y) {
	foreach ( surrounders() AS $d ) {
		if ( isset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]]) ) {
			$f = $_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]];
			$arrUpdates[] = array($f_x+$d[1], $f_y+$d[0], $f);
			unset($_SESSION[S_NAME]['sessions'][SESSION]['map'][$f_y+$d[0]][$f_x+$d[1]]);
			if ( 0 === $f ) {
				click_on_surrounders($arrUpdates, $f_x+$d[1], $f_y+$d[0]);
			}
		}
	}
}
