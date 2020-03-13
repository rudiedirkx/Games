<?php
// BLACKBOX (PHP)

require __DIR__ . '/inc.bootstrap.php';

session_start();

define( 'S_NAME', 'bb2' );

define( 'BASEPAGE',	basename($_SERVER['SCRIPT_NAME']) );

$SIDES				= 8;
$ATOMS				= 5;
$KLEUR_absorbed		= "#555";
$RECHTDOORKLEUREN	= array(
	'#ff0',
	'#bd7',
	'#f00',
	'#00f',
	'#0f0',
	'#f80',
	'#f8f',
	'#b0f',
	'#320',
	'#b60',
	'green',
	'#46c',
	'#704',
);


if ( !empty($_GET['debug']) && array('144') === $_GET['debug'] ) {
	print_r($_SESSION[S_NAME]);
	exit;
}


// full session reset //
if ( !empty($_GET['fullreset']) ) {
	unset($_SESSION[S_NAME]);
	header('Location: 104.php');
	exit;
}

// reset //
if ( isset($_POST['reset']) ) {
	$arrMap = array_fill(0, $SIDES, array_fill(0, $SIDES, false));
	$iAtoms = 0;
	while ( $iAtoms < $ATOMS ) {
		$x = rand(0, $SIDES-1);
		$y = rand(0, $SIDES-1);
		if ( false === $arrMap[$y][$x] ) {
			$arrMap[$y][$x] = true;
			$iAtoms++;
		}
	}
	$_SESSION[S_NAME]['map'] = $arrMap;
	$_SESSION[S_NAME]['starttime'] = null;
	$_SESSION[S_NAME]['beams'] = 0;
	exit('OK');
}

// fire //
else if ( isset($_POST['fire'], $_POST['beam'], $_POST['x'], $_POST['y']) ) {
	$x = (int)$_POST['x'];
	$y = (int)$_POST['y'];

	if ( -1 == $x && -1 < $y && $SIDES > $y )			{ $dir = 'd'; }
	else if ( $SIDES == $y && -1 < $x && $SIDES > $x )	{ $dir = 'l'; }
	else if ( $SIDES == $x && -1 < $y && $SIDES > $y )	{ $dir = 'u'; }
	else if ( -1 == $y && -1 < $x && $SIDES > $x )		{ $dir = 'r'; }
	else												{ exit(__LINE__.':'.print_r($_POST, true)); }

	if ( $dir == 'u' || $dir == 'd' )		$i = $x;
	else if ( $dir == 'l' || $dir == 'r' )	$i = $y;
	else									exit(__LINE__.':'.print_r($_POST, true));

	if ( null === $_SESSION[S_NAME]['starttime'] ) {
		$_SESSION[S_NAME]['starttime'] = time();
	}
	$_SESSION[S_NAME]['beams']++;

	// Assign exit colour
	$szExitColour = $RECHTDOORKLEUREN[(int)$_POST['beam']%count($RECHTDOORKLEUREN)];

	// clean updates
	$arrUpdates = array();
	// track beam (fill updates)
	Track_Beam( $dir, array($x, $y) );

	exit(json_encode(array('updates' => $arrUpdates)));
}

// reveal //
else if ( isset($_POST['reveal']) ) {
	$arrAtoms = array();

	for ( $i=-1; $i<=$SIDES; $i++ ) {
		for ( $j=-1; $j<=$SIDES; $j++ ) {
			if ( is_atom($i,$j) ) {
				$arrAtoms[] = array($i, $j);
			}
		}
	}
	$_SESSION[S_NAME]['map'] = array();

	exit(json_encode(array('atoms' => $arrAtoms)));
}

// check //
else if ( isset($_POST['check']) ) {
	$arrAtoms = isset($_POST['atoms']) ? (array)$_POST['atoms'] : array();;
	if ( count($arrAtoms) !== $ATOMS ) {
		exit(json_encode(['error' => 'You suggested '.count($arrAtoms).' atoms, but there are '.$ATOMS.'.']));
	}
	$iFound = 0;
	foreach ( $arrAtoms AS $c ) {
		if ( 2 == count($c = explode(':', $c)) ) {
			$iFound += (int)is_atom($c[0], $c[1]);
		}
	}
	$_SESSION[S_NAME]['map'] = array();

	if ( $iFound !== $ATOMS ) {
		exit(json_encode(['error' => 'You have found '.$iFound.' / '.$ATOMS.': not enough!']));
	}

	$playtime = time() - $_SESSION[S_NAME]['starttime'];
	$beams = (int)$_SESSION[S_NAME]['beams'];
	$score = max(0, 2000 - $playtime * 5 - $beams * 30);

	exit(json_encode(['success' => 'You have found all atoms in ' . $playtime . ' seconds, using ' . $beams . " beams!\n\nScore: $score"]));
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BLACKBOX PHP</title>
<link rel="stylesheet" href="blackbox.css" />
<style>
</style>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
function Blackbox() {
	this.reset();
	this.m_szName = '?';
}
Blackbox.prototype = {
	gameover: function( set ) {
		if ( !set ) {
			return this.m_bGameOver;
		}
		this.m_bGameOver = set;
		clearInterval(this.m_iTimer);
		return this.m_bGameOver;
	},

	getTime: function() {
		return Math.ceil((Date.now() - this.m_iStartTime) / 1000);
	},

	timer: function() {
		$('#playtime').setHTML(String(this.getTime()) + ' sec');

		$('#score').setHTML(this.score());
	},

	score: function() {
		var score = Math.round(Math.max(0, 2000 - this.getTime() * 5 - this.m_iBeams * 30));
		return score;
	},

	fire : function( f_coords ) {
		if ( this.gameover() ) {
			return this.reset();
		}

		if ( this.m_iTimer == 0 ) {
			this.m_iStartTime = Date.now();
			this.m_iTimer = setInterval(this.timer.bind(this), 200);
			this.timer();
		}

		var data = 'fire=1&beam=' + this.m_iBeams++ + '&x=' + f_coords[0] + '&y=' + f_coords[1];
		var xhr = $.post('', data).on('done', function(e, rv) {
			if ( rv.error ) {
				alert(rv.error);
				return;
			}

			for ( var i=0; i<rv.updates.length; i++ ) {
				$('#fld_' + rv.updates[i][0] + '_' + rv.updates[i][1]).style.backgroundColor = rv.updates[i][2];
			}
		});
		return false;
	},

	revealAtoms : function() {
		if ( this.gameover() ) {
			return this.reset();
		}

		var self = this;
		$.post('', 'reveal=1').on('done', function(e, rv) {
			if ( rv.error ) {
				alert(rv.error);
				return;
			}

			self.gameover(1);
			for ( var i=0; i<rv.atoms.length; i++ ) {
				$('#fld_' + rv.atoms[i][0] + '_' + rv.atoms[i][1]).innerHTML = '&dagger;';
			}
		});
		return false;
	},

	check : function() {
		if ( this.gameover() ) {
			return this.reset();
		}
		this.gameover(1);

		// Collect selected atoms
		var a = $$('#blackbox td.grid.hilite').map(function(el) {
			return '&atoms[]=' + (el.parentNode.sectionRowIndex-1) + ':' + (el.cellIndex-1);
		});

		$.post('?', 'check=1' + a.join('')).on('done', (e, rsp) => {
			if ( rsp.success ) {
				Game.saveScore({
					level: <?= $SIDES ?> << 8 | <?= $ATOMS ?>,
					time: this.getTime(),
					moves: this.m_iBeams,
				});
			}

			alert(rsp.error || rsp.success);
		});
		return false;
	},

	reset : function() {
		var self = this;

		$$('#tbody_blackbox td')
			.setHTML('')
			.removeClass('hilite')
			.css('background-color', '')

		var data = 'reset=1';
		$.post('?', data).on('done', function(e, rsp) {
			self.m_iBeams = 0;
			self.m_bGameOver = false;
			self.m_iTimer = 0;
			self.m_iStartTime = 0;
			$('#playtime').setHTML('-');
		});

		return false;
	}

}; // END Class Blackbox
Blackbox.prototype.constructor = Blackbox;

function toggleFrame(name) {
	var el = $(name);
	el.toggleClass('show');
	if ( el.hasClass('show') ) {
		el.getElement('a').focus();
	}
	return false;
}
</script>
</head>

<body>
<div id="loading"><b>AJAX BUSY</b></div>

<div id="container">

	<div id="content">
		<table id="blackbox"><tbody id="tbody_blackbox"><?php

		for ( $i=-1; $i<=$SIDES; $i++ ) {
			echo '<tr>';
			for ( $j=-1; $j<=$SIDES; $j++ ) {
				$c = array($i,$j);
				if ( array(-1,-1) == $c || array(-1,$SIDES) == $c || array($SIDES,-1) == $c || array($SIDES,$SIDES) == $c ) {
					// corners
					echo '<td style="border:none;"></td>';
				}
				else if ( -1 < $i && $SIDES > $i && -1 < $j && $SIDES > $j ) {
					// grid cells
					echo '<td id="fld_' . $i . '_' . $j . '" class="grid"></td>';
				}
				else {
					// sides
					if ( -1 == $i )				{ $d = "sd"; }
					else if ( $SIDES == $j )	{ $d = "sl"; }
					else if ( $SIDES == $i )	{ $d = "su"; }
					else if ( -1 == $j )		{ $d = "sr"; }
					echo '<td id="fld_' . $i . '_' . $j . '" class="side ' . $d . '"></td>';
				}
			}
			echo '</tr>';
		}

		?></tbody></table>

		<p>
			<button class="submit" onclick="objBlackbox.check();">CHECK</button>
			<button onclick="objBlackbox.revealAtoms();">View Atoms</button>
		</p>
	</div>


	<div id="menu">
		<p><a href onclick="return objBlackbox.reset(true);">Restart</a></p>
	</div>

	<div id="about">
		<p><b>WHAT TO DO</b></p>

		<p>You must find all Atoms! The Atoms are hidden in the grey field.</p>

		<p><b>Atoms to find: <?= $ATOMS ?></b></p>
		<p>Playtime: <b id="playtime">-</b></p>
		<p>Score: <b id="score">-</b></p>
		<p>Hi-score: <b id="hiscore">-</b></p>

		<p>You can fire beams that might tell you the location of the Atoms.</p>
		<p>You do that by clicking on side cells (the lighter grey ones).</p>
		<p>A beam turns before it hits an Atom.<br/>If you fire a beam from below and there is an Atom on the left somewhere, the beam will turn to the right:</p>
		<p><img src="141.php?image=bb2"></p>
		<p><b>When the beam reaches another side cell, both cells are colored!</b></p>
		<p>If it hits an atom its absorbed:</p>
		<p><img src="141.php?image=bb1"></p>
		<p><b>The side cell (where the beam came from) is then GREY!</b></p>
		<p>It's also possible that a beam makes a U-turn and gets right back where it came from.</p>
		<p>Either it doesnt get the chance to enter the field (there's an atom right or left of where the beam enters)</p>
		<p>or it must make a U-turn:</p>
		<p><img src="141.php?image=bb3"></p>
		<p><b>The side cell is then WHITE!</b></p>
	</div>

</div>

<!-- <div id="gamerules" class="frame right">
	<?php include 'tpl.blackbox_rules.php' ?>
</div> -->

<script>
var xhrBusy = 0;
window.on('xhrStart', function() {
	xhrBusy++;
	$('#loading').show();
}).on('xhrDone', function() {
	xhrBusy--;
	xhrBusy == 0 && $('#loading').hide();
});

var objBlackbox = new Blackbox;

$('#blackbox')
	// Check for game over
	.on('click', function(e) {
		if ( objBlackbox.m_bGameOver ) {
			e.originalEvent.stopImmediatePropagation();
			objBlackbox.reset();
		}
	})

	// Fire beam
	.on('click', '.side', function(e) {
		objBlackbox.fire([ this.parentNode.sectionRowIndex-1, this.cellIndex-1 ]);
	})

	// Mark atom
	.on('click', 'td.grid', function(e) {
		this.toggleClass('hilite');
	})
;
</script>
</body>

</html>
<?php




































function valid_coords( $x, $y ) {
	return isset($_SESSION[S_NAME]['map'][$x][$y]);
}

function is_atom( $x, $y ) {
	return isset($_SESSION[S_NAME]['map'][$x][$y]) && true === $_SESSION[S_NAME]['map'][$x][$y];
}

function Track_Beam( $f_szDirection, $f_arrFrom, $f_arrTo = NULL ) {
	global	$SIDES,
			$arrUpdates,
			$szExitColour;

	if ( NULL === $f_arrTo ) { $f_arrTo = $f_arrFrom; }

	$szColorWhite		= "#fff";
	$szColorAbsorbed	= "#555";
	$bLeave				= false;

	// Curent coords
	$x = $f_arrTo[0];
	$y = $f_arrTo[1];

	/**
	 * naar rechts	= y+1
	 * naar links	= y-1
	 * naar onder	= x+1
	 * naar boven	= x-1
	 *
	 * Y is horizontal movement
	 * X is vertical movement
	 *
	**/

	if ( $f_szDirection == "r" )
	{
		if ( is_atom($x, $y+1) )
		{	// Absorbed! -> beginveld grijs
			$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorAbsorbed );
		}
		else if ( is_atom($x+1, $y+1) && is_atom($x-1, $y+1) )
		{	// U-turn, want beam is ingesloten (kan niet rechts en niet links, moet dus terug)
			if ( valid_coords($x, $y) )
			{
				return Track_Beam( "l", $f_arrFrom, array($x,$y) );
			}
			else if ( valid_coords($x,$y-1) )
			{	// Volgende stap ligt buiten het veld, dus terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else if ( !valid_coords($x,$y) )
			{	// Eerste stap, meteen U-turn terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else { echo __LINE__ . "::DIT IS ONMOGELIJK!!! $f_szDirection"; exit; }
		}
		else if ( is_atom($x+1, $y+1) )
		{
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar links: van right naar up
				return Track_Beam( "u", $f_arrFrom, array($x,$y) );
			}
		}
		else if ( is_atom($x-1, $y+1) )
		{
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar rechts: van right naar down
				return Track_Beam( "d", $f_arrFrom, array($x,$y) );
			}
		}
		else
		{
			if ( !valid_coords($x,$y+1) )
			{	// De straal komt uit het veld, zonder U-turn -> twee vakjes kleuren!
				if ( array($x, $y+1) == $f_arrFrom )
				{	// Teruggekaatst -> 1 vakje gekleurd
					$kleur = $szColorWhite;
				}
				else
				{	// AB -> Twee vakjes gekleurd
					$kleur = $szExitColour;
					$arrUpdates[] = array( $x, $y+1, $kleur );
				}
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $kleur );
			}
			else
			{	// Drie veldjes zijn vrij en volgend veldje is nog binnen het veld
				return Track_Beam( "r", $f_arrFrom, array($x,$y+1) );
			}
		}
	}
	else if ($f_szDirection == "d")
	{
		if ( is_atom($x+1, $y) )
		{	// Absorbed! -> beginveld grijs
			$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorAbsorbed );
		}
		else if ( is_atom($x+1, $y+1) && is_atom($x+1, $y-1) )
		{	// U-turn, want beam is ingesloten (kan niet rechts en niet links, moet dus terug)
			if ( valid_coords($x,$y) )
			{
				return Track_Beam( "u", $f_arrFrom, array($x,$y) );
			}
			else if ( valid_coords($x,$x-1) )
			{	// Volgende stap ligt buiten het veld, dus terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else if ( !valid_coords($x,$y) )
			{	// Eerste stap, meteen U-turn terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else { echo __LINE__ . "::DIT IS ONMOGELIJK!!! $f_szDirection"; exit; }
		}
		else if ( is_atom($x+1, $y-1) )
		{	// Bocht naar links
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar links: van down naar right
				return Track_Beam( "r", $f_arrFrom, array($x,$y) );
			}
		}
		else if ( is_atom($x+1, $y+1) )
		{	// Bocht naar rechts
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar rechts: van down naar left
				return Track_Beam( "l", $f_arrFrom, array($x,$y) );
			}
		}
		else
		{
			if ( !valid_coords($x+1,$y) )
			{	// De straal komt uit het veld, zonder U-turn -> twee vakjes kleuren!
				if ( array($x+1, $y) == $f_arrFrom )
				{	// Teruggekaatst -> 1 vakje kleuren
					$kleur = $szColorWhite;
				}
				else
				{	// AB -> Twee vakjes kleuren
					$kleur = $szExitColour;
					$arrUpdates[] = array( $x+1, $y, $kleur );
				}
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $kleur );
			}
			else
			{	// Drie veldjes zijn vrij en volgend veldje is nog binnen het veld
				return Track_Beam( "d", $f_arrFrom, arraY($x+1,$y) );
			}
		}
	}
	else if ($f_szDirection == "l")
	{
		if ( is_atom($x, $y-1) )
		{	// Absorbed! -> beginveld grijs
			$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorAbsorbed );
		}
		else if ( is_atom($x+1, $y-1) && is_atom($x-1 , $y-1) )
		{	// U-turn, want beam is ingesloten (kan niet rechts en niet links, moet dus terug)
			if ( valid_coords($x,$y) )
			{
				return Track_Beam( "r", $f_arrFrom, arraY($x,$y) );
			}
			else if ( valid_coords($x,$y+1) )
			{	// Volgende stap ligt buiten het veld, dus terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else if ( !valid_coords($x,$y) )
			{	// Eerste stap, meteen U-turn terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else { echo __LINE__ . "::DIT IS ONMOGELIJK!!! $f_szDirection"; exit; }
		}
		else if ( is_atom($x+1 ,$y-1) )
		{
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar rechts: van left naar up
				return Track_Beam( "u", $f_arrFrom, arraY($x,$y) );
			}
		}
		else if ( is_atom($x-1, $y-1) )
		{
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar links: van left naar down
				return Track_Beam( "d", $f_arrFrom, arraY($x,$y) );
			}
		}
		else
		{
			if ( !valid_coords($x,$y-1) )
			{	// De straal komt uit het veld, zonder U-turn -> twee vakjes kleuren!
				if ( array($x, $y-1) == $f_arrFrom )
				{	// Teruggekaatst -> 1 vakje kleuren
					$kleur=$szColorWhite;
				}
				else
				{	// A->B -> Twee vakjes kleuren
					$kleur = $szExitColour;
					$arrUpdates[] = array( $x, $y-1, $kleur );
				}
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $kleur );
			}
			else
			{	// Drie veldjes zijn vrij en volgend veldje is nog binnen het veld
				return Track_Beam( "l", $f_arrFrom, arraY($x,$y-1) );
			}
		}
	}
	else if ($f_szDirection == "u")
	{
		if ( is_atom($x-1, $y) )
		{	// Absorbed! -> beginveld grijs
			$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorAbsorbed );
		}
		else if ( is_atom($x-1, $y+1) && is_atom($x-1, $y-1) )
		{	// U-turn, want beam is ingesloten (kan niet rechts en niet links, moet dus terug)
			if ( valid_coords($x,$y) )
			{
				return Track_Beam( "d", $f_arrFrom, arraY($x,$y) );
			}
			else if ( valid_coords($x,$x+1) )
			{	// Volgende stap ligt buiten het veld, dus terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else if ( !valid_coords($x,$y) )
			{	// Eerste stap, meteen U-turn terug naar niks
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else { echo __LINE__ . "::DIT IS ONMOGELIJK!!! $f_szDirection"; exit; }
		}
		else if ( is_atom($x-1, $y-1) )
		{
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar rechts: van up naar right
				return Track_Beam( "r", $f_arrFrom, arraY($x,$y) );
			}
		}
		else if ( is_atom($x-1, $y+1) )
		{
			if ( !valid_coords($x,$y) )
			{	// Eerste stap onmogelijke bocht want nog niet in veld
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $szColorWhite );
			}
			else
			{	// Bocht naar links: van up naar left
				return Track_Beam( "l", $f_arrFrom, arraY($x,$y) );
			}
		}
		else
		{
			if ( !valid_coords($x-1,$y) )
			{	// De straal komt uit het veld, zonder U-turn -> twee vakjes kleuren!
				if ( array($x-1, $y) == $f_arrFrom )
				{	// Teruggekaatst -> 1 vakje kleuren
					$kleur=$szColorWhite;
				}
				else
				{	// AB -> Twee vakjes kleuren
					$kleur = $szExitColour;
					$arrUpdates[] = array( $x-1, $y, $kleur );
				}
				$arrUpdates[] = array( $f_arrFrom[0], $f_arrFrom[1], $kleur );
			}
			else
			{	// Drie veldjes zijn vrij en volgend veldje is nog binnen het veld
				return Track_Beam( "u", $f_arrFrom, arraY($x-1,$y) );
			}
		}
	}
}


