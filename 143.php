<?php
// ABALONE

session_start();
define( 'S_NAME', 'abalone' );

require_once('inc.cls.json.php');
require_once('connect.php');

function db_select( $f_tbl, $f_where = '1' ) {
	$szQuery = "SELECT * FROM ".$f_tbl." WHERE ".$f_where.";";
	$q = mysql_query($szQuery) or die(mysql_error());
	$a = array();
	while ( $r = mysql_fetch_assoc($q) ) {
		$a[] = $r;
	}
	return $a;
}
function db_count( $f_tbl, $f_where = '1' ) {
	$q = mysql_query('SELECT COUNT(1) FROM '.$f_tbl.' WHERE '.$f_where.';') or die(mysql_error());
	return mysql_result($q, 0);
}
function db_delete($f_tbl, $f_where) {
	return mysql_query('DELETE FROM '.$f_tbl.' WHERE '.$f_where.';');
}
function db_insert($f_tbl, $f_insert) {
	return mysql_query('INSERT INTO '.$f_tbl.' ('.implode(',', array_keys($f_insert)).') VALUES (\''.implode("','", array_map('addslashes', $f_insert)).'\')');
}


if ( empty($_SESSION[S_NAME]['player_id']) ) {
	$_SESSION[S_NAME]['player_id'] = 1;
}
$_player = $_SESSION[S_NAME]['player_id'];


$arrPlayer = db_select('abalone_players', "id = ".(int)$_player."");
if ( 0 == count($arrPlayer) ) { exit('Invalid login'); }
$arrPlayer = $arrPlayer[0];
$arrPlayer['balls_left'] = db_count('abalone_balls', 'player_id = '.$arrPlayer['id']);

$arrGame = db_select('abalone_games', "id = ".(int)$arrPlayer['game_id']."");
$arrGame = $arrGame[0];

$arrOpponent = db_select('abalone_players', "game_id = ".(int)$arrPlayer['game_id']." AND id <> ".(int)$arrPlayer['id']."");
$arrOpponent = $arrOpponent[0];
$arrOpponent['balls_left'] = db_count('abalone_balls', 'player_id = '.$arrOpponent['id']);

$arrPlayerByColor = array( $arrPlayer['color'] => $arrPlayer['id'], $arrOpponent['color'] => $arrOpponent['id'] );


// FETCH MAP
if ( isset($_POST['fetch_map']) ) {
//	sleep(1);
	$arrAllBalls = db_select('abalone_players p, abalone_balls b', 'b.player_id = p.id AND p.id IN ('.$arrPlayer['id'].','.$arrOpponent['id'].')');
	$arrBalls = array();
	foreach ( $arrAllBalls AS $b ) {
		$arrBalls[] = array((int)$b['x'], (int)$b['y'], $b['color']);
	}
	exit(json::encode($arrBalls));
}

// MOVE
else if ( isset($_POST['changes']) ) {
	if ( $arrPlayer['color'] === $arrGame['turn'] ) {
		$arrChanges = array_map(create_function('$a', 'return explode(\',\', $a);'), $_POST['changes']);
		foreach ( $arrChanges AS $c ) {
			db_delete('abalone_balls', 'x = '.$c[0].' AND y = '.(int)$c[1]);
			if ( $c[2] ) {
				db_insert('abalone_balls', array('x' => $c[0], 'y' => $c[1], 'player_id' => $arrPlayerByColor[$c[2]]));
			}
		}
		mysql_query('UPDATE abalone_games SET turn = \''.$arrOpponent['color'].'\' WHERE id = '.$arrGame['id']);
		$_SESSION[S_NAME]['player_id'] = $arrOpponent['id'];
	}
	exit('OK');
}

else if ( isset($_GET['player_id']) ) {
	$_SESSION[S_NAME]['player_id'] = (int)$_GET['player_id'];
	header('Location: 143.php');
	exit;
}

?>
<html>

<head>
<title>Abalone</title>
<link rel="stylesheet" type="text/css" href="143.css" />
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<script type="text/javascript" src="143.js"></script>
</head>

<body>
<div style="position:absolute;width:900px;margin-left:-450px;left:50%;height:370px;margin-top:-185px;top:50%;">
<table id="players" border="0" cellpadding="2" cellspacing="0">
<tr class="u">
	<td align="center">You</td>
	<td></td>
	<td></td>
	<td></td>
	<td align="center">Turn</td>
</tr>
<tr class="b">
	<th><img src="/icons/right.gif" /></th>
	<td><span><?php echo ucfirst($arrPlayer['color']); ?> (<?php echo $arrPlayer['balls_left']; ?>)</span></td>
	<td>:</td>
	<td><?php echo ucfirst($arrPlayer['username']); ?></td>
	<th><img id="<?php echo $arrPlayer['color']; ?>_turn" src="/icons/<?php echo $arrGame['turn'] == $arrPlayer['color'] ? 'left' : 'blank'; ?>.gif" /></th>
</tr>
<tr>
	<th><img src="/icons/blank.gif" /></th>
	<td><span><?php echo ucfirst($arrOpponent['color']); ?> (<?php echo $arrOpponent['balls_left']; ?>)</span></td>
	<td>:</td>
	<td><?php echo ucfirst($arrOpponent['username']); ?></td>
	<th><img id="<?php echo $arrOpponent['color']; ?>_turn" src="/icons/<?php echo $arrGame['turn'] == $arrOpponent['color'] ? 'left' : 'blank'; ?>.gif" /></th>
</tr>
<tr>
	<td colspan="5" style="padding-top:5px;" align="center"><input type="button" value="Unselect All" onclick="return objAbalone.unselectAllBalls();" /></td>
</tr>
<tr>
	<td align="center" style="padding:5px;" colspan="5"><img usemap="#dirmap" src="images/143_dir_map.gif" /></td>
</tr>
</table>
</div>

<div id="abalone_div" style="width:408px;height:370px;position:absolute;margin-left:-210px;left:50%;margin-top:-185px;top:50%;background-color:#999;border:solid 1px black;">
<?php foreach ( explode(',', '4:8,5:8,6:8,7:8,8:8,3:7,4:7,5:7,6:7,7:7,8:7,2:6,3:6,4:6,5:6,6:6,7:6,8:6,1:5,2:5,3:5,4:5,5:5,6:5,7:5,8:5,0:4,1:4,2:4,3:4,4:4,5:4,6:4,7:4,8:4,0:3,1:3,2:3,3:3,4:3,5:3,6:3,7:3,0:2,1:2,2:2,3:2,4:2,5:2,6:2,0:1,1:1,2:1,3:1,4:1,5:1,0:0,1:0,2:0,3:0,4:0') AS $c ) {
	echo call_user_func_array('printHtmlBall', explode(':', $c));
} ?>
</div>

<!--<map id="dirmap" name="dirmap">
	<area shape="rect" coords="46,12,93,53" href="#topleft" onclick="return false;" title="topleft" />
	<area shape="rect" coords="112,135,161,177" href="#bottomright" onclick="return false;" title="bottomright" />
	<area shape="rect" coords="116,12,162,53" href="#topright" onclick="return false;" title="topright" />
	<area shape="rect" coords="45,135,91,177" href="#bottomleft" onclick="return false;" title="bottomleft" />
	<area shape="rect" coords="161,74,196,115" href="#right" onclick="return false;" title="right" />
	<area shape="rect" coords="11,75,45,115" href="#left" onclick="return false;" title="left" />
</map>-->

<script type="text/javascript">
<!--//
var objAbalone = new Abalone('<?php echo $arrPlayer['color']; ?>', '<?php echo $arrGame['turn']; ?>');
objAbalone.fetchMap();

$('abalone_div').addEvent('click', function(e) {
	e = new Event(e).stop();
	if ( 'IMG' !== e.target.nodeName ) { return false; }
	objAbalone.clickedOn(e.target);
});
//-->
</script>
</body>

</html>
<?php

function printHtmlBall( $f_x, $f_y ) {
	return '<img title="'.$f_x.':'.$f_y.'" id="ball_'.$f_x.'_'.$f_y.'" style="top:'.(337-$f_y*40).'px;left:'.(15+$f_x*45+($f_y>4?-22*($f_y-4):($f_y<4?22*(4-$f_y):0))).'px;" src="/images/143_empty.gif" />'."\n";
}

function nextCoords( $f_arrCoords, $f_iDir ) {
	$d = array(0, 0);
	switch ( $f_iDir ) {
		case 'topright':
		case 2:
			$d = array(1, 1);
		break;
		case 'bottomleft':
		case 1:
			$d = array(-1, -1);
		break;
		case 'topleft':
		case 4:
			$d = array(0, 1);
		break;
		case 'bottomright':
		case 3:
			$d = array(0, -1);
		break;
		case 'right':
		case 6:
			$d = array(1, 0);
		break;
		case 'left':
		case 5:
			$d = array(-1, 0);
		break;
	}
	return array( $f_arrCoords[0]+$d[0], $f_arrCoords[1]+$d[1] );
}

?>