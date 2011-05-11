<?php
// Maze

$iUtcStart = microtime(true);

$x = 40;
$y = 20;

$arrMaze = array_fill(0, $x*$y, '01111');
$arrMoves = array();

$iStart = rand(0, $x*$y-1);
$iPosition = $iStart;

?>
<style type="text/css">
table { border:solid 2px #000;border-width:2px 0 0 2px; }
td { border:solid 2px #000;border-width:0;width:30px;height:30px;text-align:center; }
.w01 { border-width:0 0 2px 0; }
.w10 { border-width:0 2px 0 0; }
.w11 { border-width:0 2px 2px 0; }
.pos { font-weight:bold;color:red; }
.d0 { background-color:#eee; }
.strt { font-weight:bold;color:blue; }
</style>
<?php

$arrMaze[$iPosition]{0} = '1';
$iDiscovered = 1;

while ( $iDiscovered < $x*$y ) {
	$dirs = '';
	if ( isset($arrMaze[$iPosition-$x]) && '0' === $arrMaze[$iPosition-$x]{0} ) {
		$dirs .= 'U';
	}
	if ( isset($arrMaze[$iPosition-1]) && 0 != $iPosition%$x && '0' === $arrMaze[$iPosition-1]{0} ) {
		$dirs .= 'L';
	}
	if ( isset($arrMaze[$iPosition+1]) && 0 != ($iPosition+1)%$x && '0' === $arrMaze[$iPosition+1]{0} ) {
		$dirs .= 'R';
	}
	if ( isset($arrMaze[$iPosition+$x]) && '0' === $arrMaze[$iPosition+$x]{0} ) {
		$dirs .= 'D';
	}
	if ( !$dirs ) {
//		break;
		$iPosition = array_pop($arrMoves);
//		echo 'No possible directions. NP: '.$iPosition;
	}
	else {
		array_push($arrMoves, $iPosition);
		$dir = $dirs{rand(0, strlen($dirs)-1)};
//		echo 'Possible directions: ['.$dirs.'] -> '.$dir.'<br />';
		switch ( $dir ) {
			case 'U':
				$arrMaze[$iPosition]{1} = $arrMaze[$iPosition-$x]{4} = '0';
				$iPosition -= $x;
			break;
			case 'L':
				$arrMaze[$iPosition]{2} = $arrMaze[$iPosition-1]{3} = '0';
				$iPosition -= 1;
			break;
			case 'R':
				$arrMaze[$iPosition]{3} = $arrMaze[$iPosition+1]{2} = '0';
				$iPosition += 1;
			break;
			case 'D':
				$arrMaze[$iPosition]{4} = $arrMaze[$iPosition+$x]{1} = '0';
				$iPosition += $x;
			break;
		}
		$arrDiscovered[$iPosition] = 1;
		if ( '0' === $arrMaze[$iPosition]{0} ) {
			$iDiscovered++;
			$arrMaze[$iPosition]{0} = '1';
		}
	}
}

printMaze();

echo '<pre>'.number_format(microtime(true)-$iUtcStart, 4).' ('.$iStart.' -> '.$iPosition.')</pre>';

?>
<script type="text/javascript">
<!--//
var iStart = <?php echo $iStart; ?>, iPosition = <?php echo $iPosition; ?>, x = <?php echo $x; ?>;
document.getElementById('maze').rows[Math.floor(iStart/x)].cells[iStart%x].className += ' strt';
document.getElementById('maze').rows[Math.floor(iPosition/x)].cells[iPosition%x].className += ' pos';
document.getElementById('maze').onclick = function(e) {
	e = e || window.event || this.event;
	if ( 'TD' == e.target.nodeName ) {
		e.target.style.backgroundColor = 'green';
	}
}
//-->
</script>
<?php

function printMaze() {
	global $arrMaze, $iStart, $iPosition, $x, $y;
	echo '<table width="100%" height="100%" id="maze" border="0" cellpadding="0" cellspacing="0">';
	foreach ( $arrMaze AS $k => $p ) {
		if ( 0 == $k%$x ) { echo '<tr>'; }
		echo '<td class="d'.$p{0}.' w'.substr($p, 3).'">'.$k.'</td>';
		if ( 0 == ($k+1)%$x ) { echo '</tr>'; }
	}
	echo '</table>';
}

?>