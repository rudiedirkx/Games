<?php
// MINESWEEPER

require_once __DIR__ . '/inc.minesweeper.php';
require __DIR__ . '/inc.bootstrap.php';

session_start();

isset($minesweeper) or $minesweeper = new MinesweeperMaker();
isset($title) or $title = 'MINESWEEPER';

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

$_SESSION['ms2']['sessions'] = array_filter($_SESSION['ms2']['sessions'] ?? [], function(array $session) {
	return empty($session['starttime']) || $session['starttime'] > strtotime('-2 hours');
});


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
	"d" => array(
		"name"	=> "XXL",
		"sides" => array(70, 40),
		"mines" => 350,
	),
);



// Start new game //
if ( isset($_POST['fetch_map'], $_POST['field']) ) {
	if ( !isset($_FIELDS[ $_POST['field'] ]) ) {
		exit(json_encode(array('error' => 'Invalid field!')));
	}

	$arrLevel = $_FIELDS[ $_POST['field'] ];
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
		$_SESSION[S_NAME]['sessions'][SESSION]['map'] = $minesweeper->create_map(
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
		$minesweeper->click_on_surrounders($arrUpdates, $f_x, $f_y);
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
		$playtime = time()-$_SESSION[S_NAME]['sessions'][SESSION]['starttime'];

		$m = floor($playtime / 60);
		$s = $playtime % 60;
		$szMsg = "You win!\n\nIt took you " . $m . ":" . str_pad($s, 2, '0', STR_PAD_LEFT) . ".";
	}

	header('Content-type: text/json');
	exit(json_encode(array(
		'updates' => $arrUpdates,
		'msg' => $szMsg,
		'gameover' => $bGameOver,
	)));
}

if ( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
	exit('Invalid request');
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= $title ?></title>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('102.js') ?>"></script>
<script src="<?= html_asset('102c.js') ?>"></script>
<link rel="stylesheet" href="<?= html_asset('102.css') ?>" />
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
</head>

<body>

<div id="loading">
	<b>WORKING...</b>
</div>

<div id="container">
	<div id="left">
		<a class="cheat" href="#" onclick="getSolver().mf_SaveAndMarkAndClickAll(function(change) { console.warn('change', change); change || alert('I can only help those who help themselves!'); }); return false">Cheat!</a>
		&nbsp;
		<a class="export" href="#" onclick="
			objMinesweeper.export(function(rows) {
				$('#form-export').setHTML(rows.map(function(row) {
					return '<input name=&quot;map[]&quot; type=&quot;hidden&quot; value=&quot;' + row + '&quot; />';
				}).join(''));
				$('#form-export').submit();
			});
			return false
		">Export</a>
		<form id="form-export" method="post" action="102d_create.php" target="_blank"></form>
	</div>
	<div id="content">
		<table id="field" style="border:solid 1px #777;"><tr><td class="wrap">
			<table style="border:solid 10px #bbb;"><tr><td class="wrap">
				<table style="border-style:solid;border-width:3px;border-color:#777 #eee #eee #777;"><tr><td class="wrap">
					<div class="sizer">
						<table border="0" cellpadding="0" cellspacing="0" style="font-size:4px;"><tbody id="ms_tbody">
							<!-- tiles here -->
						</tbody></table>
					</div>
				</td></tr></table>
			</td></tr></table>
		</td></tr></table>
		<br />
		<div>
			<? $first = true; foreach ($_FIELDS AS $szField => $arrField): ?>
				<? if (!$first): ?> | <? endif ?>
				<a href="#" onclick="return objMinesweeper.fetchMap('<?= $szField ?>'), false"><?= $arrField['name'] ?></a>
			<? $first = false; endforeach ?>
		</div>
	</div>
	<div id="right">
		Mines: <b id="mines_to_find"></b> (<b><span id="mine_percentage"></span> %</b>)
		&nbsp;
		Flags left: <b id="flags_left"></b>
	</div>
</div>

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
