<?php

require __DIR__ . '/inc.bootstrap.php';

$g_arrMap = array(
	24, 5, 15, 11, 20,
	9, 14, 25, 4, 10,
	1, 22, 3, 19, 12,
	18, 13, 23, 2, 8,
	7, 17, 6, 16, 21
);

$arrEmpties = [7, 10, 11, 17, 18, 20];

shuffle($g_arrMap);
$arrMap = array_chunk($g_arrMap, 5);

?>
<!doctype html>
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
<? include 'tpl.onerror.php' ?>
</head>

<body>
<div id="padcontainer">
<table id="padtable" border="0" cellpadding="0" cellspacing="0">
<?php

$n = 0;
foreach ( $arrMap AS $arrLine ) {
	echo '<tr>';
	foreach ( $arrLine AS $iImage ) {
		$n++;
		echo '<th><img id="pad_'.$n.'" src="/images/149_'.(int)$iImage.'.bmp" width="83" height="83" /></th>';
	}
	echo '</tr>';
}

?>
</table>

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

<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('gridgame.js') ?>"></script>
<script>
function rotate(o) {
	const c = o.attr('pads').split(',');
	const srcs = [
		$('#pad_'+c[3]).src,
		$('#pad_'+c[0]).src,
		$('#pad_'+c[1]).src,
		$('#pad_'+c[2]).src,
	]

	c.forEach(function(v, k) {
		$('#pad_'+v).src = srcs[k];
	});
}

$('#dots').on('click', 'img', function(e) {
	return rotate(this);
});
</script>
</body>

</html>
