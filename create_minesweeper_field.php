<?php

session_start();

if ( isset($_POST['field']) )
{
	exit('<pre>'.print_r($_POST['field'],1).'</pre>');
}
else if ( isset($_POST['sides']) )
{
	$_SESSION['create_ms_field_sides'] = explode(",", $_POST['sides']);
	header("Location: ".basename($_SERVER['PHP_SELF']));
	exit;
}

$g_arrSides = isset($_SESSION['create_ms_field_sides']) ? $_SESSION['create_ms_field_sides'] : array(16,30);

?>
<html>

<head>
<title>Create Minesweeper Field</title>
<script type="text/javascript" src="general_1_2_2.js"></script>
<script type="text/javascript">
<!--//
var g_arrImgs = {
	'-1' : 'images/dicht.gif',
	 '0' : 'images/open_0.gif',
	 '1' : 'images/open_1.gif',
	 '2' : 'images/open_2.gif',
	 '3' : 'images/open_3.gif',
	 '4' : 'images/open_4.gif',
	 '5' : 'images/open_5.gif',
	 '6' : 'images/open_6.gif',
	 '7' : 'images/open_7.gif',
	 '8' : 'images/open_8.gif',
};
for ( x in g_arrImgs ) {
	(new Image).src = g_arrImgs[x];
}

function reTileP( f_obj ) {
	iTile = f_obj.getAttribute('tile') * 1;
	iNext = iTile + 1;
	if ( 8 < iNext )
	{
		iNext = -1;
	}
	f_obj.setAttribute('tile', iNext);
	f_obj.src = g_arrImgs[iNext];
	return false;
}
function reTileM( f_obj ) {
	iTile = f_obj.getAttribute('tile') * 1;
	iNext = iTile - 1;
	if ( -1 > iNext )
	{
		iNext = 8;
	}
	f_obj.setAttribute('tile', iNext);
	f_obj.src = g_arrImgs[iNext];
	return false;
}
function createPhpArray() {
	trs = $('m_tbl').getElementsByTagName('tr');
	szPhpArray = "array(\n";
	for ( i=0; i<trs.length; i++ ) {
		szPhpArray += "	array(";
		imgs = trs[i].getElementsByTagName('img');
		d = "";
		for ( j=0; j<imgs.length; j++ ) {
			t = imgs[j].getAttribute('tile');
			$('f_'+i+'_'+j).value = t;
			d += ",";
			if ( t.length == 1 ) {
				d += " ";
			}
			d += t;
		}
		szPhpArray += d.substr(1) + "),\n";
	}
	szPhpArray += "),\n";
	$('php_array').innerHTML = szPhpArray;
}
function fakeSubmit() {
	document.forms[1].action = '?';
	document.forms[1].submit();
}
//-->
</script>
</head>

<body>
<p>
	<form method="post" action="?">
		<select name="sides" onchange="this.form.submit();">
			<option value="16,30"<?php echo $g_arrSides == array(16,30) ? ' selected="selected"' : ''; ?>>30 * 16</option>
			<option value="16,16"<?php echo $g_arrSides == array(16,16) ? ' selected="selected"' : ''; ?>>16 * 16</option>
			<option value="9,9"<?php echo $g_arrSides == array(9,9) ? ' selected="selected"' : ''; ?>>9 * 9</option>
		</select>
	</form>
</p>

<table id="m_tbl" border="0" cellpadding="0" cellspacing="0"><?php

for ( $i=0; $i<$g_arrSides[0]; $i++ )
{
	echo '<tr>';
	for ( $j=0; $j<$g_arrSides[1]; $j++ )
	{
		echo '<td><img title="['.(1+$j).','.(1+$i).']" src="images/dicht.gif" border="0" tile="-1" onclick="reTileP(this);" oncontextmenu="return reTileM(this);" /></td>';
	}
	echo '</tr>';
}

?></table>

<p><input type="button" value="create php array" onclick="createPhpArray();" /></p>

<pre id="php_array"></pre>

<form method="post" action="102c_test_field_analysis.php">
<?php

for ( $i=0; $i<$g_arrSides[0]; $i++ )
{
	for ( $j=0; $j<$g_arrSides[1]; $j++ )
	{
		echo '<input type="hidden" id="f_'.$i.'_'.$j.'" name="field['.$i.']['.$j.']" value="-1" />'."\n";
	}
}

?>
<input type="submit" value="real submit" onclick="createPhpArray();" />
<input type="button" value="fake submit" onclick="createPhpArray();fakeSubmit();" />
</form>

<p><a href="#" onclick="alert(getFormVars(document.forms[1]));return false;">test form</a></p>
</body>

</html>