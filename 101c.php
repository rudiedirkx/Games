<?php
// BLACKJACK

// Todo //
// 1. Insurance					v done 2007-02-05 09:15
// 2. Double down				v done 2007-02-07 05:00
// 3. Reshuffle and cut card
// 4. Split
// 5. Multiple seats?
// Todo //

$g_fStartUtc = microtime(true);
require 'inc.cls.cardgame.php';

// config //
define( 'S_NAME',			'101_blackjack_v2_2008_02_05' );
define( 'USE_DECKS',		6 );
define( 'CUT_CARD',			15 );
define( 'HIGHEST_HITTER',	16 );
define( 'DEFAULT_SCORE',	1000 );
// config //

// session start after config, because session creation uses classes which use config constants
session_start();


$bDebug = !empty($_SESSION[S_NAME]['debug']);

card::$__tostring = function($c) {
	return '<img src="/images/' . $c->suit . '_' . $c->short . '.gif" />';
};


if ( !empty($_GET['reset']) || empty($_SESSION[S_NAME]['game']) ) {
	$_SESSION[S_NAME]['game'] = null;
	$_SESSION[S_NAME]['game'] = new Blackjack(USE_DECKS);
	$_SESSION[S_NAME]['game']->dealer = new Dealer('Dealer');
	$_SESSION[S_NAME]['game']->player = new Player('You');
	$_SESSION[S_NAME]['game']->player->balance = DEFAULT_SCORE;
	if ( !empty($_GET['reset']) ) {
		header('Location: '.basename($_SERVER['PHP_SELF']));
		exit;
	}
}
else if ( isset($_GET['debug']) ) {
	$_SESSION[S_NAME]['debug'] = !empty($_GET['debug']);
	header('Location: '.basename($_SERVER['PHP_SELF']));
	exit;
}



$objGame = $_SESSION[S_NAME]['game'];

/** direct-blackjack-test **
// cards: 0, 22, 11, 12, 50
$objGame->deck = new Deck(false);
$objGame->deck->add_card( new Card(0) );
$objGame->deck->add_card( new Card(22) );
$objGame->deck->add_card( new Card(11) );
$objGame->deck->add_card( new Card(12) );
$objGame->deck->add_card( new Card(50) );
/** direct-blackjack-test **/


if ( isset($_GET['bet']) ) {
	$objGame->bet( (int)max(0, $_GET['bet']) );
	header('Location: '.basename($_SERVER['PHP_SELF']));
	exit;
}
else if ( !empty($_GET['insurance']) ) {
	$objGame->insurance();
	header('Location: '.basename($_SERVER['PHP_SELF']));
	exit;
}
else if ( !empty($_GET['doubledown']) ) {
	$objGame->doubledown();
	header('Location: '.basename($_SERVER['PHP_SELF']));
	exit;
}
else if ( !empty($_GET['hit']) ) {
	if ( 4 <= count($objGame->player->cards())+count($objGame->dealer->cards()) && !$objGame->gameover && 21 > $objGame->player->score() ) {
		$objGame->hit($objGame->player);
	}
	header('Location: '.basename($_SERVER['PHP_SELF']));
	exit;
}
else if ( !empty($_GET['stand']) ) {
	if ( 4 <= count($objGame->player->cards())+count($objGame->dealer->cards()) && !$objGame->gameover ) {
		$objGame->dealerPlays();
	}
	header('Location: '.basename($_SERVER['PHP_SELF']));
	exit;
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Blackjack v2 | <?php echo $objGame->decks; ?> decks</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
</head>

<body>
<p><input type="button" value="Turn debug <?php echo $bDebug ? 'OFF' : 'ON'; ?>" onclick="document.location='?debug=<?php echo (int)!$bDebug; ?>';" /></p>

<p>Winner: <?php echo $objGame->winner ? $objGame->winner->name.' ('.$objGame->winner->score().')' : ( $objGame->gameover ? '[push]' : '-' ); ?></p>

<p><b>DEALER:</b><br /><?php
foreach ( $objGame->dealer->cards() AS $k => $objCard ) {
	echo ( (1 != $k || $objGame->player->stands) && $objCard ? $objCard->__tostring() : '<img src="images/blank.gif" width="71" height="96" />' ).' ';
}
echo $objGame->player->stands ? '<b>('.$objGame->dealer->score().')</b>' : '';
?></p>

<p><b>YOU (balance = <?php echo $objGame->player->balance.( 0 <= $objGame->player->bet ? ', bet = '.$objGame->player->bet : '' ).( 0 < $objGame->player->insurance ? ', insurance = '.$objGame->player->insurance : '' ).( $objGame->gameover ? ', payout = '.$objGame->player->payout : '' ); ?>):</b><br /><?php
foreach ( $objGame->player->cards() AS $objCard ) {
	echo $objCard ? $objCard->__tostring().' ' : '';
}
echo 0 < $objGame->player->score() ? '<b>('.$objGame->player->score().')</b>' : '';

?></p>

<form method="get">
	<input type="submit" name="reset" value="reset" />
<?php if ( !$objGame->gameover && 2 == count($objGame->player->cards()) && 2 == count($c=$objGame->dealer->cards()) && 'a' === $c[0]->short && $objGame->player->balance >= floor($objGame->player->bet/2) && 0 < floor($objGame->player->bet/2) ) { ?>
	<input<?php echo 0 < $objGame->player->insurance ? ' style="color:green;" disabled="1"' : ''; ?> type="submit" name="insurance" value="insurance (<?php echo min($objGame->player->balance, floor($objGame->player->bet/2)); ?>)" />
<?php } ?>
	<input type="submit" name="bet" value="bet" onclick="var b=prompt('Your bet:','<?php echo min($objGame->player->balance, $objGame->player->bet); ?>');if(!b||0>b){return false;}this.value=b;" />
<?php if ( !$objGame->gameover && 2 == count($objGame->player->cards()) ) { ?>
	<input<?php echo $objGame->player->balance < $objGame->player->bet ? ' disabled="1"' : ''; ?> type="submit" name="doubledown" value="double" />
<?php } ?>
	<input type="submit" name="hit" value="hit"<?php echo 4 > count($objGame->player->cards())+count($objGame->dealer->cards()) || $objGame->gameover ? ' disabled="1"' : ''; ?> />
	<input type="submit" name="stand" value="stand"<?php echo 4 > count($objGame->player->cards())+count($objGame->dealer->cards()) || $objGame->gameover ? ' disabled="1"' : ''; ?> />
</form>

<p><?php echo number_format(microtime(true)-$g_fStartUtc, 4); ?></p>

<p>Cards left: <?php echo $objGame->deck->size(); ?>; Cut card at <?php echo blackjack::cut_card; ?> cards left</p>

<?php

if ( $bDebug ) {
	echo "\n<pre>";
	print_r($objGame);
	echo '</pre>';
}

?>
</body>

</html>
<?php





class Dealer {
	public $name = '';
	protected $score = 0; # protected
	public $stands = false;
	protected $cards = array(); # protected
	public function __construct($f_szName) {
		$this->name = $f_szName;
	}
	public function receive(Card $f_objCard) {
		if ( null === $f_objCard ) {
			return false;
		}
		array_push($this->cards, $f_objCard);
		if ( 21 < $this->score(true) ) {
			$this->stands = true;
		}
		return $f_objCard;
	}
	public function cards() {
		return $this->cards;
	}
	public function score($f_bRecalculate = false) {
		if ( $f_bRecalculate ) {
			$iScore = $iAces = 0;
			foreach ( $this->cards AS $objCard ) {
				$iScore += $objCard->value;
				$iAces += (int)($objCard->short === 'a');
			}
			while ( 21 < $iScore && 0 < $iAces ) {
				$iScore -= 10;
				$iAces--;
			}
			$this->score = $iScore;
		}
		return $this->score;
	}
	public function busted() {
		return (21 < $this->score());
	}
	public function blackjack() {
		return (21 === $this->score() && 2 === count($this->cards));
	}
	public function reset() {
		$this->cards = array();
		$this->score = 0;
		$this->stands = false;
	}
}

class Player extends Dealer {
	public $balance = 0;
	public $bet = -1;
	public $insurance = -1;
	public $payout = -1; # purely cosmetic
	public function reset() {
		parent::reset();
		$this->insurance = -1;
		$this->payout = -1;
	}
	public function insurance() {
		// No arguments, because insurance is set on floor(bet/2)
		$iInsurance = floor($this->bet/2);
		if ( 0 >= $this->insurance && $this->balance >= $iInsurance && 2 == count($this->cards) ) {
			$this->balance -= $iInsurance;
			$this->insurance = $iInsurance;
			return true;
		}
		return false;
	}
}

class Blackjack {
	const highest_hitter = HIGHEST_HITTER;
	const cut_card = CUT_CARD;
	public $dealer = null;
	public $player = null;
	public $winner = null;
	public $gameover = false;
	public $decks = 0;
	public $deck = null;
	public function __construct($f_iDecks = 6) {
		// Prepare deck
		$this->decks = (int)max(1, min(8, $f_iDecks));
		$this->deck = new Deck();
		while ( $this->deck->size() < $this->decks*52 ) {
			$this->deck->add_deck(new Deck());
		}
		$this->deck->shuffle();
	}
/*	public function wins(Dealer $f_objPlayer, $f_iPayout = 0) {
		$this->gameover = true;
		$this->player->stands = true;
		$this->dealer->stands = true;
		$this->winner = $f_objPlayer;
		if ( $this->player === $this->winner ) {
			$this->player->balance += $this->player->payout;
		}
	}*/
	public function bet($f_iAmount) {
		// No Player as argument because there is only one
		if ( 0 > (int)$f_iAmount || $this->player->balance < (int)$f_iAmount ) {
			return false;
		}
		// save bet
		$this->player->bet = (int)$f_iAmount;
		$this->player->balance -= $this->player->bet;
		// Reset values
		$this->player->reset();
		$this->dealer->reset();
		$this->winner = null;
		$this->gameover = false;
		// make sure there's enough cards in the deck left
		$iCardsLeft = $this->deck->size();
		if ( blackjack::cut_card >= $iCardsLeft ) {
			$this->deck->replenish();
		}
		// deal first 4 cards
		$this->hit($this->player, true);
		$this->hit($this->dealer, true);
		$this->hit($this->player, true);
		$this->hit($this->dealer, true);
		// player could have blackjack, in which case the dealer plays
		if ( 21 <= $this->player->score() ) {
			$this->dealerPlays();
		}
	}
	public function hit(Dealer $f_objPlayer, $f_bForced = false) {
		if ( 21 <= $f_objPlayer->score() ) {
			return false;
		}
		$objNextCard = $this->deck->next();
		if ( !$objNextCard ) {
			// out of cards, bad value for blackjack::cut_card
			$this->gameover = true;
			$this->player->stands = true;
			$this->dealer->stands = true;
			$this->winner = $this->player;
			$this->player->payout = 2*$this->player->bet;
			$this->player->balance += $this->player->payout;
			return false;
		}
		$f_objPlayer->receive($objNextCard);
		if ( 21 <= $f_objPlayer->score() && !$f_bForced ) {
			if ( $this->player === $f_objPlayer ) {
				$this->dealerPlays();
			}
		}
		return true;
	}
	public function insurance() {
		// No Player as argument because there is only one, no amount as argument because insurance is set at floor( bet / 2 )
		if ( !$this->gameover && 2 == count($c=$this->dealer->cards()) && 'a' === $c[0]->short ) {
			return $this->player->insurance();
		}
		return false;
	}
	public function doubledown() {
		// No Player as argument because there is only one
		if ( 2 == count($this->player->cards()) && !$this->player->stands && $this->player->balance >= $this->player->bet ) {
			$this->player->balance -= $this->player->bet;
			$this->player->bet += $this->player->bet;
			$this->hit($this->player);
			$this->dealerPlays();
		}
	}
	public function dealerPlays() {
		$this->gameover = true;
		$this->player->payout = 0; # who knows what will happen, but for now it's still 0

		// Player stands
		$this->player->stands = true;
		if ( $this->player->busted() ) {
			$this->winner = $this->dealer;
			$this->dealer->stands = true;
			// Player wins nothing, so exit
			return;
		}

		// deal cards for dealer
		while ( blackjack::highest_hitter >= $this->dealer->score() ) {
			$this->hit($this->dealer);
		}
		$this->dealer->stands = true;

		// conclude winner
		if ( $this->dealer->blackjack() ) {
			// Dealer has blackjack, the rest doesn't matter
			$this->winner = $this->dealer;
			if ( 0 < $this->player->insurance ) {
				$this->player->payout = 3*$this->player->insurance;
				$this->player->balance += $this->player->payout;
			}
		}
		else if ( $this->player->blackjack() ) {
			// Player has blackjack and dealer doesn't
			$this->winner = $this->player;
		}
		else if ( $this->dealer->busted() ) {
			// Player isn't busted, we know that for sure, so if the dealer is, the player wins
			$this->winner = $this->player;
		}
		else if ( $this->player->score() === $this->dealer->score() ) {
			// Push, nobody wins, but the game is over
			$this->winner = null;
			// No win, but bet is refunded
			$this->player->payout = $this->player->bet;
			$this->player->balance += $this->player->payout;
		}
		else {
			// No push, no blackjack, no busts
			$this->winner = $this->player->score() > $this->dealer->score() ? $this->player : $this->dealer;
		}

		// Player pay-out?
		if ( $this->winner === $this->player ) {
			$this->player->payout = ( $this->player->blackjack() ? 2.5 : 2 )*max(0, $this->player->bet);
			$this->player->balance += $this->player->payout;
		}
	}
}

?>
