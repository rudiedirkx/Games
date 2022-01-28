<?php
// KEYPAD

if ( isset($_GET['code']) )
{
	exit($_GET['code']);
}

?>
<title>KEYPAD</title>
<style type="text/css">
#kp td input {
	width		: 38px;
	height		: 45px;
	font-size	: 22px;
	font-weight	: bold;
}
</style>

<script type="text/javascript">
<!--//
var g_objField;
function addChar(c) {
	if ( g_objField ) {
		g_objField.value += ""+c+"";
	}
}
//-->
</script>

<form>
<table id="kp" border="0" cellpadding="2" cellspacing="0">
<tr><td align="center" style="padding:1px;" colspan="3"><input disabled BLUR="g_objField=null;this.style.backgroundColor='#fff';this.style.borderColor='#999';" onfocus="g_objField=this;this.style.backgroundColor='#ddd';this.style.borderColor='#b00';" type="text" value="" id="kpcode" style="background-color:#fff;color:#000;border:solid 1px #999;padding:8px;width:124px;vertical-align:middle;text-align:center;" /></td></tr>
<tr>
<td><input type="button" value="1" onclick="addChar('1');" /></td>
<td><input type="button" value="2" onclick="addChar('2');" /></td>
<td><input type="button" value="3" onclick="addChar('3');" /></td>
</tr>
<tr>
<td><input type="button" value="4" onclick="addChar('4');" /></td>
<td><input type="button" value="5" onclick="addChar('5');" /></td>
<td><input type="button" value="6" onclick="addChar('6');" /></td>
</tr>
<tr>
<td><input type="button" value="7" onclick="addChar('7');" /></td>
<td><input type="button" value="8" onclick="addChar('8');" /></td>
<td><input type="button" value="9" onclick="addChar('9');" /></td>
</tr>
<tr>
<td><input type="button" value="K" style="color:green;" onclick="if(g_objField&&g_objField.value.length){alert(g_objField.value);}" /></td>
<td><input type="button" value="0" onclick="addChar('0');" /></td>
<td><input type="button" value="C" style="padding:0;color:red;" onclick="if(g_objField){g_objField.value='';}" /></td>
</tr>
</table>
</form>

<script type="text/javascript">
<!--//
g_objField = document.getElementById('kpcode');
document.forms[0].reset();
//-->
</script>
