<?php
// CUMULARI ABSOLUTUS

$g_arrColors = array (
	-13 => 'mediumblue',
	-12 => '#1313ff',
	-11 => '#2727ff',
	-10 => '#3a3aff',
	-9 => '#4e4eff',
	-8 => '#6262ff',
	-7 => '#7575ff',
	-6 => '#8989ff',
	-5 => '#9c9cff',
	-4 => '#b0b0ff',
	-3 => '#c4c4ff',
	-2 => '#d7d7ff',
	-1 => '#ebebff',
	0 => '#ffffff',
	1 => '#ffebeb',
	2 => '#ffd7d7',
	3 => '#ffc4c4',
	4 => '#ffb0b0',
	5 => '#ff9c9c',
	6 => '#ff8989',
	7 => '#ff7575',
	8 => '#ff6262',
	9 => '#ff4e4e',
	10 => '#ff3a3a',
	11 => '#ff2727',
	12 => '#ff1313',
	13 => '#dd0000',
);

if ( isset($_GET['check'], $_POST['moves']) ) {
	$arrFields = range(-12, 12);
	$arrMoves = explode(',', $_POST['moves']);
	foreach ( $arrMoves AS $n ) {
		if ( !isset($arrFields[$n]) ) {
			exit("Invalid move: $n\n");
		}

		$v = $arrFields[$n];
		if ( $v ) {
			$tn = $n + $v;
			if ( $tn > 24 || $tn < 0 ) {
				$tn -= 25 * floor($tn / 25);
			}
			$arrFields[$tn] += $v;
		}
	}

	$iScore = array_sum(array_map('abs', $arrFields));

	if ( 100 <= $iScore ) {
		exit("I'm sure you can do better than that!\nWe don't accept scores > 99.");
	}

	exit("Your score of $iScore was NOT saved, because reasons.\n");
}

?>
<!doctype html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="utf-8" />
<title>CUMULARI ABSOLUTUS</title>
<style>
* {
	margin: 0;
	padding: 0;
}
#cat {
	border-collapse: collapse;
	border: solid 1px #000;
	font-family: verdana, arial;
	font-size: 14px;
	position: absolute;
	top: 50%;
	left: 50%;
	-webkit-transform: translate(-50%, -50%);
	   -moz-transform: translate(-50%, -50%);
	    -ms-transform: translate(-50%, -50%);
	        transform: translate(-50%, -50%);
}
#cat td {
	border: solid 1px #000;
	width: 70px;
	height: 70px;
	cursor: pointer;
	font-weight: bold;
	text-align: center;
	vertical-align: middle;
}
#cat th {
	padding: 5px;
}
</style>
<script src="/js/rjs-custom.js"></script>
<script>
var g_colors = <?= json_encode($g_arrColors) ?>;
var g_stack = [];

function cancel(e) {
	e.preventDefault();
}

function initTable() {
	$$('#ca td').each(function(td, n) {
		td.title = String(n + 1);
		td.textContent = String(n - 12);
		td.style.backgroundColor = g_colors[n - 12];
		td.onclick = clickedOn;
		td.onmousedown = cancel;
	});
	reviewScore();
}

function redrawField(f_td) {
	var v = parseFloat(f_td.textContent);
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
}

function reviewScore() {
	var s = 0;
	$$('#ca td').each(function(td) {
		var v = parseFloat(td.textContent);
		s += Math.abs(v);
	});
	$('#score').textContent = String(s);
}

function clickedOn() {
	var td = this;
	var v = parseFloat(td.textContent);
	var n = td.cellIndex + 5 * td.parentNode.sectionRowIndex;

	var tn = n+v;
	if ( v == 0 ) {
		return false;
	}

	g_stack.push(n);
	if ( tn > 24 || tn < 0 ) {
		tn -= 25 * Math.floor((tn + 0) / 25);
	}
	var ttd = $('#ca').rows[ Math.floor(tn/5) ].cells[tn % 5];
	ttd.textContent = String(parseFloat(ttd.textContent) + v);
	redrawField(ttd);
}

function postScore() {
	var data = 'moves=' + g_stack.join(',');
	$.post('?check=1', data).on('load', function(e) {
		alert(this.responseText);
	});
	return false;
}
</script>
</head>

<body onload="initTable()">

<table id="cat" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td colspan="5">
				<span id="score">?</span>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="5">
				<input type="button" value="Save score" style="font-weight: bold" onclick="postScore()" />
				<input type="button" value="Restart" onclick="document.location.reload()" />
			</td>
		</tr>
	</tfoot>
	<tbody id="ca">
		<?= str_repeat('<tr>' . str_repeat('<td>0</td>', 5) . '</tr>', 5) ?>
	</tbody>
</table>

</body>

</html>
