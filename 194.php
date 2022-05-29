<?php
// Keer op keer MULTI

require __DIR__ . '/inc.bootstrap.php';
require 'inc.db.php';
require '194_models.php';

header('Cache-Control: no-store, no-cache, must-revalidate');

$debug = is_local() || is_debug_ip();

Model::$_db = $db;
$db->ensureSchema(require '194_schema.php');

[$columns, $boards] = require '191_levels.php';
$mapCenter = ceil(count($columns[0]) / 2) - 1;

$player = Player::get($_GET['player'] ?? null);

function printPlayersTable(Game $game, ?Player $player) {
	global $debug;
	?>
	<table class="players" style="margin-top: 1em">
		<tr>
			<th>Player</th>
			<? if (is_local() || !$game->see_all || $game->isPlayerComplete()): ?>
				<th>Score</th>
			<? endif ?>
			<th>Jokers</th>
			<th></th>
			<th align="right">Online</th>
			<th></th>
		</tr>
		<? foreach ($game->players as $plr): ?>
			<tr
				id="plr-<?= $plr->id ?>"
				class="
					<? if ($plr->id == ($player->id ?? 0)): ?>me<? endif ?>
					<?= $player && $plr->is_kickable ? 'kickable' : '' ?>
					<?= $plr->is_kicked ? 'kicked' : '' ?>
					<?= $plr->is_turn ? 'turn' : '' ?>
				"
			>
				<td>
					<span class="name"><?= do_html($plr->name) ?></span>
					<span class="turn">&#127922;</span>
					<? if ($game->isPlayerComplete() && $game->winner == $plr): ?>
						<span class="winner">&#127881;</span>
					<? endif ?>
				</td>
				<? if (is_local() || !$game->see_all || $game->isPlayerComplete()): ?>
					<td><span id="score-<?= $plr->id ?>"><?= $plr->score ?></span></td>
				<? endif ?>
				<td nowrap><span id="jokers-left-<?= $plr->id ?>"><?= Game::MAX_JOKERS - $plr->used_jokers ?></span> / <?= Game::MAX_JOKERS ?></td>
				<td><button class="kick" data-kick="<?= $plr->id ?>">KICK</button></td>
				<td align="right" nowrap>
					<? if (!$plr->is_kicked): ?>
						<span id="online-<?= $plr->id ?>"><?= $plr->online_ago_text ?></span>
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
			try {
				$password = $game->addPlayer($_POST['name']);
				return do_redirect("?player=$password");
			}
			catch (MultiPlayerException $ex) {
				return do_redirect("?game=$game->password&error=" . $ex->getCode());
			}
		}

		?>
		<meta charset="utf-8" />
		<title>Keer Op Keer # <?= $game->id ?></title>
		<style>body { font-family: sans-serif }</style>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="<?= html_asset('keeropkeer.css') ?>" />
		<body style="--color: <?= $boards[$game->board]['color'] ?>; --text: <?= $boards[$game->board]['text'] ?? '#fff' ?>">

		<h1>Keer Op Keer MULTIPLAYER</h1>
		<? if (!empty($_GET['error'])): ?>
			<p class="error"><?= do_html((new MultiPlayerException($_GET['error']))->getMessage()) ?></p>
		<? endif ?>
		<h2>Join game <?= $game->id ?>?</h2>
		<p>
			In round <?= $game->round ?>.
			Last change: <?= date('Y-m-d H:i', $game->changed_on) ?>.
			<? if ($game->isPlayerComplete()): ?><b>COMPLETE!</b> See scores:<? endif ?>
		</p>
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
		include 'tpl.queries.php';
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

	<h2>Your games</h2>
	<?php
	if ($debug) {
		$games = Game::all('1=1 order by id desc limit 20');
	}
	else {
		$ids = Player::getHistory();
		$players = Player::finds($ids);
		$games = Player::eager('game', $players);
		usort($games, fn($a, $b) => $b->created_on - $a->created_on);
	}
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
						- <a href="?delete=<?= $gm->password ?>" onclick="return confirm('DELETE GAME?')">delete</a>
					<? endif ?>
				<? endif ?>
			</li>
		<? endforeach ?>
	</ul>
	<?php
	include 'tpl.queries.php';
	exit;
}



Player::addHistory($player->id);

if (isset($_GET['status'])) {
	$status = $player->getStatus();
	$player->touch();
	return json_respond($status->toResponseArray($_GET['status']));
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

			$player->clear();
			$player->game->clear();
// print_r($player->getStatus()->toResponseArray());
// exit;
		});
		return json_respond([
			'reload' => 0,
			'status' => $player->getStatus()->toResponseArray(),
		]);
	}
	return json_respond(['reload' => 2]);
}

elseif (isset($_GET['endturn'], $_POST['state'], $_POST['score'], $_POST['color'], $_POST['number'])) {
	if ($player->can_end_turn) {
		$ended = $db->transaction(function($db) use ($player) {
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

			$ended = $player->game->maybeEndRound();
			$player->game->touch();

			$player->clear();
			$player->game->clear();
// var_dump($ended, $player->is_turn);
// print_r($player->getStatus()->toResponseArray());
// print_r($db->queries);
// exit;
			return $ended;
		});
		return json_respond([
			'reload' => 0,
			'status' => $player->getStatus()->toResponseArray(),
		]);
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
<title>Keer Op Keer # <?= $player->game->id ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#333" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('keeropkeer.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('keeropkeer.js') ?>"></script>
</head>

<body class="layout multi">

<? if (is_local()): ?>
	<div style="position: fixed; right: 5px; top: 5px; background: #000; color: #fff"><?= rand(10, 99) ?></div>
<? endif ?>

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
			<? foreach ([5, 3, 0] as $score): ?>
				<tr>
					<? foreach (Game::COLORS as $color): ?>
						<? if ($score): ?>
							<td class="full-color" data-color="<?= $color ?>" data-score="<?= $score ?>"><?= $score ?></td>
						<? else: ?>
							<td class="transparent"><span id="color-<?= $color ?>-players">0</span>P</td>
						<? endif ?>
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

	<? printPlayersTable($player->game, $player) ?>

	<? if (is_local()): ?>
		<pre id="debug"></pre>
	<? endif ?>

	<p>Share <a href="<?= do_html($player->game->url) ?>"><?= do_html($player->game->url) ?></a> to invite players.</p>
</div>

<? if ($player->game->see_all): ?>
	<? foreach ($player->game->players as $plr): if ($player->id != $plr->id): ?>
		<div>
			<h2 style="margin-bottom: 0"><?= do_html($plr) ?></h2>
			<div class="other-player-board" id="board-<?= $plr->id ?>"></div>
		</div>
	<? endif; endforeach ?>
<? endif ?>

<div id="no-connection">No connection?</div>

<script>
KeerOpKeer.JOKERS = <?= json_encode(Game::MAX_JOKERS) ?>;
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
