<?php

session_start();

define('S_NAME', '150_switchboard');

?>
<html>

<head>
<title>Switch Board</title>
<style>
table#switchboard th {
	width				: 50px;
	height				: 50px;
}
table#switchboard img {
	border				: 0;
	width				: 50px;
	height				: 50px;
}
</style>
<script type="text/javascript" src="/js/general_1_2_6.js"></script>
<script type="text/javascript" src="/js/ajax_1_2_1.js"></script>
<script type="text/javascript">
<!--//
var SWITCHBOARD = {
	types : [
		['150_0_0', '150_0_1', '150_0_2', '150_0_3'],
		['150_1_0', '150_1_1', '150_1_2', '150_1_3'],
		['150_2_0', '150_2_1', '150_2_2', '150_2_3']
	],

	switchCol : function(c) {
		var r = $('switchboard_tbody').rows, s = [];
		foreach ( r, function(k, v) {
			s.push(v.cells[c].getElementsByTagName('img')[0]);
		});
		return SWITCHBOARD.switchFromSource(s);
	},

	switchRow : function(r) {
		var s = r.getElementsByTagName('img');
		return SWITCHBOARD.switchFromSource(s);
	},

	switchFromSource : function(s) {
		foreach ( s, function(k, v) {
			var t = parseInt(v.getAttribute('type')), r = parseInt(v.getAttribute('rotation'))+1;
			if ( 3 < r ) {
				r = 0;
			}
			v.setAttribute('rotation', r);
			v.src = '/images/' + SWITCHBOARD.types[t][r] + '.bmp';
		});
		return false;
	}

};
addEventHandler(window, 'load', function() {
	foreach ( $('switchboard').getElementsByTagName('img'), function(k, v) {
		v.src = '/images/' + SWITCHBOARD.types[parseInt(v.getAttribute('type'))][parseInt(v.getAttribute('rotation'))] + '.bmp';
	});
});
//-->
</script>
</head>

<body>
<table id="switchboard" border="0" cellpadding="0" cellspacing="0">
<thead>
<tr>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th><a href="#" onclick="return SWITCHBOARD.switchCol(this.parentNode.cellIndex);">O</a></th>
	<th></th>
</tr>
</thead>
<tbody id="switchboard_tbody">
<tr>
	<td><img type="2" rotation="0" /></td>
	<td><img type="1" rotation="2" /></td>
	<td><img type="1" rotation="0" /></td>
	<td><img type="2" rotation="0" /></td>
	<td><img type="1" rotation="1" /></td>
	<td><img type="2" rotation="0" /></td>
	<td><img type="1" rotation="3" /></td>
	<th><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
<tr>
	<td><img type=1"" rotation="2" /></td>
	<td><img type=1"" rotation="2" /></td>
	<td><img type=0"" rotation="0" /></td>
	<td><img type=0"" rotation="0" /></td>
	<td><img type=1"" rotation="1" /></td>
	<td><img type=1"" rotation="2" /></td>
	<td><img type=2"" rotation="3" /></td>
	<th><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
<tr>
	<td><img type="2" rotation="3" /></td>
	<td><img type="0" rotation="0" /></td>
	<td><img type="2" rotation="3" /></td>
	<td><img type="1" rotation="3" /></td>
	<td><img type="0" rotation="0" /></td>
	<td><img type="2" rotation="3" /></td>
	<td><img type="2" rotation="2" /></td>
	<th><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
<tr>
	<td><img type="0" rotation="1" /></td>
	<td><img type="2" rotation="0" /></td>
	<td><img type="2" rotation="0" /></td>
	<td><img type="0" rotation="0" /></td>
	<td><img type="1" rotation="2" /></td>
	<td><img type="1" rotation="3" /></td>
	<td><img type="2" rotation="3" /></td>
	<th bgcolor="gold"><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
<tr>
	<td><img type="0" rotation="1" /></td>
	<td><img type="2" rotation="3" /></td>
	<td><img type="1" rotation="2" /></td>
	<td><img type="1" rotation="3" /></td>
	<td><img type="0" rotation="1" /></td>
	<td><img type="2" rotation="2" /></td>
	<td><img type="2" rotation="0" /></td>
	<th><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
<tr>
	<td><img type="2" rotation="1" /></td>
	<td><img type="0" rotation="0" /></td>
	<td><img type="1" rotation="2" /></td>
	<td><img type="1" rotation="2" /></td>
	<td><img type="2" rotation="2" /></td>
	<td><img type="0" rotation="0" /></td>
	<td><img type="2" rotation="1" /></td>
	<th><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
<tr>
	<td><img type="0" rotation="1" /></td>
	<td><img type="2" rotation="2" /></td>
	<td><img type="2" rotation="1" /></td>
	<td><img type="2" rotation="3" /></td>
	<td><img type="2" rotation="0" /></td>
	<td><img type="1" rotation="1" /></td>
	<td><img type="2" rotation="2" /></td>
	<th><a href="#" onclick="return SWITCHBOARD.switchRow(this.parentNode.parentNode);">O</a></th>
</tr>
</tbody>
</table>
</body>

</html>