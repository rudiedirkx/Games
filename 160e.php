<?php
// Pixelus editor

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<title>Pixelus</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script>window.onerror = function(e) { alert(e); };</script>
<script src="<?= html_asset('js/rjs-custom.js') ?>"></script>
<script src="<?= html_asset('160.js') ?>"></script>
<style type="text/css">
#loading { border:medium none; height:100px; left:50%; margin-left:-50px; margin-top:-50px; position:absolute; top:50%; visibility:hidden; width:100px; }
table#pixelus { border-collapse:collapse; font-family:verdana,arial; }
table#pixelus td { border:solid 1px #eee; font-size:18px; text-align:center; }
tbody#pixelus_tb td { width:25px; height:25px; color:white; font-weight:bold; cursor:pointer; }
tbody#pixelus_tb td.target { background-color:#87cefa; }
table tbody#pixelus_tb td.wall { background-color:#111; cursor:default; }
</style>
</head>

<body>
<img id="loading" alt="loading" src="images/loading.gif" />

<table border="1" cellpadding="15" cellspacing="0">
<tr>
	<td class="pad">
	<table id="pixelus" border="0">
		<tbody id="pixelus_tb"><?php echo str_repeat('<tr>'.str_repeat('<td></td>', 20).'</tr>', 20); ?></tbody>
	</table></td>
	<td valign="top" align="left" class="pad">
		<a href="#" onclick="g_class='wall';return false;">wall</a><br />
		<br />
		<a href="#" onclick="g_class='target';return false;">target</a><br />
		<br />
		<a href="#" onclick="g_class='';return false;">empty</a><br />
		<br />
		<br />
		<a href="#" onclick="return exportMap();">export</a><br />
		<br />
	</td>
</tr>
<tr>
	<td colspan="2"><pre id="export"></pre></td>
</tr>
</table>

<script type="text/javascript">
<!--//
var g_class = '';
function exportMap() {
	var m = '';
	$A($('pixelus_tb').rows).each(function(row) {
		m += ',';
		for ( var i=0; i<row.cells.length; i++ ) {
			m += row.cells[i].className === 'wall' ? 'x' : ( row.cells[i].className === 'target' ? 'o' : ' ' );
		}
	});
	m = m.substr(1);
	$('export').innerHTML = "'"+m.replace(/,/g, "',<br />'")+"'";
	return false;
}
$('pixelus_tb').onclick = function(e) {
	e = new Event(e);
	if ( 'TD' == e.target.nodeName ) {
		e.target.className = g_class;
	}
}
//-->
</script>
</body>

</html>
