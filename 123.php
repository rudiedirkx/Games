<?php
// STEPPING STONES

session_start();

// require_once("connect.php");
define( "S_NAME", "st_user" );

require_once('steppingstones_levels.php');
$g_iFirstLevel = key($g_arrLevels);


if ( isset($_GET['newlevel'], $_GET['level']) ) {
	if ( !isset($g_arrLevels[$_GET['level']]) ) {
		exit(json_encode(array('error' => 'Invalid level!')));
	}
	$arrLevel = $g_arrLevels[$_GET['level']];
	$arrLevel['level'] = $_GET['level'];
	exit(json_encode($arrLevel));
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
	$arrJumps = (array) $_POST['jumps'];
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
			exit('Invalid jump when ' . $iStones . ' stones left.');
		}
		unset($nowField, $overField, $toField);
	}

	if ( $iStones < $iStartStones/2 ) {
		// mysql_query("INSERT INTO steppingstones (name, score, level, utc) VALUES ('".addslashes($_POST['name'])."', ".$iStones.", '".addslashes($_POST['level'])."', ".time().")");
		exit('Game saved (' . $iStones . ' stones left)!');
	}

	exit($iStones . ' stones left. Not good enough!');
}

?>
<!doctype html>
<html>

<head>
<title>STEPPING STONES</title>
<link rel="stylesheet" type="text/css" href="123.css" />
<script src="js/rjs-custom.js"></script>
<script>
function SteppingStones( f_level ) {
	this.m_szName = 'Anonymous';
	this.m_iLevel = 0;
	this.m_arrJumper = [0, 0];
	this.m_iStones = 0;
	this.m_bGameOver = false;
	this.m_arrStack = [];

	this.loadLevel(f_level);
}
r.extend(SteppingStones, {
	selectStone : function( f_objTD ) {
		if ( f_objTD.available && f_objTD.stone ) {
			// Unselect current stone
			$('#stones_tbody').rows[this.m_arrJumper[1]].cells[this.m_arrJumper[0]].className = 'stone';

			// Select new stone
			f_objTD.className = 'jumper';
			this.m_arrJumper = [f_objTD.cellIndex, f_objTD.parentNode.sectionRowIndex];
		}
		return false;
	},

	jump : function( f_szDir ) {
		var overFieldC = this.coordsByDir( this.m_arrJumper, f_szDir );
		var overField = $('#stones_tbody').rows[overFieldC[1]].cells[overFieldC[0]];
		var toFieldC = this.coordsByDir( overFieldC, f_szDir );
		var toField = $('#stones_tbody').rows[toFieldC[1]].cells[toFieldC[0]];
		if ( overField.stone && !toField.stone && toField.available ) {
			var nowField = $('#stones_tbody').rows[this.m_arrJumper[1]].cells[this.m_arrJumper[0]];
			var nowFieldC = r.copy(this.m_arrJumper);

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
			$('#stats_stonesleft').innerHTML = this.m_iStones;

			// Save order
			this.addToStack(nowFieldC, f_szDir);
			if ( 1 == this.m_iStones ) {
				return this.saveGame();
			}
		}
		return false;
	},

	saveGame : function() {
		if ( this.m_bGameOver || this.m_arrStack.length <= 1 ) return;

		this.m_bGameOver = true;
		var szName = prompt("What's your name?", this.m_szName);
		if ( !szName ) {
			return false;
		}

		this.m_szName = szName;
		$('#your_name').setText(szName);

		var self = this;
		var data = {
			"name": szName,
			"level": this.m_iLevel,
			"jumps": this.m_arrStack
		};
		r.post('', r.serialize(data)).on('done', function(e, t) {
			self.SaveMessage(t);
		});

		this.m_arrStack = [];
		return false;
	},

	addToStack : function( f_arrJumper, f_szDir ) {
		this.m_arrStack.push(f_arrJumper[0] + ':' + f_arrJumper[1] + ':' + f_szDir.substr(0, 1));
	},

	coordsByDir : function( f_arrStart, f_szDir ) {
		var to = r.copy(f_arrStart);
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
		r.get('?newlevel=1&level=' + f_iLevel).on('done', function(e, rv) {
			if ( rv.error ) {
				alert(rv.error);
				return;
			}

			// Save level
			self.m_bGameOver = false;
			self.m_iLevel = rv.level;
			$('#stats_level').innerHTML = rv.level;
			self.m_arrJumper = rv.jumper;
			self.m_arrStack = [];

			// empty current map
			while ( 0 < $('#stones_tbody').childNodes.length ) {
				$('#stones_tbody').removeChild($('#stones_tbody').firstChild);
			}

			// save map
			self.m_iStones = 0;
			r.each(rv.map, function(row, y) {
				var nr = $('#stones_tbody').insertRow($('#stones_tbody').rows.length);
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
			$('#stats_stonesleft').innerHTML = self.m_iStones;
			// show jumper
			$('#stones_tbody').rows[rv.jumper[1]].cells[rv.jumper[0]].className = 'jumper';
		});
		return false;
	},

	changeName : function( f_szNewName ) {
		if ( f_szNewName ) {
			this.m_szName = f_szNewName;
			$('#your_name').setText(f_szNewName);
		}
	},

	SaveMessage : function( f_msg ) {
		$('#stack_message').innerHTML = f_msg;
		$('#stack_message').style.backgroundColor = 'red';
		setTimeout("$('#stack_message').style.backgroundColor = '';", 500);
	}
});
</script>
</head>

<body>
<img id="loading" alt="loading" src="images/loading.gif" />

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

<script>
r.xhr.busy = 0;
window.on('xhrStart', function(e) {
	r.xhr.busy++;
	$('#loading').css('visibility', 'visible');
});
window.on('xhrDone', function(e) {
	if (--r.xhr.busy == 0) {
		$('#loading').css('visibility', 'hidden');
	}
});

var objStones = new SteppingStones('<?php echo $g_iFirstLevel; ?>');

$('#stones_tbody').on('click', function(e) {
	e.preventDefault();

	if ( e.target.nodeName != 'TD' ) return;

	if ( e.target.stone && e.target.className == 'stone' ) {
		objStones.selectStone(e.target);
	}
});

document.on('keydown', function(e) {
	var dirs = {
		"37": 'left',
		"38": 'up',
		"39": 'right',
		"40": 'down',
	};
	if ( !dirs[e.key] ) return;
	var dir = dirs[e.key];

	e.preventDefault();
	objStones.jump(dir);
});
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
