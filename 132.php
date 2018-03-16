<?php

/**
 * Todo: implement the CardGame classes (Card, Deck in inc.cls.cardgame.php) and the new pokertexasholdem class (PokerTexasHoldem in 131b.php)
 */

$_CHEAT_IPS = array(

);

if ( function_exists('date_default_timezone_set') ) {
	date_default_timezone_set("Europe/Amsterdam");
}

/**
 * Volgorde van dit spelletje:
 * 1. Iedereen 2 kaarten
 * 2. First betting round, begint bij Small Blind
 * ... Small blind (MOET)
 * ... Big blind (MOET)
 * ... Alle bets gelijk (of OUT)
 * 3. 3 kaart open in Flop
 * 4. Second betting round, begint bij Small Blind
 * ... ACCEPT of RAISE
 * ... Alle bets gelijk (of OUT)
 * 5. Nog 1 kaart open in Flop
 * 6. Third betting round, begint bij ???
 * ... Alle bets gelijk (of OUT)
 * 7. Laatste kaart bij Flop open
 * 8. Fourth and final betting round, begint bij Small Blind
 * ... Alle bets gelijk (of OUT)
 * 9. Als er meer dan 1 spelers over zijn: WINNAAR KIEZEN
 * ... Als er maar 1 iemand niet OUT is, is diegene winnaar.
 **/

error_reporting(4095);
session_start();

/** INCLUDES **/
require __DIR__ . '/inc.bootstrap.php';


/** SCRIPTING CONSTANTS **/
define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );



/** CONSTANTS **/
define( "IMAGES_DIR",		"images/" );
define( "OPEN_GAME",		false );
define( "NUM_PLAYERS",		4 );


include_once("inc.cls.pokertexasholdem.php");

/** TEMPS
 * start_balance
 **/
$_START_BALANCE = 1000;




/*$qPokerGames = mquery("select g.id from mpp_games g having 0 = (select count(1) from mpp_players p where p.gid = g.id);");
while ( $arrPokerGame = mysql_fetch_assoc($qPokerGames) )
{
	mquery("delete from mpp_games where id = '".$arrPokerGame['id']."';");
}*/




/** PRE-GAME | LOGGED OUT **/
if ( !isset($_SESSION['mpp']['uid'], $_SESSION['mpp']['gid']) )
{
	unset($_SESSION['mpp']);
	MQuery("DELETE FROM mpp_players WHERE online < ".(time()-31).";");
	if ( isset($_POST['join'], $_POST['gid'], $_POST['usr']) )
	{
		$post_user = trim($_POST['usr']);
		$usr_check1 = ereg('[^A-Za-z0-9]', $post_user);
		$usr_check2 = mysql_result(MQuery("SELECT COUNT(*) FROM mpp_players WHERE name='".$post_user."';"),0);
		$gid_check = mysql_result(MQuery("SELECT COUNT(*) FROM mpp_games WHERE id='".$_POST['gid']."';"),0);;
		$places_left = NUM_PLAYERS-mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".$_POST['gid']."';"),0,'a');
		if ($usr_check1 || $usr_check2 || strlen($post_user)<3 || strlen($post_user)>40)
		{
			print_header(10);
			die("This name is already taken, or it's invalid. Only a-z and 0-9 allowed! Min 3 chars, max 40!");
		}
		else if (!$gid_check)
		{
			print_header(10);
			die("This game doesn't exist!");
		}
		else if (!$places_left)
		{
			print_header(10);
			die("This game has no free seats to join!");
		}

		$next_uid = 0;
		for ($i=1;$i<=NUM_PLAYERS;$i++)
		{
			if ( !$next_uid && !mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".$_POST['gid']."' AND uid='$i';"),0,'a') )
			{
				// UID $i staat nog niet in tabel EN $next_uid is nog niet geset
				$next_uid = $i;
			}
		}

		// Set sessions: uid, gid
		$save['uid'] = $next_uid;
		$save['gid'] = $_POST['gid'];
		$_SESSION['mpp'] = $save;

		MQuery("INSERT INTO mpp_players (uid,gid,name,balance,in_or_out) VALUES ('".$next_uid."','".$save['gid']."','".$post_user."','".$_START_BALANCE."','out');");
		$iNumPlayers = mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".$save['gid']."';"),0,'a');

		if ( 1 == $iNumPlayers )
		{
			// Enige speler dus ff game resetten
			MQuery("UPDATE mpp_games SET last_win='' WHERE id='".$save['gid']."';");
		}
		else if ( 2 == $iNumPlayers )
		{
			// Dit is de tweede speler, dus laten we maar alvast beginnen!

			// `round` = round+1
			// `dealer` is kleinste uid
			// `player_turn` is deze (nieuwe) uid
			$uids = Array();
			$kuq = MQuery("SELECT uid FROM mpp_players WHERE gid='".$save['gid']."';");
			while ( $qi = mysql_fetch_assoc($kuq) )
			{
				$uids[] = $qi['uid'];
			}
			MQuery("DELETE FROM mpp_cards WHERE gid='".$save['gid']."';");
			MQuery("UPDATE mpp_games SET round=round+1, pot=0, dealer=".min($uids).", player_turn=".$next_uid." WHERE id='".$save['gid']."';");
			MQuery("UPDATE mpp_players SET /*balance = ".$_START_BALANCE.",*/ bet = 0, in_or_out = 'in', ready_for_next_round = 'no' WHERE gid = '".$save['gid']."';");

			Header("Location: ?deal=1");
			exit;
		}

		Header("Location: ".BASEPAGE);
		exit;
	}
	else if ( isset($_POST['new_game_name'], $_POST['new_game_small_blind'], $_POST['new_game_big_blind'], $_POST['new_game_comments'], $_POST['new_game_username']) )
	{
		$_POST['new_game_small_blind'] = max( 1, (int)$_POST['new_game_small_blind'] );
		$_POST['new_game_big_blind'] = max( $_POST['new_game_small_blind']+1, (int)$_POST['new_game_big_blind'] );
		mquery("insert into mpp_games (name, small_blind, big_blind, comment) values ('".$_POST['new_game_name']."', '".$_POST['new_game_small_blind']."', '".$_POST['new_game_big_blind']."', '".$_POST['new_game_comments']."');");
		$gid = mysql_insert_id();

		mquery("insert into mpp_players (uid, gid, name, in_or_out) values ('1','".$gid."','".$_POST['new_game_username']."','out');");

		// Set sessions: uid, gid
		$_SESSION['mpp'] = array( "uid" => "1", "gid" => $gid );;

		Header("Location: ".BASEPAGE);
		exit;
	}

	print_header(10);
	echo '<script type="text/javascript">if ( top.location != document.location ) top.location="'.BASEPAGE.'";</script>'.EOL;
	echo '<style type="text/css">'.EOL;
	echo "table#mpp_not_logged_in, table#new_game {".EOL;
	echo "	width: 500px;".EOL;
	echo "	margin: 0;".EOL;
	echo "}".EOL;
	echo "table#mpp_not_logged_in td, table#new_game td {".EOL;
	echo "	border: solid 1px #777;".EOL;
	echo "	padding: 4px;".EOL;
	echo "	margin: 0;".EOL;
	echo "}".EOL;
	echo "table#mpp_not_logged_in td.head, table#new_game td.head {".EOL;
	echo "	font-weight: bold;".EOL;
	echo "	color: #07f;".EOL;
	echo "}".EOL;
	echo "table#mpp_not_logged_in td.usr, table#new_game td.usr {".EOL;
	echo "	padding-left: 13px;".EOL;
	echo "}".EOL;
	echo "</style>".EOL;
	echo "You are nog logged in yet. Choose a game to participate in:<br><br>";
	echo "<form method=post><input type=hidden name=join value=1><table id=\"mpp_not_logged_in\">\n";
	echo "<tr>\n<td class='head'>Game</td>\n<td class='head' align='right'>SB/Balance</td>\n<td class='head' align='right'>BB/Bet</td>\n<td class='head' align='center'>Join/Status</td>\n</tr>\n";
	$a = MQuery("SELECT * FROM mpp_games;");
	while ( $game = mysql_fetch_assoc($a) )
	{
		$players = MQuery("SELECT uid, name, bet, balance, in_or_out FROM mpp_players WHERE gid='".$game['id']."';");
		$iPlayers = mysql_num_rows($players);
		echo "<tr>".EOL;
		echo "<td><b>".$game['name']."</b></td>".EOL;
		echo "<td align=\"right\">&euro; ".int2str($game['small_blind'])."</td>".EOL;
		echo "<td align=\"right\">&euro; ".int2str($game['big_blind'])."</td>".EOL;
		if ( NUM_PLAYERS == $iPlayers )	echo "<td><i>Full!</i></td>".EOL;
		else							echo '<td align="center"><input type="radio" name="gid" value="'.$game['id'].'"></td>'.EOL;
		echo '<td>'.(NUM_PLAYERS-$iPlayers).' seats free</td>'.EOL;
		echo "</tr>".EOL;

		while ( $player = mysql_fetch_assoc($players) )
		{
			echo '<tr>'.EOL;
			echo '<td class="usr">* '.$player['name'].'</td>'.EOL;
			echo '<td align="right">&euro; '.$player['balance'].'</td>'.EOL;
			echo '<td align="right">&euro; '.$player['bet'].'</td>'.EOL;
			echo '<td align="center">' . ( "out" == $player['in_or_out'] ? "FOLDED" : "PLAYING" ) . '</td>'.EOL;
			echo '<td align="center">' . ( $game['dealer'] == $player['uid'] ? '<font color="red">DEALER</font>' : "" ) . '</td>'.EOL;
			echo '</tr>'.EOL;
		}
		if ( !$iPlayers )
		{
			echo '<tr>'.EOL;
			echo '<td align="center" colspan="4"><i>No players in this game...</i></td>'.EOL;
			echo '<tr>'.EOL;
		}
	}
	if ( 0 == mysql_num_rows($a) )
	{
		echo '<tr><td align="center" colspan="4"><i>No games found...</i></td></tr>';
	}
	echo '<tr><td colspan="3">Your username: <input type="text" name="usr" /></td><td><input type="submit" value="Join"'.( 0 == mysql_num_rows($a) ? ' disabled' : "" ).' /></td></tr>'.EOL;
	echo "</table></form>";

	?>
<form method="post" action="">
	<table id="new_game">
		<tr>
			<td colspan="2" class="head">Create your own game!</td>
		</tr>
		<tr>
			<td>Game name</td>
			<td><input type="text" name="new_game_name" value="" /></td>
		</tr>
		<tr>
			<td>Small Blind</td>
			<td><input type="text" name="new_game_small_blind" value="" /></td>
		</tr>
		<tr>
			<td>Big Blind</td>
			<td><input type="text" name="new_game_big_blind" value="" /></td>
		</tr>
		<tr>
			<td>Comments?</td>
			<td><input type="text" name="new_game_comments" value="" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center">Userinfo</td>
		</tr>
		<tr>
			<td>Your name</td>
			<td><input type="text" name="new_game_username" value="" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Create" /></td>
		</tr>
	</table>
</form>
<?php

	exit;
}


/** LOG OUT **/
if ( ( isset($_GET['action']) && $_GET['action'] == 'logout' ) || 1 != (int)mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE uid='".$_SESSION['mpp']['uid']."' AND gid='".$_SESSION['mpp']['gid']."';"), 0) )
{
	if ( wie_is_aan_de_beurt() == $_SESSION['mpp']['uid'] ) {
		beurt_naar_volgende_speler($_SESSION['mpp']['uid']);
	}
	MQuery("DELETE FROM mpp_players WHERE uid = '".$_SESSION['mpp']['uid']."' AND gid = '".$_SESSION['mpp']['gid']."';");
	unset($_SESSION['mpp']);

	Header("Location: ".BASEPAGE);
	exit;
}




/** GAME VARIABLES **/
$GAMEINFO		= mysql_fetch_assoc(MQuery("SELECT * FROM mpp_games WHERE id='".$_SESSION['mpp']['gid']."';"));
$USER			= mysql_fetch_assoc(MQuery("SELECT * FROM mpp_players WHERE gid='".$_SESSION['mpp']['gid']."' AND uid='".$_SESSION['mpp']['uid']."';"));

/** GAME 'CONSTANTS' **/
define( "GAMEID",			$_SESSION['mpp']['gid'] );
define( "USERID",			$_SESSION['mpp']['uid'] );
define( "DEALERID",			$GAMEINFO['dealer'] );
define( "SMALLBLINDID",		beurt_naar_volgende_speler(DEALERID, FALSE) );
define( "BIGBLINDID",		beurt_naar_volgende_speler(SMALLBLINDID, FALSE) );
$iPlayers = mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."';"),0,'a');
define( "READY_GAME",		NUM_PLAYERS == $iPlayers ? TRUE : TRUE );
define( 'SB_POSTED',		(int)$GAMEINFO['small_blind'] <= (int)@mysql_result(@MQuery("SELECT bet FROM mpp_players WHERE gid = ".GAMEID." AND uid = ".SMALLBLINDID.";"), 0) );
define( 'BB_POSTED',		(int)$GAMEINFO['big_blind'] <= (int)@mysql_result(@MQuery("SELECT bet FROM mpp_players WHERE gid = ".GAMEID." AND uid = ".BIGBLINDID.";"), 0) );

// var_dump(BIGBLINDID);


// Wie is er aan de beurt
define( "UID_AAN_DE_BEURT",	wie_is_aan_de_beurt() );





// Zorgen dat UID Online blijft
MQuery("UPDATE mpp_players SET online=UNIX_TIMESTAMP(NOW()) WHERE uid = ".(int)$_SESSION['mpp']['uid']." AND gid = ".(int)$_SESSION['mpp']['gid'].";");
// En Offline players op OUT zetten
MQuery("UPDATE mpp_players SET in_or_out = 'out' WHERE gid = ".(int)$_SESSION['mpp']['gid']." AND online < ".(time()-31).";");













/** HTML - STATUS WINDOW **/
if ( isset($_GET['mode']) && $_GET['mode'] == 'status' )
{
	// Update keepalive
	MQuery("UPDATE mpp_players SET online = ".time()." WHERE uid = ".(int)$_SESSION['mpp']['uid']." AND gid = ".(int)$_SESSION['mpp']['gid'].";");
	$arrGame = mysql_fetch_assoc(MQuery('SELECT * FROM mpp_games WHERE id = '.(int)$_SESSION['mpp']['gid'].';'));

	require_once('inc.cls.json_php5.php');
	echo json::encode(array(
		'game'			=> array(
			'dealer'		=> (int)$arrGame['dealer'],
			'turn'			=> (int)wie_is_aan_de_beurt(),
			'pot'			=> (float)$arrGame['pot'],
			'last_win'		=> (int)$arrGame['last_win'],
		),
		'_status'		=> ( UID_AAN_DE_BEURT == $_SESSION['mpp']['uid'] ? 'IT\'S YOUR TURN' : '...' ),
		'seat_1'		=> printSeat(1),
		'seat_2'		=> printSeat(2),
		'seat_3'		=> printSeat(3),
		'seat_4'		=> printSeat(4),
		'seat_5'		=> printSeat(5),
		'seat_6'		=> printSeat(6),
		'seat_7'		=> printSeat(7),
		'center_flop'	=> Print_Flop(),
	));
	exit;

	// Let page know it's your turn
	if ( UID_AAN_DE_BEURT == $_SESSION['mpp']['uid'] ) {
		exit('IT\'S YOUR TURN');
	}
	exit('...');
}













/** PROCESS - SMALL BLIND **/
if ( isset($_POST['small_blind']) && SMALLBLINDID==USERID && READY_GAME )
{
	$bet = (int)$GAMEINFO['small_blind'];
	// Posting small blind
	MQuery("UPDATE mpp_players SET bet = bet+".$bet.", balance = balance-".$bet.", ready_for_next_round = 'no' WHERE gid='".GAMEID."' AND uid='".USERID."';");
	MQuery("UPDATE mpp_games SET pot=pot+".$bet." WHERE id='".GAMEID."' AND player_turn='".USERID."';");

	// Beurt naar volgende speler
	beurt_naar_volgende_speler( USERID );

	Header( "Location: " . BASEPAGE );
	exit;
}

/** PROCESS - BIG BLIND **/
else if ( isset($_POST['big_blind']) && BIGBLINDID==USERID && READY_GAME )
{
	deal_pre_flop();

	$bet = (int)$GAMEINFO['big_blind'];
//exit((string)$bet);
	// Posting big blind
	MQuery("UPDATE mpp_players SET bet=bet+".$bet.", balance = balance-".$bet.", ready_for_next_round = 'no' WHERE gid='".GAMEID."' AND uid='".USERID."';");
	MQuery("UPDATE mpp_games SET pot=pot+".$bet." WHERE id='".GAMEID."' AND player_turn='".USERID."';");

	// Beurt naar volgende speler
	beurt_naar_volgende_speler( USERID );

	Header( "Location: " . BASEPAGE );
	exit;
}

/** PROCESS - BET/RAISE **/
else if ( isset($_POST['normal_bet']) && UID_AAN_DE_BEURT==USERID && READY_GAME )
{
	$poker = new PokerTexasHoldem;

	// Posting a normal Bet OR a Fold
	if ( isset($_POST['i_fold']) )
	{
		// This guy Folds
		MQuery("UPDATE mpp_players SET in_or_out='out' WHERE gid='".GAMEID."' AND uid='".USERID."';");
	}
	else
	{
		$bet = (int)str2int($_POST['normal_bet']);
		// If the bet is less than $tocall, dont even post it
		$tocall = max(get_bets()) - $USER['bet'];
		// The bet cannot be higher than the bet of a player who has gone All-In (IF any!)
		$allinq = MQuery("SELECT bet FROM mpp_players WHERE balance='0' AND bet>0 AND in_or_out='in';");
		$iAllIn = mysql_num_rows($allinq);
		// The bet cannot be higher than `lowest max bet`-`uid's current bet`
		$maxbet = max($GAMEINFO['big_blind'], mysql_result(MQuery("SELECT (bet+balance) AS sa FROM mpp_players WHERE gid='".GAMEID."' AND (bet>0 OR balance>0) ORDER BY (bet+balance) ASC LIMIT 1;"), 0, 'sa')) - $USER['bet'];

		if ( $tocall > $bet || $USER['balance'] < $bet || ($iAllIn && mysql_result($allinq,0,'bet')<$bet) || $maxbet < $bet )
		{
			Header("Location: ".BASEPAGE);
			exit;
		}
		MQuery("UPDATE mpp_players SET bet=bet+".$bet.", balance=balance-".$bet.", ready_for_next_round='yes' WHERE gid='".GAMEID."' AND uid='".USERID."';");
		MQuery("UPDATE mpp_games SET pot=pot+".$bet." WHERE id='".GAMEID."' AND player_turn='".USERID."';");
	}

	// $players_still_in = mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".$GAME_ID."' AND in_or_out='in';"),0,'a');
	$players_still_in = count(get_bets(TRUE));
	$players_had_turn = mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."' AND ready_for_next_round='yes';"),0,'a');
	$different_bets_amongst_in = mysql_num_rows(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."' AND in_or_out='in' GROUP BY bet;"));

	if ($players_still_in == 1)
	{
		// Eh, iemand heeft gewonnen... Met bluf :)
		/** WE HAVE A WINNER **/
		// die("SOMEBODY ACTUALLY WON!!!! <a href='".BASEPAGE."'>Back</a>");
		// Ronde afgelopen, dus:
		// * `winner` bekendmaken
		$forwinner = get_ins( );
		$winner = array_search("in", $forwinner);
		// * winner krijgt de pot
		// * bets op 0, in_or_out op 'in', `ready_for_..` op 'no'
		// * new_dealer = dealer+1, beurt aan new_dealer+1
		// * alle kaarten uit cache verwijderen
		// * nieuwe kaarten schudden en delen (aan spelers)

		// * winner krijgt de pot
		$pot = mysql_result(MQuery("SELECT pot FROM mpp_games WHERE id='".GAMEID."';"), 0, 'pot');
		MQuery("UPDATE mpp_players SET balance=balance+".$pot." WHERE uid='".$winner."' AND gid='".GAMEID."';");
		// * bets op 0, in_or_out op 'in', `ready_for_..` op 'no'
		MQuery("UPDATE mpp_players SET bet=0, in_or_out='in', ready_for_next_round='no' WHERE gid='".GAMEID."';");
		MQuery("UPDATE mpp_players SET in_or_out='out' WHERE balance<".($GAMEINFO['big_blind']).";");
		// * new_dealer = dealer+1, beurt aan new_dealer+1
		$new_dealer = beurt_naar_volgende_speler(DEALERID, FALSE);
		$player_turn = beurt_naar_volgende_speler($new_dealer, FALSE);
		MQuery("UPDATE mpp_games SET dealer=".$new_dealer.", player_turn=".$player_turn.", round=round+1, pot=0 WHERE id='".GAMEID."';");

		// Save winner into games table
		MQuery("UPDATE mpp_games SET last_win='".$winner.":BLUF' WHERE id='".GAMEID."';");

		// * alle kaarten uit cache verwijderen
		MQuery("DELETE FROM mpp_cards WHERE gid='".GAMEID."';".EOL);
		// * nieuwe kaarten schudden en delen (aan spelers)
		Header("Location: ?deal=1");
		exit;
	}
	else
	{
		if ( 1 == $different_bets_amongst_in && $players_had_turn >= $players_still_in )
		{
			// Iedereen zelfde bet, dus iedereen callt -> volgende ronde
			$this_round = get_round();
			if ( -1 != $this_round )
			{
				$next_round = 1 + $this_round;
				// echo "next round = $next_round";
				Header("Location: ?deal=".$next_round);
				exit;
			}
			else
			{
				/** SHOW-DOWN **/
				/**
				 * TASK LIST
				 * 1a. Get cards of players still competing
				 * 1b. Get the 5 public cards
				 * 1c. Mix those 2+5 cards together
				 * 2.  Put a few times 7 cards in the machine
				 * 3.  Retrieve 2, 3 or 4 'scores'
				 * 4.  Make 'reverse-array' with [score] => num_with_this_score
				 * 5.1   If count('reverse-array') is not count(players still competing) >> SPLIT POT
				 * 5.2   Else >> Retrieve uid of highest score
				 * 6.  Pay out to at least one uid
				 * 7.  bets op 0, in_or_out op 'in', `ready_for_..` op 'no'
				 * 8.  new_dealer = dealer+1, beurt aan new_dealer+1
				 * 9.  alle kaarten uit cache verwijderen
				 * 10. nieuwe kaarten schudden en delen (aan spelers)
				 **/

				// 1a.  Get cards of players still competing
				$cards_of_competing_players = MQuery("SELECT p.uid, c.card FROM mpp_cards c LEFT JOIN mpp_players p ON (p.uid=c.uid) WHERE p.in_or_out='in' AND c.gid='".GAMEID."' AND p.gid='".GAMEID."';");
				while ( $card = mysqL_fetch_assoc($cards_of_competing_players) )
				{
					$usercards[$card['uid']][] = $card['card'];
				}

				// 1b. Get the 5 public cards
				$five_public_cards = MQuery("SELECT card FROM mpp_cards WHERE gid='".GAMEID."' AND uid='99';");
				while ( $card = mysql_fetch_assoc($five_public_cards) )
				{
					$publiccards[] = $card['card'];
				}

				// 1c. Mix those 2+5 cards together
				foreach ( $usercards AS $uid => $cards )
				{
					$usercards[$uid] = array_merge($cards, $publiccards);
				}

				// print_r( $usercards );

				// 2.  Put a few times 7 cards in the machine
				// 3.  Retrieve 2, 3 or 4 'scores'
				foreach ( $usercards AS $uid => $cards )
				{
					$scores[$uid] = $poker->return_hand($cards);
				}

				// print_r( $scores );

				$hiscore = max($scores);
				$users_with_hiscore = Array( );
				// 4.  Make 'reverse-array' with [score] => num_with_this_score
				foreach ( $scores AS $uid => $score )
				{
					if ( $score == $hiscore )
					{
						$users_with_hiscore[$uid] = $score;
					}
				}

				$pot = mysql_result(MQuery("SELECT pot FROM mpp_games WHERE id='".GAMEID."';"), 0, 'pot');

				// TASK 6 DONE HERE TOO
				// 5.1   If count('reverse-array') is not count(players still competing) >> SPLIT POT
				if ( 1 < count($users_with_hiscore) )
				{
					/** WE HAVE A WINNER **/
					// MORE WINNERS >> SPLIT POT
					$win_money_per_player = round($pot/count($users_with_hiscore), 2);
					foreach ( $users_with_hiscore AS $uid => $score )
					{
						MQuery("UPDATE mpp_players SET balance=balance+".$win_money_per_player." WHERE gid='".GAMEID."' AND uid='".$uid."';");
					}
					$winneruids = implode(",", array_keys($users_with_hiscore));
					MQuery("UPDATE mpp_games SET last_win='".$winneruids.":".$hiscore."' WHERE id='".GAMEID."';");
					// echo "SPLIT POT BETWEEN PLAYERS: " . get_playernames($GAME_ID, $winneruids);
				}
				// 5.2   Else >> Retrieve uid of highest score
				else
				{
					/** WE HAVE A WINNER **/
					// ONE WINNER
					reset($users_with_hiscore);
					$winner_uid = key($users_with_hiscore);
					MQuery("UPDATE mpp_players SET balance=balance+".$pot." WHERE gid='".GAMEID."' AND uid='".$winner_uid."';");
					MQuery("UPDATE mpp_games SET last_win='".$winner_uid.":".$hiscore."' WHERE id='".GAMEID."';");
					// echo "THE WINNER: " . get_playername($GAME_ID, $winner_uid);
				}
				$winner_hand = current($users_with_hiscore);

				// 7.  bets op 0, in_or_out op 'in', `ready_for_..` op 'no'
				MQuery("UPDATE mpp_players SET bet=0, in_or_out='in', ready_for_next_round='no' WHERE gid='".GAMEID."';");
				MQuery("UPDATE mpp_players SET in_or_out='out' WHERE balance<".($GAMEINFO['big_blind']).";");
				// * new_dealer = dealer+1, beurt aan new_dealer+1
				$new_dealer = beurt_naar_volgende_speler(DEALERID, FALSE);
				$player_turn = beurt_naar_volgende_speler($new_dealer, FALSE);
				MQuery("UPDATE mpp_games SET dealer=".$new_dealer.", player_turn=".$player_turn.", round=round+1, pot=0 WHERE id='".GAMEID."';");

				// * alle kaarten uit cache verwijderen
				MQuery("DELETE FROM mpp_cards WHERE gid='".GAMEID."';".EOL);
				// * nieuwe kaarten schudden en delen (aan spelers)
				Header("Location: ?deal=1");
				exit;
			}
		}
		$bnvs = beurt_naar_volgende_speler( USERID );
		// var_dump($bnvs);
	}

	Header( "Location: ?deal=1" );
	exit;
}















/** SYSTEM - DEAL USERCARDS **/
if ( isset($_GET['deal']) && $_GET['deal'] == 1 && READY_GAME )
{
	deal_pre_flop( );

	Header( "Location: " . BASEPAGE );
	exit;
}

/** SYSTEM - DEAL FLOP **/
else if ( isset($_GET['deal']) && $_GET['deal'] == 2 && READY_GAME )
{
	// echo "dealing flop (3 cards)";
	if ( 3 > mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_cards WHERE gid='".GAMEID."' AND uid=99;"),0,'a') && READY_GAME )
	{
		// Deal cards
		$a = MQuery("SELECT * FROM mpp_cards WHERE gid='".GAMEID."' AND uid=0 LIMIT 3;");
		while ($b = mysql_fetch_assoc($a))
		{
			MQuery("UPDATE mpp_cards SET uid=99 WHERE cid='".$b['cid']."';");
		}
		// Reset everybody's `ready-for-next-round` status
		MQuery("UPDATE mpp_players SET ready_for_next_round='no' WHERE gid='".GAMEID."' AND in_or_out='in';");
		// Give turn to first player after dealer
		$bnvs = beurt_naar_volgende_speler( DEALERID );
		// var_dump($bnvs);
		// MQuery("UPDATE mpp_games SET player_turn='".uid_plus_one($DEALER_ID)."';");

		MQuery("UPDATE mpp_players SET ready_for_next_round='no';");
	}

	Header( "Location: " . BASEPAGE );
	exit;
}

/** SYSTEM - DEAL TURN/RIVER **/
else if ( isset($_GET['deal']) && 3 <= $_GET['deal'] && 4 >= $_GET['deal'] && READY_GAME )
{
	// echo "dealing turn/river (1 card: card ".(1+$_GET['deal']).")";
	if ( mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_cards WHERE gid='".GAMEID."' AND uid='99';"),0,'a')==$_GET['deal'] )
	{
		$a = MQuery("SELECT * FROM mpp_cards WHERE gid='".GAMEID."' AND uid=0 LIMIT 1;");
		while ($b = mysql_fetch_assoc($a))
		{
			MQuery("UPDATE mpp_cards SET uid=99 WHERE cid='".$b['cid']."';");
		}
		// Reset everybody's `ready-for-next-round` status
		MQuery("UPDATE mpp_players SET ready_for_next_round='no';");
		// Give turn to first player after dealer
		$bnvs = beurt_naar_volgende_speler( DEALERID );
		// var_dump($bnvs);
		// MQuery("UPDATE mpp_games SET player_turn='".uid_plus_one($DEALER_ID)."';");
	}

	Header( "Location: " . BASEPAGE );
	exit;
}
else if ( isset($_GET['deal']) )
{
	Header( "Location: " . BASEPAGE );
	exit;
}
// END SYSTEM //


print_header();

?>
<style type="text/css">
html, body {
	overflow : auto;
}
td, th {
	width : 33%;
	height : 33%;
	text-align : center;
}
#seat_<?php echo USERID; ?> {
	border : solid 12px #222;
	background-color : #444;
}
#center_flop {
	border : solid 12px black;
}
#_status {
	font-size : 18px;
}
</style>

<?php /* var_dump(SB_POSTED);
var_dump(BB_POSTED); */ ?>

<script type="text/javascript" src="/js/general_1_2_6.js"></script>
<script type="text/javascript" src="/js/ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
var g_iTurn = 0;
function reloadState(a) {
	new Ajax('?mode=status', {
		method : 'GET',
		onComplete : function(a) {
			var t = a.responseText;
			try {
				eval('var x = ('+t+');');
				var g = x.game;
				foreach ( x, function(k, v) {
					if ( $(k) ) {
						if ( 'seat_'+g_iTurn != k ) {
							$(k).innerHTML = v;
						}
					}
				});
				g_iTurn = g.turn;
			}
			catch(e) { alert('Error in received JSON: '+e.message+"\n"+t); return false; }
		}
	});
	setTimeout('reloadState(true);', 2000);
}
setTimeout('reloadState(true);', 0);
//-->
</script>

<table border="0" cellpadding="10" cellspacing="0" height="100%" width="100%">
<tr valign="middle">
	<td id="seat_7"><?php echo printSeat(7); ?></td>
	<th id="_status"></th>
	<td id="seat_1"><?php echo printSeat(1); ?></td>
</tr>
<tr valign="middle">
	<td id="seat_6"><?php echo printSeat(6); ?></td>
	<td id="center_flop"><?php //echo Print_Flop(); ?></td>
	<td id="seat_2"><?php echo printSeat(2); ?></td>
</tr>
<tr valign="middle">
	<td id="seat_5"><?php echo printSeat(5); ?></td>
	<td id="seat_4"><?php echo printSeat(4); ?></td>
	<td id="seat_3"><?php echo printSeat(3); ?></td>
</tr>
</table>

</body>

</html>
<?php













function mquery( $sql )
{
	global $GAMEINFO;

	$sql = str_replace("	", " ", ltrim($sql));
	$x = explode(" ", $sql);
	$action = strtoupper($x[0]);
	$logfile = date("Ymd") . "-sql-log.log";
	if ( defined("GAMEID") && !empty($GAMEINFO) )
	{
		$logfile = "GAME-" . GAMEID . "-ROUND-" . $GAMEINFO['round'] . ".log";
	}
	if ( $action != "SELECT" && "UPDATE mpp_players SET online=" != substr($sql,0,30) && !(stristr($sql,"online") && $action=="DELETE") && $fo = @fopen("logs/".$logfile, "a") )
	{
		@fwrite($fo, date("H:i:s") . " - " . $sql.EOL);
	}

	$q = mysql_query($sql) or die(mysql_error());

	return $q;
}

function beurt_naar_volgende_speler( $current_uid, $doSqlAction = true )
{
	if ( 1 < mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."' AND in_or_out='in';"), 0, 'a') )
	{
		$next_uid = uid_plus_one($current_uid);
		if ( mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."' AND uid='".$next_uid."' AND in_or_out='in';"), 0, 'a') )
		{
			if ( $doSqlAction )
			{
				MQuery("UPDATE mpp_games SET player_turn='".$next_uid."' WHERE id='".GAMEID."';".EOL);
			}
			return $next_uid;
		}
		else
		{
			return beurt_naar_volgende_speler( $next_uid, $doSqlAction );
		}
	}
	else
	{
		return -1;
	}
}

function uid_plus_one( $uid )
{
	$plus_one = $uid+1;
	if ($plus_one>NUM_PLAYERS)
		return $plus_one-NUM_PLAYERS;
	else
		return $plus_one;
}

function Go_Random( $max )
{
	global $used_randoms;

	$id = rand(1,$max);
	if (isset($used_randoms) && is_array($used_randoms) && in_array($id,$used_randoms))
		Go_Random($max);
	else
		$used_randoms[] = $id;
	return $id;
}

function Print_Cards( $uid )
{
	global $STATIC;
	$szHtml = '';

	$uq = MQuery("SELECT in_or_out FROM mpp_players WHERE uid='".$uid."' AND gid='".GAMEID."';");
	if ( !mysql_num_rows($uq) )
	{
		return '';
	}
	$iou = mysql_result($uq, 0, 'in_or_out');
	if ( "out" == $iou )
	{
		return '';
	}

	$poker = new PokerTexasHoldem;

	// Print private cards
	$a = MQuery("SELECT card FROM mpp_cards WHERE uid='".$uid."' AND gid='".GAMEID."';");
	$arrOpenCards = Array();
	while ($b = mysql_fetch_assoc($a))
	{
		// print_r( $b );
		$arrOpenCards[] = $b['card'];
	}
	// print_r( $arrOpenCards );
	$szHtml .= $poker->print_cards_with_imgs($arrOpenCards) . "<br/>\n<br/>\n";

	// Print current hand
	$scq = MQuery("SELECT card FROM mpp_cards WHERE gid='".GAMEID."' AND (uid='99' OR uid='".$uid."');");
	if ( 5 <= mysql_num_rows($scq) )
	{
		$sc = Array( );
		while ( $card = mysql_fetch_assoc($scq) )
		{
			$sc[] = $card['card'];
		}
		$szHtml .= "&#155;&#155; " . $poker->make_readable_hand($poker->return_hand($sc)) . " &#139;&#139;";
	}

	return $szHtml;
}

function printSeat( $uid ) {
	global $_CHEAT_IPS;
	$szHtml = '';
	$szHtml .= Print_Player_Info($uid);
	if ( BB_POSTED && ( (string)USERID == (string)$uid || OPEN_GAME || in_array($_SERVER['REMOTE_ADDR'], $_CHEAT_IPS) ) ) {
		$szHtml .= Print_Cards($uid);
	}
	return $szHtml;
}

function Print_Player_Info( $uid )
{
	$gameinfo = mysql_fetch_assoc(MQuery("SELECT * FROM mpp_games WHERE id='".GAMEID."';"));
	$szHtml = '';

	$a = MQuery("SELECT * FROM mpp_players WHERE gid='".GAMEID."' AND uid='".$uid."';");
	if ( 0 < mysql_num_rows($a) )
	{
		$b = mysql_fetch_assoc($a);
		if ( $uid == $gameinfo['dealer'] )
		{
			$szHtml .= "<b style='color:blue;'>&lt;&gt;DEALER&lt;&gt;</b><br>\n";
		}
		else if (SMALLBLINDID == $uid)
		{
			$szHtml .= "<b style='color:blue;'>SMALL BLIND</b><br>\n";
		}
		else if (BIGBLINDID == $uid)
		{
			$szHtml .= "<b style='color:blue;'>BIG BLIND</b><br>\n";
		}

		$szHtml .= '<b>'.$b['uid'].'. '.$b['name']."</b><br>\n";

		if ($b['bet'])
		{
			$szHtml .= "Current bet: &euro; ".int2str($b['bet'])."<br>\n";
		}
		if ($b['in_or_out'] == "out")
		{
			$szHtml .= "--!IS OUT!--<br/>\n";
		}

		if ( $_SESSION['mpp']['uid'] == $uid )
		{
			$szHtml .= '<form method="post">';
			$szHtml .= Print_This_User_Fields();
			$szHtml .= '</form>';
		}
		else
		{
			$small_blind_posted = (BOOL)mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."' AND uid='".SMALLBLINDID."' AND bet>=".$gameinfo['small_blind'].";"),0,'a');
			if ($uid == SMALLBLINDID && $b['bet']<$gameinfo['small_blind'] && READY_GAME)
			{
				$szHtml .= "Posting small blind....<br/>\n";
			}
			else if ($uid == BIGBLINDID && $b['bet']<$gameinfo['big_blind'] && $small_blind_posted && READY_GAME)
			{
				$szHtml .= "Posting big blind....<br/>\n";
			}
			else if (UID_AAN_DE_BEURT == $uid)
			{
				// Misschien is deze USER aan de beurt (NIET big of small blind)
				$szHtml .= "It's this guys turn...<br/>\n";
			}
		}
		$szHtml .= "<br>Balance: &euro; ".int2str(mysql_result(MQuery("SELECT balance FROM mpp_players WHERE gid='".GAMEID."' AND uid='".$uid."';"),0,'balance'));
		$szHtml .= "<br><br>";
	}
	else
	{
		$szHtml .= '<i>Free Seat &lt;'.$uid.'&gt;</i>';
	}
	return $szHtml;
}

function Print_This_User_Fields( )
{
	$gameinfo = mysql_fetch_assoc(MQuery("SELECT * FROM mpp_games WHERE id='".GAMEID."';"));
	$userinfo = mysql_fetch_assoc(MQuery("SELECT * FROM mpp_players WHERE gid='".GAMEID."' AND uid='".USERID."';"));
	$szHtml = '';

	$small_blind_posted = (BOOL)mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_players WHERE gid='".GAMEID."' AND uid='".SMALLBLINDID."' AND bet>=".$gameinfo['small_blind'].";"),0,'a');

	if (SMALLBLINDID==USERID && $userinfo['bet']<$gameinfo['small_blind'] && READY_GAME && UID_AAN_DE_BEURT==USERID)
	{
		// This user is SMALL BLIND and has NOT bet enough yet, so print <input>
		$value = (($gameinfo['small_blind']-$userinfo['bet']) >= 0) ? ($gameinfo['small_blind']-$userinfo['bet']) : 0;
		$szHtml .= "<input type=hidden name=small_blind value='".$value."'> <input type=submit value='POST SMALL BLIND'>";
	}
	else if (BIGBLINDID==USERID && $userinfo['bet']<$gameinfo['big_blind'] && $small_blind_posted && READY_GAME && UID_AAN_DE_BEURT==USERID)
	{
		// This user is BIG BLIND and has NOT bet enough yet, so print <input>
		$value = (($gameinfo['big_blind']-$userinfo['bet']) >= 0) ? ($gameinfo['big_blind']-$userinfo['bet']) : 0;
		$szHtml .= "<input type=hidden name=big_blind value='".$value."'> <input type=submit value='POST BIG BLIND'>";
	}
	else if ( UID_AAN_DE_BEURT==USERID && $userinfo['in_or_out'] == "in" )
	{
		// Deze is aan de beurt (NIET big of small blind)
		// Hij kan dus inzetten wat-ie wil. Hij BLIJFT aan de beurt tot zijn inzet minstens even hoog is als hoogste IN
		$highest_bet_so_far = mysql_result(MQuery("SELECT bet FROM mpp_players WHERE gid='".GAMEID."' AND in_or_out='in' ORDER BY bet DESC LIMIT 1"),0,'bet');

		$button_text = ( $highest_bet_so_far > $userinfo['bet'] ) ? "CALL/RAISE" : ( (USERID==BIGBLINDID && get_round()==1) ? "RAISE" : "BET" );

		$szHtml .= "<input type=text name='normal_bet' value='".($highest_bet_so_far-$userinfo['bet'])."'> <input type='submit' name='bet' value='".$button_text."'><br>\n";
		if ( $highest_bet_so_far > $userinfo['bet'] )
		{
			$szHtml .= "<input type=submit name='i_fold' value='FOLD'><br>\n";
		}
		else
		{
			$szHtml .= "<input type=submit name='check' value='CHECK'><br>\n";
		}
	}
	return $szHtml;
}

function Print_Flop()
{
	global $STATIC;
	$szHtml = '';

	$gameinfo = mysql_fetch_assoc(MQuery("SELECT name,small_blind,big_blind,last_win,round FROM mpp_games WHERE id='".GAMEID."';"));
	$szHtml .= "Game name: ".$gameinfo['name']."<br>\n";
	$szHtml .= "Round: ".$gameinfo['round']."<br>\n";
	$szHtml .= "SB: &euro; ".int2str($gameinfo['small_blind']).", BB: &euro; ".int2str($gameinfo['big_blind'])."<br>\n<br>\n\n";

	$poker = new PokerTexasHoldem;

	if ( $gameinfo['last_win'] )
	{
		$x = explode(":", $gameinfo['last_win'], 2);
		$hand = $poker->make_readable_hand($x[1]);
		$uids = explode(",", $x[0]);
		$users = Array();
		foreach ( $uids AS $uid )
		{
			$q = MQuery("SELECT name FROM mpp_players WHERE uid='".$uid."' AND gid='".GAMEID."';");
			if ( mysql_num_rows($q) ) $users[] = mysql_result($q, 0, 'name');
		}

		$szHtml .= "<font color=\"lime\">Last win: [<b>".implode(",", $users)."</b>], with [<b>".$hand."</b>]</font><br/>\n";
	}

	$szHtml .= "PUBLIC CARDS:<br>";
	$a = MQuery("SELECT * FROM mpp_cards WHERE gid='".GAMEID."' AND uid=99;");
	$arrOpenCards = Array( );
	while ($b = mysql_fetch_assoc($a))
	{
		$arrOpenCards[] = $b['card'];
	}
	if ( mysql_num_rows($a) )
	{
		$szHtml .= $poker->print_cards_with_imgs($arrOpenCards) . "<br/>\n<br/>\n";
	}
	else
	{
		$szHtml .= "No Flop Yet<br/>\n<br/>\n";
	}

	if ( READY_GAME )
	{
		$utq = MQuery("SELECT name FROM mpp_players WHERE gid='".GAMEID."' AND uid='".UID_AAN_DE_BEURT."';");
		if ( !mysql_num_rows($utq) )	$userturn = '<span style="color:#f00;">! ! E R R O R ! !</span>';
		else							$userturn = '<u>'.mysql_result($utq,0,'name').'</u>';
		$szHtml .= "It's <b title=\"UID = ".UID_AAN_DE_BEURT."\">".$userturn."</b>'s turn!<br/>\n<br/>\n";

		$bets = get_bets( );
		$szHtml .= "MONEY TO CALL: &euro; ".int2str(max($bets))."<br>\n";
		$szHtml .= "MONEY IN POT: &euro; ".int2str(array_sum($bets))."<br/>\n";
	}
	$szHtml .= '<br/>'.EOL.'<a href="?action=logout">logout</a>';

	return $szHtml;

} // END Print_Flop( )

function print_header( $margin = 0 )
{
	if ( isset($_SESSION['mpp']['uid'], $_SESSION['mpp']['gid']) )
	{
		$usr = mysql_result(MQuery("SELECT name FROM mpp_players WHERE uid='".$_SESSION['mpp']['uid']."';"),0,'name');
	}

	echo "<html>\n\n<head>";
	echo "<title>" . ( isset($usr) ? "`" . $usr . "` - " : '' ) . "MPP</title>\n";
	echo "<style>\n";
	echo "BODY,TABLE { font-family:Verdana;font-size:11px;color:white;background:#333333; }\n";
	echo "</style>\n";
	echo "</head>\n\n";
	echo "<body style='margin:".$margin."px;'>\n";
}

function get_bets( $must_be_in = FALSE )
{
	$iou = '';
	if ( $must_be_in )
	{
		$iou = " AND in_or_out='in' ";
	}

	$b = MQuery("SELECT uid, bet FROM mpp_players WHERE gid='".GAMEID."' $iou;");
	$bets = Array( );
	while ( list($uid, $tmp_bet) = mysql_fetch_row($b) )
	{
		$bets[$uid] = $tmp_bet;
	}

	return $bets;
}

function get_ins( )
{
	$i = MQuery("SELECT uid, in_or_out FROM mpp_players WHERE gid='".GAMEID."';");
	$ins = Array( );
	while ( list($uid, $tmp_ins) = mysql_fetch_row($i) )
	{
		$ins[$uid] = $tmp_ins;
	}

	return $ins;
}

function wie_is_aan_de_beurt() {
	$qTurn = MQuery("SELECT player_turn FROM mpp_games g WHERE id = '".GAMEID."' AND EXISTS (SELECT * FROM mpp_players WHERE gid = g.id AND uid = g.player_turn);");
	if ( 1 == mysql_num_rows($qTurn) ) {
		return (int)mysql_result($qTurn, 0);
	}



	return FALSE;

} // END wie_is_er_aan_de_beurt( )


function get_round( )
{
	$iOpenCards = mysql_result(MQuery("SELECT COUNT(*) AS a FROM mpp_cards WHERE gid='".GAMEID."' AND uid='99';"),0,'a');
	if ( 5 == $iOpenCards )
	{
		// post-river
		return -1;
	}
	else if ( 4 == $iOpenCards )
	{
		// post-turn
		return 3;
	}
	else if ( 3 == $iOpenCards )
	{
		// post-flop
		return 2;
	}
	else if ( 3 > $iOpenCards )
	{
		// pre-flop
		return 1;
	}
	else
	{
		// error...
		echo "<b style='color:red;'>!! ERROR (get_round:".$iOpenCards.") !!</b>";
	}
}

function str2int( $num )
{
	$a = (FLOAT)str_replace(',', '', str_replace('.', '', str_replace(' ', '', $num)));
	return abs($a);
}

function int2str( $num )
{
	return number_format($num, 0, '.', ',');
}

function deal_pre_flop( )
{
	global $STATIC;

	// var_dump($gid);
	$ncq = MQuery("SELECT COUNT(*) AS a FROM mpp_cards WHERE gid='".GAMEID."';");
	$numcards = mysql_result($ncq,0,'a');
	// echo $numcards;
	if ( 0 == $numcards )
	{
		global $used_randoms;

		// Fetch number of users
		$qAllPlayers = MQuery("SELECT * FROM mpp_players WHERE gid='".GAMEID."';");
		$iNumPlayers = mysql_num_rows($qAllPlayers);

		// Create (INT)$c (2 per user, 5 public) random (unique) cards
		// $c = 2*$iNumPlayers + 5;
		$poker = new PokerTexasHoldem;
		$arrRealCards = $poker->shuffle_and_deal( $iNumPlayers );

		// Insert every card into the cards table
		foreach ( $arrRealCards AS $card )
		{
			MQuery("INSERT INTO mpp_cards (card,gid) VALUES ('".$card."','".GAMEID."');");
		}

		$i=0;
		while ($qi = mysql_fetch_assoc($qAllPlayers))
		{
			$players[$i] = $players[$i+$iNumPlayers] = $qi['uid'];
			$i++;
		}

		$i=0;
		$q = MQuery("SELECT * FROM mpp_cards WHERE gid='".GAMEID."' ORDER BY cid ASC LIMIT ".(2*$iNumPlayers).";");
		while ($qi = mysql_fetch_assoc($q))
		{
			MQuery("UPDATE mpp_cards SET uid='".$players[$i]."' WHERE cid='".$qi['cid']."';");
			$i++;
		}
		// delete inactive players
		MQuery("DELETE FROM mpp_players WHERE in_or_out = 'out' AND online < ".(time()-31)." AND gid = ".GAMEID.";");
		// reset active players
		MQuery("UPDATE mpp_players SET ready_for_next_round = 'no' WHERE gid = ".GAMEID.";");
	}
}

?>
