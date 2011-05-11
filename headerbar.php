<?php

session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'hoofd';
$bgc = "#fff";

?>
<html>

<head>
<title>List Menu Test</title>
<style>
*
{
	margin: 0;
	padding: 0;
}

BODY, TABLE, DIV
{
	font-family: verdana, arial;
	font-size: 11px;
	background-color: <?php echo $bgc; ?>;
}





DIV#header div
{
	font-family: "Bradley Hand ITC" "Times New Roman" "Book Antiqua";
	font-size: 30px;
	font-weight: bold;
	font-style: italic;

	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 15px;
}

DIV#bar
{
	height: 10px;
	background-color: #444;
}


ul#nav
{
	list-style: none;
	border-top: 1px solid <?php echo $bgc; ?>;
}
ul#nav li
{
	float: left;
	border-left: 1px solid <?php echo $bgc; ?>;
	background: none;
	padding: 0;
	text-align: center;
}
ul#nav li a
{
	width: 90px;
	display: block;
	color: #fff;
	background: #000;
	font-weight: bold;
	padding: 0;
	padding-top: 4px;
	padding-bottom: 4px;
	text-decoration: none;
}
 
ul#nav li.current a,
ul#nav li.current a:hover
{
	background-color: #444;
	color: #fff;
	border-top: 1px solid #444;
	position: relative;
	top: -1px;
}
ul#nav li.current a:hover
{
	color: #ff7800;
}
 
ul#nav li a:hover
{
	background: #444;
	color: #ff7800;
	border-top: 1px solid <?php echo $bgc; ?>;
	position: relative;
	top: -1px;
}
</style>
</head>

<body>

<div id="box">
<div id="header">
<div align="left">jouwmoeder.nl</div>
</div>
<div id="bar">
</div>
<div id="navigation">
<ul id="nav">
	<li<?php echo ('hoofd' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://www.jouwmoeder.nl/" target="_top" title="">Index</a></li>
	<li<?php echo ('games' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://games.jouwmoeder.nl/" target="_top" title="">Games</a></li>
	<li<?php echo ('misc' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://www.jouwmoeder.nl/misc/" target="_top" title="">Misc</a></li>
	<li<?php echo ('misc_links' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://www.jouwmoeder.nl/misc/11nk5/" target="_top" title="">Misc Links</a></li>
	<li<?php echo ('projects' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://www.jouwmoeder.nl/projects/" target="_top" title="">Projects</a></li>
	<li<?php echo ('forum' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://www.jouwmoeder.nl/forum/" target="_top" title="">Forum</a></li>
	<li<?php echo ('images' == strtolower($page)) ? ' class="current"' : ''; ?>><a href="http://www.jouwmoeder.nl/images/" target="_top" title="">Images</a></li>
</ul>
</div>

</body>

</html>