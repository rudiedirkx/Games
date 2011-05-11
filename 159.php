<?php
// Atomix

session_start();
define('S_NAME', 'atx');
require_once('inc.cls.json.php');

$g_arrLevels = array(
	1 => array(
		'molecule' => 'Water',
		'formula' => 'H<sub>2</sub>O',
		'map' => array(
			'xxxxx',
			'x   x',
			'x   xxxxxx',
			'x  x     x',
			'x x      xx',
			'x x    xx x',
			'x    x x  x',
			'xxx x  x  x',
			' x        x',
			' xxxxxxxxxx',
		),
		'atoms' => array(
			array(3, 2, 'H'),
			array(3, 7, 'H'),
			array(8, 6, 'O'),
		),
		'target' => array(
			'HOH',
		),
	),
	2 => array(
		'molecule' => 'Methane',
		'formula' => 'CH<sub>4</sub>',
		'map' => array(
			'xxxxxxxxxxxxx',
			'x   x  x    x',
			'x x  x x    x',
			'x  x   x  x x',
			'x      xxxx x',
			'x       x x x',
			'x    x  x   x',
			'x xxxxx    xx',
			'x   x  x   x',
			'xxxxx  x   x',
			'       xxxxx',
		),
		'atoms' => array(
			array(6, 2, 'H'),
			array(2, 6, 'H'),
			array(2, 8, 'H'),
			array(9, 5, 'H'),
			array(8, 3, 'C'),
		),
		'target' => array(
			' H ',
			'HCH',
			' H ',
		),
	),
	3 => array(
		'molecule' => 'Methanol',
		'formula' => 'CH<sub>3</sub>OH',
		'map' => array(
			'         xxxx',
			'xxxx     x  x',
			'x  x     x  x',
			'x  xx   xx  x',
			'x   xxxxx   x',
			'x x x  x    x',
			'x      x    x',
			'x  x x   x  x',
			'x           x',
			'x   x x   x x',
			'xxxxxx    x x',
			'     xxxxxxxx',
		),
		'atoms' => array(
			array(3, 4, 'H'),
			array(1, 8, 'H'),
			array(6, 10, 'H'),
			array(9, 6, 'H'),
			array(6, 5, 'C'),
			array(11, 3, 'O'),
		),
		'target' => array(
			' H ',
			'HCOH',
			' H ',
		),
	),
	4 => array(
		'molecule' => 'Ethylene',
		'formula' => 'C<sub>2</sub>H<sub>4</sub>',
		'map' => array(
			'xxx     xxx',
			'x x     x x',
			'x xxxxxxx x',
			'x      x  x',
			'xx    x   x',
			'x x     x x',
			'x   x    xx',
			'x  x      x',
			'x xxxxxxx x',
			'x x     x x',
			'xxx     xxx',
		),
		'atoms' => array(
			array(1, 2, 'H'),
			array(4, 4, 'C'),
			array(7, 5, 'H'),
			array(9, 5, 'C'),
			array(8, 7, 'H'),
			array(9, 7, 'H'),
		),
		'target' => array(
			'H  H',
			' CC',
			'H  H',
		),
	),
	5 => array(
		'molecule' => 'Propylene',
		'formula' => 'C<sub>3</sub>H<sub>6</sub>',
		'map' => array(
			' xxxxxxxxx  ',
			' x x x x x  ',
			' x x x x xxx',
			'xx   x x x x',
			'x      x x x',
			'x x      x x',
			'x x x      x',
			'x x x x   xx',
			'xxx x x x x ',
			'  x x x x x ',
			'  xxxxxxxxx ',
		),
		'atoms' => array(
			array(2, 1, 'H'),
			array(2, 3, 'H'),
			array(1, 4, 'H'),
			array(3, 4, 'H'),
			array(4, 3, 'C'),
			array(6, 3, 'H'),
			array(8, 4, 'C'),
			array(3, 6, 'C'),
			array(5, 8, 'H'),
		),
		'target' => array(
			' HH H',
			'HCCC',
			' HH H',
		),
	),
	6 => array(
		'molecule' => 'Ethanal',
		'formula' => 'CH<sub>3</sub>CHO',
		'map' => array(
			'xxxxxxxxxxx',
			'x         x',
			'x xx   xx x',
			'x         x',
			'x xxx xxx x',
			'x         x',
			'x xxx xxx x',
			'x         x',
			'x xx   xx x',
			'x         x',
			'xxxxxxxxxxx',
		),
		'atoms' => array(
			array(1, 3, 'H'),
			array(1, 5, 'H'),
			array(1, 6, 'C'),
			array(3, 9, 'O'),
			array(5, 5, 'H'),
			array(5, 6, 'H'),
			array(7, 3, 'C'),
		),
		'target' => array(
			' HH',
			'HCCO',
			' H',
		),
	),
	7 => array(
		'molecule' => 'Ethanol',
		'formula' => 'C<sub>2</sub>H<sub>6</sub>O',
		'map' => array(
			'     xxxxx  ',
			'     x   x  ',
			' xxxxx x x  ',
			' x       x  ',
			' xxx    xx  ',
			'   x x x xxx',
			'   xx    x x',
			'xxxx       x',
			'x      x   x',
			'xxxxx x  x x',
			'   x    x  x',
			'   xxxxxxxxx',
		),
		'atoms' => array(
			array(6, 1, 'H'),
			array(7, 1, 'C'),
			array(8, 3, 'H'),
			array(4, 4, 'H'),
			array(6, 5, 'H'),
			array(5, 6, 'H'),
			array(5, 7, 'C'),
			array(2, 8, 'O'),
			array(10, 10, 'H'),
		),
		'target' => array(
			' HH',
			'HCCOH',
			' HH',
		),
	),
);
$g_arrColors = array( 'H' => 'blue', 'O' => 'red', 'C' => 'gray' );

/** START GAME **/
if ( isset($_POST['action']) && 'get_maps' === $_POST['action'] ) {
	if ( !isset($_POST['level'], $g_arrLevels[(int)$_POST['level']]) ) {
		exit(json::encode(array('error' => 'Invalid level ['.(int)$_POST['level'].']')));
	}
	$arrLevel = $g_arrLevels[(int)$_POST['level']];

	reset_game((int)$_POST['level']);

	$arrColors = array();
	foreach ( $_SESSION[S_NAME]['atoms'] AS $a ) {
		$arrColors[$a[2]] = $g_arrColors[$a[2]];
	}
	exit(json::encode(array(
		'level'		=> $_SESSION[S_NAME]['level'],
		'map'		=> $_SESSION[S_NAME]['map'],
		'atoms'		=> $_SESSION[S_NAME]['atoms'],
		'target'	=> $_SESSION[S_NAME]['target'],
		'molecule'	=> $_SESSION[S_NAME]['molecule'],
		'formula'	=> $_SESSION[S_NAME]['formula'],
		'colors'	=> $arrColors,
	)));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>Atomix</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<style type="text/css">
#loading { border:medium none; height:100px; left:50%; margin-left:-50px; margin-top:-50px; position:absolute; top:50%; visibility:hidden; width:100px; }
table#atomix, table#atomixtarget { border-collapse:collapse; font-family:verdana,arial; }
table#atomix td, table#atomixtarget td { border:solid 1px #eee; font-size:18px; text-align:center; }
table#atomixtarget td { border-color:#fff; }
tbody#atomix_tb td, tbody#atomixtarget_tb td { width:25px; height:25px; color:white; font-weight:bold; }
td.atom { cursor:pointer; width:25px; height:25px; background:green url(images/159_underlay.gif) center center no-repeat; }
td.wall1 { background-color:#111; }
td.wall2 { background-color:#222; }
</style>
<script type="text/javascript">
<!--//
function Atomix(f_level) {
	// empty constructor
	this.loadLevel(f_level);
	$('atomix_tb').onclick = function(e) {
		e = new Event(e);
		if ( 'TD' == e.target.nodeName && !e.target.wall && e.target.atom ) {
			objAtomix.selected = e.target;
		}
	}
	document.onkeydown = function(e) {
		e = new Event(e);
		var dir;
		switch ( e.code ) {
			case 37: if ( e.control ) { return objAtomix.prevLevel(); } dir = 'left';	break;
			case 38: dir = 'up';	break;
			case 39: if ( e.control ) { return objAtomix.nextLevel(); } dir = 'right';	break;
			case 40: dir = 'down';	break;
			default: return;	break;
		}
		e.stop();
		objAtomix.moveAtom(dir);
	};
}
Atomix.prototype = {
	level : 0,
	selected : null,
	stack : [],
	nextLevel : function() {
		return this.loadLevel(this.level+1);
	},
	prevLevel : function() {
		return this.loadLevel(this.level-1);
	},
	moveAtom : function(f_dir) {
		if ( !this.selected ) {
			return;
		}
		// Step 1 - Evaluate direction to make dx and dy (=dc)
		var dc = [0, 0];
		switch ( f_dir ) {
			case 'up': dc[1] = -1; break;
			case 'down': dc[1] = 1; break;
			case 'left': dc[0] = -1; break;
			case 'right': dc[0] = 1; break;
			default: return; break;
		}
		// Step 2 - Find target location (farest empty cell)
		var target = this.selected;
		while ( target === this.selected || ( !target.wall && !target.atom ) ) {
			target = $('atomix_tb').rows[target.parentNode.sectionRowIndex+dc[1]].cells[target.cellIndex+dc[0]];
			this.distance++;
		}
		target = $('atomix_tb').rows[target.parentNode.sectionRowIndex-dc[1]].cells[target.cellIndex-dc[0]];
		this.distance--;
		if ( target.wall || target.atom ) {
			return;
		}
		// Step 3 - Update stats
		this.stack.push([this.selected.cellIndex, this.selected.parentNode.sectionRowIndex, f_dir.substr(0, 1)]);
		$('stats_moves').innerHTML = this.stack.length;
		$('stats_distance').innerHTML = this.distance;
		// Step 4 - Copy selected atom to target cell
		target.atom = this.selected.atom;
		target.className = this.selected.className;
		target.innerHTML = this.selected.innerHTML;
		target.style.backgroundColor = this.selected.style.backgroundColor;
		// Step 5 - Erase selected atom
		this.selected.atom = false;
		this.selected.className = '';
		this.selected.innerHTML = '<br />';
		this.selected.style.backgroundColor = '';
		this.selected = target;
	},
	loadLevel : function(f_level) {
		var self = this;
		new Ajax('?', {
			data : 'action=get_maps&level=' + f_level,
			onComplete : function(t) {
				try {
					var rv = eval( "(" + t + ")" );
				} catch (e) {}

				if ( rv.error ) {
					alert(rv.error);
					return;
				}

				// save level
				$('stats_level').innerHTML	= rv.level;
				self.level = rv.level;
				$('molecule').innerHTML		= rv.molecule;
				$('formula').innerHTML		= rv.formula;
				self.stack = [];
				self.selected = null;
				self.distance = 0;

				// empty current map
				while ( 0 < $('atomix_tb').childNodes.length ) {
					$('atomix_tb').removeChild($('atomix_tb').firstChild);
				}

				// save map
				$A(rv.map).each(function(row, y) {
					var nr = $('atomix_tb').insertRow($('atomix_tb').rows.length);
					for ( var x=0; x<row.length; x++ ) {
						var nc = nr.insertCell(nr.cells.length);
						nc.innerHTML = '';
						nc.wall = false;
						nc.atom = false;
						if ( 'x' == row.substr(x, 1) ) {
							nc.className = 'wall' + Math.ceil(2*Math.random());
							nc.wall = true;
						}
					}
				});

				// show atoms
				$A(rv.atoms).each(function(box) {
					$('atomix_tb').rows[box[1]].cells[box[0]].className = 'atom';
					$('atomix_tb').rows[box[1]].cells[box[0]].atom = box[2];
					$('atomix_tb').rows[box[1]].cells[box[0]].innerHTML = box[2];
					$('atomix_tb').rows[box[1]].cells[box[0]].style.backgroundColor = rv.colors[box[2]];
				});

				// empty target image
				while ( 0 < $('atomixtarget_tb').childNodes.length ) {
					$('atomixtarget_tb').removeChild($('atomixtarget_tb').firstChild);
				}

				// create target image
				$A(rv.target).each(function(row, y) {
					var nr = $('atomixtarget_tb').insertRow($('atomixtarget_tb').rows.length);
					for ( var x=0; x<row.length; x++ ) {
						var nc = nr.insertCell(nr.cells.length);
						nc.className = 'atom';
						nc.innerHTML = '<br />';
						if ( ' ' !== row.substr(x, 1) ) {
							nc.innerHTML = row.substr(x, 1);
							nc.style.backgroundColor = rv.colors[row.substr(x, 1)];
						}
					}
				});
			}
		}).request();
		return false;
	}
};
//-->
</script>
</head>

<body>
<img id="loading" alt="loading" src="images/loading.gif" />

<script type="text/javascript">if ( window.console && 'object' == typeof window.console && window.console.firebug ) { document.write('<div style="background-color:pink;font-weight:bold;margin:10px;padding:10px;color:white;">Firebug can slow this page down... It\'s not necessary but advised to shut it down.</div>'); }</script>

<table border="1" cellpadding="15" cellspacing="0">
<tr>
	<th class="pad">LEVEL <span id="stats_level">0</span></th>
	<td></td>
</tr>
<tr>
	<td class="pad" style="padding-top:0;">
	<table id="atomix" border="0">
		<thead>
			<tr><th class="pad" colspan="30">Your name: <span id="your_name">?</span></th></tr>
		</thead>
		<tbody id="atomix_tb"></tbody>
		<tfoot>
			<tr><th class="pad" colspan="30">Moves: <span id="stats_moves">0</span> (<span id="stats_distance">0</span>)</th></tr>
		</tfoot>
	</table></td>
	<td valign="top" align="left" class="pad">
		<div><span id="molecule">MOLECULE</span> (<span id="formula">FORMULA</span>)</div>
		<table id="atomixtarget">
			<tbody id="atomixtarget_tb"></tbody>
		</table>
		<br />
		<a href="#" onclick="objAtomix.loadLevel(prompt('Map #:', objAtomix.level));return false;">load level #</a><br />
		<br />
		<a href="#" onclick="objAtomix.prevLevel();return false;">&lt;&lt;</a> &nbsp; <a href="#" onclick="objAtomix.nextLevel();return false;">&gt;&gt;</a><br />
		<br />
		<a href="#" onclick="objAtomix.loadLevel(objAtomix.level);return false;">restart</a><br />
		<br />
		<a href="?action=reset">reset</a><br />
		<br />
		<a href="#" onclick="objAtomix.changeName(prompt('New name:', $('your_name').innerHTML));return false;">Change name</a><br />
		<br />
	</td>
</tr>
<tr>
	<th colspan="2" class="pad" id="stack_message">no stack messages</th>
</tr>
</table>

<script type="text/javascript">
<!--//
Ajax.setGlobalHandlers({
	onStart : function() {
		$('loading').style.visibility = 'visible';
	},
	onComplete: function() {
		if( !Ajax.busy ) {
			$('loading').style.visibility = 'hidden';
		}
	}
});
var objAtomix = new Atomix(1);
//-->
</script>
</body>

</html>
<?php

function reset_game( $f_iLevel = 0 ) {
	global $g_arrLevels;
	$arrLevel = $g_arrLevels[$f_iLevel];

	$_SESSION[S_NAME]['play']		= true;
	if ( empty($_SESSION[S_NAME]['name']) ) {
		$_SESSION[S_NAME]['name'] = 'Anonymous';
	}
	$_SESSION[S_NAME]['level']		= $f_iLevel;
	$_SESSION[S_NAME]['map']		= $arrLevel['map'];
	$_SESSION[S_NAME]['atoms']		= $arrLevel['atoms'];
	$_SESSION[S_NAME]['target']		= $arrLevel['target'];
	$_SESSION[S_NAME]['molecule']	= $arrLevel['molecule'];
	$_SESSION[S_NAME]['formula']	= $arrLevel['formula'];

}

?>
