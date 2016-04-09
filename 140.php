<?php
// THE BOX
// drunkmenworkhere: Q3, 8iR, rZ9a2, Dxg20aj, Hdf7, K4sU,

define( 'BASEPAGE',	basename($_SERVER['SCRIPT_NAME']) );
if ( 5 > (int)PHP_VERSION ) {
	exit('Sorry, not supported in PHP '.PHP_VERSION.'! Check out <a href="http://games.home.hotblocks.nl/'.BASEPAGE.'">http://games.home.hotblocks.nl</a>');
}

$bShowCoords	= false;
$bDebug			= true;

$iFuelPerWalk		= 1;
$iExtraFuelPerPush	= 2;

define( 'S_NAME', 'bxb_user' );
session_start();


$_page		= isset($_POST['page'])		? strtolower(trim($_POST['page']))		: ( isset($_GET['page'])	? strtolower(trim($_GET['page']))	: '' );
$_action	= isset($_POST['action'])	? strtolower(trim($_POST['action']))	: ( isset($_GET['action'])	? strtolower(trim($_GET['action']))	: '' );


require_once('140_levels.php');
$eerste_level = key($g_arrLevels);

// $qCustomLevels = mysql_query("SELECT * FROM the_box_multiple_custom_levels ORDER BY id ASC;");
// while ( $arrCLevel = mysql_fetch_assoc($qCustomLevels) ) {
// 	$g_arrLevels['C'.$arrCLevel['id']] = unserialize($arrCLevel['level']);
// }


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

/** CHANGE NAME **/
else if ( isset($_POST['new_name']) ) {
	if ( goede_gebruikersnaam(trim($_POST['new_name'])) ) {
		$_SESSION[S_NAME]['name'] = trim($_POST['new_name']);
	}
	exit(htmlspecialchars($_SESSION[S_NAME]['name']));
}

/** START GAME **/
else if ( 'get_maps' == $_action ) {
	if ( !isset($_POST['level'], $g_arrLevels[$_POST['level']]) ) {
		$iLevel = 0;
//		exit(json_encode(array('error' => 'Invalid level: '.(int)$_POST['level'])));
	}
	else {
		$iLevel = $_POST['level'];
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
else if ( "move" == $_action && isset($_POST['dir'], $_POST['level'], $_POST['name']) ) {

	if ( !isset($g_arrLevels[$_POST['level']]) ) {
		exit('Invalid level!');
	}
	$arrLevel = $g_arrLevels[$_POST['level']];
	$arrDirs = explode(',', $_POST['dir']);

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

	for ( $i=0; $i<strlen($_POST['dir']); $i++ ) {
		$szDir = substr($_POST['dir'], $i, 1);
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
			exit('INVALID DIRECTION: '.$_POST['dir']);
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
		mysql_query("INSERT INTO the_box_multiple (level, name, moves, utc) VALUES ('".addslashes($_POST['level'])."', '".addslashes($_POST['name'])."', ".(int)$iMoves.", ".time().")");
		exit('LEVEL '.$_POST['level'].' ACHIEVEMENT SAVED');
	}
	exit('Level is not complete... No errors have occurred!');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>THE BOX -MULTIPLE TARGETS</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<script type="text/javascript" src="140.js"></script>
<link rel="stylesheet" type="text/css" href="140.css" />
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
		<thead>
			<tr><th class="pad" colspan="30">Your name: <span id="your_name"><?php echo htmlspecialchars($_SESSION[S_NAME]['name']); ?></span></th></tr>
		</thead>
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
		<a href="#" onclick="objTheBox.ChangeName(prompt('New name:', $('your_name').innerHTML));return false;">Change name</a><br />
		<br />
	</td>
</tr>
<tr>
	<th colspan="2" class="pad" id="stack_message">-</th>
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

var objTheBox = new TheBox();
objTheBox.LoadAndPrintMap( document.location.hash ? document.location.hash.substr(1) : <?php echo (int)$_SESSION[S_NAME]['level']; ?> );

document.addEvent('keydown', function(e) {
	e = new Event(e);
	var dir;
	switch ( e.code ) {
		case 37: if ( e.control ) { objTheBox.LoadAndPrintMap(objTheBox.m_iLevel-1);return; } dir = 'left';	break;
		case 38: dir = 'up';	break;
		case 39: if ( e.control ) { objTheBox.LoadAndPrintMap(objTheBox.m_iLevel-(-1));return; } dir = 'right';	break;
		case 40: dir = 'down';	break;
		default: return;			break;
	}
	e.stop();
	objTheBox.Move(dir);
});

document.body.focus();
//-->
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
	if ( empty($_SESSION[S_NAME]['name']) ) {
		$_SESSION[S_NAME]['name'] = 'Anonymous';
	}
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
