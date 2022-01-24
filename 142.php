<?php
// MULTI PLAYER POKER

// Todo
// ->> Make `state` a TINYINT, not ENUM - Update everywhere it's used!
// - Save bets differently
// - First player in table is dealer
// - Dealer can start round when >= 2 players
// - Update `turn` and `ready_for_next_round` and force small and big blinds
// - Rotate through players untill bets are equal within `in_or_out` and all `ready_for_next_round`
// - Sitouts due to lack of chips are determined PRE GAME

exit('disabled');

// misc preparation
session_start();

define( 'BASEPAGE',			'/142.php' );
define( 'S_NAME',			'mpp_142_v2' );

define( 'TABLE_USERS',		'p142_users' );
define( 'TABLE_TABLES',		'p142_tables' );
define( 'TABLE_PLAYERS',	'p142_players' );
define( 'TABLE_BETS',		'p142_bets' );
define( 'TABLE_POOLS',		'p142_pools' );
define( 'MAX_PLAYERS_EVER',	10 );

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

require_once('inc.cls.cardgame.php');
require_once('inc.cls.pokertexasholdem.php');


if ( isset($_GET['card']) ) {
	card::$__tostring = function($c) { return "images/" . $c->suit . "_" . $c->short . ".gif"; };
	$objCard = new Card($_GET['card']);
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', 0).' GMT');
	header('Content-type: image/gif');
	readfile((string)$objCard);
	exit;
}


// Two stages for every user: logged in, not logged in

// not logged in
if ( !loginCheck() ) {
	if ( isset($_POST['username'], $_POST['password']) ) {
		$szMessage = 'ERROR';
		$arrUser = $db->select(TABLE_USERS, "username = '".addslashes($_POST['username'])."'")->first();
		if ( $arrUser ) {
			$arrSession = array(
				'ip'	=> ifsetor($_SERVER['REMOTE_ADDR'], ""),
				'uid'	=> $arrUser['id'],
			);
			$_SESSION[S_NAME] = $arrSession;
			$szMessage = 'LOGGED_IN';
		}
		header('Location: '.BASEPAGE);
		exit;
	}

?>
<html>

<head>
<title>MPP :: OUT</title>
</head>

<body style="overflow:auto;" onload="document.forms[0]['username'].focus();">
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
<tr valign="middle">
	<td align="center">
		<form method="post" action="">
		<table border="0" cellpadding="0" cellspacing="0" width="300" align="center">
		<tr>
			<td colspan="2" align="center">Please log in!</td>
		</tr>
		<tr>
			<td>Username</td>
			<td><input type="text" name="username" value="" /></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input type="password" name="password" value="" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" value="Login" /></td>
		</tr>
		</table>
		</form>
	</td>
</tr>
</table>
</body>

</html>
<?php

	exit;
}



// Two stages for every logged in user: viewing one table, viewing table list

// on a table
if ( isset($_GET['table']) && ($arrTable=$db->select(TABLE_TABLES, 'id = '.(int)$_GET['table'])->first()) ) {
	define( 'TABLE_ID', (int)$arrTable['id'] );
	// $db->transaction(function($db) use ($arrTable) {
		$arrTable = getTableDetails($arrTable);
// print_r($db->queries);
// exit;
// 	});

	$arrPlayer = $db->select(TABLE_PLAYERS, 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID)->first();
	$bPlaying = !!$arrPlayer;
	if ( $arrPlayer ) {
		$db->update(TABLE_PLAYERS, 'online = '.time(), 'user_id = '.USER_ID.' AND table_id = '.TABLE_ID);

		// leave table //
		if ( isset($_GET['leave_seat']) ) {
			if ( $db->delete(TABLE_PLAYERS, 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID.' AND balance = '.(float)$arrPlayer['balance']) && 0 < $db->affected_rows() ) {
				$db->update(TABLE_USERS, 'balance=balance+'.(float)$arrPlayer['balance'], 'id = '.USER_ID);
			}
			header('Location: ?table='.TABLE_ID);
			exit;
		}

		// post action / contribute //
		if ( isset($_POST['action']) ) {
			if ( $arrPlayer['seat'] !== $arrTable['current_seat'] || '1' === $arrPlayer['sit_out'] || 'in' !== $arrPlayer['in_or_out'] ) {
				exit('It\'s not your turn!');
			}
			switch ( $_POST['action'] ) {
				case 'start':
					if ( 0 === $arrTable['state'] ) {
						// Shuffle deck
						$arrDeck = range(0, 51);
						shuffle($arrDeck);
						// Filter players with too few chips AND deal cards to the rest
						foreach ( $arrTable['seats'] AS $iSeat => $p ) {
							if ( $p['balance'] < $arrTable['big_blind'] ) {
								$db->update(TABLE_PLAYERS, 'sit_out = \'1\'', 'table_id = '.TABLE_ID.' AND user_id = '.$p['user_id']);
							}
							else if ( '1' !== $p['sit_out'] ) {
								$db->update(TABLE_PLAYERS, 'in_or_out = \'in\', private_card_1 = '.array_shift($arrDeck).', private_card_2 = '.array_shift($arrDeck), 'table_id = '.TABLE_ID.' AND user_id = '.$p['user_id']);
							}
						}
						// Move dealer to next seat
						$arrUpdate = array(
							'state' => '1',
							'dealer_seat' => nextSeat(getSeats(), $arrTable['dealer_seat']),
							// Save 5 public cards
							'public_card_1' => array_shift($arrDeck),
							'public_card_2' => array_shift($arrDeck),
							'public_card_3' => array_shift($arrDeck),
							'public_card_4' => array_shift($arrDeck),
							'public_card_5' => array_shift($arrDeck),
						);
						// Move player to seat after dealer
						$arrUpdate['current_seat'] = nextSeat(getSeats(), $arrUpdate['dealer_seat']);
						$db->update(TABLE_TABLES, $arrUpdate, 'id = '.TABLE_ID);
						// 'Activate' players
						$db->update(TABLE_PLAYERS, 'in_or_out = \'in\'', 'table_id = '.TABLE_ID.' AND sit_out = \'0\'');
						$db->update(TABLE_PLAYERS, 'last_action = \'\'', 'table_id = '.TABLE_ID);
						// Remove old pools and add first of this game
						$db->delete(TABLE_POOLS, 'table_id = '.TABLE_ID);
						$db->insert(TABLE_POOLS, array('table_id' => TABLE_ID));
						// Update last action of USER
						$db->update(TABLE_PLAYERS, 'last_action = \'started game\'', 'user_id = '.USER_ID.' AND table_id = '.TABLE_ID);
						exit('OK');
					}
				break;
				case 'check':
					if ( sqlBetween($arrTable['state'], 3, 6) ) {
						$arrBets = getUserBets();
						$fCurrentMaxBet = max($arrBets);
						if ( $fCurrentMaxBet > $arrBets[USER_ID] ) {
							exit('You can\'t check! It costs you '.($fCurrentMaxBet-$arrBets[USER_ID]).' chips to call!');
						}
						$db->update(TABLE_PLAYERS, 'ready_for_next_round = \'1\', last_action = \'checked on '.$fCurrentMaxBet.'\'', 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID);
						if ( !checkAndActOnAllSameBets($arrTable) ) {
							saveNextSeat($arrTable);
						}
						exit('OK');
					}
				break;
				case 'call':
						if ( sqlBetween($arrTable['state'], 3, 6) ) {
						$arrBets = getUserBets();
						$fCurrentMaxBet = max($arrBets);
						$fTargetBet = $fCurrentMaxBet;
						$fBetNeeded = $fTargetBet - $arrBets[USER_ID];
						if ( true === ($msg=addUserBet($fBetNeeded)) ) {
							if ( !checkAndActOnAllSameBets($arrTable) ) {
								saveNextSeat($arrTable);
							}
							$db->update(TABLE_PLAYERS, 'ready_for_next_round = \'1\', last_action = \'called to '.(float)$fTargetBet.'\'', 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID);
							exit('OK');
						}
						else {
							exit($msg);
						}
					}
				break;
				case 'fold':
					if ( sqlBetween($arrTable['state'], 3, 6) ) {
						$db->update(TABLE_PLAYERS, 'ready_for_next_round = \'1\', in_or_out = \'out\'', 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID);
						if ( !checkAndActOnAllSameBets($arrTable) ) {
							saveNextSeat($arrTable);
						}
						exit('OK');
					}
				break;
				case 'bet/raise':
					if ( sqlBetween($arrTable['state'], 3, 6) ) {
						$arrBets = getUserBets();
						$fCurrentMaxBet = max($arrBets);
						$fTargetBet = $fCurrentMaxBet + $arrTable['big_blind'];
						$fBetNeeded = $fTargetBet - $arrBets[USER_ID];
						if ( true !== ($msg=addUserBet($fBetNeeded)) ) {
							exit($msg);
						}
						saveNextSeat($arrTable);
						$db->update(TABLE_PLAYERS, 'last_action = \'raised to '.(float)$fTargetBet.'\'', 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID);
						exit('OK');
					}
				break;
				case 'smallblind':
					if ( 1 === $arrTable['state'] ) {
						addUserBet($arrTable['small_blind']);
						saveNextSeat($arrTable, 2);
						$db->update(TABLE_PLAYERS, 'last_action = \'posted small blind\'', 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID);
						exit('OK');
					}
				break;
				case 'bigblind':
					if ( 2 === $arrTable['state'] ) {
						addUserBet($arrTable['big_blind']);
						saveNextSeat($arrTable, 3);
						$db->update(TABLE_PLAYERS, 'last_action = \'posted big blind\'', 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID);
						exit('OK');
					}
				break;
			}
			exit('Invalid action!');
		}
	}

	// fetch table details //
	if ( isset($_GET['fetch_table']) ) {
		$arrGameStates = array(	'ready for next game',
								'posting small blind',
								'posting big blind',
								'first betting round', // 0 cards open
								'second betting round', // 3 cards open
								'third betting round', // 4 cards open
								'fourth betting round', // 5 cards open
								'[showdown??]' );
		$arrSeats = array_fill(0, MAX_PLAYERS_EVER+1, false);
		$iCards = 0 === $arrTable['state'] || 6 <= $arrTable['state'] ? 5 : ( 5 === $arrTable['state'] ? 4 : ( 4 === $arrTable['state'] ? 3 : 0 ) );
		$arrCards = array((int)$arrTable['public_card_1'], (int)$arrTable['public_card_2'], (int)$arrTable['public_card_3'], (int)$arrTable['public_card_4'], (int)$arrTable['public_card_5']);
		$arrCards = array_slice($arrCards, 0, $iCards, false);
		$arrSeats[0] = array(
			'msgs' => [
				$bPlaying ? '<a href="?table='.TABLE_ID.'&leave_seat=1">leave seat</a>' : '',
				'Game state: '.$arrGameStates[$arrTable['state']].' ('.$arrTable['state'].')',
			],
			'cards' => array_map(function(int $n) {
				$card = new Card($n);
				return [$n, $card->fullname()];
			}, $arrCards),
			'dealer' => (int)$arrTable['dealer_seat'],
			'current' => (int)$arrTable['current_seat'],
			'player' => $bPlaying ? (int)$arrPlayer['seat'] : null,
			'buttons' => $bPlaying && $arrTable['current_seat'] == $arrPlayer['seat'] ? array('check', 'call', 'fold', 'bet/raise (BB)') : array(),
			'actions' => $bPlaying && $arrTable['current_seat'] == $arrPlayer['seat'] ? array('start', 'smallblind ('.$arrTable['small_blind'].')', 'bigblind ('.$arrTable['big_blind'].')') : array(),
		);
		$arrS = $arrTable['seats'];
		foreach ( $arrS AS $s ) {
			$arrSeats[(int)$s['seat']] = array(
				'user' => array((int)$s['user_id'], $s['username']),
				'lastAction' => $s['last_action'],
				'action' => '1' === $s['sit_out'] ? 'SITTING OUT' : ( 'in' !== $s['in_or_out'] ? 'FOLDED / IDLE' : ( $arrTable['current_seat'] == $s['seat'] ? 'Thinking??' : 'Waiting...' ) ),
				'notes' => '[notes]',
				'balance' => number_format($s['balance'], 2),
				'bet' => number_format((float)$s['total_bet'], 2),
				'inRound' => 'in' === $s['in_or_out'],
				'cards' => ((((int)$s['user_id'] === USER_ID && 3 <= $arrTable['state']) || (0 === $arrTable['state'] && 'in' === $s['in_or_out'] && null !== $s['private_card_1'] && null !== $s['private_card_2'])) && ($c1=new Card($s['private_card_1'])) && ($c2=new Card($s['private_card_2']))) ? array(array((int)$s['private_card_1'], $c1->fullname()), array((int)$s['private_card_2'], $c2->fullname())) : [],
			);
		}
		return json_respond($arrSeats);
	}

	// join table //
	if ( isset($_POST['take_seat'], $_POST['balance']) ) {
		$iSeat = min(MAX_PLAYERS_EVER, max(1, (int)$_POST['take_seat']));
		// check if user is playing this table already
		if ( 0 < $db->count(TABLE_PLAYERS, 'table_id = '.TABLE_ID.' AND user_id = '.USER_ID) ) {
			exit('You already joined this table... Refresh?');
		}
		// check if seat is already taken
		else if ( 0 < $db->count(TABLE_PLAYERS, 'table_id = '.TABLE_ID.' AND seat = \''.$iSeat."'") ) {
			exit('This seat ('.$iSeat.') is already taken!');
		}

		// seat is free and player is not playing this table -> have a seat!
		$iBalance = max($arrTable['big_blind']*2, $_POST['balance']);
		$db->update(TABLE_USERS, 'balance = balance-'.(int)$iBalance, 'balance >= '.(int)$iBalance.' AND id = '.USER_ID);
		if ( 1 > $db->affected_rows() ) {
			exit('You don\'t have '.$iBalance.' chips! Rebuy real-time, leave another table, or join a cheaper table!');
		}
		$arrInsert = array(
			'user_id'		=> USER_ID,
			'table_id'		=> TABLE_ID,
			'seat'			=> (string)$iSeat,
			'balance'		=> $iBalance,
			'in_or_out'		=> 'out',
			'sit_out'		=> '0',
			'online'		=> time(),
		);
		$db->insert(TABLE_PLAYERS, $arrInsert);
		if ( 1 == ($iPlayers=(int)$db->count(TABLE_PLAYERS, 'sit_out = \'0\' AND table_id = '.TABLE_ID)) ) {
			$db->update(TABLE_TABLES, 'dealer_seat = '.$iSeat, 'id = '.TABLE_ID);
		}
		echo $db->error();
		exit('OK');
	}

?>
<html>

<head>
<title>MPP :: T<?php echo TABLE_ID; ?><?php if ( $bPlaying ) { echo ' :: '.$g_arrUser['username']; } ?></title>
<script src="/js/mootools_1_11.js"></script>
<script>
var TABLE_ID = <?php echo TABLE_ID; ?>;
var USER_ID = <?php echo USER_ID; ?>;
var g_objTable = <?php echo json_encode(array('smallBlind' => $arrTable['small_blind'], 'bigBlind' => $arrTable['big_blind'], 'dealer' => (int)$arrTable['dealer_seat'], 'current' => (int)$arrTable['current_seat'])); ?>;
var g_objUser = <?php echo json_encode(array('balance' => (float)$g_arrUser['balance'])); ?>;
var g_iPlayerSeat;
var g_noticeTimer = 0;
function unsetNotice() {
	$('ajax_msg').innerHTML = '';
	$('ajax_msg').style.backgroundColor = '';
	$('ajax_msg').style.color = '';
	g_noticeTimer = 0;
}
function setNotice(notice, keep) {
	if ( g_noticeTimer ) { clearTimeout(g_noticeTimer); }
	$('ajax_msg').innerHTML = notice;
	$('ajax_msg').style.backgroundColor = 'red';
	$('ajax_msg').style.color = 'white';
	if ( !keep ) {
		g_noticeTimer = setTimeout(unsetNotice, 2000);
	}
}
function doAction(f_action) {
	if ( true || g_iPlayerSeat === g_objTable.current ) {
		new Ajax('?table=' + TABLE_ID, {
			data : 'action=' + f_action.split(' ')[0],
			onComplete : function(t) {
				if ( 'OK' !== t ) {
					setNotice(t);
				}
				else {
					fetchTable();
				}
			}
		}).request();
	}
	return false;
}
function fetchTable() {
	new Ajax('?table=' + TABLE_ID + '&fetch_table=1', {onComplete : function(t) {
		try { var x = eval('('+t+')'); } catch (ex) { return setNotice(ex.message+"\n"+t, true); }
		$A(x).each(drawSeat);
	}}).request();
	return false;
}
function renderCard(card) {
	return `<img width="50" height="67" src="?card=${card[0]}" title="${card[1]}" />`;
}
function drawCenter(info) {
	$('msgs').innerHTML = info.msgs.join(' &nbsp; | &nbsp; ');
	$('cards').innerHTML = info.cards.map(renderCard).join('');
	$('buttons').innerHTML = '';
	$A(info.buttons).each(function(bt) {
		$('buttons').innerHTML += '<input type="button" value="' + bt + '" onclick="doAction(this.value);" />';
	});
	$('actions').innerHTML = '';
	$A(info.actions).each(function(bt) {
		$('actions').innerHTML += '<input type="button" value="' + bt + '" onclick="doAction(this.value);" />';
	});
	g_objTable.dealer = info.dealer;
	g_objTable.current = info.current;
	g_iPlayerSeat = info.player;
}
function drawSeat(seat, id) {
	if ( 0 == id ) {
		return drawCenter(seat);
	}
	var obj = $('seat_'+id);
	if ( seat ) {
		obj.className = seat.user[0] === USER_ID ? 'player' : 'taken';
		if ( g_objTable.dealer === id ) {
			obj.className += ' dealer';
		}
		if ( g_objTable.current === id ) {
			obj.className += ' current';
		}
		var t = makeEmptySeat();
		t.rows[0].cells[2].innerHTML = id + ' (' + seat.user[0] + ')';
		t.rows[0].cells[0].innerHTML = seat.lastAction;
		t.rows[0].cells[1].innerHTML = seat.action;
		t.rows[2].cells[0].innerHTML = seat.notes;
		t.rows[1].cells[1].innerHTML = seat.user[1];
		t.rows[1].cells[0].innerHTML = 'balance<br />'+seat.balance;
		t.rows[1].cells[2].innerHTML = 'bet<br />' + seat.bet;
		t.rows[2].cells[2].innerHTML = g_objTable.dealer === id ? '<b>dealer</b>' : '';
		t.rows[2].cells[1].setAttribute('nowrap', '1');
		t.rows[2].cells[1].innerHTML = seat.cards.map(renderCard).join('');
		t.rows[1].cells[1].style.borderColor = seat.inRound ? 'green' : 'red';
		attachSeat(obj, t);
		delete t;
	}
	else {
		obj.className = 'open';
		obj.innerHTML = 'EMPTY SEAT ' + id + ( null === g_iPlayerSeat ? '<br /><a href="#' + id + '" onclick="return joinTableSeat(' + id + ');">take seat</a>' : '' );
	}
}
function makeEmptySeat() {
	var t = document.createElement('table');
	t.setAttribute('width', '100%');
	t.setAttribute('height', '100%');
	for ( var i=0; i<3; i++ ) {
		var r = t.insertRow(t.rows.length);
		for ( var j=0; j<3; j++ ) {
			r.insertCell(r.cells.length);
		}
	}
	t.rows[1].cells[1].className = 'center';
	delete r;
	return t;
}
function attachSeat(obj, seat) {
	while ( 0 < obj.childNodes.length ) {
		obj.removeChild(obj.firstChild);
	}
	obj.appendChild(seat);
	delete obj, seat;
}
function joinTableSeat( f_iSeat ) {
	var iBalance = prompt('Balance:', ''+Math.min(50*g_objTable.bigBlind, g_objUser.balance));
	if ( !iBalance ) {
		return false;
	}
	var szUrl = '?table=' + TABLE_ID;
	new Ajax(szUrl, {
		data : 'take_seat=' + f_iSeat + '&balance=' + iBalance,
		onComplete	: function(t) {
			if ( 'OK' != t ) {
				alert(t);
			}
			else {
				fetchTable();
			}
		}
	}).request();
	return false;
}
var g_interval;
window.onload = function() {
	// Ajax.setGlobalHandlers({
	// 	onStart : function() { $('seat_0').style.backgroundColor = 'red'; },
	// 	onComplete : function() { if ( 0 == Ajax.busy ) { $('seat_0').style.backgroundColor = ''; } }
	// });
	g_interval = setInterval(fetchTable, 5000);
	if ( !window.console || 'function' != typeof window.console.debug ) {
		window.console = {debug:function(){}};
	}
	fetchTable();
}
</script>
<style>
* {
	padding				: 0;
	margin				: 0;
}
table {
	border-collapse		: collapse;
}
td {
	width				: 25%;
	height				: 33%;
	text-align			: center;
	vertical-align		: middle;
	border				: solid 1px #666;
	background-color	: #fff;
}
td#seat_0 {
	background-color	: #ddd;
	width				: 50%;
}
td#seat_0 div {
	margin				: 3px;
	padding				: 3px;
}
td#seat_0 div#buttons input,
td#seat_0 div#actions input {
	margin				: 0px 10px;
	padding				: 3px 10px;
}
table table td {
	border				: none;
	width				: 33%;
/*	height				: 33%;*/
	padding				: 5px;
}
table td table td {
	background-color	: #eee;
}
table td.player table td {
	background-color	: #ccc;
}
table td table td.center {
	background-color	: #fff;
	font-weight			: bold;
	border				: solid 1px #999;
}
body table#mpp tr td.current table tr td.center {
	background-color	: #87cefa;
}
body table#mpp tr td.dealer table tr td.center {
	border-width		: 3px;
}
</style>
</head>

<body style="overflow:auto;">
<table id="mpp" width="100%" height="100%">
<tr><?php printSeats(array(9, 10, 1, 2)); ?></tr>
<tr><?php printSeats(8); ?><td colspan="2" id="seat_0"><div id="ajax_msg"></div><div id="msgs"></div><div id="cards"></div><div id="buttons"></div><div id="actions"></div></td><?php printSeats(3); ?></tr>
<tr><?php printSeats(array(7, 6, 5, 4)); ?></tr>
</tr>
</table>
</body>

</html>
<?php

	exit;
}


if ( isset($_GET['logout']) ) {
	unset($_SESSION[S_NAME]);
	header('Location: '.BASEPAGE);
	exit;
}

// not on a table (print tables)
$arrTables = $db->fetch('SELECT *, (SELECT COUNT(1) FROM '.TABLE_PLAYERS.' WHERE table_id = t.id AND user_id = '.USER_ID.') AS joined FROM '.TABLE_TABLES.' t ORDER BY id ASC');

?>
<html>

<head>
<title>MPP :: IN</title>
<style>
td { text-align:center; }
</style>
</head>

<body style="overflow:auto;">
<table border="1" cellpadding="0" cellspacing="0" width="300" align="center">
<tr>
	<th width="150">Username</th>
	<td width="150"><?php echo $g_arrUser['username'].' ('.USER_ID; ?>)</td>
</tr>
<tr>
	<th width="150">Liquid balance</th>
	<td width="150"><?php echo number_format($g_arrUser['balance'], 2); ?> chips</td>
</tr>
<tr>
	<th width="150">Locked in games</th>
	<td width="150"><?php echo number_format($db->select_one(TABLE_PLAYERS, 'SUM(balance)', 'user_id = '.USER_ID), 2); ?> chips</td>
</tr>
<tr>
	<td width="300" colspan="2"><a href="?logout=1">log out</a></td>
</tr>
</table>

<br />

<table border="1" cellpadding="0" cellspacing="0" width="600" align="center">
<tr>
	<th>Table</th>
	<th>Blinds</th>
	<th># Players</th>
	<th>&nbsp;</th>
</tr>
<?php

foreach ( $arrTables AS $arrTable ) {
	echo '<tr>';
	echo '<td>Table '.$arrTable['id'].'</td>';
	echo '<td>'.(float)$arrTable['small_blind'].' / '.(float)$arrTable['big_blind'].'</td>';
	echo '<td'.( 0 < (int)$arrTable['joined'] ? ' bgcolor="#dddddd"' : '' ).'>'.$db->count(TABLE_PLAYERS, 'table_id = '.$arrTable['id']).'</td>';
	echo '<td><a href="?table='.$arrTable['id'].'">Open table</a></td>';
	echo '</tr>'."\n";
}

?>
</table>
</body>

</html>
<?php

exit;









function sqlBetween($x, $a, $b) {
	return (int)$x >= (int)$a && (int)$x <= (int)$b;
}

function checkAndActOnAllSameBets($f_arrTable) {
	global $db;
	$arrBets = getUserBets();
	if ( 1 == count($arrBets) ) {
		// Only one player left
		$fTotalPot = $db->select_one(TABLE_BETS.' b, '.TABLE_POOLS.' po', 'SUM(bet)', 'b.pool_id = po.id AND po.table_id = '.TABLE_ID);
		$iUserId = key($arrBets);
		$db->update(TABLE_PLAYERS, 'balance = balance+'.$iUserId.', last_action = \'Won '.(float)$fTotalPot.'\' due to fold', 'table_id = '.TABLE_ID.' AND user_id = '.$iUserId);
		$db->update(TABLE_TABLES, 'state = 0', 'id = '.TABLE_ID);
		return true;
	}
	$iReady = $db->count(TABLE_PLAYERS, 'table_id = '.TABLE_ID.' AND in_or_out = \'in\'');
	if ( $iReady == count($arrBets) && 1 == count(array_flip($arrBets)) ) {
		if ( 6 === (int)$f_arrTable['state'] ) {
			// Showdown
			$fTotalPot = $db->select_one(TABLE_BETS.' b, '.TABLE_POOLS.' po', 'SUM(bet)', 'b.pool_id = po.id AND po.table_id = '.TABLE_ID);
			$arrPubCards = array(new Card($f_arrTable['public_card_1']), new Card($f_arrTable['public_card_2']), new Card($f_arrTable['public_card_3']), new Card($f_arrTable['public_card_4']), new Card($f_arrTable['public_card_5']));
			$arrWinners = array();
			$fMaxScore = 0.0;
			foreach ( $arrBets AS $iUserId => $b ) {
				$p = $db->select(TABLE_PLAYERS, 'table_id = '.TABLE_ID.' AND user_id = '.$iUserId)->first();
				$arrPrivCards = array(new Card($p['private_card_1']), new Card($p['private_card_2']));
				$fScore = PokerTexasHoldem::score(array_merge($arrPubCards, $arrPrivCards));
				if ( $fScore > (float)$fMaxScore ) {
					$fMaxScore = $fScore;
					$arrWinners = array();
				}
				if ( (float)$fScore === (float)$fMaxScore ) {
					$arrWinners[] = $iUserId;
				}
			}
			$fUserWinnings = floor($fTotalPot*100/count($arrWinners))/100;
			$db->update(TABLE_PLAYERS, 'balance = balance+'.$fUserWinnings.', last_action = \'Won ['.$fUserWinnings.'] with a ['.PokerTexasHoldem::readable_hand($fMaxScore).']\'', 'table_id = '.TABLE_ID.' AND user_id IN ('.implode(',', $arrWinners).')');
			$db->update(TABLE_TABLES, 'state = 0', 'id = '.TABLE_ID);
			return true;
		}
		$db->update(TABLE_PLAYERS, 'ready_for_next_round = \'0\'', 'table_id = '.TABLE_ID);
		$f_arrTable['current_seat'] = $f_arrTable['dealer_seat'];
		saveNextSeat($f_arrTable, 'state+1');
		return true;
	}
	return false;
}

function getUserBets() {
	global $db;
	$arrBets = $db->fetch_fields('SELECT pl.user_id, IFNULL(SUM(b.bet), 0) AS bet FROM '.TABLE_POOLS.' po, '.TABLE_PLAYERS.' pl LEFT JOIN '.TABLE_BETS.' b ON b.user_id = pl.user_id WHERE pl.sit_out = \'0\' AND pl.in_or_out = \'in\' AND po.table_id = '.TABLE_ID.' GROUP BY pl.user_id');
	return $arrBets;
}

function addUserBet($f_iBet) {
	global $db;
	$arrPool = $db->select(TABLE_POOLS, 'table_id = '.TABLE_ID.' ORDER BY id DESC')->first();
	if ( $db->update(TABLE_PLAYERS, 'balance = balance-'.$f_iBet, 'balance >= '.$f_iBet.' AND table_id = '.TABLE_ID.' AND user_id = '.USER_ID) && 0 < $db->affected_rows() ) {
		if ( 0 < $db->count(TABLE_BETS, 'pool_id = '.$arrPool['id'].' AND user_id = '.USER_ID) ) {
			$db->update(TABLE_BETS, 'bet = bet+'.$f_iBet, 'user_id = '.USER_ID.' AND pool_id = '.$arrPool['id']);
		}
		else {
			$db->insert(TABLE_BETS, array('pool_id' => $arrPool['id'], 'user_id' => USER_ID, 'bet' => $f_iBet));
		}
		return true;
	}
	return 'You don\'t have enough chips!';
} // END saveNextSeat()

function getSeatInfo( $f_iSeat ) {
	global $db;
	$iSeat = min(MAX_PLAYERS_EVER, max(1, $f_iSeat));
	return $db->fetch("
	SELECT
		u.*,
		p.*
	FROM
		".TABLE_USERS." u,
		".TABLE_PLAYERS." p
	WHERE
		(p.seat = '".$iSeat."') AND
		(p.table_id = ".TABLE_ID.") AND
		(p.user_id = u.id);
	");
} // END getSeatInfo()

function saveNextSeat($arrTable, $f_szNewState = null) {
	global $db;
	$iNextSeat = nextSeat($arrTable['seats'], $arrTable['current_seat']);
	return $db->update(TABLE_TABLES, ( null !== $f_szNewState ? 'state = '.$f_szNewState.', ' : '' ).'current_seat = '.$iNextSeat, 'id = '.$arrTable['id']);
} // END saveNextSeat()

function nextSeat( $f_arrSeats, $f_iCurrentSeat ) {
	if ( !isset($f_arrSeats[$f_iCurrentSeat]) || 2 > count($f_arrSeats) ) {
		return min(array_keys($f_arrSeats));
	}
	ksort($f_arrSeats, SORT_NUMERIC);
	$next = false;
	foreach ( $f_arrSeats AS $iSeat => $x ) {
		if ( $next && '0' === $x['sit_out'] && 'in' === $x['in_or_out'] ) {
			return $iSeat;
		}
		else if ( (int)$f_iCurrentSeat === (int)$iSeat ) {
			$next = true;
		}
	}
	return min(array_keys($f_arrSeats));
} // END nextSeat()

function getSeats() {
	global $db;
	$arrSeats = $db->fetch('SELECT *, (SELECT SUM(b.bet) FROM '.TABLE_BETS.' b, '.TABLE_POOLS.' pl WHERE pl.id = b.pool_id AND b.user_id = u.id AND pl.table_id = p.table_id) AS total_bet FROM '.TABLE_USERS.' u, '.TABLE_PLAYERS.' p WHERE u.id = p.user_id AND p.table_id = '.TABLE_ID);
	$arrPlayersBySeat = array();
	foreach ( $arrSeats AS $s ) {
		$arrPlayersBySeat[(int)$s['seat']] = $s;
	}
	return $arrPlayersBySeat;
}

function getTableDetails( $arrTable ) {
	global $db;
	$arrTable['state'] = (int)$arrTable['state'];
	$arrTable['seats'] = $arrPlayersBySeat = getSeats();
// print_r($arrTable);
// print_r($arrPlayersBySeat);
// exit;
	if ( !isset($arrPlayersBySeat[$arrTable['current_seat']]) || $arrPlayersBySeat[$arrTable['current_seat']]['sit_out'] || 'in' !== $arrPlayersBySeat[$arrTable['current_seat']]['in_or_out'] ) {
		$arrTable['current_seat'] = $db->select_one(TABLE_PLAYERS, 'seat', 'sit_out = \'0\' AND in_or_out = \'in\' AND table_id = '.$arrTable['id'].' ORDER BY seat>='.(int)$arrTable['current_seat'].' DESC, seat ASC');
		$db->update(TABLE_TABLES, 'current_seat = '.(int)$arrTable['current_seat'], 'id = '.$arrTable['id']);
	}
	if ( !isset($arrPlayersBySeat[$arrTable['dealer_seat']]) || $arrPlayersBySeat[$arrTable['dealer_seat']]['sit_out'] ) {
		$arrTable['dealer_seat'] = $db->select_one(TABLE_PLAYERS, 'seat', 'sit_out = \'0\' AND table_id = '.$arrTable['id'].' ORDER BY seat>='.(int)$arrTable['dealer_seat'].' DESC, seat ASC');
		$db->update(TABLE_TABLES, 'dealer_seat = '.(int)$arrTable['dealer_seat'], 'id = '.$arrTable['id']);
	}
	return $arrTable;
}

function printSeats($f_seats) {
	echo '<td class="open" id="seat_'.implode('"></td><td class="open" id="seat_', (array)$f_seats).'"></td>';
}

function money($f_fAmount) {
	return number_format((float)$f_fAmount, 2, '.', ' ');
}

function randString( $f_iLength = 8 ) {
	$arrTokens = array_merge( range("a","z"), range("A","Z"), range("0","9") );
	$szRandString = "";
	for ( $i=0; $i<max(1, (int)$f_iLength); $i++ ) {
		$szRandString .= $arrTokens[array_rand($arrTokens)];
	}
	return $szRandString;
} // END randString()

function loginCheck() {
	global $db;
	if ( defined('USER_ID') ) {
		return true;
	}
	// session
	if ( empty($_SESSION[S_NAME]) ) {
		return false;
	}
	$arrSession = $_SESSION[S_NAME];
	// variables
	if ( !is_array($arrSession) || !isset($arrSession['uid'], $arrSession['ip']) ) {
		return false;
	}
	// ip check
	if ( empty($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] !== $arrSession['ip'] ) {
		return false;
	}
	// user check in db
	$arrUser = $db->select(TABLE_USERS, 'id = '.(int)$arrSession['uid'])->first();
	if ( !$arrUser ) {
		return false;
	}
	global $g_arrUser;
	$g_arrUser = $arrUser;
	// update online time
	$db->update(TABLE_USERS, 'online = '.time(), 'id = '.(int)$arrSession['uid']);
	if ( !defined('USER_ID') ) {
		define( 'USER_ID', (int)$arrSession['uid'] );
	}
	return true;

} // END loginCheck()

function exitWithError( $f_bAct, $f_szMsg )
{
	unset($_SESSION[S_NAME]);
	if ( $f_bAct ) exit($f_szMsg);
	if ( !defined('USER_ID') ) define( 'USER_ID', 0 );
	return false;

} // END exitWithError()

function ifsetor( &$f_mvFirst, $f_mvSecond = null )
{
	return isset($f_mvFirst) ? $f_mvFirst : $f_mvSecond;

} // END ifsetor()
