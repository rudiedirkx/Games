<?php

define('THUMB_SIZE', 91);

function get_thumbs_positions() {
	$thumbs = get_thumbs();
	$positions = array();
	foreach ( $thumbs as $index => $thumb ) {
		$positions[substr(basename($thumb), 0, -4)] = THUMB_SIZE * $index;
	}
	return $positions;
}

function get_thumbs() {
	$thumbs = glob('images/_*.gif');
	natcasesort($thumbs);
	return $thumbs;
}

function goede_gebruikersnaam( $usr ) {
	$usr = trim($usr);
	$L = strlen($usr);

	if ( 3 <= $L && 12 >= $L ) {
		return $usr;
	}
}
