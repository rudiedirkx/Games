<?php
// MULTI PLAYER POKER 3.0

require __DIR__ . '/inc.bootstrap.php';
require 'inc.db.php';
require '195_models.php';
require 'inc.cls.cardgame.php';
require 'inc.cls.pokertexasholdem.php';

session_start();

$debug = is_local() || is_debug_ip();

Model::$_db = $db;
$db->ensureSchema(require '195_schema.php');

Card::$__tostring = fn($c) => "<img title='$c->suit' src='images/$c->suit.gif'> " . strtoupper($c->short);
// Card::$__tostring = fn(Card $card) => strtoupper($card->short) . ' ' . [
// 	'clubs' => '♣',
// 	'diamonds' => '♦',
// 	'hearts' => '♥',
// 	'spades' => '♠',
// ][$card->suit];



$player = Player::get($_GET['player'] ?? null);

if (!$player) {
	if ($table = Table::get($_GET['table'] ?? null)) {
		if (isset($_POST['join'], $_POST['name'])) {
			$player = $db->transaction(function($db) use ($table) {
				$table->touch();
				return $table->addPlayer($_POST['name']);
			});
			return do_redirect("?player=$player->password");
		}

		?>
		<meta charset="utf-8" />
		<title>Poker Texas Hold'em MULTI</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<h1>Poker Texas Hold'em MULTIPLAYER</h1>
		<h2>Join table <?= $table->id ?>?</h2>
		<p>
			In round <?= $table->round ?>.
			Last change: <?= date('Y-m-d H:i', $table->changed_on) ?>.
			<? if ($table->is_player_complete): ?><b>COMPLETE!</b> See scores:<? endif ?>
		</p>
		<p>Current players:</p>
		<ul>
			<? foreach ($table->players as $plr): ?>
				<li>
					<?= do_html($plr->name) ?>
					($ <?= $plr->balance ?>)
					(online <?= get_time_ago($plr->online_ago) ?> ago)
					<? if ($debug || in_array($plr->id, $_SESSION['p195']['pids'] ?? [])): ?>
						- <a href="?player=<?= do_html($plr->password) ?>">play as</a>
					<? endif ?>
				</li>
			<? endforeach ?>
		</ul>
		<? if ($table->round < 3): ?>
			<form method="post" action>
				<p>Your name: <input name="name" required autofocus /></p>
				<p><button name="join" value="1">JOIN GAME</button></p>
			</form>
		<? endif ?>
		<hr />
		<p>No, <a href="195.php">start a new table</a>.</p>
		<?php
		exit;
	}

	if (isset($_POST['start'], $_POST['name'])) {
		$player = Table::createNew($_POST['name']);
		return do_redirect("?player=$player->password");
	}

	?>
	<meta charset="utf-8" />
	<title>Poker Texas Hold'em MULTI</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<h1>Poker Texas Hold'em MULTIPLAYER</h1>
	<h2>Start new table?</h2>
	<form method="post" action>
		<p>Your name: <input name="name" required autofocus /></p>
		<p><button name="start" value="1">START GAME</button></p>
	</form>
	<? if ($debug):
		$tables = Table::all('1=1 order by id desc');
		Table::eager('num_players', $tables);
		?>
		<ul>
			<? foreach ($tables as $tbl): ?>
				<li>
					<a href="?table=<?= $tbl->password ?>"><?= date('j M H:i', $tbl->created_on) ?></a> -
					<?= $tbl->num_players ?> players -
					<? if ($tbl->is_player_complete): ?>
						<b>COMPLETE!</b>
					<? else: ?>
						round <?= $tbl->round ?>
					<? endif ?>
				</li>
			<? endforeach ?>
		</ul>
	<? endif ?>
	<?php
	include 'tpl.queries.php';
	exit;
}



header('Content-type: text/plain; charset=utf-8');

define('BASE', "195.php?player=$player->password");

if (!in_array($player->id, $_SESSION['p195']['pids'] ?? [])) {
	$_SESSION['p195']['pids'][] = $player->id;
}



$player->touch();

$action = $_POST['action'] ?? '';

if (isset($_GET['status'])) {
	$status = $player->getStatus();
	$hash = $status->getHash();

	$details = [];
	// if ($hash !== $_GET['status']) {
	// 	if ($status->shouldReload()) {
	// 		$details = [
	// 			'reload' => true,
	// 		];
	// 	}
	// 	else {
	// 		$details = [
	// 			'html' => (string) $status,
	// 			'pubcards' => $player->table->printCards(),
	// 			'owncards' => $player->printCards(),
	// 		];
	// 	}
	// }

	return json_respond($details + [
		'hash' => $hash,
		'onlines' => array_map('get_time_ago', array_column($player->table->players, 'online_ago', 'id')),
	]);
}

if ($action == 'start') {
	if ($player->table->state == Table::STATE_IDLE && $player->is_dealer) {
		$db->transaction(function($db) use ($player) {
			$player->update([
				'log' => "Started round",
			]);
			$player->table->update([
				'turn_player_id' => $player->table->sb_player_id,
				'round' => $player->table->round + 1,
				'state' => Table::STATE_SB,
			]);
			$player->table->dealAll();
			$player->touch();
// print_r($db->queries);
// exit;
		});
	}
	return do_redirect(BASE);
}

elseif ($action == 'sb') {
	if ($player->table->state == Table::STATE_SB && $player->is_sb) {
		$db->transaction(function($db) use ($player) {
			$player->mandatoryBet($player->table->small_blind, "Posted SB");
			$player->table->nextTurn([
				'state' => Table::STATE_BB,
			]);
			$player->touch();
		});
	}
	return do_redirect(BASE);
}

elseif ($action == 'bb') {
	if ($player->table->state == Table::STATE_BB && $player->is_bb) {
		$db->transaction(function($db) use ($player) {
			$player->mandatoryBet($player->table->big_blind, "Posted BB");
			$player->table->nextTurn([
				'state' => Table::STATE_PREFLOP,
			]);
			$player->touch();
		});
	}
	return do_redirect(BASE);
}

elseif ($action == 'check') {
	if ($player->table->isBettingState() && $player->is_turn && $player->bet == $player->table->getMaxBet()) {
		$db->transaction(function($db) use ($player) {
			$player->manualBet(0, "Checked [money:{$player->bet}]");
			$player->table->maybeEndBetting();
			$player->touch();
// print_r($db->queries);
// exit;
		});
	}
	return do_redirect(BASE);
}

elseif ($action == 'call') {
	if ($player->table->isBettingState() && $player->is_turn && $player->bet < $player->table->getMaxBet()) {
		$db->transaction(function($db) use ($player) {
			$max = $player->table->getMaxBet();
			$player->betTo($max, "Called to [money:$max]");
			$player->table->maybeEndBetting();
			$player->touch();
// print_r($db->queries);
// exit;
		});
	}
	return do_redirect(BASE);
}

elseif ($action == 'raise' && isset($_POST['raise'])) {
	if ($player->table->isBettingState() && $player->is_turn) {
		$db->transaction(function($db) use ($player) {
			$bet = max($_POST['raise'], $player->table->getMaxBet() + $player->table->raise);
			$player->betTo($bet, "Raised to [money:$bet]");
			$player->table->maybeEndBetting();
			$player->touch();
// print_r($db->queries);
// exit;
		});
	}
	return do_redirect(BASE);
}

elseif ($action == 'fold') {
	if ($player->table->isBettingState() && $player->is_turn) {
		$db->transaction(function($db) use ($player) {
			$player->fold();
			$player->table->maybeEndBetting();
			$player->touch();
// print_r($db->queries);
// exit;
		});
	}
	return do_redirect(BASE);
}

header('Content-type: text/html; charset=utf-8');

$status = $player->getStatus();

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Poker Texas Hold'em MULTI</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
body {
	background-color: #444;
	color: #aaa;
	font-family: sans-serif;
}
a {
	color: lightblue;
}
button, input[type="number"] {
	padding: 6px 9px;
}
input[type="number"] {
	width: 4em;
}
.table-wrapper {
	overflow-x: auto;
}
table {
	border-spacing: 0;
}
th {
	text-align: left;
}
th, td {
	padding: 6px 8px;
	border: solid 0px #aaa;
	border-width: 0 0 2px 0;
	vertical-align: top;
}
th {
	border-width: 2px 0 3px 0;
}
tr.self {
	background-color: #222;
}
.totals {
	font-weight: bold;
}
#state {
	height: 3em;
}
</style>
</head>

<body data-status="<?= $status->getHash() ?>">

<p>
	Round: <?= $player->table->round ?> |
	State: <?= do_html($player->table->state_label) ?> |
	Pot: $ <?= $player->table->pot ?> |
	<code><?= do_html($player->table->log) ?></code>
</p>
<p>Public cards: <span id="pubcards"><?= $player->table->printCards() ?></span></p>
<p>Your cards: <span id="owncards"><?= $player->printCards() ?></span></p>

<form id="state" method="post" action>
	<p><?= $status ?></p>
</form>

<div class="table-wrapper">
	<table>
		<tr>
			<th>Name</th>
			<th>Balance</th>
			<th>Bet</th>
			<th>Turn</th>
			<th>Role</th>
			<!-- <th>State</th> -->
			<? if ($player->table->has_showdowned): ?>
				<th>Hand</th>
			<? endif ?>
			<th>Log</th>
			<th>Online</th>
		</tr>
		<? foreach ($player->table->players as $plr): ?>
			<tr class="<?= $player == $plr ? 'self' : '' ?>">
				<td nowrap>
					<!-- <?= $plr->id ?>. -->
					<? if (is_local()): ?>
						<a href="?player=<?= do_html($plr->password) ?>"><?= do_html($plr) ?></a>
					<? else: ?>
						<?= do_html($plr) ?>
					<? endif ?>
				</td>
				<td nowrap>$ <?= $plr->balance ?></td>
				<td nowrap>$ <?= $plr->bet ?></td>
				<td align="center"><?= $plr->is_turn ? 'x' : '' ?></td>
				<td><?= implode('<br>', $plr->roles) ?></td>
				<!-- <td><?= do_html($plr->state_label) ?></td> -->
				<? if ($player->table->has_showdowned): ?>
					<td nowrap><?= $plr->state == Player::STATE_FOLDED ? '' : implode(' ', $plr->cards_objects) ?></td>
				<? endif ?>
				<td nowrap><?= do_html($plr->log_markup) ?></td>
				<td nowrap id="online-<?= $plr->id ?>"><?= get_time_ago($plr->online_ago) ?></td>
			</tr>
		<? endforeach ?>
		<tr class="totals">
			<td></td>
			<td align="right">Pot:</td>
			<td nowrap>$ <?= $player->table->pot ?></td>
			<td colspan="5"></td>
		</tr>
	</table>
</div>

<p>Share <a href="<?= do_html($player->table->url) ?>"><?= do_html($player->table->url) ?></a> to invite players.</p>

<?php include 'tpl.queries.php'; ?>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script>
setInterval(() => {
	if (document.hidden) return;
	$.get(`${location.search}&status=${document.body.dataset.status}`).on('done', (e, rsp) => {
		if (rsp.hash != document.body.dataset.status) {
// console.log(rsp);
			// document.body.dataset.status = rsp.hash;
			// if (rsp.reload) {
				location.reload();
			// }
			// else {
			// 	$('#state').setHTML(rsp.html);
			// }
		}

		// if (rsp.owncards) $('#owncards').setHTML(rsp.owncards);
		// if (rsp.pubcards) $('#pubcards').setHTML(rsp.pubcards);
		if (rsp.onlines) $.each(rsp.onlines, (time, id) => {
			const el = $(`#online-${id}`);
			if (el) el.setText(time);
		});
	});
}, 1000);
</script>
</body>

</html>
