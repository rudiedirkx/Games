<pre><?php

$g_arrMaps = require 'inc.102.maps.php';
// print_r($g_arrMaps);

$converted = array();
foreach ($g_arrMaps as $n => $map) {
	$converted[] = array();
	foreach ($map as $line) {
		$line = implode(array_map(function($cell) {
			return $cell == -1 ? ' ' : (string) (int) $cell;
		}, $line));
		$converted[count($converted)-1][] = $line;
	}
}

var_export($converted);
