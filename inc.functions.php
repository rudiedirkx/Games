<?php

define('THUMB_SIZE', 91);

function do_html( $text ) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string)$text, ENT_QUOTES, 'ISO-8859-1');
}

function do_redirect($uri = null) {
	$uri or $uri = $_SERVER['SCRIPT_NAME'];

	header("Location: " . $uri);
	exit;
}

function shash($str) {
	$hash = 0;
	for ($i=0; $i < strlen($str); $i++) {
		$chr = ord($str[$i]);
		$hash = ((($hash << 5) - $hash) + $chr) & 0xFFFFFFFF; // Force to 32b int, bc JS doesn't do 64b
	}
	return sprintf('%u', $hash);
}

function get_thumbs_positions( &$thumbs = array() ) {
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
