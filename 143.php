<?php
// ABALONE

require 'inc.bootstrap.php';
require 'inc.db.php';
$db->ensureSchema(require '143.schema.php');

$login = $_GET['login'] ?? null;

if ( !$login ) {

	// New game
	if ( isset($_GET['newgame']) ) {
		$login = newGame();

		header("Location: 143.php?login=$login");
		exit('Game created');
	}

	// Log in form
	?>
	<title>Abalone: log in</title>
	<style>html, body, h1 { margin: 0; } body { padding: 30px; }</style>

	<h1>Abalone: log in</h1>

	<form action method="get">
		<p>Password: <input name="login" /></p>
		<p><button>Log in</button></p>
	</form>

	<p><a href="?newgame=1">Start a new game for 2</a></p>
	<?php

	exit;
}



// Load player
$objPlayer = $db->select('abalone_players', array('password' => $login), null, true);
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
if ( isset($_POST['changes'], $_POST['move']) ) {
	if ( $objGame->turn == $objPlayer->color ) {
		try {
			$db->transaction(function($db) use ($arrPlayerByColor, $objOpponent, $objGame) {
				foreach ( $_POST['changes'] as $coord => $color ) {
					$conditions = array_combine(['x', 'y', 'z'], explode('_', $coord));

					$db->delete('abalone_balls', ['player_id' => $arrPlayerByColor] + $conditions);
					if ( $color ) {
						$db->insert('abalone_balls', ['player_id' => $arrPlayerByColor[$color]] + $conditions);
					}
				}

				$db->update('abalone_games', array(
					'turn' => $objOpponent->color,
					'last_move' => json_encode($_POST['move']),
				), array('id' => $objGame->id));
			});
			return json_respond(array('error' => 0));
		}
		catch (db_exception $ex) {
			return json_respond(array('error' => $ex->getMessage()));
		}
	}

	return json_respond(array('error' => 'Not your turn!'));
}

// Fetch status
else if ( isset($_GET['status']) ) {
	$db->update('abalone_players', [
		'online' => time(),
		'ip' => $_SERVER['REMOTE_ADDR'],
	], ['id' => $objPlayer->id]);

	$status = (object) array(
		'turn' => $objGame->turn,
		// 'opponent' => (int) $objOpponent->id,
		'opponentOnline' => $objOpponent->online ? time() - $objOpponent->online : -1,
		'playerBalls' => $objPlayer->balls_left,
		'opponentBalls' => $objOpponent->balls_left,
	);

	$lastMove = json_decode($objGame->last_move) ?: new stdClass;

	return json_respond(compact('status', 'lastMove'));
}

$friendURL = 'https://' . $_SERVER['HTTP_HOST'] . '/143.php?login=' . $objOpponent->password;

?>
<html>

<head>
<title>Abalone</title>
<link rel="stylesheet" href="<?= html_asset('143.css') ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="utf-8" />
<style>
<? foreach (Abalone::holes() as $hole): ?>
[data-coord="<?= implode('_', $hole->coords) ?>"] { --left: <?= $hole->left ?>; --top: <?= $hole->top ?>; }
<? endforeach ?>
</style>
</head>

<body>

<h1>Abalone</h1>

<p class="friend-url">Share <a href="<?= $friendURL ?>"><?= $friendURL ?></a> with your friend.</p>

<div id="board">
	<div class="shape"></div>

	<a href="#" class="direction tl" data-dir="-1_-1_0" title="-1, -1, 0"></a>
	<a href="#" class="direction tr" data-dir="0_-1_-1" title="0, -1, -1"></a>
	<a href="#" class="direction r"  data-dir="1_0_-1" title="1, 0, -1"></a>
	<a href="#" class="direction br" data-dir="1_1_0" title="1, 1, 0"></a>
	<a href="#" class="direction bl" data-dir="0_1_1" title="0, 1, 1"></a>
	<a href="#" class="direction l"  data-dir="-1_0_1" title="-1, 0, 1"></a>

	<? foreach (Abalone::holes() as $hole): ?>
		<?= Abalone::renderHole($hole) . "\n" ?>
	<? endforeach ?>
</div>

<div id="players">
	<table class="players">
		<tr>
			<th>You</th>
			<th></td>
			<th>Turn</th>
		</tr>
		<tr class="self">
			<td class="img"><span class="img self"></span></td>
			<td>
				<?= ucfirst($objPlayer->color) ?> (<span id="player-balls-left"><?= $objPlayer->balls_left ?></span>)
			</td>
			<td class="img"><span class="img turn"></span></td>
		</tr>
		<tr class="other">
			<td class="img"><span class="img self"></span></td>
			<td>
				<?= ucfirst($objOpponent->color) ?> (<span id="opponent-balls-left"><?= $objOpponent->balls_left ?></span>)
			</td>
			<td class="img"><span class="img turn"></span></td>
		</tr>
		<tr>
			<td colspan="3" align="center">
				<a href="143.php">Log out</a>
			</td>
		</tr>
	</table>
	<p><button id="replay-last-move">Replay last move</button></p>
</div>

<div id="help">
	<h2>What's this then?</h2>
	<p><a href="http://en.wikipedia.org/wiki/Abalone_(board_game)">It's Abalone.</a></p>
	<p>Click your balls, and then an arrow.</p>
</div>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>THREE = {}</script>
<script src="<?= html_asset('143.js') ?>"></script>
<script>
objAbalone = new Abalone($('#board'));
objAbalone.startGame('<?= $objPlayer->color ?>', <?= json_encode(Abalone::ensureAllBalls()) ?>);
objAbalone.listenControls();
</script>

</body>

</html>
<?php

class AbalonePosition {
	public $coords;
	public $top;
	public $left;
	public function __construct(array $coords, float $top, float $left) {
		$this->coords = $coords;
		$this->top = $top;
		$this->left = $left;
	}
}

class Abalone {

	static public $allBalls = [];

	static function holes() {
		return [
			new AbalonePosition([1, 1, 5],  0, 2.0),
			new AbalonePosition([2, 1, 4],  0, 3.0),
			new AbalonePosition([3, 1, 3],  0, 4.0),
			new AbalonePosition([4, 1, 2],  0, 5.0),
			new AbalonePosition([5, 1, 1],  0, 6.0),
			new AbalonePosition([1, 2, 6],  1, 1.5),
			new AbalonePosition([2, 2, 5],  1, 2.5),
			new AbalonePosition([3, 2, 4],  1, 3.5),
			new AbalonePosition([4, 2, 3],  1, 4.5),
			new AbalonePosition([5, 2, 2],  1, 5.5),
			new AbalonePosition([6, 2, 1],  1, 6.5),
			new AbalonePosition([1, 3, 7],  2, 1.0),
			new AbalonePosition([2, 3, 6],  2, 2.0),
			new AbalonePosition([3, 3, 5],  2, 3.0),
			new AbalonePosition([4, 3, 4],  2, 4.0),
			new AbalonePosition([5, 3, 3],  2, 5.0),
			new AbalonePosition([6, 3, 2],  2, 6.0),
			new AbalonePosition([7, 3, 1],  2, 7.0),
			new AbalonePosition([1, 4, 8],  3, 0.5),
			new AbalonePosition([2, 4, 7],  3, 1.5),
			new AbalonePosition([3, 4, 6],  3, 2.5),
			new AbalonePosition([4, 4, 5],  3, 3.5),
			new AbalonePosition([5, 4, 4],  3, 4.5),
			new AbalonePosition([6, 4, 3],  3, 5.5),
			new AbalonePosition([7, 4, 2],  3, 6.5),
			new AbalonePosition([8, 4, 1],  3, 7.5),
			new AbalonePosition([1, 5, 9],  4, 0.0),
			new AbalonePosition([2, 5, 8],  4, 1.0),
			new AbalonePosition([3, 5, 7],  4, 2.0),
			new AbalonePosition([4, 5, 6],  4, 3.0),
			new AbalonePosition([5, 5, 5],  4, 4.0),
			new AbalonePosition([6, 5, 4],  4, 5.0),
			new AbalonePosition([7, 5, 3],  4, 6.0),
			new AbalonePosition([8, 5, 2],  4, 7.0),
			new AbalonePosition([9, 5, 1],  4, 8.0),
			new AbalonePosition([2, 6, 9],  5, 0.5),
			new AbalonePosition([3, 6, 8],  5, 1.5),
			new AbalonePosition([4, 6, 7],  5, 2.5),
			new AbalonePosition([5, 6, 6],  5, 3.5),
			new AbalonePosition([6, 6, 5],  5, 4.5),
			new AbalonePosition([7, 6, 4],  5, 5.5),
			new AbalonePosition([8, 6, 3],  5, 6.5),
			new AbalonePosition([9, 6, 2],  5, 7.5),
			new AbalonePosition([3, 7, 9],  6, 1.0),
			new AbalonePosition([4, 7, 8],  6, 2.0),
			new AbalonePosition([5, 7, 7],  6, 3.0),
			new AbalonePosition([6, 7, 6],  6, 4.0),
			new AbalonePosition([7, 7, 5],  6, 5.0),
			new AbalonePosition([8, 7, 4],  6, 6.0),
			new AbalonePosition([9, 7, 3],  6, 7.0),
			new AbalonePosition([4, 8, 9],  7, 1.5),
			new AbalonePosition([5, 8, 8],  7, 2.5),
			new AbalonePosition([6, 8, 7],  7, 3.5),
			new AbalonePosition([7, 8, 6],  7, 4.5),
			new AbalonePosition([8, 8, 5],  7, 5.5),
			new AbalonePosition([9, 8, 4],  7, 6.5),
			new AbalonePosition([5, 9, 9],  8, 2.0),
			new AbalonePosition([6, 9, 8],  8, 3.0),
			new AbalonePosition([7, 9, 7],  8, 4.0),
			new AbalonePosition([8, 9, 6],  8, 5.0),
			new AbalonePosition([9, 9, 5],  8, 6.0),
		];
	}

	static function renderHole(AbalonePosition $hole) {
		$coord = implode('_', $hole->coords);
		return '<span' . html_attributes([
			// 'href' => '#',
			// 'id' => "ball_$coord",
			// 'style' => "--left: {$hole->left}; --top: {$hole->top}",
			'class' => 'hole',
			'data-coord' => $coord,
			'title' => $coord,
		]) . '></span>';
	}

	// static function getCoordPlayer( $x, $y, $z ) {
	// 	$balls = self::ensureAllBalls();
	// 	$coord = implode('_', [$x, $y, $z]);
	// 	return $balls[$coord] ?? '';
	// }

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
		return strtoupper($password);
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

function newGame() {
	global $db;

	$db->begin();

	// create game
	$turn = rand(0, 1) ? 'white' : 'black';
	$db->insert('abalone_games', array(
		'turn' => $turn,
		'password' => '',
	));
	$gid = $db->insert_id();

	$balls = Abalone::initialBalls();

	// create players
	$login = null;
	foreach ( array('white', 'black') AS $color ) {
		$password = Abalone::password();
		$db->insert('abalone_players', array(
			'game_id' => $gid,
			'password' => $password,
			'color' => $color,
		));
		$pid = $db->insert_id();

		if ($color == $turn) {
			$login = $password;
		}

		// create balls
		foreach ( $balls[$color] AS $ball ) {
			$ball = array_combine(array('x', 'y', 'z'), explode(':', $ball));
			$ball['player_id'] = $pid;
			$db->insert('abalone_balls', $ball);
		}
	}

	$db->commit();

	return $login;
}


