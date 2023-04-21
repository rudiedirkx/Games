<?
// STICK RPG

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

?>
<html>

<head>
<title>STICK RPG</title>
<script>
if (top.location!=this.location)
	top.location='<?= $_SERVER['SCRIPT_NAME'] ?>';
</script>
</head>

<body style='margin:0px;overflow:auto;'>
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
<tr valign=middle><td><center>

<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
 codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
 WIDTH="550" HEIGHT="400" id="stick" ALIGN="">
 <PARAM NAME=movie VALUE="stick.swf"> <PARAM NAME=menu VALUE=false>  <PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=#FFFFFF> <EMBED src="stick.swf" quality=high bgcolor=#000000  WIDTH="550" HEIGHT="400" NAME="castle" ALIGN=""
 TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
</OBJECT>

</td></tr></table>
</body>
