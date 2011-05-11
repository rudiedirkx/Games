<?php

// misc preparation
session_start();

define( 'BASEPAGE',					basename($_SERVER['SCRIPT_NAME']) );
define( 'SESSION_NAME',				'GAME_142_MPP' );

define( 'TABLE_PLAYERS',			'142_users' );
define( 'TABLE_GAMES',				'142_tables' );
define( 'TABLE_ROUNDS',				'142_rounds' );
define( 'TABLE_CARDS',				'142_cards' );
define( 'TABLE_PLAYERS_IN_GAMES',	'142_players' );
define( 'TABLE_CARDS_IN_GAMES',		'142_cards_in_games' );
define( 'MAX_PLAYERS_EVER',			10 );

require_once('inc.db_mysql.php');
db_set(db_connect('localhost', 'usager', 'usager', 'games'));



// CLEAN UP //
#db_delete(TABLE_PLAYERS_IN_GAMES, 'last_online+16 < '.time());



// Two stages for every user: logged in, not logged in

// not logged in
if ( !logincheck() )
{
	if ( isset($_POST['username'], $_POST['password']) )
	{
		$szMessage = 'FOUT';
		$arrUser = db_select(TABLE_PLAYERS, "username = '".addslashes($_POST['username'])."' AND password = MD5(CONCAT(id,':".addslashes($_POST['password'])."'))");
		if ( 1 == count($arrUser) )
		{
			$arrSession = array(
				'hash'	=> randString(20),
				'ip'	=> ifsetor($_SERVER['REMOTE_ADDR'], ""),
				'uid'	=> $arrUser[0]['id'],
			);
			db_update(TABLE_PLAYERS, array('hash' => $arrSession['hash']), "id = '".$arrSession['uid']."'");
			$_SESSION[SESSION_NAME] = $arrSession;
			$szMessage = 'INGELOGD';
		}

		header("Location: ".BASEPAGE."?msg=".$szMessage);
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
			<form method="post" action="<?php echo BASEPAGE; ?>">
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


// logged in
if ( isset($_GET['keepalive']) )
{
	if ( isset($_GET['game']) ) {
		db_update(TABLE_PLAYERS_IN_GAMES, array('last_online' => time()), "player_id = '".USER_ID."' AND game_id = '".(int)$_GET['game']."'");
		if ( 0 >= db_affected_rows() ) {
//			exit('You were exiled!');
		}
	}
	exit('OK');
}



// Two stages for every logged in user: viewing one table, viewing table list

// on a table
if ( isset($_GET['game']) && ($arrGame=db_select(TABLE_GAMES, 'id = '.(int)$_GET['game'])) && 1 == count($arrGame) )
{
	$arrGame = $arrGame[0];
	define( 'GAME_ID', (int)$arrGame['id'] );

	if ( isset($_GET['take_seat'], $_GET['balance']) )
	{
		$iSeat = min(MAX_PLAYERS_EVER, max(1, (int)$_GET['take_seat']));
		// check if user is playing this table already
		if ( 0 < db_count(TABLE_PLAYERS_IN_GAMES, "game_id = '".GAME_ID."' AND player_id = '".USER_ID."'") )
		{
			exit('You already joined this table... Refresh?');
		}
		// check if seat is already taken
		else if ( 0 < db_count(TABLE_PLAYERS_IN_GAMES, "game_id = '".GAME_ID."' AND seat = '".$iSeat."'") )
		{
			exit('This seat is already taken!');
		}

		// seat is free and player is not playing this table -> have a seat!
		$iBalance = max($arrGame['small_blind']*2, $_GET['balance']);
		db_update(TABLE_PLAYERS, 'balance = balance-'.(int)$iBalance, 'balance >= '.(int)$iBalance.' AND id = '.USER_ID);
		if ( 1 != db_affected_rows() ) {
			exit('Your balance is too low for this table! Rebuy real-time, leave another table, or join a cheaper table!');
		}
		$arrInsert = array(
			'user_id'		=> USER_ID,
			'table_id'		=> GAME_ID,
			'seat'			=> $iSeat,
			'balance'		=> $iBalance,
			'in_for_round'	=> '0',
			'last_online'	=> time(),
		);
		db_insert(TABLE_PLAYERS_IN_GAMES, $arrInsert);
		echo db_error();
		exit('OK');
	}

	$bPlaying = 0 < db_count(TABLE_PLAYERS_IN_GAMES, 'game_id = '.GAME_ID.' AND player_id = '.USER_ID);
	$szEmptySeat = ( !$bPlaying ? '<a href="#%1$s" onclick="return joinTableSeat(%1$s);">' : '' ) . 'EMPTY SEAT %1$s' . ( !$bPlaying ? '</a>' : '' );

	// Update online status for this user on this table
	db_update(TABLE_PLAYERS_IN_GAMES, array('last_online' => time()), "user_id = '".USER_ID."' AND table_id = '".GAME_ID."'");

	function printSeat($f_iSeat) {
		global $szEmptySeat;
		$iSeat = min(MAX_PLAYERS_EVER, max(1, (int)$f_iSeat));
		$arrSeat = getSeatInfo($iSeat);
		$szHtml = '';
		if ( $arrSeat ) {
			$arrPlayer = $arrSeat[0];
			$szHtml .= '<td'.( (int)$arrPlayer['user_id'] === USER_ID ? ' bgcolor="#eeeeee"' : '' ).'>';
			$szHtml .= '<table width="100%" height="100%">';
			$szHtml .= '<tr>';
			$szHtml .= '<td></td>';
			$szHtml .= '<td>[action]</td>';
			$szHtml .= '<td>[notes]</td>';
			$szHtml .= '</tr>';
			$szHtml .= '<tr>';
			$szHtml .= '<td></td>';
			$szHtml .= '<td class="center">'.$arrPlayer['username'].'</td>';
			$szHtml .= '<td></td>';
			$szHtml .= '</tr>';
			$szHtml .= '<tr>';
			$szHtml .= '<td></td>';
			$szHtml .= '<td>'.money($arrPlayer['balance']).'</td>';
			$szHtml .= '<td></td>';
			$szHtml .= '</tr>';
			$szHtml .= '</table>';
			$szHtml .= '</td>';
		}
		else {
			$szHtml .= '<td>'.sprintf($szEmptySeat, $iSeat).'</td>';
		}
		return $szHtml;
	}

?>
<html>

<head>
<title>MPP :: TABLE <?php echo GAME_ID; ?></title>
<script type="text/javascript" src="/js/general_1_2_6.js"></script>
<script type="text/javascript" src="/js/ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
function joinTableSeat( f_iSeat )
{
	iBalance = prompt('Balance:', '<?php echo (200*$arrGame['small_blind']); ?>');
	if ( !iBalance ) return false;

	szUrl = '<?php echo BASEPAGE; ?>?game=<?php echo GAME_ID; ?>&take_seat=' + f_iSeat + '&balance=' + iBalance;
	new Ajax(szUrl, {
		method		: 'get',
		onComplete	: function(ajax)
		{
			if ( 'OK' != ajax.responseText )
			{
				alert(ajax.responseText);
			}
			else
			{
				document.location.reload();
			}
		}
	});
	return false;
}

function keepAlive() {
	new Ajax('<?php echo BASEPAGE; ?>?keepalive&game=<?php echo GAME_ID; ?>', {
		method		: 'get',
		onComplete	: function(ajax) {
			var t = ajax.responseText;
			if ( 'OK' != t ) {
				alert(t);
				document.location.reload();
			}
			setTimeout('keepAlive();', 5000);
		}
	});
	return false;
}

<?php if ( $bPlaying ): ?>
window.onload = function() {
	setTimeout('keepAlive();', 5000);
}
<?php endif; ?>
//-->
</script>
<style type="text/css">
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
}
td#center {
	background-color	: #ddd;
	width				: 50%;
}
table table td {
	border				: none;
	width				: 33%;
}
table table td.center {
	background-color	: #fff;
	font-weight			: bold;
	border				: solid 1px #999;
}
</style>
</head>

<body style="overflow:auto;">
<table width="100%" height="100%">
	<tr>
		<?php $s=9; echo printSeat($s); ?>
		<?php $s=10;echo printSeat($s); ?>
		<?php $s=1; echo printSeat($s); ?>
		<?php $s=2; echo printSeat($s); ?>
	</tr>
	<tr>
		<?php $s=8; echo printSeat($s); ?>
		<td colspan="2" id="center">FLOP - TURN - RIVER</td>
		<?php $s=3; echo printSeat($s); ?>
	</tr>
	<tr>
		<?php $s=7; echo printSeat($s); ?>
		<?php $s=6; echo printSeat($s); ?>
		<?php $s=5; echo printSeat($s); ?>
		<?php $s=4; echo printSeat($s); ?>
	</tr>
</table>
</body>

</html>
<?php

	exit;
}


// not in a game (print games)
$arrGames = db_select(TABLE_GAMES, '1 ORDER BY id ASC');

?>
<html>

<head>
<title>MPP :: IN</title>
<script type="text/javascript" src="/js/general_1_2_6.js"></script>
<script type="text/javascript" src="/js/ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
function openTable( f_iGame )
{
	window.open('<?php echo BASEPAGE; ?>?game=' + f_iGame, '', 'top=15,left=15,width=950,height=600,statusbar=no');
	return false;
}
function keepAlive()
{
	new Ajax('<?php echo BASEPAGE; ?>?keepalive', {
		method		: 'get',
		onComplete	: function(ajax)
		{
			setTimeout('keepAlive();', 2000);
		}
	});
	return false;
}
window.onload = function() {
	setTimeout('keepAlive();', 2000);
}
//-->
</script>
<style type="text/css">
td {
	text-align			: center;
}
</style>
</head>

<body style="overflow:auto;">
<table border="1" cellpadding="0" cellspacing="0" width="600" align="center">
	<tr>
		<th>Table</th>
		<th>Blinds</th>
		<th># Players</th>
		<th>&nbsp;</th>
	</tr>
<?php

foreach ( $arrGames AS $arrGame )
{
	echo '<tr>';
	echo '<td>Table '.$arrGame['id'].'</td>';
	echo '<td>'.(float)$arrGame['small_blind'].' / '.(float)(2*$arrGame['small_blind']).'</td>';
	echo '<td>'.db_count(TABLE_PLAYERS_IN_GAMES, 'game_id = '.(int)$arrGame['id']).'</td>';
	echo '<td><a onclick="return openTable('.$arrGame['id'].');" href="?game='.$arrGame['id'].'">Open table</a></td>';
	echo '</tr>'."\r\n";
}

?>
</table>
</body>

</html>
<?php

exit;









function getSeatInfo( $f_iSeat ) {
	$iSeat = min(MAX_PLAYERS_EVER, max(1, $f_iSeat));
	return db_fetch("
	SELECT
		u.*,
		p.*
	FROM
		".TABLE_PLAYERS." u,
		".TABLE_PLAYERS_IN_GAMES." p
	WHERE
		(p.seat = ".$iSeat.") AND
		(p.table_id = ".GAME_ID.") AND
		(p.user_id = u.id);
	");
} // END getSeatInfo()






















function money($f_fAmount) {
	return number_format((float)$f_fAmount, 2, '.', ' ');
}

function randString( $f_iLength = 8 )
{
	$arrTokens = array_merge( range("a","z"), range("A","Z"), range("0","9") );

	$szRandString = "";
	for ( $i=0; $i<max(1, (int)$f_iLength); $i++ )
	{
		$szRandString .= $arrTokens[array_rand($arrTokens)];
	}

	return $szRandString;

} // END randString()

function loginCheck( $f_bAct = false )
{
	global $db;

	// session
	if ( empty($_SESSION[SESSION_NAME]) ) {
		return exitWithError($f_bAct, "Invalid session!");
	}
	$arrSession = $_SESSION[SESSION_NAME];

	// variables
	if ( !is_array($arrSession) || !isset($arrSession['uid'], $arrSession['ip'], $arrSession['hash']) ) {
		return exitWithError($f_bAct, "Invalid session!");
	}

	// ip check
	if ( empty($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] !== $arrSession['ip'] ) {
		return exitWithError($f_bAct, "Invalid session!");
	}

	// user check in db
	$arrUser = db_select(TABLE_PLAYERS, "id = '".$arrSession['uid']."' AND hash = '".$arrSession['hash']."'");
	if ( 1 != count($arrUser) ) return exitWithError($f_bAct, "Invalid session!");

	// update online time
	db_update(TABLE_PLAYERS, array('last_online' => time()), 'id = '.(int)$arrSession['uid']);

	if ( !defined('USER_ID') ) {
		define( 'USER_ID', (int)$arrSession['uid'] );
	}

	return true;

} // END loginCheck()

function exitWithError( $f_bAct, $f_szMsg )
{
	unset($_SESSION[SESSION_NAME]);
	if ( $f_bAct ) exit($f_szMsg);
	if ( !defined('USER_ID') ) define( 'USER_ID', 0 );
	return false;

} // END exitWithError()

function ifsetor( &$f_mvFirst, $f_mvSecond = null )
{
	return isset($f_mvFirst) ? $f_mvFirst : $f_mvSecond;

} // END ifsetor()

?>
