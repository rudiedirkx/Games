<?
// TRAPPED

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

?>
<html>

<head>
<title>TRAPPED</title>
<script>
if (top.location!=this.location)
	top.location='<?=$_SERVER[SCRIPT_NAME]?>';
</script>
</head>

<body style='margin:0px;overflow:auto;'>
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr valign=middle><td><center>


<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH="360" HEIGHT="300" id="ng">
<param name="movie" value="swf/trapped.swf">
<param name="quality" value="high">
<embed src="swf/trapped.swf" quality="high" WIDTH="360" HEIGHT="300" NAME="ng" ALIGN TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
</object>


</td></tr></table>
