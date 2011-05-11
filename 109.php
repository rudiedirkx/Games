<?
// GRIDLOCK

define( "BASEPAGE",	basename($_SERVER['SCRIPT_NAME']) );
define( "EOL",		defined('PHP_EOL') ? PHP_EOL : "\n" );

?>
<html>

<head>
<title>GRIDLOCK</title>
<script>
if (top.location!=this.location)
	top.location='<?=$_SERVER[SCRIPT_NAME]?>';
</script>
</head>

<body style='margin:0px;overflow:auto;'>
<table border=0 cellpadding=0 cellspacing=0 width=100% height=100%>
<tr valign=middle><td><center>

<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=600 HEIGHT=600 id="gridlock">
 <PARAM NAME=movie VALUE="gridlock.swf">
 <PARAM NAME=loop VALUE=false>
 <PARAM NAME=quality VALUE=high>
 <PARAM NAME=bgcolor VALUE=#FFFFFF>
 <EMBED src="gridlock.swf" quality=high bgcolor=#FFFFFF WIDTH=600 HEIGHT=600 NAME="gridlock" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
</OBJECT>

</td></tr></table>
</body>