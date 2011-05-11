<?php
// THE BOX | EDITOR

require_once('inc.cls.json.php');
require_once('connect.php');

if ( isset($_POST['map'], $_POST['boxes'], $_POST['pusher'], $_POST['name']) ) {
	$arrMap = explode(',', $_POST['map']);
	foreach ( $arrMap AS $k => &$szLine ) {
		if ( '' === trim($szLine) ) {
			unset($arrMap[$k]);
		}
		else {
			$szLine = rtrim($szLine);
		}
		unset($szLine);
	}
	$arrMap = array_values($arrMap);
	$arrBoxes = array();
	foreach ( explode(',', $_POST['boxes']) AS $szBox ) {
		$arrBoxes[] = array_map('intval', explode(':', $szBox));
	}
	$arrPusher = array_map('intval', explode(':', $_POST['pusher']));
	$arrLevel = array(
		'map'		=> $arrMap,
		'pusher'	=> $arrPusher,
		'boxes'		=> $arrBoxes,
	);
	mysql_query("INSERT INTO the_box_multiple_custom_levels (name, level) VALUES ('".addslashes($_POST['name'])."', '".addslashes(serialize($arrLevel))."');") or die(mysql_error());
	exit('Your custom level has been saved as C'.mysql_insert_id());
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<title>THE BOX -MULTIPLE TARGETS | EDITOR</title>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<link rel="stylesheet" type="text/css" href="140.css" />
</head>

<body>
<img id="loading" alt="loading" src="images/loading.gif" />

<table border="1" cellpadding="15" cellspacing="0">
<tr>
	<th class="pad">NEW LEVEL</th>
	<td></td>
</tr>
<tr>
	<td class="pad">
	<table id="thebox" border="0">
		<tbody id="thebox_tbody"><?php echo str_repeat('<tr>'.str_repeat('<td></td>', 15).'</tr>', 15); ?></tbody>
	</table></td>
	<td valign="top" align="left" class="pad">
		<table>
		<tr><th class="pad" bgcolor="lime" align="center" colspan="2" id="fieldtype">empty</th></tr>
		<tr onclick="g_szField='';$('fieldtype').innerHTML='empty';"><td class="fld"></td><td>empty</td>
		<tr onclick="g_szField='wall';$('fieldtype').innerHTML='wall';"><td class="fld wall"></td><td>wall</td>
		<tr onclick="g_szField='target';$('fieldtype').innerHTML='target';"><td class="fld target">T</td><td>target</td>
		<tr onclick="g_szField='box';$('fieldtype').innerHTML='box';"><td class="fld box"></td><td>box</td>
		<tr><td class="fld pusher"></td><td>pusher</td>
		<tr><td class="pad" align="center" colspan="2"><a href="#" onclick="return evaluateTheBox();">save</a></td></tr>
		</table>
	</td>
</tr>
</table>

<script type="text/javascript">
<!--//
var g_szField = '', g_arrPusher = null, g_arrTheBox = {};

function evaluateTheBox() {
	var m = [], b = [];
	$A($('thebox_tbody').rows).each(function(row, y) {
		var r = '';
		$A(row.cells).each(function(cell, x) {
			var f = cell.wall ? 'x' : ( cell.target ? 't' : ' ' );
			r += f;
			if ( 'x' !== f && cell.box ) {
				b.push(x+':'+y);
			}
		});
		m.push(r);
	});
	m = m.join(',');
	b = b.join(',');
	g_arrTheBox = {map:m, boxes:b, pusher:false};
	alert('CLICK ON PUSHER FIELD');
	g_arrPusher = true;
	setTimeout("g_arrPusher = null;", 5000);
}

$('thebox_tbody').addEvent('click', function(e) {
	e = new Event(e).stop();
	if ( 'TD' !== e.target.nodeName ) { return; }
	if ( true === g_arrPusher && false === g_arrTheBox.pusher && !e.target.wall && !e.target.box ) {
		g_arrTheBox.pusher = e.target.cellIndex + ':' + e.target.parentNode.sectionRowIndex;
		new Ajax('?', {
			data : 'name=' + encodeURIComponent(prompt('What\'s your name?', '')) + '&map=' + g_arrTheBox.map + '&boxes=' + g_arrTheBox.boxes + '&pusher=' + g_arrTheBox.pusher,
			onComplete : function(t) {
				alert(t);
			}
		}).request();
		return false;
	}
	switch ( g_szField ) {
		case 'target':
			e.target.target = true;
			e.target.innerHTML = 'T';
			e.target.className = 'target';
		break;
		case 'wall':
			e.target.wall = true;
			e.target.target = false;
			e.target.innerHTML = '';
			e.target.className = 'wall';
		break;
		case 'box':
			e.target.box = true;
			e.target.className = 'box';
		break;
		default:
			e.target.target = false;
			e.target.box = false;
			e.target.wall = false;
			e.target.className = '';
			e.target.innerHTML = '';
		break;
	}
});

document.body.focus();
//-->
</script>
</body>

</html>