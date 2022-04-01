<?php
// Keer op keer MULTI

require __DIR__ . '/inc.bootstrap.php';
require 'inc.db.php';
require '194_models.php';

$debug = is_local() || is_debug_ip();

Model::$_db = $db;
$db->ensureSchema(require '194_schema.php');

[$columns, $boards] = require '191_levels.php';
$mapCenter = ceil(count($columns[0]) / 2) - 1;
$maxJokers = 8;

$player = Player::get($_GET['player'] ?? null);

function printPlayersTable(Game $game, ?Player $player) {
	global $debug, $maxJokers;
	?>
	<table class="players">
		<tr>
			<th>Name</th>
			<? if (is_local() || !$game->see_all || $game->isPlayerComplete()): ?>
				<th>Score</th>
			<? endif ?>
			<th>Jokers</th>
			<th></th>
			<th align="right">Online</th>
			<th></th>
			<th></th>
		</tr>
		<? foreach ($game->players as $plr): ?>
			<tr class="<? if ($plr->id == ($player->id ?? 0)): ?>me<? endif ?>">
				<td><?= do_html($plr->name) ?></td>
				<? if (is_local() || !$game->see_all || $game->isPlayerComplete()): ?>
					<td><?= $plr->score ?></td>
				<? endif ?>
				<td nowrap><?= $maxJokers - $plr->used_jokers ?> / <?= $maxJokers ?></td>
				<td>
					<? if ($plr->is_turn): ?>TURN<? endif ?>
					<? if ($plr->is_kicked): ?>OUT<? endif ?>
				</td>
				<td align="right">
					<? if (!$plr->is_kicked): ?>
						<span id="online-<?= $plr->id ?>"><?= get_time_ago($plr->online_ago) ?></span> ago
					<? endif ?>
				</td>
				<td>
					<? if (($player->is_leader ?? false) && $plr->is_kickable): ?>
						<button data-kick="<?= $plr->id ?>">KICK</button>
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

if (!$player) {
	if ($debug && isset($_GET['delete'])) {
		if ($game = Game::get($_GET['delete'])) {
			if ($game->is_deletable) {
				$game->delete();
			}
		}
		return do_redirect('194.php');
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
					'finished_round' => $game->round,
				]);
				return $password;
			});
			return do_redirect("?player=$password");
		}

		?>
		<meta charset="utf-8" />
		<title>Keer Op Keer MULTI</title>
		<style>body { font-family: sans-serif }</style>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="<?= html_asset('keeropkeer.css') ?>" />
		<body style="--color: <?= $boards[$game->board]['color'] ?>">

		<h1>Keer Op Keer MULTIPLAYER</h1>
		<h2>Join game <?= $game->id ?>?</h2>
		<p>
			In round <?= $game->round ?>.
			Last change: <?= date('Y-m-d H:i', $game->changed_on) ?>.
			<? if ($game->isPlayerComplete()): ?><b>COMPLETE!</b> See scores:<? endif ?>
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
		<p>No, <a href="194.php">start a new game</a>.</p>
		<?php
		exit;
	}

	if (isset($_POST['start'], $_POST['name'], $_POST['board'], $_POST['see_all'])) {
		$player = Game::createNew($_POST['board'] ?: array_rand($boards), $_POST['name'], $_POST['see_all']);
		return do_redirect("?player=$player->password");
	}

	$boardNames = array_keys($boards);

	?>
	<meta charset="utf-8" />
	<title>Keer Op Keer MULTI</title>
	<style>body { font-family: sans-serif }</style>
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<h1>Keer Op Keer MULTIPLAYER</h1>
	<h2>Start new game?</h2>
	<form method="post" action>
		<p>Your name: <input name="name" required autofocus /></p>
		<p>Board: <select name="board"><?= do_html_options(array_combine($boardNames, $boardNames), null, '-- RANDOM') ?></select></p>
		<p>See all players? <select name="see_all"><?= do_html_options(['0' => 'No', '1' => 'Yes']) ?></select></p>
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
					<?= $gm->board ?> -
					<?= count($gm->players) ?> players -
					<? if ($gm->isPlayerComplete()): ?>
						<b>COMPLETE!</b>
					<? else: ?>
						round <?= $gm->round ?>
						<? if ($gm->is_deletable): ?>
							- <a href="?delete=<?= $gm->password ?>">delete</a>
						<? endif ?>
					<? endif ?>
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

elseif (isset($_GET['roll'], $_POST['colors'], $_POST['numbers'])) {
	if ($player->is_turn && $player->can_roll) {
		$db->transaction(function() use ($player) {
			$colors = $_POST['colors'];
			$numbers = array_map(fn($n) => intval($n), $_POST['numbers']);
			$player->game->update([
				'dice' => json_encode(compact('colors', 'numbers')),
				'round' => $player->game->round == 0 ? 1 : $player->game->round,
			]);
			$player->game->touch();
		});
		return json_respond(['reload' => 1]);
	}
	return json_respond(['reload' => 2]);
}

elseif (isset($_GET['endturn'], $_POST['state'], $_POST['score'], $_POST['color'], $_POST['number'])) {
	if ($player->can_end_turn) {
		$db->transaction(function($db) use ($player) {
			$jokers = $player->getUseJokersUpdate($_POST['color'] === '?', $_POST['number'] === '0');
			$player->update($jokers + [
				'finished_round' => $player->game->round,
				'board' => $_POST['state'],
				'score' => $_POST['score'],
			]);
			$player->registerFullColumns($_POST['fulls']['columns'] ?? []);
			$player->registerFullColors($_POST['fulls']['colors'] ?? []);

			if (!$player->game->free_dice && $player->is_turn) {
				$player->game->disableDice($_POST['color'], $_POST['number']);
			}

			$player->game->maybeEndRound();
			$player->game->touch();
// print_r($db->queries);
// exit;
		});
		return json_respond(['reload' => 1]);
	}
	return json_respond(['reload' => 2]);
}

elseif (isset($_GET['kick'], $_POST['pid'])) {
	$plr = $player->game->getActivePlayer($_POST['pid']);
	if ($plr && $plr->is_kickable) {
		$db->transaction(function($db) use ($plr) {
			$plr->kick();
			$plr->game->maybeEndRound();
// print_r($db->queries);
// exit;
		});
		return json_respond(['reload' => 1]);
	}
	return json_respond(['reload' => 2]);
}

$status = $player->getStatus();

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Keer Op Keer MULTI</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#333" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('keeropkeer.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('keeropkeer.js') ?>"></script>
</head>

<body class="layout multi" style="--color: <?= $boards[$player->game->board]['color'] ?>">

<table id="board" class="board game">
	<thead>
		<tr>
			<? foreach ($columns[0] as $i => $cell): ?>
				<td data-col="<?= $i ?>" class="<?= $mapCenter == $i ? 'center' : '' ?>"><?= $cell ?></td>
			<? endforeach ?>
		</tr>
	</thead>
	<tbody id="grid"></tbody>
	<tfoot>
		<? foreach (array_slice($columns, 1, 2) as $row): ?>
			<tr>
				<? foreach ($row as $i => $cell): ?>
					<td class="full-column" data-col="<?= $i ?>" data-score="<?= $cell ?>"><?= $cell ?></td>
				<? endforeach ?>
			</tr>
		<? endforeach ?>
	</tfoot>
</table>

<div class="meta">
	<div id="status" data-hash="<?= $status->getHash() ?>"><?= $status ?></div>
	<div id="dice" class="dice-line"></div>

	<div class="colors-stats">
		<table class="colors game">
			<? foreach ([5, 3] as $score): ?>
				<tr>
					<? foreach (['g', 'y', 'b', 'p', 'o'] as $color): ?>
						<td class="full-color" data-color="<?= $color ?>" data-score="<?= $score ?>"><?= $score ?></td>
					<? endforeach ?>
				</tr>
			<? endforeach ?>
		</table>
		<p id="stats">
			Round: <span class="value" id="stats-round"><?= $player->game->round ?></span><br>
			Jokers: <span class="value" id="stats-jokers">8 / 8</span><br>
			Score: <span class="value" id="stats-score">?</span><br>
		</p>
	</div>

	<p style="margin-bottom: 0">Players:</p>
	<? printPlayersTable($player->game, $player) ?>
	<p>Share <a href="<?= do_html($player->game->url) ?>"><?= do_html($player->game->url) ?></a> to invite players.</p>
</div>

<? if ($player->game->see_all): ?>
	<? foreach ($player->game->players as $plr): if ($player->id != $plr->id): ?>
		<div>
			<h2 style="margin-bottom: 0"><?= do_html($plr) ?></h2>
			<div
				class="other-player-board"
				data-board='<?= json_encode(do_html($plr->board ?: '')) ?>'
				data-columns='<?= json_encode($plr->getOthersColumns()) ?>'
				data-colors='<?= json_encode($plr->getOthersColors()) ?>'
			></div>
		</div>
	<? endif; endforeach ?>
<? endif ?>

<script>
KeerOpKeer.JOKERS = <?= json_encode($maxJokers) ?>;
KeerOpKeer.CENTER = <?= json_encode($mapCenter) ?>;
KeerOpKeer.BOARDS = <?= json_encode($boards) ?>;
var objGame = new MultiKeerOpKeer($('#grid'));
objGame.startGame(
	<?= json_encode($player->game->board) ?>,
	<?= json_encode($player->board ?: '') ?>,
	<?= (int) $player->used_jokers ?>,
	<?= json_encode($player->getOthersColumns()) ?>,
	<?= json_encode($player->getOthersColors()) ?>,
);
objGame.listenControls();
<? if ($player->game->dice): ?>objGame.importDice(<?= json_encode($player->game->dice_array) ?>);<? endif ?>
</script>
