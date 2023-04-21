<?
// JAVACAVE

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

?>
<html>

<head>
<title>JAVACAVE</title>
<script>
if (top.location!=this.location)
	top.location='<?= $_SERVER['SCRIPT_NAME'] ?>';
</script>
<style>
body { cursor:default;background-color:#999999;color:#ffffff;font-family:century gothic;font-size:15px; }
.table { cursor:default;background-color:#999999;color:#ffffff;font-family:century gothic;font-size:15px; }
a { cursor:pointer;color:#000000;text-decoration:none; }
a:active { cursor:pointer;color:#000000;text-decoration:none; }
a:visited { cursor:pointer;color:#000000;text-decoration:none; }
a:hover { cursor:pointer;color:#ffffff;text-decoration:none; }
</style>
</head>

<body style='margin:0px;overflow:auto;'>
<table width=100% height=100% border=0><tr valign=middle><td align=center>

<table border=1 cellpadding=4 cellspacing=0 bordercolor=#eeeeee class=table>
<tr valign=middle>
<td align=center width=*>
<applet code="JavaCave" alt="JavaApplet" width="128" height="160"></applet></td>
<td align=center width=*>
Click to <b>start</b><br>
<hr>
Click and hold: worm <b>goes up</b><br>
<hr>
Release mouse: worm <b>goes down</b><br></td>
</tr>
</table>

</td></tr></table>








