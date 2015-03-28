<?php
// BIOSHOCK HACKER

// Tiles: left-to-right, top-to-bottom, left-to-top, left-to-bottom, right-to-top, right-to-bottom

$g_iSize = isset($_GET['size']) && 3 <= (int)$_GET['size'] && 11 >= (int)$_GET['size'] ? (int)$_GET['size'] : 6;

$arrTiles = array();
for ( $i=0; $i<$g_iSize*$g_iSize; $i++ ) {
	$arrTiles[$i] = $t = (string)rand(1, 6);
	if ( ( $t == '1' || $t == '2' ) && 0 == rand(0, 2) ) {
		$arrTiles[$i] .= rand(0, 1) ? 's' : 'f';
	}
}
//print_r($arrTiles);

?>
<!doctype html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
table { border-collapse: separate; border-spacing: 1px; }
table tr > *, table img { padding: 0; border: 0; height: 40px; width: 40px; }
table td { background-color: #add8e6; opacity: 0.7; cursor: pointer; }
table th { text-align: center; font-weight: bold; font-size: 8px; }
table.selected td:not(.selected):not(:hover) { opacity: 0.5; }
table.selected td.selected, table.selected td:hover { opacity: 1.0; /*-webkit-transform: scale(1.1);*/ }
table img { display: block; }
table th.start { background-color: #4169e1; }
table th.target { background-color: yellow; }

table td.flooding { background-color: #4169e1; }
table td.flooded { background-color: #000080; }
</style>
<link rel="shortcut icon" href="favicon.ico" />
</head>

<body>
<?php

function gc( $p2 ) {
	global $g_iSize;
	return !$p2 ? rand(0, ceil($g_iSize/2-1)) : rand(ceil($g_iSize/2-1), $g_iSize-1);
}
// sections = topright, righttop, rightbottom, bottomright, bottomleft, leftbottom, lefttop, topleft
$sections = array(array(gc(1), 0), array($g_iSize-1, gc(0)), array($g_iSize-1, gc(1)), array(gc(1), $g_iSize-1), array(gc(0), $g_iSize-1), array(0, gc(1)), array(0, gc(0)), array(gc(0), 0));
$sides = array('t', 'r', 'r', 'b', 'b', 'l', 'l', 't');
$start = rand(0, 7);
$target = $start-4 < 0 ? $start+4 : $start-4;
$start = array($sections[$start], $sides[$start]);
$target = array($sections[$target], $sides[$target]);

echo '<table id="flow">'."\n";
echo '<tr>'.str_repeat('<th></th>', $g_iSize+2).'</tr>'."\n";
foreach ( $arrTiles AS $n => $t ) {
	if ( 0 == $n%$g_iSize ) {
		echo '<tr>'.'<th></th>';
	}
	echo '<td tile="' . $t . '" style="background-image: url(images/161_t' . $t . '.png);"></td>';
	if ( 0 == ($n+1)%$g_iSize ) {
		echo '<th></th>'.'</tr>'."\n";
	}
}
echo '<tr>'.str_repeat('<th></th>', $g_iSize+2).'</tr>'."\n";
echo '</table>'."\n\n";

//echo '<pre>'."\n".print_r($start, true).print_r($target, true).'</pre>';

?>

<p><input type="button" onclick="doTick();" value="Tick!" /></p>

<script src="js/rjs-custom.js"></script>
<script>
//'use strict';

var g_props = [[], ['l','r'], ['t','b'], ['l','t'], ['l','b'], ['r','t'], ['r','b']];
var g_tile, g_curTile, g_speedy = false;
var flowtbl, stile, ttile, ticker;
function doTick() {
	switch ( g_curTile.stage ) {
		case 1:
			g_curTile.addClass('flooding');
			g_curTile.stage = 2;
		break;
		case 2:
			g_curTile.addClass('flooded');
			g_curTile.stage = 3;
			if ( g_speedy ) {
				doTick();
			}
		break;
		case 3:
			doNextStep();
		break;
	}
}
function doNextStep() {
	var te = tileExit(g_curTile, g_curTile.entry);
//console.debug(te);
	var nt = nextTile(g_curTile, te);
console.debug(nt);
	if ( false === nt ) {
		clearInterval(ticker);
		alert('Invalid connection!');
		// document.location.reload();
		return false;
	}
	else if ( true === nt ) {
		clearInterval(ticker);
		alert('Excellent!');
		// document.location.reload();
		return false;
	}
	g_curTile = nt;
	g_curTile.stage = 1;
	return true;
}
function tileExit(tile, start) {
	if ( tile.exit ) {
		return tile.exit;
	}
	var i = tile.connectors.indexOf(start);
//console.debug('tileExit(): start = '+start+', index = '+i);
	var te = -1 != i ? tile.connectors[0 == i ? 1 : 0] : false;
//console.debug('tileExit(): exit = '+te);
	return te;
}
function nextTile(tile) {
	side = tile.exit;
	var dd = {'r' : [0, 1], 'l' : [0, -1], 't' : [-1, 0], 'b' : [1, 0]};
	var dx = dd[side][0],
		dy = dd[side][1];
	var cy = tile.cellIndex,
		cx = tile.parentNode.sectionRowIndex;
	var ntile = flowtbl.rows[cx+dx].cells[cy+dy];
// console.log('ntile', ntile);
	if ( undefined === ntile.connectors ) {
		return ntile.target === true;
	}
	var nxtTileEntry = oppositeSide(side);
	if ( -1 != ntile.connectors.indexOf(nxtTileEntry) ) {
		ntile.entry = nxtTileEntry;
		ntile.exit = tileExit(ntile, ntile.entry);
		return ntile;
	}
	return false;
}
function oppositeSide(side) {
	switch ( side ) {
		case 't':
			return 'b';
		case 'b':
			return 't';
		case 'l':
			return 'r';
		case 'r':
			return 'l';
	}
	return null;
}
function doSwitch(b) {
	var a = g_tile;
	unselect();
	a = a.parentNode.replaceChild(b.cloneNode(false), a);
	b.parentNode.replaceChild(a, b);
}
function select(td) {
	g_tile = td;
	flowtbl.classList.add('selected');
	g_tile.classList.add('selected');
}
function unselect() {
	if ( g_tile ) {
		g_tile.classList.remove('selected');
		flowtbl.classList.remove('selected');
		g_tile = null;
	}
}

(function() {
	// Tile properties
	$$('#flow td').each(function(td) {
		td.tile = parseInt(td.attr('tile'));
		td.connectors = g_props[td.tile];
		td.flooded = false;
		td.entry = td.exit = false;
	});

	// Start
	flowtbl = $('flow');
	var start = { x : <?php echo $start[0][0]+1 + ( $start[1] == 'l' ? -1 : ( $start[1] == 'r' ? 1 : 0 ) ); ?> , y : <?php echo $start[0][1]+1 + ( $start[1] == 't' ? -1 : ( $start[1] == 'b' ? 1 : 0 ) ); ?> }, target = { x : <?php echo $target[0][0]+1 + ( $target[1] == 'l' ? -1 : ( $target[1] == 'r' ? 1 : 0 ) ); ?> , y : <?php echo $target[0][1]+1 + ( $target[1] == 't' ? -1 : ( $target[1] == 'b' ? 1 : 0 ) ); ?> };
	with ( flowtbl.rows[start.y].cells[start.x] ) {
		innerHTML = '<img src="images/161_t<?php echo $start[1]; ?>.png" />';
		className = 'start';
		title = 'START';
	}
	stile = $('#flow th.start', 1);
	stile.start = true;
	stile.exit = oppositeSide('<?php echo $start[1]; ?>');
	stile.stage = 2;
	g_curTile = stile;

	// Target
	with ( flowtbl.rows[target.y].cells[target.x] ) {
		innerHTML = '<img src="images/161_t<?php echo $target[1]; ?>.png" />';
		className = 'target';
		title = 'TARGET';
	}
	ttile = $('#flow th.target', 1);
	ttile.target = true;
	ttile.entry = oppositeSide('<?php echo $target[1]; ?>');

	flowtbl.on('click', function(e) {
		e.preventDefault();
		if ( 'TD' == e.target.nodeName ) {
//			if ( 't' === e.target.parentNode.className && 0 !== e.target.tile && !e.target.flooded ) {
				if ( g_tile === e.target ) {
					unselect();
				}
				else if ( !g_tile ) {
					select(e.target);
				}
				else {
					doSwitch(e.target);
				}
//			}
		}
	});
	flowtbl.on('contextmenu', function(e) {
		// e.preventDefault();
		unselect();
	});

	// setTimeout("ticker = setInterval(\"doTick();\", 1500);", 1000);
})();
</script>
</body>

</html>
