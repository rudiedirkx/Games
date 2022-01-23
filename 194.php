<?php
// Keer op keer MULTI

require __DIR__ . '/inc.bootstrap.php';
require 'inc.db.php';
require '194_models.php';

session_start();

$debug = is_local() || is_debug_ip();

Model::$_db = $db;
$db->ensureSchema(require '194_schema.php');

[$columns, $boards] = require '191_levels.php';
$mapCenter = ceil(count($columns[0]) / 2) - 1;

$player = Player::get($_GET['player'] ?? null);

if (!$player) {
	if ($game = Game::get($_GET['game'] ?? null)) {
		if (isset($_POST['join'], $_POST['name'])) {
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
		<title>Keer Op Keer MULTI</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<h1>Keer Op Keer MULTIPLAYER</h1>
		<h2>Join game <?= $game->id ?>?</h2>
		<p>
			In round <?= $game->round ?>.
			Last change: <?= date('Y-m-d H:i', $game->changed_on) ?>.
			<? if ($game->is_color_complete): ?><b>COMPLETE!</b> See scores:<? endif ?>
		</p>
		<p>Current players:</p>
		<ul>
			<? foreach ($game->players as $plr): ?>
				<li>
					<?= do_html($plr->name) ?>
					(score <?= $plr->score ?>)
					<? if ($debug || in_array($plr->id, $_SESSION['keeropkeer']['pids'] ?? [])): ?>
						- <a href="?player=<?= do_html($plr->password) ?>">play as</a>
					<? endif ?>
				</li>
			<? endforeach ?>
		</ul>
		<? if ($game->round < 3): ?>
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

	if (isset($_POST['start'], $_POST['name'], $_POST['board'])) {
		$player = Game::createNew($_POST['board'] ?: array_rand($boards), $_POST['name']);
		return do_redirect("?player=$player->password");
	}

	$boardNames = array_keys($boards);

	?>
	<meta charset="utf-8" />
	<title>Keer Op Keer MULTI</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<h1>Keer Op Keer MULTIPLAYER</h1>
	<h2>Start new game?</h2>
	<form method="post" action>
		<p>Your name: <input name="name" required autofocus /></p>
		<p>Board: <select name="board"><?= do_html_options(array_combine($boardNames, $boardNames), null, '-- RANDOM') ?></select></p>
		<p><button name="start" value="1">START GAME</button></p>
	</form>
	<? if ($debug):
		$games = Game::all('1=1 order by id desc');
		Game::eager('num_players', $games);
		?>
		<ul>
			<? foreach ($games as $gm): ?>
				<li>
					<a href="?game=<?= $gm->password ?>"><?= date('j M H:i', $gm->created_on) ?></a> -
					<?= $gm->board ?> -
					<?= $gm->num_players ?> players -
					round <?= $gm->round ?>
				</li>
			<? endforeach ?>
		</ul>
	<? endif ?>
	<?php
	exit;
}



if (!in_array($player->id, $_SESSION['keeropkeer']['pids'] ?? [])) {
	$_SESSION['keeropkeer']['pids'][] = $player->id;
}

$status = $player->getStatus();

if (isset($_GET['status'])) {
	$player->touch();
	return json_respond([
		'status' => $status->getHash(),
		'onlines' => array_column($player->game->players, 'online_ago', 'id'),
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
			$jokers = $player->getUseJokersUpdate($_POST['color'] === '?', $_POST['number'] === '?');
			$player->update($jokers + [
				'finished_round' => $player->game->round,
				'board' => $_POST['state'],
				'score' => $_POST['score'],
			]);
			$player->registerFullColumns($_POST['fulls']['columns'] ?? []);
			$player->registerFullColors($_POST['fulls']['colors'] ?? []);

			if ($player->game->round > 1 && $player->is_turn) {
				$player->game->disableDice($_POST['color'], $_POST['number']);
			}

			if ($player->game->allPlayersTurnReady()) {
				$player->game->endRound();
			}

			$player->game->touch();
// print_r($db->queries);
// exit;
		});
		return json_respond(['reload' => 1]);
	}
	return json_respond(['reload' => 2]);
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Keer Op Keer MULTI</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<link rel="stylesheet" href="<?= html_asset('keeropkeer.css') ?>" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script src="<?= html_asset('keeropkeer.js') ?>"></script>
</head>

<body>

<table class="board">
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
	<div id="dice"></div>

	<div class="colors-stats">
		<table>
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

	<p>Players:</p>
	<ul>
		<? foreach ($player->game->players as $plr): ?>
			<li <? if ($plr->id == $player->id): ?>style="color: lime"<? endif ?>>
				<?= do_html($plr->name) ?>
				(score <?= $plr->score ?>)
				<? if ($plr->is_turn): ?>(TURN)<? endif ?>
				(online <span id="online-<?= $plr->id ?>"><?= time() - $plr->online ?></span> sec ago)
				<? if ($debug): ?>
					- <a href="?player=<?= do_html($plr->password) ?>">play as</a>
				<? endif ?>
			</li>
		<? endforeach ?>
	</ul>
	<p>Share <a href="<?= do_html($player->game->url) ?>"><?= do_html($player->game->url) ?></a> to invite players.</p>
</div>

<script>
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
<? if ($player->game->dice): ?>objGame.printDice(<?= json_encode($player->game->dice_array) ?>);<? endif ?>
</script>
