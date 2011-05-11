<?php
// STEPPING STONES

session_start();

require_once("connect.php");
define( "S_NAME", "st_user" );

require_once('inc.cls.json.php');

require_once('steppingstones_levels.php');
$g_iFirstLevel = key($g_arrLevels);


if ( isset($_POST['newlevel'], $_POST['level']) ) {
	if ( !isset($g_arrLevels[$_POST['level']]) ) {
		exit(json::encode(array('error' => 'Invalid level!')));
	}
	$arrLevel = $g_arrLevels[$_POST['level']];
	$arrLevel['level'] = $_POST['level'];
	exit(json::encode($arrLevel));
}

else if ( isset($_POST['new_name']) ) {
	exit(htmlspecialchars($_POST['new_name']));
}

else if ( isset($_POST['name'], $_POST['level'], $_POST['jumps']) ) {
	if ( !isset($g_arrLevels[$_POST['level']]) ) {
		exit('Invalid level!');
	}
	$iStones = 0;
	$arrMap = array();
	foreach ( $g_arrLevels[$_POST['level']]['map'] AS $y => $szLine ) {
		for ( $x=0; $x<strlen($szLine); $x++ ) {
			switch ( substr($szLine, $x, 1) ) {
				case 'o': $arrMap[$x][$y] = (object)array('available' => true, 'stone' => false); break;
				case 's': $iStones++; $arrMap[$x][$y] = (object)array('available' => true, 'stone' => true); break;
				default: $arrMap[$x][$y] = (object)array('available' => false, 'stone' => false); break;
			}
		}
	}
	$iStartStones = $iStones;
	$arrJumps = explode(',', $_POST['jumps']);
	foreach ( $arrJumps AS $szJump ) {
		$x = explode(':', $szJump);
		$dir = array_pop($x);
		$nowField =& $arrMap[$x[0]][$x[1]];
		$overFieldC = coordsByDir( $x, $dir );
		$overField =& $arrMap[$overFieldC[0]][$overFieldC[1]];
		$toFieldC = coordsByDir( $overFieldC, $dir );
		$toField =& $arrMap[$toFieldC[0]][$toFieldC[1]];
		if ( $overField->stone && !$toField->stone && $toField->available ) {
			// nowField is stoneless
			$nowField->stone = false;
			// overField is stoneless
			$overField->stone = false;
			// toField is stone and jumper
			$toField->stone = true;
			$iStones--;
		}
		else {
			exit('Invalid jump when '.$iStones.' stones left.');
		}
		unset($nowField, $overField, $toField);
	}
	if ( $iStones < $iStartStones/2 ) {
		mysql_query("INSERT INTO steppingstones (name, score, level, utc) VALUES ('".addslashes($_POST['name'])."', ".$iStones.", '".addslashes($_POST['level'])."', ".time().")");
		exit('Game saved ('.$iStones.' stones left)!');
	}
	exit($iStones.' stones left. Not good enough!');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>STEPPING STONES</title>
<link rel="stylesheet" type="text/css" href="123.css" />
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<script type="text/javascript">
<!--//
function SteppingStones( f_level ) {
	this.m_szName = 'Anonymous';
	this.m_iLevel = 0;
	this.m_arrJumper = [0, 0];
	this.m_iStones = 0;
	this.m_bGameOver = false;
	this.m_arrStack = [];

	this.loadLevel(f_level);
}

SteppingStones.prototype = {
	selectStone : function( f_objTD ) {
		if ( f_objTD.available && f_objTD.stone ) {
			// Unselect current stone
			$('stones_tbody').rows[this.m_arrJumper[1]].cells[this.m_arrJumper[0]].className = 'stone';
			// Select new stone
			f_objTD.className = 'jumper';
			this.m_arrJumper = [f_objTD.cellIndex, f_objTD.parentNode.sectionRowIndex];
		}
		return false;
	},

	jump : function( f_szDir ) {
		var overFieldC = this.coordsByDir( this.m_arrJumper, f_szDir );
		var overField = $('stones_tbody').rows[overFieldC[1]].cells[overFieldC[0]];
		var toFieldC = this.coordsByDir( overFieldC, f_szDir );
		var toField = $('stones_tbody').rows[toFieldC[1]].cells[toFieldC[0]];
		if ( overField.stone && !toField.stone && toField.available ) {
			var nowField = $('stones_tbody').rows[this.m_arrJumper[1]].cells[this.m_arrJumper[0]];
			var nowFieldC = this.m_arrJumper.clone();
			// nowField is stoneless
			nowField.className = 'no-stone';
			nowField.stone = false;
			// overField is stoneless
			overField.stone = false;
			overField.className = 'no-stone';
			// toField is stone and jumper
			this.m_arrJumper = toFieldC;
			toField.stone = true;
			toField.className = 'jumper';
			this.m_iStones--;
			$('stats_stonesleft').innerHTML = this.m_iStones;
			// Save order
			this.addToStack(nowFieldC, f_szDir);
			if ( 1 == this.m_iStones ) {
				return this.saveGame();
			}
		}
		return false;
	},

	saveGame : function() {
		if ( this.m_bGameOver || 1 >= this.m_arrStack.length ) { return false; }
		this.m_bGameOver = true;
		var szName = prompt('What\'s your name?', this.m_szName);
		if ( !szName ) {
			return false;
		}
		this.m_szName = szName;
		$('your_name').innerHTML = szName;
		var self = this;
		new Ajax('?', {
			data : 'name=' + encodeURIComponent(szName) + '&level=' + this.m_iLevel + '&jumps=' + this.m_arrStack.join(','),
			onComplete : function(t) {
				self.SaveMessage(t);
			}
		}).request();
		this.m_arrStack = [];
		return false;
	},

	addToStack : function( f_arrJumper, f_szDir ) {
		this.m_arrStack.push(f_arrJumper[0] + ':' + f_arrJumper[1] + ':' + f_szDir.substr(0, 1));
	},

	coordsByDir : function( f_arrStart, f_szDir ) {
		var to = f_arrStart.clone();
		switch ( f_szDir.substr(0, 1) ) {
			case 'u': to[1]--; break;
			case 'd': to[1]++; break;
			case 'l': to[0]--; break;
			case 'r': to[0]++; break;
		}
		return to;
	},

	loadLevel : function( f_iLevel ) {
		var self = this;
		new Ajax('?', {
			data : 'newlevel=1&level=' + f_iLevel,
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
				// Save level
				self.m_bGameOver = false;
				self.m_iLevel = rv.level;
				$('stats_level').innerHTML = rv.level;
				self.m_arrJumper = rv.jumper;
				self.m_arrStack = [];
				// empty current map
				while ( 0 < $('stones_tbody').childNodes.length ) {
					$('stones_tbody').removeChild($('stones_tbody').firstChild);
				}
				// save map
				self.m_iStones = 0;
				$A(rv.map).each(function(row, y) {
					var nr = $('stones_tbody').insertRow($('stones_tbody').rows.length);
					for ( var x=0; x<row.length; x++ ) {
						var nc = nr.insertCell(nr.cells.length);
						nc.innerHTML = '';
						if ( 'o' == row.substr(x, 1) ) {
							nc.className = 'no-stone';
							nc.available = true;
						}
						else if ( 's' == row.substr(x, 1) ) {
							nc.className = 'stone';
							nc.available = true;
							nc.stone = true;
							self.m_iStones++;
						}
						else {
							nc.className = '';
						}
					}
				});
				$('stats_stonesleft').innerHTML = self.m_iStones;
				// show jumper
				$('stones_tbody').rows[rv.jumper[1]].cells[rv.jumper[0]].className = 'jumper';
			}
		}).request();
		return false;
	},

	changeName : function( f_szNewName ) {
		if ( f_szNewName ) {
			this.m_szName = f_szNewName;
			$('your_name').innerHTML = f_szNewName;
		}
	},

	SaveMessage : function( f_msg ) {
		$('stack_message').innerHTML = f_msg;
		$('stack_message').style.backgroundColor = 'red';
		setTimeout("$('stack_message').style.backgroundColor = '';", 500);
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
	<th class="pad">LEVEL <span id="stats_level">0</span></td>
	<td></td>
</tr>
<tr>
	<td class="pad"><table id="stones" border="0">
		<thead>
			<tr><th class="pad" colspan="30">Your name: <span id="your_name">?</span></th></tr>
		</thead>
		<tbody id="stones_tbody"></tbody>
		<tfoot>
			<tr><th class="pad" colspan="30">Stones left: <span id="stats_stonesleft">0</span></th></tr>
		</tfoot>
	</table></td>
	<td valign="top" class="pad">
		<a href="#" onclick="objStones.loadLevel(prompt('Map #:', objStones.m_iLevel));return false;">load level #</a><br />
		<br />
		<a href="#" onclick="return objStones.loadLevel(objStones.m_iLevel-1);">&lt;&lt;</a> &nbsp; <a href="#" onclick="return objStones.loadLevel(objStones.m_iLevel-(-1));">&gt;&gt;</a><br />
		<br />
		<a href="#" onclick="objStones.loadLevel(objStones.m_iLevel);return false;">restart</a><br />
		<br />
		<a href="#" onclick="return objStones.saveGame();">SAVE</a><br />
		<br />
		<a href="?action=reset">reset</a><br />
		<br />
		<a href="#" onclick="return objStones.changeName(prompt('New name:', objStones.m_szName));">Change name</a><br />
		<br />
	</td>
</tr>
<tr>
	<th colspan="2" class="pad" id="stack_message">-</th>
</tr>
</table>

<script type="text/javascript">
<!--//
var objStones = new SteppingStones('<?php echo $g_iFirstLevel; ?>');

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

$('stones_tbody').addEvent('click', function(e) {
	e = new Event(e).stop();
	if ( 'TD' !== e.target.nodeName ) { return false; }
	if ( e.target.stone && 'stone' === e.target.className ) {
		objStones.selectStone(e.target);
	}
});

document.addEvent('keydown', function(e) {
	e = new Event(e);
	var dir;
	switch ( e.code ) {
		case 37:	dir = 'left';	break;
		case 38:	dir = 'up';		break;
		case 39:	dir = 'right';	break;
		case 40:	dir = 'down';	break;
		default:	return;			break;
	}
	e.stop();
	objStones.jump(dir);
});
//-->
</script>
</body>

</html>
<?php

function coordsByDir( $f_start, $f_dir ) {
	$to = $f_start;
	switch ( substr($f_dir, 0, 1) ) {
		case 'u': $to[1]--; break;
		case 'd': $to[1]++; break;
		case 'l': $to[0]--; break;
		case 'r': $to[0]++; break;
	}
	return $to;
}

?>