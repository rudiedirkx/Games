<?php
// Maze

require __DIR__ . '/inc.bootstrap.php';

$iUtcStart = microtime(true);

$x = 20;
$y = 10;

$arrMaze = array_fill(0, $x*$y, '01111');
$arrMoves = array();

$iStart = rand(0, $x*$y-1);
$iPosition = $iStart;

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
		$iPosition = array_pop($arrMoves);
	}
	else {
		array_push($arrMoves, $iPosition);
		$dir = $dirs{rand(0, strlen($dirs)-1)};
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

?>
<html>

<head>
<meta charset="utf-8" />
<title>MAZE</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<style>
body {
	margin-right: 40px;
}
table {
	border: solid 2px #000;
	border-width: 2px 0 0 2px;
	touch-action: none;
}
td {
	border: solid 2px #000;
	border-width: 0;
	width: 30px;
	height: 30px;
	text-align: center;
	cursor: pointer;
}
.w01 {
	border-width: 0 0 2px 0;
}
.w10 {
	border-width: 0 2px 0 0;
}
.w11 {
	border-width: 0 2px 2px 0;
}
.d0 {
	background-color: #eee;
}
.done {
	background-color: yellow;
}
.end {
	background-color: lightblue;
}
.current {
	background-color: orange;
}
</style>
</head>

<body class="maze outside">
<?= printMaze() ?>

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
var iStart = <?= rand(0, $x*$y-1) ?>;
var iPosition = <?= rand(0, $x*$y-1) ?>;
var x = <?php echo $x; ?>;

var tbl = document.getElementById('maze');
var start = tbl.rows[Math.floor(iPosition/x)].cells[iPosition%x];
var end = tbl.rows[Math.floor(iStart/x)].cells[iStart%x];
var current = start;

start.className += ' done current start';
end.className += ' end';

class Maze extends GridGame {
	createStats() {
	}

	setMoves( f_iMoves ) {
		if ( f_iMoves != null ) {
			this.m_iMoves = f_iMoves;
		}
		if ( this.m_iMoves > 0 ) {
			this.startTime();
		}
	}

	setTime() {
	}

	haveWon() {
		return this.getCurrent().hasClass('end');
	}

	restart() {
		location.reload();
	}

	listenControls() {
		this.listenCellClick();
		this.listenGlobalDirection();
	}

	getCurrent() {
		return this.m_objGrid.getElement('.current');
	}

	getBorder( cell, border ) {
		return parseFloat(getComputedStyle(cell)['border' + border + 'Width']);
	}

	validMove( current, direction ) {
		var borders = ['Top', 'Right', 'Bottom', 'Left'];

		var dirIndex = this.dir4Names.indexOf(direction);
		if ( this.getBorder(current, borders[dirIndex]) > 0 ) {
			return false;
		}

		var next = this.getNext(current, direction);
		if ( this.getBorder(next, borders[(dirIndex+2)%4]) > 0 ) {
			return false;
		}

		return true;
	}

	getDirection( from, to ) {
		var offset = this.getCoord(to).subtract(this.getCoord(from));
		var direction = this.dir4Coords.findIndex((C) => C.equal(offset));
		if ( direction > -1 ) {
			return this.dir4Names[direction];
		}
	}

	getNext( current, direction ) {
		return this.getCell(this.getCoord(current).add(this.dir4Coords[this.dir4Names.indexOf(direction)]));
	}

	moveTo( cell ) {
		this.getCurrent().removeClass('current');
		cell.addClass('current').addClass('done');

		this.setMoves(this.m_iMoves + 1);
		this.winOrLose();
	}

	handleGlobalDirection( direction ) {
		if ( this.m_bGameOver ) return this.restart();

		direction = direction[0];
		var current = this.getCurrent();
		var next = this.getNext(current, direction);
		if ( next && this.validMove(current, direction) ) {
			this.moveTo(next);
		}
	}

	handleCellClick( cell ) {
		if ( this.m_bGameOver ) return this.restart();

		var current = this.getCurrent();
		var direction = this.getDirection(current, cell);
		if ( direction && this.validMove(current, direction) ) {
			this.moveTo(cell);
		}
	}
}

var objGame = new Maze($('table'));
objGame.listenControls();
</script>
</body>

</html>
<?php

function printMaze() {
	global $arrMaze, $iStart, $iPosition, $x, $y;

	$out = '';
	$out .= '<table width="100%" height="100%" id="maze" border="0" cellpadding="0" cellspacing="0">';
	foreach ( $arrMaze AS $k => $p ) {
		if ( 0 == $k%$x ) {
			$out .= '<tr>';
		}
		$out .= '<td class="d' . $p[0] . ' w' . substr($p, 3) . '"></td>';
		if ( 0 == ($k+1)%$x ) {
			$out .= '</tr>';
		}
	}
	$out .= '</table>';
	return $out;
}
