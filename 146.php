<?php
// SLITHER

$g_arrBoards = require '146_levels.php';

$lvl = explode('-', $_GET['lvl'] ?? 'easy-0');
$iGame = (int) ($lvl[1] ?? 0);
$szDifficulty = $lvl[0];
if (!isset($g_arrBoards[$szDifficulty][$iGame])) {
	$iGame = 0;
	$szDifficulty = 'easy';
}
$arrGame = $g_arrBoards[$szDifficulty][$iGame];

$b = 5;		// border width
$t = 26;	// td 'size'

$c = (int)$arrGame['size'][0];	// # cells (width)
$r = (int)$arrGame['size'][1];	// # rows (height)

$w = ($c+1)*$b + $c*$t;			// total table width
$h = 40 + ($r+1)*$b + $r*$t;	// total table height

?>
<!doctype html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>SLITHER | LEVEL <?= str_pad($iGame + 1,3,'0',STR_PAD_LEFT).' '.strtoupper($szDifficulty) ?></title>
<style>
* {
	padding: 0;
	margin: 0;
}
html, body {
	height: 100%;
}
html {
	background: #bde4a3;
}
form {
	padding: 10px;
}
#slither_div {
	margin: 0 auto;
	margin-top: 80px;
}
table, td {
	border: 0;
}
table {
	-webkit-transform: scale(<?= $c == 5? 1.8 : 1.5 ?>);
}
td.horbor {
	width: <?= $t ?>px;
	height: <?= $b ?>px;
	background: #888 url(images/146_horbor.bmp) center center repeat-x;
	cursor: pointer;
}
td.verbor {
	width: <?= $b ?>px;
	height: <?= $t ?>px;
	background: #888 url(images/146_verbor.bmp) center center repeat-y;
	cursor: pointer;
}
td.dot {
	width: <?= $b ?>px;
	height: <?= $b ?>px;
	background: green url(images/146_dot.bmp) center center no-repeat;
}
th.clue {
	width: <?= $t ?>px;
	height: <?= $t ?>px;
	background: lime url(images/146_clue.gif) center center no-repeat;
	font-family: arial 'courier new';
	font-size: 16px;
}
button {
	padding: 5px 12px;
}
</style>
<script src="js/rjs-custom.js"></script>
<script>
var g_c = <?= $c ?>;
var g_r = <?= $r ?>;

(new Image()).src = 'images/146_horbor_not.bmp';
(new Image()).src = 'images/146_verbor_not.bmp';
(new Image()).src = 'images/146_horbor_on.bmp';
(new Image()).src = 'images/146_verbor_on.bmp';

function clickBorder(o) {
	if ( '1' != o.getAttribute('on') ) {
		// SET
		o.style.backgroundImage = 'url(images/146_'+o.className+'_on.bmp)';
		o.setAttribute('on', '1')
	}
	else {
		// UNSET
		o.style.backgroundColor = '';
		o.style.backgroundImage = 'url(images/146_' + o.className + '.bmp)';
		o.setAttribute('on', '0')
	}
	if ( 'verbor' == o.className ) {
// VERTICAL BORDERS
		// Update clues
		var a = 0 < o.cellIndex ? (o.cellIndex)/2 : 0;
		var b = o.parentNode.sectionRowIndex/2-0.5;
		if ( 0 == a ) {
			updateClue('c_'+a+'_'+b);
		}
		else if ( g_c == a ) {
			updateClue('c_'+(a-1)+'_'+b);
		}
		else {
			updateClue('c_'+a+'_'+b);
			updateClue('c_'+(a-1)+'_'+b);
		}
		// Update dots
		var td = $('#slither').rows[o.parentNode.sectionRowIndex-1].cells[o.cellIndex];
		var bd = $('#slither').rows[o.parentNode.sectionRowIndex+1].cells[o.cellIndex];
		if ( '1' == o.getAttribute('on') ) {
			td.setAttribute( 'on', parseInt(td.getAttribute('on'))+1 );
			bd.setAttribute( 'on', parseInt(bd.getAttribute('on'))+1 );
		}
		else {
			td.setAttribute( 'on', parseInt(td.getAttribute('on'))-1 );
			bd.setAttribute( 'on', parseInt(bd.getAttribute('on'))-1 );
		}
	}
	else if ( 'horbor' == o.className ) {
// HORIZONTAL BORDERS
		// Update clues
		var a = o.cellIndex/2-0.5;
		var b = 0 < o.parentNode.sectionRowIndex ? (o.parentNode.sectionRowIndex)/2 : 0;
		if ( 0 == b ) {
			updateClue('c_'+a+'_'+b);
		}
		else if ( g_c == b ) {
			updateClue('c_'+a+'_'+(b-1));
		}
		else {
			updateClue('c_'+a+'_'+b);
			updateClue('c_'+a+'_'+(b-1));
		}
		// Update dots
		var ld = $('#slither').rows[o.parentNode.sectionRowIndex].cells[o.cellIndex-1];
		var rd = $('#slither').rows[o.parentNode.sectionRowIndex].cells[o.cellIndex+1];
		if ( '1' == o.getAttribute('on') ) {
			ld.setAttribute( 'on', parseInt(ld.getAttribute('on'))+1 );
			rd.setAttribute( 'on', parseInt(rd.getAttribute('on'))+1 );
		}
		else {
			ld.setAttribute( 'on', parseInt(ld.getAttribute('on'))-1 );
			rd.setAttribute( 'on', parseInt(rd.getAttribute('on'))-1 );
		}
	}
}

function rightClickBorder(o) {
	if ( '1' != o.getAttribute('not') ) {
		o.style.backgroundImage = 'url(images/146_' + o.className + '_not.bmp)';
		o.setAttribute('not', '1');
	}
	else {
		o.style.backgroundImage = 'url(images/146_' + o.className + '.bmp)';
		o.setAttribute('not', '0');
	}
}

function updateClue(id) {
	var o = $('#' + id);
	// Maybe there are no restrictions/demands
	if ( o.innerHTML ) {
		var iClue = parseInt(o.innerHTML), iBorders = 0;
		// Find the 4 adjacent borders
		var t = $('#slither').rows;
		var r = o.parentNode.sectionRowIndex;
		var c = o.cellIndex;
		var top = t[r-1].cells[c], bottom = t[r+1].cells[c], left = t[r].cells[c-1], right = t[r].cells[c+1];
		iBorders += '1' == top.getAttribute('on') ? 1 : 0;
		iBorders += '1' == right.getAttribute('on') ? 1 : 0;
		iBorders += '1' == bottom.getAttribute('on') ? 1 : 0;
		iBorders += '1' == left.getAttribute('on') ? 1 : 0;
		o.style.color = iBorders == iClue ? 'white' : 'black';
	}
	checkClues();
	return false;
}

function checkClues() {
	var t = $('#slither').getElementsByTagName('th');
	var i = t.length;
	var iHave = 0;
	var iMustHave = 0;
	while (i--) {
		if ( 'clue' == t[i].className && '' != t[i].innerHTML ) {
			iMustHave++;
			if ( t[i].style.color == 'white' ) {
				iHave++;
			}
		}
	}
	if ( iHave == iMustHave ) {
		if ( checkBorders() ) {
			return true;
		}
	}
	return false;
}

function checkBorders() {
	var t = $('#slither').getElementsByTagName('td');
	var i = t.length;
	while (i--) {
		if ( 'dot' == t[i].className ) {
			if ( '2' != t[i].getAttribute('on') && '0' != t[i].getAttribute('on') ) {
				return false;
			}
		}
	}
	return true;
}

var g_bHB = false, g_objBHThis, g_objBHLast;
function hiliteBorders() {
	var t = $('#slither').getElementsByTagName('td'), i = t.length;
	while (i--) {
		if ( ( 'horbor' == t[i].className || 'verbor' == t[i].className ) && '1' == t[i].getAttribute('on') ) {
			g_objBHThis = t[i];
			g_bHB = true;
			return hiliteNextBorder(t[i]);
		}
	}
}

function hiliteNextBorder() {
	if ( !g_bHB ) {
		return false;
	}
	// Change background of current border
	g_objBHThis.setAttribute('old_bg_img', g_objBHThis.style.backgroundImage);
	g_objBHThis.style.backgroundImage = 'url(images/146_'+g_objBHThis.className+'_BH.bmp)';
	// Restore background of previous border
	if ( g_objBHLast ) {
		g_objBHLast.style.backgroundImage = g_objBHLast.getAttribute('old_bg_img');
	}
	// Current border's details
	var t = $('#slither').rows, r = g_objBHThis.parentNode.sectionRowIndex, c = g_objBHThis.cellIndex, m_r = g_r*2, m_c = g_c*2, nxt = null;
	// Find next border
	s = 100;
	if ( 'horbor' == g_objBHThis.className ) {
		if ( 0 <= c-2 && '1' == t[r].cells[c-2].getAttribute('on') && g_objBHLast != t[r].cells[c-2] ) {
			// left
			g_objBHLast = g_objBHThis;
			nxt = t[r].cells[c-2];
			g_objBHThis = nxt;
			return setTimeout(hiliteNextBorder, s);
		}
		else if ( m_c >= c+2 && '1' == t[r].cells[c+2].getAttribute('on') && g_objBHLast != t[r].cells[c+2] ) {
			// right
			g_objBHLast = g_objBHThis;
			nxt = t[r].cells[c+2];
			g_objBHThis = nxt;
			return setTimeout(hiliteNextBorder, s);
		}
	}
	else if ( 'verbor' == g_objBHThis.className ) {
		if ( 0 <= r-2 && '1' == t[r-2].cells[c].getAttribute('on') && g_objBHLast != t[r-2].cells[c] ) {
			// top
			g_objBHLast = g_objBHThis;
			nxt = t[r-2].cells[c];
			g_objBHThis = nxt;
			return setTimeout(hiliteNextBorder, s);
		}
		else if ( m_r >= r+2 && '1' == t[r+2].cells[c].getAttribute('on') && g_objBHLast != t[r+2].cells[c] ) {
			// bottom
			g_objBHLast = g_objBHThis;
			nxt = t[r+2].cells[c];
			g_objBHThis = nxt;
			return setTimeout(hiliteNextBorder, s);
		}
	}
	if ( 0 <= c-1 && 0 <= r-1 && '1' == t[r-1].cells[c-1].getAttribute('on') && g_objBHLast != t[r-1].cells[c-1] ) {
		// left top
		g_objBHLast = g_objBHThis;
		nxt = t[r-1].cells[c-1];
		g_objBHThis = nxt;
		return setTimeout(hiliteNextBorder, s);
	}
	else if ( m_c >= c+1 && 0 <= r-1 && '1' == t[r-1].cells[c+1].getAttribute('on') && g_objBHLast != t[r-1].cells[c+1] ) {
		// right top
		g_objBHLast = g_objBHThis;
		nxt = t[r-1].cells[c+1];
		g_objBHThis = nxt;
		return setTimeout(hiliteNextBorder, s);
	}
	else if ( 0 <= c-1 && m_r >= r+1 && '1' == t[r+1].cells[c-1].getAttribute('on') && g_objBHLast != t[r+1].cells[c-1] ) {
		// left bottom
		g_objBHLast = g_objBHThis;
		nxt = t[r+1].cells[c-1];
		g_objBHThis = nxt;
		return setTimeout(hiliteNextBorder, s);
	}
	else if ( m_c >= c+1 && m_r >= r+1 && '1' == t[r+1].cells[c+1].getAttribute('on') && g_objBHLast != t[r+1].cells[c+1] ) {
		// right bottom
		g_objBHLast = g_objBHThis;
		nxt = t[r+1].cells[c+1];
		g_objBHThis = nxt;
		return setTimeout(hiliteNextBorder, s);
	}
	return false;
}
function levelDone() {
	$('#notices').setHTML('<a href="?lvl=<?= $szDifficulty ?>-<?= ($iGame + 1) ?>">Next: <?= $szDifficulty ?> <?= ($iGame + 2) ?>...</a>');
}
function checkDone(btn) {
	g_bHB = false;
	if ( checkClues() ) {
		btn.setText('WOOHOO');
		btn.onclick = null;
		hiliteBorders();
		levelDone();
	}
	else {
		alert("That's not it... One link. White numbers. A slithering snake.");
	}
}

function xor(a, b) {
	return (a || b) && !(a && b);
}
</script>
</head>

<body onload="init()">

<form>
	<select id="levelselect0r">
		<optgroup label="-- Select a level!"></optgroup>
		<?foreach( $g_arrBoards AS $szD => $arrBoards ):?>
			<optgroup label="<?=ucfirst(strtolower($szD))?>">
			<?foreach( $arrBoards AS $iL => $arrL ):?>
				<option value="<?= "$szD-$iL" ?>"<?if( $szDifficulty == $szD && $iGame == $iL ):?>selected>&gt; <?else:?>><?endif?><?= $szD ?> <?= ($iL + 1) ?></option>
			<?endforeach?>
			</optgroup>
		<?endforeach?>
	</select>
</form>

<div id="slither_div" style="width: <?= $w ?>px">
<table border="0" cellpadding="0" cellspacing="0">
<thead><tr><th style="height:40px;" colspan="<?= (2*$c+1) ?>" id="notices">&nbsp;</th></tr></thead>
<tfoot><tr><th style="height:40px;" colspan="<?= (2*$c+1) ?>"><button onclick="checkDone(this);">check</button></th></tr></tfoot>
<tbody id="slither"><?php
echo $szRow = '<tr><td class="dot" on="0"></td>'.str_repeat('<td class="horbor"></td><td class="dot" on="0"></td>', $c).'</tr>';
for ( $i=0; $i<$r; $i++ )
{
	echo '<tr><td class="verbor"></td>';
	for ( $j=0; $j<$c; $j++ )
	{
		$iNumber = trim($arrGame['board'][$i][$j] ?? '');
		echo '<th id="c_'.$j.'_'.$i.'" class="clue" style="color:'.( '0' === $iNumber ? 'white' : 'black' ).';">'.$iNumber.'</th><td class="verbor"></td>';
	}
	echo '</tr>'.$szRow;
}
?></tbody>
</table>
</div>

<div id="red" style="position: absolute; width: 6px; height: 6px; margin: -3px 0 0 -3px; background: red;"></div>

<script>
var evType = 'ontouchstart' in document.documentElement ? 'touchstart' : 'mousedown';
var $slither = $('#slither');

function init() {
	$slither.on(evType, function(e) {
		if ( e.rightClick ) return true;

		var tw = $slither.getBoundingClientRect().width,
			bw = $slither.getElement('.verbor').getBoundingClientRect().width,
			ss = (tw-bw) / g_c;

		var col = Math.floor(e.subjectXY.x / (tw / (g_c*3+1))),
			row = Math.floor(e.subjectXY.y / (tw / (g_c*3+1)));

		if ( xor(col % 3 == 0, row % 3 == 0) ) {
			$('#red').css(e.pageXY.toCSS());

			if ( col%3 == 0 ) {
				col /= 3;
				var row = Math.floor(e.subjectXY.y / ss);
				clickBorder(this.rows[row*2+1].cells[col*2]);
			}
			else {
				row /= 3;
				var col = Math.floor(e.subjectXY.x / ss);
				clickBorder(this.rows[row*2].cells[col*2+1]);
			}
		}
	});
}

$('#levelselect0r').on('change', function(e) {
	if ( this.value && <?= $iGame ?> != this.value ) {
		location = '?lvl=' + this.value;
	}
}).form.reset();

document.on('dragstart', function(e) { ['INPUT', 'SELECT', 'BUTTON'].contains(e.target.nodeName) || e.preventDefault(); });
document.on('mousedown', function(e) { ['INPUT', 'SELECT', 'BUTTON'].contains(e.target.nodeName) || e.preventDefault(); });
</script>

</body>

</html>
