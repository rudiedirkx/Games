<?php

session_start();

define('S_NAME', '149_shifter');

// correct image
$g_arrMap = array(
	24, 5, 15, 11, 20,
	9, 14, 25, 4, 10,
	1, 22, 3, 19, 12,
	18, 13, 23, 2, 8,
	7, 17, 6, 16, 21
);

$arrEmpties = array(
	7 => true,
	10 => true,
	11 => true,
	17 => true,
	18 => true,
	20 => true,
);

if ( isset($_POST['map']) ) {
	$arrMap = array_map('intval', explode(',', $_POST['map']));

//	exit( $arrMap === $g_arrMap ? 'Yes' : 'No' );

	$iOk = 0;
	foreach ( $g_arrMap AS $k => $iPad ) {
		if ( !isset($arrMap[$k]) ) {
			exit('NO');
		}
		else if ( $iPad === $arrMap[$k] ) {
			$iOk++;
		}
		else if ( !isset($arrEmpties[$arrMap[$k]]) || !isset($arrEmpties[$iPad]) ) {
			exit('NO');
		}
	}
	if ( !isset($_SESSION[S_NAME]['start_time']) ) {
		$_SESSION[S_NAME]['start_time'] = 0;
	}
	$iTime = time()-(int)$_SESSION[S_NAME]['start_time'];
	exit('YES:'.$iTime);
}


// shuffle
shuffle($g_arrMap);

// break down into 2D array
$g_arrMap = array_chunk($g_arrMap, 5);

$_SESSION[S_NAME]['start_time'] = time();

?>
<html>

<head>
<title>Shifter</title>
<style>
div#padcontainer {
	position			: absolute;
	width				: 415px;
	margin-left			: -212px;
	left				: 50%;
	background-color	: transparent;
	text-align			: center;
}
div.dot {
	position			: absolute;
	width				: 16px;
	height				: 16px;
	background-color	: transparent;
}
div.dot img {
	border				: 0;
	cursor				: pointer;
	width				: 16px;
	height				: 16px;
}
</style>
<script type="text/javascript" src="/js/general_1_2_6.js"></script>
<script type="text/javascript" src="/js/ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
var SHIFTER = {
	check : function() {
		new Ajax('', {
			params		: 'map=' + SHIFTER.export(),
			onComplete	: function(a) {
				var t = a.responseText;
				alert('YES' == t.split(':')[0] ? 'Game over! Congratulations! ('+t.split(':')[1]+' sec)' : 'Not yet... But keep trying!');
			}
		});
		return false;
	},

	rotate : function(o) {
		var c = o.getAttribute('pads').split(','), srcs = [$('pad_'+c[3]).src, $('pad_'+c[0]).src, $('pad_'+c[1]).src, $('pad_'+c[2]).src]
		foreach ( c, function(k, v) {
			$('pad_'+v).src = srcs[k];
		});
		return false;
	},

	export : function() {
		var pads = $('padtable').getElementsByTagName('img'), o = [];
		foreach(pads, function(k, v) {
			var x = v.src.split('_'), x = x[x.length-1], x = x.split('.')[0];
			o.push(x);
		});
		return o.join(',');
	}
};

addEventHandler(window, 'load', function() {
	foreach ( $('dots').getElementsByTagName('img'), function(k, dot) {
		dot.onclick = function() {
			return SHIFTER.rotate(this);
		}
	});
});
//-->
</script>
</head>

<body>
<div id="padcontainer">
<table id="padtable" border="0" cellpadding="0" cellspacing="0">
<?php

$n = 0;
foreach ( $g_arrMap AS $arrLine )
{
	echo '<tr>';
	foreach ( $arrLine AS $iImage )
	{
		$n++;
		echo '<th><img id="pad_'.$n.'" src="/images/149_'.(int)$iImage.'.bmp" width="83" height="83" /></th>';
	}
	echo '</tr>';
}

?>
</table>

<p>
	<div>
		<div><a href="#" onclick="return SHIFTER.check();">check</a></div>
		<div><a href="#" onclick="$('exportresult').innerHTML = SHIFTER.export();return false;">export</a></div>
	</div>
	<div id="exportresult"></div>
</p>

<div id="dots">
	<div class="dot" style=" left:75px; top:75px;"><img pads="1,2,7,6" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:158px; top:75px;"><img pads="2,3,8,7" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:241px; top:75px;"><img pads="3,4,9,8" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:324px; top:75px;"><img pads="4,5,10,9" src="/images/149_dot.gif" /></div>
	<div class="dot" style=" left:75px;top:158px;"><img pads="6,7,12,11" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:158px;top:158px;"><img pads="7,8,13,12" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:241px;top:158px;"><img pads="8,9,14,13" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:324px;top:158px;"><img pads="9,10,15,14" src="/images/149_dot.gif" /></div>
	<div class="dot" style=" left:75px;top:241px;"><img pads="11,12,17,16" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:158px;top:241px;"><img pads="12,13,18,17" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:241px;top:241px;"><img pads="13,14,19,18" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:324px;top:241px;"><img pads="14,15,20,19" src="/images/149_dot.gif" /></div>
	<div class="dot" style=" left:75px;top:324px;"><img pads="16,17,22,21" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:158px;top:324px;"><img pads="17,18,23,22" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:241px;top:324px;"><img pads="18,19,24,23" src="/images/149_dot.gif" /></div>
	<div class="dot" style="left:324px;top:324px;"><img pads="19,20,25,24" src="/images/149_dot.gif" /></div>
</div>
</div>
</body>

</html>