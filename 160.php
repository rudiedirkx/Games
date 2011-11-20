<?php
// Pixelus

session_start();
define('S_NAME', 'pxl');
require_once('inc.cls.json.php');

$g_arrLevels = array(
	1 => array(
		'map' => array(
			'xxx xx',
			'o    x',
			'x    x',
			'x    x',
			'x    o',
			'xoxxx ',
		),
		'stones' => 3,
	),
	2 => array(
		'map' => array(
			'         xx',
			'        xxx',
			'     xxx x ',
			'   ox     x',
			'  o       x',
			' o        x',
			'o     xx  x',
			'x      x  x',
			'x         x',
			'x         x',
			'x         o',
			' oox     x ',
			'  ox    x  ',
			'   xxxxx   ',
		),
		'stones' => 8,
	),
	3 => array(
		'map' => array(
			'        xx    ',
			'       x  x   ',
			'  xx   x   x  ',
			' x  o  x    x ',
			'o    x x    x ',
			'x     x    x  ',
			' x        x   ',
			'  oo       xx ',
			'    o        o',
			'   x         o',
			'  x    xx   x ',
			'  x    x xxx  ',
			'   x   x x    ',
			'    xxx  x    ',
			'         x    ',
			'         xx   ',
		),
		'stones' => 7,
	),
	4 => array(
		'map' => array(
			'    xxxo            ',
			'   x    o           ',
			'  x      o          ',
			' x      x o         ',
			' x     x  x         ',
			'x      x   x        ',
			'x       x  x        ',
			'x     xx x x        ',
			'x    x    xx        ',
			'x    x     x        ',
			' x        x         ',
			'  x      x          ',
			'   x    x           ',
			'    xxxx            ',
		),
		'stones' => 4,
	),
	5 => array(
		'map' => array(
			'           oooo     ',
			'        ooo   o     ',
			'   x  oo     o      ',
			'   x x      o       ',
			'   x x      o       ',
			'   x x     x  xx    ',
			'xx x xx    xxxx     ',
			' xxxxxxxx  x        ',
			'  xxxxxxxxx         ',
			'  xx                ',
			'  x                 '
		),
		'stones' => 13,
	),
	6 => array(
		'map' => array(
			'          xxo       ',
			' ox      x  o       ',
			'x  xxxxxx   o       ',
			'x          x        ',
			' o         x        ',
			'  x        x        ',
			'  o   o     o       ',
			' o           x      ',
			' x           x      ',
			' x          xx      ',
			'  oxx       xx      ',
			'     xxxxxxxx       ',
		),
		'stones' => 10,
	),
	7 => array(
		'map' => array(
			'             x  ',
			'            x x ',
			'           x   x',
			'  xxxxxxxxx  xx ',
			' x          x   ',
			'x x         x   ',
			'x  x  xxxxx x   ',
			'x  xxx    xxx   ',
			'  oo  o  oo  o  ',
			'  o    o o    o ',
			' o      o      x',
		),
		'stones' => 12,
	),
	8 => array(
		'map' => array(
			'     xxxxx    ',
			'   xx     ox  ',
			'  o         o ',
			' x          x ',
			'x          x x',
			'x        x   x',
			'x       xx   x',
			'x            x',
			'x   x        x',
			'x       x   x ',
			' x         x  ',
			'  o      oo   ',
			'   oxxxxx     ',
		),
		'stones' => 7,
	),
	9 => array(
		'map' => array(
			'      xx       ',
			'  xx  x x  oo  ',
			'   xoxx  xx o  ',
			'    o      o   ',
			'xxxx  xxx x    ',
			' xx  xx x  xxx ',
			'   x  xxx     x',
			'   o        xx ',
			'  x oxx xx  x  ',
			'  x o x x x x  ',
			'   x  x x x x  ',
			'      x x  xx  ',
			'       x       ',
		),
		'stones' => 8,
	),
	10 => array(
		'map' => array(
			'      x       ',
			'oxxxxxxxxxxox ',
			'              ',
			'       xxxxoxx',
			'              ',
			'              ',
			'              ',
			'        xx o  ',
			' xxxxxxxxxx x ',
			'         oo x ',
			'o       x   x ',
			' xxxxxxx    x ',
			'  xxxxxxxxxo  ',
		),
		'stones' => 8,
	),
	11 => array(
		'map' => array(
			'           o    ',
			'          o     ',
			'         o      ',
			'  o     o     o ',
			' x   x x   x o  ',
			' x  x x  x x x o',
			' x x x  x x x x ',
			' x x x x x  x x ',
			'x  x xx x  x xx ',
			'x x x x x  x x  ',
			'x x x x x  x x  '
		),
		'stones' => 8,
	),
	12 => array(
		'map' => array(
			'     xxxx      ',
			'   xx    x     ',
			'  xxx    xxx   ',
			' xxxxx  x   xxx',
			'xxxx    x      ',
			'   xx  x   xxxx',
			'    xxx   x    ',
			'    xx   x     ',
			'     o   o     ',
			'     o    o    ',
			'     oo    o   ',
			'      oo    xxx',
			'       oo      ',
			'        oo     ',
			'          o    ',
		),
		'stones' => 14,
	),
	13 => array(
		'map' => array(
			'  xxx    ooo   ',
			'   x x  x x    ',
			'    xx  xo  xx ',
			'xxx        xxx ',
			'xxx         x  ',
			'  xx           ',
			'    o    o  xx ',
			'            xx ',
			'            xx ',
			'            xx ',
			'           ox  ',
			'       x   ox  ',
			'      ox   oxxx',
			'            x  ',
			'            x  ',
		),
		'stones' => 10,
	),
);

/** START GAME **/
if ( isset($_POST['action']) && 'get_maps' === $_POST['action'] ) {
	if ( !isset($_POST['level'], $g_arrLevels[(int)$_POST['level']]) ) {
		exit(json::encode(array('error' => 'Invalid level ['.(int)$_POST['level'].']')));
	}
	$arrLevel = $g_arrLevels[(int)$_POST['level']];

	reset_game((int)$_POST['level']);

	exit(json::encode(array(
		'level'		=> $_SESSION[S_NAME]['level'],
		'map'		=> $_SESSION[S_NAME]['map'],
		'stones'	=> $_SESSION[S_NAME]['stones'],
	)));
}

?>
<!doctype html>
<html lang="en">

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, maximum-scale=1.0, minimum-scale=1.0" />
<meta charset="utf-8" />
<title>Pixelus</title>
<script src="/js/mootools_1_11.js"></script>
<style>
#loading { border:medium none; height:100px; left:50%; margin-left:-50px; margin-top:-50px; position:absolute; top:50%; visibility:hidden; width:100px; }
table#pixelus { border-collapse:collapse; font-family:verdana,arial; }
#pixelus_tb { -webkit-tap-highlight-color: rgba(0,0,0,0); }
table#pixelus td { border:solid 1px #eee; font-size:18px; text-align:center; }
tbody#pixelus_tb td { width:35px; height:35px; color:white; font-weight:bold; cursor:pointer; }
tbody#pixelus_tb span { display: block; height:100%; width: 100%; border-radius: 50px; }
tbody#pixelus_tb td.target { background-color:#87cefa; }
table tbody#pixelus_tb td.wall1 { background-color:#111; cursor:default; }
table tbody#pixelus_tb td.wall2 { background-color:#222; cursor:default; }
table#pixelus tbody#pixelus_tb td.stone span { background-color: #8b4513; }
table#pixelus tbody#pixelus_tb td.valid { background-color:green; }
table#pixelus tbody#pixelus_tb td.invalid { background-color:red; }
</style>
<script>
navigator.mobile = navigator.userAgent.toLowerCase().contains('mobile')
function Pixelus(f_level) {
	// empty constructor
	this.loadLevel(f_level);
	if ( !navigator.mobile ) {
		$('pixelus_tb').onmouseover = function(e, cell) {
			e = new Event(e);
			if ( 'SPAN' == e.target.nodeName && (cell=e.target.parentNode) && !cell.wall && !cell.stone ) {
				cell.addClass(objPixelus.isReachable(cell) ? 'valid' : 'invalid');
			}
		}
		$('pixelus_tb').onmouseout = function(e, cell) {
			e = new Event(e);
			if ( 'SPAN' == e.target.nodeName && (cell=e.target.parentNode) && !cell.wall ) {
				cell.removeClass('valid').removeClass('invalid');
			}
		}
	}
	$('pixelus_tb').onclick = function(e, cell) {
		e = new Event(e);
		if ( 'SPAN' == e.target.nodeName && (cell=e.target.parentNode) && !cell.wall ) {
			if ( !cell.stone ) {
				objPixelus.slingStone(cell);
			}
			else {
				objPixelus.removeStone(cell);
			}
			cell.removeClass('valid').removeClass('invalid');
		}
	}
}
Pixelus.prototype = {
	level : 0,
	stones : 0,
	wrongStones : 0,
	stack : [],
	nextLevel : function() {
		return this.loadLevel(this.level+1);
	},
	prevLevel : function() {
		return this.loadLevel(this.level-1);
	},
	slingStone : function(f_target) {
		if ( 0 < this.stones && !f_target.stone && this.isReachable(f_target) ) {
			f_target.stone = true;
			f_target.addClass('stone');
			this.stones--;
			$('stones_left').innerHTML = this.stones;
			if ( f_target.target ) {
				this.wrongStones--;
			}
			if ( 0 == this.stones && 0 == this.wrongStones ) {
				setTimeout("alert('Level complete!')", 50)
			}
		}
	},
	removeStone : function(f_target) {
		var x = f_target.cellIndex, y = f_target.parentNode.sectionRowIndex;
		if ( f_target.stone && ( this.clearPath([x, y+1], 'd') || this.clearPath([x, y-1], 'u') || this.clearPath([x+1, y], 'r') || this.clearPath([x-1, y], 'l') ) ) {
			f_target.stone = false;
			f_target.removeClass('stone');
			this.stones++;
			$('stones_left').innerHTML = this.stones;
			if ( f_target.target ) {
				this.wrongStones++;
			}
		}
	},
	clearPath : function(f_from, f_dir) {
		var grid = $('pixelus_tb');
		if ( 'd' == f_dir ) {
			for ( var y=f_from[1]; y<grid.rows.length; y++ ) {
				if ( grid.rows[y].cells[f_from[0]].wall || grid.rows[y].cells[f_from[0]].stone ) { return false; }
			}
		}
		else if ( 'u' == f_dir ) {
			for ( var y=f_from[1]; y>=0; y-- ) {
				if ( grid.rows[y].cells[f_from[0]].wall || grid.rows[y].cells[f_from[0]].stone ) { return false; }
			}
		}
		else if ( 'r' == f_dir ) {
			for ( var x=f_from[0]; x<grid.rows[0].cells.length; x++ ) {
				if ( grid.rows[f_from[1]].cells[x].wall || grid.rows[f_from[1]].cells[x].stone ) { return false; }
			}
		}
		else if ( 'l' == f_dir ) {
			for ( var x=f_from[0]; x>=0; x-- ) {
				if ( grid.rows[f_from[1]].cells[x].wall || grid.rows[f_from[1]].cells[x].stone ) { return false; }
			}
		}
		return true;
	},
	isReachable : function(f_target) {
		var x = f_target.cellIndex, y = f_target.parentNode.sectionRowIndex, grid = $('pixelus_tb');
		// check the 4 adjacent cells for wall / stone
		// above
		if ( 0 < y && ( grid.rows[y-1].cells[x].wall || grid.rows[y-1].cells[x].stone ) ) {
			if ( this.clearPath([x, y+1], 'd') ) {
				return true;
			}
		}
		// below
		if ( grid.rows.length-1 > y && ( grid.rows[y+1].cells[x].wall || grid.rows[y+1].cells[x].stone ) ) {
			if ( this.clearPath([x, y-1], 'u') ) {
				return true;
			}
		}
		// left side
		if ( 0 < x && ( grid.rows[y].cells[x-1].wall || grid.rows[y].cells[x-1].stone ) ) {
			if ( this.clearPath([x+1, y], 'r') ) {
				return true;
			}
		}
		// right side
		if ( grid.rows[0].cells.length-1 > x && ( grid.rows[y].cells[x+1].wall || grid.rows[y].cells[x+1].stone ) ) {
			if ( this.clearPath([x-1, y], 'l') ) {
				return true;
			}
		}
		return false;
	},
	loadLevel : function(f_level) {
		var self = this;
		new Ajax('?', {
			data : 'action=get_maps&level=' + f_level,
			onComplete : function(t) {
				try {
					var rv = eval( "(" + t + ")" );
				} catch (e) { alert(t); return; }

				if ( rv.error ) {
					alert(rv.error);
					return;
				}

				// save level
				$('stats_level').innerHTML	= rv.level;
				self.level = rv.level;
				self.stones = rv.stones;
				$('stones_left').innerHTML = self.stones;
				self.wrongStones = self.stones;
				self.stack = [];

				// empty current map
				while ( 0 < $('pixelus_tb').childNodes.length ) {
					$('pixelus_tb').removeChild($('pixelus_tb').firstChild);
				}

				// save map
				$A(rv.map).each(function(row, y) {
					var nr = $('pixelus_tb').insertRow($('pixelus_tb').rows.length);
					for ( var x=0; x<row.length; x++ ) {
						var nc = nr.insertCell(nr.cells.length);
						nc.innerHTML = '<span></span>';
						nc.wall = false;
						nc.target = false;
						nc.stone = false;
						if ( 'x' == row.substr(x, 1) ) {
							nc.className = 'wall' + Math.ceil(2*Math.random());
							nc.wall = true;
						}
						else if ( 'o' == row.substr(x, 1) ) {
							nc.className = 'target';
							nc.target = true;
						}
					}
				});
			}
		}).request();
		return false;
	}
};
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
	<table id="pixelus" border="0">
		<thead>
			<tr><th class="pad" colspan="30">Your name: <span id="your_name">?</span></th></tr>
		</thead>
		<tbody id="pixelus_tb"></tbody>
		<tfoot>
			<tr><th class="pad" colspan="30">Stones left: <span id="stones_left">0</span><br />Moves: <span id="stats_moves">0</span></th></tr>
		</tfoot>
	</table></td>
	<td valign="top" align="left" class="pad">
		<a href="#" onclick="objPixelus.loadLevel(prompt('Map #:', objPixelus.level));return false;">load level #</a><br />
		<br />
		<a href="#" onclick="return objPixelus.prevLevel();">&lt;&lt;</a> &nbsp; <a href="#" onclick="return objPixelus.nextLevel();">&gt;&gt;</a><br />
		<br />
		<a href="#" onclick="objPixelus.loadLevel(objPixelus.level);return false;">restart</a><br />
		<br />
		<a href="?action=reset">reset</a><br />
		<br />
		<a href="#" onclick="objPixelus.changeName(prompt('New name:', $('your_name').innerHTML));return false;">Change name</a><br />
		<br />
	</td>
</tr>
<tr>
	<th colspan="2" class="pad" id="stack_message">no stack messages</th>
</tr>
</table>

<script type="text/javascript">
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
var objPixelus = new Pixelus(1);
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
	$_SESSION[S_NAME]['stones']		= $arrLevel['stones'];

}


