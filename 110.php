<?php
// CUMULARI ABSOLUTUS

//session_start();

require_once( "connect.php" );
require_once( "json_php".(int)PHP_VERSION.".php" );
//define( "S_NAME", "nn_user" );

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );

$g_arrColors = array (
	-13 => 'mediumblue',
	-12 => '1313ff',
	-11 => '2727ff',
	-10 => '3a3aff',
	-9 => '4e4eff',
	-8 => '6262ff',
	-7 => '7575ff',
	-6 => '8989ff',
	-5 => '9c9cff',
	-4 => 'b0b0ff',
	-3 => 'c4c4ff',
	-2 => 'd7d7ff',
	-1 => 'ebebff',
	0 => 'ffffff',
	1 => 'ffebeb',
	2 => 'ffd7d7',
	3 => 'ffc4c4',
	4 => 'ffb0b0',
	5 => 'ff9c9c',
	6 => 'ff8989',
	7 => 'ff7575',
	8 => 'ff6262',
	9 => 'ff4e4e',
	10 => 'ff3a3a',
	11 => 'ff2727',
	12 => 'ff1313',
	13 => 'dd0000',
);

if ( isset($_GET['check'], $_POST['moves']) ) {
	$arrFields = range(-12, 12);
	$arrMoves = explode(',', $_POST['moves']);
	foreach ( $arrMoves AS $n ) {
		if ( isset($arrFields[$n]) ) {
			$v = (int)$arrFields[$n];
			if ( 0 !== $v ) {
				$tn = $n + $v;
				if ( $tn > 24 || $tn < 0 ) {
					$tn -= 25*floor($tn/25);
				}
				$arrFields[$tn] += $v;
			}
		}
	}
	$iScore = array_sum(array_map('abs', $arrFields));
	if ( 100 <= $iScore ) {
		exit('I\'m sure you can do better than that!'."\n".'We don\'t accept scores >= 100.');
	}
	$s = (bool)mysql_query("INSERT INTO cumulari_absolutus (name, ip, score, moves, `utc`) VALUES ('".gethostbyaddr($_SERVER['REMOTE_ADDR'])."', '".$_SERVER['REMOTE_ADDR']."', '".$iScore."', '".count($arrMoves)."', '".time()."');");
	exit('Your score of '.(string)$iScore.' has '.( !$s ? 'not ' : '' ).'been saved.');
}

if ( isset($_GET['top10']) ) {
	echo trim(str_repeat("<Name> - <Score> - <Time> sec\n", 10));
	exit;
}

?>
<html>

<head>
<title>CUMULARI ABSOLUTUS</title>
<style type="text/css">
* { margin:0; padding:0; }
table#cat {
	border-collapse		: collapse;
	border				: solid 1px #000;
	font-family			: verdana, arial;
	font-size			: 14px;
}
table#cat td {
	border				: solid 1px #000;
	width				: 70px;
	height				: 70px;
	cursor				: pointer;
	font-weight			: bold;
}
table#cat th {
	padding				: 5px;
}
</style>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<script type="text/javascript">
<!--//
function postScore() {
	new Ajax('?check=1', {
		data : 'moves='+g_stack.join(','),
		onComplete : function (t) {
			alert(t);
		}
	}).request();
	return false;
}
var g_colors = <?php echo json::encode($g_arrColors); ?>, g_stack = [];
function initTable() {
	$$('#ca td').each(function(td, n) {
		td.title = ''+(n+1);
		td.innerHTML = ''+(n-12);
		td.style.backgroundColor = g_colors[n-12];
		td.onclick = clickedOn;
	});
	reviewScore();
	return false;
}
function redrawField(f_td) {
	var v = f_td.innerHTML.toInt();
	if ( v > 12 ) {
		f_td.style.backgroundColor = g_colors[13];
	}
	else if ( v < -12 ) {
		f_td.style.backgroundColor = g_colors[-13];
	}
	else {
		f_td.style.backgroundColor = g_colors[v];
	}
	reviewScore();
	return false;
}
function reviewScore() {
	var s = 0;
	$$('#ca td').each(function(td) {
		s += Math.abs(td.innerHTML.toInt());
	});
	$('score').innerHTML = s
	return s;
}
function clickedOn() {
	var td = this;
	var v = td.innerHTML.toInt(), n = td.cellIndex + 5 * td.parentNode.sectionRowIndex, tn = n+v;
	if ( v == 0 ) { return false; }
	g_stack.push(n);
	if ( tn > 24 || tn < 0 ) {
		tn -= 25*Math.floor((tn+0)/25);
	}
	var ttd = $('ca').rows[Math.floor(tn/5)].cells[tn%5];
	ttd.innerHTML = ''+(ttd.innerHTML.toInt() + v);
	return redrawField(ttd);
}
var g_top10 = false;
function top10() {
	if ( g_top10 ) {
		alert(g_top10);
		return false;
	}
	new Ajax('?top10=1', {
		async : true,
		onComplete : function(t) {
			alert(g_top10 = t);
		}
	}).request();
	return false;
}
//-->
</script>
</head>

<body>
<table border="1" cellpadding="0" cellspacing="0" width="100%" height="100%"><tr valign="middle"><td align="center">
<table id="cat" border="0" cellpadding="0" cellspacing="0"><thead><tr><th colspan="5"><span id="score">?</span> (<a href="#" onclick="return top10();">top 10</a>)</th></tr></thead><tfoot><tr><th colspan="5"><input type="button" value="Save score" style="font-weight:bold;" onclick="postScore();" /> <input type="button" value="restart" onclick="document.location.reload();" /></th></tr></tfoot><tbody id="ca"><?php echo str_repeat('<tr valign="middle">'.str_repeat('<td align="center">0</td>', 5).'</tr>', 5); ?></tbody></table>
</td></tr></table>
<script type="text/javascript">
<!--//
initTable();
//-->
</script>
</body>

</html>
