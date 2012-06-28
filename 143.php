<?php
// ABALONE

session_start();
define('S_NAME', 'abalone');

require 'inc.env.php';
require 'inc.db.php';


if ( empty($_SESSION[S_NAME]['player_id']) ) {
	$_SESSION[S_NAME]['player_id'] = 1;
}
$_player = $_SESSION[S_NAME]['player_id'];


$objPlayer = $db->select('abalone_players', array('id' => $_player), null, true);
if ( !$objPlayer ) {
	exit('Invalid login');
}
$objPlayer->balls_left = $db->count('abalone_balls', array('player_id' => $objPlayer->id));
#print_r($objPlayer);

$objGame = $db->select('abalone_games', array('id' => $objPlayer->game_id), null, true);
#print_r($objGame);

$objOpponent = $db->select('abalone_players', 'game_id = ? AND id <> ?', array($objPlayer->game_id, $objPlayer->id), true);
$objOpponent->balls_left = $db->count('abalone_balls', array('player_id' => $objOpponent->id));
#print_r($objOpponent);

$arrPlayerByColor = array(
	$objPlayer->color => $objPlayer->id,
	$objOpponent->color => $objOpponent->id,
);
#print_r($arrPlayerByColor);


// FETCH MAP
if ( isset($_GET['fetch_map']) ) {
	$arrAllBalls = $db->fetch('SELECT coord_1, coord_2, color FROM abalone_players p, abalone_balls b WHERE b.player_id = p.id AND p.id IN (?)', array($arrPlayerByColor));

	$arrBalls = array();
	foreach ( $arrAllBalls AS $b ) {
		$arrBalls[] = array((int)$b['coord_1'], (int)$b['coord_2'], $b['color']);
	}

	exit(json_encode($arrBalls));
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

// SWITCH PLAYER
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

<div id="players">
	<table class="players">
		<tr class="u">
			<td align="center">You</td>
			<td colspan="2"></td>
			<td align="center">Turn</td>
		</tr>
		<tr class="self <?if($objGame->turn == $objPlayer->color):?>turn<?endif?>">
			<td class="img"><span class="img self"></span></td>
			<td><?php echo ucfirst($objPlayer->color); ?> (<?php echo $objPlayer->balls_left; ?>)</td>
			<td><?php echo ucfirst($objPlayer->username); ?></td>
			<td class="img"><span class="img turn"></span></td>
		</tr>
		<tr class="other <?if($objGame->turn == $objOpponent->color):?>turn<?endif?>">
			<td class="img"><span class="img self"></span></td>
			<td><span><?php echo ucfirst($objOpponent->color); ?> (<?php echo $objOpponent->balls_left; ?>)</span></td>
			<td><?php echo ucfirst($objOpponent->username); ?></td>
			<td class="img"><span class="img turn"></span></td>
		</tr>
	</table>
</div>

<div id="abalone_div" style="width:408px;height:370px;position:absolute;margin-left:-210px;left:50%;margin-top:-185px;top:50%;background-color:#999;border:solid 1px black;">
<?php foreach ( explode(',', '4:8,5:8,6:8,7:8,8:8,3:7,4:7,5:7,6:7,7:7,8:7,2:6,3:6,4:6,5:6,6:6,7:6,8:6,1:5,2:5,3:5,4:5,5:5,6:5,7:5,8:5,0:4,1:4,2:4,3:4,4:4,5:4,6:4,7:4,8:4,0:3,1:3,2:3,3:3,4:3,5:3,6:3,7:3,0:2,1:2,2:2,3:2,4:2,5:2,6:2,0:1,1:1,2:1,3:1,4:1,5:1,0:0,1:0,2:0,3:0,4:0') AS $c ) {
	echo call_user_func_array('printHtmlBall', explode(':', $c));
} ?>
</div>

<script>
var objAbalone = new Abalone('<?php echo $objPlayer->color; ?>', '<?php echo $objGame->turn; ?>');
objAbalone.fetchMap();

$('abalone_div').addEvent('click', function(e) {
	e = new Event(e).stop();
	if ( e.target.is('.ball.<?=$objPlayer->color?>') ) {
		objAbalone.clickedOn(e.target);
	}
});
</script>
</body>

</html>
<?php

function printHtmlBall( $f_x, $f_y ) {
	return '<span title="' . $f_x . ':' . $f_y . '" id="ball_' . $f_x . '_' . $f_y . '" style="top: ' . (337 - $f_y * 40) . 'px; left: ' . (15 + $f_x * 45 + ($f_y > 4 ? -22 * ($f_y - 4) : ($f_y < 4 ? 22 * (4 - $f_y) : 0))) . 'px;" class="ball"></span>'."\n";
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