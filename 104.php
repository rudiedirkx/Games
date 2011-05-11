<?php
// BLACKBOX (Ajax)

if ( isset($_GET['source']) ) {
	highlight_file(__FILE__);
	exit;
}

session_start();

require_once('connect.php');
require_once('inc.cls.json.php');
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

// show top 10 //
else if ( !empty($_GET['top10']) ) {
	printTop10();
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

	exit(json::encode(array('updates' => $arrUpdates)));
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

	exit(json::encode(array('atoms' => $arrAtoms)));
}

// check //
else if ( isset($_POST['check']) ) {
	$arrAtoms = isset($_POST['atoms']) ? (array)$_POST['atoms'] : array();;
	if ( count($arrAtoms) !== $ATOMS ) {
		exit('You suggested '.count($arrAtoms).' atoms, but there are '.$ATOMS.'.');
	}
	$iFound = 0;
	foreach ( $arrAtoms AS $c ) {
		if ( 2 == count($c = explode(':', $c)) ) {
			$iFound += (int)is_atom($c[0], $c[1]);
		}
	}
	$_SESSION[S_NAME]['map'] = array();

	if ( $iFound !== $ATOMS ) {
		exit('You found '.$iFound.' / '.$ATOMS.': not enough!');
	}

	$name = !isset($_SESSION[S_NAME]['name']) ? 'Anonymous' : $_SESSION[S_NAME]['name'];
	$playtime = time() - $_SESSION[S_NAME]['starttime'];
	$beams = (int)$_SESSION[S_NAME]['beams'];
	mysql_query("INSERT INTO blackbox (name, sides, atoms, beams, playtime, utc) VALUES ('".addslashes($_SESSION[S_NAME]['name'])."', ".(int)$SIDES.", ".(int)$ATOMS.", ".$beams.", ".$playtime.", ".time().");");
	exit('You found the '.$ATOMS.' atoms with '.$beams.' beams in '.$playtime.' seconds!');
}

// change name //
else if ( isset($_POST['new_name']) ) {
	$_SESSION[S_NAME]['name'] = $_POST['new_name'];
	exit(htmlspecialchars($_POST['new_name']));
}

?>
<!DOCTYPE html>
<html>

<head>
<title>BLACKBOX</title>
<style>
* { margin:0; padding:0; }
body { background-color:#ccc; overflow:auto; overflow-x:hidden; overflow-y:auto; }
body, table, input { font-family:verdana; font-size:11px; color:#000; line-height:170%; cursor:default; }
input[type=button] { padding:3px 15px; }
p { margin:10px 0; }
a { color:#fff; text-decoration:none; }
a:hover { color:#000; text-decoration:underline; }
#gamerules, #top10, #left_frame, #right_frame { position:absolute; top:0; width:200px; text-align:center; min-height:100%; z-index:2; }
#top10 table, #left_frame table, #right_frame table, #gamerules table { text-align:left; }
#right_frame, #gamerules { right:0; border-left:solid 1px #999; background-color:#bbb; }
#gamerules { width:300px; right:-361px; z-index:4; height:100%; overflow:auto; }
#top10, #left_frame { left:0; border-right:solid 1px #999; background-color:#bbb; }
#top10 { z-index:4; width:360px; left:-361px; }
#top10 table { width:100%; }
#content_frame { position:absolute; top:0; left:0; min-height:100%; width:100%; }
table#blackbox { border-collapse:collapse; margin:20px; }
table#blackbox tbody#tbody_blackbox td { width:33px; height:33px; text-align:center; padding:0px; border:solid 1px #fff; }
table#blackbox tbody#tbody_blackbox td.corner { background-color:#0df; }
table#blackbox tbody#tbody_blackbox td.cfield { background:#aaa; font-weight:bold; font-size:13px; }
table#blackbox tbody#tbody_blackbox td.cfield_hilite { background:lime; }
table#blackbox tbody#tbody_blackbox td.sd, table#blackbox tbody#tbody_blackbox td.sl, table#blackbox tbody#tbody_blackbox td.su, table#blackbox tbody#tbody_blackbox td.sr { cursor:pointer; }
table#blackbox tbody#tbody_blackbox td.sd { background:#bbb url(images/down.gif) no-repeat center bottom; }
table#blackbox tbody#tbody_blackbox td.sl { background:#bbb url(images/left.gif) no-repeat left center; }
table#blackbox tbody#tbody_blackbox td.su { background:#bbb url(images/up.gif) no-repeat center top; }
table#blackbox tbody#tbody_blackbox td.sr { background:#bbb url(images/right.gif) no-repeat right center; }
div#loading { position:absolute; top:10px; right:10px; padding:5px; display:none; background-color:#f00; color:#fff; z-index:4; }
</style>
<script src="/js/mootools_1_11.js"></script>
<script>
<!--//
window.Blackbox = new Class({
	initialize: function() {
		this.reset();
		this.m_szName = '<?php echo isset($_SESSION[S_NAME]['name']) ? addslashes($_SESSION[S_NAME]['name']) : '?'; ?>';
	},

	gameover: function( set ) {
		if ( !set ) return this.m_bGameOver;
		this.m_bGameOver = set;
		$clear(this.m_iTimer);
		return this.m_bGameOver;
	},

	second: function() {
		this.m_iPlaytime++;
		$('playtime').update(''+this.m_iPlaytime);
	},

	fire : function( f_coords ) {
		if ( this.gameover() ) {
			return this.reset();
		}
		if ( 0 > this.m_iPlaytime ) {
			this.m_iTimer = setInterval(this.second.bind(this), 999);
			this.second();
		}
		new Ajax('?', {
			data : 'fire=1&beam=' + this.m_iBeams++ + '&x=' + f_coords[0] + '&y=' + f_coords[1],
			onComplete : function( t ) {
				try {
					var rv = eval( "(" + t + ")" );
				} catch (e) {
					alert('Response error: '+t);
					return;
				}
				if ( rv.error ) {
					alert(rv.error);
					return;
				}
				for ( var i=0; i<rv.updates.length; i++ ) {
					$('fld_' + rv.updates[i][0] + '_' + rv.updates[i][1]).style.backgroundColor = rv.updates[i][2];
				}
			}
		}).request();
		return false;
	},

	revealAtoms : function() {
		if ( this.gameover() ) {
			return this.reset();
		}
		self = this;
		new Ajax('?', {
			data : 'reveal=1',
			onComplete : function(t) {
				try {
					var rv = eval( "(" + t + ")" );
				} catch (e) {
					alert('Response error: '+t);
					return;
				}
				if ( rv.error ) {
					alert(rv.error);
					return;
				}
				self.gameover(1);
				for ( var i=0; i<rv.atoms.length; i++ ) {
					$('fld_' + rv.atoms[i][0] + '_' + rv.atoms[i][1]).innerHTML = '&dagger;';
				}
			}
		}).request();
		return false;
	},

	check : function() {
		if ( this.gameover() ) {
			return this.reset();
		}
		this.gameover(1);
		// Collect selected atoms
		var a = [];
		$('blackbox').getElements('td[grid=1]').each(function(el) {
			if ( el.green ) {
				a.push('&atoms[]='+(el.parentNode.sectionRowIndex-1)+':'+(el.cellIndex-1)+'');
			}
		});
		var self = this;
		new Ajax('?', {
			data : 'check=1' + a.join(''),
			onComplete : function(t) {
				alert(t);
			}
		}).request();
		return false;
	},

	reset : function() {
		var self = this;
		new Ajax('?', {
			data : 'reset=1',
			onComplete : function(t) {
				$$('#tbody_blackbox td').each(function(el) {
					el.css('background-color', '').update('');
					el.green = false;
					el.red = false;
				});
				self.m_iBeams = 0;
				self.m_bGameOver = false;
				self.m_iPlaytime = -1;
				this.m_iTimer = 0;
				$('playtime').update('-');
			}
		}).request();
		return false;
	},

	changeName : function(name) {
		name = name || prompt('New name:', this.m_szName);
		if ( !name ) return false;
		new Ajax('?', {
			data : 'new_name=' + name,
			onComplete : this.setName.bind(this)
		}).request();
		return false;
	},

	setName: function(name) {
		this.m_szName = name;
		$('your_name').innerHTML = name;
	},

	hideTop10: function() {
		$('top10').animate({'left': -361}, 700);
		return false;
	},

	showTop10: function() {
		new Ajax('?top10=1', {
			onComplete: function(t) {
				$('top10').update(t);
			}
		}).request();
		$('top10').animate({'left': 0}, 700);
		return false;
	},

	hideGameRules: function() {
		$('gamerules').animate({'right': -361}, 700);
		return false;
	},

	showGameRules: function() {
		$('gamerules').animate({'right': 0}, 700);
		return false;
	}

}); // END Class Blackbox
//-->
</script>
</head>

<body>
<div id="loading"><b>AJAX BUSY</b></div>

<div align="center" id="content_frame">
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
			echo '<td id="fld_'.$i.'_'.$j.'" grid="1" class="cfield"></td>';
		}
		else {
			// sides
			if ( -1 == $i )				{ $d = "sd"; }
			else if ( $SIDES == $j )	{ $d = "sl"; }
			else if ( $SIDES == $i )	{ $d = "su"; }
			else if ( -1 == $j )		{ $d = "sr"; }
			echo '<td id="fld_'.$i.'_'.$j.'" side="1" class="'.$d.'"></td>';
		}
	}
	echo '</tr>';
}

?></tbody></table>
	<p><input type="button" value="CHECK" onclick="objBlackbox.check();" /></p>
	<p><input type="button" value="View Atoms" onclick="objBlackbox.revealAtoms();" /></p>
</div>


<div id="left_frame">
	<p><a href="#reset" onclick="return objBlackbox.reset(true);">Restart</a></p>
	<p><a href="#showtop10" id="top10_html" onclick="return objBlackbox.showTop10();">Top 10</a></p>
	<p><a href="#changename" onclick="return objBlackbox.changeName();">Change Name</a></p>
</div>
<div id="top10" style=""><?php printTop10(); ?></div>

<div id="right_frame">
	<p><b>WHAT TO DO</b></p>
	<div id="game_rules">
		<p><a href="#showgamerules" onclick="return objBlackbox.showGameRules();">More...</a></p>
		<p>You must find all atoms. The sooner the better. When you think you got them, hit 'CHECK' to check if you do!</p>
	</div>
	<p><b>Atoms to find: <?php echo $ATOMS; ?></b></p>
	<p>Playtime: <b id="playtime">-</b></p>
	<p>Your name: <b id="your_name"><?php echo $_SESSION[S_NAME]['name']; ?></b></p>
	<p>Selected Atoms: <span id="stats_hilighted">0</span></p>
</div>
<div id="gamerules">
	<p><a href="#" onclick="return objBlackbox.hideGameRules();">Less...</a></p>
	<p>You must find all Atoms! The Atoms are hidden in the grey field.<br/>
	You can fire beams that might tell you the location of the Atoms.<br/>
	You do that by clicking on side cells (the lighter grey ones).<br/>
	A beam turns before it hits an Atom.<br/>If you fire a beam from below and there is an Atom on the left somewhere,the beam will turn to the right:<br/>
	<img src="/141.php?image=bb2"><br/>
	<b>When the beam reaches another side cell, both cells are colored!</b><br/>
	If it directly hits an atom its absorbed:<br/>
	<img src="/141.php?image=bb1"><br/>
	<b>The side cell (where the beam came from) is then GREY!</b><br/>
	It's also possible that a beam makes a U-turn and gets right back where it came from.<br/>
	Either it doesnt get the chance to enter the field (there's an atom right or left of where the beam enters)<br/>
	or it must make a U-turn:<br/>
	<img src="/141.php?image=bb3"><br/>
	<b>The side cell is then WHITE!</b><br/>
	<a href="#" onclick="return objBlackbox.hideGameRules();">Less...</a></p>
</div>


<script type="text/javascript">
<!--//
Ajax.setGlobalHandlers({
	onStart : function() {
		$('loading').style.display = "block";
	},
	onComplete : function() {
		if ( !Ajax.busy ) {
			$('loading').style.display = "none";
		}
	}
});

var objBlackbox = new Blackbox();
<?php if ( !isset($_SESSION[S_NAME]['name']) ) { echo 'objBlackbox.changeName(prompt(\'New name:\', objBlackbox.m_szName));'; } ?>

$('blackbox').addEvents({
	click : function(e) {
		e = new Event(e).stop();
		if ( objBlackbox.m_gameover ) {
			return;
		}
		if ( objBlackbox.m_bGameOver ) {
			return objBlackbox.reset();
		}
		if ( 'TD' !== e.target.nodeName ) { return false; }
		if ( '1' === e.target.getAttribute('grid') ) {
			if ( !e.target.red ) {
				e.target.green = !e.target.green;
				e.target.style.backgroundColor = e.target.green ? 'lime' : '';
			}
		}
		else if ( '1' === e.target.getAttribute('side') ) {
			objBlackbox.fire([ e.target.parentNode.sectionRowIndex-1, e.target.cellIndex-1 ]);
		}
	},
	contextmenu : function(e) {
		e = new Event(e).stop();
		if ( objBlackbox.m_bGameOver ) {
			return objBlackbox.reset();
		}
		if ( 'TD' !== e.target.nodeName || '1' !== e.target.getAttribute('grid') ) { return false; }
		e.target.red = !e.target.red;
		e.target.style.backgroundColor = e.target.red ? 'red' : '';
		if ( e.target.red ) {
			e.target.green = false;
		}
	}
});
//-->
</script>
</body>

</html>
<?php




































function printTop10() {
	$qTop10 = mysql_query('SELECT * FROM blackbox ORDER BY (playtime*beams) ASC, utc DESC LIMIT 10;');
	echo '<table class="top10">';
	echo '<tr><th></th><th>Name</th><th>Playtime</th><th>Beams fired</th><th>Time</th></tr>';
	$n=1;
	while ( $r = mysql_fetch_assoc($qTop10) ) {
		echo '<tr bgcolor="'.( $n%2 == 1 ? '#cccccc' : '#aaaaaa' ).'"><td>'.$n++.'</td><td>'.$r['name'].'</td><td>'.$r['playtime'].'</td><td>'.$r['beams'].'</td><td>'.date('Y-m-d H:i:s', $r['utc']).'</td></tr>';
	}
	echo '</table>';
	echo '<p><a href="#" onclick="return objBlackbox.hideTop10()">&lt; back</a></p>';
}

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


