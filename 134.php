<?php
  // PHP Tic Tac Toe
  // Coded by Joey Cato (jojo@kypsoft.com)
  // This code is free for anyone to use.
  // If you do use it, please let me know, I'd love to
  // see it in action somewhere else. -thx


$game = isset($_GET['game']) ? $_GET['game'] : NULL;
$player = isset($_GET['player']) ? $_GET['player'] : NULL;

$a = isset($_GET['a']) ? $_GET['a'] : NULL;
$b = isset($_GET['b']) ? $_GET['b'] : NULL;
$c = isset($_GET['c']) ? $_GET['c'] : NULL;
$d = isset($_GET['d']) ? $_GET['d'] : NULL;
$e = isset($_GET['e']) ? $_GET['e'] : NULL;
$f = isset($_GET['f']) ? $_GET['f'] : NULL;
$g = isset($_GET['g']) ? $_GET['g'] : NULL;
$h = isset($_GET['h']) ? $_GET['h'] : NULL;
$i = isset($_GET['i']) ? $_GET['i'] : NULL;

if ( !$game )
{
	//Initialize
	$a = $b = $c = $d = $e = $f = $g = $h = $i = 0;
	$player=1;
}
else
{
	//Advance to next player
	$player = (++$player > 2) ? 1 : 2;
}

$pieces = array ( $a,$b,$c,$d,$e,$f,$g,$h,$i );



function draw_piece( $idx )
{
	global $pieces, $player;
	$p = $pieces;

	switch ( $p[$idx] )
	{
		case 0:
			$p[$idx] = $player;
			$url = '';
//			$url .= '<a href="?game=1&a='.$p[0].'&b='.$p[1].'&c='.$p[2].'&d='.$p[3].'&e='.$p[4].'&f='.$p[5].'&g='.$p[6].'&h='.$p[7].'&i='.$p[8].'&player='.$player.'">';
			$url .= '<img p="2" style="cursor:pointer;" onclick="if(\'2\'!=this.getAttribute(\'p\') || !g_bPlaying){return false;}this.src=g_arrPlayerPics[g_iPlayer];this.style.cursor=\'default\';this.setAttribute(\'p\',g_iPlayer);g_iPlayer=!g_iPlayer?1:0;cfw();" src="/icons/blank.gif" width="64" height="64" border="0" />';
//			$url .= '</a>';
			print $url;
		break;

		case 1: //Red-O
			print "<img src=\"images/ro.gif\" width=64 height=64 border=0>";
		break;

		case 2: //Blue-X
			print "<img src=\"images/bx.gif\" width=64 height=64 border=0>";
		break;

		default:
			print "<img src=\"images/blank.gif\" width=64 height=64 border=0>";
		break;
	}
}

?>
<html>

<head>
<title>Tic Tac Toe</title>
<script type="text/javascript">
<!--//
var g_iPlayerStarted = 0, g_iPlayer = 0, g_arrPlayerPics = ['images/134_o.gif','images/134_x.gif'], g_bPlaying = true, g_arrNumbers = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'], g_arrWins = [0, 0];
var cfw = function() {
	var m = [], n = document.getElementById('ttt').getElementsByTagName('img'), i = n.length, w = 2;
	while (i--) {
		m[i] = n[i].getAttribute('p');
	}
	if ( '2' != m[0] && m[0] == m[4] && m[4] == m[8] ) {
		w = m[0];
	}
	else if ( '2' != m[2] && m[2] == m[4] && m[4] == m[6] ) {
		w = m[2];
	}
	else if ( '2' != m[0] && m[0] == m[1] && m[1] == m[2] ) {
		w = m[0];
	}
	else if ( '2' != m[3] && m[3] == m[4] && m[4] == m[5] ) {
		w = m[3];
	}
	else if ( '2' != m[6] && m[6] == m[7] && m[7] == m[8] ) {
		w = m[6];
	}
	else if ( '2' != m[0] && m[0] == m[3] && m[3] == m[6] ) {
		w = m[0];
	}
	else if ( '2' != m[1] && m[1] == m[4] && m[4] == m[7] ) {
		w = m[1];
	}
	else if ( '2' != m[2] && m[2] == m[5] && m[5] == m[8] ) {
		w = m[2];
	}
	if ( '2' != w ) {
		g_bPlaying = false;
		g_arrWins[w]++;
		alert('Player '+(1+parseInt(w))+' won this game!!');
//		document.getElementById('p_'+w+'_wins').innerHTML = g_arrNumbers[g_arrWins[w]];
		document.getElementById('p_'+w+'_wins').innerHTML = g_arrWins[w];
	}
}
//-->
</script>
</head>

<body bgcolor="#000000">
<font color="#00FF00">
<center><h1>JS Tic-Tac-Toe</h1></center>
</font>

<table id="ttt" align="center" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td><?php draw_piece(0); ?></td>
		<td bgcolor="#0060ff" width="10"></td>
		<td><?php draw_piece(1); ?></td>
		<td bgcolor="#0060ff" width="10"></td>
		<td><?php draw_piece(2); ?></td>
	</tr>
	<tr>
		<td colspan="5" height="10" bgcolor="#0060ff"></td>
	</tr>
	<tr>
		<td><?php draw_piece(3); ?></td>
		<td bgcolor="#0060ff" width="10"></td>
		<td><?php draw_piece(4); ?></td>
		<td bgcolor="#0060ff" width="10"></td>
		<td><?php draw_piece(5); ?></td>
	</tr>
	<tr>
		<td colspan="5" height="10" bgcolor="#0060ff"></td>
	</tr>
	<tr>
		<td><?php draw_piece(6); ?></td>
		<td bgcolor="#0060ff" width="10"></td>
		<td><?php draw_piece(7); ?></td>
		<td bgcolor="#0060ff" width="10"></td>
		<td><?php draw_piece(8); ?></td>
	</tr>
</table>

<br />

<table align="center" border="1" cellpadding="10" style="color:white;">
<tr>
<th colspan="2">W I N S</th>
</tr>
<tr>
<tr>
<th>Player 1</th>
<th>Player 2</th>
</tr>
<tr>
<th id="p_0_wins">&nbsp;</th>
<th id="p_1_wins">&nbsp;</th>
</tr>
</table>

<br />

<center><input type="button" value="Start Over" onclick="(function(){var m=document.getElementById('ttt').getElementsByTagName('img'), i=m.length;while(i--){m[i].setAttribute('p','2');m[i].style.cursor='pointer';m[i].src='/icons/blank.gif';}g_iPlayer=g_iPlayerStarted=(0==g_iPlayerStarted?1:0);g_bPlaying=true;})();" /></center>

</body>

</html>
