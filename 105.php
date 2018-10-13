<html>

<head>
<title>CARRACE</title>
<script type="text/javascript" src="105.js"></script>
<script type="text/javascript" src="/js/mootools_1_11.js"></script>
<style type="text/css">
* { margin:0; padding:0; cursor: default; }
body, table { background-color:#999; color:#fff; font-size:13px; }
form#trackform table { background-color:black; }
form#trackform td { padding:3px; }
form#trackform td.times output { color:#bbb; margin:0 5px; font-family:'courier new'; }
</style>
</head>

<body>
<table width="100%" height="100%" border="0"><tr valign="middle"><td align="center">

<form id="trackform" autocomplete="off">
<table border="0" cellpadding="0" cellspacing="0">
<tr>
	<td colspan="2" align="center">
		Place your cursor on the starting grid and GO when u have <strong style="color: lime">green</strong> light!
	</td>
</tr>
<tr valign="middle">
	<td><img src="./images/start.gif" id="stoplicht_etc" /></td>
	<td><img border="0" src="./images/track.jpg" usemap="#track" /></td>
</tr>
<tr>
	<td></td>
	<td class="times" align="center">
		best lap: <output id="bestlap">0.0</output>,
		last lap: <output id="lastlap">0.0</output>,
		this lap: <output id="thislap">0.0</output>
	</td>
</tr>
</table>
</form>

</td></tr></table>

<map name="track">
	<area shape="poly" coords="148,16 47,16 26,24 19,38 17,100 22,127 35,135 49,132 60,120 62,100 62,72 66,67 71,101 80,112 95,111 107,102 111,89 114,68 122,69 125,76 121,95 111,120 86,140 60,153 43,165 38,190 49,206 82,218 133,218 175,215 192,208 204,197 205,182 189,170 164,170 143,170 141,160 237,160 236,194 246,213 272,220 302,216 317,204 328,185 324,160 314,140 295,130 282,119 305,79 323,47 320,23 293,11 222,15 198,23 195,45 205,65 239,71 237,84 198,94 174,89 177,62 183,46 181,29 164,16 154,16 154,1 337,1 336,228 2,227 2,2 152,2" onmouseover="Out()" href="#1" />
	<area shape="poly" coords="163,36 153,33 52,33 40,36 35,45 35,102 39,115 40,114 44,106 44,65 49,58 64,51 71,53 82,59 85,75 85,93 91,93 96,84 101,55 111,51 129,54 135,58 142,78 134,109 123,132 92,156 70,168 56,181 58,188 73,197 143,201 172,198 181,194 187,188 174,186 136,186 124,175 124,148 133,142 238,142 253,152 253,191 261,197 285,202 303,193 309,176 304,156 280,141 267,131 265,116 272,94 302,47 303,37 294,32 224,32 217,37 217,43 231,48 254,55 261,80 252,102 228,110 179,111 156,97 154,73 163,52" onmouseover="Out()" href="#2" />
	<area shape="rect" coords="171,142 207,158" onmouseover="Wamba()" onmouseout="ValseStartTest()" href="#3" />
	<area shape="poly" coords="125,166 126,149 130,146 137,144 169,143 169,158 141,159 141,166" onmouseover="Stop()" href="#4" />
	<area shape="poly" coords="236,17 237,28 296,28 307,34 314,26 300,19 277,17" onmouseover="CheckPassage()" href="#5" />
	<area shape="poly" coords="119,18 118,30 162,29 171,38 168,49 178,48 175,30 156,20" onmouseover="CheckPassage()" href="#6" />
	<area shape="poly" coords="97,84 109,85 112,62 127,67 133,60 126,56 108,56 102,63" onmouseover="CheckPassage()" href="#7" />
	<area shape="poly" coords="278,202 278,217 288,217 288,202" onmouseover="CheckPassage()" href="#8" />
</map>
</body>

</html>
