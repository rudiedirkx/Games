<?php
// QUICK CLICKER

?>
<html>

<head>
<title>QuickClicker</title>
<script type="text/javascript">
<!--//
var g_bPlaying = false, g_iFieldsHit = 0;
function initStart() {
	setTimeout(start, 1000);
}
function start() {
	document.getElementById('notice').innerHTML = 'GO, you got 10 seconds!';
	g_bPlaying = true;
	g_iFieldsHit = 0;
	setTimeout(end, 10000)
}
function end() {
//	g_bPlaying=false;
	document.getElementById('notice').innerHTML = 'You done... You hit '+g_iFieldsHit+' fields. <a href="javascript:initStart();">Again</a>';
//	setTimeout(clean, 900);
}
function clean() {
	var c = document.getElementById('qc').getElementsByTagName('td'), i = c.length;
	while (i--) {
		c[i].style.backgroundColor = '#999';
	}
}
//-->
</script>
</head>

<body>
<table id="qc" border="0" cellpadding="0" cellspacing="1">
<tr><th colspan="12" id="notice" style="font-size:12px;">Hit as many fields as possible! Click <a href="javascript:initStart();">Start</a>!</th></tr>
<?php
echo str_repeat('<tr>'.str_repeat('<td style="background-color:#999;font-size:2px;height:20px;width:20px;" onclick="if(g_bPlaying){if(this.style.backgroundColor!=\'red\'){this.style.backgroundColor=\'red\';g_iFieldsHit++;}else{this.style.backgroundColor=\'#999\';g_iFieldsHit--;}}"></td>', 12).'</tr>', 8);
?>
</table>
</body>

</html>