<?php
// BLACKBOX (JS)

session_start();

$bShowCoords	= false;

// require_once("connect.php");
require 'inc.functions.php';

define( "S_NAME",	"st_2_user" );
define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );

$SIDES				= 8;
$ATOMS				= 5;
$OPENSOURCE			= false;
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
$arrBoolean = array('false', 'true');

$_page		= isset($_POST['page'])		? strtolower(trim($_POST['page']))		: ( isset($_GET['page'])	? strtolower(trim($_GET['page']))	: '' );
$_action	= isset($_POST['action'])	? strtolower(trim($_POST['action']))	: ( isset($_GET['action'])	? strtolower(trim($_GET['action']))	: '' );


/** GET SECRET **/
if ( $_action == "get_secret" ) {
	exit( empty($_SESSION[S_NAME]['_secret']) ? '' : $_SESSION[S_NAME]['_secret'] );
}

/** GET MAP **/
else if ( $_action == "get_map" ) {
	$_SESSION[S_NAME]['_secret'] = rand_string(24);
	$arrOutput = array($_SESSION[S_NAME]['_secret'], Create_Field($SIDES, $ATOMS));
	exit(json_encode( $arrOutput ));
}

/** STOP **/
else if ( $_action == "stop") {
	$_SESSION[S_NAME]	= array(
		'map' => array(),
	);

	go();
}

/** GAME RULES **/
else if ( $_page == "gamerules" ) {
	echo '<p><a href="#" onclick="return Blackbox.ShowGameRules();">Less...</a></p>';
	echo "You must find all Atoms! The Atoms are hidden in the grey field.<br/>";
	echo "You can fire beams that might tell you the location of the Atoms.<br/>";
	echo "You do that by clicking on side cells (the lighter grey ones).<br/>";
	echo "A beam turns before it hits an Atom.<br/>If you fire a beam from below and there is an Atom on the left somewhere,";
	echo "the beam will turn to the right:<br/>";
	echo "<img src=\"?image=bb2\"><br/>";
	echo "<b>When the beam reaches another side cell, both cells are colored!</b><br/>";
	echo "If it hits an atom its absorbed:<br/>";
	echo "<img src=\"?image=bb1\"><br/>";
	echo "<b>The side cell (where the beam came from) is then GREY!</b><br/>";
	echo "It's also possible that a beam makes a U-turn and gets right back where it came from.<br/>";
	echo "Either it doesnt get the chance to enter the field (there's an atom right or left of where the beam enters)<br/>";
	echo "or it must make a U-turn:<br/>";
	echo "<img src=\"?image=bb3\"><br/>";
	echo "<b>The side cell is then WHITE!</b><br/>";
	echo '<a href="#" onclick="return Blackbox.ShowGameRules();">Less...</a>';
	exit;
}

/** IMAGES **/
else if ( isset($_GET['image']) ) {
	$bin = get_image($_GET['image'], false);
	if ( $bin ) {
		header('Content-type: image/gif');
		echo $bin;
	}

	exit;
}

$szActionTrackBeam	= ( isset($_SESSION[S_NAME]['gameover']) && $_SESSION[S_NAME]['gameover'] > 1 ) ? "stop" : "trackbeam";
$szActionFieldColor	= ( "stop" == $szActionTrackBeam ) ? "stop" : "fieldcolor";
$OPENSOURCE = ( "stop" == $szActionTrackBeam && $_SESSION[S_NAME]['gameover'] == 3 ) ? 1 : $OPENSOURCE;

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BLACKBOX</title>
<link rel="stylesheet" href="blackbox.css" />
<script src="js/rjs-custom.js"></script>
<script>
var xhrBusy = 0;
window.on('xhrStart', function() {
	xhrBusy++;
	$('#loading').show();
}).on('xhrDone', function() {
	xhrBusy--;
	xhrBusy == 0 && $('#loading').hide();
});

function time() {
	return Math.floor(Date.now() / 1000);
}

function move( url, target ) {
	if ( target ) {
		window.popup( url, target );
		return false;
	}
	document.location = url;
	return false;
}


function Blackbox() {
	this.m_opensource = <?= $arrBoolean[(int)(bool)$OPENSOURCE] ?>;

	this.m_mapAtoms = {};
	this.m_mapUser = {};

	this.m_iHighlights = 0;
	this.m_iMaxHilights = <?= (int)$ATOMS ?>;
	this.m_iAtomsFound = 0;

	this.m_secret = "";

	this.m_iColor = 0;
	this.m_arrColors = <?= json_encode($RECHTDOORKLEUREN) ?>;
	this.m_sides = <?= (int)$SIDES ?>;

	this.m_GameOver = false;
	Blackbox.m_iStartTime = 0;
	$('#playtime').setHTML("-");

	this.m_szAbsorbed = '#555';
	this.m_szWhite = '#fff';

	this.m_arrUpdates = [];
}

Blackbox.m_iStartTime = 0;
Blackbox.UpdateTimer = function() {
	if ( 0 < Blackbox.m_iStartTime ) {
		var iPlaytime = time() - this.m_iStartTime,
			output = iPlaytime + " sec";

		$('#playtime').setHTML(output);

		setTimeout('Blackbox.UpdateTimer()', 100);
	}
};

Blackbox.ChangeName = function() {
	var new_name = prompt('New name?', $('#your_name').getText());
	if ( new_name ) {
		var data = 'new_name=' + encodeURIComponent(new_name);
		$.post(location.pathname, data).on('done', function(e) {
			$('#your_name').setHTML(this.responseText);
		});
	}
	return false;
};

Blackbox.ShowGameRules = function() {
	if ( oldgamerulesinnerhtml ) {
		$('#game_rules').innerHTML = oldgamerulesinnerhtml;
		oldgamerulesinnerhtml = false;
		$('#right_frame').style.width = '200px';

		return false;
	}

	if ( gamerulesinnerhtml )
	{
		oldgamerulesinnerhtml = $('#game_rules').innerHTML;
		$('#game_rules').innerHTML = gamerulesinnerhtml;
		$('#right_frame').style.width = '400px';
	}
	else
	{
		$.get(location.pathname + '?page=gamerules').on('done', function(e) {
			oldgamerulesinnerhtml = $('#game_rules').innerHTML;
			gamerulesinnerhtml = this.responseText;
			$('#game_rules').innerHTML = gamerulesinnerhtml;
			$('#right_frame').style.width = '400px';
		});
	}
	return false;
};
var oldgamerulesinnerhtml = false;
var gamerulesinnerhtml = false;

Blackbox.reset = function( f_bResetAll ) {
	// delete old instance
	objBlackbox = null;

	// create new instance
	objBlackbox = new Blackbox();

	// fetch map & secret
	$.get(location.pathname + '?action=get_map').on('done', function(e) {
		var retval = JSON.parse(this.responseText);
		objBlackbox.m_secret = retval[0];
		objBlackbox.m_mapAtoms = retval[1];
	});

	if ( f_bResetAll ) {
		$('#stats_hilighted').setHTML(objBlackbox.m_iHighlights);

		// Recreate field
		for ( x=-1; x<=objBlackbox.m_sides; x++ ) {
			for ( y=-1; y<=objBlackbox.m_sides; y++ ) {
				fld_id = 'fld_'+x+'_'+y+'';
				if ( $(fld_id) ) {
					$(fld_id).innerHTML = "";
					$(fld_id).style.backgroundColor = "";
					if ( objBlackbox._ValidCoords(x,y) ) {
						$(fld_id).className = "grid";
					}
				}
			}
		}
	}

	return false;
}

Blackbox.prototype = {
	Fire : function( f_coords )
	{
		if ( this.m_GameOver )
		{
			return Blackbox.reset(true);
		}

		// Calculate...
		f_x = f_coords[0];
		f_y = f_coords[1];

			 if ( -1 == f_x && -1 < f_y && this.m_sides > f_y )				dir = "d";
		else if ( this.m_sides == f_y && -1 < f_x && this.m_sides > f_x )	dir = "l";
		else if ( this.m_sides == f_x && -1 < f_y && this.m_sides > f_y )	dir = "u";
		else if ( -1 == f_y && -1 < f_x && this.m_sides > f_x )				dir = "r";
		else
		{
			alert("ERR("+f_x+":"+f_y+")");
			return;
		}

		if ( this.m_iColor == this.m_arrColors.length )
		{
			this.m_iColor = 0;
		}

			 if ( dir == "u" || dir == "d" )	i = f_x;
		else if ( dir == "l" || dir == "r" )	i = f_y;
		else
		{
			alert("ERR");
			return;
		}

		// Check if timer has already started
		if ( 0 == Blackbox.m_iStartTime )
		{
			Blackbox.m_iStartTime = time();
			Blackbox.UpdateTimer();
		}

		// clean updates
		this.m_arrUpdates = [];
		// track beam (fill updates)
		this.TrackBeam( dir, [f_x,f_y] );

		// use updates to fill the actual user array
		for ( var i=0; i<this.m_arrUpdates.length; i++ )
		{
			coords = this.m_arrUpdates[i]['coords'];
			if ( !this.m_mapUser[coords[0]] ) this.m_mapUser[coords[0]] = {};
			this.m_mapUser[coords[0]][coords[1]] = this.m_arrUpdates[i]['color'];
			this._fldcolor( coords, this.m_arrUpdates[i]['color'] );
		}

		// clean updates
		this.m_arrUpdates = [];

	},


	/**
	 * @brief		_ValidCoords
	 * 				slave of TrackBeam
	 *
	 **/
	_ValidCoords : function( f_x, f_y )
	{
		if ( "undefined" == typeof f_y )
		{
			if ( "object" != typeof f_x ) return false;
			f_y = f_x[1];
			f_x = f_x[0];
		}

		return !( 0 > f_x || f_x >= this.m_sides || 0 > f_y || f_y >= this.m_sides );

	},

	/**
	 * @brief		_IsAtom
	 * 				slave of TrackBeam
	 *
	 **/
	_IsAtom : function( f_x, f_y )
	{
		if ( "undefined" == typeof f_y )
		{
			if ( "object" != typeof f_x ) return false;
			f_y = f_x[1];
			f_x = f_x[0];
		}

		return ( this.m_mapAtoms[f_x] && this.m_mapAtoms[f_x][f_y] );

	}, // END _IsAtom() */

	TrackBeam : function( f_szDirection, f_arrFrom, f_arrTo )
	{
		if ( !f_arrTo ) f_arrTo = f_arrFrom;

		// Curent coords
		x = f_arrTo[0];
		y = f_arrTo[1];

		// Moves
		TurnAroundFrom		= { "r":"l", "d":"u", "l":"r", "u":"d" };
		TakeLeftTurnFrom	= { "r":"u", "d":"r", "l":"d", "u":"l" };
		TakeRightTurnFrom	= { "r":"d", "d":"l", "l":"u", "u":"r" };

		/**
		 * right	: y+1
		 * left		: y-1
		 * down		: x+1
		 * up		: x-1
		 *
		 * Y is horizontal movement
		 * X is vertical movement
		 *
		**/

		dx = 0;
		dy = 0;

		bx1	= 1;
		by1	= 1;
		bx2	= 1;
		by2	= 1;

		tx	= 0;
		ty	= 0;

		switch ( f_szDirection )
		{
			case "r":
				dy	= 1;
				bx2	= -1;
				ty	= -1;
				turn1 = TakeLeftTurnFrom;
				turn2 = TakeRightTurnFrom;
			break;

			case "l":
				dy	= -1;
				by1	= -1;
				bx2	= -1;
				by2	= -1;
				ty = 1;
				turn1 = TakeRightTurnFrom;
				turn2 = TakeLeftTurnFrom;
			break;

			case "u":
				dx	= -1;
				bx1	= -1;
				bx2	= -1;
				by2	= -1;
				tx	= 1;
				turn1 = TakeLeftTurnFrom;
				turn2 = TakeRightTurnFrom;
			break;

			case "d":
				dx	= 1;
				by2	= -1;
				tx	= -1;
				turn1 = TakeRightTurnFrom;
				turn2 = TakeLeftTurnFrom;
			break;
		}

		// Next field
		if ( this._IsAtom(x+dx, y+dy) )
		{
			// Beam is absorbed
			this.m_arrUpdates.push( {"coords":f_arrFrom, "color":this.m_szAbsorbed} );
		}
		// Two next fields on the 'left' and 'right' of the beam
		else if ( this._IsAtom(x+bx1, y+by1) && this._IsAtom(x+bx2, y+by2) )
		{
			// Current field in grid?
			if ( this._ValidCoords(x, y) )
			{
				// Make U-turn and beam on
				return this.TrackBeam( TurnAroundFrom[f_szDirection], f_arrFrom, [x,y] );
			}
			else
			{
				this.m_arrUpdates.push( {"coords":f_arrFrom, "color":this.m_szWhite} );
			}
		}
		// The field on one side of the beam
		else if ( this._IsAtom(x+bx1, y+by1) )
		{
			if ( !this._ValidCoords(x,y) )
			{
				this.m_arrUpdates.push( {"coords":f_arrFrom, "color":this.m_szWhite} );
			}
			else
			{
				return this.TrackBeam( turn1[f_szDirection], f_arrFrom, [x,y] );
			}
		}
		// The field on the other side of the beam
		else if ( this._IsAtom(x+bx2, y+by2) )
		{
			if ( !this._ValidCoords(x,y) )
			{
				this.m_arrUpdates.push( {"coords":f_arrFrom, "color":this.m_szWhite} );
			}
			else
			{
				return this.TrackBeam( turn2[f_szDirection], f_arrFrom, [x,y] );
			}
		}
		// Nothing is stopping this baby :)
		else
		{
			// Next field
			if ( !this._ValidCoords(x+dx, y+dy) )
			{
				// Next field is a side cell
				if ( ([x+dx, y+dy]).join("|") == f_arrFrom.join("|") )
				{
					// Next field is the same side cell as the From cell
					kleur = this.m_szWhite;
				}
				else
				{
					// Next field is an output side cell, so color it
					kleur = this.m_arrColors[this.m_iColor];
					this.m_arrUpdates.push( {"coords":[x+dx,y+dy], "color":kleur} );
					this.m_iColor = this.m_iColor+1;
				}
				this.m_arrUpdates.push( {"coords":f_arrFrom, "color":kleur} );
			}
			// Just carry on in the same direction
			else
			{
				return this.TrackBeam( f_szDirection, f_arrFrom, [x+dx, y+dy] );
			}
		}

		return false;

	}, // END TrackBeam() */



	Fieldcolor : function( f_coords )
	{
		if ( this.m_GameOver )
		{
			return Blackbox.reset(true);
		}

		szHilightClass = 'hilite';

		fld = $('#fld_'+f_coords[0]+'_'+f_coords[1]);
		if ( this.m_mapUser[f_coords[0]] && this.m_mapUser[f_coords[0]][f_coords[1]] )
		{
			this.m_iHighlights = this.m_iHighlights-1;

			this._UnFldClass( f_coords, szHilightClass );

			if ( this.m_mapAtoms[f_coords[0]] && this.m_mapAtoms[f_coords[0]][f_coords[1]] )
			{
				this.m_iAtomsFound = this.m_iAtomsFound-1;
				if ( 0 > this.m_iAtomsFound ) this.m_iAtomsFound = 0;
			}

			delete(this.m_mapUser[f_coords[0]][f_coords[1]]);
			if ( 0 == this.m_mapUser[f_coords[0]].length ) delete(this.m_mapUser[f_coords[0]]);
		}
		else
		{
			if ( this.m_iMaxHilights > this.m_iHighlights )
			{
				this.m_iHighlights = this.m_iHighlights+1;

				this._FldClass( f_coords, szHilightClass );

				if ( this.m_mapAtoms[f_coords[0]] && this.m_mapAtoms[f_coords[0]][f_coords[1]] )
				{
					this.m_iAtomsFound = this.m_iAtomsFound+1;
				}

				if ( !this.m_mapUser[f_coords[0]] ) this.m_mapUser[f_coords[0]] = {};
				this.m_mapUser[f_coords[0]][f_coords[1]] = true;
			}
		}
		$('#stats_hilighted').innerHTML = this.m_iHighlights;

	}, // END Fieldcolor() */


	RevealAtoms : function()
	{
		for ( x=0; x<this.m_sides; x++ )
		{
			if ( this.m_mapAtoms[x] )
			{
				for ( y=0; y<this.m_sides; y++ )
				{
					if ( this.m_mapAtoms[x][y] )
					{
						fld_id = 'fld_'+x+'_'+y+'';
						$(fld_id).innerHTML = "&dagger;"
					}
				}
			}
		}

		this.m_GameOver = true;

	}, // END RevealAtoms() */


	CheckIfAllAtomsFound : function()
	{
		if ( this.m_GameOver )
		{
			return Blackbox.reset(true);
		}

		if ( this.m_iAtomsFound == this.m_iMaxHilights )
		{
			// FOUND //

			// Calculate playtime and stop timer
			iPlaytime = time() - Blackbox.m_iStartTime;
			Blackbox.m_iStartTime = 0;

			// Visualize atoms
			this.RevealAtoms();

			// Alert to user
			alert('You found all the atoms in ' + iPlaytime + ' seconds');

			// Make sure game over flag is true
			this.m_GameOver = true;
		}
		else
		{
			// NOT FOUND //

			// Nothing happens, just an alert to the user
			alert('Nope! But keep going!');
		}

	}, // END CheckIfAllAtomsFound() */


	_fldcolor : function( f_coords, f_color )
	{
		$('#fld_'+f_coords[0]+'_'+f_coords[1]).style.backgroundColor = f_color;

	}, // END _fldcolor() */


	_FldClass : function( f_coords, f_class )
	{
		$('#fld_'+f_coords[0]+'_'+f_coords[1]).className += " " + f_class;

	}, // END _FldClass() */

	_UnFldClass : function( f_coords, f_class )
	{
		obj = $('#fld_'+f_coords[0]+'_'+f_coords[1]);
		obj.className = obj.className.replace(f_class, '');

	} // END _UnFldClass() */
};

var objBlackbox;
function fc(c){return objBlackbox.Fieldcolor(c);}
function fi(c){return objBlackbox.Fire(c);}
window.onload = function(){Blackbox.reset(false);}


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

<div id="loading">
	<b>AJAX BUSY</b>
</div>

<div id="container">

	<div id="content">
		<table id="blackbox">
		<?php

		for ( $i=-1; $i<=$SIDES; $i++ )
		{
			echo '<tr>';
			for ( $j=-1; $j<=$SIDES; $j++ )
			{
				$c = array($i,$j);

				if ( array(-1,-1) == $c || array(-1,$SIDES) == $c || array($SIDES,-1) == $c || array($SIDES,$SIDES) == $c )
				{
					// corners
					echo '<td style="border:none;"></td>';
				}
				else if ( -1 < $i && $SIDES > $i && -1 < $j && $SIDES > $j )
				{
					// grid cells
					echo '<td id="fld_'.$i.'_'.$j.'" onclick="fc(['.$i.','.$j.']);" class="grid"></td>';
				}
				else
				{
					// sides
					if ( -1 == $i )			$d = "sd";
					else if ( $SIDES == $j )	$d = "sl";
					else if ( $SIDES == $i )	$d = "su";
					else if ( -1 == $j )	$d = "sr";

					echo '<td id="fld_'.$i.'_'.$j.'" onclick="fi(['.$i.','.$j.']);" class="'.$d.'"></td>';
				}
			}
			echo '</tr>';
		}

		?>
		</table>

		<p>
			<button class="submit" onclick="objBlackbox.CheckIfAllAtomsFound();">CHECK</button>
			<button onclick="objBlackbox.RevealAtoms();">View Atoms</button>
		</p>
	</div>

	<div id="menu">
		<p><a href onclick="return Blackbox.reset(true);">Restart</a></p>
		<!-- p><a href="#changename" onclick="return Blackbox.ChangeName();">Change Name</a></p -->
	</div>

	<div id="about">
		<p><b>WHAT TO DO</b></p>

		<p>You must find all atoms. The sooner the better. When you think you got them, hit 'CHECK' to check if you do!</p>
		<p><a href onclick="return toggleFrame('gamerules')">More...</a></p>

		<p><b>Atoms to find: <?= $ATOMS ?></b></p>
		<p>Playtime: <b id="playtime">-</b></p>
		<p>Selected atoms: <span id="stats_hilighted">0</span></p>
	</div>
</div>

<!-- <div id="gamerules" class="frame right">
	<?php include 'tpl.blackbox_rules.php' ?>
</div> -->

</body>

</html>
<?php

function save_atom( $sides, &$parrMap ) {
	$x = rand(0, $sides);
	$y = rand(0, $sides);
	if ( empty($parrMap[$x][$y]) )
	{
		$parrMap[$x][$y] = true;
		return true;
	}

	return save_atom($sides, $parrMap);
}

function create_field( $f_iSides, $f_iAtoms ) {
	$parrMap = array();

	for ( $i=0; $i<$f_iAtoms; $i++ )
	{
		Save_Atom( $f_iSides-1, $parrMap );
	}

	return $parrMap;
}

function go() {
	header("Location: ".basename($_SERVER['SCRIPT_NAME'])."");
	exit;
}

function rand_string( $f_iChars = 16 ) {
	$arrChars = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
	$szRandString = "";
	for ( $i=0; $i<$f_iChars; $i++ )
	{
		$szRandString .= $arrChars[array_rand($arrChars)];
	}
	return $szRandString;
}

function get_image( $image, $base64 = true ) {
	$data = '';
	switch ( $image ) {
		case 'right':
			$data = 'R0lGODlhFAAWAKEAAP///8z//wAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAACK4yPqcsd4pqAUU1az8V58+h9UtiFomWeSKpqZvXCXvZsdD3duF7zjw/UFQAAOw==';
			break;
		case 'left':
			$data = 'R0lGODlhFAAWAKEAAP///8z//wAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAACK4yPqcvN4h6MSViK7MVBb+p9TihKZERqaDqNKfbCIdd5dF2CuX4fbQ9kFAAAOw==';
			break;
		case 'up':
			$data = 'R0lGODlhFAAWAKEAAP///8z//wAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAACI4yPqcvtD6OcTQgarJ1ax949IFiNpGKaSZoeLIvF8kzXdlAAADs=';
			break;
		case 'down':
			$data = 'R0lGODlhFAAWAKEAAP///8z//wAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAACIoyPqcvtD6OcNImLs8Zne4582yKCpPh80Shl1VXF8kzXUAEAOw==';
			break;
		case 'bb1':
			$data = 'R0lGODlhXwBrAIAAAAAAAP///yH/C01hZ2ljVmlld2VyAp4Anm12aXCUAAAAHAAAACAAAAABAAAABAAAAAAAAP////////D//f/w//3/8P/t//D/7f/w/+3/8P/t//D//f/w//3/8P/9//D//f/w//3/8P/9//D//f/w//3/8P/9//D//f/w//3/8P/9//D//f/w//3/8IAAAAD//f/w//3/8P/9//D//f/w//3/8P/9//CAAAAA////8P////D////wACH5BAAAAAAALAAAAABfAGsAAAL/jI+py+0PYwK02ouz3rz7TxmAxIykYp5Iqopue7CtrNKnaZO5uAc99HvgYDFHqBE0El9JpLHXLC19LyIUOo0uhksaqArTosAz1Idcy6Jvwp14oqZ22865/b2iS+3WtU6/FdcnN4jkJhjmJ/FzFMiXSAhpWFcIiVdkePhYFslZ0ji2mdY5+qlZyXmpyEMpaYm4deqaCutoOzuqSsomygvyCxwsfLb712s8qKtc68uMjNt8jLmHmuvMI+v5Kj3STQ1tvOzIBScdQQ5O7nV9vgpkyyIjnlwbr2T+XrwodF8d7Z+nlTZa+LDg46evXb9bA60VXNjwH7hpDEttA0gxFEaF//O+aZzIkV3CjgQ3jhSJkOTHlRZLgjx58N6wmTRrdnCHUGA5kylFGuQp0yfElg5N/nzZ86FOXvSUeiQqMWLAp0wvvjwqdV/CfEszQg0ntKvWpkaHVnWZdZXKqF+9TkUa1GnFs0Wvmn2Gtq1alHHLilVI1u5frlbTwgRaZ+1blnTZNl68E27isFTx5rKJOfNMnHfdeh5bWO9hyXsUf96aM/Tj06ZDymVs2XFsyLRna2092nBSv5VB5129N+bk15F19xXcG7Bq28ERl6Y8l3k+3FhF70Ye3Xddw9WBT4cOW7ts8bU5D77OvbPy39K7S0cv2j352+CLW5epOb/+m1Pm9zH/D+B6AQ5IYHIFHgigLggueB6DDr73YISpSUghhBVeaB6GFSqoYYEcdjjghyCOWGEBADs=';
			break;
		case 'bb2':
			$data = 'R0lGODlhXwBrAIAAAAAAAP///yH/C01hZ2ljVmlld2VyAp4Anm12aXCUAAAAHAAAACAAAAABAAAABAAAAAAAAP////////D//f/w9/3/8Pf9//D3/f/w9/3/8Pf9//D//f/w//3/8P/9//D//f/w//AAEP/9//D//f/w//3/8P/9//D//f/w//3/8P/9//D//f/w//3/8IAAAAD//f/w//3/8P/9//D//f/w//3/8P/9//CAAAAA////8P////D////wACH5BAAAAAAALAAAAABfAGsAAAL/jI+py+0PYwK02ouz3rz7TxmAxIykYp5Iqopue7CtrNKnacf5Cwd79IPgHKFG8HEk9l6/YmnpgyaNvIXTKs0uh5NPdabtcVfe6DYMG5d20+f5HbxO0GA4kk2vaeP4t3h/R+WXBkjU9zdYxyeI2Ehotnb46KhoKFlHqQdpJbeSd1NodKmZCbrJyTipWomUirmq2YaVSPoIcoubq1smi/JJ0jt3WvtaHPsrEexpR2taCvxFLO1siwxkLRRNbbzt5jvcHQ59/a0cg92aLKPGPf6cyj7tDpvNhC6ym+9h39xlY64tnRmA4ITNeudtILZO5/olVOgwoERyFBsSjKMk4jJh/xcFHqQ3rmPGh/KSTaznyiBIkwVZpty4kpzIlw0R1tOHM6fOCic9fvxmM91MkjDbsRz6U6VRmQtHxrzZlKbLahqb3BOEFGhScUejEp3HyqZVjUK9bgV7rKrTpVDVSq0Y9unYoBmzKr3Llanbr1Pjsp37tOzes33Til1bUu/htyipLuYL17BcxHnbPiYc2dTOzZxv9aRcFC9azWa1shV82XRiy5MZ+5T8F3Rh0oNVV0bdGnJjv6sBn65bWvTskMFD3wZeW3hm4smNj2aeWvlu2L1lL+/avGZg5NGdD8feXftvrMXFr8Yd27V11ul1v6Yd/rN68tnlu6/bOb/+C1Ce9zH/D+B3AQ5IoHcFHhggQQguOB+DDl73YIQCSkhhgxVeqOCFCGaoYYEcdjjghyCOeGEBADs=';
			break;
		case 'bb3':
			$data = 'R0lGODlhXwBrAJEAAAAAAIAAAP///wAAACH/C01hZ2ljVmlld2VyAggC/212aXDUAQAAHAAAACAAAAAEAAAADgAAAAAAAIAAAP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACIiIiIiIiIiIiIiIiIiIiIiIiIiIgIiIiIiIiIiIiIiIiIiAiAiIiIiIiIiAiIiIiICIiIiIiIiIiICIiIiIgIiIiIiIiIiIgIiIiIiAiIiIiIiIiIiAiIiIiICIiIiIiIiIiIiIiIiIgIiIiIiIiIiIiIiIiIiAiIiIiIiIiIiIiIiEiICIiIiIiIiIiIiIiEiIgIiIiIiIiIiIiIiIiIiAiIiIiIiIiIiIiIiIiICIiIiIiIiIv8iIiIiIiICIiIiIiIiIiIiIiIiIgIiIiIiIiIiIiIiIiIiAiIiIiIiIiIiIiIiIiICIiIiIiIiIiIiIiIiIgIiIiIiIiIiIiIiIiIiAiIiIiIiIiIiIiIiIiICIiIiIiIiIiIiIiIiIgIiIiIiIiIgAAAAAAAAAAAAAAAAACIiIiIiIiICIiIiIiIiIiIiIiIiIgIiIiIiIiIiIiIiIiIiAiIiIiIiIiIiIiIiIiICIiIiIiIiIiIiIiIiIgIiIiIiIiIiIiIiIiIiAiIiIiIiIiAAAAAAAAAAAAAAAAAAIiIiIiIiESIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIKIiIiIiIiIiIiIgAh+QQAAAAAACwAAAAAXwBrAAAC/5SPqcvtD2MCtNqLs968+08ZgMSMpGKeSKqKbnuwrazSp2mLeQzz5f7q4RohBxCyKxJ7r+Tx+HBGmYLhb8qUGqnW2McnnHyDsK7OAy6L0eRZW7zNGoHQOJ3OncfDVf1SToQHeLenFljo1nf4x3fFaEj4mOhYkqdIuVAnCbd58+Yl2IiilGnpFGqYSYpiGog6Ofpa8+mFOLtYOeiXKzpq63nJWwrYmZaoKQyrfAvS7PwMPYY83HvLN81KnAppWQ183b29DIw9wRCAnp5sza19gI4An+1NYqasnoBvzH4cLhDgAOAKf8zCyTsnkJaEcgODwUrYACLDfeQMQpBIsKI2iP/nGtJbqDACC44LMLoruPGiR3EoRZFUYPKjyJBI4qmkWK9VygcxWWp0eZMmFnA7A64cl9Mhu5f5jvJruY2pTZwgdRWN6PQbpGgaAnjwyjXsBqGFpL7LmpQoUJ5oq6qNGlSp1n5XO1KdaXWt0bs188Jly3doO71YA/9KOpGMWQM9kVZNHGzxv7Z43z6MCzkz2UeSGz/9+Xfv5nWgLwMeTW1w6MKo56k2LVpu2tdLMWdEbPG0bLe0tXam3Ndybd2a/cJmvbty79m/DRd7nDt28WtiL4DlcL26dnvDpd+GXrckcMF0Cdttbc54d+TTl/Nujn68nejsv+NtDz95cPfK87eeL7/aefqRB9VxAv5X4HoH2tcXfradVJqC4jlHGm7hwSTfc/fRtyCEFpo3YXwUDuMgcQxiUaJ3HoIHIoYjugaggSEOeBiLAc6IYIS+PShTg9tRkF1XP25HhYBGFolkklNNmJBkSj4ZVz7pTAlllT1INSWVVm5JgllZOsllmF5qGWaZsSFkZprIoalmm0u6CWeUcc7pIp12NnVnnnoyUQAAOw==';
			break;
	}

	if ( $data ) {
		return $base64 ? $data : base64_decode($data);
	}
}
