<?php
// Tetravex

if ( isset($_GET['image']) ) {
	if ( 4 != strlen((string)$_GET['image']) || !preg_match('/^[0-9]{4}$/', (string)$_GET['image']) ) {
		exit('Invalid tile!');
	}

	// black, brown, red, orange, yellow, lime, blue, purple, gray, white
	$g_arrColors = array('0,0,0', '139,69,19', '255,0,0', '255,140,0', '255,255,0', '50,205,50', '70,130,180', '160,32,240', '190,190,190', '255,255,255');

	$arrNumbers = array(
		array('bgpos' => array( 0,0, 40,0, 20,20, 0,0 ), 'txtpos' => array( 18,3 )),
		array('bgpos' => array( 0,0, 0,40, 20,20, 0,0 ), 'txtpos' => array( 5, 13 )),
		array('bgpos' => array( 40,0, 20,20, 40,40, 40,0 ), 'txtpos' => array( 29,13 )),
		array('bgpos' => array( 0,40, 20,20, 40,40, 0,40 ), 'txtpos' => array( 18,25 )),
	);
	$img = imagecreatetruecolor(40, 40);
	foreach ( $arrNumbers AS $k => $v ) {
		$n = (int)substr($_GET['image'], $k, 1);
		$bgc = explode(',', $g_arrColors[$n]);
		imagefilledpolygon($img, $v['bgpos'], 4, imagecolorallocate($img, $bgc[0], $bgc[1], $bgc[2]));
		$tc = explode(',', (1 >= $n ? $g_arrColors[9] : $g_arrColors[0]));
		imagestring($img, 3, $v['txtpos'][0], $v['txtpos'][1], (string)$n, imagecolorallocate($img, $tc[0], $tc[1], $tc[2]));
	}
	// topleft to bottomright
	imageline($img, 0,0, 39,39, $m=imagecolorallocate($img, 153, 153, 153));
	imageline($img, 0,1, 38,39, $d=imagecolorallocate($img, 102, 102, 102));
	imageline($img, 1,0, 39,38, $l=imagecolorallocate($img, 204, 204, 204));
	// topright to bottomleft
	imageline($img, 39,0, 0,39, $m);
	imageline($img, 38,0, 0,38, $d);
	imageline($img, 39,1, 1,39, $l);
	header('Content-type: image/png');
	imagepng($img);
	imagedestroy($img);
	exit;
}

session_start();
define( 'S_NAME', 'tvex' );

$g_iSize = isset($_GET['size']) && 2 <= (int)$_GET['size'] && 6 >= (int)$_GET['size'] ? (int)$_GET['size'] : 3;

if ( isset($_POST['solution']) ) {
	$arrTiles = explode(',', $_POST['solution']);
	if ( pow($g_iSize, 2) != count($arrTiles) ) {
		exit('Invalid solution format!');
	}
	$fs = isset($_SESSION[S_NAME]['board']) ? (array)$_SESSION[S_NAME]['board'] : array();
	$fu = $arrTiles;
	sort($fs);
	sort($fu);
	if ( !isset($_SESSION[S_NAME]['starttime']) || $fs !== $fu ) {
		exit('Invalid solution content!');
	}

	foreach ( $arrTiles AS $n => $t ) {
		// Top
		if ( isset($arrTiles[$n-$g_iSize]) && $arrTiles[$n-$g_iSize]{3} != $t{0} ) {
			exit('Invalid solution. Error in tile # '.$n);
		}
		// Left
		if ( isset($arrTiles[$n-1]) && 0 != $n%$g_iSize && $arrTiles[$n-1]{2} != $t{1} ) {
			exit('Invalid solution. Error in tile # '.$n);
		}
	}

	$sec = time()-$_SESSION[S_NAME]['starttime'];
	$min = floor($sec/60);
	$sec -= $min*60;
	$szTime = str_pad((string)$min, 2, '0', 0).':'.str_pad((string)$sec, 2, '0', 0);
	$_SESSION[S_NAME] = array();
	exit('Congratulations! You finished a size '.$g_iSize.' board in '.$szTime.'.');
}

$arrEmptyBoard = array_fill(0, pow($g_iSize, 2), '');
$arrBoard = getBoardArray($g_iSize);
shuffle($arrBoard);
$_SESSION[S_NAME]['board'] = $arrBoard;
$_SESSION[S_NAME]['starttime'] = time();

?>
<html>

<head>
<title>Tetravex</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<style type="text/css">
#tetracont table {
	border-collapse	: collapse;
/*	border			: solid 1px #000;*/
}
#tetracont table td {
	border			: solid 1px #eee;
	padding			: 0;
}
#tetracont table td img {
	width			: 40px;
	height			: 40px;
	cursor			: pointer;
}
</style>
</head>

<body>
<table id="tetracont" cellpadding="5">
<tr>
	<th><select onchange="if(this.value){document.location='?size='+this.value;}"><option value="">--</option><?php foreach ( range(2,6) AS $iSize ) { echo '<option'.( $g_iSize === $iSize ? ' selected="1"' : '' ).' value="'.$iSize.'">Size '.$iSize.' board</option>'; } ?></select></th>
	<th colspan="2" id="playtime">00:00</th>
</tr>
<tr>
	<td><?php echo getBoard($arrEmptyBoard, $g_iSize, 'solution'); ?></td>
	<td><?php echo getBoard($arrBoard, $g_iSize, 'available'); ?></td>
</tr>
<tr>
	<td align="center"><input type="button" value="Check ^" onclick="return checkSolution('solution');" /></td>
	<td align="center"><input type="button" value="Check ^" onclick="return checkSolution('available');" /></td>
</tr>
</table>

<script type="text/javascript">
<!--//
var g_iStartTime = Math.floor($time()/1000), g_bDoCheck = true, g_selected = null, op0 = 1, op1 = 0.5, g_iSize = <?php echo $g_iSize; ?>;
function shiftTiles(to) {
	switch ( to ) {
		case 'left':
			for ( var i=0; i<g_iSize; i++ ) {
				if ( $('solution').rows[i].cells[0].getElementsByTagName('img')[0].tile ) {
//					alert('Can\'t move to the left!');
					return;
				}
			}
			for ( var x=1; x<g_iSize; x++ ) {
				for ( var i=0; i<g_iSize; i++ ) {
					var tile = $('solution').rows[i].cells[x].getElementsByTagName('img')[0];
					var ttile = $('solution').rows[i].cells[x-1].getElementsByTagName('img')[0];
					ttile.tile = tile.tile;
					ttile.uniq = tile.uniq;
					tile.tile = tile.uniq = '';
				}
			}
			retile();
		break;
		case 'right':
			for ( var i=0; i<g_iSize; i++ ) {
				if ( $('solution').rows[i].cells[g_iSize-1].getElementsByTagName('img')[0].tile ) {
//					alert('Can\'t move to the right!');
					return;
				}
			}
			for ( var x=g_iSize-2; x>=0; x-- ) {
				for ( var i=0; i<g_iSize; i++ ) {
					var tile = $('solution').rows[i].cells[x].getElementsByTagName('img')[0];
					var ttile = $('solution').rows[i].cells[x+1].getElementsByTagName('img')[0];
					ttile.tile = tile.tile;
					ttile.uniq = tile.uniq;
					tile.tile = tile.uniq = '';
				}
			}
			retile();
		break;
		case 'up':
			for ( var i=0; i<g_iSize; i++ ) {
				if ( $('solution').rows[0].cells[i].getElementsByTagName('img')[0].tile ) {
//					alert('Can\'t move up!');
					return;
				}
			}
			for ( var y=1; y<g_iSize; y++ ) {
				for ( var i=0; i<g_iSize; i++ ) {
					var tile = $('solution').rows[y].cells[i].getElementsByTagName('img')[0];
					var ttile = $('solution').rows[y-1].cells[i].getElementsByTagName('img')[0];
					ttile.tile = tile.tile;
					ttile.uniq = tile.uniq;
					tile.tile = tile.uniq = '';
				}
			}
			retile();
		break;
		case 'down':
			for ( var i=0; i<g_iSize; i++ ) {
				if ( $('solution').rows[g_iSize-1].cells[i].getElementsByTagName('img')[0].tile ) {
//					alert('Can\'t move down!');
					return;
				}
			}
			for ( var y=g_iSize-2; y>=0; y-- ) {
				for ( var i=0; i<g_iSize; i++ ) {
					var tile = $('solution').rows[y].cells[i].getElementsByTagName('img')[0];
					var ttile = $('solution').rows[y+1].cells[i].getElementsByTagName('img')[0];
					ttile.tile = tile.tile;
					ttile.uniq = tile.uniq;
					tile.tile = tile.uniq = '';
				}
			}
			retile();
		break;
	}
}
function updatePlaytime() {
	var sec = Math.floor($time()/1000) - g_iStartTime, min = Math.floor(sec/60);
	sec -= min*60;
	$('playtime').innerHTML = ( 10 > min ? '0'+min : min ) + ':' + ( 10 > sec ? '0'+sec : sec );
}
setInterval(updatePlaytime, 200);
function checkSolution(tbl) {
	tbl = $(tbl);
	var s = '';
	tbl.getElements('img').each(function(tile) {
		if ( tile.tile ) {
			s += ',' + tile.tile;
		}
	});
	new Ajax('?size='+g_iSize, {
		data : 'solution=' + s.substr(1),
		onComplete : function(t) {
			alert(t);
		}
	}).request();
	return false;
}
function retile(ft) {
	$$('#tetracont img').each(function(tile) {
		if ( ft ) {
			tile.tile = tile.getAttribute('tile');
			tile.uniq = tile.tile ? (''+Math.random()+'').replace(/\./, '') : '';
			tile.n = tile.firstParent('TD').cellIndex + g_iSize * tile.firstParent('TR').sectionRowIndex;
		}
		tile.setOpacity(op0);
		tile.src = tile.tile ? '?image='+tile.tile : '/icons/blank.gif';
	});
	return false;
}
retile(true);
function getTile(n) {
	var t = $$('#solution img')[n];
	return t && t.tile ? t : false;
}
function moveTile(move, to) {
	var ok = false;
	if ( g_bDoCheck && 'solution' == $(to).firstParent('TABLE').id ) {
		var iTo = to.n;
		// Above &to
		var t = getTile(iTo-g_iSize);
		if ( t && t.uniq != move.uniq && t.tile.substr(3, 1) != move.tile.substr(0, 1) ) {
			ok = 'above';
		}
		// Left of &to
		var t = getTile(iTo-1);
		if ( t && 0 != to.n%g_iSize && t.uniq != move.uniq && t.tile.substr(2, 1) != move.tile.substr(1, 1) ) {
			ok = 'left';
		}
		// Right of &to
		var t = getTile(iTo+1);
		if ( t && 0 != (to.n+1)%g_iSize && t.uniq != move.uniq && t.tile.substr(1, 1) != move.tile.substr(2, 1) ) {
			ok = 'right';
		}
		// Below &to
		var t = getTile(iTo+g_iSize);
		if ( t && t.uniq != move.uniq && t.tile.substr(0, 1) != move.tile.substr(3, 1) ) {
			ok = 'below';
		}
	}
	if ( !ok ) {
		to.tile = move.tile;
		to.uniq = move.uniq;
		move.tile = '';
		move.uniq = '';
		g_selected = null;
		retile();
	}
	return false;
}
//document.onmousedown = document.onselectstart = function(e){return false;}
document.onclick = function(e) {
	e = new Event(e).stop();
	if ( e.target.nodeName != 'IMG' ) { return false; }
	if ( e.target.tile ) {
		if ( g_selected ) {
			$(g_selected).setOpacity(op0);
			if ( g_selected != e.target ) {
				g_selected = e.target;
				$(g_selected).setOpacity(op1);
			}
			else { g_selected = null; }
		}
		else {
			g_selected = e.target;
			$(g_selected).setOpacity(op1);
		}
	}
	else if ( g_selected ) {
		moveTile(g_selected, e.target);
	}
	return false;
}
document.onkeyup = function(e) {
	e = new Event(e);
	switch ( e.code ) {
		case 38: shiftTiles('up'); break;
		case 40: shiftTiles('down'); break;
		case 37: shiftTiles('left'); break;
		case 39: shiftTiles('right'); break;
	}
}
//-->
</script>

</body>

</html>
<?php

function getBoard($a, $s, $id) {
	$szHtml = '<table id="'.$id.'">';
	foreach ( $a AS $k => $t ) {
		if ( 0 == $k%$s ) { $szHtml .= '<tr>'; }
		$szHtml .= '<td>'.getTile($t).'</td>';
		if ( 0 == ($k+1)%$s ) { $szHtml .= '</tr>'; }
	}
	$szHtml .= '</table>';
	return $szHtml;
}

function getTile($t) {
	return '<img tile="'.$t.'" />';
}

function getBoardArray($s) {
	$arrBoard = array_fill(0, pow($s, 2), '');
	foreach ( $arrBoard AS $k => &$f ) {
		$t = empty($arrBoard[$k-$s]) ? (string)rand(0, 9) : substr($arrBoard[$k-$s], 3, 1);
		$l = empty($arrBoard[$k-1]) ? (string)rand(0, 9) : substr($arrBoard[$k-1], 2, 1);
		$r = (string)rand(0, 9);
		$b = (string)rand(0, 9);
		$f = $t . $l . $r . $b;
		unset($f);
	}
	return $arrBoard;
}

?>