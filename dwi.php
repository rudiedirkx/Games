<?php

session_start();

include("connect.php");

$id = isset($_GET['id']) ? $_GET['id'] : 1;
$mplayer = unserialize(stripslashes(mysql_result(mysql_query("SELECT player FROM dopewars WHERE id='".$id."';"),0,'player')));



echo "<table border=1 cellpadding=0 cellspacing=0><tr valign=top><td>";

echo "<pre>&_SESSION[player]:\n";
print_r($_SESSION['player']);

echo "</td><td>";

echo "<pre>&player:\n";
print_r($mplayer);

echo "</td></tr></table>";

?>