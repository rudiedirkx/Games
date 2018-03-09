<?php

$bShowCoords	= false;
$bDebug			= true;

define( 'S_NAME', 'bx_user' );
define( 'BASEPAGE',	basename($_SERVER['SCRIPT_NAME']) );


$_page		= @$_REQUEST['page'];
$_action	= @$_REQUEST['action'];


require_once('139_levels.php');
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
else if ( 'get_maps' === $_action ) {
	if ( !isset($_REQUEST['level'], $g_arrLevels[(int)$_REQUEST['level']]) ) {
		exit(json_encode(array('error' => true)));
	}
	$arrLevel = $g_arrLevels[(int)$_REQUEST['level']];

	reset_game((int)$_REQUEST['level']);

	exit(json_encode(array(
		'level'		=> $_SESSION[S_NAME]['level'],
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
			exit("TO-FIELD cannot be wall");
		}
		$nextField =& $arrMap[$nextFieldC[1]][$nextFieldC[0]];

		// NEXT-FIELD must be empty
		if ( $toField->box && ( $nextField->box || $nextField->wall ) ) {
			exit("Can't push box with box or wall behind it");
		}

		if ( $toField->box ) {
			$toField->box = false;
			if ( !$nextField->target ) {
				$nextField->box = true;
			}
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>THE BOX -ONE TARGET</title>
<script src="js/rjs-custom.js"></script>
<script src="139.js"></script>
<link rel="stylesheet" href="139.css" />
</head>

<body>
<img id="loading" alt="loading" src="images/loading.gif" />

<table border="1" cellpadding="15" cellspacing="0">
<tr>
	<th class="pad">LEVEL <span id="stats_level">0</span></th>
	<td></td>
</tr>
<tr>
	<td class="pad" style="padding-top:0;">
	<table id="thebox" border="0">
		<tbody id="thebox_tbody"></tbody>
		<tfoot>
			<tr><th class="pad" colspan="30">Energy spent: <span id="stats_moves">0</span></th></tr>
		</tfoot>
	</table></td>
	<td valign="top" align="left" class="pad">
		<a href="#" onclick="objTheBox.LoadAndPrintMap(prompt('Map #:', $('stats_level').innerHTML));return false;">load level #</a><br />
		<br />
		<a href="#" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel-1);return false;">&lt;&lt;</a> &nbsp; <a href="#" onclick="objTheBox.LoadAndPrintMap(objTheBox.m_iLevel+1);return false;">&gt;&gt;</a><br />
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
	<th colspan="2" class="pad" id="stack_message">&nbsp;</th>
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

var objTheBox = new TheBox;
objTheBox.LoadAndPrintMap( document.location.hash ? document.location.hash.substr(1) : <?php echo (int)$_SESSION[S_NAME]['level']; ?> );

document.on('keydown', function(e) {
	var dir;
	if ( e.code.match(/^Arrow/) ) {
		e.preventDefault();
		dir = e.code.substr(5).toLowerCase();
		objTheBox.Move(dir);
	}
});
</script>
</body>

</html>
<?php

class BoxField {
	public $wall	= false;
	public $box		= false;
	public $target	= false;
	public function __construct( $w = null, $b = null, $t = null ) {
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

function countBadBoxes( $f_arrMap ) {
	$iBoxes = 0;
	foreach ( $f_arrMap AS $row ) {
		foreach ( $row AS $cell ) {
			if ( $cell->box ) {
				$iBoxes++;
			}
		}
	}
	return $iBoxes;
}

?>
