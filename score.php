<?php

require __DIR__ . '/inc.bootstrap.php';
require __DIR__ . '/inc.db.php';

$ip = $_SERVER['REMOTE_ADDR'];
$local = preg_match('#^(192\.168\.|10\.0\.|127\.0\.)#', $ip) > 0;

$ok = function($line) use ($local) {
	return 'OK' . ( $local ? $line : '' );
};

$referrer = trim(@$_SERVER['HTTP_REFERER']);
if ( !$referrer ) {
	exit($ok(__LINE__));
}

$referrerHost = parse_url($referrer, PHP_URL_HOST);
$selfHost = $_SERVER['HTTP_HOST'];
if ( $referrerHost !== $selfHost ) {
	exit($ok(__LINE__));
}

$game = (int) trim(parse_url($referrer, PHP_URL_PATH), '/');
if ( !$game ) {
	exit($ok(__LINE__));
}

$data = $_POST;
if ( !$data ) {
	exit($ok(__LINE__));
}

$columns = array_intersect_key($data, array_flip(['level', 'score', 'time', 'moves']));

$columns['game'] = $game;
$columns['ip'] = $ip;
$columns['utc'] = time();

$more = array_diff_key($data, $columns);
$columns['more'] = json_encode((object) $more);

$db->insert('scores', $columns);

exit($ok(__LINE__));
