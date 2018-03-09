<?php
// THE BOX
// drunkmenworkhere: Q3, 8iR, rZ9a2, Dxg20aj, Hdf7, K4sU,

require 'inc.functions.php';

define( 'BASEPAGE',	basename($_SERVER['SCRIPT_NAME']) );
if ( 5 > (int)PHP_VERSION ) {
	exit('Sorry, not supported in PHP '.PHP_VERSION.'! Check out <a href="http://games.home.hotblocks.nl/'.BASEPAGE.'">http://games.home.hotblocks.nl</a>');
}

$bShowCoords	= false;
$bDebug			= true;

$iFuelPerWalk		= 1;
$iExtraFuelPerPush	= 2;

define( 'S_NAME', 'bxb_user' );


$_page		= @$_REQUEST['page'];
$_action	= @$_REQUEST['action'];


require_once('140_levels.php');
$eerste_level = key($g_arrLevels);


if ( empty($_SESSION[S_NAME]) ) {
	reset_game($eerste_level);
}


/** RESET **/
if ( 'reset' === $_action ) {
	$_SESSION[S_NAME] = null;
	reset_game($eerste_level);
	header('Location: '.BASEPAGE);
	exit;
}

/** START GAME **/
else if ( 'get_maps' == $_action ) {
	if ( !isset($_REQUEST['level'], $g_arrLevels[$_REQUEST['level']]) ) {
		$iLevel = 0;
	}
	else {
		$iLevel = $_REQUEST['level'];
	}
	$arrLevel = $g_arrLevels[$iLevel];

	reset_game($iLevel);

	exit(json_encode(array(
		'level'		=> $iLevel,
		'map'		=> $arrLevel['map'],
		'pusher'	=> $arrLevel['pusher'],
		'boxes'		=> $arrLevel['boxes'],
	)));
}

/** MOVE **/
else if ( "move" == $_action && isset($_REQUEST['dir'], $_REQUEST['level']) ) {

	if ( !isset($g_arrLevels[$_REQUEST['level']]) ) {
		exit('Invalid level!');
	}
	$arrLevel = $g_arrLevels[$_REQUEST['level']];
	$arrDirs = explode(',', $_REQUEST['dir']);

	foreach ( $arrLevel['map'] AS $szLine ) {
		$arrLine = array();
		for ( $i=0; $i<strlen($szLine); $i++ ) {
			$szField = substr($szLine, $i, 1);
			$arrLine[] = new BoxField( ('x' === $szField), false, ('t' === $szField) );
		}
		$arrMap[] = $arrLine;
	}
	foreach ( $arrLevel['boxes'] AS $arrBoxC ) {
		$arrMap[$arrBoxC[1]][$arrBoxC[0]]->box = true;
	}

	$arrPusher = $arrLevel['pusher'];

	$iMoves = 0;

	for ( $i=0; $i<strlen($_REQUEST['dir']); $i++ ) {
		$szDir = substr($_REQUEST['dir'], $i, 1);
		$iMoves++;
		$dx1 = $dx2 = $dy1 = $dy2 = 0;
		if ( 'l' === $szDir ) {
			$dx1 = -1;
			$dx2 = -2;
		}
		else if ( 'r' === $szDir ) {
			$dx1 = 1;
			$dx2 = 2;
		}
		else if ( 'u' === $szDir ) {
			$dy1 = -1;
			$dy2 = -2;
		}
		else if ( 'd' === $szDir ) {
			$dy1 = 1;
			$dy2 = 2;
		}
		else {
			exit('INVALID DIRECTION: '.$_REQUEST['dir']);
		}

		$nowFieldC = array($arrPusher[0], $arrPusher[1]);
		$toFieldC = array($arrPusher[0]+$dx1, $arrPusher[1]+$dy1);
		$nextFieldC = array($arrPusher[0]+$dx2, $arrPusher[1]+$dy2);

		// TO-FIELD cannot be wall
		$toField =& $arrMap[$toFieldC[1]][$toFieldC[0]];
		if ( $toField->wall ) {
			exit('['.$iMoves.'] TO-FIELD cannot be wall');
		}
		$nextField =& $arrMap[$nextFieldC[1]][$nextFieldC[0]];

		// NEXT-FIELD must be empty
		if ( $toField->box && ( $nextField->box || $nextField->wall ) ) {
			exit('['.$iMoves.'] Can\'t push box with box or wall behind it');
		}

		if ( $toField->box ) {
			$toField->box = false;
			$nextField->box = true;
		}

		$arrPusher = $toFieldC;

		unset($toField, $nextField);
	}

	if ( 0 === CountBadBoxes($arrMap) ) {
		exit('LEVEL '.$_REQUEST['level'].' ACHIEVEMENT SAVED');
	}
	exit('Level is not complete... No errors have occurred!');
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>THE BOX -MULTIPLE TARGETS</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('140.js') ?>"></script>
<link rel="stylesheet" href="<?= html_asset('140.css') ?>" />
<script>
window.onerror = function(e) {
	alert(e);
};
</script>
<script>
r.extend(Coords2D, {
	direction: function() {
		if ( Math.abs(this.y) > Math.abs(this.x) ) {
			return this.y > 0 ? 'down' : 'up';
		}
		return this.x > 0 ? 'right' : 'left';
	},
	distance: function(target) {
		return Math.sqrt(Math.pow(Math.abs(this.x - target.x), 2) + Math.pow(Math.abs(this.y - target.y), 2));
	},
});
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
	<td class="pad" style="padding-top:0;"><table id="thebox" border="0">
		<tbody id="thebox_tbody"></tbody>
		<tfoot>
			<tr><th class="pad" colspan="30">Moves: <span id="stats_moves">0</span></th></tr>
		</tfoot>
	</table></td>
	<td valign="top" align="left" class="pad">
		<a href="#" onclick="objTheBox.LoadAndPrintMap(prompt('Map #:', $('stats_level').innerHTML));return false;">load level #</a><br />
		<br />
		<a href="#" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel-1);return false;">&lt;&lt;</a> &nbsp; <a href="#" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel-(-1));return false;">&gt;&gt;</a><br />
		<br />
		<a href="#" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel);return false;">restart</a><br />
		<br />
		<a href="#" onclick="return objTheBox.UndoLastMove();">undo</a><br />
		<br />
		<a href="?action=reset">reset</a><br />
		<br />
	</td>
</tr>
<tr>
	<th colspan="2" class="pad" id="stack_message">-</th>
</tr>
</table>

<script>
window.on('xhrStart', function(e) {
	$('#loading').css('visibility', 'visible');
});
window.on('xhrDone', function(e) {
	if (r.xhr.busy == 0) {
		$('#loading').css('visibility', 'hidden');
	}
});

var objTheBox = new TheBox();
objTheBox.LoadAndPrintMap( document.location.hash ? document.location.hash.substr(1) : <?php echo (int)$_SESSION[S_NAME]['level']; ?> );

document.on('keydown', function(e) {
	var dir;
	if ( e.code.match(/^Arrow/) ) {
		e.preventDefault();
		dir = e.code.substr(5).toLowerCase();
		objTheBox.Move(dir);
	}
});

var movingStart, movingEnd;
document.on(['mousedown', 'touchstart'], '#thebox_tbody td', function(e) {
	e.preventDefault();
	movingStart = e.pageXY;
});
document.on(['mousemove', 'touchmove'], function(e) {
	e.preventDefault();
	if ( movingStart ) {
		movingEnd = e.pageXY;
	}
});
document.on(['mouseup', 'touchend'], function(e) {
	if ( movingStart && movingEnd ) {
		var distance = movingStart.distance(movingEnd);
		if ( distance > 10 ) {
			var moved = movingEnd.subtract(movingStart);
			var dir = moved.direction();
			objTheBox.Move(dir);
// document.body.append(document.el('pre').setText(dir));
		}
	}
	movingStart = movingEnd = null;
});
</script>
</body>

</html>
<?php

class BoxField {
	var $wall	= false;
	var $box		= false;
	var $target	= false;
	function __construct( $w = null, $b = null, $t = null ) {
		$this->wall = is_bool($w) ? $w : $this->wall;
		$this->box = is_bool($b) ? $b : $this->box;
		$this->target = is_bool($t) ? $t : $this->target;
	}
}

function reset_game( $f_iLevel = 0 ) {
	global $g_arrLevels;
	$arrLevel = $g_arrLevels[$f_iLevel];

	$_SESSION[S_NAME]['play']		= true;
	$_SESSION[S_NAME]['moves']		= 0;
	$_SESSION[S_NAME]['level']		= $f_iLevel;
	$_SESSION[S_NAME]['pusher']		= $arrLevel['pusher'];
	$_SESSION[S_NAME]['map']		= array();
	foreach ( $arrLevel['map'] AS $szLine ) {
		$arrLine = array();
		for ( $i=0; $i<strlen($szLine); $i++ ) {
			$szField = substr($szLine, $i, 1);
			$arrLine[] = new BoxField( ('x' === $szField), false, ('t' === $szField) );
		}
		$_SESSION[S_NAME]['map'][] = $arrLine;
	}
	foreach ( $arrLevel['boxes'] AS $arrBoxC ) {
		$_SESSION[S_NAME]['map'][$arrBoxC[1]][$arrBoxC[0]]->box = true;
	}

}

function countBadBoxes( $f_arrMap = null ) {
	$iBoxes = 0;
	$arrMap = null === $f_arrMap ? $_SESSION[S_NAME]['map'] : $f_arrMap;
	foreach ( $arrMap AS $row ) {
		foreach ( $row AS $cell ) {
			if ( $cell->box && !$cell->target ) {
				$iBoxes++;
			}
		}
	}
	return $iBoxes;
}

?>
