<?php
// LABYRINTH
// https://www.youtube.com/watch?v=6ECL-bH_GAw

require __DIR__ . '/inc.bootstrap.php';
require 'inc.db.php';
require '201_models.php';

header('Cache-Control: no-store, no-cache, must-revalidate');

$debug = is_local() || is_debug_ip();

Model::$_db = $db;
$db->ensureSchema(require '201_schema.php');

function printPlayersTable(Game $game, ?Player $player) {
	global $debug;
	?>
	<table class="players">
		<tr>
			<th>Name</th>
			<th></th>
			<th align="right">Online</th>
			<th></th>
		</tr>
		<? foreach ($game->players as $plr): ?>
			<tr class="player <? if ($plr->id == ($player->id ?? 0)): ?>me<? endif ?>">
				<td><?= do_html($plr->name) ?></td>
				<td>
					<? if ($plr->is_turn): ?>TURN<? endif ?>
				</td>
				<td align="right">
					<? if (!$plr->is_kicked): ?>
						<span id="online-<?= $plr->id ?>"><?= get_time_ago($plr->online_ago) ?></span> ago
					<? endif ?>
				</td>
				<td>
					<? if ($plr->id != ($player->id ?? 0) && ($debug || Player::inHistory($plr->id))): ?>
						<a href="?player=<?= do_html($plr->password) ?>">PLAY</a>
					<? endif ?>
				</td>
			</tr>
		<? endforeach ?>
	</table>
	<?php
}

$player = Player::get($_GET['player'] ?? null);

if (!$player) {
	if ($debug && isset($_GET['delete'])) {
		if ($game = Game::get($_GET['delete'])) {
			if ($game->is_deletable) {
				$game->delete();
			}
		}
		return do_redirect('201.php');
	}

	if ($game = Game::get($_GET['game'] ?? null)) {
		if (isset($_POST['join'], $_POST['name']) && $game->is_joinable) {
			$password = $db->transaction(function() use ($game) {
				$game->touch();
				Player::insert([
					'game_id' => $game->id,
					'online' => time(),
					'password' => $password = get_random(),
					'name' => $_POST['name'],
				]);
				return $password;
			});
			return do_redirect("?player=$password");
		}

		?>
		<meta charset="utf-8" />
		<title>Labyrinth MULTI</title>
		<style>body { font-family: sans-serif }</style>
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<h1>Labyrinth MULTIPLAYER</h1>
		<h2>Join game <?= $game->id ?>?</h2>
		<p>
			In round <?= $game->round ?>.
			Last change: <?= date('Y-m-d H:i', $game->changed_on) ?>.
		</p>
		<p>Current players:</p>
		<? printPlayersTable($game, null) ?>
		<? if ($game->is_joinable): ?>
			<form method="post" action>
				<p>Your name: <input name="name" required autofocus /></p>
				<p><button name="join" value="1">JOIN GAME</button></p>
			</form>
		<? endif ?>
		<hr />
		<p>No, <a href="201.php">start a new game</a>.</p>
		<?php
		include 'tpl.queries.php';
		exit;
	}

	if (isset($_POST['start'], $_POST['name'])) {
		$player = Game::createNew($_POST['name']);
		return do_redirect("?player=$player->password");
	}

	?>
	<meta charset="utf-8" />
	<title>Labyrinth MULTI</title>
	<style>body { font-family: sans-serif }</style>
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<h1>Labyrinth MULTIPLAYER</h1>
	<h2>Start new game?</h2>
	<form method="post" action>
		<p>Your name: <input name="name" required autofocus /></p>
		<p><button name="start" value="1">START GAME</button></p>
	</form>
	<? if ($debug):
		$games = Game::all('1=1 order by id desc limit 20');
		Game::eager('players', $games);
		?>
		<ul>
			<? foreach ($games as $gm): ?>
				<li>
					<a href="?game=<?= $gm->password ?>"><?= date('j M H:i', $gm->created_on) ?></a> -
					<?= count($gm->players) ?> players -
					round <?= $gm->round ?>
				</li>
			<? endforeach ?>
		</ul>
	<? endif ?>
	<?php
	include 'tpl.queries.php';
	exit;
}



Player::addHistory($player->id);

if (isset($_GET['status'])) {
	$status = $player->getStatus();
	$player->touch();
	return json_respond([
		'status' => $status->getHash(),
		'onlines' => array_map('get_time_ago', array_column($player->game->players, 'online_ago', 'id')),
	]);
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Labyrinth</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="<?= html_asset('labyrinth.css') ?>" />
<? include 'tpl.onerror.php' ?>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('labyrinth.js') ?>"></script>
</head>

<body>

<canvas></canvas>

<div class="meta">
	<div class="key-stats">
		<canvas id="key"></canvas>
		<div>
			<p id="status">??</p>
			<p>
				Round: <?= $player->game->round ?>
			</p>
		</div>
	</div>
	<p>Targets: <span id="targets"></span></p>

	<p>Players:</p>
	<? printPlayersTable($player->game, $player) ?>

	<p>Share <a href="<?= do_html($player->game->url) ?>"><?= do_html($player->game->url) ?></a> to invite players.</p>
</div>

<script>
objGame = new MultiLabyrinth($('canvas'), $('#key'));
objGame.startGame('InOrder', <?= json_encode($player->game->tiles) ?>);
objGame.setPlayers(<?= $player->player_index ?>, <?= $player->game->turn_player_index ?>, <?= json_encode($player->game->player_metas) ?>)
objGame.listenControls();
objGame.startPainting();
</script>
</body>

</html>
