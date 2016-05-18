<?php
// ABALONE

/*
exit(json_encode(Abalone::initialBalls()));
/*
exit(json_encode(call_user_func(function($_balls) {
	$all = array();
	foreach ( $_balls as $c => $balls ) {
		array_map(function($ball) use (&$all, $c) {
			$all[] = array_merge(array_map('intval', explode(':', $ball)), array($c));
		}, $balls);
	}
	return $all;
}, Abalone::initialBalls())));
/**/

define('REQUEST_TIME', time());

session_start();
define('S_NAME', 'abalone');

require 'inc.env.php';
require 'inc.db.php';

$db->schema(require '143.schema.php');



// Log out
if ( isset($_GET['logout']) ) {
	unset($_SESSION[S_NAME]);

	header('Location: 143.php');
	exit('Bye');
}

else if ( empty($_SESSION[S_NAME]['player_id']) ) {

	// Log in from code
	if ( isset($_REQUEST['login']) ) {
		$user = $db->select('abalone_players', array(
			'password' => $_REQUEST['login'],
		))->first();

		if ( $user ) {
			$_SESSION[S_NAME]['player_id'] = (int)$user->id;

			header('Location: 143.php');
			exit('Logging in...');
		}

		header('Location: 143.php');
		exit('Nope...');
	}

	// New game
	if ( isset($_GET['newgame']) ) {
		$info = newGame($white, $black);

		$_SESSION[S_NAME]['player_id'] = (int)$info['white'];

		header('Location: 143.php');
		exit('Game created');
	}

	// Log in form
	?>
	<style>html, body, h1 { margin: 0; } body { padding: 30px; }</style>

	<h1>Abalone: log in</h1>

	<form action method=post>
		<p>Password: <input name="login" /></p>
		<p><input type=submit /></p>
	</form>

	<p><a href="?newgame">Start a new game for 2</a></p>
	<?php

	exit;
}



$_player = $_SESSION[S_NAME]['player_id'];



// Load player
$objPlayer = $db->select('abalone_players', array('id' => $_player), null, true);
if ( !$objPlayer ) {
	exit('Invalid login');
}
$objPlayer->balls_left = $db->count('abalone_balls', array('player_id' => $objPlayer->id));

// Load game
$objGame = $db->select('abalone_games', array('id' => $objPlayer->game_id), null, true);

// Load opponent
$objOpponent = $db->select('abalone_players', 'game_id = ? AND id <> ?', array($objPlayer->game_id, $objPlayer->id), true);
$objOpponent->balls_left = $db->count('abalone_balls', array('player_id' => $objOpponent->id));



// Helper mapping
$arrPlayerByColor = array(
	$objPlayer->color => $objPlayer->id,
	$objOpponent->color => $objOpponent->id,
);



// Move
if ( isset($_POST['changes']) ) {
	if ( $objGame->turn == $objPlayer->color ) {
		$success = $db->transaction(function($db, $context) use ($changes, $arrPlayerByColor, $objOpponent, $objGame) {
			foreach ( $_POST['changes'] as $coord => $color ) {
				$conditions = array_combine(['x', 'y', 'z'], explode('_', $coord));

				$db->delete('abalone_balls', ['player_id' => $arrPlayerByColor] + $conditions);
				if ( $color ) {
					$db->insert('abalone_balls', ['player_id' => $arrPlayerByColor[$color]] + $conditions);
				}
			}

			$db->update('abalone_games', array('turn' => $objOpponent->color), array('id' => $objGame->id));
		}, $context);

		if ( $success ) {
			return json_respond(array('error' => 0));
		}

		return json_respond(array('error' => 'Db exception?'));
	}

	return json_respond(array('error' => 'Not your turn!'));
}

// Fetch status
else if ( isset($_GET['status']) ) {
	$status = (object) array(
		'turn' => $objGame->turn,
		'opponent' => (int) $objOpponent->id,
	);

	return json_respond(array('error' => 0, 'status' => $status));
}

// Always be on the 'login' page, to save the URL
if ( empty($_GET['login']) ) {
	header('Location: ?login=' . $objPlayer->password);
	exit;
}

$friendURL = 'http://' . $_SERVER['HTTP_HOST'] . '/143.php?login=' . $objOpponent->password;

?>
<html>

<head>
<title>Abalone</title>
<link rel="stylesheet" href="143.css" />
</head>

<body>

<h1>Abalone</h1>

<p class="friend-url">Share <a href="<?= $friendURL ?>"><?= $friendURL ?></a> with your friend.</p>

<div id="board">
	<a href="#" class="direction tl" data-dir="-1,-1,0">tl</a>
	<a href="#" class="direction tr" data-dir="0,-1,-1">tr</a>
	<a href="#" class="direction r"  data-dir="1,0,-1">r</a>
	<a href="#" class="direction br" data-dir="1,1,0">br</a>
	<a href="#" class="direction bl" data-dir="0,1,1">bl</a>
	<a href="#" class="direction l"  data-dir="-1,0,1">l</a>

	<? foreach (Abalone::balls() as $ball): ?>
		<?= Abalone::renderBall($ball) ?>
	<? endforeach ?>
</div>

<div id="players">
	<table class="players">
		<tr>
			<th title="
Player: <?= $objPlayer->id . "\n" ?>
Opponent: <?= $objOpponent->id ?>
			">You</th>
			<th></td>
			<th>Turn</th>
		</tr>
		<tr class="self <?if($objGame->turn == $objPlayer->color):?>turn<?endif?>">
			<td class="img"><span class="img self"></span></td>
			<td>
				<?= ucfirst($objPlayer->color) ?> (<?= $objPlayer->balls_left ?>)
			</td>
			<td class="img"><span class="img turn"></span></td>
		</tr>
		<tr class="other <?if($objGame->turn == $objOpponent->color):?>turn<?endif?>">
			<td class="img"><span class="img self"></span></td>
			<td>
				<?= ucfirst($objOpponent->color) ?> (<?= $objOpponent->balls_left ?>)
			</td>
			<td class="img"><span class="img turn"></span></td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<a href="?logout">Log out</a>
			</td>
		</tr>
	</table>
</div>

<div id="help">
	<h2>What's this then?</h2>
	<p><a href="http://en.wikipedia.org/wiki/Abalone_(board_game)">It's Abalone.</a></p>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>THREE = {}</script>
<script src="Vector3.js"></script>
<script src="143.js"></script>
<script>
$body = $('body');
objAbalone = new Abalone('#board', '<?= $objPlayer->color ?>', '<?= $objGame->turn ?>', true);
</script>

</body>

</html>
<?php

class Abalone {

	static public $allBalls = [];

	static function balls() {
		$balls = [
			['coords' => [1, 1, 5], 'top' =>  17, 'left' => 107],
			['coords' => [2, 1, 4], 'top' =>  17, 'left' => 152],
			['coords' => [3, 1, 3], 'top' =>  17, 'left' => 197],
			['coords' => [4, 1, 2], 'top' =>  17, 'left' => 242],
			['coords' => [5, 1, 1], 'top' =>  17, 'left' => 287],
			['coords' => [1, 2, 6], 'top' =>  57, 'left' =>  84],
			['coords' => [2, 2, 5], 'top' =>  57, 'left' => 129],
			['coords' => [3, 2, 4], 'top' =>  57, 'left' => 174],
			['coords' => [4, 2, 3], 'top' =>  57, 'left' => 219],
			['coords' => [5, 2, 2], 'top' =>  57, 'left' => 264],
			['coords' => [6, 2, 1], 'top' =>  57, 'left' => 309],
			['coords' => [1, 3, 7], 'top' =>  97, 'left' =>  61],
			['coords' => [2, 3, 6], 'top' =>  97, 'left' => 106],
			['coords' => [3, 3, 5], 'top' =>  97, 'left' => 151],
			['coords' => [4, 3, 4], 'top' =>  97, 'left' => 196],
			['coords' => [5, 3, 3], 'top' =>  97, 'left' => 241],
			['coords' => [6, 3, 2], 'top' =>  97, 'left' => 286],
			['coords' => [7, 3, 1], 'top' =>  97, 'left' => 331],
			['coords' => [1, 4, 8], 'top' => 137, 'left' =>  38],
			['coords' => [2, 4, 7], 'top' => 137, 'left' =>  83],
			['coords' => [3, 4, 6], 'top' => 137, 'left' => 128],
			['coords' => [4, 4, 5], 'top' => 137, 'left' => 173],
			['coords' => [5, 4, 4], 'top' => 137, 'left' => 218],
			['coords' => [6, 4, 3], 'top' => 137, 'left' => 263],
			['coords' => [7, 4, 2], 'top' => 137, 'left' => 308],
			['coords' => [8, 4, 1], 'top' => 137, 'left' => 353],
			['coords' => [1, 5, 9], 'top' => 177, 'left' =>  15],
			['coords' => [2, 5, 8], 'top' => 177, 'left' =>  60],
			['coords' => [3, 5, 7], 'top' => 177, 'left' => 105],
			['coords' => [4, 5, 6], 'top' => 177, 'left' => 150],
			['coords' => [5, 5, 5], 'top' => 177, 'left' => 195],
			['coords' => [6, 5, 4], 'top' => 177, 'left' => 240],
			['coords' => [7, 5, 3], 'top' => 177, 'left' => 285],
			['coords' => [8, 5, 2], 'top' => 177, 'left' => 330],
			['coords' => [9, 5, 1], 'top' => 177, 'left' => 375],
			['coords' => [2, 6, 9], 'top' => 217, 'left' =>  37],
			['coords' => [3, 6, 8], 'top' => 217, 'left' =>  82],
			['coords' => [4, 6, 7], 'top' => 217, 'left' => 127],
			['coords' => [5, 6, 6], 'top' => 217, 'left' => 172],
			['coords' => [6, 6, 5], 'top' => 217, 'left' => 217],
			['coords' => [7, 6, 4], 'top' => 217, 'left' => 262],
			['coords' => [8, 6, 3], 'top' => 217, 'left' => 307],
			['coords' => [9, 6, 2], 'top' => 217, 'left' => 352],
			['coords' => [3, 7, 9], 'top' => 257, 'left' =>  59],
			['coords' => [4, 7, 8], 'top' => 257, 'left' => 104],
			['coords' => [5, 7, 7], 'top' => 257, 'left' => 149],
			['coords' => [6, 7, 6], 'top' => 257, 'left' => 194],
			['coords' => [7, 7, 5], 'top' => 257, 'left' => 239],
			['coords' => [8, 7, 4], 'top' => 257, 'left' => 284],
			['coords' => [9, 7, 3], 'top' => 257, 'left' => 329],
			['coords' => [4, 8, 9], 'top' => 297, 'left' =>  81],
			['coords' => [5, 8, 8], 'top' => 297, 'left' => 126],
			['coords' => [6, 8, 7], 'top' => 297, 'left' => 171],
			['coords' => [7, 8, 6], 'top' => 297, 'left' => 216],
			['coords' => [8, 8, 5], 'top' => 297, 'left' => 261],
			['coords' => [9, 8, 4], 'top' => 297, 'left' => 306],
			['coords' => [5, 9, 9], 'top' => 337, 'left' => 103],
			['coords' => [6, 9, 8], 'top' => 337, 'left' => 148],
			['coords' => [7, 9, 7], 'top' => 337, 'left' => 193],
			['coords' => [8, 9, 6], 'top' => 337, 'left' => 238],
			['coords' => [9, 9, 5], 'top' => 337, 'left' => 283],
		];

		return $balls;
	}

	static function renderBall( array $ball, $withPlayers = true ) {
		$player = call_user_func_array(['self', 'getCoordPlayer'], $ball['coords']);
		return '<a href="#" id="ball_' . implode('_', $ball['coords']) . '" style="top: ' . $ball['top'] . 'px; left: ' . $ball['left'] . 'px" class="ball ' . $player . '" title="' . implode(', ', $ball['coords']) . '"></a>';
	}

	static function getCoordPlayer( $x, $y, $z ) {
		$balls = self::ensureAllBalls();
		$coord = implode('_', [$x, $y, $z]);
		return @$balls[$coord];
	}

	static function ensureAllBalls() {
		global $db, $arrPlayerByColor;

		if ( !self::$allBalls ) {
			self::$allBalls = $db->fetch_fields("
				SELECT CONCAT(x, '_', y, '_', z) AS coord, color
				FROM abalone_players p, abalone_balls b
				WHERE b.player_id = p.id AND p.id IN (?)
			", array($arrPlayerByColor));
		}

		return self::$allBalls;
	}

	static function initialBalls() {
		return array(
			'black' => array('1:1:5', '2:1:4', '3:1:3', '4:1:2', '5:1:1', '1:2:6', '2:2:5', '3:2:4', '4:2:3', '5:2:2', '6:2:1', '3:3:5', '4:3:4', '5:3:3'),
			'white' => array('5:7:7', '6:7:6', '7:7:5', '4:8:9', '5:8:8', '6:8:7', '7:8:6', '8:8:5', '9:8:4', '5:9:9', '6:9:8', '7:9:7', '8:9:6', '9:9:5'),
		);
	}

	static function password() {
		$password = '';
		while (strlen($password) < 30) {
			$password .= rand();
		}
		$password = preg_replace('#[^a-z0-9]#i', '', base64_encode($password));
		$password = substr($password, rand(0, strlen($password) - 10), 10);
		return $password;
	}

}

function getOpponent( $game, $player ) {
	global $db;

	$objOpponent = $db->select('abalone_players', 'game_id = ? AND id <> ?', array($player->game_id, $player->id), true);
	if ( $objOpponent ) {
		$objOpponent->balls_left = $db->count('abalone_balls', array('player_id' => $objOpponent->id));
	}

	return $objOpponent;
}

function json_respond( $object ) {
	header('Content-type: text/json');
	exit(json_encode($object));
}

function newGame(&$white, &$black) {
	global $db;

	$db->begin();

	// create game
	$db->insert('abalone_games', array(
		'turn' => rand(0, 1) ? 'white' : 'black',
		'password' => '',
	));
	$return['game'] = $gameId = $db->insert_id();

	$balls = Abalone::initialBalls();

	// create players
	foreach ( array('white', 'black') AS $color ) {
		$db->insert('abalone_players', array(
			'game_id' => $gameId,
			'password' => Abalone::password(),
			'color' => $color,
		));
		$return[$color] = $db->insert_id();

		// create balls
		foreach ( $balls[$color] AS $ball ) {
			$ball = array_combine(array('x', 'y', 'z'), explode(':', $ball));
			$ball['player_id'] = $return[$color];
			$db->insert('abalone_balls', $ball);
		}
	}

	$db->commit();

	return $return;
}


