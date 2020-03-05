<?php
// Fortune's Tower

/**
 * TODO:
 * - gatecard checken (misschien maakt ie een nieuwe verticale combi)		v
 * - als je een set maakt, is heel de rij automatisch burnvrij				v
 * - als de gebruikte gate card een knight is -> unburn heel de rij			v
 * - multiplier pas checken/tellen NA de gatecard							v
 * - 8 rows & 8 'decks', not 7												v
 * - Jackpot = [sum of all cards] * [multiplier], not X5 multiplier			v
 */

$g_iStartBalance = 0;

define('S_NAME', 'ft_157');

if ( empty($_COOKIE[S_NAME.'_balance']) ) {
	setcookie(S_NAME.'_balance', $g_iStartBalance);
}
$g_arrCards = array_merge(array(0,0,0,0),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7),range(1,7));
shuffle($g_arrCards);

$arrCards = array();
for ( $i=0; $i<8; $i++ ) {
	$arrCards[] = array_splice($g_arrCards, 0, $i+1);
}

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Fortune's Tower</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<? include 'tpl.onerror.php' ?>
<script src="/js/mootools_1_11.js"></script>
<style>
span.card {
	font-family			: 'courier new';
	font-size			: 40px;
	border				: solid 1px #000;
	margin				: 2px 1px;
	padding				: 2px 1px;
	background-color	: #c00;
	color				: #fff;
	font-weight			: bold;
}
div.row {
	margin-top			: -14px;
	text-align			: center;
}
#tower {
	padding				: 7px;
	padding-top			: 21px;
	background-color	: #eee;
	width				: 300px;
	position			: absolute;
	border				: solid 1px black;
}
div.set span.card {
	background-color	: gold;
}
</style>
<script>
function asyncAlert(msg, after) {
	setTimeout(function() {
		alert(msg);
		after && setTimeout(after);
	}, 50);
}

var ROWS = 8, COOKIENAME = '<?php echo S_NAME.'_balance'; ?>', g_iBalance = Cookie.get(COOKIENAME).toInt();
var g_iBetBase = <?php echo max(1, min(10, isset($_GET['base'])?(int)$_GET['base']:1)); ?>, g_arrCards = <?php echo json_encode($arrCards); ?>, g_iMultiplier = 1, g_iRow = 0, g_bGateCard = true, g_bGameOver = false;
function nextRow() {
	if ( 0 === g_iRow ) {
		return initTower();
	}
	return fillAndShowRow(++g_iRow);
}
function burnCard(r, c) {
	$('c'+r+''+c).style.backgroundColor = 'black';
	return false;
}
function unburnCard(r, c) {
	$('c'+r+''+c).style.backgroundColor = '';
	return false;
}
function unburnRow(r) {
	for ( var c=0; c<=r; c++ ) {
		unburnCard(r, c);
	}
	return false;
}
function addMultiplier(m) {
	return setMultiplier(g_iMultiplier*m);
}
function setMultiplier(m) {
	g_iMultiplier = m;
	$('multiplier').innerHTML = g_iMultiplier;
	return false;
}
function rowIsSet(r) {
	for ( var c=1; c<=r; c++ ) {
		if ( $('c'+r+''+c).innerHTML !== $('c'+r+''+(c-1)).innerHTML ) {
			return false;
		}
	}
	return true;
}
function rowHasKnight(r) {
	for ( var c=0; c<=r; c++ ) {
		if ( 'x' === $('c'+r+''+c).innerHTML ) {
			return true;
		}
	}
	return false;
}
function rowHasVPair(r) {
	var b = -1;
	if ( 2 > r ) { return b; }
	for ( var c=0; c<=r-1; c++ ) {
		if ( 0 < g_arrCards[r-1][c] ) {
			if ( g_arrCards[r-1][c] == g_arrCards[r][c] ) {
				burnCard(r-1, c);
				burnCard(r, c);
				if ( -1 == b ) {
					b = c;
				}
			}
			if ( g_arrCards[r-1][c] == g_arrCards[r][c+1] ) {
				burnCard(r-1, c);
				burnCard(r, c+1);
				if ( -1 == b ) {
					b = c+1;
				}
			}
		}
	}
	return b;
}
function fillAndShowRow(r) {
	if ( g_bGameOver ) {
		return false;
	}
	g_iRow = r;
	unburnRow(r);
	showRow(r);
	if ( 0 == r ) {
		$('c00').innerHTML = '?';
//		$('c00').innerHTML = g_arrCards[0][0];
		return false;
	}
	var iRowScore = 0, bKnight = false;
	g_arrCards[r].each(function(v, c) {
		$('c'+r+''+c).innerHTML = 0 < v ? v : 'x';
		iRowScore += v;
		if ( 0 == v ) {
			bKnight = true;
		}
	});
	if ( rowIsSet(r) ) {
		$('r'+r).addClass('set');
		asyncAlert('Set -> extra multiplier: X'+(r+1));
		addMultiplier(r+1);
	}
	else {
		var bc = rowHasVPair(r);
		if ( 0 <= bc ) {
			if ( rowHasKnight(r) ) {
				asyncAlert('Using knight...');
				unburnRow(r-1);
				unburnRow(r);
			}
			else if ( g_bGateCard ) {
				g_bGateCard = false;
				asyncAlert('Using gate card...');
				hideRow(0);
				unburnRow(r-1);
				unburnRow(r);
				iRowScore -= g_arrCards[r][bc];
				g_arrCards[r][bc] = g_arrCards[0][0];
				iRowScore += g_arrCards[0][0];
				$('c'+r+''+bc).innerHTML = 0 < g_arrCards[0][0] ? g_arrCards[0][0] : 'x';
				if ( 0 <= rowHasVPair(r) ) {
					if ( !rowHasKnight(r) ) {
						g_bGameOver = true;
					}
					else {
						asyncAlert('Using knight...');
						unburnRow(r-1);
						unburnRow(r);
					}
				}
			}
			else {
				g_bGameOver = true;
			}
		}
	}
	$('x_pays_y_x').innerHTML = iRowScore;
	$('x_pays_y_y').innerHTML = g_iBetBase*g_iMultiplier*iRowScore;
	if ( g_bGameOver ) {
		asyncAlert('You lose', "document.location.reload()");
		return false;
	}
	if ( ROWS-1 == r ) { // last row
		var jp = false;
		if ( g_bGateCard ) {
			var iTotal = 0;
			for ( var i=1; i<ROWS; i++ ) {
				for ( var j=0; j<=i; j++ ) {
					iTotal += g_arrCards[i][j];
				}
			}
			$('x_pays_y_y').innerHTML = g_iBetBase*g_iMultiplier*iTotal;
			jp = true;
		}
		return cashOut(jp);
	}
	return false;
}
function showRow(r) {
	$('r'+r).className = 'row';
	$('r'+r).style.visibility = 'visible';
	return false;
}
function hideRow(r) {
	$('r'+r).style.visibility = 'hidden';
	return false;
}
function initTower() {
	g_bGateCard = true;
	setMultiplier(1);
	$('bet').innerHTML = g_iBetBase*15;
	g_iBalance -= g_iBetBase*15;
	$('balance').innerHTML = g_iBalance;
	Cookie.set(COOKIENAME, g_iBalance);
	fillAndShowRow(0);
	return fillAndShowRow(1);
}
function clearTower() {
	for ( var r=0; r<ROWS; r++ ) {
		hideRow(r);
	}
	return false;
}
function completeTower() {
	for ( var r=g_iRow+1; r<ROWS; r++ ) {
		for ( var c=0; c<=r; c++ ) {
			$('c'+r+''+c).innerHTML = 0 < g_arrCards[r][c] ? g_arrCards[r][c] : 'x';
			$('c'+r+''+c).style.backgroundColor = 'blue';
		}
		showRow(r);
		rowHasVPair(r);
	}
	return false;
}
function cashOut(jp) {
	completeTower();
	var iWins = $('x_pays_y_y').innerHTML.toInt();
	g_iBalance += iWins ? iWins : 0;
	$('balance').innerHTML = g_iBalance;
	Cookie.set(COOKIENAME, g_iBalance);
	asyncAlert((jp ? 'Jackpot! ' : '') + 'You win: ' + $('x_pays_y_y').innerHTML, 'document.location.reload()');
	return false;
}
</script>
</head>

<body>
<table id="buttons" border="0" cellpadding="4" cellspacing="0">
<tr>
	<td align="center" colspan="2">
		Balance: <span ondblclick="Cookie.set(COOKIENAME, <?php echo $g_iStartBalance; ?>);document.location.reload();" id="balance"><?php echo (int) @$_COOKIE[S_NAME.'_balance']; ?></span>,
		You bet: <span id="bet">0</span>
	</td>
</tr>
<tr>
	<td align="center" colspan="2">
		<input type="button" value="Deal" accesskey="d" onclick="return nextRow();" />
		<input type="button" value="Cash out" accesskey="c" onclick="return cashOut();" />
	</td>
</tr>
</table>
<div id="tower"><?php

for ( $iRow=0; $iRow<8; $iRow++ ) {
	echo '<div style="visibility:hidden;" id="r'.$iRow.'" class="row">';
	for ( $iCard=0; $iCard<=$iRow; $iCard++ ) {
		echo '	<span id="c'.$iRow.$iCard.'" class="card">?</span>';
	}
	echo '</div>';
}

?><div style="position:absolute;left:5px;top:5px;font-size:24px;font-weight:bold;"><span id="multiplier">1</span>X</div><div style="width:60px;text-align:center;position:absolute;right:5px;top:5px;"><span style="font-weight:bold;" id="x_pays_y_x">0</span> pays<br /><span style="font-weight:bold;font-size:24px;" id="x_pays_y_y">0</span></div></div>

<script>
$('buttons').setAttribute('width', $('tower').offsetWidth);
</script>
</body>

</html>
