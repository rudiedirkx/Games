<?php
// Towers of Hanoi

session_start();

$arrDisks = array(8,7,6,5,4,3,2,1);
//$arrDisks = array(3,2,1);
$arrTowers = array(
	1 => $arrDisks,
	2 => array(),
	3 => array(),
);

define( 'BASEPAGE',					basename($_SERVER['SCRIPT_NAME']) );
define( 'SESSION_NAME',				'GAME_151_TOH' );

if ( empty($_SESSION[SESSION_NAME]) ) {
	$_SESSION[SESSION_NAME] = array(
		'towers'	=> $arrTowers,
		'moves'		=> 0,
		'starttime'	=> null,
		'finished'	=> null,
	);
}
$arrTowers =& $_SESSION[SESSION_NAME]['towers'];

$iMoves = $_SESSION[SESSION_NAME]['moves'];
$iPlaytime = null === $_SESSION[SESSION_NAME]['starttime'] ? 0 : time() - $_SESSION[SESSION_NAME]['starttime'];
$arrFinished = $_SESSION[SESSION_NAME]['finished'];

// reset //
if ( isset($_GET['reset']) ) {
	$_SESSION[SESSION_NAME] = null;
	header('Location: '.BASEPAGE);
	exit;
}

else if ( !empty($_GET['save']) ) {
	if ( !empty($arrFinished) ) {
		/**db_insert('towers_of_hanoi', array(
			'playtime'	=> $arrFinished['playtime'],
			'moves'		=> $arrFinished['moves'],
			'utc'		=> time(),
			'name'		=> addslashes($_GET['name']),
			'ip'		=> $_SERVER['REMOTE_ADDR'],
		));/**/
		header('Location: ?reset=1');
	}
	else {
		header('Location: '.BASEPAGE);
	}
	exit;
}

// move disk //
else if ( isset($_GET['t1'], $_GET['t2']) ) {
	if ( isset($arrTowers[$_GET['t1']], $arrTowers[$_GET['t2']]) && 0 < count($arrTowers[$_GET['t1']]) ) {
		// remove
		$iTopBlock = array_pop($arrTowers[$_GET['t1']]);
		// check destination tower
		if ( empty($arrTowers[$_GET['t2']]) || end($arrTowers[$_GET['t2']]) >= $iTopBlock ) {
			// add
			array_push($arrTowers[$_GET['t2']], $iTopBlock);
			$_SESSION[SESSION_NAME]['moves']++;
			if ( null === $_SESSION[SESSION_NAME]['starttime'] ) {
				$_SESSION[SESSION_NAME]['starttime'] = time();
			}
		}
		else {
			// invalid move -> return disk
			array_push($arrTowers[$_GET['t1']], $iTopBlock);
		}
		if ( $arrDisks === end($arrTowers) ) {
			$_SESSION[SESSION_NAME]['finished'] = array(
				'playtime'	=> time() - $_SESSION[SESSION_NAME]['starttime'],
				'moves'		=> $_SESSION[SESSION_NAME]['moves'],
			);
		}
		else {
			$_SESSION[SESSION_NAME]['finished'] = null;
		}
	}
	header('Location: '.BASEPAGE);
	exit;
}

?>
<html>

<head>
<title>Towers of Hanoi</title>
<style type="text/css">
th { font-size:40px; }
td.tower { background:#fff url(images/151_tower.bmp) repeat-y center; }
div { background-color:#ddd;height:20px;border:solid 1px #444;font-size:12px; }
</style>
<script type="text/javascript">
<!--//
var T1 = null, T2 = null;
var CT = function(t) {
	if ( T1 === t ) {
		// unset T1
		document.getElementById('tower_'+t).style.backgroundColor = '';
		T1 = null;
	}
	else if ( T1 ) {
		// set T2 and Act
		T2 = t;
		document.location = '?t1=' + T1 + '&t2=' + T2;
	}
	else {
		// set T1
		document.getElementById('tower_'+t).style.backgroundColor = 'yellow';
		T1 = t;
	}
}
//-->
</script>
</head>

<body>
<table border="0" width="100%" height="100%">
<tr>
	<td height="15" colspan="<?php echo count($arrTowers); ?>"><a href="?reset=1">reset</a> | moves: <?php echo $iMoves; ?> | time: <?php echo secondsToString(empty($arrFinished) ? $iPlaytime : $arrFinished['playtime']); ?><?php echo !empty($arrFinished) ? ' | <b>FINISHED: <input type="text" id="savename" size="12" /> <input type="button" value="Save" onclick="document.location=\'?save=\'+document.getElementById(\'savename\').value;" /></b>' : ''; ?></td>
</tr>
<tr valign="top">
<?php
foreach ( $arrTowers AS $k => $arrTower ) {
	echo '<th height="40" width="'.round(100/count($arrTowers)).'%">Tower '.$k.'</th>';
}
?>
</tr>
<tr valign="middle">
<?php
foreach ( $arrTowers AS $k => $arrTower ) {
	echo '<td class="tower" id="tower_'.$k.'" onclick="CT(\''.$k.'\');" width="'.round(100/count($arrTowers)).'%" align="center">';
	foreach ( array_reverse($arrTower) AS $iDisk ) {
		echo '<div style="background-color:#ddd;width:'.($iDisk*40).'px;"><br /></div>';
	}
	if ( empty($arrTower) ) {
		echo '<br />';
	}
	echo '</td>';
}
?>
</tr>
</table>
</body>

</html>
<?php
function secondsToString($f_iSeconds) {
	return str_pad((string)floor((int)$f_iSeconds/60), 2, '0', STR_PAD_LEFT).':'.str_pad((string)floor((int)$f_iSeconds%60), 2, '0', STR_PAD_LEFT);
}
?>