<?php
// ABALONE

require 'inc.bootstrap.php';
require 'inc.db.php';
require '143.models.php';
Model::$_db = $db;
$db->ensureSchema(require '143.schema.php');

$login = $_GET['login'] ?? null;

if ( !$login ) {

	// New game
	if ( isset($_GET['newgame']) ) {
		$login = Game::newGame();

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
$player = Player::first(['password' => $login]);
if ( !$player ) {
	exit('Invalid login');
}

// Helper mapping
$arrPlayerByColor = array(
	$player->color => $player->id,
	$player->opponent->color => $player->opponent->id,
);



// Move
if ( isset($_POST['changes'], $_POST['move']) ) {
	if ( $player->game->turn == $player->color ) {
		try {
			$db->transaction(function($db) use ($arrPlayerByColor, $player) {
				foreach ( $_POST['changes'] as $coord => $color ) {
					$conditions = array_combine(['x', 'y', 'z'], explode('_', $coord));

					$db->delete('abalone_balls', ['player_id' => $arrPlayerByColor] + $conditions);
					if ( $color ) {
						$db->insert('abalone_balls', ['player_id' => $arrPlayerByColor[$color]] + $conditions);
					}
				}

				$player->game->update([
					'turn' => $player->opponent->color,
				]);
				Move::insert([
					'game_id' => $player->game->id,
					'player_id' => $player->id,
					'move' => json_encode($_POST['move']),
				]);
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
	$player->update([
		'online' => time(),
		'ip' => $_SERVER['REMOTE_ADDR'],
	]);

	$status = (object) array(
		'game' => (int) $player->game->id,
		'turn' => $player->game->turn,
		// 'opponent' => (int) $player->opponent->id,
		'opponentOnline' => $player->opponent->online ? time() - $player->opponent->online : -1,
		'playerBalls' => $player->balls_left,
		'opponentBalls' => $player->opponent->balls_left,
	);

	$lastMove = $player->game->last_move ?? new stdClass;

	return json_respond(compact('status', 'lastMove'));
}

$friendURL = 'https://' . $_SERVER['HTTP_HOST'] . '/143.php?login=' . $player->opponent->password;

$replaying = ($_GET['replay'] ?? '') === 'all';

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
		<?= $hole->renderHole() . "\n" ?>
	<? endforeach ?>
</div>

<? if (!$replaying): ?>
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
					<?= ucfirst($player->color) ?> (<span id="player-balls-left"><?= $player->balls_left ?></span>)
				</td>
				<td class="img"><span class="img turn"></span></td>
			</tr>
			<tr class="other">
				<td class="img"><span class="img self"></span></td>
				<td>
					<?= ucfirst($player->opponent->color) ?> (<span id="opponent-balls-left"><?= $player->opponent->balls_left ?></span>)
				</td>
				<td class="img"><span class="img turn"></span></td>
			</tr>
			<tr>
				<td colspan="3" align="center">
					<a href="143.php">Log out</a>
				</td>
			</tr>
		</table>
		<p>
			<button id="replay-last-move">Replay last move</button>
			<a href="?login=<?= $login ?>&replay=all">Replay all</a>
		</p>
	</div>
<? endif ?>

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
<? if ($replaying): ?>
	objAbalone.replayFrom(
		<?= json_encode(Abalone::initialBallsByCoord()) ?>,
		<?= json_encode(array_column($player->game->moves, 'move_array')) ?>
	);
<? else: ?>
	objAbalone.startGame('<?= $player->color ?>', <?= json_encode(Abalone::ensureAllBalls()) ?>);
	objAbalone.listenControls();
<? endif ?>
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

	public function renderHole() {
		$coord = implode('_', $this->coords);
		return '<span' . html_attributes([
			'class' => 'hole',
			'data-coord' => $coord,
			'title' => $coord,
		]) . '></span>';
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

	static function initialBallsByCoord() {
		$state = [];
		foreach ( self::initialBalls() as $color => $coords ) {
			foreach ( $coords as $coord ) {
				$state[ str_replace(':', '_', $coord) ] = $color;
			}
		}

		return $state;
	}
}

function json_respond( $object ) {
	header('Content-type: text/json');
	exit(json_encode($object));
}
